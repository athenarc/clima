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
 * This is the model class for table "email_events_admin".
 *
 * @property int $id
 * @property int|null $user_id
 * @property bool|null $user_creation
 * @property bool|null $new_ticket
 * @property bool|null $expires_1
 * @property bool|null $expires_5
 */
class EmailEventsAdmin extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'email_events_admin';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'default', 'value' => null],
            [['user_id'], 'integer'],
            [['user_creation', 'new_ticket', 'expires_1', 'expires_5'], 'boolean'],
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
            'user_creation' => 'User Creation',
            'new_ticket' => 'New Ticket',
            'expires_1' => 'Expires 1',
            'expires_5' => 'Expires 5',
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

        if($email_type=='user_creation')
        {
            $all_users=self::getAdmins($email_type);
            $subject='New ' . $name . ' user';
            $recipient_ids=array_keys($all_users);
        }
        elseif($email_type=='new_ticket')
        {
            $all_users=self::getAdmins($email_type);
            $subject='New ' . $name . ' ticket';
            $recipient_ids=array_keys($all_users);

        }
        if (!isset(Yii::$app->params['disableEmail']))
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
                         <br /> <br /> Sincerely, <br /> the $name team.")
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

        $mailer = Yii::$app->mailer->setTransport([
        'class' => 'Swift_SmtpTransport',
        'host' => $smtp->host,
        'username' => $smtp->username,
        'password' => $decrypted_password,
        'port' => $smtp->port,
        'encryption' => $smtp->encryption,

        ]);

        
       
        if($email_type=='expires_1')
        {
            $admins=self::getAdmins($email_type);
            $subject='Expiration of project '. $project_name;
            $recipient_ids=array_keys($admins);
        }
        elseif($email_type=='expires_5')
        {
            $admins=self::getAdmins($email_type);
            $subject='Expiration of project '. $project_name;
            $recipient_ids=array_keys($admins);
        }

        if (!isset(Yii::$app->params['disableEmail']))
        {   
            foreach ($admins as $user)
            {
                try
                {    
                    Yii::$app->mailer->compose()
                         ->setFrom("$smtp->username")
                         ->setTo($user['email'])
                         ->setSubject($subject)
                         ->setTextBody('Plain text content')
                         ->setHtmlBody("Dear ". explode('@',$user['username'])[0]. ",  <br /> <br /> $message 
                         <br /> <br /> Sincerely, <br /> the $name team.")
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



    public static function getAdmins($email_event)
    {
        $query=new Query;
        $result=$query->select(['u.id', 'u.email','u.username'])
            ->from('auth_assignment as p')
            ->innerJoin('user as u', 'u.id=p.user_id')
            ->innerJoin('email_events_admin as e', 'e.user_id=u.id')
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







}
