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
use yii\helpers\Url;
use app\models\ProjectRequest;
use app\models\User;
use app\models\EmailEvents;
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
        
      
        
        $userTypes=["gold"=>"Gold","silver"=>"Silver", "temporary"=>"Temporary"];
        $currentUser=(!isset($_POST['currentUserType'])) ? "temporary": $_POST['currentUserType'] ;

        //new models
        $service=ServiceAutoaccept::find()->where(['user_type'=>$currentUser])->one();
        $ondemand=OndemandAutoaccept::find()->where(['user_type'=>$currentUser])->one();
        $coldStorage=ColdStorageAutoaccept::find()->where(['user_type'=>$currentUser])->one();
        $serviceLimits=ServiceLimits::find()->where(['user_type'=>$currentUser])->one();
        $ondemandLimits=OndemandLimits::find()->where(['user_type'=>$currentUser])->one();
        $coldStorageLimits=ColdStorageLimits::find()->where(['user_type'=>$currentUser])->one();
        $smtp= Smtp::find()->one();
        
        $general=Configuration::find()->one();
        $pages=Page::getPagesDropdown();
        
        $activeButtons=['','','','',''];
        $activeTabs=['','','','',''];

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

        if ( ($service->load(Yii::$app->request->post())) && ($general->load(Yii::$app->request->post())) 
            &&  ($ondemand->load(Yii::$app->request->post())) && ($coldStorage->load(Yii::$app->request->post()))
            && ($coldStorageLimits->load(Yii::$app->request->post())) && ($serviceLimits->load(Yii::$app->request->post())) 
            && ($ondemandLimits->load(Yii::$app->request->post())) && ($smtp->load(Yii::$app->request->post())) )
        {
            
            $password=$smtp->password;
            $encrypted_password=base64_encode($password);
            $smtp->password=$encrypted_password;
            $smtp->update();
            

           

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
                $ondemand->updateDB($previousUserType);
                $service->updateDB($previousUserType);
                $coldStorage->updateDB($previousUserType);
                $ondemandLimits->updateDB($previousUserType);
                $serviceLimits->updateDB($previousUserType);
                $coldStorageLimits->updateDB($previousUserType);

                $service=ServiceAutoaccept::find()->where(['user_type'=>$currentUser])->one();
                $ondemand=OndemandAutoaccept::find()->where(['user_type'=>$currentUser])->one();
                $coldStorage=ColdStorageAutoaccept::find()->where(['user_type'=>$currentUser])->one();
                $serviceLimits=ServiceLimits::find()->where(['user_type'=>$currentUser])->one();
                $ondemandLimits=OndemandLimits::find()->where(['user_type'=>$currentUser])->one();
                $coldStorageLimits=ColdStorageLimits::find()->where(['user_type'=>$currentUser])->one();
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
                else if ($activeButton=='cold-button')
                {
                    $activeButtons[3]='button-active';
                    $activeTabs[3]='tab-active';
                    $hiddenActiveButton='cold-button';
                   
                }
                else if ($activeButton=='email-button')
                {
                    $activeButtons[4]='button-active';
                    $activeTabs[4]='tab-active';
                    $hiddenActiveButton='email-button';
                }
                else
                {
                    $activeButtons[0]='button-active';
                    $activeTabs[0]='tab-active';
                    $hiddenActiveButton='general-button';
                }

            }

            return $this->render('configure',['form_params'=>$form_params,'service'=>$service,
                                'ondemand'=>$ondemand,'general'=>$general,
                                'coldStorage'=>$coldStorage, 'success'=>'Configuration successfully updated!',
                                "hiddenUser" => $currentUser,'userTypes'=>$userTypes, 'serviceLimits'=>$serviceLimits,
                                'ondemandLimits'=>$ondemandLimits,'coldStorageLimits'=>$coldStorageLimits,
                                'activeTabs'=>$activeTabs,'activeButtons' => $activeButtons,'hiddenActiveButton'=>$hiddenActiveButton, 'smtp'=>$smtp,'pages'=>$pages]);
        }

        return $this->render('configure',['form_params'=>$form_params,'service'=>$service,
                                'ondemand'=>$ondemand,'coldStorage'=>$coldStorage,'serviceLimits'=>$serviceLimits,
                                'ondemandLimits'=>$ondemandLimits,'coldStorageLimits'=>$coldStorageLimits,'general'=>$general,
                                'userTypes'=>$userTypes, 'success'=>'',"hiddenUser" => $currentUser,
                                'activeTabs'=>$activeTabs,'activeButtons' => $activeButtons,'hiddenActiveButton'=>$hiddenActiveButton, 'smtp'=>$smtp,'pages'=>$pages]);
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
        $user_notifications=EmailEvents::find()->where(['user_id'=>$user_id])->one();
        if(empty($user_notifications))
        {
                $user_notifications=new EmailEvents;
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
                 <br> <br> Sincerely, <br> EG-CI")
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
}
