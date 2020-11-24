<?php

namespace app\models;

use Yii;
use yii\db\Query;
use webvimark\modules\UserManagement\models\User as Userw;
use yii\httpclient\Client;
use app\models\ProjectRequest;
use yii\helpers\Url;
use app\models\Notification;
/**
 * This is the model class for table "service".
 *
 * @property int $id
 * @property string $name
 * @property string $version
 * @property string $description
 * @property string $url
 * @property int $num_of_vms
 * @property int $num_of_cores
 * @property int $num_of_ips
 * @property double $ram
 * @property double $storage
 * @property int $project_id
 */
class ServiceRequest extends \yii\db\ActiveRecord
{
    private $limits;
    private $role;
    public $flavour;

    public $flavours=[];
    private $flavourCores=[];
    private $flavourRam=[];
    private $flavourID=[];
    private $flavourDisk=[];
    public $flavourIdName=[];
    public $flavourIdNameLimitless=[];


    public function init()
    {
        parent::init();

        $gold=Userw::hasRole('Gold',$superadminAllowed=false);
        $silver=Userw::hasRole('Silver',$superadminAllowed=false);
        $isAdmin=Userw::hasRole('Admin',$superadminAllowed=false);
        if ($gold)
        {
            $this->role='gold';
        }
        else if ($silver)
        {
            $this->role='silver';
        }
        else
        {
            $this->role='temporary';
        }

        if (!$isAdmin)
        {
            $this->limits=ServiceLimits::find()->where(['user_type'=>$this->role])->one();
        }
        else
        {
            $this->limits=new ServiceLimits;
            $this->limits->cores=100000000;
            $this->limits->ram=100000000;
            $this->limits->vms=100000000;
            $this->limits->storage=10000000;
        }
        
        
        $client = new Client(['baseUrl' => 'https://keystone-louros.cloud.grnet.gr:5000/v3']);
        $response = $client->createRequest()
                            ->setMethod('POST')
                            ->setFormat(Client::FORMAT_JSON)
                            ->setUrl('auth/tokens')
                            ->setData(Yii::$app->params['openstackAuth'])
                            ->send();
        $token=$response->headers['x-subject-token'];

        $client = new Client(['baseUrl' => 'https://ncc-louros.cloud.grnet.gr:8774/v2.1']);
        $response = $client->createRequest()
                            ->setMethod('GET')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$token])
                            ->setUrl(['flavors/detail'])
                            ->send();

        $flavors=$response->data['flavors'];
        // print_r($flavors);
        // exit(0);
        foreach ($flavors as $flavor)
        {
            $name=$flavor['name'];
            $id=$flavor['id'];
            $cpus=$flavor['vcpus'];
            $ram=$flavor['ram']/1024;
            $disk=$flavor['disk'];
            
            
            $this->flavourIdNameLimitless[$id]=$name;
            
            if ((($cpus > $this->limits->cores) || ($ram > $this->limits->ram)) && (!$isAdmin))
            {
                continue;
            }
            $this->flavourID[$name]=$id;
            $this->flavours[$name]="Virtual cores: $cpus / RAM: $ram GB / VM disk: $disk GB";
            $this->flavourCores[$name]=$cpus;
            $this->flavourRam[$name]=$ram;
            $this->flavourDisk[$name]=$disk;
            $this->flavourIdName[$id]=$name;
            
        }
        // $this->flavour=(!empty($this->vm_flavour)) ? $this->flavourIdName[$this->vm_flavour] : '';
        asort($this->flavours);
        // print_r($this->vm_flavour);
        // print_r($this->flavour);
        // exit(0);

    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'service_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'url'], 'string'],
            [['num_of_vms', 'num_of_cores', 'num_of_ips'], 'default', 'value' => null],
            [['num_of_vms', 'num_of_cores', 'num_of_ips'], 'integer'],
            [['ram', 'storage'], 'number'],
            [['num_of_vms'], 'integer','max'=>$this->limits->vms,'min'=>0],
            [['num_of_cores'], 'integer','max'=>$this->limits->cores,'min'=>0],
            [['num_of_ips'], 'integer','max'=>$this->limits->ips,'min'=>0],
            [['ram'], 'number','max'=>$this->limits->ram,'min'=>0],
            [['storage'], 'number','max'=>$this->limits->storage,'min'=>0],
            [['name'], 'string', 'max' => 200],
            [['version'], 'string', 'max' => 50],
            [['name','version','description','trl'],'required'],
            [['flavour'],'required'],
            [['additional_resources'],'string'],
            [['disk'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {

        $autoaccept=ServiceAutoaccept::find()->where(['user_type'=>$this->role])->one();
        

        $maxstorage=$this->limits->storage;
        $autoacceptstorage=$autoaccept->storage;
        
        $maxvms=$this->limits->vms;
        $autoacceptvms=$autoaccept->vms;
        
        $maxcores=$this->limits->cores;
        $autoacceptcores=$autoaccept->cores;

        $maxips=$this->limits->ips;
        $autoacceptips=$autoaccept->ips;

        $maxram=$this->limits->ram;
        $autoacceptram=$autoaccept->ram;


        return [
            'id' => 'ID',
            'name' => 'Name *', 
            'version' => 'Version *',
            'description' => ' Description *',
            'url' => 'Existing (old) service URL ',
            'trl' => ' Technology readiness level (TRL) ',
            'num_of_vms' => "Maximum allowed number of VMs * [upper limits: $autoacceptvms (automatically accepted), $maxvms (requires RAC review)]",
            'num_of_cores' => "Maximum allowed number of CPU cores * [upper limits: $autoacceptcores (automatically accepted), $maxcores (requires RAC review)]",
            'num_of_ips' => "Maximum allowed number of public IP addresses * [upper limits: $autoacceptips (automatically accepted),  $maxips (requires RAC review)]",
            'ram' => "Maximum allowed memory (in GBs) * [upper limits: $autoacceptram (automatically accepted), $maxram (requires RAC review)]",
            'storage' => "Maximum allowed storage (in GBs) * [upper limits: $autoacceptstorage (automatically accepted),  $maxstorage (requires RAC review)]",
            'request_id' => 'Project ID',
            'flavour' => 'Choose VM configuration',
        ];
    }

    public function compareServices($service1,$service2)
    {
        $num_of_cores_service1=$service1->flavourCores[$service1->flavour];
        $num_of_cores_service2=$service2->flavourCores[$service2->flavour];
        $ram_service1=$service1->flavourRam[$service1->flavour];
        $ram_service2=$service2->flavourRam[$service2->flavour];
        $disk1=$service1->flavourDisk[$service1->flavour];
        $disk2=$service2->flavourDisk[$service2->flavour];
        $storage1=$service1->storage;
        $storage2=$service2->storage;
        if( ($num_of_cores_service2 < $num_of_cores_service1) || ($ram_service2 < $ram_service1) || ($disk2 < $disk1) || $storage2 < $storage1 )
        {
            return true;
        }
        return false;
        // print_r($num_of_cores_service2.'');
        // print_r($num_of_cores_service2.'');
        // print_r($ram_service1);
        // print_r($ram_service2);
        // print_r($disk1);
        // print_r($disk2);
        // print_r($storage1);
        // print_r($storage2);
        // exit(0);

    }

    public function uploadNew($requestId)
    {
        $errors='';
        $success='';
        $warnings='';

        // print_r($this->flavourID[$this->flavour]);
        // exit(0);
        $this->num_of_cores=$this->flavourCores[$this->flavour];
        $this->ram=$this->flavourRam[$this->flavour];
        $this->storage=empty($this->storage) ? 0 : $this->storage;

        Yii::$app->db->createCommand()->insert('service_request', [

                'name' => $this->name,
                'version' => $this->version,
                'description' => $this->description,
                'url' => $this->url,
                'trl' => $this->trl,
                'num_of_vms' => 1,
                'num_of_cores' => $this->num_of_cores,
                'num_of_ips' => 1,
                'ram' => $this->ram,
                'additional_resources'=>$this->additional_resources,
                'storage' => $this->storage,
                'request_id' => $requestId,
                'vm_flavour' => $this->flavourID[$this->flavour],
                'disk' => $this->flavourDisk[$this->flavour]
            ])->execute();

        $query= new Query;
        $query->select(['vms','ips','ram','cores', 'storage'])
              ->from('service_autoaccept')
              ->where(['user_type'=>$this->role]);
         
        $row=$query->one();

        $autoaccepted_num=Project::find()->where(['status'=>2,'project_type'=>1])->count();

        //get project request and project
        $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
        $project=Project::find()->where(['id'=>$request->project_id])->one();

        if ($autoaccepted_num<1)
        {
            $autoaccept_allowed=true;
        }
        else
        {
            $autoaccept_allowed=false;
        }


        if (($this->num_of_cores<=$row['cores']) && ($this->ram <=$row['ram']) && ($this->storage<=$row['storage']) 
            && ($this->num_of_ips <=$row['ips']) && ($this->num_of_vms <=$row['vms']) && ($autoaccept_allowed) )
        {
            
            $request->status=2;
            $request->approval_date='NOW()';
            $request->approved_by=0;
            $request->save(false);

            /*
             * Get project_request from request id in order to get the project_id 
             * in order to update the latest active request 
             */
            // $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
            $message="Project '$request->name' has been automatically approved.";

            
            
            
            
            foreach ($request->user_list as $user) 
            {
                Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'auto-approved']));
            }// $result=$query->select(['project_id'])
            //           ->from('project_request')
            //           ->where(['id'=>$requestId])
            //           ->one();
            // $projectId=$result['project_id'];


            ;
            $project->latest_project_request_id=$request->id;
            $project->pending_request_id=null;
            $project->status=2;
            $project->save();
        }
        else
        {
            $warnings='Your request will be examined by the RAC.';
        }

            

       
       $success='Successfully added project request!';

       return [$errors,$success,$warnings];
    
    }

    public function uploadNewEdit($requestId)
    {
        $errors='';
        $success='';
        $warnings='';

        // print_r($this->flavourID[$this->flavour]);
        // exit(0);
        $this->num_of_cores=$this->flavourCores[$this->flavour];
        $this->ram=$this->flavourRam[$this->flavour];
        $this->storage=empty($this->storage) ? 0 : $this->storage;

        Yii::$app->db->createCommand()->insert('service_request', [

                'name' => $this->name,
                'version' => $this->version,
                'description' => $this->description,
                'url' => $this->url,
                'trl' => $this->trl,
                'num_of_vms' => 1,
                'num_of_cores' => $this->num_of_cores,
                'num_of_ips' => 1,
                'ram' => $this->ram,
                'storage' => $this->storage,
                'request_id' => $requestId,
                'additional_resources'=>$this->additional_resources,
                'vm_flavour' => $this->flavourID[$this->flavour],
                'disk' => $this->flavourDisk[$this->flavour]
            ])->execute();

        $query= new Query;
        $query->select(['vms','ips','ram','cores', 'storage'])
              ->from('service_autoaccept')
              ->where(['user_type'=>$this->role]);
         
        $row=$query->one();


        $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
        $project=Project::find()->where(['id'=>$request->project_id])->one();
        $autoaccepted_num=Project::find()->where(['status'=>2,'project_type'=>1])->count();

        if (($project->status==2) || ($autoaccepted_num<1))
        {
            $autoaccept_allowed=true;
        }
        else
        {
            $autoaccept_allowed=false;
        }


        // print_r($row);
        // exit(0);
        if (($this->num_of_cores<=$row['cores']) && ($this->ram <=$row['ram']) && ($this->storage<=$row['storage']) 
            && ($this->num_of_ips <=$row['ips']) && ($this->num_of_vms <=$row['vms']) && ($autoaccept_allowed) )
        {
            $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
            $request->status=2;
            $request->approval_date='NOW()';
            $request->approved_by=0;
            $request->save(false);

            /*
             * Get project_request from request id in order to get the project_id 
             * in order to update the latest active request 
             */
            // $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
            $message="Updates to project '$request->name' have been automatically approved.";

            
            
            
            
            foreach ($request->user_list as $user) 
            {
                Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'auto-approved']));
            }// $result=$query->select(['project_id'])
            //           ->from('project_request')
            //           ->where(['id'=>$requestId])
            //           ->one();
            // $projectId=$result['project_id'];

            //set status for old request to -3 (modified)
            $old_request=ProjectRequest::find()->where(['id'=>$project->latest_project_request_id])->one();
            if (!empty($old_request))
            {
                $old_request->status=-3;
                $old_request->save(false);
            }
            
            $project->latest_project_request_id=$request->id;
            $project->pending_request_id=null;
            $project->status=2;
            $project->save(false);
        }
        else
        {
            $warnings='Your request will be examined by the RAC.';
        }

            

       
       $success="Successfully modified project '$request->name'.";

       return [$errors,$success,$warnings];
    
    }

}
