<?php

namespace app\models;

use Yii;
use yii\db\Query;
use webvimark\modules\UserManagement\models\User as Userw;
use yii\httpclient\Client;
use app\models\ProjectRequest;
use yii\helpers\Url;
use app\models\Notification;
use app\models\Configuration;
use app\models\Openstack;
use app\models\ServiceAutoaccept;
use app\models\User;
use app\models\EmailEventsUser;
use app\models\EmailEventsModerator;
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

    private $allFlavourCores=[];
    private $allFlavourRam=[];
    private $allFlavourDisk=[];
    public $allFlavours=[];
    private $allFlavourID=[];


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
            $this->role='bronze';
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
        
        $openstack=Openstack::find()->one();
        // $client = new Client(['baseUrl' => 'https://keystone-louros.cloud.grnet.gr:5000/v3']);
        $creds=[
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
                        "id"=> base64_decode($openstack->cred_id),
                        "secret"=> base64_decode($openstack->cred_secret)
                    ],
                ]
            ]
        ];

        $flag=true;
        try
        {
            $client = new Client(['baseUrl' => $openstack->keystone_url]);
            $response = $client->createRequest()
                                ->setMethod('POST')
                                ->setFormat(Client::FORMAT_JSON)
                                ->setUrl('auth/tokens')
                                ->setData($creds)
                                ->send();
            
        }
        catch(Exception $e)
        {
            $flag=false;
            $this->flavours=["There was an error with the OpenStack configuration."];
        }
        if(!$response->getIsOK())
        {
            $flag=false;
            $this->flavours=["There was an error with the OpenStack configuration."];
        }
        if(!$flag)
        {
            $token="";
        }
        else
        {
            $token=$response->headers['x-subject-token'];

        }
        

        try
        {
            $client = new Client(['baseUrl' => $openstack->nova_url]);
            $response = $client->createRequest()
                                ->setMethod('GET')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$token])
                                ->setUrl(['flavors/detail'])
                                ->send();
        }
        catch(Exception $e)
        {
            $flag=false;
            $this->flavours=["There was an error with the OpenStack configuration."];
        }
        if(!$response->getIsOK())
        {
            $flag=false;
            $this->flavours=["There was an error with the OpenStack configuration."];        
        }
        
        if($flag)
        {
            $flavors=$response->data['flavors'];
            
            //sort by cpu. If cpu is the same, sort by ram
            usort($flavors, function ($a, $b) {
                if ($a['vcpus'] == $b['vcpus']) {
                    return $a['ram'] - $b['ram'];
                }
                return $a['vcpus'] - $b['vcpus'];
            });

            foreach ($flavors as $flavor)
            {
                $name=$flavor['name'];
                $id=$flavor['id'];
                $cpus=$flavor['vcpus'];
                $ram=$flavor['ram']/1024;
                $volume=$flavor['disk'];
                if (isset(Yii::$app->params['ioFlavors']) && isset(Yii::$app->params['ioFlavors'][$id]))
                {
                    $label="$name: Virtual cores: $cpus / RAM: $ram GB / SSD: $volume GB";
                }
                else
                {
                    $label="$name: Virtual cores: $cpus / RAM: $ram GB / Volume: $volume GB";
                }
            
                /*
                 * This is done due to users 
                 * needing larger VMs than the respective limits
                 */
                $this->flavourIdNameLimitless[$id]=$name;
                $this->allFlavourCores[$name]=$cpus;
                $this->allFlavourRam[$name]=$ram;
                $this->allFlavourDisk[$name]=$volume;
                $this->allFlavours[$name]=$label;
                $this->allFlavourID[$name]=$id;
                
                if ((($cpus > $this->limits->cores) || ($ram > $this->limits->ram)) && (!$isAdmin))
                {
                    continue;
                }
                $this->flavourID[$name]=$id;
                $this->flavours[$name]=$label;
                $this->flavourCores[$name]=$cpus;
                $this->flavourRam[$name]=$ram;
                $this->flavourDisk[$name]=$volume;
                $this->flavourIdName[$id]=$name;
                
            }
            // $this->flavour=(!empty($this->vm_flavour)) ? $this->flavourIdName[$this->vm_flavour] : '';

        }
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
            'num_of_vms' => "Maximum allowed number of VMs * [upper limits: $autoacceptvms (automatically accepted), $maxvms (requires review)]",
            'num_of_cores' => "Maximum allowed number of CPU cores * [upper limits: $autoacceptcores (automatically accepted), $maxcores (requires review)]",
            'num_of_ips' => "Maximum allowed number of public IP addresses * [upper limits: $autoacceptips (automatically accepted),  $maxips (requires review)]",
            'ram' => "Maximum allowed memory (in GBs) * [upper limits: $autoacceptram (automatically accepted), $maxram (requires review)]",
            'storage' => "Maximum allowed storage (in GBs) * [upper limits: $autoacceptstorage (automatically accepted),  $maxstorage (requires review)]",
            'request_id' => 'Project ID',
            'flavour' => 'Choose VM configuration',
        ];
    }

    public static function compareServices($service1,$service2)
    {
        $num_of_cores_service1=$service1->allFlavourCores[$service1->flavour];
        $num_of_cores_service2=$service2->allFlavourCores[$service2->flavour];
        $ram_service1=$service1->allFlavourRam[$service1->flavour];
        $ram_service2=$service2->allFlavourRam[$service2->flavour];
        $disk1=$service1->allFlavourDisk[$service1->flavour];
        $disk2=$service2->allFlavourDisk[$service2->flavour];
        $storage1=$service1->storage;
        $storage2=$service2->storage;
        if( ($num_of_cores_service2 < $num_of_cores_service1) || ($ram_service2 < $ram_service1) || ($disk2 < $disk1) || $storage2 < $storage1 )
        {
            return true;
        }
        return false;
        

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

        // $autoaccepted_num=Project::find()->where(['status'=>2,'project_type'=>1])->count();
        $role=User::getRoleType();
        $service_autoaccept= ServiceAutoaccept::find()->where(['user_type'=>$role])->one();
        $service_autoaccept_number=$service_autoaccept->autoaccept_number;
        $autoaccepted_num=ProjectRequest::find()->where(['status'=>2,'project_type'=>1,'submitted_by'=>Userw::getCurrentUser()['id'], ])->andWhere(['>=','end_date', date("Y-m-d")])->count();
        
        $autoaccept_allowed=($autoaccepted_num - $service_autoaccept_number < 0) ? true :false; 

        //get project request and project
        $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
        $project=Project::find()->where(['id'=>$request->project_id])->one();

        
        $message_autoaccept='';
        $message_autoaccept_mod='';
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
            $message="Project '$request->name' has been automatically approved.";

            
            
            
            
            foreach ($request->user_list as $user) 
            {
                Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'auto-approved']));
            }
            $project->latest_project_request_id=$request->id;
            $project->pending_request_id=null;
            $project->status=2;
            $project->name=$request->name;
            $project->save();
            $username = User::returnUsernameById($request->submitted_by);


            $message_autoaccept="We are happy to inform you that yor project '$project->name' has been automatically approved. <br /> You can access the project resources via the " . Yii::$app->params['name'] . " website";  
            $message_autoaccept_mod="We would like to inform you that the 24/7 service project '$project->name', submitted by user $username, has been automatically approved.";


            
        }
        else
        {
            $warnings='Your request will be reviewed.';
        }

            

       
       $success='Successfully added project request!';

       return [$errors,$success,$warnings,$message_autoaccept,$project->id, $message_autoaccept_mod];
    
    }

    public function uploadNewEdit($requestId,$uchanged)
    {
        $errors='';
        $success='';
        $warnings='';

        
        $this->num_of_cores=$this->allFlavourCores[$this->flavour];
        $this->ram=$this->allFlavourRam[$this->flavour];
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
                'vm_flavour' => $this->allFlavourID[$this->flavour],
                'disk' => $this->allFlavourDisk[$this->flavour]
            ])->execute();

        $query= new Query;
        $query->select(['vms','ips','ram','cores', 'storage'])
              ->from('service_autoaccept')
              ->where(['user_type'=>$this->role]);
         
        $row=$query->one();

        $role=User::getRoleType();
        $service_autoaccept= ServiceAutoaccept::find()->where(['user_type'=>$role])->one();
        $service_autoaccept_number=$service_autoaccept->autoaccept_number;
        $autoaccepted_num=ProjectRequest::find()->where(['status'=>2,'project_type'=>1,'submitted_by'=>Userw::getCurrentUser()['id'], ])->andWhere(['>=','end_date', date("Y-m-d")])->count();
        $autoaccept_allowed=($autoaccepted_num - $service_autoaccept_number < 0) ? true :false; 

        $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
        $project=Project::find()->where(['id'=>$request->project_id])->one();
        // $autoaccepted_num=Project::find()->where(['status'=>2,'project_type'=>1])->count();

        


        if ( (($this->num_of_cores<=$row['cores']) && ($this->ram <=$row['ram']) && ($this->storage<=$row['storage']) 
            && ($this->num_of_ips <=$row['ips']) && ($this->num_of_vms <=$row['vms']) && ($autoaccept_allowed) ) || $uchanged)
        {

            /*
             * Get project_request from request id in order to get the project_id 
             * in order to update the latest active request 
             */
            
            $message="Updates to project '$request->name' have been automatically approved.";
            
            foreach ($request->user_list as $user) 
            {
                Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'auto-approved']));
            }

            //set status for old request to -3 (modified)
            $old_request=ProjectRequest::find()->where(['id'=>$project->latest_project_request_id])->one();
            
            $request->status=$old_request->status;
            $request->approval_date='NOW()';
            $request->approved_by=$old_request->approved_by;
            $request->save(false);

            if (!empty($old_request))
            {
                $old_request->status=-3;
                $old_request->save(false);
            }
            
            $project->latest_project_request_id=$request->id;
            $project->pending_request_id=null;
            $project->status=$old_request->status;
            $project->name=$request->name;
            $project->save(false);
        }
        else
        {
            $prev = null;
            if (!empty($project->latest_project_request_id)) {
                $prev = ProjectRequest::find()->where(['id' => $project->latest_project_request_id])->one();
            }

            // Safely resolve submitted_by
            $submitted_by = $prev->submitted_by
                ?? $request->submitted_by
                ?? (Yii::$app->user->id ?? null);

            $username   = $submitted_by ? User::returnUsernameById($submitted_by) : '(unknown user)';
            $warnings='Your request will be reviewed.';
            $project_id=$project->id;
            $message="The 24/7 service project '$project->name', created by user $username, has been modified and is pending approval.";
            EmailEventsModerator::NotifyByEmail('edit_project', $project_id,$message);
        }

            

       
       $success="Successfully modified project '$request->name'.";

       return [$errors,$success,$warnings];
    
    }

    public function getFormattedDiff($other) {
        $diff = $this->getDiff($other);

        // 3. For each resource type calculate difference and fetch from OpenStack corresponding resource
        // status
        $otherProjectNumOfVms = null;
        if (isset($diff['num_of_vms'])) {
            // Store num_of_vms for the other project request because it may be needed in evaluation of differences of
            // other resources
            $otherProjectNumOfVms = $diff['num_of_vms']['other'];

            $diff['num_of_vms']['difference'] = $diff['num_of_vms']['current'] - $diff['num_of_vms']['other'];;
        }

        // For cores, ram, ips and disk, even if no change has been made to the number of individual VM resources, if
        // the number of VMs changed, then the total amount needed regarding these resources also has changed.
        if (isset($diff['num_of_cores']) || isset($diff['num_of_vms'])) {
            // If no change has been made to a project's num_of_cores, then get the value from the current project
            // details request
            $currentProjectCores = $diff['num_of_cores']['current'] ?? $this->num_of_cores;
            $otherProjectCores = $diff['num_of_cores']['other'] ?? $currentProjectCores;
            $differenceCores = $this->num_of_vms * $currentProjectCores - ($otherProjectNumOfVms ?: $this->num_of_vms) * $otherProjectCores;

            $diff['num_of_cores']['difference'] = $differenceCores;
        }
        if (isset($diff['ram']) || isset($diff['num_of_vms'])) {
            $currentProjectRam = $diff['ram']['current'] ?? $this->ram;
            $otherProjectRam = $diff['ram']['other'] ?? $currentProjectRam;
            $differenceRam = $this->num_of_vms * $currentProjectRam - ($otherProjectNumOfVms ?: $this->num_of_vms ) * $otherProjectRam;

            $diff['ram']['difference'] = $differenceRam;
        }
        if (isset($diff['num_of_ips']) || isset($diff['num_of_vms'])) {
            $currentProjectNumOfIps = $diff['num_of_ips']['current'] ?? $this->num_of_ips;
            $otherProjectNumOfIps = $diff['num_of_ips']['other'] ?? $currentProjectNumOfIps;
            $differenceIps = $this->num_of_vms * $currentProjectNumOfIps - ($otherProjectNumOfVms ?: $this->num_of_vms) * $otherProjectNumOfIps;

            $diff['num_of_ips']['difference'] = $differenceIps;
        }
        if (isset($diff['disk']) || isset($diff['num_of_vms'])) {
            $currentProjectDisk = $diff['disk']['current'] ?? $this->disk;
            $otherProjectDisk = $diff['disk']['other'] ?? $currentProjectDisk;
            $differenceDisk = $this->num_of_vms * $currentProjectDisk - ($otherProjectNumOfVms ?: $this->num_of_vms) * $otherProjectDisk;

            $diff['disk']['difference'] = $differenceDisk;
        }

        return $diff;
    }

    public function getDiff($other) {
        $exclude = ['id','request_id'];
        $diff=[];
        $otherAttributes = $other->getAttributes();

        foreach ($otherAttributes as $attributeName => $attributeValue)
        {
            if (in_array($attributeName,$exclude)) continue;
            if($this->$attributeName !== $attributeValue) {
                $diff[$attributeName]=[
                    'current'=>$this->$attributeName,
                    'other'=>$attributeValue
                ];
            }
        }

        return $diff;
    }
}
