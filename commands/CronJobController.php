<?php
namespace app\commands;


use app\models\Token;
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



class CronJobController extends Controller
{

    public function actionIndex()
    {
        echo "cron service runnning" . "\n";
        return ExitCode::OK;
    }

    public function actionInit($from, $to)
    {


        $dates = CronJob::getDateRange($from, $to);
        $command = CronJob::run($this->id, $this->action->id, 0, CronJob::countDateRange($dates));


        $active_projects = Project::getAllActiveProjects();
        if ($command === false) {
            return Controller::EXIT_CODE_ERROR;
        } else {
            foreach ($dates as $date) {
                //this is the function to execute for each day
                foreach ($active_projects as $project) {

                    $now = strtotime(date("Y-m-d"));
                    $end_project = strtotime($project['end_date']);
                    $remaining_secs = $end_project - $now;
                    $user_id = $project['submitted_by'];

                    $notification_remaining_days = $remaining_secs / 86400;

                    $message1 = "We would like to notify you that project '$project[name]' will expire in 30 days.";
                    $message2 = "We would like to notify you that project '$project[name]' will expire in 15 days.";
                    $message3 = "We would like to notify you that project '$project[name]' will expire in 1 day.";
                    $message4 = "We would like to notify you that project '$project[name]' will expire in 5 days.";


                    $notifications1 = Notification::find()->where(['recipient_id' => $user_id])
                        ->andWhere(['message' => $message1])->all();
                    $notifications2 = Notification::find()->where(['recipient_id' => $user_id])
                        ->andWhere(['message' => $message2])->all();
                    $notifications3 = Notification::find()->where(['recipient_id' => $user_id])
                        ->andWhere(['message' => $message3])->all();
                    $notifications4 = Notification::find()->where(['recipient_id' => $user_id])
                        ->andWhere(['message' => $message4])->all();

                    if (empty($notifications1) && ($notification_remaining_days == 30)) {
                        Notification::notifyDate($user_id, $message1, 1, null, (string)$date);
                    }


                    if (empty($notifications2) && ($notification_remaining_days == 15)) {
                        Notification::notifyDate($user_id, $message2, 1, null, (string)$date);
                    }

                    if (empty($notifications3) && ($notification_remaining_days == 1)) {
                        Notification::notifyDate($user_id, $message3, 1, null, (string)$date);
                    }

                    if (empty($notifications4) && ($notification_remaining_days == 5)) {
                        Notification::notifyDate($user_id, $message4, 1, null, (string)$date);
                    }

                    $email_30 = Email::find()->where(['project_id' => $project['project_id']])
                        ->andWhere(['type' => 'expires_30'])
                        ->one();
                    if (empty($email_30) && ($notification_remaining_days == 30)) {

                        EmailEventsUser::NotifyByEmailDate('expires_30', $project['project_id'], $message1, (string)$date);
                        EmailEventsModerator::NotifyByEmailDate('expires_30', $project['project_id'], $message1, (string)$date);

                    }

                    $email_15 = Email::find()->where(['project_id' => $project['project_id']])
                        ->andWhere(['type' => 'expires_15'])
                        ->one();
                    if (empty($email_15) && ($notification_remaining_days == 15)) {
                        EmailEventsUser::notifyByEmailDate('expires_15', $project['project_id'], $message2, (string)$date);
                        EmailEventsModerator::notifyByEmailDate('expires_15', $project['project_id'], $message2, (string)$date);
                    }


                    $email_1 = Email::find()->where(['project_id' => $project['project_id']])
                        ->andWhere(['type' => 'expires_1'])
                        ->one();
                    if (empty($email_1) && ($notification_remaining_days == 1)) {
                        EmailEventsUser::notifyByEmailDate('expires_1', $project['project_id'], $message3, (string)$date);
                        EmailEventsAdmin::notifyByEmailDate('expires_1', $project['project_id'], $message3, (string)$date);
                        EmailEventsModerator::notifyByEmailDate('expires_1', $project['project_id'], $message3, (string)$date);
                    }

                    $email_5 = Email::find()->where(['project_id' => $project['project_id']])
                        ->andWhere(['type' => 'expires_5'])
                        ->one();
                    if (empty($email_5) && ($notification_remaining_days == 5)) {
                        EmailEventsUser::notifyByEmailDate('expires_5', $project['project_id'], $message4, (string)$date);
                        EmailEventsAdmin::notifyByEmailDate('expires_5', $project['project_id'], $message4, (string)$date);
                        EmailEventsModerator::notifyByEmailDate('expires_5', $project['project_id'], $message4, (string)$date);
                    }
                }

            }
            $command->finish();
            return Controller::EXIT_CODE_NORMAL;
        }
    }

