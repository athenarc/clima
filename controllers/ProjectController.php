<?php

namespace app\controllers;

use app\models\ColdStorageAutoaccept;
use app\models\ColdStorageLimits;
use app\models\ColdStorageRequest;
use app\models\Configuration;
use app\models\EmailEventsModerator;
use app\models\EmailEventsUser;
use app\models\HotVolumes;
use app\models\MachineComputeLimits;
use app\models\MachineComputeRequest;
use app\models\OndemandAutoaccept;
use app\models\OndemandLimits;
use app\models\OndemandRequest;
use app\models\Project;
use app\models\ProjectRequest;
use app\models\ProjectRequestCold;
use app\models\ServiceAutoaccept;
use app\models\ServiceLimits;
use app\models\ServiceRequest;
use app\models\Smtp;
use app\models\User;
use app\models\Vm;
use app\models\VmMachines;
use webvimark\modules\UserManagement\models\User as Userw;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\UploadedFile;


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
            /*
             * Remove duplicate users before validating
             */
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

            $isValid = $projectModel->validate();
            $isValid = $serviceModel->validate() && $isValid;

            if ($isValid)
            {   
                
                $messages=$projectModel->uploadNew($project_types['service']);
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
                    	EmailEventsModerator::NotifyByEmail('new_project', $project_id,$submitted_email);
                    }
                    else
                    {
                        Yii::$app->session->setFlash('success', "$message_autoaccept");
                    	EmailEventsModerator::NotifyByEmail('project_decision', $project_id,$message_autoaccept);
                        EmailEventsUser::NotifyByEmail('project_decision', $project_id,$message_autoaccept);
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
        $num_vms_dropdown=[];
        /*
         * Create dropdown for the number of VMs
         */
        for ($i=1; $i<31; $i++)
            $num_vms_dropdown[$i]=$i;

        
        if ( ($serviceModel->load(Yii::$app->request->post())) && ($projectModel->load(Yii::$app->request->post())) )
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
            $isValid = $projectModel->validate();
            $isValid = $serviceModel->validate() && $isValid;
            $isValid = $projectModel->machinesDuration30() && $isValid;

            if ($isValid)
            {   

                
                $messages=$projectModel->uploadNew($project_types['machine_compute']);
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
        

        return $this->render('new_machine_compute_request',['service'=>$serviceModel, 'project'=>$projectModel,  'form_params'=>$form_params, 'participating'=>$participating, 'errors'=>$errors, 'new_project_allowed'=>$new_project_allowed,'num_vms_dropdown'=>$num_vms_dropdown]);



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


        if (($role=='gold') || (Userw::hasRole('Admin', $superadminAllowed=true)) )
        {
            $vm_types=[1=>'24/7 service', 2=>'On-demand computation machines'];

        }
        else
        {
            $vm_types=[1=>'24/7 service'];
        }

        $multiple=[];
        for ($i=1; $i<=30; $i++)
        {
            $multiple[$i]=$i;
        }
 
        
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
            $isValid = $projectModel->validate();
            $isValid = $coldStorageModel->validate() && $isValid;
            $projectModel->end_date='2100-1-1';

            if ($isValid)
            {    
                
                $messages=$projectModel->uploadNew($project_types['coldstorage']);
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
                        EmailEventsModerator::NotifyByEmail('new_project', $project_id,$submitted_email);
                    }
                    else
                    {
                        Yii::$app->session->setFlash('success', "$message_autoaccept");
                        EmailEventsModerator::NotifyByEmail('project_decision', $project_id,$message_autoaccept);
                        EmailEventsUser::NotifyByEmail('project_decision', $project_id,$message_autoaccept);
                    }
                    
                    return $this->redirect(['project/index']);

                }
            }
        }
        
        
        return $this->render('new_cold_storage_request',['coldStorage'=>$coldStorageModel, 'project'=>$projectModel, 
                'form_params'=>$form_params, 'participating'=>$participating, 'errors'=>$errors,
                 'upperlimits'=>$upperlimits, 'autoacceptlimits'=>$autoacceptlimits,'autoaccept_allowed' => $autoaccept_allowed, 'role'=>$role, 
                 'new_project_allowed'=>$new_project_allowed, 'vm_types'=>$vm_types, 'multiple' => $multiple]);


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

            $isValid = $projectModel->validate();
            $isValid = $ondemandModel->validate() && $isValid;

            if ($isValid)
            {

                $messages=$projectModel->uploadNew($project_types['ondemand']);
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
                        EmailEventsModerator::NotifyByEmail('new_project', $project_id,$submitted_email);

                    }
                    else
                    {
                        Yii::$app->session->setFlash('success', "$message_autoaccept");
                        EmailEventsModerator::NotifyByEmail('project_decision', $project_id,$message_autoaccept);
                        EmailEventsUser::NotifyByEmail('project_decision', $project_id,$message_autoaccept);
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
        $project_request = ProjectRequest::findOne($id);
        $project_status = ProjectRequest::STATUSES[$project_request->status];

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


        $resourcesStats=[];

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
            $vm_type="";
            if ($project_request->status == 0) { // Poll OpenStack only in case the request is pending
                $previouslyApprovedProjectRequest = $project_request->getPreviouslyApprovedProjectRequest();
                if (isset($previouslyApprovedProjectRequest)) {
                    $diff = $project_request->getFormattedDiff($previouslyApprovedProjectRequest);
                }
            }

        }
        else if ($project_request->project_type==1)
        {
            $details = ServiceRequest::findOne(['request_id' => $id]);
            $view_file = 'view_service_request';
            $type = "24/7 Service";
            $vm_type = "";
            // Required stats: cores, ram , ips, disk
            if ($project_request->status == 0) { // Poll OpenStack only in case the request is pending
                $model = new Vm;
                session_write_close();
                $previouslyApprovedProjectRequest = $project_request->getPreviouslyApprovedProjectRequest();

                // If the project is actually a modification, then there's no need to poll for all the resources; just
                // the ones that have been modified in the newer request
                if (isset($previouslyApprovedProjectRequest)) {
                    $diff=$project_request->getFormattedDiff($previouslyApprovedProjectRequest);
                    // Check if cores or ram have been modified in the new request
                    if (isset($diff['details']['num_of_cores']) || isset($diff['details']['ram'])) {
                        $openStackCpuAndRam = Vm::getOpenstackCpuAndRamStatistics();

                        if (isset($diff['details']['num_of_cores'])) {
                            $openStackCpuAndRam['num_of_cores']['requested'] = $diff['details']['num_of_cores']['difference'];
                            $resourcesStats['num_of_cores'] = $openStackCpuAndRam['num_of_cores'];
                        } else {
                            unset($openStackCpuAndRam['num_of_cores']);
                        }
                        if (isset($diff['details']['ram'])) {
                            $openStackCpuAndRam['ram']['requested'] = $diff['details']['ram']['difference'];
                            $resourcesStats['ram'] = $openStackCpuAndRam['ram'];
                        } else {
                            unset($openStackCpuAndRam['ram']);
                        }
                    }
                    if (isset($diff['details']['num_of_ips'])) {
                        $openStackIps = Vm::getOpenstackIpStatistics();
                        $openStackIps['num_of_ips']['requested'] = $diff['details']['num_of_ips']['difference'];
                        $resourcesStats['num_of_ips'] = $openStackIps['num_of_ips'];
                    }
                    if (isset($diff['details']['disk'])) {
                        $openStackStorage = Vm::getOpenstackStorageStatistics();
                        $openStackStorage['storage']['requested'] = $diff['details']['disk']['difference'];
                        $resourcesStats['disk'] = $openStackStorage['storage'];
                    }
                    if (session_status()!==PHP_SESSION_ACTIVE) session_start();
                } // If it is not a modification, then poll for all required resources
                else {
                    $openStackCpuAndRam = Vm::getOpenstackCpuAndRamStatistics();
                    $openStackIps = Vm::getOpenstackIpStatistics();
                    $openStackStorage = Vm::getOpenstackStorageStatistics();
                    if (session_status()!==PHP_SESSION_ACTIVE) session_start();

                    $openStackCpuAndRam['num_of_cores']['requested'] = $details->num_of_vms * $details->num_of_cores;
                    $openStackCpuAndRam['ram']['requested'] = $details->num_of_vms * $details->ram;
                    $openStackIps['num_of_ips']['requested'] = $details->num_of_vms * $details->num_of_ips;
                    // this should equal always to 1 since for 24/7 services, only 1 vm is allocated with only 1 ip
                    $openStackStorage['disk'] = $openStackStorage['storage'];
                    unset($openStackStorage['storage']);
                    $openStackStorage['disk']['requested'] = $details->num_of_vms * $details->disk;

                    $resourcesStats = array_merge($openStackCpuAndRam, $openStackIps, $openStackStorage);
                    error_log(serialize($resourcesStats));
                }
            }
        }
        else if ($project_request->project_type==2)
        {
            $details=ColdStorageRequest::findOne(['request_id'=>$id]);
            $vm_type="24/7 Service";
            $view_file='view_cold_request';
            $type="Storage volumes";
            if($details->vm_type==2)
            {
                $vm_type="On-demand computation machines";
                $modelClass = VmMachines::class;
            }
            else {
                $modelClass = Vm::class;
            }
            $model = new $modelClass;
            // Required stats: cores, ram , ips, disk
            if ($project_request->status == 0) {
                $previouslyApprovedProjectRequest = $project_request->getPreviouslyApprovedProjectRequest();
                // If the project is actually a modification, then there's no need to poll for all the resources; just
                // the ones that have been modified in the newer request
                if (isset($previouslyApprovedProjectRequest)) {
                    $diff=$project_request->getFormattedDiff($previouslyApprovedProjectRequest);
                    if (isset($diff['details']['storage'])) {
                        session_write_close();
                        $openStackStorage = $modelClass::getOpenstackStorageStatistics();
                        if (session_status()!==PHP_SESSION_ACTIVE) session_start();
                        $openStackStorage['storage']['requested'] = $diff['details']['storage']['difference'];
                        $resourcesStats['storage'] = $openStackStorage['storage'];
                    }
                } // If it is not a modification, then poll for all required resources
                else {
                    session_write_close();
                    $openStackStorage = $modelClass::getOpenstackStorageStatistics();
                    if (session_status()!==PHP_SESSION_ACTIVE) session_start();
                    $totalRequestedStorage = $details->storage * $details->num_of_volumes;
                    $openStackStorage['storage']['requested'] = $totalRequestedStorage;
                    $resourcesStats['storage'] = $openStackStorage['storage'];
                }
            }
        }
        else if ($project_request->project_type==3)
        {
            $details = MachineComputeRequest::findOne(['request_id' => $id]);
            $view_file = 'view_machine_compute_request';
            $type = "On-demand computation machines";
            $vm_type = "";
            // Required stats: cores, ram , ips, disk
            if ($project_request->status == 0) { // Poll OpenStack only in case the request is pending
                $model = new VmMachines;
                session_write_close();
                $previouslyApprovedProjectRequest = $project_request->getPreviouslyApprovedProjectRequest();
                // If the project is actually a modification, then there's no need to poll for all the resources; just
                // the ones that have been modified in the newer request
                if (isset($previouslyApprovedProjectRequest)) {
                    $diff=$project_request->getFormattedDiff($previouslyApprovedProjectRequest);

                    // Check if cores or ram have been modified in the new request
                    if (isset($diff['details']['num_of_cores']) || isset($diff['details']['ram'])) {
                        $openStackCpuAndRam = VmMachines::getOpenstackCpuAndRamStatistics();

                        if (isset($diff['details']['num_of_cores'])) {
                            $openStackCpuAndRam['num_of_cores']['requested'] = $diff['details']['num_of_cores']['difference'];
                            $resourcesStats['num_of_cores'] = $openStackCpuAndRam['num_of_cores'];
                        } else {
                            unset($openStackCpuAndRam['num_of_cores']);
                        }
                        if (isset($diff['details']['ram'])) {
                            $openStackCpuAndRam['ram']['requested'] = $diff['details']['ram']['difference'];
                            $resourcesStats['ram'] = $openStackCpuAndRam['ram'];
                        } else {
                            unset($openStackCpuAndRam['ram']);
                        }
                    }
                    if (isset($diff['details']['num_of_ips'])) {
                        $openStackIps = VmMachines::getOpenstackIpStatistics();
                        $openStackIps['num_of_ips']['requested'] = $diff['details']['num_of_ips']['difference'];
                        $resourcesStats['num_of_ips'] = $openStackIps['num_of_ips'];
                    }
                    if (isset($diff['details']['disk'])) {
                        $openStackStorage = VmMachines::getOpenstackStorageStatistics();
                        $openStackStorage['storage']['requested'] = $diff['details']['disk']['difference'];
                        $resourcesStats['disk'] = $openStackStorage['storage'];
                    }
                    if (session_status()!==PHP_SESSION_ACTIVE) session_start();
                } // If it is not a modification, then poll for all required resources
                else {
                    $openStackCpuAndRam = VmMachines::getOpenstackCpuAndRamStatistics();
                    $openStackIps = VmMachines::getOpenstackIpStatistics();
                    $openStackStorage = VmMachines::getOpenstackStorageStatistics();
                    if (session_status()!==PHP_SESSION_ACTIVE) session_start();

                    $openStackCpuAndRam['num_of_cores']['requested'] = $details->num_of_vms * $details->num_of_cores;
                    $openStackCpuAndRam['ram']['requested'] = $details->num_of_vms * $details->ram;
                    $openStackIps['num_of_ips']['requested'] = $details->num_of_vms * $details->num_of_ips;
                    // this should equal always to num_of_vms since for on-demand computation machines, each vm
                    // is only bound to 1 ip
                    $openStackStorage['disk'] = $openStackStorage['storage'];
                    unset($openStackStorage['storage']);
                    $openStackStorage['disk']['requested'] = $details->num_of_vms * $details->disk;

                    $resourcesStats = array_merge($openStackCpuAndRam, $openStackIps, $openStackStorage);
                }
            }
        }

        // Configure general information about statistics and visualizations
        $excessiveRequest=false;
        foreach ($resourcesStats as $resourceStats) {
            if ($resourceStats['current']+$resourceStats['requested']>=$resourceStats['total']) {
                $excessiveRequest=true;
                break;
            }
        }
        $resourcesStats['general'] = [
            'excessiveRequest' => $excessiveRequest
        ];

        $requestHistory = ['isMod' => isset($diff)];
        if (isset($diff)) $requestHistory['diff'] = $diff;

        $usernames = [];
        // If the project request is a modification, in which the user_list has been modified then the user list will
        // have been stored in the corresponding diff field
        if ($requestHistory['isMod'] && isset($diff['project']['user_list'])) {
            $usernames = $diff['project']['user_list']['current'];
        } else {
            $users = User::find()->where(['IN', 'id', $project_request->user_list])->all();
            $mapUsername = function ($usr) {
                return explode('@', $usr->username)[0];
            };
            $usernames = array_map($mapUsername, $users);
        }

        $submitted = User::find()->where(['id' => $project_request->submitted_by])->one();
        $project_owner = ($submitted->username == Userw::getCurrentUser()['username']);
        /*
         * Fix username so that it is shown without @
         */
        // $users=User::find()->where(['IN','id',$project_request->user_list])->all();
        $submitted->username = explode('@', $submitted->username)[0];
        // $users=User::returnList($project->user_list);
        $number_of_users = count($usernames);
        $maximum_number_users = $project_request->user_num;

        $user_list = join(', ', array_values($usernames));
        $expired = 0;

        return $this->render($view_file, ['project' => $project_request, 'details' => $details,
            'filter' => $filter, 'usage' => $usage, 'user_list' => $user_list, 'submitted' => $submitted,
            'request_id' => $id, 'type' => $type, 'ends' => $ends, 'start' => $start,
            'remaining_time' => $remaining_time, 'project_owner' => $project_owner,
            'number_of_users' => $number_of_users, 'maximum_number_users' => $maximum_number_users,
            'remaining_jobs' => $remaining_jobs, 'expired' => $expired, 'resourcesStats' => $resourcesStats,
            'requestHistory' => $requestHistory, 'project_status' => $project_status]);


    }

    public function actionViewRequestUser($id,$filter='all',$return='index')
    {

        ProjectRequest::recordViewed($id);
        $project_request=ProjectRequest::findOne($id);
        $project=Project::find()->where(['id'=>$project_request->project_id])->one();
        
        $user_list=$project_request->user_list->getValue();
        $users=User::find()->where(['id'=>$user_list])->all();

        $current_user=Userw::getCurrentUser();

        // if ( (!in_array($current_user['id'], $user_list)) && (!Userw::hasRole('Moderator',$superadminAllowed=true))
        //         && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        // {
        //     return $this->render('error_unauthorized');
        // }

        // if ( ($return!='admin') || (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        // {
        //     $return='index';
        // }
      

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
            $active_vms=VM::find()->where(['project_id'=>$project->id, 'active'=>'t'])->count();
            $total_vms=VM::find()->where(['project_id'=>$project->id])->count();
            $usage['active_vms']=$active_vms;
            $usage['total_vms']=$total_vms;
            

        }
        else if ($project_request->project_type==3)
        {
            $details=MachineComputeRequest::findOne(['request_id'=>$id]);
            $view_file='view_machine_compute_request_user';
            $type="On-demand computation machines";
            $active_vms=VmMachines::find()->where(['project_id'=>$project->id, 'active'=>'t'])->count();
            $total_vms=VmMachines::find()->where(['project_id'=>$project->id])->count();
            $usage['active_vms']=$active_vms;
            $usage['total_vms']=$total_vms;
           
        }
        else if ($project_request->project_type==2)
        {
            $details=ColdStorageRequest::findOne(['request_id'=>$id]);
            $view_file='view_cold_request_user';
            $type="Storage volumes";
            $remaining_jobs=0;
            $active_volumes=HotVolumes::find()->where(['project_id'=>$project->id, 'active'=>'t'])->count();
            $total_volumes=HotVolumes::find()->where(['project_id'=>$project->id])->count();
            $usage['active_volumes']=$active_volumes;
            $usage['total_volumes']=$total_volumes;
        }
        

        $submitted=User::find()->where(['id'=>$project_request->submitted_by])->one();
        $project_owner= ($submitted->username==Userw::getCurrentUser()['username']);
        /*
         * Fix username so that it is shown without @
         */
        $submitted->username=explode('@',$submitted->username)[0];
        
        $maximum_number_users=$project_request->user_num;


        foreach ($users as $user)
        {
            $username_list[]=explode('@',$user->username)[0];
        }
        $username_list=implode(', ',$username_list);


        $number_of_users=count($users);
        
        $expired=0;

        return $this->render($view_file,['project'=>$project_request,'details'=>$details, 'return'=>$return,
            'filter'=>$filter,'usage'=>$usage,'user_list'=>$username_list, 'submitted'=>$submitted,'request_id'=>$id, 'type'=>$type, 'ends'=>$ends, 'start'=>$start, 'remaining_time'=>$remaining_time,
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


    public function actionConfigureVm($id,$backTarget='s')
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
            
            $project=Project::find()->where(['id'=>$id])->one();
            $latest_project_request_id=$project->latest_project_request_id;
            $service=ServiceRequest::find()->where(['request_id'=>$latest_project_request_id])->one();

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
                    $result=$model->createVM($latest_project_request_id,$service, $imageDD, $service->disk);
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
                        $existing->getServerStatus();
                        session_start();
    
                        return $this->render('vm_details',['model'=>$existing, 'requestId'=>$id, 'service'=>$service,'backTarget'=>$backTarget]);
                    }
                }
            }
            
            return $this->render('configure_vm',['model'=>$model,'form_params'=>$form_params,'imageDD'=>$imageDD,'service'=>$service,'backTarget'=>$backTarget]);
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
            $existing->getServerStatus();
            session_start();
            return $this->render('vm_details',['model'=>$existing,'requestId'=>$id, 'service'=>$service, 'additional_storage'=>$additional_storage]);
        }
        

    }

    public function actionMachineComputeConfigureVm($id,$multOrder=1,$backTarget='s')
    {
        /*
         * Check that someone is not trying to do something illegal
         * by "hacking" at URLs 
         */
        $owner=Project::userInProject($id);

        if ( (empty($owner)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }

        $existing=VmMachines::find()->where(['project_id'=>$id])->andWhere(['active'=>true])->andWhere(['project_multiple_order'=>$multOrder])->one();

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

            session_write_close();
            $avResources=VmMachines::getOpenstackAvailableResources();
            session_start();

            $project=Project::find()->where(['id'=>$id])->one();
            $latest_project_request_id=$project->latest_project_request_id;
            $service=MachineComputeRequest::find()->where(['request_id'=>$latest_project_request_id])->one();

            /*
             * If someone is trying to do something fishy with the 
             * parameters in the URL to create more VMs than allowed,
             * stop them.
             */
            if ($service->num_of_vms < $multOrder)
            {
                return $this->render('error_unauthorized');
            }
            
            if ( ($service->num_of_ips>$avResources[2]) || ($service->ram>$avResources[1]) || ($service->num_of_cores > $avResources[0]) || ($service->storage > $avResources[3]) )
            {
                return $this->render('service_unavailable_resources');
            }
            
            $imageDD=VmMachines::getOpenstackImages();

            $form_params =
            [
                'action' => URL::to(['project/machine-compute-configure-vm','id'=>$id,'multOrder'=>$multOrder,'backTarget'=>'m']),
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
                if ($model->validate())
                {
                    
                    $model->project_multiple_order=$multOrder;
                    session_write_close();
                    $result=$model->createVM($latest_project_request_id,$service, $imageDD,$service->disk);
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
                        $existing=VmMachines::find()->where(['project_id'=>$id])->andWhere(['active'=>true])->one();
                        $existing->getConsoleLink();
    
                        return $this->render('vm_machines_details',['model'=>$existing, 'requestId'=>$id, 'service'=>$service,'backTarget'=>$backTarget]);
                    }
                }
            }
            
            return $this->render('configure_vm',['model'=>$model,'form_params'=>$form_params,'imageDD'=>$imageDD,'service'=>$service,'backTarget'=>$backTarget,'project_id'=>$project->id]);
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



        
            session_write_close();
            $existing->getConsoleLink();
            $existing->getServerStatus();
            session_start();
            
             return $this->render('vm_machines_details',['model'=>$existing,'requestId'=>$id, 'service'=>$service, 'additional_storage'=>$additional_storage,'backTarget'=>$backTarget]);
        }
        

    }


    public function actionMachineComputeAccessProject($id)
    {
        $owner=Project::userInProject($id);

        /*
         * Check that someone is not trying to do something funny via urls
         */
        if ( (empty($owner)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }
        $project=Project::find()->where(['id'=>$id])->one();
        $details=MachineComputeRequest::find()->where(['request_id'=>$project->latest_project_request_id])->one();

        /*
         * if project has only one vm
         * then redirect to the appropriate 
         * configure vm action
         */
        if ($details->num_of_vms == 1 )
        {
            $this->redirect(['/project/machine-compute-configure-vm','id'=>$id]);
        }
        /*
         * I'm not writing an else action here
         * because if there is a redirect you
         * certainly go away from here
         */
        /*
         * Get available VMs if any
         */
        $existing=VmMachines::find()->where(['project_id'=>$id])->andWhere(['active'=>true])->all();
        $vms=[];
        $vm_ids=[];
        $vm_match=[];
        foreach ($existing as $vm)
        {
            $vm_ids[]=$vm->id;
            if (!isset($vm_match[$vm->id]))
            {
                $vm_match[$vm->id]=[];
            }
            $vm_match[$vm->id][]=$vm->project_multiple_order;
            $vms[$vm->project_multiple_order]=$vm;
        }
        $volumes=HotVolumes::find()->where(['vm_id'=>$vm_ids])->all();
        $storage=[];

        foreach ($volumes as $volume)
        {

            $vmorder=$vm_match[$volume->vm_id];

            foreach ($vmorder as $ord)
            {
                if (!isset($storage[$ord]))
                {
                    $storage[$ord]=[];
                }
                $storage[$ord][]=['name'=>$volume->name,'mountpoint'=>$volume->mountpoint];
            }
                
        }


        return $this->render('machines_multiple',['num_of_vms'=>$details->num_of_vms,'vms'=>$vms, 'project_id'=>$project->id,'storage' => $storage]);


    }


    public function actionGetVmStatus($vm_id='')
    {
        if (empty($vm_id))
        {
            return $this->asJson('');
        }

        $vm = new Vm();
        $vm->vm_id=$vm_id;
        $status=empty($vm->getServerStatus($vm_id))? '' : $vm->status;
        
        return $this->asJson($status);

    }

    public function actionStartVm($vm_id='')
    {
        if (empty($vm_id))
        {
            Yii::$app->response->statusCode = 500;
            return;
        }
        $vm=new Vm();
        $vm->vm_id=$vm_id;
        $ok=$vm->startVM();
        // $ok='success';
        if ($ok='success')
        {
            Yii::$app->response->statusCode = 200;
            return;
        }
        else
        {
            Yii::$app->response->statusCode = 500;
            return;
        }
    }

    public function actionStopVm($vm_id='')
    {
        if (empty($vm_id))
        {
            Yii::$app->response->statusCode = 500;
            return;
        }
        $vm=new Vm();
        $vm->vm_id=$vm_id;
        $ok=$vm->stopVM();
        if ($ok='success')
        {
            Yii::$app->response->statusCode = 200;
            return;
        }
        else
        {
            Yii::$app->response->statusCode = 500;
            return;
        }
    }

    public function actionRebootVm($vm_id='')
    {
        if (empty($vm_id))
        {
            Yii::$app->response->statusCode = 500;
            return;
        }
        $vm=new Vm();
        $vm->vm_id=$vm_id;
        $ok=$vm->rebootVM();
        if ($ok='success')
        {
            Yii::$app->response->statusCode = 200;
            return;
        }
        else
        {
            Yii::$app->response->statusCode = 500;
            return;
        }
    }

    public function actionGetVmMachinesStatus($vm_id='')
    {
        if (empty($vm_id))
        {
            return $this->asJson('');
        }

        $vm = new VmMachines();
        $vm->vm_id=$vm_id;
        $status=empty($vm->getServerStatus($vm_id))? '' : $vm->status;
        
        return $this->asJson($status);

    }

    public function actionStartVmMachines($vm_id='')
    {
        if (empty($vm_id))
        {
            Yii::$app->response->statusCode = 500;
            return;
        }
        $vm=new VmMachines();
        $vm->vm_id=$vm_id;
        $ok=$vm->startVM();
        // $ok='success';
        if ($ok='success')
        {
            Yii::$app->response->statusCode = 200;
            return;
        }
        else
        {
            Yii::$app->response->statusCode = 500;
            return;
        }
    }

    public function actionStopVmMachines($vm_id='')
    {
        if (empty($vm_id))
        {
            Yii::$app->response->statusCode = 500;
            return;
        }
        $vm=new VmMachines();
        $vm->vm_id=$vm_id;
        $ok=$vm->stopVM();
        if ($ok='success')
        {
            Yii::$app->response->statusCode = 200;
            return;
        }
        else
        {
            Yii::$app->response->statusCode = 500;
            return;
        }
    }

    public function actionRebootVmMachines($vm_id='')
    {
        if (empty($vm_id))
        {
            Yii::$app->response->statusCode = 500;
            return;
        }
        $vm=new VmMachines();
        $vm->vm_id=$vm_id;
        $ok=$vm->rebootVM();
        if ($ok='success')
        {
            Yii::$app->response->statusCode = 200;
            return;
        }
        else
        {
            Yii::$app->response->statusCode = 500;
            return;
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
        /*
         * Remove all volume attachments from the database before deleting.
         * No point in detaching from the actual VM, OpenStack will take care of it
         */

        $volumes=HotVolumes::find()->where(['vm_id'=>$vm->id])->all();
        foreach ($volumes as $volume)
        {
            $volume->vm_id=null;
            $volume->mountpoint=null;
            $volume->save();
        }

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

        /*
         * Remove all volume attachments from the database before deleting.
         * No point in detaching from the actual VM, OpenStack will take care of it
         */

        $volumes=HotVolumes::find()->where(['vm_id'=>$vm->id])->all();
        foreach ($volumes as $volume)
        {
            $volume->vm_id=null;
            $volume->mountpoint=null;
            $volume->save();
        }

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

        $filters_search=['user'=>Yii::$app->request->get('username',''), 'project'=>Yii::$app->request->get('project_name',''), 'ip'=>Yii::$app->request->get('ip_address','')];

        $results=ProjectRequest::getVmList($filter, $filters_search['user'], $filters_search['project'], $filters_search['ip']);
        $vmcount_all=ProjectRequest::getVmCount("all");
        $vmcount_active=ProjectRequest::getVmCount("active");
        $vmcount_deleted=ProjectRequest::getVmCount("deleted");

        $new_results=[];
        $ips = array();
        foreach ($results[1] as $res) 
        {
            $now = strtotime(date("Y-m-d"));
            $end_project = strtotime($res['end_date']);
            $remaining=$now-$end_project;
            
            $vm=VM::find()->where(['id'=>$res['vm_id']])->one();
            $ips[] = $vm->ip_address;

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
                                'sideItems'=>$sidebarItems,'filter'=>$filter, "count_all"=>$vmcount_all, "count_active"=>$vmcount_active,"count_deleted"=>$vmcount_deleted, 
                                'ips'=>$ips,'filters'=>$filters_search, 'search_user'=>$filters_search['user'], 'search_project'=>$filters_search['project'], 'ip_address'=>$filters_search['ip']]);
    }

    public function actionVmMachinesList($filter='all')
    {
        $filters_search=['user'=>Yii::$app->request->get('username',''), 'project'=>Yii::$app->request->get('project_name',''), 'ip'=>Yii::$app->request->get('ip_address','')];
        
        $results=ProjectRequest::getVmMachinesList($filter, $filters_search['user'], $filters_search['project'], $filters_search['ip']);
        $vmcount_all=ProjectRequest::getVmMachinesCount("all");
        $vmcount_active=ProjectRequest::getVmMachinesCount("active");
        $vmcount_deleted=ProjectRequest::getVmMachinesCount("deleted");

        $new_results=[];
        $ips = array();
        foreach ($results[1] as $res) 
        {
            
            $now = strtotime(date("Y-m-d"));
            $end_project = strtotime($res['end_date']);
            $remaining=$now-$end_project;

            $vm=VmMachines::find()->where(['id'=>$res['vm_id']])->one();
            $ips[] = $vm->ip_address;

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
                                'sideItems'=>$sidebarItems,'filter'=>$filter, "count_all"=>$vmcount_all, "count_active"=>$vmcount_active,"count_deleted"=>$vmcount_deleted, 'ips'=>$ips,
                                'filters'=>$filters_search, 'search_user'=>$filters_search['user'], 'search_project'=>$filters_search['project'], 'ip_address'=>$filters_search['ip']]);
    }


    public function actionAdminVmDetails($id,$project_id,$filter,$pages=null)
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
                                'deletedBy'=>$deletedBy, 'filter'=>$filter, 'project_id'=>$project->id, 'pages'=>$pages]);
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


        /*
         * If someone other than the project owner or an Admin are trying
         * to edit the request, then show an error.
         */
        if ( (!$owner) && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }
        /*
         * Check that an invalid request is being updated
         */
        if (($prequest->status!=ProjectRequest::APPROVED) && ($prequest->status!=ProjectRequest::AUTOAPPROVED))
        {
            return $this->render('error_already_modified');
        }
        /*
         * Check that project has not expired.
         */
        $date1 = new \DateTime($prequest->end_date);
        $date2 = new \DateTime('now');

        /*
         * Since datetime involves time too
         * equality will not work. Instead, check that 
         * the date strings are not the same
         */
        if (($date1->format("Y-m-d")!=$date2->format("Y-m-d")) && ($date2>$date1))
        {
            return $this->render('error_expired');
        }
        
        $prequest->fillUsernameList();

        $prType=$prequest->project_type;

        $project=Project::find()->where(['latest_project_request_id'=>$id])->one();
        $trls=[];
        $maturities=[];
        $num_vms_dropdown=[];
        $volume_exists=false;

        $role=User::getRoleType();

        $vm_exists=false;

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
            $drequest->flavour=isset($drequest->flavourIdNameLimitless[$drequest->vm_flavour])?$drequest->flavourIdNameLimitless[$drequest->vm_flavour]:'';
            if (!isset($drequest->flavours[$drequest->flavour]))
            {
                if (!empty($drequest->flavor))
                {
                    $drequest->flavours[$drequest->flavour]=$drequest->allFlavours[$drequest->flavour];
                }
            }
            $view_file='edit_service';
            $upperlimits=ServiceLimits::find()->where(['user_type'=>$role])->one();
            $autoacceptlimits=ServiceAutoaccept::find()->where(['user_type'=>$role])->one();
            $prequest->end_date=$ends;
            
            $project_id=$prequest->project_id;
            $vm=VM::find()->where(['project_id'=>$project_id, 'active'=>true])->one();
            if (!empty($vm))
            {
                $vm_exists=true;
            }

            $trls[0]='Unspecified';
            for ($i=1; $i<10; $i++)
            {
                $trls[$i]='Level ' . $i;
            }
        }
        else if ($prType==3)
        {
            /* Get request quotas */
            $drequest=MachineComputeRequest::find()->where(['request_id'=>$id])->one();
            /*
             * If flavor has been changed from openstack allow system to continue, in order to 
             * be able to update the flavor (provided that the VM does not exist).
             */
            $drequest->flavour=isset($drequest->flavourIdNameLimitless[$drequest->vm_flavour])?$drequest->flavourIdNameLimitless[$drequest->vm_flavour]:'';
            $view_file='edit_machine_compute';
            $prequest->end_date=$ends;
            $upperlimits='';
            $autoacceptlimits='';
            $project_id=$prequest->project_id;
            $vm=VMmachines::find()->where(['project_id'=>$project_id, 'active'=>true])->one();
            /*
             * Create dropdown for the number of VMs
             */
            for ($i=1; $i<31; $i++)
                $num_vms_dropdown[$i]=$i;
            if (!empty($vm))
            {
                $vm_exists=true;
            }
        }
        else if ($prType==2)
        {
            $drequest=ColdStorageRequest::find()->where(['request_id'=>$id])->one();
            $view_file='edit_cold_storage';
            for ($i=1; $i<31; $i++)
                $num_vms_dropdown[$i]=$i;
            
            $prequest->end_date='2100-1-1';
            $volume='';
            if ($drequest->type=='hot')
            {
                $volume=HotVolumes::find()->where(['project_id'=>$prequest->project_id, 'active'=>true])->one();
            }
            else
            {
                /*
                 * Placeholder for cold storage
                 */
            }
            if (!empty($volume))
            {
                $volume_exists=true;
            }

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
            // Enforce one volume for 24/7 service
            if ($prType==2 && $drequest->vm_type==1) {
                $drequest->num_of_volumes=1;
            }

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
            
            $prequest->user_list=new yii\db\ArrayExpression($participant_ids, 'int4');

            $isValid = $prequest->validate();
            $isValid = $drequest->validate() && $isValid;
            if($prType==3)
            {
                $isValid = $prequest->machinesDuration30() && $isValid;
            }
            
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
                    if ($volume_exists)
                    {
                        if ($dold->changed($drequest))
                        {
                            Yii::$app->session->setFlash('danger', "You cannot modify storage volume details before a volume is deleted");
                            return $this->redirect(['project/index']);
                        }
                    }
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

                    $messages=$prequest->uploadNewEdit($prType,$uchanged,$id);
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


        return $this->render($view_file,['details'=>$drequest, 'project'=>$prequest, 
                    'trls'=>$trls, 'form_params'=>$form_params, 'participating'=>$participating, 'errors'=>$errors, 'upperlimits'=>$upperlimits, 'autoacceptlimits'=>$autoacceptlimits,'maturities'=>$maturities, 'vm_exists'=>$vm_exists, 'ends'=>$ends, 'role'=>$role, 'num_vms_dropdown'=>$num_vms_dropdown, 'volume_exists'=>$volume_exists]);


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
        $num_vms_dropdown=[];

        $vm_exists=false;
        
        $prequest->fillUsernameList();

        $prType=$prequest->project_type;

        $project=Project::find()->where(['latest_project_request_id'=>$id])->one();
        $trls=[];
        $maturities=[];
        $volume_exists=false;

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
                $vm_exists=true;
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
            /*
             * Create dropdown for the number of VMs
             */
            for ($i=1; $i<31; $i++)
                $num_vms_dropdown[$i]=$i;
            if (!empty($vm))
            {
                $vm_exists=true;
            }
        }
        else if ($prType==2)
        {
            $drequest=ColdStorageRequest::find()->where(['request_id'=>$id])->one();
            $view_file='edit_cold_storage';
            $prequest->duration='2100-1-1';
            $volume='';
            for ($i=1; $i<31; $i++)
                $num_vms_dropdown[$i]=$i;
            if ($drequest->type=='hot')
            {
                $volume=HotVolumes::find()->where(['project_id'=>$prequest->project_id, 'active'=>true])->one();
            }
            else
            {
                /*
                 * Placeholder for cold storage
                 */
            }
            if (!empty($volume))
            {
                $volume_exists=true;
            }
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

            $isValid = $prequest->validate();
            $isValid = $drequest->validate() && $isValid;

            // Enforce one volume for 24/7 service
            if ($prType==2 && $drequest->vm_type==1) {
                $drequest->num_of_volumes=1;
            }


            /* 
             * Get participant ids
             */
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

            
            $prequest->user_list=new yii\db\ArrayExpression($participant_ids, 'int4');
            $pchanged_tmp= ProjectRequest::ProjectModelChanged($pold,$prequest);
            $pchanged=$pchanged_tmp[0];
            $uchanged=$pchanged_tmp[1];
            $dchanged= ProjectRequest::modelChanged($dold,$drequest);
            
            $project_id=$prequest->project_id;
            $vm=VM::find()->where(['project_id'=>$project_id, 'active'=>true])->one();

            

            if ($isValid)
            {   
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
//                    $messages=$prequest->uploadNewEdit($participating,$prType,$id,$uchanged);
                    $messages = $prequest->uploadNewEdit($prType, $uchanged, $id);
                    $errors .= $messages[0];
                    $success .= $messages[1];
                    $warnings .= $messages[2];
                    $requestId = $messages[3];
                    if ($requestId != -1) {
                        $messages = $drequest->uploadNewEdit($requestId, $uchanged);
                        $errors .= $messages[0];
                        $success .= $messages[1];
                        $warnings .= $messages[2];
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
                    'trls'=>$trls, 'form_params'=>$form_params, 'participating'=>$participating, 'errors'=>$errors, 'upperlimits'=>$upperlimits, 'autoacceptlimits'=>$autoacceptlimits,'maturities'=>$maturities, 'ends'=>$ends, 'vm_exists'=>$vm_exists,'role'=>$role, 'num_vms_dropdown'=>$num_vms_dropdown,'volume_exists'=>$volume_exists]);


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
        if (!Userw::hasRole('Moderator',$superadminAllowed=true))
                
        {
            return $this->render('error_unauthorized');
        }
        $user_notifications=EmailEventsModerator::find()->where(['user_id'=>$user_id])->one();
        if(empty($user_notifications))
        {
        		$user_notifications=new EmailEventsModerator;
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
        /*
         * Get storage active projects for user
         */
        $results=ColdStorageRequest::getActiveProjects();
        $services=$results[0];
        $machines=$results[1];
        
        return $this->render('storage_volumes', ['services'=>$services, 'machines'=>$machines, 'results'=>$results]);
    }

    public function actionCreateVolume($id,$order=1,$ret='u')
    {
        $participant=Project::userInProject($id);
        if ( (empty($participant)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }

        if ($ret=='a')
        {
            $return=['administration/storage-volumes'];
        }
        else
        {
            $return=['project/storage-volumes'];
        }

        $volume=HotVolumes::find()->where(['project_id'=>$id, 'mult_order' =>$order, 'active'=>true])->one();

        if (!empty($volume))
        {
            Yii::$app->session->setFlash('danger', "Volume already exists. Please delete it and try again.");
            return $this->redirect($return);
        }

        $project=Project::find()->where(['id'=>$id])->one();
        $crequest=ColdStorageRequest::find()->where(['request_id'=>$project->latest_project_request_id])->one();

        if ($order>$crequest->num_of_volumes)
        {
            Yii::$app->session->setFlash('danger', "Number of volumes exceeds project quota.");
            return $this->redirect($return);
        }

        if($crequest->type=='hot')
        {
            $hotvolume=new HotVolumes;
            $hotvolume->initialize($crequest->vm_type);
            $hotvolume->authenticate();
            if (!empty($hotvolume->errorMessage))
            {
                Yii::$app->session->setFlash('danger', $hotvolume->errorMessage);
                return $this->redirect($return);
            }

            /*
             * Create volume
             */
            
            $hotvolume->create($project,$crequest,$order);
            if (!empty($hotvolume->errorMessage))
            {
                Yii::$app->session->setFlash('danger', $hotvolume->errorMessage);  
                return $this->redirect($return);
            }

            Yii::$app->session->setFlash('success', "Volume created successfully");  
            return $this->redirect($return);
        }
        else
        {
            /*
             * This is a placeholder for when the cold storage
             * backend is provided;
             */
            
        }
        $this->redirect($return);

    }

    public  function actionDeleteVolume($vid,$ret='u')
    {
        $volume=HotVolumes::find()->where(['id'=>$vid])->one();

        if ($ret=='a')
        {
            $return=['administration/storage-volumes'];
        }
        else
        {
            $return=['project/storage-volumes'];
        }

        if (empty($volume))
        {
            Yii::$app->session->setFlash('danger', "Volume was not found. Please try again or contact an administrator.");
            return $this->redirect($return);
        }

        $participant=Project::userInProject($volume->project_id);
        if ( (empty($participant)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }

        $volume->initialize($volume->vm_type);
        $volume->authenticate();
        if (!empty($hotvolume->errorMessage))
        {
            Yii::$app->session->setFlash('danger', $volume->errorMessage);
            return $this->redirect($return);
        }
        
        $volume->deleteVolume();

        if (!empty($volume->errorMessage))
        {
            Yii::$app->session->setFlash('danger', $volume->errorMessage);
            return $this->redirect($return);
        }

        Yii::$app->session->setFlash('success', "Volume $volume->name has been successfully deleted.");
        return $this->redirect($return);
        
    }

    public function actionManageVolumes($id,$vid,$ret='u')
    {
        $participant=Project::userInProject($id);
        if ( (empty($participant)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }

        $volume=HotVolumes::find()->where(['id'=>$vid, 'project_id'=>$id])->one();
        if ($ret=='a')
        {
            $return=['administration/storage-volumes'];
        }
        else
        {
            $return=['project/storage-volumes'];
        }

        if (empty($volume))
        {
            Yii::$app->session->setFlash('danger', "Volume does not exist. Please create it and try again");
            return $this->redirect($return);
        }

        $volume->getVms();
        if($volume->load(Yii::$app->request->post()))
        {
            /*
             * These scenarios are not really possible,
             * but check nevertheless
             */
            if (empty($volume->new_vm_id))
            {
                Yii::$app->session->setFlash('danger', "A VM has not been provided.");
                return $this->redirect($return);
            }
            if ($volume->vm_id==$volume->new_vm_id)
            {
                Yii::$app->session->setFlash('warning', "Volume already attached to VM.");
                return $this->redirect($return);
            }

            if (!empty($volume->vm_id))
            {
                Yii::$app->session->setFlash('danger', "The volume is attached to another VM. Please detach the volume first and try again.");
                return $this->redirect($return);
            }

            /*
             * Initialize model. If OpenStack is unreachable, show message.
             */
            $volume->initialize($volume->vm_type);

            /*
             * Get API token. If OpenStack is unreachable, show message.
             */
            $volume->authenticate();

            if (!empty($volume->errorMessage))
            {
                Yii::$app->session->setFlash('danger', $volume->errorMessage);
                return $this->redirect($return);
            }

            $volume->attach();

            if (!empty($volume->errorMessage))
            {
                Yii::$app->session->setFlash('danger', $volume->errorMessage);
                return $this->redirect($return);
            }

            Yii::$app->session->setFlash('success', "Volume has been successfully attached to the VM. If the volume is new, 
                            you will need to partition, format and mount it. 
                            See " . Html::a('this guide', ['/site/additional-storage-tutorial']) . " on how to do it.");
            return $this->redirect($return);

        }
        
        $vm_name=(isset($volume->vm_dropdown[$volume->vm_id])) ? $volume->vm_dropdown[$volume->vm_id] : '';

        $form_params=
        [
            'action' => URL::to(['project/manage-volumes','id'=>$id, 'vid'=>$vid,'ret'=>$ret]),
            'method' => 'POST'
        ];

        return $this->render('manage_volumes', ['volume'=>$volume,'pid'=>$id, 'vm_name'=>$vm_name,'form_params'=>$form_params, 'ret'=>$ret]);
    }

    public function actionDetachVolumeFromVm($id,$vid,$ret='u')
    {
        $participant=Project::userInProject($id);
        if ( (empty($participant)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }

        if ($ret=='a')
        {
            $return=['administration/storage-volumes'];
        }
        else
        {
            $return=['project/storage-volumes'];
        }

        $volume=HotVolumes::find()->where(['id'=>$vid, 'project_id'=>$id])->one();

        if (empty($volume))
        {
            Yii::$app->session->setFlash('danger', "Volume does not exist. Please create it and try again");
            return $this->redirect($return);
        }

        $volume->initialize($volume->vm_type);
        $volume->authenticate();
        if (!empty($volume->errorMessage))
        {
            Yii::$app->session->setFlash('danger', $volume->errorMessage);
            return $this->redirect($return);
        }
        $volume->detach();
        if (!empty($volume->errorMessage))
        {
            Yii::$app->session->setFlash('danger', $volume->errorMessage);
            return $this->redirect($return);
        }
        
        Yii::$app->session->setFlash('success', "Volume has been successfully detached from the VM.");
        return $this->redirect($return);

    }

    public function actionUserStatistics()
    {
        $user=Userw::getCurrentUser();
        $uid=$user['id'];
        $username=explode('@',$user['username'])[0];
        $usage_owner=Project::userStatisticsOwner($uid);
        $usage_participant=Project::userStatisticsParticipant($uid);
        return $this->render('user_statistics', ['usage_participant'=>$usage_participant,'usage_owner'=>$usage_owner, 'username'=>$username]);
    }

    public function actionOnDemandLp($id) {
        $existing=Vm::find()->where(['project_id'=>$id])->andWhere(['active'=>true])->one();

        $project=Project::find()->where(['id'=>$id])->one();
        $project_id=$project->id;
        return $this->render('on_demand_lp',['model'=>$existing, 'requestId'=>$id, 'project'=>$project]);
    }



}

