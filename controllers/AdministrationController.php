<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\base\Swift_TransportException;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\ServiceAutoaccept;
use app\models\OndemandAutoaccept;
use app\models\ColdStorageAutoaccept;
use app\models\ServiceLimits;
use app\models\OndemandLimits;
use app\models\ColdStorageLimits;
use app\models\Configuration;
use app\models\Openstack;
use app\models\OpenstackMachines;
use app\models\MachineComputeLimits;
use yii\helpers\Url;
use app\models\ProjectRequest;
use app\models\Project;
use app\models\User;
use app\models\EmailEventsAdmin;
use app\models\Smtp;
use app\models\Page;
use app\components\LoukasMailer;
use webvimark\modules\UserManagement\models\User as Userw;

class AdministrationController extends Controller
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
        return $this->render('index');
    }

    public function actionConfigure()
    {
        
      
        
        $userTypes=["gold"=>"Gold","silver"=>"Silver", "bronze"=>"Bronze"];
        $currentUser=(!isset($_POST['currentUserType'])) ? "bronze": $_POST['currentUserType'] ;

        //new models
        $service=ServiceAutoaccept::find()->where(['user_type'=>$currentUser])->one();
        $ondemand=OndemandAutoaccept::find()->where(['user_type'=>$currentUser])->one();
        $coldStorage=ColdStorageAutoaccept::find()->where(['user_type'=>$currentUser])->one();
        $serviceLimits=ServiceLimits::find()->where(['user_type'=>$currentUser])->one();
        $ondemandLimits=OndemandLimits::find()->where(['user_type'=>$currentUser])->one();
        $machineComputationLimits=MachineComputeLimits::find()->where(['user_type'=>$currentUser])->one();
        $coldStorageLimits=ColdStorageLimits::find()->where(['user_type'=>$currentUser])->one();
        $smtp= Smtp::find()->one();
        $openstack=Openstack::find()->one();
        $openstackMachines=OpenstackMachines::find()->one();

        
        $general=Configuration::find()->one();
        $pages=Page::getPagesDropdown();
        
        $activeButtons=['','','','','','','',''];
        $activeTabs=['','','','','','','',''];

        if (!isset($_POST['hidden-active-button']))
        {
            $activeButtons[0]='button-active';
            $activeTabs[0]='tab-active';
            $hiddenActiveButton='general-button';
        }
       
        $form_params =
        [
            'action' => URL::to(['administration/configure']),
            'options' => 
            [
                'class' => 'configuration_form',
                'id'=> "configuration_form"
            ],
            'method' => 'POST'
        ];

        
        $user_email=Userw::getCurrentUser()['email'];
        if(empty($user_email))
        {  
          Yii::$app->session->setFlash('danger', "You must provide your email to receive email notifications.");
        }

        if ( ($service->load(Yii::$app->request->post())) && ($machineComputationLimits->load(Yii::$app->request->post())) && ($general->load(Yii::$app->request->post())) 
            &&  ($ondemand->load(Yii::$app->request->post())) && ($coldStorage->load(Yii::$app->request->post()))
            && ($coldStorageLimits->load(Yii::$app->request->post())) && ($serviceLimits->load(Yii::$app->request->post())) 
            && ($ondemandLimits->load(Yii::$app->request->post())) && ($smtp->load(Yii::$app->request->post())) 
            && ($openstack->load(Yii::$app->request->post())) && $openstackMachines->load(Yii::$app->request->post())
            )
        {
            
            $password=$smtp->password;
            $encrypted_password=base64_encode($password);
            $smtp->password=$encrypted_password;
            $smtp->update();

            $openstack->encode();
            $openstack->save();

            $openstackMachines->encode();
            $openstackMachines->save();

           

            $isValid = $general->validate();
            $isValid = $service->validate() && $isValid;
            $isValid = $ondemand->validate() && $isValid;
            $isValid = $coldStorage->validate() && $isValid;
            $isValid = $coldStorageLimits->validate() && $isValid;
            $isValid = $serviceLimits->validate() && $isValid;
            $isValid = $ondemandLimits->validate() && $isValid;
            if ($isValid)
            {
                
                $previousUserType=$_POST['previousUserType'];
                $general->updateDB();
                $success='Configuration successfully updated';

                $max_autoaccepted_services=Project::getMaximumActiveAcceptedProjects(1,$previousUserType,2);
                $max_accepted_services=Project::getMaximumActiveAcceptedProjects(1,$previousUserType,[1,2]);
                if(($service->autoaccept_number > $serviceLimits->number_of_projects))
                {
                    Yii::$app->session->setFlash('danger', "The maximum number of 24/7 service projects should be greater than the number of autoaccepted projects");
                    $success='';
                }
                elseif($max_autoaccepted_services > $service->autoaccept_number)
                {
                    
                    Yii::$app->session->setFlash('danger', "There is a $previousUserType user with $max_autoaccepted_services active autoaccepted 24/7 service projects");
                    $success='';
                }
                elseif($max_accepted_services > $serviceLimits->number_of_projects)
                {
                    Yii::$app->session->setFlash('danger', "There is a $previousUserType user with $max_accepted_services active 24/7 service projects");
                    $success='';
                }
                else
                {
                    $service->updateDB($previousUserType);
                    $serviceLimits->updateDB($previousUserType);
                }

                $max_autoaccepted_ondemand=Project::getMaximumActiveAcceptedProjects(0,$previousUserType,2);
                $max_accepted_ondemand=Project::getMaximumActiveAcceptedProjects(0,$previousUserType,[1,2]);

                if(($ondemand->autoaccept_number > $ondemandLimits->number_of_projects))
                {
                    Yii::$app->session->setFlash('danger', "The maximum number of on-demand batch computation projects should be greater than the number of autoaccepted projects");
                    $success='';
                }
                elseif($max_autoaccepted_ondemand > $ondemand->autoaccept_number)
                {
                    
                    Yii::$app->session->setFlash('danger', "There is a $previousUserType user with $max_autoaccepted_ondemand active autoaccepted on-demand batch computation projects");
                    $success='';
                }
                elseif($max_accepted_ondemand > $ondemandLimits->number_of_projects)
                {
                    Yii::$app->session->setFlash('danger', "There is a $previousUserType user with $max_accepted_ondemand active on-demand batch computation projects");
                    $success='';
                }
                else
                {
                    $ondemand->updateDB($previousUserType);
                    $ondemandLimits->updateDB($previousUserType);
                }

                $max_autoaccepted_volumes=Project::getMaximumActiveAcceptedProjects(2,$previousUserType,2);
                $max_accepted_volumes=Project::getMaximumActiveAcceptedProjects(2,$previousUserType,[1,2]);

                if(($coldStorage->autoaccept_number > $coldStorageLimits->number_of_projects))
                {
                    Yii::$app->session->setFlash('danger', "The maximum number of storage volumes projects should be greater than the number of autoaccepted projects");
                    $success='';
                }
                elseif($max_autoaccepted_volumes > $coldStorage->autoaccept_number)
                {
                    
                    Yii::$app->session->setFlash('danger', "There is a $previousUserType user with $max_autoaccepted_volumes autoaccepted active storage volumes");
                    $success='';
                }
                elseif($max_accepted_volumes > $coldStorageLimits->number_of_projects)
                {
                    Yii::$app->session->setFlash('danger', "There is a $previousUserType user with $max_accepted_volumes active storage volumes");
                    $success='';
                }
                else
                {
                    $coldStorage->updateDB($previousUserType);
                    $coldStorageLimits->updateDB($previousUserType);
                }


                $max_accepted_machines=Project::getMaximumActiveAcceptedProjects(3,$previousUserType,[1,2]);
                if((($machineComputationLimits->number_of_projects==-1) && ($previousUserType=='gold')) || 
                    ($max_accepted_machines <= $machineComputationLimits->number_of_projects))
                {
                        // print_r($max_accepted_machines);
                        // exit(0);
                        $machineComputationLimits->updateDB($previousUserType);
                }
                else
                {
                    Yii::$app->session->setFlash('danger', "There is a $previousUserType user with $max_accepted_machines active on-demand computation machines projects");
                     $success='';
                }
                
                
                

                $service=ServiceAutoaccept::find()->where(['user_type'=>$currentUser])->one();
                $ondemand=OndemandAutoaccept::find()->where(['user_type'=>$currentUser])->one();
                $coldStorage=ColdStorageAutoaccept::find()->where(['user_type'=>$currentUser])->one();
                $serviceLimits=ServiceLimits::find()->where(['user_type'=>$currentUser])->one();
                $ondemandLimits=OndemandLimits::find()->where(['user_type'=>$currentUser])->one();
                $coldStorageLimits=ColdStorageLimits::find()->where(['user_type'=>$currentUser])->one();
                $machineComputationLimits=MachineComputeLimits::find()->where(['user_type'=>$currentUser])->one();
                $general=Configuration::find()->one();

                

                $activeButton=$_POST['hidden-active-button'];
               

                if ($activeButton=='ondemand-button')
                {
                    $activeButtons[1]='button-active';
                    $activeTabs[1]='tab-active';
                    $hiddenActiveButton='ondemand-button';
                }
                else if ($activeButton=='service-button')
                {
                    $activeButtons[2]='button-active';
                    $activeTabs[2]='tab-active';
                    $hiddenActiveButton='service-button';
                }
                else if ($activeButton=='machines-button')
                {
                    $activeButtons[3]='button-active';
                    $activeTabs[3]='tab-active';
                    $hiddenActiveButton='machines-button';
                }
                else if ($activeButton=='cold-button')
                {
                    $activeButtons[4]='button-active';
                    $activeTabs[4]='tab-active';
                    $hiddenActiveButton='cold-button';
                   
                }
                else if ($activeButton=='email-button')
                {
                    $activeButtons[5]='button-active';
                    $activeTabs[5]='tab-active';
                    $hiddenActiveButton='email-button';
                }
                else if ($activeButton=='openstack-button')
                {
                    $activeButtons[6]='button-active';
                    $activeTabs[6]='tab-active';
                    $hiddenActiveButton='openstack-button';
                }
                else if ($activeButton=='openstack-machines-button')
                {
                    $activeButtons[7]='button-active';
                    $activeTabs[7]='tab-active';
                    $hiddenActiveButton='openstack-machines-button';
                }
                else
                {
                    $activeButtons[0]='button-active';
                    $activeTabs[0]='tab-active';
                    $hiddenActiveButton='general-button';
                }

            }

            $smtp->password=base64_decode($smtp->password);
            $openstack->decode();
            $openstackMachines->decode();

            return $this->render('configure',['form_params'=>$form_params,'service'=>$service,
                                'ondemand'=>$ondemand,'general'=>$general,
                                'coldStorage'=>$coldStorage, 'success'=>$success,
                                "hiddenUser" => $currentUser,'userTypes'=>$userTypes, 'serviceLimits'=>$serviceLimits,
                                'ondemandLimits'=>$ondemandLimits,'coldStorageLimits'=>$coldStorageLimits,
                                'activeTabs'=>$activeTabs,'activeButtons' => $activeButtons,'hiddenActiveButton'=>$hiddenActiveButton, 'smtp'=>$smtp, 'machineComputationLimits'=>$machineComputationLimits,
                                'openstack'=>$openstack,'openstackMachines'=>$openstackMachines,'pages'=>$pages]);
        }

        $smtp->password=base64_decode($smtp->password);
        $openstack->decode();
        $openstackMachines->decode();
        return $this->render('configure',['form_params'=>$form_params,'service'=>$service,
                                'ondemand'=>$ondemand,'coldStorage'=>$coldStorage,'serviceLimits'=>$serviceLimits,
                                'ondemandLimits'=>$ondemandLimits,'coldStorageLimits'=>$coldStorageLimits,'general'=>$general,
                                'userTypes'=>$userTypes, 'success'=>'',"hiddenUser" => $currentUser,
                                'activeTabs'=>$activeTabs,'activeButtons' => $activeButtons,'hiddenActiveButton'=>$hiddenActiveButton, 'smtp'=>$smtp, 'machineComputationLimits'=>$machineComputationLimits,
                                'openstack'=>$openstack,'openstackMachines'=>$openstackMachines,'pages'=>$pages]);
    }



    public function actionAdministration()
    {
        return $this->render('administration');
    }

    public function actionPeriodStatistics()
    {
        $schema=ProjectRequest::getSchemaPeriodUsage();
        $usage=ProjectRequest::getEgciPeriodUsage();
        $users=User::find()->where(['like','username','elixir-europe.org'])
        //->createCommand()->getRawSql();
        ->count();

        $usage['o_jobs']=$schema['total_jobs'];
        $usage['o_time']=$schema['total_time'];
        $usage['users']=$users;

        return $this->render('period_statistics',['usage'=>$usage]);
    }


    public function actionEmailNotifications()
    {
        
        $user=Userw::getCurrentUser();
        $user_id=$user->id;
        if (!Userw::hasRole('Admin',$superadminAllowed=true))      
        {
            return $this->render('//project/error_unauthorized');
        }
        $user_notifications=EmailEventsAdmin::find()->where(['user_id'=>$user_id])->one();
        if(empty($user_notifications))
        {
                $user_notifications=new EmailEventsAdmin;
                $user_notifications->user_id=$user_id;
                $user_notifications->save();
                
        }
        $smtp_config=true;
        $smtp=Smtp::find()->one();
        if((empty($smtp->host)) || (empty($smtp->port)) || (empty($smtp->username)) || (empty($smtp->password)) || (empty($smtp->encryption)))
        {
            Yii::$app->session->setFlash('danger', "SMTP is not configured properly to enable email notifications");
            $smtp_config=false;
        }

        if($user->load(Yii::$app->request->post()) && $user_notifications->load(Yii::$app->request->post()))
        {
            // print_r($user_notifications);
            // exit(0);
            $user->update();
            $user_notifications->update();
            Yii::$app->session->setFlash('success', "Your changes have been successfully submitted");
            return $this->redirect(['index']);
        }
        

        return $this->render('email_notifications', ['user'=>$user, 'user_notifications'=>$user_notifications, 'smtp_config'=>$smtp_config]);
    }

    public function actionTestSmtpConfiguration()
    {
        $user_email=Userw::getCurrentUser()['email'];
        $name=Yii::$app->params['name'];

        $smtp=Smtp::find()->one();
        $encrypted_password=$smtp->password;
        $decrypted_password= base64_decode($encrypted_password);
       

        $mailer = Yii::$app->mailer->setTransport([

        'class' => 'Swift_SmtpTransport',
        'host' => $smtp->host,
        'username' => $smtp->username,
        'password' => $decrypted_password,
        'port' => $smtp->port,
        'encryption' => $smtp->encryption,

        ]);

        try { 
         $r=Yii::$app->mailer->compose()
                 ->setFrom("$smtp->username")
                 ->setTo("$user_email")
                 ->setSubject('Test')
                 ->setTextBody('Plain text content')
                 ->setHtmlBody("Dear Mr/Mrs,  <br> <br> This email is send as a test to the SMTP configuration. 
                 <br> <br> Sincerely, <br> $name")
                 ->send();
                 Yii::$app->session->setFlash('success', "SMTP is configured properly. A test email has been sent to you.");
        }
        catch (\Exception $e)
        {
            Yii::$app->session->setFlash('danger', "SMTP is not configured properly.");
            
        }
        

        return $this->redirect(['configure']);

    }

    public function actionManagePages()
    {
        $pages=Page::find()->all();

        return $this->render('manage-pages',['pages'=>$pages]);

    }

    public function actionAddPage()
    {
        $model=new Page;
        $form_params =
        [
            'action' => URL::to(['administration/add-page']),
            'options' => 
            [
                'class' => 'add_page_form',
                'id'=> "add_page_form"
            ],
            'method' => 'POST'
        ];

        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $model->save();
            $this->redirect(['administration/manage-pages']);
        }

        return $this->render('add-page',['model'=>$model,'form_params'=>$form_params]);
        
    }
    public function actionEditPage($id)
    {
        $page=Page::find()->where(['id'=>$id])->one();

        if (empty($page))
        {
            return $this->render('error_page_exist');
        }

        $form_params =
        [
            'action' => URL::to(['administration/edit-page', 'id'=>$id]),
            'options' => 
            [
                'class' => 'edit_page_form',
                'id'=> "edit_page_form"
            ],
            'method' => 'POST'
        ];

        if ($page->load(Yii::$app->request->post()) && $page->validate())
        {
            $page->save();
            $this->redirect(['administration/manage-pages']);
        }

        return $this->render('edit-page',['page'=>$page,'form_params'=>$form_params]);
    }
    public function actionDeletePage($id)
    {
        $page=Page::find()->where(['id'=>$id])->one();

        if (empty($page))
        {
            return $this->render('error_page_exist');
        }

        $page->delete();
        $this->redirect(['administration/manage-pages']);

        
    }
    public function actionViewPage($id)
    {
        $page=Page::find()->where(['id'=>$id])->one();

        if (empty($page))
        {
            return $this->render('error_page_exist');
        }

        return $this->render('view-page',['page'=>$page]);
    }

    public function actionAllProjects()
    {
        $configuration=Configuration::find()->one();
        $schema_url=$configuration->schema_url;

        $project_types=Project::TYPES;
        $button_links=[0=>'/project/view-ondemand-request-user', 1=>'/project/view-service-request-user', 
                    2=>'/project/view-cold-storage-request-user', 3=>'/project/view-machine-compute-user'];

        $deleted=Project::getAllDeletedProjects();
       
        $all_projects=Project::getAllActiveProjectsAdm();
        $expired_owner=Project::getAllExpiredProjects();
        $role=User::getRoleType();
        $username=Userw::getCurrentUser()['username'];
        $user_split=explode('@',$username)[0];
        
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
            }
            else
            {
                array_push($project, "$project[username]");
                array_push($project, $remaining_days);
             }
                $active[]=$project;
        }

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


        $number_of_active=count($all_projects);
        $number_of_expired=count($expired);
        
        
       
        return $this->render('all_projects',['button_links'=>$button_links,'project_types'=>$project_types,'role'=>$role,
            'deleted'=>$deleted,'expired'=>$expired, 'active'=>$active, 'number_of_active'=>$number_of_active, 'number_of_expired'=>$number_of_expired, 'schema_url'=>$schema_url]);
    }
}
