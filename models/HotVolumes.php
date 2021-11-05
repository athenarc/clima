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

    private $name, $token;
    public $openstack,$creds;

    public function initialize($vm_type)
    {

        // parent::init();
        if($vm_type==1)
        {
           $this->openstack=Openstack::find()->one();
        }
        else
        {
            $this->openstack=OpenstackMachines::find()->one();
        }
        // $this->openstack=Openstack::find()->one();
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
            [['active'],'boolean']
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


    public static function getHotVolumesInfo()
    {
        $query=new Query;

        $userID=Userw::getCurrentUser()['id'];
        $query->select(['ht.id','ht.name','ht.volume_id','p.id as project_id','p.latest_project_request_id as request_id','ht.accepted_at','cs.vm_type', 'ht.active', 'ht.deleted_at', 'ht.deleted_by', 'ht.vm_id','ht.mountpoint', 'p.name as project_name'])
              ->from('hot_volumes as ht')
              ->innerJoin('project as p','p.id=ht.project_id')
              ->innerJoin('project_request as pr', 'pr.id=p.latest_project_request_id')
              ->innerJoin('cold_storage_request as cs', 'pr.id=cs.request_id')
              ->where("$userID = ANY(pr.user_list)")
              ->andWhere(["not", ['ht.volume_id'=>null]]);
              
              // ->where(['p.status'=>[1,2]]);
              // ->andWhere(['pr.project_type'=>2]);


        $results = $query->orderBy('ht.accepted_at DESC')->all();
        
        return $results;
    }

    public static function getHotVolumesInfoAdmin()
    {
        $query=new Query;

        $userID=Userw::getCurrentUser()['id'];
        $query->select(['ht.id','ht.name','ht.volume_id','p.id as project_id','p.latest_project_request_id as request_id','ht.accepted_at','cs.vm_type', 'ht.active', 'ht.deleted_at', 'ht.deleted_by', 'ht.vm_id','ht.mountpoint', 'p.name as project_name'])
              ->from('hot_volumes as ht')
              ->innerJoin('project as p','p.id=ht.project_id')
              ->innerJoin('project_request as pr', 'pr.id=p.latest_project_request_id')
              ->innerJoin('cold_storage_request as cs', 'pr.id=cs.request_id')
              ->andWhere(["not", ['ht.volume_id'=>null]]);


        $results = $query->orderBy('ht.accepted_at DESC')->all();
        
        return $results;
    }

    public static function getVolumeServices($volume_id,$user_id)
    {
        $query=new Query;

        $userID=Userw::getCurrentUser()['id'];
        $query->select(['v.id','p.id as project_id','v.name as vm_name','p.name'])
              ->from('vm as v')
              ->innerJoin('project as p','p.id=v.project_id')
              ->innerJoin('project_request as pr', 'pr.id=p.latest_project_request_id')
              ->where(['v.active'=>true])
              ->andWhere("$userID = ANY(pr.user_list)");


        $results = $query->orderBy('v.created_at DESC')->all();
        
        return $results;
    }

    public static function getVolumeMachines($volume_id, $user_id)
    {
        $query=new Query;

        $userID=Userw::getCurrentUser()['id'];
        $query->select(['v.id','p.id as project_id','v.name as vm_name', 'p.name'])
              ->from('vm_machines as v')
              ->innerJoin('project as p','p.id=v.project_id')
              ->innerJoin('project_request as pr', 'pr.id=p.latest_project_request_id')
              ->where(['v.active'=>true])
              ->andWhere("$userID = ANY(pr.user_list)");


        $results = $query->orderBy('v.created_at DESC')->all();
        
        return $results;
    }

    public static function getCreatedVolumesServicesUser($user_id)
    {
        $query=new Query;

        $query->select(['user_list'])
              ->from('project_request as p')
              ->innerJoin('cold_storage_request as c','p.id=c.request_id')
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
              ->innerJoin('cold_storage_request as c','p.id=c.request_id')
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
                $cold_storage_request=ColdStorageRequest::find()->where(['request_id'=>$project->latest_project_request_id])->one();
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
                $cold_storage_request=ColdStorageRequest::find()->where(['request_id'=>$project->latest_project_request_id])->one();
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
        $flag=true;
        $message='';
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
        catch(Exception $e)
        {
            $flag=false;
            $token='';
            $message="There was an error contacting OpenStack API";
        }
        if(!$response->getIsOK())
        {
            $flag=false;
            $token='';
            $message=$response->data['error']['message'];
        }

        if($flag)
        {
            $token=$response->headers['x-subject-token'];
        }
        return [$token,$message];
    }


    public function createVolume($size,$name,$token,$vm_type,$project_id,$multOrder)
    {
        /*
         * Add a new ssh key
         */
        
        $volumedata=
        [
            "volume"=>
            [  
                "size"=> $size,
                "name" => $name . '_' . $multOrder,

            ],
        ];

        
        try
        {
            $client = new Client(['baseUrl' => $this->openstack->cinder_url]);
            $response = $client->createRequest()
                                ->setMethod('POST')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$token])
                                ->setUrl(base64_decode($this->openstack->tenant_id) . '/volumes')
                                ->setData($volumedata)
                                ->send();
        }
        catch(Exception $e)
        {
           
            return [false, "There was an error contacting OpenStack API"];
        }
        if (!$response->getIsOk())
        {
            return [false, ""];
        }

        $volume_id=$response->data['volume']['id'];

        Yii::$app->db->createCommand()->insert('hot_volumes', [
                            'name' => $name . '_' . $multOrder,
                            'accepted_at'=>'NOW()',
                            'project_id' => $project_id,
                            'volume_id'=>$volume_id,
                            'vm_type'=>$vm_type,
                            'active'=>true,
                        ])->execute();

        return $volume_id;

    }

    public function attachVolume($volume_id,$vm_id,$token,$vm_type)
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
                                ->addHeaders(['X-Auth-Token'=>$token])
                                ->setUrl(base64_decode($this->openstack->tenant_id) . '/volumes/' . $volume_id)
                                ->send();
        // print_r(base64_decode($this->openstack->tenant_id));
        // exit(0);
            
        }
        catch(Exception $e)
        {
           
            return [false, "There was an error contacting OpenStack API 1"];
        }

        if (!$response->getIsOk())
        {
            return [false, "There was an error contacting OpenStack API 2"];
        }

        $volumeStatus=$response->data['volume']['status'];
        
        if($volumeStatus=='in-use')
        {
            return [false, "The volume is already in use. Please first detach the volume."];
        }
        while($volumeStatus!='available')
        {
            sleep(10);
            try
            {
                $client = new Client(['baseUrl' => $this->openstack->cinder_url]);
                $response = $client->createRequest()
                                    ->setMethod('GET')
                                    ->setFormat(Client::FORMAT_JSON)
                                    ->addHeaders(['X-Auth-Token'=>$token])
                                    ->setUrl(base64_decode($this->openstack->tenant_id) . '/volumes/' . $volume_id)
                                    ->send();
                
            }
            catch(Exception $e)
            {
               
                return [false, "There was an error contacting OpenStack API 3"];
            }
            if (!$response->getIsOk())
            {
                return [false, "There was an error contacting OpenStack API 4"];
            }

            $volumeStatus=$response->data['volume']['status'];

        }
        /*
         * Check if VM is ready
         */
        if ($vm_type==1)
        {
            $vm=Vm::find()->where(['id'=>$vm_id])->one();

        }
        else
        {
            $vm=VmMachines::find()->where(['id'=>$vm_id])->one();
        }
        try
        {
            $client = new Client(['baseUrl' => $this->openstack->nova_url]);
            $response = $client->createRequest()
                            ->setMethod('GET')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$token])
                            ->setUrl('/servers/' . $vm->vm_id)
                            ->send();
        
        }
        catch(Exception $e)
        {
           
            return [false, "There was an error contacting OpenStack API 5"];
        }
        // print_r($response);
        // exit(0);
        if (!$response->getIsOk())
        {
            return [false, "There was an error contacting OpenStack API 6"];
        }
        
        $status=$response->data['server']['status'];
        

        while ($status!='ACTIVE')
        {
            
            try
            {
                $client = new Client(['baseUrl' => $this->openstack->nova_url]);
                $response = $client->createRequest()
                                    ->setMethod('GET')
                                    ->setFormat(Client::FORMAT_JSON)
                                    ->addHeaders(['X-Auth-Token'=>$token])
                                    ->setUrl('/servers/' . $vm_id)
                                    ->send();
                
            }
            catch(Exception $e)
            {
               
                return [false, "There was an error contacting OpenStack API 7"];
            }
            if (!$response->getIsOk())
            {
                return [false, "There was an error contacting OpenStack API 8"];
            }
            $status=$response->data['server']['status'];
        }

        /*
         * Attach Volume
         */

        $volumedata=
        [
            "volumeAttachment"=>
            [  
                'volumeId' => $volume_id,

            ],
        ];

        try
        {
            $client = new Client(['baseUrl' => $this->openstack->nova_url]);
            $response = $client->createRequest()
                            ->setMethod('POST')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$token])
                            ->setUrl('/servers/' . $vm->vm_id . '/os-volume_attachments' )
                            ->setData($volumedata)
                            ->send();
        }
        catch(Exception $e)
        {
           
            return [false, "There was an error contacting OpenStack API 9"];
        }
        if (!$response->getIsOk())
        {
            return [false, "There was an error contacting OpenStack API 10"];
        }

        return $response->data['volumeAttachment']['device'];

    }

    public function detachVolume($volume_id, $vm_id, $token,$vm_type)
    {
        /*
         * Add a new ssh key
         */
        if ($vm_type==1)
        {
            $vm=Vm::find()->where(['id'=>$vm_id])->one();

        }
        else
        {
            $vm=VmMachines::find()->where(['id'=>$vm_id])->one();
        }

        try
        {
            $client = new Client(['baseUrl' => $this->openstack->nova_url]);
            $response = $client->createRequest()
                                ->setMethod('DELETE')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$token])
                                ->setUrl('/servers/' . $vm->vm_id . '/os-volume_attachments/' . $volume_id)
                                ->send();
        }
        catch(Exception $e)
        {
           
            return [false, "There was an error contacting OpenStack API"];
        }
        // print_r($response);
        // exit(0);
        if (!$response->getIsOk())
        {
            return [false, "There was an error contacting OpenStack API"];
        }

        Yii::$app->db->createCommand()->update('hot_volumes',
        ['vm_id'=>'', 'mountpoint'=>''], "volume_id='$volume_id'")->execute();

        return [true,""];

        


    }

    public function deleteVolume($volume_id,$token, $id)
    {
        /*
         * Add a new ssh key
         */
        try
        {
            $client = new Client(['baseUrl' => $this->openstack->cinder_url]);
            $response = $client->createRequest()
                                ->setMethod('DELETE')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$token])
                                ->setUrl(base64_decode($this->openstack->tenant_id) . '/volumes/' . $volume_id)
                                ->send();
        }
        catch(Exception $e)
        {
           
            return [false, "There was an error contacting OpenStack API"];
        }
        if (!$response->getIsOk())
        {
            return [false, "There was an error contacting OpenStack API"];
        }

        $user=Userw::getCurrentUser()['username'];
        $username=explode('@',$user)[0];

        Yii::$app->db->createCommand()->update('hot_volumes',
            ['active'=>false, 'deleted_at'=>'NOW', 'deleted_by'=>$username], "id='$id'")->execute();

        return [true,""];

    }

}