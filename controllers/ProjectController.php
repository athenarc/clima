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
use app\models\ColdStorageLimits;
use app\models\ColdStorageAutoaccept;
use app\models\OndemandRequest;
use app\models\OndemandLimits;
use app\models\OndemandAutoaccept;
use app\models\ServiceLimits;
use app\models\ServiceAutoaccept;
use app\models\ColdStorageRequest;
use app\models\ServiceVmCredentials;
use app\models\Notification;
use app\models\User;
use app\models\Vm;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\UploadedFile;
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
        $project_types=Project::TYPES;
        $button_links=[0=>'/project/view-ondemand-request-user', 1=>'/project/view-service-request-user', 
                    2=>'/project/view-cold-storage-request-user'];

        //ProjectRequest::invalidateExpiredProjects();
        $deleted=Project::getDeletedProjects();
        //$expired=Project::getExpiredProjects();
        $owner=Project::getActiveProjectsOwner();
        $participant=Project::getActiveProjectsParticipant();
        $role=User::getRoleType();
        $username=Userw::getCurrentUser()['username'];
        $user_split=explode('@',$username)[0];
        $all_projects=array_merge($owner,$participant);
        $expired=[];
        $active=[];
        foreach ($all_projects as $project) 
        {
            
            $start=date('Y-m-d',strtotime($project['approval_date']));
            $duration=$project['duration'];
            $end=date('Y-m-d', strtotime($start. " + $duration months"));
            $now = strtotime(date("Y-m-d"));
            $end_project = strtotime($end);
            $remaining_secs=$end_project-$now;
            $remaining_days=$remaining_secs/86400;
            $remaining_months=round($remaining_days/30);
            if($remaining_days<=0)
            {

                if($username==$project['username'])
                {
                    array_push($project,'<b>You</b>');
                    array_push($project, $end);
                }
                else
                {
                    array_push($project, "$project[username]");
                    array_push($project, $end);
                }

                 $expired[]=$project;

            }
            else
            {
                if($username==$project['username'])
                {
                    array_push($project,'<b>You</b>' );
                    array_push($project, $remaining_days);
                }
                else
                {
                    array_push($project, "$project[username]");
                    array_push($project, $remaining_days);
                }
                $active[]=$project;
             }


        }

        $number_of_active=count($active);
        $number_of_expired=count($expired);

        
        foreach ($active as $project) 
        {
                $start=date('Y-m-d',strtotime($project['approval_date']));
                $duration=$project['duration'];
                $end=date('Y-m-d', strtotime($start. " + $duration months"));
                $now = strtotime(date("Y-m-d"));
                $end_project = strtotime($end);
                $remaining_secs=$end_project-$now;
                $user_id=User::findByUsername($username)->id;
                $notification_remaining_days=$remaining_secs/86400;
                // if($duration>1)
                // {
                $message1="Project '$project[name] will end in 30 days.";
                //}

                $message2="Project '$project[name] will end in 15 days.";
                
                
                $notifications1=Notification::find(['recipient_id'=>$user_id])
                ->andWhere(['message'=>$message1])->all();
                $notifications2=Notification::find(['recipient_id'=>$user_id])
                ->andWhere(['message'=>$message2])->all();
                 // print_r($notification_remaining_days);
                 // print_r($notification_remaining_days);
                 // exit(0);
                
                if(empty($notifications1))
                {
                    if($notification_remaining_days==30 && $duration>1)
                    {
                             Notification::notify($user_id,$message1,1,null);
                    }
                    
                }
                else
                {
                    continue;
                }

                if(empty($notifications2))
                {
                    if($notification_remaining_days==15)
                    {
                             Notification::notify($user_id,$message2,1,null);
                    }
                    
                }
                else
                {
                    continue;
                }    
        }
        

        return $this->render('index',['owner'=>$owner,'participant'=>$participant,
            'button_links'=>$button_links,'project_types'=>$project_types,'role'=>$role,
            'deleted'=>$deleted,'expired'=>$expired, 'active'=>$active, 'number_of_active'=>$number_of_active, 'number_of_expired'=>$number_of_expired]);

    }

     public function actionNewRequest()
    {

        return $this->render('new_request');
    }

    



    public function actionNewServiceRequest()
    {
        $serviceModel=new ServiceRequest;
        $projectModel=new ProjectRequest;
        $limitsModel=new ServiceLimits;
        $autoacceptModel=new ServiceAutoaccept;


        $role=User::getRoleType();
        $autoaccepted_num=ProjectRequest::find()->where(['status'=>2,'project_type'=>1,'submitted_by'=>Userw::getCurrentUser()['id'], ])->count();
        $autoaccept_allowed=($autoaccepted_num < 1) ? true :false; 
        
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
                if ($requestId!=-1)
                {
                    $messages=$serviceModel->uploadNew($requestId);
                    $errors.=$messages[0];
                    $success.=$messages[1];
                    $warnings.=$messages[2];
                    // ServiceVmCredentials::createEmpty($requestId);
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
        

        return $this->render('new_service_request',['service'=>$serviceModel, 'project'=>$projectModel, 
                    'trls'=>$trls, 'form_params'=>$form_params, 'participating'=>$participating, 'errors'=>$errors, 'upperlimits'=>$upperlimits, 'autoacceptlimits'=>$autoacceptlimits,'autoaccept_allowed' => $autoaccept_allowed]);



    }


    public function actionNewColdStorageRequest()
    {
        $coldStorageModel=new ColdStorageRequest;
        $projectModel=new ProjectRequest;
        $projectModel->duration=36;
        $projectModel->backup_services=false;

        $limitsModel=new ColdStorageLimits;
        $autoacceptModel=new ColdStorageAutoaccept;

        $role=User::getRoleType();
        $autoaccepted_num=ProjectRequest::find()->where(['status'=>2,'project_type'=>2,'submitted_by'=>Userw::getCurrentUser()['id'], ])->count();
        $autoaccept_allowed=($autoaccepted_num < 1) ? true :false; 
        
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
            $projectModel->duration=1200;
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
                if ($requestId!=-1)
                {
                    $messages=$coldStorageModel->uploadNew($requestId);
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
        

        return $this->render('new_cold_storage_request',['coldStorage'=>$coldStorageModel, 'project'=>$projectModel, 
                    'form_params'=>$form_params, 'participating'=>$participating, 'errors'=>$errors,
                     'upperlimits'=>$upperlimits, 'autoacceptlimits'=>$autoacceptlimits,'autoaccept_allowed' => $autoaccept_allowed]);



    }


    public function actionNewOndemandRequest()
    {
        $ondemandModel=new OndemandRequest();
        $projectModel=new ProjectRequest;
        $limitsModel=new OndemandLimits;
        $autoacceptModel=new OndemandAutoaccept;

        $role=User::getRoleType();
        $autoaccepted_num=ProjectRequest::find()->where(['status'=>2,'project_type'=>0,'submitted_by'=>Userw::getCurrentUser()['id'], ])->count();
        $autoaccept_allowed=($autoaccepted_num < 1) ? true :false; 
        
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

                if ($requestId!=-1)
                {
                    $messages=$ondemandModel->uploadNew($requestId);
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
        

        return $this->render('new_ondemand_request',['ondemand'=>$ondemandModel, 'project'=>$projectModel, 
                     'maturities'=>$maturities, 'form_params'=>$form_params, 'participating'=>$participating, 'errors'=>$errors, 'upperlimits'=>$upperlimits, 'autoacceptlimits'=>$autoacceptlimits,'autoaccept_allowed' => $autoaccept_allowed]);
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

        if (!Userw::hasRole('Admin',$superadminAllowed=true) && (!Userw::hasRole('Moderator',$superadminAllowed=true)) )
        {
            return $this->render('//site/error_unauthorized');
        }

        ProjectRequest::recordViewed($id);

        $project=ProjectRequest::findOne($id);

        //Request details must be retrieved by the project request id
        if ($project->project_type==0)
        {
            $details=OndemandRequest::findOne(['request_id'=>$id]);
            $view_file='view_ondemand_request';
            $usage=ProjectRequest::getProjectSchemaUsage($project->name);
            $type="On-demand computation";
            $start = date('Y-m-d', strtotime($project->submission_date));
            $ends= date('Y-m-d', strtotime($start. " + $project->duration months"));
            $now=date('Y-m-d');
            $datetime1 = strtotime($now);
            $datetime2 = strtotime($ends);
            $secs = $datetime2 - $datetime1;
            $remaining_time = $secs / 86400;
            if($remaining_time<=0)
            {    
                $remaining_time=0;
            }
            $num_of_jobs=$details->num_of_jobs;
            $used_jobs=$usage['count'];
            $remaining_jobs=$num_of_jobs-$used_jobs;

            

        }
        else if ($project->project_type==1)
        {
            $details=ServiceRequest::findOne(['request_id'=>$id]);
            $view_file='view_service_request';
            $usage=[];
            $type="24/7 Service";
            $start = date('Y-m-d', strtotime($project->submission_date));
            $ends= date('Y-m-d', strtotime($start. " + $project->duration months"));
            $now=date('Y-m-d');
            $datetime1 = strtotime($now);
            $datetime2 = strtotime($ends);
            $secs = $datetime2 - $datetime1;
            $remaining_time = $secs / 86400;
            if($remaining_time<=0)
            {    
                $remaining_time=0;
            }
            $remaining_jobs=0;
            

        }
        else if ($project->project_type==2)
        {
            $details=ColdStorageRequest::findOne(['request_id'=>$id]);
            $view_file='view_cold_request';
            $usage=[];
            $type="Cold-Storage";
            $start = date('Y-m-d', strtotime($project->submission_date));
            $ends= date('Y-m-d', strtotime($start. " + $project->duration months"));
            $now=date('Y-m-d');
            $datetime1 = strtotime($now);
            $datetime2 = strtotime($ends);
            $secs = $datetime2 - $datetime1;
            $remaining_time = $secs / 86400;
            if($remaining_time<=0)
            {    
                $remaining_time=0;
            }
            $remaining_jobs=0;
        }
        

        $users=User::find()->where(['IN','id',$project->user_list])->all();
        $submitted=User::find()->where(['id'=>$project->submitted_by])->one();
        $project_owner= ($submitted->username==Userw::getCurrentUser()['username']);
        /*
         * Fix username so that it is shown without @
         */
        $submitted->username=explode('@',$submitted->username)[0];
        // $users=User::returnList($project->user_list);
        $number_of_users=count($users);
        $maximum_number_users=$project->user_num;

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
        return $this->render($view_file,['project'=>$project,'details'=>$details, 
            'filter'=>$filter,'usage'=>$usage,'user_list'=>$user_list, 'submitted'=>$submitted,'request_id'=>$id, 'type'=>$type, 'ends'=>$ends, 'start'=>$start, 'remaining_time'=>$remaining_time,
            'project_owner'=>$project_owner, 'number_of_users'=>$number_of_users, 'maximum_number_users'=>$maximum_number_users, 'remaining_jobs'=>$remaining_jobs, 'expired'=>$expired]);

        // ProjectRequest::recordViewed($id);

        // $project=ProjectRequest::findOne($id);

        // //Request details must be retrieved by the project request id
        // if ($project->project_type==0)
        // {
        //     $details=OndemandRequest::findOne(['request_id'=>$id]);
        //     $view_file='view_ondemand_request';
        //     $usage=ProjectRequest::getProjectSchemaUsage($project->name);
        // }
        // else if ($project->project_type==1)
        // {
        //     $details=ServiceRequest::findOne(['request_id'=>$id]);
        //     $view_file='view_service_request';
        //     $usage=[];
        // }
        // else if ($project->project_type==2)
        // {
        //     $details=ColdStorageRequest::findOne(['request_id'=>$id]);
        //     $view_file='view_cold_request';
        //     $usage=[];
        // }
        

        // $users=User::find()->where(['IN','id',$project->user_list])->all();
        // $submitted=User::find()->where(['id'=>$project->submitted_by])->one();
        // $submitted->username=explode('@',$submitted->username)[0];
        // // $users=User::returnList($project->user_list);
        // $user_list='';
        // foreach ($users as $user)
        // {
        //     $usernames=$user->username;
        //     $user_list.=explode('@', $usernames)[0].'<br/>';
        // }

        

        // return $this->render($view_file,['project'=>$project,'details'=>$details, 
        //     'filter'=>$filter,'usage'=>$usage,'user_list'=>$user_list, 'submitted'=>$submitted,'request_id'=>$id,]);

    }

    public function actionViewRequestUser($id,$filter='all',$return='')
    {

        $owner=Project::userInProject($id);

        if ( (!$owner) && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }

        ProjectRequest::recordViewed($id);

        $project=ProjectRequest::findOne($id);

        //Request details must be retrieved by the project request id
        if ($project->project_type==0)
        {
            $details=OndemandRequest::findOne(['request_id'=>$id]);
            $view_file='view_ondemand_request_user';
            $usage=ProjectRequest::getProjectSchemaUsage($project->name);
            $type="On-demand computation";
            $start = date('Y-m-d', strtotime($project->approval_date));
            $ends= date('Y-m-d', strtotime($start. " + $project->duration months"));
            $now=date('Y-m-d');
            $datetime1 = strtotime($now);
            $datetime2 = strtotime($ends);
            $secs = $datetime2 - $datetime1;
            $remaining_time = $secs / 86400;
            if($remaining_time<=0)
            {    
                $remaining_time=0;
            }
            $num_of_jobs=$details->num_of_jobs;
            $used_jobs=$usage['count'];
            $remaining_jobs=$num_of_jobs-$used_jobs;

            

        }
        else if ($project->project_type==1)
        {
            $details=ServiceRequest::findOne(['request_id'=>$id]);
            $view_file='view_service_request_user';
            $usage=[];
            $type="24/7 Service";
            $start = date('Y-m-d', strtotime($project->approval_date));
            $ends= date('Y-m-d', strtotime($start. " + $project->duration months"));
            $now=date('Y-m-d');
            $datetime1 = strtotime($now);
            $datetime2 = strtotime($ends);
            $secs = $datetime2 - $datetime1;
            $remaining_time = $secs / 86400;
            if($remaining_time<=0)
            {    
                $remaining_time=0;
            }
            $remaining_jobs=0;

        }
        else if ($project->project_type==2)
        {
            $details=ColdStorageRequest::findOne(['request_id'=>$id]);
            $view_file='view_cold_request_user';
            $usage=[];
            $type="Cold-Storage";
            $start = date('Y-m-d', strtotime($project->approval_date));
            $ends= date('Y-m-d', strtotime($start. " + $project->duration months"));
            $now=date('Y-m-d');
            $datetime1 = strtotime($now);
            $datetime2 = strtotime($ends);
            $secs = $datetime2 - $datetime1;
            $remaining_time = $secs / 86400;
            if($remaining_time<=0)
            {    
                $remaining_time=0;
            }
            $remaining_jobs=0;
        }
        

        $users=User::find()->where(['IN','id',$project->user_list])->all();
        $submitted=User::find()->where(['id'=>$project->submitted_by])->one();
        $project_owner= ($submitted->username==Userw::getCurrentUser()['username']);
        /*
         * Fix username so that it is shown without @
         */
        $submitted->username=explode('@',$submitted->username)[0];
        // $users=User::returnList($project->user_list);
        $number_of_users=count($users);
        $maximum_number_users=$project->user_num;

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

        return $this->render($view_file,['project'=>$project,'details'=>$details, 'return'=>$return,
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


        $statuses=ProjectRequest::STATUSES;
        $filters=['all'=>'All','pending'=>'Pending','approved'=>'Approved','auto-approved'=>'Auto-approved','rejected'=>'Rejected'];
        $project_types=Project::TYPES;
        $button_links=[0=>'/project/view-ondemand-request', 1=>'/project/view-service-request', 2=>'/project/view-cold-storage-request'];
        $line_classes=[-5=>'expired',-4=>'deleted',-3=>'modified',-1=>'rejected',0=>'pending', 1=>'approved', 2=>'approved'];
        // $user=User::getCurrentUser()['username'];

        $results=ProjectRequest::getRequestList('all');


        $pages=$results[0];
        $results=$results[1];

        $sidebarItems=[];

        foreach ($filters as $f=>$text)
        {
            $active=($f==$filter) ? 'active' : '';
            $sidebarItems[]=['link'=>Url::to(['project/user-request-list', 'filter'=>$f]), 'class'=>"list-group-item $active",'text'=>$text];
        }


        return $this->render('request_list',['results'=>$results,'pages'=>$pages,'statuses'=>$statuses,'message'=>$message,
                                'sideItems'=>$sidebarItems,'project_types'=>$project_types,
                                'button_links'=>$button_links,'line_classes'=>$line_classes,'filter'=>$filter]);

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

        $statuses=ProjectRequest::STATUSES;
        $filters=['all'=>'All','pending'=>'Pending','approved'=>'Approved','auto-approved'=>'Auto-approved','rejected'=>'Rejected'];
        $project_types=Project::TYPES;
        $button_links=[0=>'/project/view-ondemand-request', 1=>'/project/view-service-request', 2=>'/project/view-cold-storage-request'];
        $line_classes=[-5=>'expired',-4=>'deleted',-3=>'modified',-1=>'rejected',0=>'pending', 1=>'approved', 2=>'approved'];
        // $user=User::getCurrentUser()['username'];

        $results=ProjectRequest::getRequestList('all');

        $pages=$results[0];
        $results=$results[1];

        $sidebarItems=[];

        foreach ($filters as $f=>$text)
        {
            $active=($f==$filter) ? 'active' : '';
            $sidebarItems[]=['link'=>Url::to(['project/user-request-list', 'filter'=>$f]), 'class'=>"list-group-item $active",'text'=>$text];
        }


        return $this->render('request_list',['results'=>$results,'pages'=>$pages,'statuses'=>$statuses,'message'=>$message,
                                'sideItems'=>$sidebarItems,'project_types'=>$project_types,
                                'button_links'=>$button_links,'line_classes'=>$line_classes,'filter'=>$filter]);
    }


    public function actionConfigureVm($id)
    {
        $owner=Project::userInProject($id);

        if ( (empty($owner)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }

        $existing=Vm::find()->where(['request_id'=>$id])->andWhere(['active'=>true])->one();
        if (empty($existing))
        {
            /*
             * Create new VM
             */
            $model=new Vm;

            $avResources=VM::getOpenstackAvailableResources();
            // print_r($avResources);
            // exit(0);
            $service=ServiceRequest::find()->where(['request_id'=>$id])->one();

            // print_r($avResources);
            // exit(0);
            
            if ( ($service->num_of_ips>$avResources[2]) || ($service->ram>$avResources[1]) || ($service->num_of_cores > $avResources[0]) )
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
                    $result=$model->createVM($id,$service, $imageDD);
                    $error=$result[0];
                    $message=$result[1];
                    if ($error!=0)
                    {
                        
                        return $this->render('error_vm_creation',['error' => $error,'message'=>$message]);
                    }

                    else
                    {
                        $existing=Vm::find()->where(['request_id'=>$id])->andWhere(['active'=>true])->one();
                        $existing->getConsoleLink();
    
                        return $this->render('vm_details',['model'=>$existing, 'requestId'=>$id]);
                    }
                }
            }
            
            return $this->render('configure_vm',['model'=>$model,'form_params'=>$form_params,'imageDD'=>$imageDD,'service'=>$service]);
        }
        else
        {
             $existing->getConsoleLink();
             return $this->render('vm_details',['model'=>$existing,'requestId'=>$id]);
        }
        

    }

    public function actionDeleteVm($id)
    {
        $owner=Project::userInProject($id);

        if ( (empty($owner)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) )
        {
            return $this->render('error_unauthorized');
        }

        $vm=Vm::find()->where(['request_id'=>$id])->andWhere(['active'=>true])->one();

        $result=$vm->deleteVM();
        $error=$result[0];
                    $message=$result[1];

        if ($error!=0)
        {
            return $this->render('error_vm_deletion',['error' => $error,'message'=>$message]);
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

        $pages=$results[0];
        $results=$results[1];

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

    public function actionAdminVmDetails($id,$request_id,$filter)
    {
        
        if (!Userw::hasRole('Admin',$superadminAllowed=true))
        {
            return $this->render('error_unauthorized');
        }

        $project=ProjectRequest::find()->where(['id'=>$request_id])->one();
        $projectOwner=User::returnUsernameById($project->submitted_by);
        $projectOwner=explode('@', $projectOwner)[0];
        $service=ServiceRequest::find()->where(['request_id'=>$request_id])->one();
        $vm=VM::find()->where(['id'=>$id])->one();
        $createdBy=User::returnUsernameById($vm->created_by);
        $createdBy=explode('@', $createdBy)[0];
        $deletedBy=(!empty($vm->deleted_by)) ? User::returnUsernameById($vm->deleted_by): '';
        $deletedBy=explode('@', $deletedBy)[0];


        
        return $this->render('vm_admin_details',['project'=>$project,'service'=>$service,
                                'vm'=>$vm, 'projectOwner'=>$projectOwner, 'createdBy'=>$createdBy,
                                'deletedBy'=>$deletedBy, 'filter'=>$filter ]);
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
            $drequest->flavour=$drequest->flavourIdNameLimitless[$drequest->vm_flavour];
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
        else if ($prType==2)
        {
            $drequest=ColdStorageRequest::find()->where(['request_id'=>$id])->one();
            $view_file='edit_cold_storage';
            $prequest->duration=36;
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
                    $prequest->duration=1200;
                }
                if ($prType==1)
                {
                    if ($dold->flavour != $drequest->flavour)
                    {
                        $dchanged=true;
                    }
                }

                if ($pchanged || $dchanged)
                {
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
                        // ServiceVmCredentials::createEmpty($requestId);
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
                    'trls'=>$trls, 'form_params'=>$form_params, 'participating'=>$participating, 'errors'=>$errors, 'upperlimits'=>$upperlimits, 'autoacceptlimits'=>$autoacceptlimits,'maturities'=>$maturities, 'vm_exists'=>$vm_exists]);


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
                return $this->render('error_service_vm_exist');
            }

            $trls[0]='Unspecified';
            for ($i=1; $i<10; $i++)
            {
                $trls[$i]='Level ' . $i;
            }
        }
        else if ($prType==2)
        {
            $drequest=ColdStorageRequest::find()->where(['request_id'=>$id])->one();
            $view_file='edit_cold_storage';
            $prequest->duration=36;
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
                    $prequest->duration=1200;
                }
                if ($prType==1)
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
                        // ServiceVmCredentials::createEmpty($requestId);
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
                    'trls'=>$trls, 'form_params'=>$form_params, 'participating'=>$participating, 'errors'=>$errors, 'upperlimits'=>$upperlimits, 'vm_exists'=>$vm_exists, 'autoacceptlimits'=>$autoacceptlimits,'maturities'=>$maturities]);


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

        if ($prequest->project_type==ProjectRequest::SERVICE)
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

        $existing=Vm::find()->where(['request_id'=>$id])->andWhere(['active'=>true])->one();

        $password=$existing->retrieveWinPassword();

        return $this->renderPartial('vm_password',['message'=>$password]);
    }
}


