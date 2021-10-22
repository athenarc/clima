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
 * This is the model class for table "email_events_moderator".
 *
 * @property int $id
 * @property int|null $user_id
 * @property bool|null $new_project
 * @property bool|null $expires_30
 * @property bool|null $expires_15
 * @property bool|null $expires_1
 * @property bool|null $expires_5
 * @property bool|null $project_decision
 * @property bool|null $edit_project
 */
class EmailEventsModerator extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'email_events_moderator';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'default', 'value' => null],
            [['user_id'], 'integer'],
            [['new_project', 'expires_30', 'expires_15', 'expires_1', 'expires_5', 'project_decision', 'edit_project'], 'boolean'],
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
            'new_project' => 'New Project',
            'expires_30' => 'Expires 30',
            'expires_15' => 'Expires 15',
            'expires_1' => 'Expires 1',
            'expires_5' => 'Expires 5',
            'project_decision' => 'Project Decision',
            'edit_project' => 'Edit Project',
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
            
            $all_users=self::getModerators($email_type);
            $subject='Decision on project '. $project_name;
            $recipient_ids=array_keys($all_users);

        }
        elseif($email_type=='new_project')
        {
            $all_users=self::getModerators($email_type);
            $subject='New ' . $name . ' project';
            $recipient_ids=array_keys($all_users);

        }
        elseif($email_type=='edit_project')
        {
            $all_users=self::getModerators($email_type);
            $subject='Project ' . $project_name . ' modification';
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
            $moderators=self::getModerators($email_type);
            $subject='Expiration of project '. $project_name;
            $recipient_ids=array_keys($moderators);
            

        }
        elseif($email_type=='expires_15')
        {
            $moderators=self::getModerators($email_type);
            $subject='Expiration of project '. $project_name;
            $recipient_ids=array_keys($moderators);
    
        }
        elseif($email_type=='expires_1')
        {
            $moderators=self::getModerators($email_type);
            $subject='Expiration of project '. $project_name;
            $recipient_ids=array_keys($moderators);
        }
        elseif($email_type=='expires_5')
        {
            $moderators=self::getModerators($email_type);
            $subject='Expiration of project '. $project_name;
            $recipient_ids=array_keys($moderators);
        }

        if (!isset(Yii::$app->params['disableEmail']))
        {   
            foreach ($moderators as $user)
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

    
}