    public function actionYesterday()
    {

        $this->actionInit(date("Y-m-d", strtotime("-1 days")), date("Y-m-d", strtotime("-1 days")));


        $this->actionTokenNotify(date("Y-m-d", strtotime("-1 days")), date("Y-m-d", strtotime("-1 days")));
        $this->actionInactiveUsersNotify(date("Y-m-d", strtotime("-1 days")), date("Y-m-d", strtotime("-1 days")));
        $this->actionPostExpiryResourceNotify(date("Y-m-d", strtotime("-1 days")), date("Y-m-d", strtotime("-1 days")));

        return Controller::EXIT_CODE_NORMAL;
    }

    public function actionDeleteServers($from, $to)
    {

        $dates = CronJob::getDateRange($from, $to);
        $command = CronJob::run($this->id, $this->action->id, 0, CronJob::countDateRange($dates));
        if ($command === false) {
            return Controller::EXIT_CODE_ERROR;
        } else {
            $expired_owner = Project::getExpiredProjects();
            $expired_projects = $expired_owner;
            foreach ($expired_projects as $expired_project) {
                if ($expired_project['project_type'] == 4) {
                    $all_servers = JupyterServer::find()->where(['active' => true, 'project' => $expired_project['name']])->all();
                    if (!empty($all_servers)) {
                        foreach ($all_servers as $server) {
                            $server->Stopserver();
                        }
                    }

                }
            }

            $command->finish();
            return Controller::EXIT_CODE_NORMAL;
        }
    }

    public function actionToday()
    {
        return $this->actionDeleteServers(date("Y-m-d"), date("Y-m-d"));
    }

