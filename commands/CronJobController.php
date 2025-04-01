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
use app\models\AuthUser;
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

        $active_projects = Project::getAllActiveProjects();
        if ($command === false) {
            return Controller::EXIT_CODE_ERROR;
        } else {
            foreach ($dates as $date) {
                foreach ($active_projects as $project) {
                    $now = strtotime(date("Y-m-d"));
                    $end_project = strtotime($project['end_date']);
                    $remaining_secs = $end_project - $now;
                    $expired_secs = $now - $end_project;
                    $user_id = $project['submitted_by'];

                    $notification_remaining_days = $remaining_secs / 86400;
                    $notification_expired_days = $expired_secs / 86400;

                    $messages = [
                        30 => "We would like to notify you that project '$project[name]' will expire in 30 days.",
                        15 => "We would like to notify you that project '$project[name]' will expire in 15 days.",
                        5  => "We would like to notify you that project '$project[name]' will expire in 5 days.",
                        1  => "We would like to notify you that project '$project[name]' will expire in 1 day."
                    ];

                    foreach ($messages as $days => $message) {
                        $existing_notification = Notification::find()
                            ->where(['recipient_id' => $user_id])
                            ->andWhere(['message' => $message])
                            ->all();

                        if (empty($existing_notification) && ($notification_remaining_days == $days)) {
                            Notification::notifyDate($user_id, $message, 1, null, (string) $date);
                        }
                    }

                    $email_types = [
                        30 => 'expires_30',
                        15 => 'expires_15',
                        5  => 'expires_5',
                        1  => 'expires_1'
                    ];

                    foreach ($email_types as $days => $type) {
                        $existing_email = Email::find()
                            ->where(['project_id' => $project['project_id']])
                            ->andWhere(['type' => $type])
                            ->one();

                        if (empty($existing_email) && ($notification_remaining_days == $days)) {
                            EmailEventsUser::notifyByEmailDate($type, $project['project_id'], $messages[$days], (string) $date);
                            EmailEventsModerator::notifyByEmailDate($type, $project['project_id'], $messages[$days], (string) $date);
                            if ($days == 1) {
                                EmailEventsAdmin::notifyByEmailDate($type, $project['project_id'], $messages[$days], (string) $date);
                            }
                        }
                    }

                    // Post-expiration emails
                    $expired_messages = [
                        3  => "We would like to notify you that project '$project[name]' expired 3 days ago.",
                        5  => "We would like to notify you that project '$project[name]' expired 5 days ago.",
                        10 => "We would like to notify you that project '$project[name]' expired 10 days ago."
                    ];

                    foreach ($expired_messages as $days => $message) {
                        $type = "expired_{$days}";
                        $existing_email = Email::find()
                            ->where(['project_id' => $project['project_id']])
                            ->andWhere(['type' => $type])
                            ->one();

                        if (empty($existing_email) && ($notification_expired_days == $days)) {
                            EmailEventsUser::notifyByEmailDate($type, $project['project_id'], $message, (string) $date);
                            EmailEventsAdmin::notifyByEmailDate($type, $project['project_id'], $message, (string) $date);
                            EmailEventsModerator::notifyByEmailDate($type, $project['project_id'], $message, (string) $date);
                        }
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

    public function actionCheckInactiveUsers() {
        $sixMonthsAgo = date('Y-m-d H:i:s', strtotime('-6 months'));
        $fiveDaysBeforeFlag = date('Y-m-d H:i:s', strtotime('-30 days', strtotime($sixMonthsAgo)));
        $twoDaysBeforeFlag = date('Y-m-d H:i:s', strtotime('-15 days', strtotime($sixMonthsAgo)));
        $oneDayBeforeFlag = date('Y-m-d H:i:s', strtotime('-1 day', strtotime($sixMonthsAgo)));

        // Get users from auth_user who have not logged in for 6 months
        $inactiveUsers = AuthUser::find()
            ->where(['<', 'last_login', $sixMonthsAgo])
            ->andWhere(['status' => Userw::STATUS_ACTIVE])
            ->all();

        foreach ($inactiveUsers as $authUser) {
            $user = User::find()->where(['username' => $authUser->username])->one();
            if (!$user) {
                continue;
            }

            $currentDate = date('Y-m-d');

            // Send notifications at specific intervals before marking inactivity
            $emailStages = [
                'inactive_5_days' => [$fiveDaysBeforeFlag, "You have been inactive for a long time. Your account might be affected in 5 days."],
                'inactive_2_days' => [$twoDaysBeforeFlag, "You have been inactive for a long time. Your account might be affected in 2 days."],
                'inactive_1_day' => [$oneDayBeforeFlag, "You have been inactive for a long time. Your account might be affected in 1 day."],
            ];

            foreach ($emailStages as $type => [$sendDate, $notificationMessage]) {
                if ($currentDate >= date('Y-m-d', strtotime($sendDate))) {
                    $existingEmail = Email::find()
                        ->where(['user_id' => $user->id])
                        ->andWhere(['type' => $type])
                        ->one();

                    if (empty($existingEmail)) {
                        EmailEventsUser::notifyByEmailDate($type, $user->id, $notificationMessage, $currentDate);
                        echo "Email sent to inactive user: {$user->email} - $notificationMessage\n";
                    }
                }
            }
        }

        return ExitCode::OK;
    }
}