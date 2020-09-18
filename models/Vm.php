<?php

namespace app\models;

use Yii;
use yii\httpclient\Client;
use app\models\ProjectRequest;
use webvimark\modules\UserManagement\models\User as Userw;

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
    public $keyFile,$consoleLink;
    private $name, $token, $port_id, $windows_unique_id;


    private $create_errors=[
        1=>"There was an error connecting with the Openstack infrastructure.",
        2=>"There was an error creating a key-pair with the provided key.",
        3=>"There was an error creating the Virtual Machine.",
        4=>"There was an error connecting with the Openstack infrastructure.",
        5=>"There was an error creating an external IP address.",
        6=>"There was an error creating the additional storage space.",
        7=>"There was an error attaching the additional storage space to the VM.",

    ];

    private $delete_errors=[
        1=>"There was an error connecting with the Openstack infrastructure.",
        2=>"There was an error deleting the external IP address.",
        3=>"There was an error deleting the Virtual Machine.",
        4=>"There was an error deleting the key-pair.",
        5=>"There was an error deleting the additional storage space.",
        6=>"There was an error detaching the additional storage space from the VM.",

    ];
    /**
     * {@inheritdoc}
     */
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
        $token=self::authenticate();

        $client = new Client(['baseUrl' => 'https://ncc-louros.cloud.grnet.gr:8774/v2.1']);
        $response = $client->createRequest()
                            ->setMethod('GET')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$token])
                            ->setUrl(['flavors/detail'])
                            ->send();

        return $response->data['flavors'];
    }

    public function getOpenstackImages()
    {
        $token=self::authenticate();

        $client = new Client(['baseUrl' => 'https://glance-louros.cloud.grnet.gr:9292/v2']);
        $response = $client->createRequest()
                            ->setMethod('GET')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$token])
                            ->setUrl(['images'])
                            ->send();

        $dropdown=[];
        $isAdmin=Userw::hasRole('Admin',$superadminAllowed=false);
        foreach ($response->data['images'] as $image)
        {
            $id=$image['id'];
            $name=$image['name'];
            /*
             * Do not show windows image for people other than admins
             */
            // if ((!$isAdmin) && ($id=='189b93db-ab73-42fc-82b9-77be716f687e'))
            // {
            //     continue;
            // }

            $dropdown[$id]=$name;
        }

        return $dropdown;

    }

    public static function authenticate()
    {
        /*
         * Authenticate with the openstack api
         */
        $client = new Client(['baseUrl' => 'https://keystone-louros.cloud.grnet.gr:5000/v3']);
        $response = $client->createRequest()
                            ->setMethod('POST')
                            ->setFormat(Client::FORMAT_JSON)
                            ->setUrl('auth/tokens')
                            ->setData(Yii::$app->params['openstackAuth'])
                            ->send();

        if (!$response->getIsOk())
        {
            $token='';
        }
        
        $token=$response->headers['x-subject-token'];


        return $token;
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
        $client = new Client(['baseUrl' => 'https://ncc-louros.cloud.grnet.gr:8774/v2.1']);
        $response = $client->createRequest()
                            ->setMethod('POST')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$this->token])
                            ->setUrl('os-keypairs')
                            ->setData($keydata)
                            ->send();
        
        if (!$response->getIsOk())
        {
            return false;
        }

        return true;

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
        $client = new Client(['baseUrl' => 'https://cinder-louros.cloud.grnet.gr:8776/v3']);
        $response = $client->createRequest()
                            ->setMethod('POST')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$this->token])
                            ->setUrl(Yii::$app->params['openstackProjectID'] . '/volumes')
                            ->setData($volumedata)
                            ->send();
        
        if (!$response->getIsOk())
        {
            return false;
        }

        $this->volume_id=$response->data['volume']['id'];

        return true;

    }

    public function attachVolume()
    {
        /*
         * Check if volume is available
         */

        $client = new Client(['baseUrl' => 'https://cinder-louros.cloud.grnet.gr:8776/v3']);
        $response = $client->createRequest()
                            ->setMethod('GET')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$this->token])
                            ->setUrl(Yii::$app->params['openstackProjectID'] . '/volumes/' . $this->volume_id)
                            ->send();
        $volumeStatus=$response->data['volume']['status'];
        // print_r($volumeStatus);
        while($volumeStatus!='available')
        {
            sleep(10);

            $client = new Client(['baseUrl' => 'https://cinder-louros.cloud.grnet.gr:8776/v3']);
            $response = $client->createRequest()
                                ->setMethod('GET')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$this->token])
                                ->setUrl(Yii::$app->params['openstackProjectID'] . '/volumes/' . $this->volume_id)
                                ->send();
            $volumeStatus=$response->data['volume']['status'];
            // print_r($volumeStatus);

        }
        /*
         * Check if VM is ready
         */

        $client = new Client(['baseUrl' => 'https://ncc-louros.cloud.grnet.gr:8774/v2.1']);
        $response = $client->createRequest()
                            ->setMethod('GET')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$this->token])
                            ->setUrl('/servers/' . $this->vm_id)
                            ->send();
        $status=$response->data['server']['status'];

        while ($status!='ACTIVE')
        {
            sleep(10);
            $client = new Client(['baseUrl' => 'https://ncc-louros.cloud.grnet.gr:8774/v2.1']);
            $response = $client->createRequest()
                                ->setMethod('GET')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$this->token])
                                ->setUrl('/servers/' . $this->vm_id)
                                ->send();
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
        $client = new Client(['baseUrl' => 'https://ncc-louros.cloud.grnet.gr:8774/v2.1']);
        $response = $client->createRequest()
                            ->setMethod('POST')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$this->token])
                            ->setUrl('/servers/' . $this->vm_id . '/os-volume_attachments' )
                            ->setData($volumedata)
                            ->send();
        // print_r($response);
        // exit(0);
        if (!$response->getIsOk())
        {
            return false;
        }

        return true;

    }

    public function detachVolume()
    {
        /*
         * Add a new ssh key
         */

        $client = new Client(['baseUrl' => 'https://ncc-louros.cloud.grnet.gr:8774/v2.1']);
        $response = $client->createRequest()
                            ->setMethod('DELETE')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$this->token])
                            ->setUrl('/servers/' . $this->vm_id . '/os-volume_attachments/' . $this->volume_id)
                            ->send();
        
        if (!$response->getIsOk())
        {
            return false;
        }

        return true;

    }

    public function deleteVolume()
    {
        /*
         * Add a new ssh key
         */

        $client = new Client(['baseUrl' => 'https://cinder-louros.cloud.grnet.gr:8776/v3']);
        $response = $client->createRequest()
                            ->setMethod('DELETE')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$this->token])
                            ->setUrl(Yii::$app->params['openstackProjectID'] . '/volumes/' . $this->volume_id)
                            ->send();
        
        if (!$response->getIsOk())
        {
            return false;
        }

        return true;

    }


    public function deleteKey($key_name)
    {
        $client = new Client(['baseUrl' => 'https://ncc-louros.cloud.grnet.gr:8774/v2.1']);
        $response = $client->createRequest()
                            ->setMethod('DELETE')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$this->token])
                            ->setUrl(["os-keypairs/$key_name"])
                            ->send();
        if (!$response->getIsOk())
        {
            return false;
        }

        return true;
    }

    public function createServer($flavour)
    {
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
                    ['uuid'=>'e50472dd-3cd0-4165-b171-f6aec3aa452f'],
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
            ]
        ];
        $client = new Client(['baseUrl' => 'https://ncc-louros.cloud.grnet.gr:8774/v2.1']);
        $response = $client->createRequest()
                            ->setMethod('POST')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$this->token])
                            ->setUrl('servers')
                            ->setData($vmdata)
                            ->send();
        if (!$response->getIsOk())
        {
            // print_r($response);
            // exit(0);
            return false;
        }

        $this->vm_id=$response->data['server']['id'];

        return true;
    }

    public function getServerPort()
    {
        $client = new Client(['baseUrl' => 'https://ncc-louros.cloud.grnet.gr:8774/v2.1']);
        $response = $client->createRequest()
                            ->setMethod('GET')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$this->token])
                            ->setUrl(["servers/$this->vm_id/os-interface"])
                            ->send();
        
        if (!$response->getIsOk())
        {
            return false;
        }
        // print_r($response->data);
        // exit(0);
        
        $this->port_id=$response->data['interfaceAttachments'][0]['port_id'];

        return true;
    }

    public function deleteServer()
    {
        $client = new Client(['baseUrl' => 'https://ncc-louros.cloud.grnet.gr:8774/v2.1']);
        $response = $client->createRequest()
                            ->setMethod('DELETE')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$this->token])
                            ->setUrl(["servers/$this->vm_id"])
                            ->send();

        if (!$response->getIsOk())
        {
            return false;
        }

        return true;
    }

    public function createIP()
    {
        $ipdata=
        [
            "floatingip"=>
            [
                "project_id" => Yii::$app->params['openstackProjectID'],
                "floating_network_id" =>  Yii::$app->params['openstackFloatNetID'],
                "port_id" => $this->port_id,
                "description" => $this->name
            ]
        ];

        $client = new Client(['baseUrl' => 'https://net-louros.cloud.grnet.gr:9696']);
        $response = $client->createRequest()
                            ->setMethod('POST')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$this->token])
                            ->setUrl('v2.0/floatingips')
                            ->setData($ipdata)
                            ->send();
        if (!$response->getIsOk())
        {
            return false;
        }

        $this->ip_id=$response->data['floatingip']['id'];
        $this->ip_address=$response->data['floatingip']['floating_ip_address'];

        return true;
    }

    public function deleteIP()
    {
        $client = new Client(['baseUrl' => 'https://net-louros.cloud.grnet.gr:9696']);
        $response = $client->createRequest()
                            ->setMethod('DELETE')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$this->token])
                            ->setUrl(["v2.0/floatingips/$this->ip_id"])
                            ->send();

        if (!$response->getIsOk())
        {
            return false;
        }

        return true;

    }


    public static function getOpenstackAvailableResources()
    {
        $token=self::authenticate();

        $client = new Client(['baseUrl' => 'https://ncc-louros.cloud.grnet.gr:8774/v2.1']);
        $responseOK=false;
            while(!$responseOK)
        {
                    $response = $client->createRequest()
                                        ->setMethod('GET')
                                        ->setFormat(Client::FORMAT_JSON)
                                        ->addHeaders(['X-Auth-Token'=>$token])
                                        ->setUrl(['limits'])
                                        ->send();



                    // print_r($response);
                    // exit(0);
                    if (($response->getIsOk()) && (isset($response->data['limits'])))
                    {
                        $responseOK=true;
                    }
                    // print_r($response);
                    // exit(0);
                    sleep(1);
                    
        }
        $results=$response->data['limits']['absolute'];

        // print_r($results);
        // exit(0);

        $cpu=$results['maxTotalCores']-$results['totalCoresUsed'];
        $ram=$results['maxTotalRAMSize']-$results['totalRAMUsed'];
        $ram/=1024;


        $client = new Client(['baseUrl' => 'https://net-louros.cloud.grnet.gr:9696']);
        $response = $client->createRequest()
                            ->setMethod('GET')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$token])
                            ->setUrl(['v2.0/floatingips'])
                            ->setData(['floating_network_id'=>'fa87edbd-b40c-4144-b317-5838aaf440db'])
                            ->send();
        // print_r($response->data);
        // exit(0);
        $ipRes=$response->data['floatingips'];
        // print_r($ipRes);
        // exit(0);
        if (empty($ipRes))
        {
            $usedIps=0;
        }
        else
        {
            $usedIps=count($ipRes);
        }
        
        // print_r($token);
        $client = new Client(['baseUrl' => 'https://net-louros.cloud.grnet.gr:9696']);
        $response = $client->createRequest()
                            ->setMethod('GET')
                            ->setFormat(Client::FORMAT_JSON)
                            ->addHeaders(['X-Auth-Token'=>$token])
                            ->setUrl(['v2.0/quotas/8119419d2b294b5596c17a310f229fb9'])
                            ->send();


        $floatingIps=$response->data['quota']['floatingip'];
        // print_r($floatingIps);
        // exit(0);
        // $ips=$results['floatingip']-$usedIps;
        $ips=$floatingIps-$usedIps;
        // print_r($ips);
        // exit(0);

        return [$cpu,$ram,$ips];
        
    }

    public function createVM($requestId,$service,$images)
    {   
        $keyFileName='/data/docker/tmp-keys/' . $this->keyFile->baseName . '.' . $this->keyFile->extension;
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
        $project=ProjectRequest::find()->where(['id'=>$requestId])->one();
        $this->image_name=$images[$this->image_id];

        $this->name=$project->name;
        // $this->name=str_replace(',', '', $this->name);
        // $this->name = preg_replace('/\s+/', '-', $this->name);
        // if (strlen($this->name)>40)
        // {
        //  $this->name=substr($this->name,0,40);
        // }
        // print_r($this->name);
        // exit(0);
        
        $flavour=$service->vm_flavour;

        /*
         * Get authentication token from the openstack api
         */

        $this->token=self::authenticate();

        if (empty($this->token))
        {
            return [1,$this->create_errors[2]];
        }

        /*
         * Add a new ssh key
         */
        $keyCreated=$this->createKey();
        
        if (!$keyCreated)
        {
            return [2,$this->create_errors[2]];
        }

        /*
         * Create VM
         */
        
        $serverCreated=$this->createServer($flavour);
        if (!$serverCreated)
        {
            $this->deleteKey($this->name);
            return [3,$this->create_errors[3]];
        }


        /*
         * Get server port but sleep for 15 seconds to ensure that the VM is up and running
         */
        sleep(15);
        $portRetrieved=$this->getServerPort();

        if (!$portRetrieved)
        {
            $this->deleteServer();
            $this->deleteKey($this->name);
            return [4,$this->create_errors[4]];
        }

        /*
         * Create floating ip
         */

        $ipCreated=$this->createIP();
        if (!$ipCreated)
        {
            $this->deleteServer();
            $this->deleteKey($this->name);
            return [5,$this->create_errors[5]];
        }

        if ($service->storage>0)
        {

            $volumeCreated=$this->createVolume($service->storage);
            // exit(0);
            if (!$volumeCreated)
            {
                $this->deleteIP();
                $this->deleteServer();
                $this->deleteKey($this->name);
                return [6,$this->create_errors[6]];
            }

            // sleep(15);

            $volumeAttached=$this->attachVolume();
            if (!$volumeAttached)
            {
                $this->deleteVolume();
                $this->deleteIP();
                $this->deleteServer();
                $this->deleteKey($this->name);
                return [7,$this->create_errors[7]];
            }
            
        }
        else
        {
            $this->volume_id='';
        }
        
        Yii::$app->db->createCommand()->insert('vm', [

                'request_id' => $requestId,
                'ip_address' => $this->ip_address,
                'ip_id' => $this->ip_id,
                'vm_id' => $this->vm_id,
                'public_key' => $this->public_key,
                'image_id'=> $this->image_id,
                'image_name' => $this->image_name,
                'active' => true,
                'keypair_name'=> $this->name,
                'created_by'=> $user,
                'volume_id'=> $this->volume_id,
                'created_at'=>'NOW()',
                'windows_unique_id' => $this->windows_unique_id,
            ])->execute();

        return [0,''];



    }

    public function deleteVM()
    {
        $vmid=$this->id;
        $user=Userw::getCurrentUser()['id'];
        Yii::$app->db->createCommand()
                     ->update('vm',['deleted_by'=>$user,], "id=$this->id")
                     ->execute();

        $this->token=self::authenticate();

        if (empty($this->token))
        {
            return [1,$this->$delete_errors[1]];
        }

        if (!empty($this->volume_id))
        {   
            $volumeDetached=$this->detachVolume();
            if (!$volumeDetached)
            {
                return [6,$this->delete_errors[6]];
            }
            if (!$this->do_not_delete_disk)
            {
                sleep(15);
            
                $volumeDeleted=$this->deleteVolume();
                if (!$volumeDeleted)
                {
                    return [5,$this->$delete_errors[5]];
                }
            }
            

        }

        $ipDeleted=$this->deleteIP();

        if (!$ipDeleted)
        {
            return [2,$this->$delete_errors[2]];
        }


        $serverDeleted=$this->deleteServer();

        if (!$serverDeleted)
        {
            return [3,$this->$delete_errors[3]];
        }

        $keyDeleted=$this->deleteKey($this->keypair_name);
        if (!$keyDeleted)
        {
            return [4,$this->$delete_errors[4]];
        }

        Yii::$app->db->createCommand()
                     ->update('vm',['active'=>false,'deleted_at'=>'NOW()'], "id=$this->id")
                     ->execute();
        return [0,''];
    }

    public function retrieveWinPassword()
    {
        $token=self::authenticate();
        
        $passNotExists=true;
        $encrypted='';
        while ($passNotExists || empty($encrypted))
        {
            $client = new Client(['baseUrl' => 'https://ncc-louros.cloud.grnet.gr:8774/v2.1']);
            $response = $client->createRequest()
                                ->setMethod('GET')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$token])
                                ->setUrl('/servers/' . $this->vm_id . '/os-server-password')
                                ->send();

        

            if ($response->getIsOk())
            {
                $passNotExists=false;
                $encrypted=$response->data['password'];

            }
            sleep(5);

        }
        // print_r($response->data);
        // exit(0);
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
        $token=self::authenticate();

        $consoleData=
        [
            "os-getVNCConsole"=>
            [
                "type"=>"novnc"
            ]
        ];
        $consoleAvailable=false;
        while (!$consoleAvailable)
        {
            $client = new Client(['baseUrl' => 'https://ncc-louros.cloud.grnet.gr:8774/v2.1/']);
            $response = $client->createRequest()
                                ->setMethod('POST')
                                ->setFormat(Client::FORMAT_JSON)
                                ->addHeaders(['X-Auth-Token'=>$token])
                                ->addHeaders(['X-OpenStack-Nova-API-Version'=> '2.1'])
                                ->setUrl('/servers/' . $this->vm_id . '/action')
                                ->setData($consoleData)
                                ->send();
            
            if ($response->getIsOk() && (isset($response->data['console'])))
            {
                $consoleAvailable=true;
            }
            sleep(2);
        }
        $console=$response->data['console'];
        $this->consoleLink=$console['url'];
    }
}
