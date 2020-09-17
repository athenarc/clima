<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
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
        $general=Configuration::find()->one();
        
        $activeButtons=['','','',''];
        $activeTabs=['','','',''];

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

        if ( ($service->load(Yii::$app->request->post())) && ($general->load(Yii::$app->request->post())) 
            &&  ($ondemand->load(Yii::$app->request->post())) && ($coldStorage->load(Yii::$app->request->post()))
            && ($coldStorageLimits->load(Yii::$app->request->post())) && ($serviceLimits->load(Yii::$app->request->post())) 
            && ($ondemandLimits->load(Yii::$app->request->post())) )
        {
            //print_r($_POST);
            //exit(0);
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
                                'activeTabs'=>$activeTabs,'activeButtons' => $activeButtons,'hiddenActiveButton'=>$hiddenActiveButton]);
        }

        return $this->render('configure',['form_params'=>$form_params,'service'=>$service,
                                'ondemand'=>$ondemand,'coldStorage'=>$coldStorage,'serviceLimits'=>$serviceLimits,
                                'ondemandLimits'=>$ondemandLimits,'coldStorageLimits'=>$coldStorageLimits,'general'=>$general,
                                'userTypes'=>$userTypes, 'success'=>'',"hiddenUser" => $currentUser,
                                'activeTabs'=>$activeTabs,'activeButtons' => $activeButtons,'hiddenActiveButton'=>$hiddenActiveButton]);
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
}
