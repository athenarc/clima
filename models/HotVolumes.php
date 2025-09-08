<?php

namespace app\models;


use Yii;
use app\models\User;
use webvimark\modules\UserManagement\models\User as Userw;
use yii\db\Query;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\httpclient\Client;
use app\models\Openstack;
use app\models\Vm;
use app\models\VmMachines;
use app\models\OpenstackMachines;
use app\models\ProjectRequest;



/**
 * This is the model class for table "hot_volumes".
 *
 * @property int $id
 * @property int|null $project_id
 * @property string|null $volume_id
 * @property string|null $vm_id
 * @property string|null $mountpoint
 * @property string|null $accepted_at
 */
class HotVolumes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */

    private $name, $token='';
    public $openstack,$creds,$errorMessage='', $vm_dropdown=[], $new_vm_id='';

    public function initialize($vm_type)
    {

        if($vm_type==1)
        {
           $this->openstack=Openstack::find()->one();
        }
        else
        {
            $this->openstack=OpenstackMachines::find()->one();
        }
        
        $this->creds=[
            "auth"=> 
            [
                "identity"=>
                [
                    "methods"=>
                    [
                        "application_credential"
                    ],
                
                    "application_credential"=>
                    [
                        "id"=> base64_decode($this->openstack->cred_id),
                        "secret"=> base64_decode($this->openstack->cred_secret)
                    ],
                ]
            ]
        ];
    }
    public static function tableName()
    {
        return 'hot_volumes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id'], 'default', 'value' => null],
            [['project_id'], 'integer'],
            [['vm_type'], 'integer'],
            [['volume_id', 'vm_id', 'mountpoint', 'deleted_by'], 'string'],
            [['accepted_at','deleted_at'], 'safe'],
            [['active'],'boolean'],
            [['new_vm_id'],'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'Project ID',
            'volume_id' => 'Volume ID',
            'vm_id' => 'Vm ID',
            'mountpoint' => 'Mountpoint',
            'accepted_at' => 'Accepted At',
        ];
    }


    public function getVms()
    {
        $query=new Query;

        if ($this->vm_type==1)
        {
            $table='vm';
        }
        else
        {
            $table='vm_machines';
        }
        $userID=Userw::getCurrentUser()['id'];
        $results=$query->select(['v.id as vm_id','p.id as project_id','v.name as vm_name','p.name'])
                      ->from("$table as v")
                      ->innerJoin('project as p','p.id=v.project_id')
                      ->innerJoin('project_request as pr', 'pr.id=p.latest_project_request_id')
                      ->where(['v.active'=>true])
                      ->andWhere("$userID = ANY(pr.user_list)")
                      ->orderBy('v.created_at DESC')->all();
        
        foreach ($results as $res) 
        {
            $this->vm_dropdown[$res['vm_id']]=$res['vm_name'];
        }

        return;
    }


    public static function getCreatedVolumesServicesUser($user_id)
    {
        $query=new Query;

        $query->select(['user_list'])
              ->from('project_request as p')
              ->innerJoin('storage_request as c','p.id=c.request_id')
              ->where(['>', 'p.end_date','NOW'])
              ->andWhere(['p.project_type'=>2])
              ->andWhere(['p.status'=>[1,2]])
              ->andWhere(['c.vm_type'=>1]);
     
        $results = $query->all();
       
        if(str_contains($results[0]['user_list'], $user_id))
        {
            return true;
        }
        else
        {
            return false;
        }

    }

    public static function getCreatedVolumesMachinesUser($user_id)
    {
        $query=new Query;

        $query->select(['user_list'])
              ->from('project_request as p')
              ->innerJoin('storage_request as c','p.id=c.request_id')
              ->where(['>', 'p.end_date','NOW'])
              ->andWhere(['p.project_type'=>2])
              ->andWhere(['p.status'=>[1,2]])
              ->andWhere(['c.vm_type'=>1]);
     
        $results = $query->all();
       
        if(str_contains($results[0]['user_list'], $user_id))
        {
            return true;
        }
        else
        {
            return false;
        }

    }

    public static function getAdditionalStorageServices($vm_id)
    {
        $query=new Query;

        $query->select(['*'])
              ->from('hot_volumes')
              ->where(['vm_id'=>$vm_id])
              ->andWhere(['active'=>true]);
     
        $results = $query->all();

        $additional_storage=[];
        if(!empty($results))
        {
            foreach ($hotvolume as $hot) 
            {
                $project=Project::find()->where(['id'=>$hot->project_id])->one();
                $cold_storage_request=StorageRequest::find()->where(['request_id'=>$project->latest_project_request_id])->one();
                $additional_storage[$hot->id]=['name'=>$hot->name, 'size'=>$cold_storage_request->storage,'mountpoint'=>$hot->mountpoint];
            }
        }
        return $additinal_storage;
    }

    public static function getAdditionalStorageMachines($vm_id)
    {
        $query=new Query;

        $query->select(['*'])
              ->from('hot_volumes')
              ->where(['vm_id'=>$vm_id])
              ->andWhere(['active'=>true]);
     
        $results = $query->all();

        $additional_storage=[];
        if(!empty($results))
        {
            foreach ($hotvolume as $hot) 
            {
                $project=Project::find()->where(['id'=>$hot->project_id])->one();
                $cold_storage_request=StorageRequest::find()->where(['request_id'=>$project->latest_project_request_id])->one();
                $additional_storage[$hot->id]=['name'=>$hot->name, 'size'=>$cold_storage_request->storage,'mountpoint'=>$hot->mountpoint];
            }
        }
        return $additinal_storage;
    }

    public function authenticate()
    {
        /*
         * Authenticate with the openstack api
         */
        
        try
        {
            $client = new Client(['baseUrl' => $this->openstack->keystone_url]);
            $response = $client->createRequest()
                                ->setMethod('POST')
                                ->setFormat(Client::FORMAT_JSON)
                                ->setUrl('auth/tokens')
                                ->setData($this->creds)
                                ->send();
        }
        catch(\Exception $e)
        {
            $this->errorMessage="There was an error contacting OpenStack API " . $e;
            return;
        }
        if(!$response->getIsOK())
        {
            $this->errorMessage==$response->data['error']['message'];
            return;
        }

        $this->token=$response->headers['x-subject-token'];

        return;
    }


    public function create($project,$crequest,$order)
    {
        /*
         * Add a new ssh key
         */
        $this->name=$project->name . '_' . $order;
        $volumedata=
        [
            "volume"=>
            [  
                "size"=> $crequest->storage,
                "name" => $this->name,
            ],
        ];

        
        try
        {
            $client = new Client(['baseUrl' => $this->openstack->cinder_url]);
            $response = $client->createRequest()
                                ->setMethod('POST')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$this->token])
                                ->setUrl(base64_decode($this->openstack->tenant_id) . '/volumes')
                                ->setData($volumedata)
                                ->send();
        }
        catch(\Exception $e)
        {
            $this->errorMessage="There was an error contacting OpenStack API. Please contact an administrator. " . $e;
        }
        if (!$response->getIsOk())
        {
            $this->errorMessage="There was an error with the request to the OpenStack API. Please contact an administrator.";
        }
        
        try
        {
            $volume_id=$response->data['volume']['id'];
        }
        catch(\Exception $e)
        {
            $this->errorMessage=$response->data['badRequest']['message'];
            return;
        }
        $this->created_at='NOW()';
        $this->project_id=$project->id;
        $this->volume_id=$volume_id;
        $this->vm_type=$crequest->vm_type;
        $this->mult_order=$order;
        $this->active=true;
        $this->save();


        return $volume_id;

    }

    public function attach()
    {
        /*
         * Check if volume is available
         */
        try
        {
            $client = new Client(['baseUrl' => $this->openstack->cinder_url]);
            $response = $client->createRequest()
                                ->setMethod('GET')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$this->token])
                                ->setUrl(base64_decode($this->openstack->tenant_id) . '/volumes/' . $this->volume_id)
                                ->send();
            
        }
        catch(Exception $e)
        {
           
            $this->errorMessage="Error code 1 while creating: there was an error contacting the OpenStack API. " . $e;
            return;
        }

        if (!$response->getIsOk())
        {
            $this->errorMessage="Error code 2 while creating: there was an error contacting the OpenStack API.";
            return;
        }

        $volumeStatus=$response->data['volume']['status'];
        
        if($volumeStatus=='in-use')
        {
            $this->errorMessage="The volume is already in use. Please detach the volume first.";
            return;
        }

        if($volumeStatus!='available')
        {
            $this->errorMessage="The volume is not available. Please try again or contact an administrator.";
            return;
        }


        /*
         * Check if VM is ready
         */
        if ($this->vm_type==1)
        {
            $vm=Vm::find()->where(['id'=>$this->new_vm_id])->one();

        }
        else
        {
            $vm=VmMachines::find()->where(['id'=>$this->new_vm_id])->one();
        }
        try
        {
            $client = new Client(['baseUrl' => $this->openstack->nova_url]);
            $response = $client->createRequest()
                            ->setMethod('GET')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$this->token])
                            ->setUrl('/servers/' . $vm->vm_id)
                            ->send();
        
        }
        catch(\Exception $e)
        {
           
            $this->errorMessage="Error code 5 while creating: there was an error contacting the OpenStack API. " . $e;
            return;
        }
        
        if (!$response->getIsOk())
        {
            $this->errorMessage="Error code 6 while creating: there was an error contacting the OpenStack API.";
            return;
        }
        
        $status=$response->data['server']['status'];
        
        /*
         * Check if VM is ready.
         */
        if ($status!='ACTIVE')
        {
            $this->errorMessage="The VM is not ready. Please try again or contact an administrator.";
            return;
        }


        /*
         * Attach Volume
         */

        $volumedata=
        [
            "volumeAttachment"=>
            [  
                'volumeId' => $this->volume_id,

            ],
        ];

        try
        {
            $client = new Client(['baseUrl' => $this->openstack->nova_url]);
            $response = $client->createRequest()
                            ->setMethod('POST')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$this->token])
                            ->setUrl('/servers/' . $vm->vm_id . '/os-volume_attachments' )
                            ->setData($volumedata)
                            ->send();
        }
        catch(Exception $e)
        {
           
            $this->errorMessage="Error code 9 while creating: there was an error contacting the OpenStack API. " . $e;
            return;
        }
        if (!$response->getIsOk())
        {
            $this->errorMessage="Error code 10 while creating: there was an error contacting the OpenStack API.";
            return;
        }

        $this->mountpoint=$response->data['volumeAttachment']['device'];
        $this->vm_id=$this->new_vm_id;
        $this->save();

    }


    public function detach()
    {
        /*
         * Add a new ssh key
         */
        if ($this->vm_type==1)
        {
            $vm=Vm::find()->where(['id'=>$this->vm_id])->one();

        }
        else
        {
            $vm=VmMachines::find()->where(['id'=>$this->vm_id])->one();
        }

        try
        {
            $client = new Client(['baseUrl' => $this->openstack->nova_url]);
            $response = $client->createRequest()
                                ->setMethod('DELETE')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$this->token])
                                ->setUrl('/servers/' . $vm->vm_id . '/os-volume_attachments/' . $this->volume_id)
                                ->send();
        }
        catch(\Exception $e)
        {
           
            $this->errorMessage="Error code 1 while detaching: there was an error contacting the OpenStack API. " . $e;
            return;
        }
        
        if (!$response->getIsOk())
        {
            $this->errorMessage="Error code 2 while detaching: there was an error contacting the OpenStack API.";
            return;
        }

        $this->vm_id='';
        $this->mountpoint='';

        $this->save();  


    }

    /*
     * Name "delete" clashed with the ActiveRecord function
     */
    public function deleteVolume()
    {
        if (!empty($this->vm_id))
        {
            $this->detach();
            if (!empty($this->errorMessage))
            {
                return;
            }
            /*
             * After detaching we must wait until the volume is available for deletion.
             */
            try
            {
                $client = new Client(['baseUrl' => $this->openstack->cinder_url]);
                $response = $client->createRequest()
                                    ->setMethod('GET')
                                    ->setFormat(Client::FORMAT_JSON)
                                    ->addHeaders(['X-Auth-Token'=>$this->token])
                                    ->setUrl(base64_decode($this->openstack->tenant_id) . '/volumes/' . $this->volume_id)
                                    ->send();
                
            }
            catch(\Exception $e)
            {
                
                $this->errorMessage="Error code 1 while deleting: there was an error contacting the OpenStack API. " . $e;
                return;
            }

            if (!$response->getIsOk())
            {
                $this->errorMessage="Error code 2 while deleting: there was an error contacting the OpenStack API.";
                return;
            }

            $volumeStatus=$response->data['volume']['status'];
            
            while($volumeStatus!='available')
            {
                sleep(5);
                try
                {
                    $client = new Client(['baseUrl' => $this->openstack->cinder_url]);
                    $response = $client->createRequest()
                                        ->setMethod('GET')
                                        ->setFormat(Client::FORMAT_JSON)
                                        ->addHeaders(['X-Auth-Token'=>$this->token])
                                        ->setUrl(base64_decode($this->openstack->tenant_id) . '/volumes/' . $this->volume_id)
                                        ->send();
                    
                }
                catch(\Exception $e)
                {
                   
                    $this->errorMessage="Error code 3 while deleting: there was an error contacting the OpenStack API. " . $e;
                    return;
                }
                if (!$response->getIsOk())
                {
                    $this->errorMessage="Error code 4 while deleting: there was an error contacting the OpenStack API.";
                    return;
                }

                $volumeStatus=$response->data['volume']['status'];
            }
        }
        /*
         * Delete volume using the OpenStack API
         */
        try
        {
            $client = new Client(['baseUrl' => $this->openstack->cinder_url]);
            $response = $client->createRequest()
                                ->setMethod('DELETE')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$this->token])
                                ->setUrl(base64_decode($this->openstack->tenant_id) . '/volumes/' . $this->volume_id)
                                ->send();
        }
        catch(\Exception $e)
        {
            $this->errorMessage="Error code 5 while deleting: there was an error contacting the OpenStack API. " . $e;
            return;
        }

        if (!$response->getIsOk())
        {
            $this->errorMessage="Error code 6 while deleting: there was an error contacting the OpenStack API.";
            return;
        }

        $user=Userw::getCurrentUser()['username'];
        $username=explode('@',$user)[0];

        $this->deleted_by=$username;
        $this->deleted_at='NOW()';
        $this->active=false;
        $this->save();

        return;
       

    }

}