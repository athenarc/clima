<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\Smtp;
use app\models\EmailEventsUser;
use app\models\EmailVerificationRequest;
use app\models\User;
use webvimark\modules\UserManagement\models\User as Userw;
use yii\web\UrlManager;
use yii\helpers\Url;


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
        $user_old_email = $user->email;
        $user_notifications=EmailEventsUser::find()->where(['user_id'=>$user_id])->one();
        if(empty($user_notifications))
        {
                $user_notifications=new EmailEventsUser;
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
            if($user_old_email!=$user->email){
                $user->email_confirmed=0;
            }
            $user->update();
            $user_notifications->update();
            if($user->email && $user_old_email!=$user->email){
                $email_verification_request = new EmailVerificationRequest();
                $email_verification_request->email = $user->email;
                $email_verification_request->user_id = $user->id;
                EmailVerificationRequest::revokeOldAndSaveNew($email_verification_request);
                EmailVerificationRequest::sendVerificationEmail($email_verification_request);
                Yii::$app->session->setFlash('success', "Your changes have been successfully submitted. We've sent you a verification email.");
                return $this->redirect(['user-options']);

            }


            Yii::$app->session->setFlash('success', "Your changes have been successfully submitted.");
            return $this->redirect(['user-options']);
        }

        return $this->render('user_email_notifications', ['user'=>$user, 'user_notifications'=>$user_notifications,
            'smtp_config'=>$smtp_config]);
    }

    public function actionEmailVerification() {
        $currentUser=Userw::getCurrentUser();
        $form_params = [
            'options' => [
                'id'=> "email_verification_form"
            ],
        ];

        /*  During GET:
         *  -   If a user that has a verified email ends up here, then pass the user to the view so that the user's
         *      current email is filled in the first text field. If a user with a not verified mail ends up here, then
         *      also give this user as *it is expected that non-verified emails won't be set in the email field of the
         *      user model*.
         */
        $email_verification = new EmailVerificationRequest();
        if ($currentUser->email){
            $email_verification->email = $currentUser->email;
        }
        if ($email_verification->load(Yii::$app->request->post())) {
            if ($email_verification->validate($attributeNames=['email'])) {
                $userWithGivenMail = User::find()->where(['email'=>$email_verification['email'], 'email_confirmed'=>1])->one();
                Yii::debug('Searching for user that has reserved the given email: '.var_export($userWithGivenMail, true));
                //Yii::debug('Host info: '.UrlManager::$hostInfo);
                Yii::debug(Url::to(['post/index'], true));
                if ($userWithGivenMail and $userWithGivenMail->username === $currentUser->username) {
                    // Say that this mail has already been assigned to the current user
                    Yii::debug("User $currentUser->username has already verified $email_verification->email as their email");
//                    Yii::$app->session->setFlash('danger', "The provided email, <b>$email_verification->email</b>, is already assigned to your account");]
                    $email_verification->addError('email', "Email $email_verification->email, is already assigned to your account");
                    return $this->render('email_verification', ['email_verification'=>$email_verification, 'form_params'=>$form_params]);
                }
                else if ($userWithGivenMail) {
                    Yii::debug("Given email $email_verification->email is not available");
                    Yii::$app->session->setFlash('danger', "Email address <b>$email_verification->email</b> is not available.");
                    return $this->render('email_verification', ['email_verification'=>$email_verification, 'form_params'=>$form_params]);
                }
                $email_verification->user_id=$currentUser->id;
                if ($email_verification->validate()) {
                    EmailVerificationRequest::revokeOldAndSaveNew($email_verification);
                    EmailVerificationRequest::sendVerificationEmail($email_verification);
                    return $this->redirect(array('email-verification-sent', 'email'=>$email_verification->email, 'resend'=>0));


                }
                Yii::debug($email_verification->errors);
            }
            else {
                Yii::debug('Errors found');
                Yii::debug(var_export($email_verification->errors, true));
            }

        }
        /*
        *  -   If the user already has made a mail verification request that hasn't expired yet, notify him so that he
        *      may visit his mail provider.
        *  During POST:
        *  -   Get the mail submitted, from both fields. Perform the following checks
        *      >   Assert that the mail address can be validated by Yii's email validator, including DNS lookup and IDN
        *      >   Assert that the given mail does not already exist as a verified mail in the database. If it does,
        *          then:
        *          *   Check whether it is assigned to the current user. In this case return with a related error
        *              message. If not, return with an error message informing the user that this email is not
        *              available.
        *  -   Reaching this point, the checks above have been passed. After this point, the email verification flow is
        *      followed:
        *      1.  Generate a secure token for the mail verification URL. Also infer the rest of the information needed
        *          to create an EmailVerificationRequest record (expiry, user_id) and create the record.
        *      2.  Using the created record, send the verification mail to the given mail address, containing the
        *          qualified URL for verifying the email.
        *      3.  Return by informing the user that a mail has been sent and they need to visit their mail provider
        */

        return $this->render('email_verification', ['email_verification'=>$email_verification, 'form_params'=>$form_params]);
    }

    public function actionEmailVerificationSent ($email, $resend=0){

        // resend=0 -> inform the user that a verification email has been sent to his email
        // resend=1 -> the user clicked on the resend email button. Revoke the old token, create a new one and send him a verification email
        if ($resend==1){
            $currentUser = Userw::getCurrentUser();
            $user_id = $currentUser->id;
            $email_verification = new EmailVerificationRequest();
            $email_verification->email = $email;
            $email_verification->user_id = $user_id;
            if (!empty($email_verification)) {
                EmailVerificationRequest::revokeOldAndSaveNew($email_verification);
                EmailVerificationRequest::sendVerificationEmail($email_verification);

                // redirect to the dashboard to avoid to resend email (if resend=1) on refresh
                $project = Yii::$app->createControllerByID('project');
                return $project->redirect(['project/index']);
            }

        }
        return $this->render('email_verification_sent', ['email'=>$email]);
        //return $this->redirect(['email-verification-sent', 'email'=>$email, 'redirect'=>0]);
    }

    public function actionEmailVerified () {

        $token=$_GET['token'];
        $currentUser = Userw::getCurrentUser();
        $email_verification_request=EmailVerificationRequest::find()->where(['user_id'=>$currentUser->id, 'status'=>0])->andwhere(['>', 'expiry', date('c')])->one();
        // if there is a pending request (not expired) and the token matches the token hash for this request
        // validate the email address

        if (!empty($email_verification_request) && password_verify($token, $email_verification_request->verification_token)){

            $token_expiry = $email_verification_request->expiry;
            $token_status = $email_verification_request->status;
            $user_id = $email_verification_request->user_id;
            $user=User::find()->where(['id'=>$user_id])->one();
            EmailVerificationRequest::EmailVerified($email_verification_request);
            Yii::$app->session->setFlash('success', "Your email has been verified.");
            $project = Yii::$app->createControllerByID('project');
            return $project->redirect(['project/index']);

        } else {
            // if there is no pending request or the token doent match the hash:
            // find all the user's requests 
            $email_verification_requests=EmailVerificationRequest::find()->where(['user_id'=>$currentUser->id])->all();
            foreach ($email_verification_requests as $email_request){
                $token_expiry = $email_request->expiry;
                $token_status = $email_request->status;
                $user_id = $email_request->user_id;
                $user=User::find()->where(['id'=>$user_id])->one();
                if (password_verify($token, $email_request->verification_token)){
                    $project = Yii::$app->createControllerByID('project');
                    // if the request is already verified and there is no pending request
                    // email is verified 
                    // inform user that his email is verified
                    if ($token_status == 1 && empty($email_verification_request)){
                        Yii::$app->session->setFlash('success', "Your email is already verified.");
                        return $project->redirect(['project/index']);
                    // if the request is already verified, but there is also a pending email verification request
                    // user clicked on an outdated token
                    // inform the user to view the latest email and click on the link there
                    } elseif ($token_status == 1 && !empty($email_verification_request)){
                        Yii::$app->session->setFlash('danger', "Please view the latest verification email.");
                        return $this->redirect(['project/index']);
                    }
                    // if the request is revoked or expired and the email is not confirmed
                    // either the token expired or the user sent a new request
                    // inform him that the token is no longer valid 
                    // redirect him either to re-enter his email (no pending request) or to email_verification_sent view
                    if (($token_status == 2 || $token_expiry<=date('c')) && $user->email_confirmed == 0){
                        Yii::$app->session->setFlash('danger', "This token is no longer valid.");
                        return $this->redirect(['project/index']);
                    // if the request is revoked or expired, but the email is not confirmed
                    // the user clicked on an old token
                    // inform him that his email is confirmed
                    } elseif (($token_status == 2 || $token_expiry<=date('c')) && $user->email_confirmed == 1) {
                        Yii::$app->session->setFlash('success', "Your email is already verified.");
                        return $project->redirect(['project/index']);
                    }
                }
            }
            // in any other case, inform the user that the token is not valid
            // redirct the user to the appropriate view
            Yii::$app->session->setFlash('danger', "Invalid token");
            $project = Yii::$app->createControllerByID('project');
            return $project->redirect(['project/index']);
        }


    }
}
