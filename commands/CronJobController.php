<?php
namespace app\commands;


use Yii;
use yii\console\ExitCode;
use yii\console\Controller;
use fedemotta\cronjob\models\CronJob;
use app\models\EmailEvents;
use app\models\Project;
use app\models\Notification;
use app\models\User;
use app\models\Email;
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
                    
                    $message1="Project '$project[name]' will end in 30 days.";
                    $message2="Project '$project[name]' will end in 15 days.";
                    
                    
                    $notifications1=Notification::find()->where(['recipient_id'=>$user_id])
                    ->andWhere(['message'=>$message1])->all();
                    $notifications2=Notification::find()->where(['recipient_id'=>$user_id])
                    ->andWhere(['message'=>$message2])->all();


                    if(empty($notifications1) && ($notification_remaining_days==30))
                    {
                        Notification::notifyDate($user_id,$message1,1,null, (string) $date);
                    }
                    

                    if(empty($notifications2) && ($notification_remaining_days==15))
                    {
                       Notification::notifyDate($user_id,$message2,1,null, (string) $date);
                    }
                    

                    $email_30=Email::find()->where(['project_id'=>$project['project_id']])
                    ->andWhere(['type'=>'expires_30'])
                    ->one();
                    if(empty($email_30) && ($notification_remaining_days==30))
                    {
                         
                        EmailEvents::NotifyByEmailDate('expires_30', $project['project_id'],$message1, (string) $date);
                        
                    }
                    
                    $email_15=Email::find()->where(['project_id'=>$project['project_id']])
                    ->andWhere(['type'=>'expires_15'])
                    ->one();
                    if(empty($email_15) && ($notification_remaining_days==15))
                    {
                        EmailEvents::notifyByEmailDate('expires_15', $project['project_id'],$message2, (string) $date);
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
    

}