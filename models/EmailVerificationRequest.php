<?php

namespace app\models;

use Yii;
use yii\base\Security;
use app\models\Smtp;
use webvimark\modules\UserManagement\models\User;

/**
 * This is the model class for table "email_verification".
 *
 * @property string $verification_token
 * @property int $user_id
 * @property int $created_at
 * @property string $email
 * @property int $expiry
 * @property int $status
 */
class EmailVerificationRequest extends \yii\db\ActiveRecord
{
    const STATUS=['Pending'=> 0, 'Complete' => 1, 'Revoked' => 2];

    public static function tableName()
    {
        return 'email_verification';
    }

    private static function generateVerificationToken() {

    }

    public function rules()
    {
        return [
            ['email', 'trim'],
            ['verification_token', 'default', 'value' => function ($model, $attribute) {
                $securityProvider = new Security();
                return $securityProvider->generateRandomString();
            }],
            ['created_at', 'default', 'value' => function ($model, $attribute) {
                return date('c');
            }],
            ['expiry', 'default', 'value' => function ($model, $attribute) {
                return date('c', strtotime($model->created_at.' + '.(Yii::$app->params['email_verification']['validity_period'] ?? '1 day')));
            }],
            [['verification_token', 'user_id', 'email', 'expiry'], 'required'],
            ['email', 'email', 'allowName'=>true, 'checkDNS'=>true],
            [['created_at','expiry'], 'string'],
            // Make sure that currently active verification tokens do not have the assigned verification token
            ['verification_token', 'unique', 'filter'=>'expiry>=\''.date('c').'\' AND status=0'],
            ['status', 'in', 'range'=>EmailVerificationRequest::STATUS]
        ];
    }

    public static function revokeOldAndSaveNew($emailVerification) {
        return EmailVerificationRequest::getDb()->transaction(function($db) use ($emailVerification) {
            // Revoke currently pending email verifications for the requesting user
            EmailVerificationRequest::updateAll(['status'=>EmailVerificationRequest::STATUS['Revoked']], $condition=['user_id'=>$emailVerification->user_id, 'status'=>EmailVerificationRequest::STATUS['Pending']]);
            return $emailVerification->save();
        });
    }

    public static function sendVerificationEmail($emailVerification) {

        $hashed_token = password_hash($emailVerification->verification_token, PASSWORD_DEFAULT);
        $token = $emailVerification->verification_token;
        Yii::$app->db->createCommand()->update('email_verification',['verification_token'=>$hashed_token], "verification_token='$emailVerification->verification_token'")->execute();

        $smtp=Smtp::find()->one();
        $encrypted_password=$smtp->password;
        $decrypted_password= base64_decode($encrypted_password);
        $name=Yii::$app->params['name'];
        $url=Yii::$app->params['email_verification']['email_verification_url'];
        $verification_url = $url.$token;
        $currentUser = User::getCurrentUser();
        // update the email of the user and set email_confirmed=0
        Yii::$app->db->createCommand()->update('user',['email'=>$emailVerification->email, 'email_confirmed'=>0], "id=$currentUser->id")->execute();


        $mailer = Yii::$app->mailer->setTransport([

            'class' => 'Swift_SmtpTransport',
            'host' => $smtp->host,
            'username' => $smtp->username,
            'password' => $decrypted_password,
            'port' => $smtp->port,
            'encryption' => $smtp->encryption,
        ]);

        $message = 'To verify your email, click the following url: <br />'.$verification_url.
        '<br /> Please keep in mind that the validity of your token is '. Yii::$app->params['email_verification']['validity_period'];
        $subject='HYPATIA email verification';
        $email_to_be_verified=$emailVerification->email;

        if (!isset(Yii::$app->params['disableEmail']) || (isset(Yii::$app->params['disableEmail']) && Yii::$app->params['disableEmail']==false)){

            try
            {
                Yii::$app->mailer->compose()
                     ->setFrom("$smtp->username")
                     ->setTo($email_to_be_verified)
                     ->setSubject($subject)
                     ->setTextBody('Plain text content')
                     ->setHtmlBody("Dear " .explode('@',$currentUser->username)[0].  "<br /> $message 
                     <br />  Sincerely, <br /> the $name Administration team.")
                     ->send();
                     usleep(2000);
            }
            catch (Throwable $e)
            {
                ;
            }
            catch (\Swift_TransportException $e)
            {
                ;
            }

        }
    }

    public static function EmailVerified ($verified_request){

        // set the status of the verified email request to completed
        Yii::$app->db->createCommand()->update('email_verification',['status'=>EmailVerificationRequest::STATUS['Complete']], "verification_token='$verified_request->verification_token'")->execute();
        // update the user with the verified email
        Yii::$app->db->createCommand()->update('user',['email'=>$verified_request->email, 'email_confirmed'=>1], "id=$verified_request->user_id")->execute();

    }
}

?>