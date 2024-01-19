<?php

namespace app\models;

use Yii;
use yii\httpclient\Client;
use app\models\ProjectRequest;
use webvimark\modules\UserManagement\models\User as Userw;
use app\models\Openstack;

/**
 * This is the model class for table "vm".
 *
 * @property int $id
 * @property string $ip_address
 * @property string $ip_id
 * @property string $vm_id
 * @property string $public_key
 * @property string $image_id
 * @property string $image_name
 */
class Vm extends \yii\db\ActiveRecord
{
    public $keyFile,$consoleLink='', $status, $diskSize;
    private $token, $port_id;
    public static $openstack,$creds;
    public $serverExists=true;


    private $create_errors=[
        1=>"There was an error connecting to the Openstack infrastructure.",
        2=>"There was an error creating a key-pair with the provided key.",
        3=>"There was an error creating the VM.",
        4=>"There was an error connecting to the Openstack infrastructure.",
        5=>"There was an error creating a public IP address.",
        6=>"There was an error creating the additional storage space.",
        7=>"There was an error attaching the additional storage space to the VM.",

    ];

    private $delete_errors=[
        1=>"There was an error connecting with the Openstack infrastructure.",
        2=>"There was an error deleting the public IP address.",
        3=>"There was an error deleting the Virtual Machine.",
        4=>"There was an error deleting the public key.",
        5=>"There was an error deleting the additional storage space.",
        6=>"There was an error detaching the additional storage space from the VM.",

    ];
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        self::$openstack=Openstack::find()->one();
        self::$creds=[
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
                        "id"=> base64_decode(self::$openstack->cred_id),
                        "secret"=> base64_decode(self::$openstack->cred_secret)
                    ],
                ]
            ]
        ];
    }

    public static function tableName()
    {
        return 'vm';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ip_id', 'vm_id', 'public_key', 'image_id'], 'string'],
            [['ip_address', 'image_name'], 'string', 'max' => 100],
            [['keyFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'pub', 'checkExtensionByMimeType' => false],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip_address' => 'Ip Address',
            'ip_id' => 'Ip ID',
            'vm_id' => 'Vm ID',
            'public_key' => 'Public Key',
            'image_id' => 'Image ID',
            'image_name' => 'Image Name',
        ];
    }

    public static function getOpenstackFlavours()
    {
        $result=self::authenticate();
        $token=$result[0];
        $message=$result[1];

        $flag=true;
        try
        {
            $client = new Client(['baseUrl' => self::$openstack->nova_url]);
            $response = $client->createRequest()
                                ->setMethod('GET')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$token])
                                ->setUrl(['flavors/detail'])
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
            $flag=false;
            return "There was an error contacting the OpenStack API. Please contact an administrator.";
        }
        if(!$response->getIsOK())
        {
            $flag=false;
            return "There was an error getting the available VM flavors from OpenStack. Please contact an administrator.";
        }

        if($flag)
        {
            return $response->data['flavors'];
        }
        
    }

    public function getOpenstackImages()
    {
        $result=self::authenticate();
        $token=$result[0];
        $message=$result[1];

        $flag=true;
        try
        {
            $client = new Client(['baseUrl' => self::$openstack->glance_url]);
            $response = $client->createRequest()
                                ->setMethod('GET')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$token])
                                ->setUrl(['images?visibility=public'])
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
            $flag=false;
            return ["There was an error contacting the OpenStack API. Please contact an administrator."];
        }
        if(!$response->getIsOK())
        {
            $flag=false;
            return ["There was an error getting the available images from OpenStack. Please contact an administrator."];
        }

        if($flag)
        {
            $dropdown=[];
            $isAdmin=Userw::hasRole('Admin',$superadminAllowed=false);
            foreach ($response->data['images'] as $image)
            {
                $id=$image['id'];
                $name=$image['name'];
    

                $dropdown[$id]=$name;
            }

            arsort($dropdown);
            return $dropdown;
        }

    }

    public static function authenticate()
    {
        /*
         * Authenticate with the openstack api
         */
        $flag=true;
        $message='';
        try
        {
            $client = new Client(['baseUrl' => self::$openstack->keystone_url]);
            $response = $client->createRequest()
                                ->setMethod('POST')
                                ->setFormat(Client::FORMAT_JSON)
                                ->setUrl('auth/tokens')
                                ->setData(self::$creds)
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
            $flag=false;
            $token='';
            $message="There was an error contacting the OpenStack API. Please contact an administrator.";
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

    public function createKey()
    {
        /*
         * Add a new ssh key
         */
        $keydata=
        [
            "keypair"=>
            [  
                "name"=> $this->name,
                "public_key"=> $this->public_key,

            ],
        ];

        try
        {
            $client = new Client(['baseUrl' => self::$openstack->nova_url]);
            $response = $client->createRequest()
                                ->setMethod('POST')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$this->token])
                                ->setUrl('os-keypairs')
                                ->setData($keydata)
                                ->send();

        }
        catch(\yii\httpclient\Exception $e)
        {
           
            return [false,"There was an error contacting the OpenStack API. Please contact an administrator."];
        }
        if (!$response->getIsOk())
        {
            return [false,""];
        }

        
        return [true,''];
    

    }

    public function createVolume($size)
    {
        /*
         * Add a new ssh key
         */

        $volumedata=
        [
            "volume"=>
            [  
                "size"=> $size,
                "name" => $this->name . '-volume',

            ],
        ];

        
        try
        {
            $client = new Client(['baseUrl' => self::$openstack->cinder_url]);
            $response = $client->createRequest()
                                ->setMethod('POST')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$this->token])
                                ->setUrl(base64_decode(self::$openstack->tenant_id) . '/volumes')
                                ->setData($volumedata)
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
           
            return [false, "There was an error contacting the OpenStack API. Please contact an administrator."];
        }
        if (!$response->getIsOk())
        {
            return [false, ""];
        }

        $this->volume_id=$response->data['volume']['id'];

        return [true, ""];

    }

    public function attachVolume()
    {
        /*
         * Check if volume is available
         */
        try
        {
            $client = new Client(['baseUrl' => self::$openstack->cinder_url]);
            $response = $client->createRequest()
                                ->setMethod('GET')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$this->token])
                                ->setUrl(base64_decode(self::$openstack->tenant_id) . '/volumes/' . $this->volume_id)
                                ->send();
            
        }
        catch(\yii\httpclient\Exception $e)
        {
           
            return [false, "There was an error contacting the OpenStack API. Please contact an administrator."];
        }
        if (!$response->getIsOk())
        {
            return [false, ""];
        }

        $volumeStatus=$response->data['volume']['status'];
        // print_r($volumeStatus);
        // exit(0);
        
        while($volumeStatus!='available')
        {
            sleep(10);
            try
            {
                $client = new Client(['baseUrl' => self::$openstack->cinder_url]);
                $response = $client->createRequest()
                                    ->setMethod('GET')
                                    ->setFormat(Client::FORMAT_JSON)
                                    ->addHeaders(['X-Auth-Token'=>$this->token])
                                    ->setUrl(base64_decode(self::$openstack->tenant_id) . '/volumes/' . $this->volume_id)
                                    ->send();
                
            }
            catch(\yii\httpclient\Exception $e)
            {
               
                return [false, "There was an error contacting the OpenStack API. Please contact an administrator."];
            }
            if (!$response->getIsOk())
            {
                return [false, ""];
            }

            $volumeStatus=$response->data['volume']['status'];

        }
        /*
         * Check if VM is ready
         */
        try
        {
            $client = new Client(['baseUrl' => self::$openstack->nova_url]);
            $response = $client->createRequest()
                            ->setMethod('GET')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$this->token])
                            ->setUrl('/servers/' . $this->vm_id)
                            ->send();
        
        }
        catch(\yii\httpclient\Exception $e)
        {
           
            return [false, "There was an error contacting the OpenStack API. Please contact an administrator."];
        }
        if (!$response->getIsOk())
        {
            return [false, ""];
        }
        
        $status=$response->data['server']['status'];
        

        while ($status!='ACTIVE')
        {
            sleep(10);
            try
            {
                $client = new Client(['baseUrl' => self::$openstack->nova_url]);
                $response = $client->createRequest()
                                    ->setMethod('GET')
                                    ->setFormat(Client::FORMAT_JSON)
                                    ->addHeaders(['X-Auth-Token'=>$this->token])
                                    ->setUrl('/servers/' . $this->vm_id)
                                    ->send();
                
            }
            catch(\yii\httpclient\Exception $e)
            {
               
                return [false, "There was an error contacting the OpenStack API. Please contact an administrator."];
            }
            if (!$response->getIsOk())
            {
                return [false, ""];
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
                'volumeId' => $this->volume_id,
                'device'=> '/dev/vdb'

            ],
        ];

        try
        {
            $client = new Client(['baseUrl' => self::$openstack->nova_url]);
            $response = $client->createRequest()
                            ->setMethod('POST')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$this->token])
                            ->setUrl('/servers/' . $this->vm_id . '/os-volume_attachments' )
                            ->setData($volumedata)
                            ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
           
            return [false, "There was an error contacting the OpenStack API. Please contact an administrator."];
        }
        if (!$response->getIsOk())
        {
            return [false, "There was an error contacting the OpenStack API. Please contact an administrator."];
        }

        return [true,""];

    }

    public function detachVolume()
    {
        /*
         * Add a new ssh key
         */
        try
        {
            $client = new Client(['baseUrl' => self::$openstack->nova_url]);
            $response = $client->createRequest()
                                ->setMethod('DELETE')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$this->token])
                                ->setUrl('/servers/' . $this->vm_id . '/os-volume_attachments/' . $this->volume_id)
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
           
            return [false, "There was an error contacting the OpenStack API. Please contact an administrator."];
        }
        if (!$response->getIsOk())
        {
            return [false, "There was an error contacting the OpenStack API. Please contact an administrator."];
        }
        return [true,""];


    }

    public function deleteVolume()
    {
        /*
         * Add a new ssh key
         */
        try
        {
            $client = new Client(['baseUrl' => self::$openstack->cinder_url]);
            $response = $client->createRequest()
                                ->setMethod('DELETE')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$this->token])
                                ->setUrl(base64_decode(self::$openstack->tenant_id) . '/volumes/' . $this->volume_id)
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
           
            return [false, "There was an error contacting the OpenStack API. Please contact an administrator."];
        }
        if (!$response->getIsOk())
        {
            return [false, "There was an error contacting the OpenStack API. Please contact an administrator."];
        }

        return [true,""];

    }


    public function deleteKey($key_name)
    {
        try
        {
            $client = new Client(['baseUrl' => self::$openstack->nova_url]);
            $response = $client->createRequest()
                                ->setMethod('DELETE')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$this->token])
                                ->setUrl(["os-keypairs/$key_name"])
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
           
            return [false, "There was an error contacting the OpenStack API. Please contact an administrator."];
        }
        if (!$response->getIsOk())
        {
            return [false, ""];
        }
        return [true,""];
    }

    public function createServer($flavour,$diskSize)
    {
        if (isset(Yii::$app->params['ioFlavors']) && isset(Yii::$app->params['ioFlavors'][$flavour]))
        {
            $blockDest='local';
        }
        else
        {
            $blockDest='volume';
        }
        $vmdata=
        [
            "server" =>
            [
                "name" => $this->name,
                "imageRef" =>$this->image_id,
                "flavorRef" => $flavour,
                "OS-DCF:diskConfig" => "AUTO",
                "networks" =>
                [
                    ['uuid'=>base64_decode(self::$openstack->internal_net_id)],
                ],
                "metadata" =>
                [
                    "My Server Name" => $this->name
                ],
                "security_groups" =>
                [
                    [
                        "name"=> "default",
                    ],
                ],

                "key_name"=>$this->name,
                "block_device_mapping_v2"=>
                [
                    [
                        "boot_index"=> "0",
                        "uuid"=> $this->image_id,
                        "source_type"=> "image",
                        "volume_size"=> $diskSize,
                        "destination_type"=> $blockDest,
                        "delete_on_termination"=> true,
                    ]
                ]

            ]
        ];

        try
        {
            $client = new Client(['baseUrl' => self::$openstack->nova_url]);
            $response = $client->createRequest()
                                ->setMethod('POST')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$this->token])
                                ->setUrl('servers')
                                ->setData($vmdata)
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
           return [false,"There was an error contacting the OpenStack API. Please contact an administrator."];
        }
        if (!$response->getIsOk())
        {
            return [false,""];
        }

        $this->vm_id=$response->data['server']['id'];

        return [true,''];
    }

    public function getServerPort()
    {
        try
        {
            $client = new Client(['baseUrl' => self::$openstack->nova_url]);
            $response = $client->createRequest()
                                ->setMethod('GET')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$this->token])
                                ->setUrl(["servers/$this->vm_id/os-interface"])
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
           return [false, "There was an error contacting the OpenStack API. Please contact an administrator."];
        }

        if (!$response->getIsOk())
        {
            return [false, ""];
        }
        
        $this->port_id=$response->data['interfaceAttachments'][0]['port_id'];

        return [true,""];
    }

    public function deleteServer()
    {
        try
        {
            $client = new Client(['baseUrl' => self::$openstack->nova_url]);
            $response = $client->createRequest()
                                ->setMethod('DELETE')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$this->token])
                                ->setUrl(["servers/$this->vm_id"])
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
           return [false, "There was an error contacting the OpenStack API. Please contact an administrator."];
        }

        if (!$response->getIsOk())
        {
            return [false, ""];
        }

        return [true,""];
    }

    public function createIP()
    {
        $ipdata=
        [
            "floatingip"=>
            [
                "project_id" => base64_decode(self::$openstack->tenant_id),
                "floating_network_id" =>  base64_decode(self::$openstack->floating_net_id),
                "port_id" => $this->port_id,
                "description" => $this->name
            ]
        ];

        try
        {
            $client = new Client(['baseUrl' => self::$openstack->neutron_url]);
            $response = $client->createRequest()
                                ->setMethod('POST')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$this->token])
                                ->setUrl('floatingips')
                                ->setData($ipdata)
                                ->send();
        }
        catch (\yii\httpclient\Exception $e)
        {
            return [false, "There was an error contacting the OpenStack API. Please contact an administrator."];
        }
        if (!$response->getIsOk())
        {
            return [false, ""];
        }

        $this->ip_id=$response->data['floatingip']['id'];
        $this->ip_address=$response->data['floatingip']['floating_ip_address'];

        return [true,""];
    }

    public function deleteIP()
    {
        try
        {
            $client = new Client(['baseUrl' => self::$openstack->neutron_url]);
            $response = $client->createRequest()
                                ->setMethod('DELETE')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$this->token])
                                ->setUrl(["floatingips/$this->ip_id"])
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
           return [false, "There was an error contacting the OpenStack API. Please contact an administrator."];
        }

        if (!$response->getIsOk())
        {
            return [false, ""];
        }

        return [true,""];

    }


    public static function getOpenstackAvailableResources()
    {
        $result=self::authenticate();
        $token=$result[0];
        $message=$result[1];
        $client = new Client(['baseUrl' => self::$openstack->nova_url]);
        $responseOK=false;

        while(!$responseOK)
        {
            try
            {
                $response = $client->createRequest()
                                    ->setMethod('GET')
                                    ->setFormat(Client::FORMAT_JSON)
                                    ->addHeaders(['X-Auth-Token'=>$token])
                                    ->setUrl(['limits'])
                                    ->send();
            }
            catch(\yii\httpclient\Exception $e)
            {
               $responseOK=false;
            }

            if (($response->getIsOk()) && (isset($response->data['limits'])))
            {
                $responseOK=true;
            }
            sleep(1);
                    
        }
        $results=$response->data['limits']['absolute'];


        $cpu=$results['maxTotalCores']-$results['totalCoresUsed'];
        $ram=$results['maxTotalRAMSize']-$results['totalRAMUsed'];
        $ram/=1024;

        try
        {
            $client = new Client(['baseUrl' => self::$openstack->neutron_url]);
            $response = $client->createRequest()
                                ->setMethod('GET')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$token])
                                ->setUrl(['floatingips'])
                                ->setData(['floating_network_id'=>self::$openstack->floating_net_id])
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
            return ['','','',''];
        }
        if (!$response->getIsOk())
        {
            return ['','','',''];
        }

        $ipRes=$response->data['floatingips'];
        
        if (empty($ipRes))
        {
            $usedIps=0;
        }
        else
        {
            $usedIps=count($ipRes);
        }
        
        // print_r($token);
        try
        {
            $client = new Client(['baseUrl' => self::$openstack->neutron_url]);
            $response = $client->createRequest()
                                ->setMethod('GET')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$token])
                                ->setUrl(['quotas/' . base64_decode(self::$openstack->tenant_id)])
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
            return ['','','',''];
        }
        if (!$response->getIsOk())
        {
            return ['','','',''];
        }


        $floatingIps=$response->data['quota']['floatingip'];
        
        $ips=$floatingIps-$usedIps;
        

        /*
         * Get available volume space
         */
        try
        {
            $client = new Client(['baseUrl' => self::$openstack->cinder_url]);
            $response = $client->createRequest()
                                ->setMethod('GET')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$token])
                                ->setUrl([base64_decode(self::$openstack->tenant_id) .'/limits'])
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
            return ['','','',''];
        }
        if (!$response->getIsOk())
        {
            return ['','','',''];
        }


        $used_disk=$response->data['limits']['absolute']['totalGigabytesUsed'];
        $total_disk=$response->data['limits']['absolute']['maxTotalVolumeGigabytes'];
        $disk=$total_disk-$used_disk;
        

        return [$cpu,$ram,$ips,$disk];
        
    }

    public static function getOpenstackStorageStatistics() {
        $statistics = [
            'storage'=>null
        ];

        $result=self::authenticate();
        $token=$result[0];

        try
        {
            $client = new Client(['baseUrl' => self::$openstack->cinder_url]);
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setFormat(Client::FORMAT_JSON)
                ->addHeaders(['X-Auth-Token'=>$token])
                ->setUrl([base64_decode(self::$openstack->tenant_id) .'/limits'])
                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
            return $statistics;
        }
        if (!$response->getIsOk())
        {
            return $statistics;
        }


        $result = $response->data['limits']['absolute'];

        $statistics['storage'] = [
            'current' => $result['totalGigabytesUsed'],
            'total' => $result['maxTotalVolumeGigabytes']
        ];

        return $statistics;
    }

    public static function getOpenstackCpuAndRamStatistics()
    {
        $statistics = [
            'num_of_cores' => null,
            'ram' => null
        ];

        $result = self::authenticate();
        $token = $result[0];

        $client = new Client(['baseUrl' => self::$openstack->nova_url]);
        try {
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setFormat(Client::FORMAT_JSON)
                ->addHeaders(['X-Auth-Token' => $token])
                ->setUrl(['limits'])
                ->send();

        } catch (\yii\httpclient\Exception $e) {
            return $statistics;
        }
        if (!$response->getIsOk()) {
            return $statistics;
        }

        $result = $response->data['limits']['absolute'];

        $statistics['num_of_cores'] = [
            'current' => $result['totalCoresUsed'],
            'total' => $result['maxTotalCores']
        ];
        $statistics['ram'] = [
            'current' => $result['totalRAMUsed'] / 1024,
            'total' => $result['maxTotalRAMSize'] / 1024
        ];
        return $statistics;
    }

    public static function getOpenstackIpStatistics() {
        $statistics = [
            'num_of_ips' => null
        ];

        $result = self::authenticate();
        $token = $result[0];

        try
        {
            $client = new Client(['baseUrl' => self::$openstack->neutron_url]);
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setFormat(Client::FORMAT_JSON)
                ->addHeaders(['X-Auth-Token'=>$token])
                ->setUrl(['floatingips'])
                ->setData(['floating_network_id'=>self::$openstack->floating_net_id])
                ->send();
        }
        catch (\yii\httpclient\Exception $e)
        {
            return $statistics;
        }
        if (!$response->getIsOk())
        {
            return $statistics;
        }

        $result=$response->data['floatingips'];

        if (empty($result))
        {
            $currentIps=0;
        }
        else
        {
            $currentIps=count($result);
        }

        try
        {
            $client = new Client(['baseUrl' => self::$openstack->neutron_url]);
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setFormat(Client::FORMAT_JSON)
                ->addHeaders(['X-Auth-Token'=>$token])
                ->setUrl(['quotas/' . base64_decode(self::$openstack->tenant_id)])
                ->send();
        }
        catch (\yii\httpclient\Exception $e)
        {
            return $statistics;
        }
        if (!$response->getIsOk())
        {
            return $statistics;
        }

        $totalIps=$response->data['quota']['floatingip'];

        $statistics['num_of_ips']=[
            'current'=>$currentIps,
            'total'=>$totalIps
        ];

        return $statistics;
    }

    public function createVM($requestId,$service,$images,$diskSize)
    {   
        $keyFileName=Yii::$app->params['tmpKeyLocation'] . $this->keyFile->baseName . '.' . $this->keyFile->extension;
        $this->keyFile->saveAs($keyFileName);
        $this->windows_unique_id='';
        
        /*
         * If we are creating a windows image then we need a pair of keys (public,private)
         * in order to get the password for the machine
         */
        if (isset(Yii::$app->params['windowsImageIDs'][$this->image_id]))
        {
            $this->windows_unique_id=uniqid();
            $folder=Yii::$app->params['windowsKeysFolder'] . '/' . $this->windows_unique_id . '/';
            $command='mkdir ' . $folder;
            exec($command,$out,$ret);


            $keyCommand='ssh-keygen -t RSA -P "" -m PEM -f ' . $folder . 'key';
            unset($out);
            exec($keyCommand,$out,$ret);
            $keyFileName=$folder . 'key.pub';
        }

        $this->public_key=file_get_contents($keyFileName);
        $this->public_key=trim($this->public_key);
        $user=Userw::getCurrentUser()['id'];
        $project_request=ProjectRequest::find()->where(['id'=>$requestId])->one();
        $project_id=$project_request->project_id;
        $project=Project::find()->where(['id'=>$project_id])->one();
        $this->image_name=$images[$this->image_id];

        $this->name=$project->name;
        
        
        $flavour=$service->vm_flavour;

        /*
         * Get authentication token from the openstack api
         */

        $result=self::authenticate();
        $this->token=$result[0];
        $message=$result[1];

        if (empty($this->token))
        {
            return [1,$this->create_errors[2],$message];
        }

        /*
         * Add a new ssh key
         */
        $result=$this->createKey();
        $keyCreated=$result[0];
        $message=$result[1];
        
        if (!$keyCreated)
        {
            return [2,$this->create_errors[2],$message];
        }

        /*
         * Create VM
         */
        
        $result=$this->createServer($flavour,$diskSize);
        $serverCreated=$result[0];
        $message=$result[1];

        if (!$serverCreated)
        {
            $this->deleteKey($this->name);
            return [3,$this->create_errors[3],$message];
        }


        /*
         * Get server port but sleep for 15 seconds to ensure that the VM is up and running
         */
        sleep(15);
        $result=$this->getServerPort();
        $portRetrieved=$result[0];
        $message=$result[1];


        if (!$portRetrieved)
        {
            $this->deleteServer();
            $this->deleteKey($this->name);
            return [4,$this->create_errors[4],$message];
        }

        /*
         * Create floating ip
         */

        $result=$this->createIP();
        $ipCreated=$result[0];
        $message=$result[1];

        if (!$ipCreated)
        {
            $this->deleteServer();
            $this->deleteKey($this->name);
            return [5,$this->create_errors[5],''];
        }

        
        
        Yii::$app->db->createCommand()->insert('vm', [
                'request_id'=> $requestId,
                'project_id' => $project_id,
                'ip_address' => $this->ip_address,
                'ip_id' => $this->ip_id,
                'vm_id' => $this->vm_id,
                'public_key' => $this->public_key,
                'image_id'=> $this->image_id,
                'image_name' => $this->image_name,
                'active' => true,
                'keypair_name'=> $this->name,
                'created_by'=> $user,
                'created_at'=>'NOW()',
                'name'=> $this->name,
                'windows_unique_id' => $this->windows_unique_id,
            ])->execute();

        return [0,'',''];



    }

    public function deleteVM()
    {
        $vmid=$this->id;
        $user=Userw::getCurrentUser()['id'];
        $result=self::authenticate();
        $this->token=$result[0];
        $message=$result[1];

        if (empty($this->token))
        {
            return [1,$this->delete_errors[1],$message];
        }

        $result=$this->deleteIP();
        $ipDeleted=$result[0];
        $message=$result[1];

        if (!$ipDeleted)
        {
            return [2,$this->delete_errors[2],$message];
        }

        $result=$this->deleteServer();
        $serverDeleted=$result[0];
        $message=$result[1];
        if (!$serverDeleted)
        {
            return [3,$this->delete_errors[3],$message];
        }

        $result=$this->deleteKey($this->keypair_name);
        $keyDeleted=$result[0];
        $message=$result[1];
        if (!$keyDeleted)
        {
            return [4,$this->$delete_errors[4],$message];
        }

        Yii::$app->db->createCommand()
        ->update('vm',['deleted_by'=>$user,], "id=$this->id")
        ->execute();

        Yii::$app->db->createCommand()
                     ->update('vm',['active'=>false,'deleted_at'=>'NOW()'], "id=$this->id")
                     ->execute();

        return [0,'',''];
    }

    public function retrieveWinPassword()
    {
        $result=self::authenticate();
        $token=$result[0];
        $message=$result[1];
        
        $passNotExists=true;
        $encrypted='';
        while ($passNotExists || empty($encrypted))
        {
            try
            {
                $client = new Client(['baseUrl' => self::$openstack->nova_url]);
                $response = $client->createRequest()
                                    ->setMethod('GET')
                                    ->setFormat(Client::FORMAT_JSON)
                                    ->addHeaders(['X-Auth-Token'=>$token])
                                    ->setUrl('/servers/' . $this->vm_id . '/os-server-password')
                                    ->send();
            }
            catch(\yii\httpclient\Exception $e)
            {
                $passNotExists=true;
                $encrypted='';
            }
            if ($response->getIsOk())
            {
                $passNotExists=false;
                $encrypted=$response->data['password'];

            }
            sleep(5);

        }
        
        $keyfile=Yii::$app->params['windowsKeysFolder'] . '/' . $this->windows_unique_id . '/key';

        $command="echo '$encrypted' | openssl base64 -d | openssl rsautl -decrypt -inkey $keyfile -keyform PEM";
        exec($command,$out,$ret);
        
        $password=$out[0];

        $this->read_win_password=true;
        $this->save(false);

        return $password;

    }

    public function getConsoleLink()
    {
        $result=self::authenticate();
        $token=$result[0];
        $message=$result[1];

        try
        {
            $client = new Client(['baseUrl' => self::$openstack->nova_url]);
            $response = $client->createRequest()
                                ->setMethod('GET')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$token])
                                ->setUrl('/servers/' . $this->vm_id)
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
                $this->serverExists=false;
                return;
        }

        
        if (!$response->getIsOk())
        {
            $this->serverExists=false;
            return;
        }
            

        $counter=0;
        $consoleData=
        [
            "os-getSPICEConsole"=>
            [
                "type"=>"spice-html5"
            ]
        ];
        $consoleAvailable=false;
        while (!$consoleAvailable)
        {
            try
            {
                $client = new Client(['baseUrl' => self::$openstack->nova_url]);
                $response = $client->createRequest()
                                    ->setMethod('POST')
                                    ->setFormat(Client::FORMAT_JSON)
                                    ->addHeaders(['X-Auth-Token'=>$token])
                                    ->addHeaders(['X-OpenStack-Nova-API-Version'=> '2.1'])
                                    ->setUrl('/servers/' . $this->vm_id . '/action')
                                    ->setData($consoleData)
                                    ->send();
            }
            catch(\yii\httpclient\Exception $e)
            {
                $consoleAvailable=false;
                return;
            }

            
            if ($response->getIsOk() && (isset($response->data['console'])))
            {
                $consoleAvailable=true;
            }
            sleep(2);
            $counter++;
            if ($counter>10)
            {
                $this->consoleLink='';
                return;
            }
        }
        $console=$response->data['console'];
        $this->consoleLink=$console['url'];
    }

    public function getServerStatus()
    {
        $result=self::authenticate();
        $token=$result[0];

        if (empty($token))
        {
            return '';
        }
        try
        {
            $client = new Client(['baseUrl' => self::$openstack->nova_url]);
            $response = $client->createRequest()
                                ->setMethod('GET')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$token])
                                ->addHeaders(['X-OpenStack-Nova-API-Version'=> '2.1'])
                                ->setUrl('/servers/' . $this->vm_id)
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
            return '';
        }
        $this->status=$response->data['server']['status'];
        
        return $this->status;

    }

    public function startVM()
    {
        $result=self::authenticate();
        $token=$result[0];

        if (empty($token))
        {
            return 'error';
        }
        $startData=
        [
            "os-start"=> null
        ];
        
        
        try
        {
            $client = new Client(['baseUrl' => self::$openstack->nova_url]);
            $response = $client->createRequest()
                                ->setMethod('POST')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$token])
                                ->addHeaders(['X-OpenStack-Nova-API-Version'=> '2.1'])
                                ->setUrl('/servers/' . $this->vm_id . '/action')
                                ->setData($startData)
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
            return 'error';
        }
        return 'success';

    }

    public function stopVM()
    {
        $result=self::authenticate();
        $token=$result[0];

        if (empty($token))
        {
            return 'error';
        }
        $startData=
        [
            "os-stop"=> null
        ];
        
        
        try
        {
            $client = new Client(['baseUrl' => self::$openstack->nova_url]);
            $response = $client->createRequest()
                                ->setMethod('POST')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$token])
                                ->addHeaders(['X-OpenStack-Nova-API-Version'=> '2.1'])
                                ->setUrl('/servers/' . $this->vm_id . '/action')
                                ->setData($startData)
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
            return 'error';
        }
        return 'success';

    }

    public function rebootVM()
    {
        $result=self::authenticate();
        $token=$result[0];

        if (empty($token))
        {
            return 'error';
        }
        $startData=
        [
            "reboot" => 
            [
                "type" => "SOFT"
            ]
        ];
        
        
        try
        {
            $client = new Client(['baseUrl' => self::$openstack->nova_url]);
            $response = $client->createRequest()
                                ->setMethod('POST')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$token])
                                ->addHeaders(['X-OpenStack-Nova-API-Version'=> '2.1'])
                                ->setUrl('/servers/' . $this->vm_id . '/action')
                                ->setData($startData)
                                ->send();
        }
        catch(\yii\httpclient\Exception $e)
        {
            return 'error';
        }
        return 'success';

    }
}
