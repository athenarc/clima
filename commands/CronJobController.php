<?php
namespace app\commands;


use Yii;
use yii\console\ExitCode;
use yii\console\Controller;
use fedemotta\cronjob\models\CronJob;
use app\models\EmailEventsAdmin;
use app\models\EmailEventsModerator;
use app\models\EmailEventsUser;
use app\models\Project;
use app\models\Notification;
use app\models\User;
use app\models\Email;
use app\models\JupyterServer;
use webvimark\modules\UserManagement\models\User as Userw;



class CronJobController extends Controller {

    public function actionIndex() {
        echo "cron service runnning".  "\n";
        return ExitCode::OK;
    }

   public function actionInit($from, $to){


        $dates  = CronJob::getDateRange($from, $to);
        $command = CronJob::run($this->id, $this->action->id, 0, CronJob::countDateRange($dates));


        $active_projects=Project::getAllActiveProjects();
        if ($command === false)
        {
            return Controller::EXIT_CODE_ERROR;
        }
        else
        {
            foreach ($dates as $date) 
            {
                //this is the function to execute for each day
                foreach ($active_projects as $project) 
                {
                    
                    $now = strtotime(date("Y-m-d"));
                    $end_project = strtotime($project['end_date']);
                    $remaining_secs=$end_project-$now;
                    $user_id=$project['submitted_by'];

                    $notification_remaining_days=$remaining_secs/86400;
                    
                    $message1="We would like to notify you that project '$project[name]' will expire in 30 days.";
                    $message2="We would like to notify you that project '$project[name]' will expire in 15 days.";
                    $message3="We would like to notify you that project '$project[name]' will expire in 1 day.";
                    $message4="We would like to notify you that project '$project[name]' will expire in 5 days.";
                    
                    
                    
                    $notifications1=Notification::find()->where(['recipient_id'=>$user_id])
                    ->andWhere(['message'=>$message1])->all();
                    $notifications2=Notification::find()->where(['recipient_id'=>$user_id])
                    ->andWhere(['message'=>$message2])->all();
                    $notifications3=Notification::find()->where(['recipient_id'=>$user_id])
                    ->andWhere(['message'=>$message3])->all();
                    $notifications4=Notification::find()->where(['recipient_id'=>$user_id])
                    ->andWhere(['message'=>$message4])->all();

                    if(empty($notifications1) && ($notification_remaining_days==30))
                    {
                        Notification::notifyDate($user_id,$message1,1,null, (string) $date);
                    }
                    

                    if(empty($notifications2) && ($notification_remaining_days==15))
                    {
                       Notification::notifyDate($user_id,$message2,1,null, (string) $date);
                    }
                    
                    if(empty($notifications3) && ($notification_remaining_days==1))
                    {
                       Notification::notifyDate($user_id,$message3,1,null, (string) $date);
                    }

                    if(empty($notifications4) && ($notification_remaining_days==5))
                    {
                       Notification::notifyDate($user_id,$message4,1,null, (string) $date);
                    }

                    $email_30=Email::find()->where(['project_id'=>$project['project_id']])
                    ->andWhere(['type'=>'expires_30'])
                    ->one();
                    if(empty($email_30) && ($notification_remaining_days==30))
                    {
                         
                        EmailEventsUser::NotifyByEmailDate('expires_30', $project['project_id'],$message1, (string) $date);
                        EmailEventsModerator::NotifyByEmailDate('expires_30', $project['project_id'],$message1, (string) $date);
                        
                    }
                    
                    $email_15=Email::find()->where(['project_id'=>$project['project_id']])
                    ->andWhere(['type'=>'expires_15'])
                    ->one();
                    if(empty($email_15) && ($notification_remaining_days==15))
                    {
                        EmailEventsUser::notifyByEmailDate('expires_15', $project['project_id'],$message2, (string) $date);
                        EmailEventsModerator::notifyByEmailDate('expires_15', $project['project_id'],$message2, (string) $date);
                    }


                    $email_1=Email::find()->where(['project_id'=>$project['project_id']])
                    ->andWhere(['type'=>'expires_1'])
                    ->one();
                    if(empty($email_1) && ($notification_remaining_days==1))
                    {
                        EmailEventsUser::notifyByEmailDate('expires_1', $project['project_id'],$message3, (string) $date);
                        EmailEventsAdmin::notifyByEmailDate('expires_1', $project['project_id'],$message3, (string) $date);
                        EmailEventsModerator::notifyByEmailDate('expires_1', $project['project_id'],$message3, (string) $date);
                    }

                    $email_5=Email::find()->where(['project_id'=>$project['project_id']])
                    ->andWhere(['type'=>'expires_5'])
                    ->one();
                    if(empty($email_5) && ($notification_remaining_days==5))
                    {
                        EmailEventsUser::notifyByEmailDate('expires_5', $project['project_id'],$message4, (string) $date);
                        EmailEventsAdmin::notifyByEmailDate('expires_5', $project['project_id'],$message4, (string) $date);
                        EmailEventsModerator::notifyByEmailDate('expires_5', $project['project_id'],$message4, (string) $date);
                    }
                }
                       
            }
            $command->finish();
            return Controller::EXIT_CODE_NORMAL;
        }
    }

     public function actionYesterday(){
        return $this->actionInit(date("Y-m-d", strtotime("-1 days")), date("Y-m-d", strtotime("-1 days")));
    }

    public function actionDeleteServers($from, $to){

        $dates  = CronJob::getDateRange($from, $to);
        $command = CronJob::run($this->id, $this->action->id, 0, CronJob::countDateRange($dates));
        if ($command === false)
        {
            return Controller::EXIT_CODE_ERROR;
        }
        else
        {
            $expired_owner=Project::getExpiredProjects();
            $expired_projects = $expired_owner;
            foreach ($expired_projects as $expired_project){
                if ($expired_project['project_type']==4){
                    $all_servers=JupyterServer::find()->where(['active'=>true,'project'=>$expired_project['name']])->all();
                    if (!empty($all_servers)){
                        foreach ($all_servers as $server){
                            $server->Stopserver();
                        }
                    }

                }
            }
        
            $command->finish();
            return Controller::EXIT_CODE_NORMAL;
        }
    }

    public function actionToday(){
        return $this->actionDeleteServers(date("Y-m-d"), date("Y-m-d"));
    }
    

}