<?php

namespace app\models;

use Yii;
use webvimark\modules\UserManagement\models\User as Userw;
use app\models\Smtp;
use app\models\Project;
use app\models\ProjectRequest;
use app\models\Email;
use yii\db\Query;

/**
 * This is the model class for table "email_events_user".
 *
 * @property int $id
 * @property int|null $user_id
 * @property bool|null $project_decision
 * @property bool|null $new_project
 * @property bool|null $expires_1
 * @property bool|null $expires_5
 * @property bool|null $expires_30
 * @property bool|null $expires_15
 */
class EmailEventsUser extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'email_events_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'default', 'value' => null],
            [['user_id'], 'integer'],
            [['project_decision','expires_1', 'expires_5', 'expires_30', 'expires_15'], 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'project_decision' => 'Project Decision',
            'expires_1' => 'Expires 1',
            'expires_5' => 'Expires 5',
            'expires_30' => 'Expires 30',
            'expires_15' => 'Expires 15',
        ];
    }

    public static function NotifyByEmail($email_type, $project_id, $message)
    {
        $smtp=Smtp::find()->one();
        $encrypted_password=$smtp->password;
        $decrypted_password= base64_decode($encrypted_password);
        $project=Project::find()->where(['id'=>$project_id])->one();
        $name=Yii::$app->params['name'];
        if(!empty($project))
        {
            $project_name=$project->name;
        }
        $mailer = Yii::$app->mailer->setTransport([

            'class' => 'Swift_SmtpTransport',
            'host' => $smtp->host,
            'username' => $smtp->username,
            'password' => $decrypted_password,
            'port' => $smtp->port,
            'encryption' => $smtp->encryption,

        ]);

        if($email_type=='project_decision')
        {
            
            
            $all_users=self::getProjectUsers($project_id,$email_type);
            $subject='Decision on project '. $project_name;
            $recipient_ids=array_keys($all_users);

        }
        
        if (!(Yii::$app->params['disableEmail'] ?? false))
        {
            foreach ($all_users as $user) 
            {
                try
                {
                    Yii::$app->mailer->compose()
                         ->setFrom("$smtp->username")
                         ->setTo($user['email'])
                         ->setSubject($subject)
                         ->setTextBody('Plain text content')
                         ->setHtmlBody("Dear ". explode('@',$user['username'])[0] . ",  <br /> <br /> $message 
                         <br /> <br /> Sincerely, <br /> the $name Administration team.")
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

            Yii::$app->db->createCommand()->insert('email', [
                        'recipient_ids' => $recipient_ids,
                        'type'=>$email_type,
                        'sent_at' => 'NOW()',
                        'message' => $message,
                        'project_id' => $project_id,
                  ])->execute();
        }
    }

    public static function NotifyByEmailDate($email_type, $project_id, $message, $date)
    {
        $smtp=Smtp::find()->one();
        $encrypted_password=$smtp->password;
        $name=Yii::$app->params['name'];
        $decrypted_password= base64_decode($encrypted_password);
        $project=Project::find()->where(['id'=>$project_id])->one();
        $project_name=$project->name;

        Yii::$app->mailer->setTransport([
        'class' => 'Swift_SmtpTransport',
        'host' => $smtp->host,
        'username' => $smtp->username,
        'password' => $decrypted_password,
        'port' => $smtp->port,
        'encryption' => $smtp->encryption,

        ]);
        $latestRequestId = $project->latest_project_request_id;
        $projectRequest = ProjectRequest::findOne($latestRequestId);
        $submitterId = $projectRequest->submitted_by ?? null;
        $submitter = User::findOne($submitterId);
        if (strpos($email_type, 'apikey_expires_') === 0) {
            $submitterId = $projectRequest->submitted_by ?? null;
            $submitter = User::findOne($submitterId);
            echo "📋 Prepared to notify user ID: {$submitterId}\n";

            if ($submitter && $submitter->email) {
                $project_users = [
                    $submitter->id => [
                        'email' => $submitter->email,
                        'username' => $submitter->username
                    ]
                ];
                echo "📧 Will send to: {$submitter->email}\n";

                $recipient_ids = [$submitter->id];
                $subject = 'API Token Expiry Notification for Project ' . $project_name;
            } else {
                echo "Submitter not found or has no email for project ID: $project_id\n";
                return;
            }
        }
        if (
            strpos($email_type, 'expires_') === 0 ||
            strpos($email_type, 'expired_resources_notify_') === 0
        ) {
            $project_users = self::getProjectUsers($project_id, $email_type);
            if (empty($project_users)) {
                echo "⚠️ No users found for project $project_id with email type $email_type\n";
                return;
            }
            $recipient_ids = array_keys($project_users);
            $subject = 'Expiration of project ' . $project_name;
        } else {
            echo "⚠️ Unknown email type: $email_type\n";
            return;
        }



        if($email_type=='expires_30')
        {

            $project_users=self::getProjectUsers($project_id,$email_type);
            $subject='Expiration of project '. $project_name;
            $recipient_ids=array_keys($project_users);

        }
        elseif($email_type=='expires_15')
        {
            $project_users=self::getProjectUsers($project_id,$email_type);
            $subject='Expiration of project '. $project_name;
            $recipient_ids=array_keys($project_users);;
        }
        elseif($email_type=='expires_1')
        {
            $project_users=self::getProjectUsers($project_id,$email_type);
            $subject='Expiration of project '. $project_name;
            $recipient_ids=array_keys($project_users);
        }
        elseif($email_type=='expires_5')
        {
            $project_users=self::getProjectUsers($project_id,$email_type);
            $subject='Expiration of project '. $project_name;
            $recipient_ids=array_keys($project_users);
        }

        if (!(Yii::$app->params['disableEmail'] ?? false))
        {
            foreach ($project_users as $user)
            {
                try
                {
                    $result = Yii::$app->mailer->compose()
                         ->setFrom("$smtp->username")
                         ->setTo($user['email'])
                         ->setSubject($subject)
                         ->setTextBody('Plain text content')
                         ->setHtmlBody("Dear ". explode('@',$user['username'])[0]. ",  <br /> <br /> $message 
                         <br /> <br /> Sincerely, <br /> the $name Administration team.")
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

            Yii::$app->db->createCommand()->insert('email', [
                        'recipient_ids' => $recipient_ids,
                        'type'=>$email_type,
                        'sent_at' => 'NOW()',
                        'message' => $message,
                        'project_id' => $project_id,
                  ])->execute();
        }


    }
    public static function NotifyInactiveUserByEmailDate($email_type, $message, $date, $user_id, $email, $username)
    {
        $smtp = Smtp::find()->one();
        $decrypted_password = base64_decode($smtp->password);
        $name = Yii::$app->params['name'];

        if (!$email) {
            echo "❌ User $user_id has no email address.\n";
            return;
        }

        $recipient_ids = [$user_id];
        $subject = 'Inactive Account';


        Yii::$app->mailer->setTransport([
            'class' => 'Swift_SmtpTransport',
            'host' => $smtp->host,
            'username' => $smtp->username,
            'password' => $decrypted_password,
            'port' => $smtp->port,
            'encryption' => $smtp->encryption,
        ]);

        if (!(Yii::$app->params['disableEmail'] ?? false)) {
            try {
                Yii::$app->mailer->compose()
                    ->setFrom($smtp->username)
                    ->setTo($email)
                    ->setSubject($subject)
                    ->setTextBody('Plain text version')
                    ->setHtmlBody("Dear " . explode('@', $username)[0] . ",<br><br>$message<br><br>Sincerely,<br>the $name Team")
                    ->send();
            } catch (\Throwable $e) {
                echo "❌ Failed to send email to $email: " . $e->getMessage() . "\n";
            }

            Yii::$app->db->createCommand()->insert('email', [
                'recipient_ids' => $recipient_ids,
                'type' => $email_type,
                'sent_at' => new \yii\db\Expression('NOW()'),
                'message' => $message,
                'project_id' => null,
                'related_user_id' => $user_id,
            ])->execute();
        }
    }




    public static function getProjectUsers($project_id, $email_event)
    {
        $project=Project::find()->where(['id'=>$project_id])->one();
        $project_request_id=$project->latest_project_request_id;
        $project_request=ProjectRequest::find()->where(['id'=>$project_request_id])->one();

        $query=new Query;
        $users=$query->select(['u.id', 'u.email','u.username'])
            ->from('user as u')
            ->innerJoin('email_events_user as e', 'e.user_id=u.id')
            ->where(['in','u.id',$project_request->user_list])
            ->andWhere(["e.$email_event"=>1])
            ->andWhere(['not',['u.email'=>null]])
            ->all();
    
        $project_users=[];
        foreach($users as $user)
        {
                $project_users[$user['id']]=['email'=>$user['email'], 'username'=>$user['username']];
        }

        return $project_users;


    }

    public static function NotifyByEmailUserUpgrade($user_id, $revoke, $assign){

        $smtp=Smtp::find()->one();
        $encrypted_password=$smtp->password;
        $decrypted_password= base64_decode($encrypted_password);
        $name=Yii::$app->params['name'];
        $mailer = Yii::$app->mailer->setTransport([

            'class' => 'Swift_SmtpTransport',
            'host' => $smtp->host,
            'username' => $smtp->username,
            'password' => $decrypted_password,
            'port' => $smtp->port,
            'encryption' => $smtp->encryption,

        ]);

        $user=self::getUpgradedUser($user_id);
        $ids=array($user[2]);
        $subject='User upgrade';
        //return $user;

        $roles_assign = array();
        $roles_revoke = array();
        $assign_bronze = 0;
        $assign_silver = 0;
        $assign_gold = 0;
        $revoke_bronze = 0;
        $revoke_silver = 0;
        $revoke_gold = 0;
        $role_assigned='';

        foreach ($revoke as $re) {
            array_push($roles_revoke, $re);
        }
        foreach ($assign as $as) {
            array_push($roles_assign, $as);
            if ($as=='Bronze'){
                $assign_bronze=1;
            } elseif($as=='Silver'){
                $assign_silver = 1;
            } elseif($as=='Gold'){
                $assign_gold = 1;
            }
        }
        foreach ($revoke as $rev) {
            array_push($roles_revoke, $re);
            if ($re=='Bronze'){
                $revoke_bronze=1;
            } elseif($re=='Silver'){
                $revoke_silver = 1;
            } elseif($re=='Gold'){
                $revoke_gold = 1;
            }
        }

        if ($assign_silver==1 && $revoke_gold!=1 && $assign_gold!=1){
            $role_assigned='silver';
        }
        if ($assign_gold==1){
            $role_assigned='gold';
        }
        $message = "We would like to inform you that you have been upgraded to " .$role_assigned. " user." ;
        if (!empty($role_assigned)) {
            if (!(Yii::$app->params['disableEmail'] ?? false))
            {
                try
                {
                    Yii::$app->mailer->compose()
                            ->setFrom("$smtp->username")
                            ->setTo($user[0])
                            ->setSubject($subject)
                            ->setTextBody('Plain text content')
                            ->setHtmlBody("Dear ". explode('@',$user[1])[0] . ",  <br /> <br /> $message 
                            <br /> <br /> Sincerely, <br /> the $name Administration team.")
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
            

                Yii::$app->db->createCommand()->insert('email', [
                            'recipient_ids' => $ids,
                            'type'=>'user_upgrade',
                            'sent_at' => 'NOW()',
                            'message' => $message
                    ])->execute();
            }
        }

    }


    public static function getUpgradedUser($user_id) {

        // $project=Project::find()->where(['id'=>$project_id])->one();
        // $project_request_id=$project->latest_project_request_id;
        // $project_request=ProjectRequest::find()->where(['id'=>$project_request_id])->one();

        $query=new Query;
        $user=$query->select(['u.id','u.email','u.username'])
            ->from('user as u')
            ->where(['not',['u.email'=>null]])
            ->andWhere(["u.id"=>$user_id])
            ->one();
    
        $up_user = array($user['email'], $user['username'], $user['id']);

        // $up_user[$user['id']]=['email'=>$user['email'], 'username'=>$user['username']];

        return $up_user;


    }











}
