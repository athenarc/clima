<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\Smtp;
use app\models\EmailEvents;
use webvimark\modules\UserManagement\models\User as Userw;


/*
 * Controller for the management of Users
 */

class PersonalController extends Controller
{
    // Determine the layout file.
    public $freeAccess = false;

    public function behaviors()
    {
        return [
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
            'ghost-access'=> [
                'class' => 'webvimark\modules\UserManagement\components\GhostAccessControl',
            ],
        ];
    }

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

    public function actionIndex(){
        return $this->render('index');
    }

    public function actionSuperadminActions(){
        return $this->render('superadmin_actions');
    }

    public function actionUserOptions()
    {
        return $this->render('user_options');
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
            return $this->redirect(['user-options']);
        }

        return $this->render('user_email_notifications', ['user'=>$user, 'user_notifications'=>$user_notifications,
            'smtp_config'=>$smtp_config]);
    }

}