    public function actionTokenNotify($from, $to)
    {

        $dates = CronJob::getDateRange($from, $to);
        $command = CronJob::run($this->id, $this->action->id, 0, CronJob::countDateRange($dates));

        $schema_api_url = Yii::$app->params['schema_api_url'];
        $schema_api_token = Yii::$app->params['schema_api_token'];
        $headers = [
            'Authorization: ' . $schema_api_token,
            'Content-Type: application/json'
        ];

        $active_projects = Project::getAllActiveProjects();

        if ($command === false) {
            return Controller::EXIT_CODE_ERROR;
        }

        foreach ($dates as $date) {
            foreach ($active_projects as $project) {
                $project_name = $project['name'];
                $project_id = $project['project_id'];
                $user_id = $project['submitted_by'];

                $username = explode('@', Userw::findOne($user_id)->username)[0];
                $url = "{$schema_api_url}/api_auth/contexts/{$project_name}/users/{$username}/tokens?status=active";

                $tokens = Token::GetTokens($url, $headers);

                if (!isset($tokens[1]) || !is_array($tokens[1])) {
                    continue;
                }

                foreach ($tokens[1] as $raw_token) {
                    if (empty($raw_token)) continue;

                    $raw_token = strpos($raw_token, '{') === 0 ? $raw_token : '{' . $raw_token . '}';
                    $token = json_decode($raw_token, true);

                    if (!$token || !isset($token['expiry']) || !isset($token['title'])) {
                        continue;
                    }

                    $expiry_date = strtotime($token['expiry']);
                    $now = strtotime($date);
                    $remaining_secs = $expiry_date - $now;
                    $notification_remaining_days = floor($remaining_secs / 86400);
                    $title = $token['title'];

                    $msg30 = "Your API key with label:{$title} for project '{$project_name}' will expire in 30 days.";
                    $msg15 = "Your API key with label:{$title} for project '{$project_name}' will expire in 15 days.";
                    $msg1 = "Your API key with label:{$title} for project '{$project_name}' will expire in 1 day.";

                    if ($notification_remaining_days == 30) {
                        if (!Notification::find()->where(['recipient_id' => $user_id, 'message' => $msg30])->exists()) {
                            Notification::notifyDate($user_id, $msg30, 1, null, (string)$date);
                        }
                        if (!Email::find()
                            ->where(['project_id' => $project_id, 'type' => 'apikey_expires_30'])
                            ->andWhere(['like', 'message', $title])
                            ->exists()) {
                            EmailEventsUser::notifyByEmailDate('apikey_expires_30', $project_id, $msg30, (string)$date);

                        }
                    }
                    if ($notification_remaining_days == 15) {
                        if (!Notification::find()->where(['recipient_id' => $user_id, 'message' => $msg15])->exists()) {
                            Notification::notifyDate($user_id, $msg15, 1, null, (string)$date);
                        }
                        if (!Email::find()
                            ->where(['project_id' => $project_id, 'type' => 'apikey_expires_15'])
                            ->andWhere(['like', 'message', $title])
                            ->exists()) {
                            EmailEventsUser::notifyByEmailDate('apikey_expires_15', $project_id, $msg15, (string)$date);

                        }
                    }
                    if ($notification_remaining_days == 1) {
                        if (!Notification::find()->where(['recipient_id' => $user_id, 'message' => $msg1])->exists()) {
                            Notification::notifyDate($user_id, $msg1, 1, null, (string)$date);
                        }
                        if (!Email::find()
                            ->where(['project_id' => $project_id, 'type' => 'apikey_expires_1'])
                            ->andWhere(['like', 'message', $title])
                            ->exists()) {
                            EmailEventsUser::notifyByEmailDate('apikey_expires_1', $project_id, $msg1, (string)$date);

                        }
                    }


                }
            }
        }

        $command->finish();
        return Controller::EXIT_CODE_NORMAL;
    }

    public function actionInactiveUsersNotify($from, $to)
    {
        $dates = CronJob::getDateRange($from, $to);
        $command = CronJob::run($this->id, $this->action->id, 0, CronJob::countDateRange($dates));

        if ($command === false) {
            return Controller::EXIT_CODE_ERROR;
        }

        foreach ($dates as $date) {

            // All users inactive 181+ days
            $inactiveUsers = (new \yii\db\Query())
                ->select(['id', 'username', 'last_login'])
                ->from('auth_user')
                ->where(['<', 'last_login', new \yii\db\Expression("NOW() - INTERVAL '181 days'")])
                ->all(Yii::$app->db2);

            echo "Found " . count($inactiveUsers) . " users inactive over 181 days\n";

            foreach ($inactiveUsers as $authUser) {
                $userId = $authUser['id'];
                $username = $authUser['username'];
                $lastLogin = strtotime($authUser['last_login']);
                $now = strtotime($date);
                $inactiveDays = floor(($now - $lastLogin) / 86400);

                echo "$username last login: " . date('Y-m-d', $lastLogin) . " ($inactiveDays days ago)\n";

                $user = User::find()->where(['username' => $username])->one();
                if (!$user || !$user->email) {
                    echo "No matching local user or missing email for: $username\n";
                    continue;
                }

                $milestones = [181, 195, 210];
                $emailTypes = [];

                // Add matching milestone email type
                if (in_array($inactiveDays, $milestones)) {
                    $emailTypes[] = "inactive_user_notify_{$inactiveDays}";
                }

                // Add "over 210" if inactivity has exceeded
                if ($inactiveDays > 210) {
                    $emailTypes[] = "inactive_user_notify_over_210";
                }

                foreach ($emailTypes as $emailType) {
                    $emailPrefs = EmailEventsUser::find()->where(['user_id' => $user->id])->one();

                    if (!$emailPrefs || !($emailPrefs->{$emailType} ?? false)) {
                        echo "Email type '$emailType' disabled for user ID: {$user->id}\n";
                        continue;
                    }

                    $alreadySent = Email::find()
                        ->where([
                            'type' => $emailType,
                            'project_id' => null,
                            'related_user_id' => $user->id,
                        ])
                        ->exists();

                    if ($alreadySent) {
                        echo "Email already sent to {$user->email} for $emailType\n";
                        continue;
                    }

                    $message = "Dear {$user->username},<br><br>"
                        . "Our records show that you haven’t logged in since $lastLogin. Please log in to activate your account.";

                    echo "Sending '$emailType' to {$user->email} (inactive for $inactiveDays days)\n";

                    EmailEventsUser::NotifyInactiveUserByEmailDate(
                        $emailType,
                        $message,
                        (string)$date,
                        $user->id,
                        $user->email,
                        $user->username
                    );

                    $alreadyNotified = Notification::find()
                        ->where(['recipient_id' => $user->id, 'message' => $message])
                        ->exists();

                    if (!$alreadyNotified) {
                        Notification::notifyDate($user->id, $message, 1, null, (string)$date);
                    }
                }
            }
        }
        $command->finish();
        return Controller::EXIT_CODE_NORMAL;
    }


