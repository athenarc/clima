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
        foreach ($flavors as $flavor)
        {
            $name=$flavor['name'];
            $id=$flavor['id'];
            $cpus=$flavor['vcpus'];
            $ram=$flavor['ram']/1024;
            $disk=$flavor['disk'];
            $ephemeral=$flavor['OS-FLV-EXT-DATA:ephemeral'];
            $io='';
            if ($ephemeral>0)
            {
                $io=" / SSD: " . $ephemeral . "GB";
            }
            
            
            $this->flavourIdNameLimitless[$id]=$name;
            
           
            $this->flavourID[$name]=$id;
            $this->flavours[$name]="$name: Virtual cores: $cpus / RAM: $ram GB / VM disk: $disk GB" . $io;
            $this->flavourCores[$name]=$cpus;
            $this->flavourRam[$name]=$ram;
            $this->flavourDisk[$name]=$disk;
            $this->flavourIdName[$id]=$name;
            
        }
       
        asort($this->flavours);
        
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
            [['description', 'url'], 'string'],
            [['num_of_vms', 'num_of_cores', 'num_of_ips'], 'default', 'value' => null],
            [['num_of_vms', 'num_of_cores', 'num_of_ips'], 'integer'],
            [['ram', 'storage'], 'number'],
            [['num_of_vms'], 'integer','min'=>0],
            [['num_of_cores'], 'integer','min'=>0],
            [['num_of_ips'], 'integer','min'=>0],
            [['ram'], 'number','min'=>0],
            [['storage'], 'number','min'=>0],
            [['name'], 'string', 'max' => 200],
            [['version'], 'string', 'max' => 50],
            [['name','version','description',],'required'],
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
            'version' => 'Version *',
            'description' => ' Description *',
            'url' => 'Existing (old) service URL ',
            'num_of_vms' => "Νumber of VMs ",
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

        Yii::$app->db->createCommand()->insert('machine_compute_request', [

                'name' => $this->name,
                'version' => $this->version,
                'description' => $this->description,
                'url' => $this->url,
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

       
        $warnings='Your request will be reviewed.';
        

            

       
        $success='Successfully added project request!';

        return [$errors,$success,$warnings];
    
    }

    public function uploadNewEdit($requestId)
    {
        $errors='';
        $success='';
        $warnings='';

        
        $this->num_of_cores=$this->flavourCores[$this->flavour];
        $this->ram=$this->flavourRam[$this->flavour];
        $this->storage=empty($this->storage) ? 0 : $this->storage;

        Yii::$app->db->createCommand()->insert('machine_compute_request', [

                'name' => $this->name,
                'version' => $this->version,
                'description' => $this->description,
                'url' => $this->url,
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


        $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
        $project=Project::find()->where(['id'=>$request->project_id])->one();

        
        $warnings='Your request will be reviewed.';
        $success="Successfully modified project '$request->name'.";

        return [$errors,$success,$warnings];
    
    }

}
