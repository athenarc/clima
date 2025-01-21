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
use app\models\OndemandRequest;
use app\models\StorageRequest;
use app\models\User;
use yii\helpers\Url;
use yii\helpers\Html;
use webvimark\modules\UserManagement\models\User as Userw;


class ApiController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $freeAccess = true;
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
    

    public function actionActiveProjects($username)
    {
        // Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $records=Project::getActiveProjectsApi($username);
        
        return $this->asJson($records);
    }

    public function actionOndemandProjectQuotas($username,$project)
    {
        // Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $records=Project::getOndemandProjectQuotas($username,$project);
        
        return $this->asJson($records);
    }

    public function actionActiveOndemandQuotas($username)
    {

        $records=Project::getActiveOndemandQuotasApi($username);
        
        
        
        return $this->asJson($records);
    }

    public function actionAllOndemandQuotas($username)
    {

        $records=Project::getAllOndemandQuotasApi($username);
        
        
        return $this->asJson($records);
    }
}