    public function actionPostExpiryResourceNotify($from, $to)
    {
        $dates = CronJob::getDateRange($from, $to);
        $command = CronJob::run($this->id, $this->action->id, 0, CronJob::countDateRange($dates));

        if ($command === false) {
            return Controller::EXIT_CODE_ERROR;
        }

        $resources = Project::getActiveResources();
        $expired_projects = Project::getAllExpiredProjects();
        $expired_projects = array_filter($expired_projects, function($project) use ($resources) {
            $project_id = $project['project_id'];
            $project_type = $project['project_type'];
            return isset($resources[$project_type][$project_id]);
        });
        echo "Found " . count($expired_projects) . " expired projects with active resources.\n";

        foreach ($dates as $date) {

            foreach ($expired_projects as $project) {
                $project_id = $project['project_id'];
                $project_type = $project['project_type'];
                $project_name = $project['name'];
                $end_date = strtotime($project['end_date']);
                $now = strtotime($date);
                $days_since_expiry = floor(($now - $end_date) / 86400);

                if ($days_since_expiry <= 0) {
                    continue;
                }

                $has_active_resources = isset($resources[$project_type][$project_id]);
                if (!$has_active_resources) {
                    continue;
                }

                // Determine email type
                if (in_array($days_since_expiry, [1, 15, 30])) {
                    $email_type = "expired_resources_notify_{$days_since_expiry}";
                } elseif ($days_since_expiry > 30) {
                    $email_type = "expired_resources_notify_over_30";
                } else {
                    continue;
                }

                // Prevent duplicate email
                $alreadySent = Email::find()->where([
                    'type' => $email_type,
                    'project_id' => $project_id,
                ])->exists();

                if ($alreadySent) {
                    echo "Email already sent for project ID $project_id, type $email_type\n";
                    continue;
                }

                echo "Sending email for project ID $project_id, expired $days_since_expiry days ago\n";
                // Email message
                if ($days_since_expiry === 1) {
                    $message = "We would like to inform you that the project '$project_name' expired yesterday and is scheduled for deletion soon.Please take any necessary action to back up your data or renew the project if needed.";
                } elseif ($days_since_expiry === 15) {
                    $message = "We would like to inform you that the project '$project_name' expired 15 days ago and is scheduled for deletion soon.Please take any necessary action to back up your data or renew the project if needed.";
                } elseif ($days_since_expiry === 30) {
                    $message = "We would like to inform you that the project '$project_name' expired 30 days ago and is scheduled for deletion soon.Please take any necessary action to back up your data or renew the project if needed.";
                } elseif ($days_since_expiry > 30) {
                    $message = "We would like to inform you that the project '$project_name' expired a long time ago and is scheduled for deletion soon.Please take any necessary action to back up your data or renew the project if needed.";
                } else {
                    continue;
                }


                EmailEventsUser::NotifyByEmailDate($email_type, $project_id, $message, (string)$date);
            }
        }

        $command->finish();
        return Controller::EXIT_CODE_NORMAL;
    }
}