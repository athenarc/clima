<?php

namespace app\models;

use Yii;
use app\models\Softare;
use app\models\JupyterImages;
use webvimark\modules\UserManagement\models\User;
use yii\helpers\Html;
use yii\httpclient\Client;

/**
 * This is the model class for table "jupyter_server".
 *
 * @property int $id
 * @property string|null $manifest
 * @property string|null $project
 * @property string|null $server_id
 * @property string|null $created_at
 * @property string|null $deleted_at
 * @property string|null $created_by
 * @property string|null $deleted_by
 * @property string|null $project_end_date
 * @property string|null $url
 * @property bool|null $active
 */
class JupyterServer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $cpu,$memory,$password;

    public static function tableName()
    {
        return 'jupyter_server';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'deleted_at', 'project_end_date'], 'safe'],
            [['created_by', 'deleted_by', 'url', 'image'], 'string'],
            [['active'], 'boolean'],
            [['manifest', 'project'], 'string', 'max' => 100],
            [['server_id'], 'string', 'max' => 20],
            [['password', 'image_id'],'required']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'manifest' => 'Manifest',
            'project' => 'Project',
            'server_id' => 'Server ID',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'created_by' => 'Created By',
            'deleted_by' => 'Deleted By',
            'project_end_date' => 'Project End Date',
            'url' => 'Url',
            'active' => 'Active',
            'password' => "Password for the Jupyter server",
            'image' => "Please select an image:",
        ];
    }

    // public static function getActiveProjects()
    // {
    //     $username=User::getCurrentUser()['username']; 
    //     $client = new Client();
    //     $response = $client->createRequest()
    //             ->setMethod('GET')
    //             ->setUrl(Yii::$app->params['egciActiveQuotas'] . "&username=$username")
    //             ->send();

    //     $apiProjects=$response->data;
    //     $projects=[];

    //     foreach ($apiProjects as $project)
    //     {
    //         $projects[$project['name']]=['cpu'=>$project['cores'], 'memory'=>$project['ram'], 'expires'=>$project['end_date']];
    //     }

    //     return $projects;

    // }


    public static function matchServersWithProjects($projects)
    {
        $username=User::getCurrentUser()['username'];

        $servers=JupyterServer::find()->where(['active'=>true, 'created_by'=>$username])->all();

        foreach ($servers as $server)
        {
            /*
             * Servers are identified by user and by project.
             * Users are allowed to create one server per project, so 
             * each project can have multiple servers.
             */
            if (isset($projects[$server->project]))
            {
                $projects[$server->project]['server']=$server;
                if ($server->state=='spawning')
                {
                    try
                    {
                        $client = new Client();
                        $response = $client->createRequest()
                                ->setMethod('GET')
                                ->setUrl($server->url)
                                ->send();
                        if ($response->getIsOk())
                        {
                            $server->state='running';
                            $server->save(false);
                        }

                    }
                    catch (\Exception $e)
                    {

                    }
                }
            }
        }

        return $projects;
    }

    // public static function getProjectQuotas($project)
    // {
    //     $username=User::getCurrentUser()['username']; 
    //     $url=Yii::$app->params['egciSingleProjecteQuotas'] . "&username=$username&project=$project";
        
    //     $client = new Client();
    //     $response = $client->createRequest()
    //             ->setMethod('GET')
    //             ->setUrl($url)
    //             ->send();
    //     $quotas=$response->data;

    //     if (!empty($quotas))
    //     {
    //         $quotas=$quotas[0];
    //     }
    //     return $quotas;

    // }

    public function startServer()
    {
        $server=JupyterServer::find()->where(['active'=>true,'project'=>$this->project, 'created_by'=> User::getCurrentUser()['username']])->one();
        if (!empty($server))
        {
            $success = '';
            $error = 'You already have an active server';
            return [$success, $error];
        } 
        $ssid=uniqid();
        $sid = 'a'.$ssid;
        $username=User::getCurrentUser()['username'];
        $user=explode('@',$username)[0];

        $image=JupyterImages::find()->where(['id'=>$this->image_id])->one();
        if (empty($image))
        {
            $error='Image not found. Please try again or contact an administrator';
            return ['',$error];
        }

        // $data=[];
        // if (file_exists('/data/containerized'))
        // {
        //     $nfs="container";
        // }
        // else
        // {
        //     $nfs=Yii::$app->params['nfsIp'];
        // }

        // $data['nfs']=$nfs;
        // $data['image']=$image->image;
        // $data['image_id']=$this->image_id;
        // $data['gpu']=$image->gpu;
        // $data['id']=$sid;
        // $data['folder']=Yii::$app->params['tmpFolderPath'] . '/' . $sid . '/';
        // $data['mountFolder']=Yii::$app->params['userDataPath'] . $user . '/';
        // $data['password']=$this->password;
        // $data['expires']=$this->expires_on;
        // $data['project']=$this->project;
        // $data['user']=$username;
        // $data['resources']=['cpu'=>$this->cpu,'mem'=>$this->memory];

        // $file=$data['folder'] . $sid . '.json';

        $tmpf=Yii::$app->params['tmpFolderPath'];
        if (!is_dir($tmpf))
        {
            JupyterServer::exec_log("mkdir $tmpf");
            JupyterServer::exec_log("chmod 777 $tmpf");
        }

        $folder=Yii::$app->params['tmpFolderPath'] . '/' . $sid . '/';

        $file_deploy=$folder . $sid . '-deployment.json';
        $file_service=$folder. $sid . '-service.json';
        $file_ingress=$folder . $sid . '-ingress.json';

        $salt_t = uniqid();
        $salt = substr($salt_t, 0, -1);
        $pass = $this->password . $salt;
        $password_hash=hash('SHA256', $pass);

        $deployment = [];
        $deployment = array(
            'metadata' => array(
                'name' => $sid,
                'labels' => array(
                    'name'=> $sid
                )
            ),
            'spec' => array(
                'replicas' => 1,
                'template' => array(
                    'metadata' => array(
                        'name' => $sid,
                        'labels' => array(
                            'name' => $sid
                        )
                    ),
                    'spec' => array(
                        'containers' => array(
                            array(
                                'name' => 'jupyter',
                                'image' => $image->image,
                                'command' => array (
                                    "start-notebook.sh",
                                    "--NotebookApp.password=". "'sha256:" . $salt . ":" .$password_hash."'",
                                    "--NotebookApp.notebook_dir='/home/jovyan/work"
                                ),
                                'resources' => array(
                                    'requests' => array (
                                        'memory' => $this->memory . "Gi",
                                        'cpu' => $this->cpu*1000 . "m"
                                    ),
                                    'limits' => array (
                                        'memory' => $this->memory . "Gi",
                                        'cpu' => $this->cpu*1000 . "m"
                                    )
                                ),
                                'env' => array(
                                    array(
                                        'name' => 'JUPYTER_ENABLE_LAB',
                                        'value' => 'yes'
                                    )
                                ),
                                'ports' => array(
                                    array('containerPort' => 8888)
                                ), 
                                'volumeMounts' => array(
                                    array('mountPath'=>'/home/jovyan/work',
                                            'name'=>$sid
                                    )
                                )
                            )
                        ),
                        'volumes'=>array(
                            array('name'=>$sid,
                            'nfs'=>array(
                                'path'=>Yii::$app->params['userDataPath'].'/'.explode('@',User::getCurrentUser()['username'])[0].'/',
                                'server'=>Yii::$app->params['nfsIp']
                            ))
                        )
                    )
                ),
                    'selector' => array (
                        'matchLabels' => array(
                            'name' => $sid
                        )
                    )
            )      
        );
        
        // $json_data=json_encode($data);
        $json_data_deploy=json_encode($deployment);

        $headers = [
            Yii::$app->params['jupyter_bearer_token'],
            'Content-Type: application/json'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Yii::$app->params['jupyter_deployments_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data_deploy);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $out2 = curl_exec($ch);

        if(curl_errno($ch)){
            $out4=curl_error($ch);
        }
        curl_close($ch);

        //check if the previous deployment is up

        $ch = curl_init();
        sleep(10);
        curl_setopt($ch, CURLOPT_URL, Yii::$app->params['jupyter_deployments_url'] .'/'.$sid);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $count=0;
        $out10 = curl_exec($ch);
        curl_close($ch);
        $deploym = json_decode($out10, true);
        $condition2=$deploym['status']['conditions'][0]['status'];
        $condition1=$deploym['status']['conditions'][1]['status'];
        while ($condition1=='True' && $condition2=='False') {
        //while ($count<60 && $condition=='False') {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, Yii::$app->params['jupyter_deployments_url'] .'/'.$sid);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $out10 = curl_exec($ch);
            $deploym = json_decode($out10, true);
            $condition2=$deploym['status']['conditions'][0]['status'];
            $condition1=$deploym['status']['conditions'][1]['status'];
            curl_close($ch);
            $count = $count+1;
            sleep(5);
        }


        if ($deploym['status']['conditions'][0]['status']=='False'){

            $error = 'Your request could not be fulfilled at the current time due to insufficient resources at the cluster:  '. $deploym['status']['conditions'][0]['message'];
            return ['',$error,''];

        } else{

            $service = [];
            $service = array (
                'metadata' => array (
                    'name' => $sid
                ),
                'spec' => array (
                    'selector' => array (
                        'name' => $sid
                    ),
                    'ports' => array(
                        array(
                            'protocol'=> 'TCP',
                            'port' => 80,
                            'targetPort' => 8888
                        )
                    ),
                    'type' => 'ClusterIP'
                )
            );

            $json_data_service=json_encode($service);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, Yii::$app->params['jupyter_services_url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data_service);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $out5 = curl_exec($ch);

            if(curl_errno($ch)){
                $out5=curl_error($ch);
            }
            curl_close($ch);

            $ingress = [];
            $ingress = array (
                'metadata' => array(
                    'name' => $sid,
                    'annotations' => array(
                        'kubernetes.io/ingress.class' => 'nginx',
                        'cert-manager.io/cluster-issuer' => 'letsencrypt-prod',
                        'kubernetes.io/tls-acme' => 'true'
                    )
                ),
                'spec' => array (
                    'rules' => array (
                        array (
                            'host' => $sid . '.jupyter.'.Yii::$app->params['schema_domain'],
                            'http' => array (
                                'paths' => array (
                                    array (
                                        'path' => '/',
                                        'pathType' => 'Prefix',
                                        'backend' => array (
                                            'service' => array (
                                                'name' => $sid,
                                                'port' => array (
                                                    'number' => 80
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    ),
                    'tls' => array(
                        array(
                            'hosts'=>array($sid . '.jupyter.'.Yii::$app->params['schema_domain']),
                            'secretName' => $sid.'-ingress-secret'
                        )
                    )
                )
            );

            $json_data_ingress=json_encode($ingress);
            // $out1=exec("mkdir " . $data['folder']);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, Yii::$app->params['jupyter_ingresses_url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data_ingress);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $out6 = curl_exec($ch);

            if(curl_errno($ch)){
                $out6=curl_error($ch);
            }
            curl_close($ch);

            mkdir ($folder, 0777, true);
            
            exec ("chmod 777 -R " . $folder);
            // file_put_contents($file, $json_data);
            file_put_contents($file_deploy, $json_data_deploy);
            file_put_contents($file_service, $json_data_service);
            file_put_contents($file_ingress, $json_data_ingress);

            // $command=Yii::$app->params['scriptsFolder'] . "jupyterServerStart.py " . self::enclose($file) . ' 2>&1' ;
            // //print_r($command);exit(0);
            // //$command=JupyterServer::sudoWrap($command);
            // $command;
            // exec($command,$out,$ret);
            // $out3 = $sid;
            // $out1 = $out3 . '  ' . $out6;
            // $success='';
            // $error='';
            // $status='';
            // session_write_close();

            $manifest = Yii::$app->params['tmpFolderPath'] . $sid  ;
            $url = 'https://'.$sid.'.jupyter.'.Yii::$app->params['schema_domain'];

            //insert to jupyterserver

            Yii::$app->db->createCommand()->insert('jupyter_server', [

                'manifest' => $manifest,
                'image' => $image->image,
                'project' => $this->project,
                'server_id' => $sid,
                'created_at'=>date('Y-m-d H:i:s'),
                'created_by' => $username,
                'url' => $url,
                'active' => 't',
                'expires_on' => $this->expires_on,
                'state' => 'spawning',
                'image_id'=> $this->image_id
            ])->execute();


            $success = 'Server was started successfully! It can be accessed <a href="' . $url . '" target="blank">here</a>.';
            return [$success,'',''];
        }
       
    }

    public function stopServer()
    {
        $username=User::getCurrentUser()['username'];
        $error_c = 0;

        $headers = [
            Yii::$app->params['jupyter_bearer_token'],
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Yii::$app->params['jupyter_ingresses_url'] . '/' .  $this->server_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $out1 = curl_exec($ch);
        $out4 = ' ';
        if(curl_errno($ch)){
            $out4=curl_error($ch);
            $error_c=1;
        }
        curl_close($ch);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Yii::$app->params['jupyter_services_url'] . '/' . $this->server_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $out2 = curl_exec($ch);
        $out5 = ' ';
        if(curl_errno($ch)){
            $out5=curl_error($ch);
            $error_c=1;
        }
        curl_close($ch);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Yii::$app->params['jupyter_deployments_url'] . '/' . $this->server_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $out3 = curl_exec($ch);
        $out6 = ' ';
        if(curl_errno($ch)){
            $out6=curl_error($ch);
            $error_c=1;
        }
        curl_close($ch);

        // $command=Yii::$app->params['scriptsFolder'] . "jupyterServerStop.py " . self::enclose($this->server_id) . ' '. self::enclose($username) . ' 2>&1' ;


        // exec($command,$out,$ret);

        Yii::$app->db->createCommand()->update('jupyter_server', 
        ['active'=>false, 
        'deleted_by'=>$username,
        'deleted_at'=>date('Y-m-d H:i:s')], 
        'server_id = '."'".$this->server_id."'")
        ->execute();


        $success='';
        $error='';
        if ($error_c==0)
        {
            $success='Server was deleted successfully!';
        }
        else
        {
            $error='There was an error stopping the Jupyter server. Please contact an administrator and report the following: <br />' . $out4 . '<br />' . $out5 . '<br />' . $out6;
        }
        

        return [$success,$error];
    }

    public static function enclose($s)
    {
        return "'" . $s . "'";
    }

    public static function exec_log($command, array &$out=null, int &$ret=null)
    {
        exec($command,$out,$ret);
        if ($ret != 0) {
            error_log("ERROR (".$ret."): While running '".$command."'");
            error_log(implode(" ", $out));
        }
    }

    public static function sudoWrap($command)
    {
        if (file_exists('/data/containerized'))
        {
            return $command;
        }
        else
        {
            return "sudo -u ". Yii::$app->params['systemUser'] . " " . $command;
        }
    }


}
