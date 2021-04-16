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

    public static function NotifyByEmail($email_type, $project_id, $message)
    {
        $smtp=Smtp::find()->one();
        $encrypted_password=$smtp->password;
        $decrypted_password= base64_decode($encrypted_password);
        $project=Project::find()->where(['id'=>$project_id])->one();
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
            
            $moderators=self::getModerators($email_type);
            $project_users=self::getProjectUsers($project_id);
            $subject='Decision on '. $project_name;
            $all_users=$moderators+$project_users;
            $moderator_ids=array_keys($moderators);
            $project_users_ids=array_keys($project_users);
            $recipient_ids=array_unique(array_merge($moderator_ids, $project_users_ids));

        }
        elseif($email_type=='user_creation')
        {
            $all_users=self::getAdmins($email_type);
            $subject='User creation';
            $recipient_ids=array_keys($all_users);
        }

        elseif($email_type=='new_project')
        {
            $all_users=self::getModerators($email_type);
            $subject='New project';
            $recipient_ids=array_keys($all_users);

        }
        elseif($email_type=='new_ticket')
        {
            $all_users=self::getAdmins($email_type);
            $subject='New ticket';
            $recipient_ids=array_keys($all_users);

        }

        foreach ($all_users as $user) 
        {
                Yii::$app->mailer->compose()
                 ->setFrom("$smtp->username")
                 ->setTo($user['email'])
                 ->setSubject($subject)
                 ->setTextBody('Plain text content')
                 ->setHtmlBody("Dear ". explode('@',$user['username'])[0]. ",  <br> <br> $message 
                 <br> <br> Sincerely, <br> EG-CI")
                 ->send();
                 usleep(2000);
        }

        Yii::$app->db->createCommand()->insert('email', [
                    'recipient_ids' => $recipient_ids,
                    'type'=>$email_type,
                    'sent_at' => 'NOW()',
                    'message' => $message,
                    'project_id' => $project_id,
              ])->execute();
        
    }

    public static function NotifyByEmailDate($email_type, $project_id, $message, $date)
    {
        $smtp=Smtp::find()->one();
        $encrypted_password=$smtp->password;
        $decrypted_password= base64_decode($encrypted_password);
        $project=Project::find()->where(['id'=>$project_id])->one();
        $project_name=$project->name;

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
            $moderators=self::getModerators($email_type);
            $project_users=self::getProjectUsers($project_id);
            $subject='Expiration of '. $project_name;
            $all_users=$moderators+$project_users;
            $moderator_ids=array_keys($moderators);
            $project_users_ids=array_keys($project_users);
            $recipient_ids=array_unique(array_merge($moderator_ids, $project_users_ids));

        }
        elseif($email_type=='expires_15')
        {
            $moderators=self::getModerators($email_type);
            $project_users=self::getProjectUsers($project_id);
            $subject='Expiration of '. $project_name;
            $all_users=$moderators+$project_users;
            $moderator_ids=array_keys($moderators);
            $project_users_ids=array_keys($project_users);
            $recipient_ids=array_unique(array_merge($moderator_ids, $project_users_ids));
        }

        foreach ($all_users as $user) 
        {
                Yii::$app->mailer->compose()
                 ->setFrom("$smtp->username")
                 ->setTo($user['email'])
                 ->setSubject($subject)
                 ->setTextBody('Plain text content')
                 ->setHtmlBody("Dear ". explode('@',$user['username'])[0]. ",  <br> <br> $message 
                 <br> <br> Sincerely, <br> EG-CI")
                 ->send();
                 usleep(2000);
        }

        Yii::$app->db->createCommand()->insert('email', [
                    'recipient_ids' => $recipient_ids,
                    'type'=>$email_type,
                    'sent_at' => 'NOW()',
                    'message' => $message,
                    'project_id' => $project_id,
              ])->execute();

        
    }



    public static function getModerators($email_event)
    {
        $moderat=new Query;
        $moderat=$moderat->select(['u.id', 'u.email', 'u.username'])
            ->from('auth_assignment as p')
            ->innerJoin('user as u', 'u.id=p.user_id')
            ->innerJoin('email_events as e', 'e.user_id=u.id')
            ->where(['p.item_name'=>'Moderator'])
            ->andWhere(["e.$email_event"=>1])
            ->andWhere(['not',['u.email'=>null]])
            ->all();

        $moderator_emails=[];        
        foreach($moderat as $mod)
        {
            $moderator_emails[$mod['id']]=['email'=>$mod['email'], 'username'=>$mod['username']];
        }

        return $moderator_emails;

    }

    public static function getAdmins($email_event)
    {
        $query=new Query;
        $result=$query->select(['u.id', 'u.email','u.username'])
            ->from('auth_assignment as p')
            ->innerJoin('user as u', 'u.id=p.user_id')
            ->innerJoin('email_events as e', 'e.user_id=u.id')
            ->where(['p.item_name'=>'Admin'])
            ->andWhere(["e.$email_event"=>1])
            ->andWhere(['not',['u.email'=>null]])
            ->all();

        $admin_emails=[];        
        foreach($result as $res)
        {
            $admin_emails[$res['id']]=['email'=>$res['email'], 'username'=>$res['username']];
        }
        
        return $admin_emails;

    }

    public static function getProjectUsers($project_id)
    {
        $project=Project::find()->where(['id'=>$project_id])->one();
        $project_request_id=$project->latest_project_request_id;
        $project_request=ProjectRequest::find()->where(['id'=>$project_request_id])->one();
        $project_users_all=Userw::find()->where(['id'=>$project_request->user_list])
                    ->andWhere(['not', ['email' => null]])
                    ->all();

        $project_users=[];
        foreach($project_users_all as $user)
        {
                $project_users[$user->id]=['email'=>$user->email, 'username'=>$user->username];
        }

        return $project_users;


    }



}
