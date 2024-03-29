<?php

namespace app\controllers;

use Yii;
use app\models\TicketBody;
use app\models\TicketFile;
use app\models\TicketHead;
use app\models\TicketUploadForm;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\UploadedFile;
use app\models\Notification;
use app\models\TicketConfig;
use app\models\User;
use app\models\Smtp;
use app\models\EmailEventsAdmin;
use webvimark\modules\UserManagement\models\User as Userw;

/**
 * Default controller for the `ticket` module
 */
class TicketUserController extends Controller
{
    public $module;
    public $freeAccess = false;

    // public function beforeAction($action)
    // {
    //     TicketConfig::=new TicketConfig;
    //     return parent::beforeAction($action);
    // }

    public function behaviors()
    {
        return [
            'access' => [
                'class'      => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules'      => [
                    [
                        'actions' => ['index', 'view', 'open'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Делается выборка тела тикета по id и отображаем данные
     * Если пришел пустой результат показываем список тикетов
     * Создаем экземпляр новой модели тикета
     * К нам пришел пост делаем загрузку в модель и проходим валидацию, если все хорошо делаем выборку шапки, меняем ей статус и сохраняем
     * Записываем id тикета новому ответу чтоб не потерялся и сохроняем новый ответ
     *
     * @param $id
     * @return string
     */
    public function actionView($id)
    {
        // TicketConfig::= new TicketConfig;

        $ticket = TicketHead::findOne([
            'id'      => $id,
            'user_id' => \Yii::$app->user->id,
        ]);
        if ($ticket && $ticket->status == TicketHead::ANSWER) {
            $ticket->status = TicketHead::VIEWED;
            $ticket->save();
        }

        $thisTicket = TicketBody::find()->where(['id_head' => $id])->joinWith('file')->orderBy('date DESC')->all();

        if (!$ticket || !$thisTicket) {
            return $this->actionIndex();
        }

        $newTicket = new TicketBody();
        $ticketFile = new TicketFile();

        if (\Yii::$app->request->post() && $newTicket->load(\Yii::$app->request->post()) && $newTicket->validate()) {

            $ticket->status = TicketHead::WAIT;

            $uploadForm = new TicketUploadForm();
            $uploadForm->imageFiles = UploadedFile::getInstances($ticketFile, 'fileName');

            if ($ticket->save() && $uploadForm->upload()) {
                $newTicket->id_head = $id;
                $newTicket->save();

                TicketFile::saveImage($newTicket, $uploadForm);
            } else {
                \Yii::$app->session->setFlash('error', $uploadForm->firstErrors['imageFiles']);

                return $this->render('view', [
                    'thisTicket' => $thisTicket,
                    'newTicket'  => $newTicket,
                    'fileTicket' => $ticketFile,
                ]);
            }

            if (\Yii::$app->request->isAjax) {
                return 'OK';
            }

            $username=explode('@',$newTicket->name_user)[0];
            $message="User <strong>$username</strong> posted an answer for ticket <strong>$ticket->topic</strong>.";
            $url=Url::to(['/ticket-admin/answer','id'=>$id, 'mode'=>0]);
            
            //added to fix the following error
            foreach (User::getAdminIds() as $admin)
            {
                Notification::notify($admin, $message, '0' ,$url);
            }
            //EmailEventsAdmin::NotifyByEmail('new_ticket',-1, $message);

            //error: unknown adminId
            // foreach (TicketConfig::adminId as $admin)
            // {
            //     Notification::notify($admin, $message, '0' ,$url);
            // }
        

            $this->redirect(Url::to(['/ticket-user/index']));
        }

        return $this->render('view', [
            'thisTicket' => $thisTicket,
            'newTicket'  => $newTicket,
            'fileTicket' => $ticketFile,
        ]);
    }

    /**
     * Renders the index view for the module
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = (new TicketHead())->dataProviderUser();
        Url::remember();

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    /**
     * Создаем два экземпляра
     * 1. Шапка тикета
     * 2. Тело тикета
     * Делаем рендеринг страницы
     * Если post, проводим загрузку данных в модели, делаем валидацию
     * Сохраняем сначало шапку, узнаем его id, этот id присваеваем телу сообщения чтоб не потерялось и сохраняем
     *
     * @return string|\yii\web\Response
     */
    public function actionOpen($link='', $upgrade=0)
    {
        $ticketHead = new TicketHead();
        $ticketBody = new TicketBody();
        $ticketFile = new TicketFile();

        if (\Yii::$app->request->post()) {
            $ticketHead->load(\Yii::$app->request->post());
            $ticketBody->load(\Yii::$app->request->post());

            if ($ticketBody->validate() && $ticketHead->validate()) {
                $ticketHead->page=urldecode($link);
                if ($ticketHead->save()) {
                    $ticketBody->id_head = $ticketHead->getPrimaryKey();
                    $ticketBody->save();

                    $uploadForm = new TicketUploadForm();
                    $uploadForm->imageFiles = UploadedFile::getInstances($ticketFile, 'fileName');
                    if ($uploadForm->upload()) {
                        TicketFile::saveImage($ticketBody, $uploadForm);
                    } 
                    // else {
                    //     Yii::$app->session->setFlash('error', "error");
                    //     return $this->redirect(['project/index']);
                    // }

                    if (\Yii::$app->request->isAjax) {
                        return 'OK';
                    }

                    $username=explode('@',$ticketBody->name_user)[0];
                    $message="User <strong>$username</strong> created a new <strong>$ticketHead->department</strong> ticket with the following topic:  <br /> <strong>$ticketHead->topic</strong>.";
                    $url=Url::to(['/ticket-admin/answer','id'=>$ticketHead->id, 'mode'=>0]);
                    foreach (User::getAdminIds() as $admin)
                    {
                        Notification::notify($admin, $message, '0' ,$url);
                    }
                    
                    EmailEventsAdmin::NotifyByEmail('new_ticket',-1, $message);


                    return $this->redirect(Url::previous());
                }
            }
        }

        if ($upgrade==0){
            return $this->render('open', [
                'ticketHead' => $ticketHead,
                'ticketBody' => $ticketBody,
                'qq'         => TicketConfig::qq,
                'fileTicket' => $ticketFile,
                'upgrade' => 0,
            ]);
        } else {
            return $this->render('open', [
                'ticketHead' => $ticketHead,
                'ticketBody' => $ticketBody,
                'qq'         => TicketConfig::up,
                'fileTicket' => $ticketFile,
                'upgrade' => 1,
            ]);
        }
    }

}
