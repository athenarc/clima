<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Project;
use app\models\ProjectRequest;
use app\models\ProjectRequestCold;
use app\models\ServiceRequest;
use app\models\MachineComputeRequest;
use app\models\MachineComputeLimits;
use app\models\ColdStorageLimits;
use app\models\ColdStorageAutoaccept;
use app\models\OndemandRequest;
use app\models\OndemandLimits;
use app\models\OndemandAutoaccept;
use app\models\ServiceLimits;
use app\models\ServiceAutoaccept;
use app\models\ColdStorageRequest;
use app\models\Notification;
use app\models\Configuration;
use app\models\User;
use app\models\Vm;
use app\models\VmMachines;
use yii\db\Query;
use app\models\Smtp;
use app\models\EmailEvents;
use app\models\Email;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\UploadedFile;
use app\models\HotVolumes;
use webvimark\modules\UserManagement\models\User as Userw;



class ProjectController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $freeAccess = false;
    public function behaviors()
    {
        return [
            'ghost-access'=> [
                'class' => 'webvimark\modules\UserManagement\components\GhostAccessControl',
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {

        $configuration=Configuration::find()->one();
        $schema_url=$configuration->schema_url;

        // $role=User::getRoleType();
        // $service_autoaccept= ServiceAutoaccept::find()->where(['user_type'=>$role])->one();
        // $service_autoaccept_number=$service_autoaccept->autoaccept_number;
        // $autoaccepted_num=ProjectRequest::find()->where(['status'=>2,'project_type'=>1,'submitted_by'=>Userw::getCurrentUser()['id'], ])->andWhere(['>=','end_date', date("Y-m-d")])->count();
        // print_r($service_autoaccept_number);
        // exit(0);

        $project_types=Project::TYPES;
        $button_links=[0=>'/project/view-ondemand-request-user', 1=>'/project/view-service-request-user', 
                    2=>'/project/view-cold-storage-request-user', 3=>'/project/view-machine-computation-request-user'];

       	$deleted=Project::getDeletedProjects();
        $owner=Project::getActiveProjectsOwner();
        $participant=Project::getActiveProjectsParticipant();
        $expired_owner=Project::getExpiredProjects();
        $role=User::getRoleType();
    
        $username=Userw::getCurrentUser()['username'];
        $user_split=explode('@',$username)[0];
		$all_projects=array_merge($owner,$participant);
		
        $active=[];
        $expired=[];
      
        foreach ($all_projects as $project) 
        {
           	$now = strtotime(date("Y-m-d"));
            $end_project = strtotime($project['end_date']);
            $remaining_secs=$end_project-$now;
            $remaining_days=$remaining_secs/86400;
            $remaining_months=round($remaining_days/30);
            if($username==$project['username'])
            {
                array_push($project,'<b>You</b>' );
                array_push($project, $remaining_days);
                array_push($project, ['favorite'=>$project['favorite']]);
            }
            else
           	{
                array_push($project, "$project[username]");
                array_push($project, $remaining_days);
                array_push($project, ['favorite'=>$project['favorite']]);
             }
                $active[]=$project;
        }

        $favorite = array_column($active, 'favorite');
        $submission_date = array_column($active, 'submission_date');
        array_multisort($favorite, SORT_DESC, $submission_date, SORT_DESC, $active);
        
        

        foreach ($expired_owner as $project) 
        {
           	$now = strtotime(date("Y-m-d"));
            $end_project = strtotime($project['end_date']);
            $remaining_secs=$end_project-$now;
            $remaining_days=$remaining_secs/86400;
            $remaining_months=round($remaining_days/30);
	        if($username==$project['username'])
            {
                array_push($project,'<b>You</b>');
                array_push($project, $project['end_date']);
            }
            else
            {
                array_push($project, "$project[username]");
                array_push($project, $project['end_date']);
            }
			$expired[]=$project;
		}


        $number_of_active=count($active);
        $number_of_expired=count($expired);
        
        
       
        return $this->render('index',['owner'=>$owner,'participant'=>$participant,
            'button_links'=>$button_links,'project_types'=>$project_types,'role'=>$role,
            'deleted'=>$deleted,'expired'=>$expired, 'active'=>$active, 'number_of_active'=>$number_of_active, 'number_of_expired'=>$number_of_expired, 'schema_url'=>$schema_url]);

    }

    public function actionMakeFavorite($project_id)
    {
        $project=Project::find()->where(['id'=>$project_id])->one();
        $project->favorite=true;
        $project->update();
        return $this->redirect(['project/index']);
    }

    public function actionRemoveFavorite($project_id)
    {
        $project=Project::find()->where(['id'=>$project_id])->one();
        $project->favorite=false;
        $project->update();
        return $this->redirect(['project/index']);
    }

    public function actionNewRequest()
    {

        return $this->render('new_request');
    }

    



    public function actionNewServiceRequest()
    {
        $role=User::getRoleType();
        $service_limits= ServiceLimits::find()->where(['user_type'=>$role])->one();
        $service_maximum_number=$service_limits->number_of_projects;
        $number_of_user_projects=ProjectRequest::find()->where(['status'=>[1,2],'project_type'=>1,'submitted_by'=>Userw::getCurrentUser()['id'],])->andWhere(['>=','end_date', date("Y-m-d")])->count();
        $new_project_allowed=($number_of_user_projects-$service_maximum_number < 0) ? true :false;

        if((!$new_project_allowed) && (!Userw::hasRole('Admin', $superadminAllowed=true)) && (!Userw::hasRole('Moderator', $superadminAllowed=true)))
        {
            return $this->render('no_project_allowed', ['project'=>"24/7 service", 'user_type'=>$role]);
        }

        $serviceModel=new ServiceRequest;
        $projectModel=new ProjectRequest;
        $limitsModel=new ServiceLimits;
        $autoacceptModel=new ServiceAutoaccept;

        $service_autoaccept= ServiceAutoaccept::find()->where(['user_type'=>$role])->one();
        $service_autoaccept_number=$service_autoaccept->autoaccept_number;

        $autoaccepted_num=ProjectRequest::find()->where(['status'=>2,'project_type'=>1,'submitted_by'=>Userw::getCurrentUser()['id'], ])->andWhere(['>=','end_date', date("Y-m-d")])->count();
        
        $autoaccept_allowed=($autoaccepted_num - $service_autoaccept_number < 0) ? true :false; 

        
        
        $upperlimits=$limitsModel::find()->where(['user_type'=>$role])->one();
        
        $autoacceptlimits=$autoacceptModel::find()->where(['user_type'=>$role])->one();

        $project_types=['service'=>1, 'ondemand'=>0, 'coldstorage'=>2];

        $form_params =
        [
            'action' => URL::to(['project/new-service-request']),
            'options' => 
            [
                'class' => 'service_request_form',
                'id'=> "service_request_form"
            ],
            'method' => 'POST'
        ];

        $trls=[];
        $trls[0]='Unspecified';
        for ($i=1; $i<10; $i++)
        {
            $trls[$i]='Level ' . $i;
        }

        $errors='';
        $success='';
        $warnings='';
        $username=Userw::getCurrentUser()['username'];
        $user_split=explode('@',$username)[0];
        $participating= (isset($_POST['participating'])) ? $_POST['participating'] : [ $user_split ];
        
        if ( ($serviceModel->load(Yii::$app->request->post())) && ($projectModel->load(Yii::$app->request->post())) )
        {
            // print_r($serviceModel);
            // exit(0);
            $isValid = $projectModel->validate();
            $isValid = $serviceModel->validate() && $isValid;

            if ($isValid)
            {   
                $participant_ids_tmp=[];
                foreach ($participating as $participant)
                {
                    // $name_exp=explode(' ',$participant);
                    // $name=$name_exp[0];
                    // $surname=$name_exp[1];
                    $username=$participant . '@elixir-europe.org';
                    $pid=User::findByUsername($username)->id;
                    $participant_ids_tmp[$pid]=null;
                }

                $participant_ids=[];
                foreach ($participant_ids_tmp as $pid => $dummy)
                {
                    $participant_ids[]=$pid;
                }

                $projectModel->user_list=$participant_ids;
                $messages=$projectModel->uploadNew($participating,$project_types['service']);
                $errors.=$messages[0];
                $success.=$messages[1];
                $warnings.=$messages[2];
                $requestId=$messages[3];
                $submitted_email=$messages[4];
                $project_id=$messages[5];

                if ($requestId!=-1)
                {
                    $messages=$serviceModel->uploadNew($requestId);
                    $errors.=$messages[0];
                    $success.=$messages[1];
                    $warnings.=$messages[2];
                    $message_autoaccept=$messages[3];
                }

                if (empty($errors))
                {
                    if(!empty($success))
                    {
                        Yii::$app->session->setFlash('success', "$success");
                    }
                    if(!empty($warnings))
                    {
                        Yii::$app->session->setFlash('warning', "$warnings");
                    }

                    if(empty($message_autoaccept))
                    {
                    	EmailEvents::NotifyByEmail('new_project', $project_id,$submitted_email);
                    }
                    else
                    {
                    	EmailEvents::NotifyByEmail('project_decision', $project_id,$message_autoaccept);
                    }
                    
                    return $this->redirect(['project/index']);
                }
            }
        }
        

        return $this->render('new_service_request',['service'=>$serviceModel, 'project'=>$projectModel, 
                    'trls'=>$trls, 'form_params'=>$form_params, 'participating'=>$participating, 'errors'=>$errors, 'upperlimits'=>$upperlimits, 'autoacceptlimits'=>$autoacceptlimits,'autoaccept_allowed' => $autoaccept_allowed, 'role'=>$role, 'new_project_allowed'=>$new_project_allowed]);



    }

    public function actionNewMachineComputeRequest()
    {
        
       
        $role=User::getRoleType();
        $machineLimits=MachineComputeLimits::find()->where(['user_type'=>$role])->one();
        $machine_maximum_number=$machineLimits->number_of_projects;
        $number_of_user_projects=ProjectRequest::find()->where(['status'=>1,'project_type'=>3,'submitted_by'=>Userw::getCurrentUser()['id'],])->andWhere(['>=','end_date', date("Y-m-d")])->count();
        $new_project_allowed=($number_of_user_projects-$machine_maximum_number < 0) ? true :false;
        $new_project_allowed=($machine_maximum_number==-1)? true : false;

        if((!$new_project_allowed) && (!Userw::hasRole('Admin', $superadminAllowed=true)) && (!Userw::hasRole('Moderator', $superadminAllowed=true)))
        {
            return $this->render('no_project_allowed', ['project'=>"On-demand computation machines", 'user_type'=>$role]);
        }

        $serviceModel=new MachineComputeRequest;
        $projectModel=new ProjectRequest;

        $project_types=['service'=>1, 'ondemand'=>0, 'coldstorage'=>2, 'machine_compute'=>3];

        $form_params =
        [
            'action' => URL::to(['project/new-machine-compute-request']),
            'options' => 
            [
                'class' => 'machine_compute_request_form',
                'id'=> "machine_compute_request_form"
            ],
            'method' => 'POST'
        ];


        $errors='';
        $success='';
        $warnings='';
        $username=Userw::getCurrentUser()['username'];
        $user_split=explode('@',$username)[0];
        $participating= (isset($_POST['participating'])) ? $_POST['participating'] : [ $user_split ];
        
        if ( ($serviceModel->load(Yii::$app->request->post())) && ($projectModel->load(Yii::$app->request->post())) )
        {
            
            $isValid = $projectModel->validate();
            $isValid = $serviceModel->validate() && $isValid;

            // print_r($serviceModel->validate());
            // exit(0);

            if ($isValid)
            {   
                $participant_ids_tmp=[];
                foreach ($participating as $participant)
                {
                   
                    $username=$participant . '@elixir-europe.org';
                    $pid=User::findByUsername($username)->id;
                    $participant_ids_tmp[$pid]=null;
                }

                $participant_ids=[];
                foreach ($participant_ids_tmp as $pid => $dummy)
                {
                    $participant_ids[]=$pid;
                }

                $projectModel->user_list=$participant_ids;
                $messages=$projectModel->uploadNew($participating,$project_types['machine_compute']);
                $errors.=$messages[0];
                $success.=$messages[1];
                $warnings.=$messages[2];
                $requestId=$messages[3];
                
                if ($requestId!=-1)
                {
                    $messages=$serviceModel->uploadNew($requestId);
                    $errors.=$messages[0];
                    $success.=$messages[1];
                    $warnings.=$messages[2];
                   
                }

                if (empty($errors))
                {
                    if(!empty($success))
                    {
                        Yii::$app->session->setFlash('success', "$success");
                    }
                    if(!empty($warnings))
                    {
                        Yii::$app->session->setFlash('warning', "$warnings");
                    }

                    
                    
                    return $this->redirect(['project/index']);
                }
            }
        }
        

        return $this->render('new_machine_compute_request',['service'=>$serviceModel, 'project'=>$projectModel,  'form_params'=>$form_params, 'participating'=>$participating, 'errors'=>$errors, 'new_project_allowed'=>$new_project_allowed]);



    }



    public function actionNewColdStorageRequest()
    {

        $role=User::getRoleType();
        $cold_storage_limits= ColdStorageLimits::find()->where(['user_type'=>$role])->one();
        $cold_storage_maximum_number=$cold_storage_limits->number_of_projects;
        $number_of_user_projects=ProjectRequest::find()->where(['status'=>[1,2],'project_type'=>2,'submitted_by'=>Userw::getCurrentUser()['id'],])->andWhere(['>=','end_date', date("Y-m-d")])->count();
        $new_project_allowed=($number_of_user_projects-$cold_storage_maximum_number < 0) ? true :false;

        if((!$new_project_allowed) && (!Userw::hasRole('Admin', $superadminAllowed=true)) && (!Userw::hasRole('Moderator', $superadminAllowed=true)) )
        {
            return $this->render('no_project_allowed', ['project'=>"Storage volume", 'user_type'=>$role]);
        }

        $coldStorageModel=new ColdStorageRequest;
        $projectModel=new ProjectRequest;
        // $projectModel->duration=36;
        $projectModel->end_date='2100-1-1';
        $projectModel->backup_services=false;

        $limitsModel=new ColdStorageLimits;
        $autoacceptModel=new ColdStorageAutoaccept;

        
        $cold_autoaccept= ColdStorageAutoaccept::find()->where(['user_type'=>$role])->one();
        $cold_autoaccept_number=$cold_autoaccept->autoaccept_number;
        $autoaccepted_num=ProjectRequest::find()->where(['status'=>2,'project_type'=>2,'submitted_by'=>Userw::getCurrentUser()['id'],])->andWhere(['>=','end_date', date("Y-m-d")])->count();
        $autoaccept_allowed=($autoaccepted_num-$cold_autoaccept_number < 0) ? true :false;


        
 
        
        $upperlimits=$limitsModel::find()->where(['user_type'=>$role])->one();
        
        $autoacceptlimits=$autoacceptModel::find()->where(['user_type'=>$role])->one();
       


        $project_types=['service'=>1, 'ondemand'=>0, 'coldstorage'=>2];

        $form_params =
        [
            'action' => URL::to(['project/new-cold-storage-request']),
            'options' => 
            [
                'class' => 'cold_storage_request_form',
                'id'=> "cold_storage_request_form"
            ],
            'method' => 'POST'
        ];

        $errors='';
        $success='';
        $warnings='';
        $username=Userw::getCurrentUser()['username'];
        $user_split=explode('@',$username)[0];
        $participating= (isset($_POST['participating'])) ? $_POST['participating'] : [ $user_split ];


        
        if ( ($coldStorageModel->load(Yii::$app->request->post())) && ($projectModel->load(Yii::$app->request->post())) )
        {
            $isValid = $projectModel->validate();
            $isValid = $coldStorageModel->validate() && $isValid;
            $projectModel->end_date='2100-1-1';
            if ($isValid)
            {    
                $participant_ids_tmp=[];
                foreach ($participating as $participant)
                {
                    // $name_exp=explode(' ',$participant);
                    // $name=$name_exp[0];
                    // $surname=$name_exp[1];
                    $username=$participant . '@elixir-europe.org';
                    $pid=User::findByUsername($username)->id;
                    $participant_ids_tmp[$pid]=null;
                }

                $participant_ids=[];
                foreach ($participant_ids_tmp as $pid => $dummy)
                {
                    $participant_ids[]=$pid;
                }

                $projectModel->user_list=$participant_ids;
                $messages=$projectModel->uploadNew($participating,$project_types['coldstorage']);
                $errors.=$messages[0];
                $success.=$messages[1];
                $warnings.=$messages[2];
                $requestId=$messages[3];
                $submitted_email=$messages[4];
                $project_id=$messages[5];
                if ($requestId!=-1)
                {
                    $messages=$coldStorageModel->uploadNew($requestId);
                    $errors.=$messages[0];
                    $success.=$messages[1];
                    $warnings.=$messages[2];
                    $message_autoaccept=$messages[4];
                }

                if (empty($errors))
                {
                    
                    if(!empty($success))
                    {
                        Yii::$app->session->setFlash('success', "$success");
                    }
                    if(!empty($warnings))
                    {
                        Yii::$app->session->setFlash('warning', "$warnings");
                    }

                    if(empty($message_autoaccept))
                    {
                    	EmailEvents::NotifyByEmail('new_project', $project_id,$submitted_email);
                    }
                    else
                    {
                    	EmailEvents::NotifyByEmail('project_decision', $project_id,$message_autoaccept);
                    }
                    
                    return $this->redirect(['project/index']);

                }
            }
        }
        
        
            return $this->render('new_cold_storage_request',['coldStorage'=>$coldStorageModel, 'project'=>$projectModel, 
                    'form_params'=>$form_params, 'participating'=>$participating, 'errors'=>$errors,
                     'upperlimits'=>$upperlimits, 'autoacceptlimits'=>$autoacceptlimits,'autoaccept_allowed' => $autoaccept_allowed, 'role'=>$role, 'new_project_allowed'=>$new_project_allowed]);


    }


    public function actionNewOndemandRequest()
    {

        $role=User::getRoleType();
        $ondemand_limits= OndemandLimits::find()->where(['user_type'=>$role])->one();
        $ondemand_maximum_number=$ondemand_limits->number_of_projects;
        $number_of_user_projects=ProjectRequest::find()->where(['status'=>[1,2],'project_type'=>0,'submitted_by'=>Userw::getCurrentUser()['id'],])->andWhere(['>=','end_date', date("Y-m-d")])->count();
        $new_project_allowed=($number_of_user_projects-$ondemand_maximum_number < 0) ? true :false;

        if((!$new_project_allowed) && (!Userw::hasRole('Admin', $superadminAllowed=true)) && (!Userw::hasRole('Moderator', $superadminAllowed=true)) )
        {
            return $this->render('no_project_allowed', ['project'=>"On-demand batch computations", 'user_type'=>$role]);
        } 

        $ondemandModel=new OndemandRequest();
        $projectModel=new ProjectRequest;
        $limitsModel=new OndemandLimits;
        $autoacceptModel=new OndemandAutoaccept;

        
        $ondemand_autoaccept= OndemandAutoaccept::find()->where(['user_type'=>$role])->one();
        $ondemand_autoaccept_number=$ondemand_autoaccept->autoaccept_number;
        $autoaccepted_num=ProjectRequest::find()->where(['status'=>2,'project_type'=>0,'submitted_by'=>Userw::getCurrentUser()['id'],])->andWhere(['>=','end_date', date("Y-m-d")])->count();
        $autoaccept_allowed=($autoaccepted_num-$ondemand_autoaccept_number < 0) ? true :false; 


        
    
        
        $upperlimits=$limitsModel::find()->where(['user_type'=>$role])->one();
        
        $autoacceptlimits=$autoacceptModel::find()->where(['user_type'=>$role])->one();
       


        $project_types=['service'=>1, 'ondemand'=>0, 'coldstorage'=>2];

        $form_params =
        [
            'action' => URL::to(['project/new-ondemand-request']),
            'options' => 
            [
                'class' => 'ondemand_project',
                'id'=> "ondemand_project"
            ],
            'method' => 'POST'
        ];

        $maturities=["developing"=>'Developing', 'testing'=> 'Testing', 'production'=>'Production'];

        $errors='';
        $success='';
        $warnings='';
        $message='';
        $username=Userw::getCurrentUser()['username'];
        $user_split=explode('@',$username)[0];
        $participating= (isset($_POST['participating'])) ? $_POST['participating'] : [ $user_split ];
      
                

        if ( ($ondemandModel->load(Yii::$app->request->post())) && ($projectModel->load(Yii::$app->request->post())) )
        {
            $isValid = $projectModel->validate();
            $isValid = $ondemandModel->validate() && $isValid;

            if ($isValid)
            {

                $participant_ids_tmp=[];
                foreach ($participating as $participant)
                {
                    // $name_exp=explode(' ',$participant);
                    // $name=$name_exp[0];
                    // $surname=$name_exp[1];
                    $username=$participant . '@elixir-europe.org';
                    $pid=User::findByUsername($username)->id;
                    $participant_ids_tmp[$pid]=null;
                }

                $participant_ids=[];
                foreach ($participant_ids_tmp as $pid => $dummy)
                {
                    $participant_ids[]=$pid;
                }

                $projectModel->user_list=$participant_ids;
                $messages=$projectModel->uploadNew($participating,$project_types['ondemand']);
                $errors.=$messages[0];
                $success.=$messages[1];
                $warnings.=$messages[2];
                $requestId=$messages[3];
                $submitted_email=$messages[4];
                $project_id=$messages[5];

                if ($requestId!=-1)
                {
                    $messages=$ondemandModel->uploadNew($requestId);
                    $errors.=$messages[0];
                    $success.=$messages[1];
                    $warnings.=$messages[2];
                    $message_autoaccept=$messages[3];
                }

                if (empty($errors))
                {
                    if(!empty($success))
                    {
                        Yii::$app->session->setFlash('success', "$success");
                    }
                    if(!empty($warnings))
                    {
                        Yii::$app->session->setFlash('warning', "$warnings");
                    }

                    if(empty($message_autoaccept))
                    {
                    	EmailEvents::NotifyByEmail('new_project', $project_id,$submitted_email);
                    }
                    else
                    {
                    	EmailEvents::NotifyByEmail('project_decision', $project_id,$message_autoaccept);
                    }
                    
                    return $this->redirect(['project/index']);
                }
            }
        }
        

        return $this->render('new_ondemand_request',['ondemand'=>$ondemandModel, 'project'=>$projectModel, 
                     'maturities'=>$maturities, 'form_params'=>$form_params, 'participating'=>$participating, 'errors'=>$errors, 'upperlimits'=>$upperlimits, 'autoacceptlimits'=>$autoacceptlimits,'autoaccept_allowed' => $autoaccept_allowed, 'role'=>$role, 'new_project_allowed'=>$new_project_allowed]);
    }



    public function actionAutoCompleteNames($expansion, $max_num, $term)
    {
        //Create mature version model
        $model = new User;
        //Get names based on query parameters. NOTE: results should be in json
        $names = $model::getNamesAutoComplete($expansion, $max_num, $term);
        $namesDecoded=json_decode($names);
        //Check if results are empty
        if(empty($namesDecoded)) 
        {
            $names = json_encode(["No suggestions found"]);
        }     
        //Return results - these are already encoded in json
        return $names;       
    }

    public function actionRequestList($filter='all')
    {
        
        $statuses=ProjectRequest::STATUSES;
        $filters=['all'=>'All','pending'=>'Pending','approved'=>'Approved','auto-approved'=>'Auto-approved','rejected'=>'Rejected'];
        $project_types=Project::TYPES;
        // $button_links=[0=>'/project/view-ondemand-request', 1=>'/project/view-service-request', 2=>'/project/view-cold-storage-request'];
        $line_classes=[-5=>'expired',-4=>'deleted',-3=>'modified',-1=>'rejected',0=>'pending', 1=>'approved', 2=>'approved'];
        // $user=User::getCurrentUser()['username'];

        $results=ProjectRequest::getRequestList($filter);

        $pages=$results[0];
        $results=$results[1];

        $sidebarItems=[];

        foreach ($filters as $f=>$text)
        {
            $active=($f==$filter) ? 'active' : '';
            $sidebarItems[]=['link'=>Url::to(['project/request-list', 'filter'=>$f]), 'class'=>"list-group-item $active",'text'=>$text];
        }

        return $this->render('request_list',['results'=>$results,'pages'=>$pages,'statuses'=>$statuses,
                                'sideItems'=>$sidebarItems,'project_types'=>$project_types,
                                'line_classes'=>$line_classes,'filter'=>$filter]);
    }


    public function actionUserRequestList($filter='all')
    {
        $statuses=ProjectRequest::STATUSES;
        
        $filters=['all'=>'All','pending'=>'Pending','approved'=>'Approved','auto-approved'=>'Auto-approved','rejected'=>'Rejected'];
        $project_types=Project::TYPES;
        // $button_links=[0=>'/project/view-ondemand-request-user', 1=>'/project/view-service-request-user', 
        //                 2=>'/project/view-cold-storage-request-user'];
        $line_classes=[-5=>'expired',-4=>'deleted',-3=>'modified',-1=>'rejected',0=>'pending', 1=>'approved', 2=>'approved'];
        // $user=User::getCurrentUser()['username'];

        $results=ProjectRequest::getUserRequestList($filter);
        

        $pages=$results[0];
        $results=$results[1];

        $sidebarItems=[];
        $expired=0;

        foreach ($filters as $f=>$text)
        {
            $active=($f==$filter) ? 'active' : '';
            $sidebarItems[]=['link'=>Url::to(['project/user-request-list', 'filter'=>$f]), 'class'=>"list-group-item $active",'text'=>$text];
        }

        return $this->render('request_list_user',['results'=>$results,'pages'=>$pages,'statuses'=>$statuses,
                                'sideItems'=>$sidebarItems,'project_types'=>$project_types,
                                'filter'=>$filter,'line_classes'=>$line_classes, 'expired'=>$expired]);
    }

    public function actionViewRequest($id,$filter='all')
    {

        ProjectRequest::recordViewed($id);
        $project_request=ProjectRequest::findOne($id);
        
        if (!Userw::hasRole('Admin',$superadminAllowed=true) && (!Userw::hasRole('Moderator',$superadminAllowed=true)) )
        {
            return $this->render('//site/error_unauthorized');
        }

        if(is_null($project_request->approval_date))
        {
            $start = date('Y-m-d', strtotime($project_request->submission_date));
        }
        else
        {
            $start = date('Y-m-d', strtotime($project_request->approval_date));
        }
        if(is_null($project_request->end_date))
        {
            $ends=date('Y-m-d', strtotime($start. " + $project_request->duration months"));
        }
        else
        {
            $ends= explode(' ', $project_request->end_date)[0];
        }
        $now=date('Y-m-d');
        $datetime1 = strtotime($now);
        $datetime2 = strtotime($ends);
        $secs = $datetime2 - $datetime1;
        $remaining_time = $secs / 86400;
        if($remaining_time<=0)
        {    
            $remaining_time=0;
        }

        $usage=[];
        $remaining_jobs=0;
        
    

        //Request details must be retrieved by the project request id
        if ($project_request->project_type==0)
        {
            
            $details=OndemandRequest::findOne(['request_id'=>$id]);
            $view_file='view_ondemand_request';
            $usage=ProjectRequest::getProjectSchemaUsage($project_request->name);
            $type="On-demand batch computation";
            $num_of_jobs=$details->num_of_jobs;
            $used_jobs=$usage['count'];
            $remaining_jobs=$num_of_jobs-$used_jobs;

        }
        else if ($project_request->project_type==1)
        {
            $details=ServiceRequest::findOne(['request_id'=>$id]);
            $view_file='view_service_request';
            $type="24/7 Service";
         
        }
        else if ($project_request->project_type==3)
        {
            $details=MachineComputeRequest::findOne(['request_id'=>$id]);
            $view_file='view_machine_compute_request';
            $type="On-demand computation machines";
        }
        else if ($project_request->project_type==2)
        {
            $details=ColdStorageRequest::findOne(['request_id'=>$id]);
            $view_file='view_cold_request';
            $type="Storage volumes";
           
        }
        
        $submitted=User::find()->where(['id'=>$project_request->submitted_by])->one();
        $project_owner= ($submitted->username==Userw::getCurrentUser()['username']);
        /*
         * Fix username so that it is shown without @
         */
        $users=User::find()->where(['IN','id',$project_request->user_list])->all();
        $submitted->username=explode('@',$submitted->username)[0];
        // $users=User::returnList($project->user_list);
        $number_of_users=count($users);
        $maximum_number_users=$project_request->user_num;

        $user_list='';
        foreach ($users as $user)
        {
            $usernames=$user->username;
            if (--$number_of_users <= 0) 
            {
                $user_list.=explode('@', $usernames)[0].'';
            }    
            else
            {
                $user_list.=explode('@', $usernames)[0].', ';
            }
        }

        $number_of_users=count($users);
        $expired=0;

        return $this->render($view_file,['project'=>$project_request,'details'=>$details, 
            'filter'=>$filter,'usage'=>$usage,'user_list'=>$user_list, 'submitted'=>$submitted,'request_id'=>$id, 'type'=>$type, 'ends'=>$ends, 'start'=>$start, 'remaining_time'=>$remaining_time,
            'project_owner'=>$project_owner, 'number_of_users'=>$number_of_users, 'maximum_number_users'=>$maximum_number_users, 'remaining_jobs'=>$remaining_jobs, 'expired'=>$expired]);


    }

    public function actionViewRequestUser($id,$filter='all',$return='')
    {

        ProjectRequest::recordViewed($id);
        $project_request=ProjectRequest::findOne($id);
        

        $users=User::find()->where(['IN','id',$project_request->user_list])->all();
        $user_ids=array_column($users, 'id');
        $current_user_id=Userw::getCurrentUser()['id'];

        if ( (!in_array($current_user_id, $user_ids)) && (!Userw::hasRole('Moderator',$superadminAllowed=true))
                && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }

      

        if(is_null($project_request->approval_date))
        {
            $start = date('Y-m-d', strtotime($project_request->submission_date));
        }
        else
        {
            $start = date('Y-m-d', strtotime($project_request->approval_date));
        }
        if(is_null($project_request->end_date))
        {
            $ends=date('Y-m-d', strtotime($start. " + $project_request->duration months"));
        }
        else
        {
            $ends= explode(' ', $project_request->end_date)[0];
        }
        $now=date('Y-m-d');
        $datetime1 = strtotime($now);
        $datetime2 = strtotime($ends);
        $secs = $datetime2 - $datetime1;
        $remaining_time = $secs / 86400;
        if($remaining_time<=0)
        {    
            $remaining_time=0;
        }
        $usage=[];
        $remaining_jobs=0;
        

        //Request details must be retrieved by the project request id
        if ($project_request->project_type==0)
        {
            $details=OndemandRequest::findOne(['request_id'=>$id]);
            $view_file='view_ondemand_request_user';
            $usage=ProjectRequest::getProjectSchemaUsage($project_request->name);
            $type="On-demand batch computation";
            $num_of_jobs=$details->num_of_jobs;
            $used_jobs=$usage['count'];
            $remaining_jobs=$num_of_jobs-$used_jobs;
        }
        else if ($project_request->project_type==1)
        {
            $details=ServiceRequest::findOne(['request_id'=>$id]);
            $view_file='view_service_request_user';
            $type="24/7 Service";
            

        }
        else if ($project_request->project_type==3)
        {
            $details=MachineComputeRequest::findOne(['request_id'=>$id]);
            $view_file='view_machine_compute_request_user';
            $type="On-demand computation machines";
           
        }
        else if ($project_request->project_type==2)
        {
            $details=ColdStorageRequest::findOne(['request_id'=>$id]);
            $view_file='view_cold_request_user';
            $type="Storage volumes";
            $remaining_jobs=0;
        }
        

        $submitted=User::find()->where(['id'=>$project_request->submitted_by])->one();
        $project_owner= ($submitted->username==Userw::getCurrentUser()['username']);
        /*
         * Fix username so that it is shown without @
         */
        $submitted->username=explode('@',$submitted->username)[0];
        // $users=User::returnList($project->user_list);
        $number_of_users=count($users);
        $maximum_number_users=$project_request->user_num;

        $user_list='';
        foreach ($users as $user)
        {
            $usernames=$user->username;
            if (--$number_of_users <= 0) 
            {
                $user_list.=explode('@', $usernames)[0].'';
            }    
            else
            {
                $user_list.=explode('@', $usernames)[0].', ';
            }
        }

        $number_of_users=count($users);
        
        $expired=0;

        return $this->render($view_file,['project'=>$project_request,'details'=>$details, 'return'=>$return,
            'filter'=>$filter,'usage'=>$usage,'user_list'=>$user_list, 'submitted'=>$submitted,'request_id'=>$id, 'type'=>$type, 'ends'=>$ends, 'start'=>$start, 'remaining_time'=>$remaining_time,
        	'project_owner'=>$project_owner, 'number_of_users'=>$number_of_users, 'maximum_number_users'=>$maximum_number_users, 'remaining_jobs'=>$remaining_jobs, 'expired'=>$expired]);

    }

    public function actionApprove($id,$filter='all')
    {
        if ((!Userw::hasRole('Moderator',$superadminAllowed=true))  && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }

        $request=ProjectRequest::find()->where(['id'=>$id])->one();
        $request->approve();

        $message='Project approved.';


        Yii::$app->session->setFlash('success', $message);
        return $this->redirect(['project/request-list']);

    }

    public function actionReject($id,$filter='all')
    {
        if ((!Userw::hasRole('Moderator',$superadminAllowed=true))  && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }

        $request=ProjectRequest::find()->where(['id'=>$id])->one();
        $request->reject();

        $message='Project rejected.';

        Yii::$app->session->setFlash('danger', $message);
        return $this->redirect(['project/request-list']);
    }


    public function actionConfigureVm($id)
    {
        $owner=Project::userInProject($id);

        if ( (empty($owner)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }

        $existing=Vm::find()->where(['project_id'=>$id])->andWhere(['active'=>true])->one();

        $project=Project::find()->where(['id'=>$id])->one();
        $project_id=$project->id;
        
        
        if (empty($existing))
        {
            /*
             * Create new VM
             */
            $model=new Vm;
            session_write_close();
            $avResources=VM::getOpenstackAvailableResources();
            session_start();
            // print_r($avResources);
            // exit(0);
            $project=Project::find()->where(['id'=>$id])->one();
            $latest_project_request_id=$project->latest_project_request_id;
            $service=ServiceRequest::find()->where(['request_id'=>$latest_project_request_id])->one();

            // print_r($avResources);
            // exit(0);
            
            if ( ($service->num_of_ips>$avResources[2]) || ($service->ram>$avResources[1]) || ($service->num_of_cores > $avResources[0]) || ($service->storage > $avResources[3]) )
            {
                return $this->render('service_unavailable_resources');
            }
            
            $imageDD=Vm::getOpenstackImages();

            $form_params =
            [
                'action' => URL::to(['project/configure-vm','id'=>$id]),
                'options' => 
                [
                    'class' => 'vm__form',
                    'id'=> "vm_form"
                ],
                'method' => 'POST'
            ];

            if ($model->load(Yii::$app->request->post()))
            {

                $model->keyFile = UploadedFile::getInstance($model, 'keyFile');
                // print_r($model->keyFile->extension);
                // exit(0);
                if ($model->validate())
                {
                    session_write_close();
                    $result=$model->createVM($latest_project_request_id,$service, $imageDD);
                    session_start();
                    $error=$result[0];
                    $message=$result[1];
                    $openstackMessage=$result[2];
                    if ($error!=0)
                    {
                        
                        return $this->render('error_vm_creation',['error' => $error,'message'=>$message,'openstackMessage'=>$openstackMessage]);
                    }

                    else
                    {
                        $existing=Vm::find()->where(['project_id'=>$id])->andWhere(['active'=>true])->one();
                        session_write_close();
                        $existing->getConsoleLink();
                        session_start();
    
                        return $this->render('vm_details',['model'=>$existing, 'requestId'=>$id, 'service'=>$service]);
                    }
                }
            }
            
            return $this->render('configure_vm',['model'=>$model,'form_params'=>$form_params,'imageDD'=>$imageDD,'service'=>$service]);
        }
        else
        {
            $user_id=Userw::getCurrentUser()['id'];
            $volume_exists=HotVolumes::getCreatedVolumesServicesUser($user_id);
           

            $hotvolume=HotVolumes::find()->where(['vm_id'=>$existing->id])->andWhere(['active'=>true])->all();
            $additional_storage=[];
            if(!empty($hotvolume))
            {
                foreach ($hotvolume as $hot) 
                {
                    $project=Project::find()->where(['id'=>$hot->project_id])->one();
                    // print_r($hot);
                    // exit(0);
                    $cold_storage_request=ColdStorageRequest::find()->where(['request_id'=>$project->latest_project_request_id])->one();
                    $additional_storage[$hot->id]=['name'=>$hot->name, 'size'=>$cold_storage_request->storage,'mountpoint'=>$hot->mountpoint];
                }
            }
            $attached_volumes_ids=array_column($hotvolume, 'id');
            $not_attached_volumes=HotVolumes::find()
            ->where(['NOT',['id'=>$attached_volumes_ids]])
            ->andWhere(['active'=>true])
            ->andWhere(['vm_type'=>1])
            ->all();
            // print_r($not_attached_volumes);
            // exit(0);

            $service_old=ServiceRequest::find()->where(['request_id'=>$existing->request_id])->one();
            $service_old_id=$service_old->id;
            $project=Project::find()->where(['id'=>$id])->one();
            $latest_service_request_id=$project->latest_project_request_id;
            $service=ServiceRequest::find()->where(['request_id'=>$latest_service_request_id])->one();
            if($service_old_id<$latest_service_request_id)
            {
                if($service_old->vm_flavour != $service->vm_flavour)
                {
                    Yii::$app->session->setFlash('success', "Due to an accepted project update, you have the option to create a larger machine. If you want to create it, backup any data stored in your current machine and then destroy it. After that you will be able to create a larger machine");
                }
            }
            session_write_close();
            $existing->getConsoleLink();
            session_start();
            return $this->render('vm_details',['model'=>$existing,'requestId'=>$id, 'service'=>$service, 'additional_storage'=>$additional_storage]);
        }
        

    }

    public function actionMachineComputeConfigureVm($id)
    {
        // $project=Project::find()->where(['id'=>$id])->one();
        // $latest_project_request_id=$project->latest_project_request_id;
        $owner=Project::userInProject($id);

        // print_r($owner);
        // exit(0);
        if ( (empty($owner)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }

        $existing=VmMachines::find()->where(['project_id'=>$id])->andWhere(['active'=>true])->one();

        //If vm is upgraded, then show message to user to delete past vm and create new
        if(!empty($existing))
        {
            $service_old=MachineComputeRequest::find()->where(['request_id'=>$existing->request_id])->one();
            $service_old_id=$service_old->id;
            $project=Project::find()->where(['id'=>$id])->one();
            $latest_service_request_id=$project->latest_project_request_id;
            $service=MachineComputeRequest::find()->where(['request_id'=>$latest_service_request_id])->one();
            if($service_old_id<$latest_service_request_id)
            {
                if($service_old->vm_flavour != $service->vm_flavour)
                {
                    Yii::$app->session->setFlash('success', "Due to an accepted project update, you have the option to create a larger machine. If you want to create it, backup any data stored in your current machine and then destroy it. After that you will be able to create a larger machine");
                }
            }
        }
        
       
        if (empty($existing))
        {
            /*
             * Create new VM
             */
            $model=new VmMachines;

            $avResources=VmMachines::getOpenstackAvailableResources();

            // print_r($model);
            // exit(0);
            // print_r($avResources);
            // exit(0);
            $project=Project::find()->where(['id'=>$id])->one();
            $latest_project_request_id=$project->latest_project_request_id;
            $service=MachineComputeRequest::find()->where(['request_id'=>$latest_project_request_id])->one();

            // print_r($avResources);
            // exit(0);
            
            if ( ($service->num_of_ips>$avResources[2]) || ($service->ram>$avResources[1]) || ($service->num_of_cores > $avResources[0]) || ($service->storage > $avResources[3]) )
            {
                return $this->render('service_unavailable_resources');
            }
            
            $imageDD=VmMachines::getOpenstackImages();

            $form_params =
            [
                'action' => URL::to(['project/machine-compute-configure-vm','id'=>$id]),
                'options' => 
                [
                    'class' => 'vm__form',
                    'id'=> "vm_form"
                ],
                'method' => 'POST'
            ];

            if ($model->load(Yii::$app->request->post()))
            {

                $model->keyFile = UploadedFile::getInstance($model, 'keyFile');
                // print_r($model->keyFile->extension);
                // exit(0);
                if ($model->validate())
                {
                    $result=$model->createVM($latest_project_request_id,$service, $imageDD);
                    $error=$result[0];
                    $message=$result[1];
                    $openstackMessage=$result[2];
                    if ($error!=0)
                    {
                        
                        return $this->render('error_vm_creation',['error' => $error,'message'=>$message,'openstackMessage'=>$openstackMessage]);
                    }

                    else
                    {
                        $existing=VmMachines::find()->where(['project_id'=>$id])->andWhere(['active'=>true])->one();
                        $existing->getConsoleLink();
    
                        return $this->render('vm_machines_details',['model'=>$existing, 'requestId'=>$id, 'service'=>$service]);
                    }
                }
            }
            
            return $this->render('configure_vm',['model'=>$model,'form_params'=>$form_params,'imageDD'=>$imageDD,'service'=>$service]);
        }
        else
        {
        	$hotvolume=HotVolumes::find()->where(['vm_id'=>$existing->id])->andWhere(['active'=>true])->all();
            $additional_storage=[];
            if(!empty($hotvolume))
            {
                foreach ($hotvolume as $hot) 
                {
                    $project=Project::find()->where(['id'=>$hot->project_id])->one();
                    $cold_storage_request=ColdStorageRequest::find()->where(['request_id'=>$project->latest_project_request_id])->one();
                    $additional_storage[$hot->id]=['name'=>$hot->name, 'size'=>$cold_storage_request->storage,'mountpoint'=>$hot->mountpoint];
                }
            }



        
             $existing->getConsoleLink();
             return $this->render('vm_machines_details',['model'=>$existing,'requestId'=>$id, 'service'=>$service, 'additional_storage'=>$additional_storage]);
        }
        

    }

    public function actionDeleteVm($id)
    {
        $owner=Project::userInProject($id);

        if ( (empty($owner)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }

        $vm=Vm::find()->where(['project_id'=>$id])->andWhere(['active'=>true])->one();

        $result=$vm->deleteVM();
        $error=$result[0];
        $message=$result[1];
        $openstackMessage=$result[2];

        if ($error!=0)
        {
            return $this->render('error_vm_deletion',['error' => $error,'message'=>$message, 'openstackMessage'=>$openstackMessage]);
        }

        /*
         * If there are no errors, load the index page
         */
        
        

        $success='Successfully deleted VM.';

        if(!empty($success))
        {
            Yii::$app->session->setFlash('success', "$success");
        }
        
                    
        return $this->redirect(['project/index']);
        

    }

    public function actionDeleteVmMachines($id)
    {
        $owner=Project::userInProject($id);

        if ( (empty($owner)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }

        $vm=VmMachines::find()->where(['project_id'=>$id])->andWhere(['active'=>true])->one();

        $result=$vm->deleteVM();
        $error=$result[0];
        $message=$result[1];
        $openstackMessage=$result[2];

        if ($error!=0)
        {
            return $this->render('error_vm_deletion',['error' => $error,'message'=>$message, 'openstackMessage'=>$openstackMessage]);
        }

        /*
         * If there are no errors, load the index page
         */
        
        

        $success='Successfully deleted VM.';

        if(!empty($success))
        {
            Yii::$app->session->setFlash('success', "$success");
        }
        
                    
        return $this->redirect(['project/index']);
        

    }

    public function actionVmList($filter='all')
    {

        $results=ProjectRequest::getVmList($filter);

        $new_results=[];
        foreach ($results[1] as $res) 
        {
            $now = strtotime(date("Y-m-d"));
            $end_project = strtotime($res['end_date']);
            $remaining=$now-$end_project;
            if($res['project_type']==1)
            {
                $type='service';
                $res['type']=$type;
            }
            else
            {
                $type='machines';
                $res['type']=$type;
            }
            if($remaining<0)
            {
                $expired=0;
                $res['expired']=$expired;
                

            }    
            else
            {   
                $expired=1;
                $res['expired']=$expired;
                
            }
            $new_results[]=$res;
        }

        $pages=$results[0];
        $results=$new_results;
      
        $sidebarItems=[];
        $filters=['all', 'active', 'deleted'];
        $filter_names=['all'=>'All','active'=>'Active','deleted'=>'Deleted'];

        foreach ($filters as $f)
        {
            $active=($f==$filter) ? 'active' : '';
            $sidebarItems[]=['link'=>Url::to(['project/vm-list', 'filter'=>$f]), 'class'=>"list-group-item $active",'text'=>$filter_names[$f]];
        }

        return $this->render('vm_list',['results'=>$results,'pages'=>$pages,
                                'sideItems'=>$sidebarItems,'filter'=>$filter]);
    }

    public function actionVmMachinesList($filter='all')
    {

        $results=ProjectRequest::getVmMachinesList($filter);

        $new_results=[];
        foreach ($results[1] as $res) 
        {
            $now = strtotime(date("Y-m-d"));
            $end_project = strtotime($res['end_date']);
            $remaining=$now-$end_project;
            if($res['project_type']==3)
            {
                $type='service';
                $res['type']=$type;
            }
            else
            {
                $type='machines';
                $res['type']=$type;
            }
            if($remaining<0)
            {
                $expired=0;
                $res['expired']=$expired;
                

            }    
            else
            {   
                $expired=1;
                $res['expired']=$expired;
                
            }
            $new_results[]=$res;
        }

        $pages=$results[0];
        $results=$new_results;
      
        $sidebarItems=[];
        $filters=['all', 'active', 'deleted'];
        $filter_names=['all'=>'All','active'=>'Active','deleted'=>'Deleted'];

        foreach ($filters as $f)
        {
            $active=($f==$filter) ? 'active' : '';
            $sidebarItems[]=['link'=>Url::to(['project/vm-machines-list', 'filter'=>$f]), 'class'=>"list-group-item $active",'text'=>$filter_names[$f]];
        }

        return $this->render('vm_machines_list',['results'=>$results,'pages'=>$pages,
                                'sideItems'=>$sidebarItems,'filter'=>$filter]);
    }


    public function actionAdminVmDetails($id,$project_id,$filter)
    {
        
        if (!Userw::hasRole('Admin',$superadminAllowed=true))
        {
            return $this->render('error_unauthorized');
        }
        $project=Project::find()->where(['id'=>$project_id])->one();
        $project_request=ProjectRequest::find()->where(['id'=>$project->latest_project_request_id])->one();
       // $project_request=ProjectRequest::find()->where(['id'=>$request_id])->one();
        $projectOwner=User::returnUsernameById($project_request->submitted_by);
        $projectOwner=explode('@', $projectOwner)[0];
        $service=ServiceRequest::find()->where(['request_id'=>$project_request->id])->one();
        $vm=VM::find()->where(['id'=>$id])->one();
        $createdBy=User::returnUsernameById($vm->created_by);
        $createdBy=explode('@', $createdBy)[0];
        $deletedBy=(!empty($vm->deleted_by)) ? User::returnUsernameById($vm->deleted_by): '';
        $deletedBy=explode('@', $deletedBy)[0];


        
        return $this->render('vm_admin_details',['project'=>$project,'service'=>$service,
                                'vm'=>$vm, 'projectOwner'=>$projectOwner, 'createdBy'=>$createdBy,
                                'deletedBy'=>$deletedBy, 'filter'=>$filter, 'project_id'=>$project->id ]);
    }

    

    public function actionAdminVmMachinesDetails($id,$project_id,$filter)
    {
        
        if (!Userw::hasRole('Admin',$superadminAllowed=true))
        {
            return $this->render('error_unauthorized');
        }
        $project=Project::find()->where(['id'=>$project_id])->one();
        $project_request=ProjectRequest::find()->where(['id'=>$project->latest_project_request_id])->one();
       // $project_request=ProjectRequest::find()->where(['id'=>$request_id])->one();
        $projectOwner=User::returnUsernameById($project_request->submitted_by);
        $projectOwner=explode('@', $projectOwner)[0];
        $service=MachineComputeRequest::find()->where(['request_id'=>$project_request->id])->one();
        $vm=VmMachines::find()->where(['id'=>$id])->one();
        $createdBy=User::returnUsernameById($vm->created_by);
        $createdBy=explode('@', $createdBy)[0];
        $deletedBy=(!empty($vm->deleted_by)) ? User::returnUsernameById($vm->deleted_by): '';
        $deletedBy=explode('@', $deletedBy)[0];


        
        return $this->render('vm_machines_admin_details',['project'=>$project,'service'=>$service,
                                'vm'=>$vm, 'projectOwner'=>$projectOwner, 'createdBy'=>$createdBy,
                                'deletedBy'=>$deletedBy, 'filter'=>$filter, 'project_id'=>$project->id ]);
    }

    public function actionModeratorOptions()
    {
        return $this->render('moderator_options');
    }

    public function actionEditProject($id)
    {
        $prequest=ProjectRequest::find()->where(['id'=>$id])->one();
        
        if (empty($prequest))
        {
            return $this->render('error_unauthorized');
        }
        $owner=($prequest->submitted_by==Userw::getCurrentUser()['id']);

        // print_r($prequest->status);
        // exit(0);

        if ( (!$owner) && (!Userw::hasRole('Admin',$superadminAllowed=true)) || (($prequest->status!=ProjectRequest::APPROVED) && ($prequest->status!=ProjectRequest::AUTOAPPROVED)) )
        {
            return $this->render('error_unauthorized');
        }


        
        $prequest->fillUsernameList();

        $prType=$prequest->project_type;

        $project=Project::find()->where(['latest_project_request_id'=>$id])->one();
        $trls=[];
        $maturities=[];

        $role=User::getRoleType();

        $vm_exists=0;

        $start=date('Y-m-d',strtotime($prequest->approval_date));

        $duration=$prequest->duration;

        if(empty($prequest->end_date))
        {
                $ends=date('Y-m-d', strtotime($start. " + $duration months"));
        }
        else
        {
                $ends= explode(' ', $prequest->end_date)[0];
        }

        $prequest->end_date=$ends;

        if ($prType==0)
        {
            $drequest=OndemandRequest::find()->where(['request_id'=>$id])->one();
            $view_file='edit_ondemand';
            $upperlimits=OndemandLimits::find()->where(['user_type'=>$role])->one();
            $autoacceptlimits=OndemandAutoaccept::find()->where(['user_type'=>$role])->one();
            $maturities=["developing"=>'Developing', 'testing'=> 'Testing', 'production'=>'Production'];
            $prequest->end_date=$ends;

        }
        else if ($prType==1)
        {
            $drequest=ServiceRequest::find()->where(['request_id'=>$id])->one();
            $drequest->flavour=$drequest->flavourIdNameLimitless[$drequest->vm_flavour];
            if (!isset($drequest->flavours[$drequest->flavour]))
            {
                $drequest->flavours[$drequest->flavour]=$drequest->allFlavours[$drequest->flavour];
            }
            $view_file='edit_service';
            $upperlimits=ServiceLimits::find()->where(['user_type'=>$role])->one();
            $autoacceptlimits=ServiceAutoaccept::find()->where(['user_type'=>$role])->one();
            $prequest->end_date=$ends;
            
            $project_id=$prequest->project_id;
            $vm=VM::find()->where(['project_id'=>$project_id, 'active'=>true])->one();
            if (!empty($vm))
            {
                // return $this->render('error_service_vm_exist');
                $vm_exists=1;
            }


            $trls[0]='Unspecified';
            for ($i=1; $i<10; $i++)
            {
                $trls[$i]='Level ' . $i;
            }
        }
        else if ($prType==3)
        {
            $drequest=MachineComputeRequest::find()->where(['request_id'=>$id])->one();
            $drequest->flavour=$drequest->flavourIdNameLimitless[$drequest->vm_flavour];
            $view_file='edit_machine_compute';
            $prequest->end_date=$ends;
            $upperlimits='';
            $autoacceptlimits='';
            $project_id=$prequest->project_id;
            $vm=VM::find()->where(['project_id'=>$project_id, 'active'=>true])->one();
            if (!empty($vm))
            {
                // return $this->render('error_service_vm_exist');
                $vm_exists=1;
            }
        }
        else if ($prType==2)
        {
            $drequest=ColdStorageRequest::find()->where(['request_id'=>$id])->one();
            $view_file='edit_cold_storage';
            //$prequest->duration=0;
            $prequest->end_date='2100-1-1';
            //$prequest->update();
            // print_r($prequest);
            // exit(0);
            $upperlimits=ColdStorageLimits::find()->where(['user_type'=>$role])->one();
            $autoacceptlimits=ColdStorageAutoaccept::find()->where(['user_type'=>$role])->one();
        }

        

        $form_params =
        [
            'action' => URL::to(['project/edit-project', 'id'=>$id]),
            'options' => 
            [
                'class' => 'service_request_form',
                'id'=> "service_request_form"
            ],
            'method' => 'POST'
        ];

        $errors='';
        $success='';
        $warnings='';
        $username=Userw::getCurrentUser()['username'];
        $user_split=explode('@',$username)[0];
        $participating= (isset($_POST['participating'])) ? $_POST['participating'] : $prequest->usernameList;
        $pold=clone $prequest;
        $dold=clone $drequest;
        $attr=$pold->getAttributes();

        if ( ($drequest->load(Yii::$app->request->post())) && ($prequest->load(Yii::$app->request->post())) )
        {

            // print_r($drequest);
            // print_r($prequest->name);
            // exit(0);
            $isValid = $prequest->validate();
            $isValid = $drequest->validate() && $isValid;

            // print_r($dold->flavour);
            // print_r($drequest->flavour);
            // exit(0);
            $participant_ids_tmp=[];
            foreach ($participating as $participant)
            {
                // $name_exp=explode(' ',$participant);
                // $name=$name_exp[0];
                // $surname=$name_exp[1];
                $username=$participant . '@elixir-europe.org';
                $pid=User::findByUsername($username)->id;
                $participant_ids_tmp[$pid]=null;
            }

            $participant_ids=[];
            foreach ($participant_ids_tmp as $pid => $dummy)
            {
                $participant_ids[]=$pid;
            }
            
            $prequest->user_list=new yii\db\ArrayExpression($participant_ids, 'int4');
            $pchanged_tmp= ProjectRequest::ProjectModelChanged($pold,$prequest);
            $pchanged=$pchanged_tmp[0];
            $uchanged=$pchanged_tmp[1];
            $dchanged= ProjectRequest::modelChanged($dold,$drequest);
            
            $project_id=$prequest->project_id;
            $vm=VM::find()->where(['project_id'=>$project_id, 'active'=>true])->one();
            
            if (!empty($vm))
            {
                   
                if(ServiceRequest::compareServices($dold,$drequest))
                {
                    Yii::$app->session->setFlash('danger', "You are not allowed to request fewer resources, since you have already created a VM");
                    return $this->redirect(['project/edit-project', 'id'=>$id]);
                 }
                
            }
            

            if ($isValid)
            {   
                if ($prType==2)
                {
                  //  $prequest->end_date='2100-1-1';
                }
                if ($prType==1 || $prType==3)
                {
                    if ($dold->flavour != $drequest->flavour)
                    {
                        $dchanged=true;
                    }
                }

                if ($pchanged || $dchanged)
                {
                
                    $messages=$prequest->uploadNewEdit($participating,$prType,$id,$uchanged);
                    $errors.=$messages[0];
                    $success.=$messages[1];
                    $warnings.=$messages[2];
                    $requestId=$messages[3];
                    if ($requestId!=-1)
                    {
                        /*
                         * If the only the user list was changed
                         * we should not create a new record. However,
                         * in order to keep a complete history,
                         * we will create a new record regardless
                         *
                         * Also, if the drequest details where changed,
                         * then only take into account the new details
                         * and not the fact that the user list was
                         * changed in the project request. If not,
                         * autoapprove based on the user list change.
                         */

                        if ($dchanged)
                        {
                            $messages=$drequest->uploadNewEdit($requestId,false);
                            $errors.=$messages[0];
                            $success.=$messages[1];
                            $warnings.=$messages[2];
                        }
                        else
                        {
                            $messages=$drequest->uploadNewEdit($requestId,$uchanged);
                            $errors.=$messages[0];
                            $success.=$messages[1];
                            $warnings.=$messages[2];
                        }
                        
                    }
                }
                else
                {
                    $warnings.="Project $prequest->name was not changed.";
                }

                if (empty($errors))
                {
                    if(!empty($success))
                    {
                        Yii::$app->session->setFlash('success', "$success");
                    }
                    if(!empty($warnings))
                    {
                        Yii::$app->session->setFlash('warning', "$warnings");
                    }
                    
                    return $this->redirect(['project/index']);
                }
            }
        }

        // print_r($prequest);
        // exit(0);

        return $this->render($view_file,['details'=>$drequest, 'project'=>$prequest, 
                    'trls'=>$trls, 'form_params'=>$form_params, 'participating'=>$participating, 'errors'=>$errors, 'upperlimits'=>$upperlimits, 'autoacceptlimits'=>$autoacceptlimits,'maturities'=>$maturities, 'vm_exists'=>$vm_exists, 'ends'=>$ends, 'role'=>$role]);


    }

    public function actionModifyRequest($id)
    {
        $prequest=ProjectRequest::find()->where(['id'=>$id])->one();
        if (empty($prequest))
        {
            return $this->render('error_unauthorized');
        }
        $owner=($prequest->submitted_by==Userw::getCurrentUser()['id']);

        if ( (!$owner) && (!Userw::hasRole('Admin',$superadminAllowed=true)) || (($prequest->status!=ProjectRequest::PENDING) && ($prequest->status!=ProjectRequest::APPROVED) && ($prequest->status!=ProjectRequest::AUTOAPPROVED)) )
        {
            return $this->render('error_unauthorized');
        }

        $start=date('Y-m-d',strtotime($prequest->submission_date));
        $duration=$prequest->duration;

        if(empty($prequest->end_date))
        {
                $ends=date('Y-m-d', strtotime($start. " + $duration months"));
        }
        else
        {
                $ends= explode(' ', $prequest->end_date)[0];
        }

        $prequest->end_date=$ends;

        $vm_exists=0;
        
        $prequest->fillUsernameList();

        $prType=$prequest->project_type;

        $project=Project::find()->where(['latest_project_request_id'=>$id])->one();
        $trls=[];
        $maturities=[];

        $role=User::getRoleType();

        if ($prType==0)
        {
            $drequest=OndemandRequest::find()->where(['request_id'=>$id])->one();
            $view_file='edit_ondemand';
            $upperlimits=OndemandLimits::find()->where(['user_type'=>$role])->one();
            $autoacceptlimits=OndemandAutoaccept::find()->where(['user_type'=>$role])->one();
            $maturities=["developing"=>'Developing', 'testing'=> 'Testing', 'production'=>'Production'];

        }
        else if ($prType==1)
        {
            $drequest=ServiceRequest::find()->where(['request_id'=>$id])->one();
            $drequest->flavour=$drequest->flavourIdName[$drequest->vm_flavour];
            $view_file='edit_service';
            $upperlimits=ServiceLimits::find()->where(['user_type'=>$role])->one();
            $autoacceptlimits=ServiceAutoaccept::find()->where(['user_type'=>$role])->one();

            $vm=VM::find()->where(['request_id'=>$id, 'active'=>true])->one();
            if (!empty($vm))
            {
                // return $this->render('error_service_vm_exist');
                $vm_exists=1;
            }
            $trls[0]='Unspecified';
            for ($i=1; $i<10; $i++)
            {
                $trls[$i]='Level ' . $i;
            }
        }
        else if ($prType==3)
        {
            $drequest=MachineComputeRequest::find()->where(['request_id'=>$id])->one();
            $drequest->flavour=$drequest->flavourIdName[$drequest->vm_flavour];
            $view_file='edit_machine_compute';
            $upperlimits='';
            $autoacceptlimits='';
            $vm=VM::find()->where(['request_id'=>$id, 'active'=>true])->one();
            if (!empty($vm))
            {
                // return $this->render('error_service_vm_exist');
                $vm_exists=1;
            }
        }
        else if ($prType==2)
        {
            $drequest=ColdStorageRequest::find()->where(['request_id'=>$id])->one();
            $view_file='edit_cold_storage';
            //$prequest->duration=36;
            $prequest->duration='2100-1-1';
            $upperlimits=ColdStorageLimits::find()->where(['user_type'=>$role])->one();
            $autoacceptlimits=ColdStorageAutoaccept::find()->where(['user_type'=>$role])->one();
        }

        

        $form_params =
        [
            'action' => URL::to(['project/modify-request', 'id'=>$id]),
            'options' => 
            [
                'class' => 'service_request_form',
                'id'=> "service_request_form"
            ],
            'method' => 'POST'
        ];

        
        

        $errors='';
        $success='';
        $warnings='';
        $username=Userw::getCurrentUser()['username'];
        $user_split=explode('@',$username)[0];
        $participating= (isset($_POST['participating'])) ? $_POST['participating'] : $prequest->usernameList;
        $pold=clone $prequest;
        $dold=clone $drequest;
        
        if ( ($drequest->load(Yii::$app->request->post())) && ($prequest->load(Yii::$app->request->post())) )
        {

            // print_r($drequest);
            // print_r($prequest->name);
            // exit(0);
            $isValid = $prequest->validate();
            $isValid = $drequest->validate() && $isValid;

            // print_r($dold->flavour);
            // print_r($drequest->flavour);
            // exit(0);

            /* 
             * Get participant ids
             */
            $participant_ids_tmp=[];
            foreach ($participating as $participant)
            {
                // $name_exp=explode(' ',$participant);
                // $name=$name_exp[0];
                // $surname=$name_exp[1];
                $username=$participant . '@elixir-europe.org';
                $pid=User::findByUsername($username)->id;
                $participant_ids_tmp[$pid]=null;
            }

            $participant_ids=[];
            foreach ($participant_ids_tmp as $pid => $dummy)
            {
                $participant_ids[]=$pid;
            }

            $prequest->user_list=new yii\db\ArrayExpression($participant_ids, 'int4');
            // print_r($prequest->user_list);
            // print_r("<br /><br />");
            // print_r($pold->user_list);
            // exit(0);
            $pchanged= ProjectRequest::modelChanged($pold,$prequest);
            $dchanged= ProjectRequest::modelChanged($dold,$drequest);
            // exit(0);

            

            if ($isValid)
            {   
                // print_r("ok");
                // exit(0);
                // print_r($prType);
                // exit(0);
                if ($prType==2)
                {
                    $prequest->end_date='2100-1-1';
                }
                if ($prType==1 || $prType==3)
                {
                    if ($dold->flavour != $drequest->flavour)
                    {
                        $dchanged=true;
                    }
                }

                if ($pchanged || $dchanged)
                {
                    // print_r($id);
                    // exit(0);
                    $messages=$prequest->uploadNewEdit($participating,$prType,$id);
                    $errors.=$messages[0];
                    $success.=$messages[1];
                    $warnings.=$messages[2];
                    $requestId=$messages[3];
                    if ($requestId!=-1)
                    {
                        $messages=$drequest->uploadNewEdit($requestId);
                        $errors.=$messages[0];
                        $success.=$messages[1];
                        $warnings.=$messages[2];
                    }
                }
                else
                {
                    $warnings.="Project $prequest->name was not changed.";
                }

                if (empty($errors))
                {
                    if(!empty($success))
                    {
                        Yii::$app->session->setFlash('success', "$success");
                    }
                    if(!empty($warnings))
                    {
                        Yii::$app->session->setFlash('warning', "$warnings");
                    }
                    
                    return $this->redirect(['project/index']);
                }
            }
        }

        return $this->render($view_file,['details'=>$drequest, 'project'=>$prequest, 
                    'trls'=>$trls, 'form_params'=>$form_params, 'participating'=>$participating, 'errors'=>$errors, 'upperlimits'=>$upperlimits, 'autoacceptlimits'=>$autoacceptlimits,'maturities'=>$maturities, 'ends'=>$ends, 'vm_exists'=>$vm_exists,'role'=>$role]);


    }

    public function actionCancelRequest($id)
    {


        $prequest=ProjectRequest::find()->where(['id'=>$id])->one();
        if (empty($prequest))
        {
            return $this->render('error_unauthorized');
        }
        $owner=($prequest->submitted_by==Userw::getCurrentUser()['id']);

        if ( (!$owner) && (!Userw::hasRole('Admin',$superadminAllowed=true)) || (($prequest->status!=ProjectRequest::PENDING) && ($prequest->status!=ProjectRequest::APPROVED) && ($prequest->status!=ProjectRequest::AUTOAPPROVED)) )
        {
            return $this->render('error_unauthorized');
        }

        $prequest->cancel();

        $warnings='';
        $errors='';
        $success='Project request canceled.';

        if(!empty($success))
        {
            Yii::$app->session->setFlash('success', "$success");
        }
        if(!empty($warnings))
        {
            Yii::$app->session->setFlash('warning', "$warnings");
        }
        
        return $this->redirect(['project/index']);
    }

    public function actionCancelProject($id)
    {

        $prequest=ProjectRequest::find()->where(['id'=>$id])->one();
        if (empty($prequest))
        {
            return $this->render('error_unauthorized');
        }
        $owner=($prequest->submitted_by==Userw::getCurrentUser()['id']);

        if ( (!$owner) && (!Userw::hasRole('Admin',$superadminAllowed=true)) || (($prequest->status!=ProjectRequest::APPROVED) && ($prequest->status!=ProjectRequest::AUTOAPPROVED)) )
        {
            return $this->render('error_unauthorized');
        }

        if ($prequest->project_type==ProjectRequest::SERVICE || $prequest->project_type==ProjectRequest::MACHINECOMPUTE)
        {
            $vm=VM::find()->where(['request_id'=>$id, 'active'=>true])->one();
            if (!empty($vm))
            {
                return $this->render('error_service_vm_exist');
            }
        }
        $prequest->cancelActiveProject();

        $warnings='';
        $errors='';
        $success="Project $prequest->name canceled.";

        if(!empty($success))
        {
            Yii::$app->session->setFlash('success', "$success");
        }
        if(!empty($warnings))
        {
            Yii::$app->session->setFlash('warning', "$warnings");
        }
        
        return $this->redirect(['project/index']);
    }

    public function actionRetrieveWinPassword($id)
    {
        $owner=Project::userInProject($id);

        if ( (empty($owner)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->renderPartial('vm_password',['message'=>'You are not authorized to view the VM password.']);
        }

        $existing=Vm::find()->where(['project_id'=>$id])->andWhere(['active'=>true])->one();
        $password=$existing->retrieveWinPassword();

        return $this->renderPartial('vm_password',['message'=>$password]);
    }

    public function actionModeratorEmailNotifications()
    {


        $user=Userw::getCurrentUser();
        $user_id=$user->id;
        $user_notifications=EmailEvents::find()->where(['user_id'=>$user_id])->one();
        if(empty($user_notifications))
        {
        		$user_notifications=new EmailEvents;
        		$user_notifications->user_id=$user_id;
        		$user_notifications->save();
        		
       	}

        $smtp=Smtp::find()->one();
        $smtp_config=true;
        if((empty($smtp->host)) || (empty($smtp->port)) || (empty($smtp->username)) || (empty($smtp->password)) || (empty($smtp->encryption)))
        {
            Yii::$app->session->setFlash('danger', "SMTP is not configured properly to enable email notifications");
            $smtp_config=false;
        }
       	if($user->load(Yii::$app->request->post()) && $user_notifications->load(Yii::$app->request->post()))
        {
        	
            $user->update();
           	$user_notifications->update();
       		Yii::$app->session->setFlash('success', "Your changes have been successfully submitted");
            return $this->redirect(['moderator-options']);
        }

        return $this->render('moderator_email_notifications', ['user'=>$user, 'user_notifications'=>$user_notifications,
            'smtp_config'=>$smtp_config]);
    }


    public  function actionStorageVolumes()
    {
        $results=HotVolumes::getHotVolumesInfo();
        $services=[];
        $machines=[];
        foreach ($results as $res) 
        {
            if($res['vm_type']==1)
            {
                $services[$res['id']]=$res;
                if(!empty($res['vm_id']))
                {
                    $vm=Vm::find()->where(['id'=>$res['vm_id']])->one();
                    $project_request=ProjectRequest::find()->where(['id'=>$vm->request_id])->one();
                    $services[$res['id']]['24/7 name']=$project_request->name;
                }
            }
            else
            {
                $machines[$res['id']]=$res;
                if(!empty($res['vm_id']))
                {
                    $vm=VmMachines::find()->where(['id'=>$res['vm_id']])->one();
                    $project_request=ProjectRequest::find()->where(['id'=>$vm->request_id])->one();
                    $machines[$res['id']]['machine name']=$project_request->name;
                }
            }

        }
        return $this->render('storage_volumes', ['services'=>$services, 'machines'=>$machines, 'results'=>$results]);
    }

    public  function actionStorageVolumesAdmin()
    {
        $results=HotVolumes::getHotVolumesInfoAdmin();
        $services=[];
        $machines=[];
        foreach ($results as $res) 
        {
            if($res['vm_type']==1)
            {
                $services[$res['id']]=$res;
                if(!empty($res['vm_id']))
                {
                    $vm=Vm::find()->where(['id'=>$res['vm_id']])->one();
                    $project_request=ProjectRequest::find()->where(['id'=>$vm->request_id])->one();
                    $services[$res['id']]['24/7 name']=$project_request->name;
                }
            }
            else
            {
                $machines[$res['id']]=$res;
                if(!empty($res['vm_id']))
                {
                    $vm=VmMachines::find()->where(['id'=>$res['vm_id']])->one();
                    $project_request=ProjectRequest::find()->where(['id'=>$vm->request_id])->one();
                    $machines[$res['id']]['machine name']=$project_request->name;
                }
            }

        }
        return $this->render('storage_volumes', ['services'=>$services, 'machines'=>$machines, 'results'=>$results]);
    }

    public  function actionDeleteVolume($id)
    {
        $hotvolume=HotVolumes::find()->where(['id'=>$id])->one();
        $project_id=$hotvolume->project_id;
        $volume_id=$hotvolume->volume_id;
        $authenticate=$hotvolume::authenticate();
        $token=$authenticate[0];
        $message=$authenticate[1];
        if(!$token=='')
        {
            $deleted=HotVolumes::deleteVolume($volume_id,$token,$id);
            $success=$deleted[0];
            $message=$deleted[1];
        }
        if($success)
        {
            Yii::$app->session->setFlash('success', "Volume has been successfully deleted");
        }
        else
        {
            Yii::$app->session->setFlash('danger', "$message");
        }
        return $this->redirect(['storage-volumes', 'id'=>$project_id]);
    }

    public  function actionManageVolume($id,$service)
    {
        $hotvolume=HotVolumes::find()->where(['id'=>$id])->one();
        if (empty($hotvolume))
        {
            return $this->render('error_unauthorized');
        }
        $participant=Project::userInProject($hotvolume->project_id);

        if ( (empty($participant)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }

        $user_id=Userw::getCurrentUser()['id'];
        
        $hotvolume->initialize($hotvolume->vm_type);
        $project_id=$hotvolume->project_id;
        $volume_id=$hotvolume->volume_id;
        $vm_id=$hotvolume->vm_id;
        $name=$hotvolume->name;
        $project_name='';
        $vms_dropdown=[];
        if($vm_id==null)
        {
           
            if($hotvolume->vm_type==1)
            {
                $vms=HotVolumes::getVolumeServices($hotvolume->volume_id,$user_id);
               
            }
            else
            {   
                $vms=HotVolumes::getVolumeMachines($hotvolume->volume_id,$user_id);
                
            }

            foreach ($vms as $vm) 
            {
                $vms_dropdown[$vm['id']]=$vm['name'];
            }
        }
        else
        {
            if($hotvolume->vm_type==1)
            {
                $vm=Vm::find()->where(['id'=>$vm_id])->one();

            }
            else
            {
                $vm=VmMachines::find()->where(['id'=>$vm_id])->one();
            }

            $project_id=$vm->project_id;
            $project=Project::find()->where(['id'=>$project_id])->one();
            $project_name=$project->name;
            $vms_dropdown[$vm_id]=$project_name;
            
        }
        if($hotvolume->vm_id==null)
        {
            if($hotvolume->load(Yii::$app->request->post()))
            {
                $new_vm_id=$hotvolume->vm_id;
                $vm_type=$hotvolume->vm_type;
                $hotvolume->initialize($vm_type);
                $authenticate=$hotvolume->authenticate();
                $token=$authenticate[0];
                $message=$authenticate[1];
                if(!$token=='')
                {
                    $attach=$hotvolume->attachVolume($volume_id,$new_vm_id,$token,$vm_type);
                    $hotvolume->mountpoint=$attach;
                    $hotvolume->vm_id=$new_vm_id;
                    
                }
                $hotvolume->update();
                Yii::$app->session->setFlash('success', "Volume has been attached to VM");
                return $this->redirect(['storage-volumes', 'project_id'=>$hotvolume->project_id]);
            }
        }
        
        return $this->render('manage_volumes', ['hotvolume'=>$hotvolume, 'vms_dropdown'=>$vms_dropdown, 'name'=>$name, 'project_id'=>$project_id, 'volume_id'=>$volume_id, 'vm_id'=>$vm_id, 'project_name'=>$project_name]);
    }

    public function actionDetachVolumeFromVm($volume_id,$vm_id)
    {
        $hotvolume=HotVolumes::find()->where(['volume_id'=>$volume_id])->one();
        if (empty($hotvolume))
        {
            return $this->render('error_unauthorized');
        }

        $participant=Project::userInProject($hotvolume->project_id);
        if ( (empty($participant)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }

        $vm_type=$hotvolume->vm_type;
        $hotvolume->initialize($vm_type);
        $authenticate=$hotvolume->authenticate();
        $token=$authenticate[0];
        $message=$authenticate[1];
        if(!$token=='')
        {
            $detach=$hotvolume->detachVolume($volume_id,$vm_id,$token,$vm_type);
            if($detach[0])
            {
                Yii::$app->session->setFlash('success', "Volume has been detached from VM");
                return $this->redirect(['storage-volumes', 'project_id'=>$hotvolume->project_id]);
            }
            else
            {
                Yii::$app->session->setFlash('danger', $detach[1]);
                return $this->redirect(['storage-volumes', 'project_id'=>$hotvolume->project_id]);
            }
        }
        else
        {
            Yii::$app->session->setFlash('danger', $message);
            return $this->redirect(['storage-volumes', 'project_id'=>$hotvolume->project_id]);
        }

    }

}


