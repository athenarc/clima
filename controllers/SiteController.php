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
use app\models\StorageAutoaccept;
use app\models\Configuration;
use app\models\User;
use yii\helpers\Url;
use app\models\Notification;
use webvimark\modules\UserManagement\models\User as Userw;
use app\models\EmailEventsAdmin;
use app\models\Page;
use app\models\Analytics;
use app\models\Project;
use app\models\ProjectRequest;

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
    public function actionPolicyAcceptance()
    {
        // Check if the user is a guest (not logged in)
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $user = Yii::$app->user->identity;

        // Check if the user has already accepted the policy
        if ($user->policy_accepted) {
            Yii::$app->session->setFlash('info', 'You have already accepted the new policy.');
            return $this->redirect(['site/index']);
        }

        if (Yii::$app->request->isPost) {
            $user->policy_accepted = true;
            if ($user->save(false, ['policy_accepted'])) {
                Yii::$app->session->setFlash('success', 'Thank you for accepting the policy.');
                return $this->redirect(['site/index']);
            } else {
                Yii::$app->session->setFlash('error', 'Failed to save your acceptance. Please try again.');
            }
        }

        return $this->render('policy_acceptance', [
            'policyContent' => 'This is your policy content.',
        ]);
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

            /*
             * Auth server changed, so persistent id changed.
             * In order not to break database of users already existing
             * the search is performed by username instead of persistent id.
             * Someone didn't think that far ahead it seems. :)
             */
            $identityP=User::findByPersistentId($persistent_id);
            $identityU=User::findByUsername($username);
            $identity='';
            
            if (empty($identityU) && empty($identityP))
            {
                /*
                 * If user doesn't exist
                 */
                User::createNewUser($username, $persistent_id);
                $identity=User::findByUsername($username);
                $message="A new user with username $username has been created";
                EmailEventsAdmin::NotifyByEmail('user_creation', -1,$message);
            }
            else if ((!empty($identityU)) && empty($identityP))
            {
                /*
                 * If auth server and persistent ID changed
                 */
                $identityU->password_hash=$persistent_id;
                $identityU->save();
                $identity=$identityU;
            }
            else if ((!empty($identityP)) && empty($identityU))
            {
                /*
                 * If user was renamed
                 */
                $identityP->username=$username;
                $identityP->save();
                $identity=$identityP;
            }
            else
            {
                /*
                 * If user was not altered in any way.
                 *
                 * Any other case in here means that there was an error
                 * because both the username and the persistent id 
                 * exist and point to different users (not really expected
                 * but just to be safe). 
                 */
                if ($identityP==$identityU)
                {
                    $identity=$identityU;
                }

            }
            
            if (empty($identity))
            {
                Yii::$app->session->setFlash('danger', 'There was an error with your login. Please contact an administrator');
                return $this->redirect(['site/index']);
            }
            else
            {
                Yii::$app->user->login($identity,0);
                return $this->redirect(['project/index']);
            }
            
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
        $analytics=Analytics::find()->all();

        return $this->render('privacy',['page'=>$page,'analytics'=>$analytics]);
    }

    public function actionNotificationRedirect($id)
    {

        //type -1 -> reject
        //type 0 -> new ticket
        //type 1 -> expiration
        //type 2 -> approved, auto-approved
        $notification=Notification::find()->where(['id'=>$id])->one();

        $notification->markAsSeen();

        if (empty($notification->url))
        {
            return $this->redirect(['project/index']);
        }
         if ($notification->type == 0 && Userw::hasRole('Admin', $superadminAllowed=true)){
             return $this->redirect($notification->url);
        } elseif ($notification->type == 0) {
            $url_split = explode('=', $notification->url);
            $ticket_id = $url_split[2];
            $url = 'index.php?r=ticket-user%2Fview&id='.$ticket_id;
            return $this->redirect($url);
        } elseif ($notification->type == -1 || $notification->type == 2) {
            $current_user=Userw::getCurrentUser()['id'];
            $message_split = explode("'", $notification->message);
            $project_name = $message_split[1];
            $project=Project::find()->where(['name'=>$project_name])->one();
            $latest_request=ProjectRequest::find()->where(['id'=>$project['latest_project_request_id']])->one();
            $owner=$latest_request['submitted_by'];
            //if the current user is the owner of the project, redirect him to his requests
            if ($owner==$current_user){
                return $this->redirect($notification->url);
            //if the currect user is not the owner redirect him to project details page
            } else{
                return $this->redirect(['/project/view-request-user','id'=>$project['latest_project_request_id']]);
            }
        }else{
            return $this->redirect($notification->url);
        }


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


        return $this->render('notification_history',['notifications'=>$notifications,'pages'=>$pages,'typeClass'=>$typeClass]);
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

    public function actionHealth()
    {
        $response=Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->setStatusCode(200); 
        $response->send();
        return;

    }

}
