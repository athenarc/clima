<?php

namespace app\models;

use Yii;
use webvimark\modules\UserManagement\models\User as Userw;
use app\models\Smtp;
use app\models\Project;
use app\models\ProjectRequest;
use app\models\Email;



/**
 * This is the model class for table "email_notifications".
 *
 * @property int $user_id
 * @property bool $user_creation
 * @property bool $new_project
 * @property bool $expiring_project
 * @property bool $project_decision
 * @property bool $new_ticket
 */
class EmailEvents extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'email_events';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['user_creation', 'new_project', 'expires_30','expires_15', 'project_decision', 'new_ticket'], 'boolean'],
            [['user_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'user_creation' => 'User Creation',
            'new_project' => 'New Project',
            'expires_30' => 'Project expires in 30 days',
            'expires_15' => 'Project expires in 15 days',
            'project_decision' => 'Project Decision',
            'new_ticket' => 'New Ticket',
        ];
    }

    public function NotifyByEmail($email_type, $project_id, $message)
    {
        $smtp=Smtp::find()->one();
        $encrypted_password=$smtp->password;
        $decrypted_password= base64_decode($encrypted_password);

        // print_r($decrypted_password);
        // exit(0);
      
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
            $email_notifications=EmailEvents::find()->where(['project_decision'=>1])->all();
            $user_ids=array_column($email_notifications, 'user_id');
            $admins=Userw::find()->where(['id'=>$user_ids])
                ->andWhere(['not', ['email' => null]])
            ->all();

            $admin_emails=array_column($admins, 'email');
            $admin_ids=array_column($admins, 'id');

            foreach ($admin_emails as $user) 
            {
                Yii::$app->mailer->compose()
                 ->setFrom("$smtp->username")
                 ->setTo("$user")
                 ->setSubject('Project decision')
                 ->setTextBody('Plain text content')
                 ->setHtmlBody("Dear Mr/Mrs,  <br> <br> $message 
                 <br> <br> Sincerely, <br> EG-CI")
                 ->send();
                 usleep(2000);
            }

            $project=Project::find()->where(['id'=>$project_id])->one();
            $project_request_id=$project->latest_project_request_id;
            $project_request=ProjectRequest::find()->where(['id'=>$project_request_id])->one();

            $project_users=Userw::find()->where(['id'=>$project_request->user_list])
                ->andWhere(['not', ['email' => null]])
                ->all();
            $project_users_emails=array_column($project_users, 'email');
            $project_users_ids=array_column($project_users,'id');

            foreach ($project_users_emails as $user) 
            {
                Yii::$app->mailer->compose()
                 ->setFrom("$smtp->username")
                 ->setTo("$user")
                 ->setSubject('Project decision')
                 ->setTextBody('Plain text content')
                 ->setHtmlBody("Dear Mr/Mrs,  <br> <br> $message 
                 <br> <br> Sincerely, <br> EG-CI")
                 ->send();
                 usleep(2000);
            }

            $recipient_ids=array_merge($admin_ids, $project_users_ids);

             Yii::$app->db->createCommand()->insert('email', [
                    'recipient_ids' => $recipient_ids,
                    'type'=>$email_type,
                    'sent_at' => 'NOW()',
                    'message' => $message,
                    'project_id' => $project_id,
              ])->execute();



        }
        elseif($email_type=='user_creation')
        {
            $email_notifications=EmailEvents::find()->where(['user_creation'=>1])->all();
            $user_ids=array_column($email_notifications, 'user_id');
            $admins=Userw::find()->where(['id'=>$user_ids])
                ->andWhere(['not', ['email' => null]])
                ->all();
            $admin_emails=array_column($admins, 'email');

            foreach ($admin_emails as $user) 
            {
                Yii::$app->mailer->compose()
                 ->setFrom("$smtp->username")
                 ->setTo("$user")
                 ->setSubject('Project decision')
                 ->setTextBody('Plain text content')
                 ->setHtmlBody("Dear Mr/Mrs,  <br> <br> $message 
                 <br> <br> Sincerely, <br> EG-CI")
                 ->send();
                 usleep(2000);
            }

            $recipient_ids=array_column($admins, 'id');
            Yii::$app->db->createCommand()->insert('email', [
                    'recipient_ids' => $recipient_ids,
                    'type'=>$email_type,
                    'sent_at' => 'NOW()',
                    'message' => $message,
                    'project_id' => $project_id,
              ])->execute();
        }
        elseif($email_type=='new_project')
        {
            $email_notifications=EmailEvents::find()->where(['new_project'=>1])->all();
            $user_ids=array_column($email_notifications, 'user_id');
            $admins=Userw::find()->where(['id'=>$user_ids])
                ->andWhere(['not', ['email' => null]])
                ->all();
            $admin_emails=array_column($admins, 'email');

            foreach ($admin_emails as $user) 
            {
                Yii::$app->mailer->compose()
                 ->setFrom("$smtp->username")
                 ->setTo("$user")
                 ->setSubject('Project decision')
                 ->setTextBody('Plain text content')
                 ->setHtmlBody("Dear Mr/Mrs,  <br> <br> $message 
                 <br> <br> Sincerely, <br> EG-CI")
                 ->send();
                 usleep(2000);
            }

            $recipient_ids=array_column($admins, 'id');
            Yii::$app->db->createCommand()->insert('email', [
                    'recipient_ids' => $recipient_ids,
                    'type'=>$email_type,
                    'sent_at' => 'NOW()',
                    'message' => $message,
                    'project_id' => $project_id,
              ])->execute();
        }
        else
        {
            $email_notifications=EmailEvents::find()->where(['new_ticket'=>1])->all();
            $user_ids=array_column($email_notifications, 'user_id');
            $admins=Userw::find()->where(['id'=>$user_ids])
                ->andWhere(['not', ['email' => null]])
                ->all();
            $admin_emails=array_column($admins, 'email');

            foreach ($admin_emails as $user) 
            {
                Yii::$app->mailer->compose()
                 ->setFrom("$smtp->username")
                 ->setTo("$user")
                 ->setSubject('Project decision')
                 ->setTextBody('Plain text content')
                 ->setHtmlBody("Dear Mr/Mrs,  <br> <br> $message 
                 <br> <br> Sincerely, <br> EG-CI")
                 ->send();
                 usleep(2000);
            }

            $recipient_ids=array_column($admins, 'id');
            Yii::$app->db->createCommand()->insert('email', [
                    'recipient_ids' => $recipient_ids,
                    'type'=>$email_type,
                    'sent_at' => 'NOW()',
                    'message' => $message,
                    'project_id' => $project_id,
              ])->execute();
        }
        
    }

    public function NotifyByEmailDate($email_type, $project_id, $message, $date)
    {
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

        
        if($email_type=='expires_30')
        {
            $project=Project::find()->where(['id'=>$project_id])->one();
            $project_request_id=$project->latest_project_request_id;
            $project_request=ProjectRequest::find()->where(['id'=>$project_request_id])->one();


            $project_users=Userw::find()->where(['id'=>$project_request->user_list])
                ->andWhere(['not', ['email' => null]])
                ->all();
            $project_users_emails=array_column($project_users, 'email');

            foreach ($project_users_emails as $user) 
            {
                Yii::$app->mailer->compose()
                 ->setFrom("$smtp->username")
                 ->setTo("$user")
                 ->setSubject('Project ending')
                 ->setTextBody('Plain text content')
                 ->setHtmlBody("Dear Mr/Mrs,  <br> <br> $message 
                 <br> <br> Sincerely, <br> EG-CI")
                 ->send();
                 usleep(2000);
            }

            $recipient_ids=array_column($project_users, 'id');

           
            Yii::$app->db->createCommand()->insert('email', [
                    'recipient_ids' => $recipient_ids,
                    'type'=>$email_type,
                    'sent_at' => 'NOW()',
                    'message' => $message,
                    'project_id' => $project_id,
              ])->execute();
        }
        else
        {
            $project=Project::find()->where(['id'=>$project_id])->one();
            $project_request_id=$project->latest_project_request_id;
            $project_request=ProjectRequest::find()->where(['id'=>$project_request_id])->one();

            $project_users=Userw::find()->where(['id'=>$project_request->user_list])
                ->andWhere(['not', ['email' => null]])
                ->all();
            $project_users_emails=array_column($project_users, 'email');
            foreach ($project_users_emails as $user) 
            {
                Yii::$app->mailer->compose()
                 ->setFrom("$smtp->username")
                 ->setTo("$user")
                 ->setSubject('Project ending')
                 ->setTextBody('Plain text content')
                 ->setHtmlBody("Dear Mr/Mrs,  <br> <br> $message 
                 <br> <br> Sincerely, <br> EG-CI")
                 ->send();
                 usleep(2000);
            }

            $recipient_ids=array_column($project_users, 'id');
            Yii::$app->db->createCommand()->insert('email', [
                    'recipient_ids' => $recipient_ids,
                    'type'=>$email_type,
                    'sent_at' => 'NOW()',
                    'message' => $message,
                    'project_id' => $project_id,
              ])->execute();
        }
        
    }



}
