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
use app\models\Configuration;
use app\models\User;
use yii\helpers\Url;
use app\models\Notification;
use webvimark\modules\UserManagement\models\User as Userw;
use app\models\EmailEvents;
use app\models\Page;

class SiteController extends Controller
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
    public function actionIndex()
    {
        $config=Configuration::find()->one();
        $id=$config->home_page;
        $page=Page::find()->where(['id'=>$id])->one();

        return $this->render('index',['page'=>$page]);
    }

    public function actionHelp()
    {
        $config=Configuration::find()->one();
        $id=$config->help_page;
        $page=Page::find()->where(['id'=>$id])->one();

        return $this->render('help',['page'=>$page]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    

    public function actionAuthConfirmed($token)
    {
    
            
        if (empty($token))
        {
            return $this->render('login_error');
        }
        else
        {
            $query=new \yii\db\Query;

            $sql=$query->select('*')->from('auth_user')->where(['token'=>$token])->createCommand()->getRawSql();

            $result=Yii::$app->db2->createCommand($sql)->queryOne();
            
            $username=$result['username'];
            $persistent_id=$result['persistent_id'];

            $identity=User::findByPersistentId($persistent_id);
            
            if (empty($identity))
            {
                User::createNewUser($username, $persistent_id);
                $identity=User::findByUsername($username);
                $message="A new user with username $username has been created";
                EmailEvents::NotifyByEmail('user_creation', -1,$message);
            }
            else
            {
                if ($identity->username!=$username)
                {
                    $identity->username=$username;
                }
            }

            Yii::$app->user->login($identity,0);

            return $this->redirect(['project/index']);
        }
        

        // $model->password = '';
        // return $this->render('login', [
        //     'model' => $model,
        // ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }


    public function actionUnderConstruction()
    {
        return $this->render('under_construction');
    }

    public function actionPrivacy()
    {
        $config=Configuration::find()->one();
        $id=$config->privacy_page;
        $page=Page::find()->where(['id'=>$id])->one();

        return $this->render('privacy',['page'=>$page]);
    }

    public function actionNotificationRedirect($id)
    {
        $notification=Notification::find()->where(['id'=>$id])->one();

        $notification->markAsSeen();

        return $this->redirect($notification->url);


    }

    public function actionMarkAllNotificationsSeen()
    {
        Notification::markAllAsSeen();
    }

    public function actionNotificationHistory()
    {
        $typeClass=[Notification::DANGER=>'notification-danger', Notification::NORMAL=>'', 
                    Notification::WARNING=>'notification-warning', Notification::SUCCESS=>'notification-success'];
        $results=Notification::getNotificationHistory();
        $pages=$results[0];
        $notifications=$results[1];


        return $this->render('notification_history',['notifications'=>$notifications,'pages'=>$pages,'typeClass'=>$typeClass,]);
    }

    public function actionSshTutorial()
    {
        return $this->render('ssh_tutorial');
    }

    public function actionMassNotification()
    {
        $notification=new Notification;

        $form_params =
        [
            'action' => URL::to(['site/mass-notification']),
            'options' => 
            [
                'class' => 'mass_notification_form',
                'id'=> "mass_notification_form"
            ],
            'method' => 'POST'
        ];

        if ( ($notification->load(Yii::$app->request->post())) && $notification->validate() )
        {
            $users=User::find()->all();

            if ($notification->urlType=='external')
            {
                $notification->url=Url::to($notification->url);
            }
            else
            {
                $notification->url=Url::to([$notification->url]);
            }

            foreach ($users as $user)
            {
                Notification::notify($user->id, $notification->message, $notification->type,$notification->url);
            }

            $success='Message sent to all users';

            return $this->render('//administration/index',['success'=>$success]);

            

        }


        return $this->render('mass_notification',['form_params'=>$form_params, 'notification'=>$notification]);
    }

    public function actionAdditionalStorageTutorial()
    {
        //
        return $this->render('additional_storage_tutorial');
    }

}
