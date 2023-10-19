<?php

namespace app\models;

use Yii;
use yii\db\Query;
use webvimark\modules\UserManagement\models\User as Userw;
use yii\httpclient\Client;
use app\models\ProjectRequest;
use yii\helpers\Url;
use app\models\Notification;
use app\models\Openstack;
use app\models\OpenstackMachines;
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
class MachineComputeRequest extends \yii\db\ActiveRecord
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
        
        $openstack=OpenstackMachines::find()->one();
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
        $client = new Client(['baseUrl' => $openstack->keystone_url]);
        $response = $client->createRequest()
                            ->setMethod('POST')
                            ->setFormat(Client::FORMAT_JSON)
                            ->setUrl('auth/tokens')
                            ->setData($creds)
                            ->send();
        $token=$response->headers['x-subject-token'];

        $client = new Client(['baseUrl' => $openstack->nova_url]);
        $response = $client->createRequest()
                            ->setMethod('GET')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$token])
                            ->setUrl(['flavors/detail'])
                            ->send();

        $flavors=$response->data['flavors'];
        // print_r($flavors);
        // exit(0);

        //sort by cpu. If cpu is the same, sort by ram. If ram is the same sort by disk
        usort($flavors, function ($a, $b) {
            if ($a['vcpus'] !== $b['vcpus']) {
                return $a['vcpus'] - $b['vcpus'];
            } elseif($a['ram'] !== $b['ram']) {
                return $a['ram'] - $b['ram'];
            }
            return $a['disk'] - $b['disk'];
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
            
            
            $this->flavourIdNameLimitless[$id]=$name;
            
           
            $this->flavourID[$name]=$id;
            $this->flavours[$name]=$label;
            $this->flavourCores[$name]=$cpus;
            $this->flavourRam[$name]=$ram;
            $this->flavourDisk[$name]=$volume;
            $this->flavourIdName[$id]=$name;
            
        }
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'machine_compute_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['num_of_vms', 'num_of_cores', 'num_of_ips'], 'default', 'value' => null],
            [['num_of_vms', 'num_of_cores', 'num_of_ips'], 'integer'],
            [['ram', 'storage'], 'number'],
            [['num_of_vms'], 'integer','min'=>0],
            [['num_of_cores'], 'integer','min'=>0],
            [['num_of_ips'], 'integer','min'=>0],
            [['ram'], 'number','min'=>0],
            [['storage'], 'number','min'=>0],
            [['description'],'required'],
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

        


        return [
            'id' => 'ID',
            'name' => 'Name *', 
            'description' => ' Description *',
            'num_of_vms' => "Νumber of VMs",
            'num_of_cores' => "Νumber of CPU cores",
            'num_of_ips' => "Νumber of public IP addresses ",
            'ram' => "Μemory (in GBs)",
            'storage' => "Storage (in GBs)",
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
        

    }

    public function uploadNew($requestId)
    {
        $errors='';
        $success='';
        $warnings='';

        
        $this->num_of_cores=$this->flavourCores[$this->flavour];
        $this->ram=$this->flavourRam[$this->flavour];
        $this->storage=empty($this->storage) ? 0 : $this->storage;
        $this->num_of_vms=intval($this->num_of_vms);

        Yii::$app->db->createCommand()->insert('machine_compute_request', [

                'description' => $this->description,
                'num_of_vms' => $this->num_of_vms,
                'num_of_cores' => $this->num_of_cores,
                'num_of_ips' => 1,
                'ram' => $this->ram,
                'storage' => $this->storage,
                'request_id' => $requestId,
                'vm_flavour' => $this->flavourID[$this->flavour],
                'disk' => $this->flavourDisk[$this->flavour]
            ])->execute();

       
        $warnings='Your request will be reviewed.';
        

            

       
        $success='Successfully added project request!';

        return [$errors,$success,$warnings];
    
    }

    public function uploadNewEdit($requestId,$uchanged)
    {
        $errors='';
        $success='';
        $warnings='';

        
        $this->num_of_cores=$this->flavourCores[$this->flavour];
        $this->ram=$this->flavourRam[$this->flavour];
        $this->storage=empty($this->storage) ? 0 : $this->storage;
        $this->num_of_vms=intval($this->num_of_vms);

        Yii::$app->db->createCommand()->insert('machine_compute_request', [

                'description' => $this->description,
                'num_of_vms' => $this->num_of_vms,
                'num_of_cores' => $this->num_of_cores,
                'num_of_ips' => 1,
                'ram' => $this->ram,
                'storage' => $this->storage,
                'request_id' => $requestId,
                'vm_flavour' => $this->flavourID[$this->flavour],
                'disk' => $this->flavourDisk[$this->flavour]
            ])->execute();


        $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
        $project=Project::find()->where(['id'=>$request->project_id])->one();
        $old_request=ProjectRequest::find()->where(['id'=>$project->latest_project_request_id])->one();
        $old_request_status=0;
        if(!empty($old_request))
        {
            $old_request_status=1;
        }

        if ($uchanged && $old_request_status==1)
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


            // $project=Project::find()->where(['id'=>$request->project_id])->one();

            //set status for old request to -3 (modified)

            
            
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
            $project->save();
            $warnings='';
        }
        else
        {
            $warnings='Your request will be reviewed.';
            $project_id=$project->id;
            $message="Project $project->name has been modified and is pending approval.";
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
            $differenceRam = ($this->num_of_vms ?? 1) * $currentProjectRam - ($otherProjectNumOfVms ?: ($this->num_of_vms ?? 1)) * $otherProjectRam;

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
        $diff=[];
        $otherAttributes = $other->getAttributes();

        foreach ($otherAttributes as $attributeName => $attributeValue)
        {
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
