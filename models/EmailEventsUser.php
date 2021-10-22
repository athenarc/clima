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

        if (!isset(Yii::$app->params['disableEmail']))
        {   
            foreach ($project_users as $user)
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


    public static function getProjectUsers($project_id, $email_event)
    {
        $project=Project::find()->where(['id'=>$project_id])->one();
        $project_request_id=$project->latest_project_request_id;
        $project_request=ProjectRequest::find()->where(['id'=>$project_request_id])->one();

        $query=new Query;
        $users=$query->select(['u.id', 'u.email','u.username'])
            ->from('user as u')
            ->innerJoin('email_events as e', 'e.user_id=u.id')
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













}
