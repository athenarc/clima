<?php

namespace app\controllers;

use app\models\AuthUser;
use app\models\ActiveProjectSearch;
use app\models\ExpiredProjectSearch;
use app\models\ViewProjectSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\base\Swift_TransportException;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\ServiceAutoaccept;
use app\models\OndemandAutoaccept;
use app\models\JupyterAutoaccept;
use app\models\StorageAutoaccept;
use app\models\ServiceLimits;
use app\models\OndemandLimits;
use app\models\JupyterLimits;
use app\models\JupyterServer;
use app\models\StorageLimits;
use app\models\Configuration;
use app\models\Openstack;
use app\models\OpenstackMachines;
use app\models\MachineComputeLimits;
use yii\helpers\Url;
use app\models\ProjectRequest;
use app\models\Project;
use app\models\User;
use app\models\EmailEventsAdmin;
use app\models\Smtp;
use app\models\Page;
use app\models\Analytics;
use app\models\StorageRequest;
use webvimark\modules\UserManagement\models\User as Userw;
use app\models\Schema;
use yii\db\Expression;

class AdministrationController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $freeAccess = false;
    public function behaviors()
    {
        return [
            'ghost-access'=> [
                'class' => 'webvimark\modules\UserManagement\components\GhostAccessControl',
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        // Skip the check for login, logout, and policy acceptance
        $allowedActions = ['login', 'logout', 'policy-acceptance'];

        if (!in_array($action->id, $allowedActions)) {
            if (!Yii::$app->user->isGuest && !Yii::$app->user->identity->policy_accepted) {
                return $this->redirect(['site/policy-acceptance'])->send();
            }
        }
        // Continue with the action
        return true;
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
    public function actionInactive()
    {
        $userData = (new \yii\db\Query())
            ->select(['id', 'username', 'email', 'name', 'surname', 'created_at', 'updated_at'])
            ->from('user')
            ->all();

        $inactiveLogins = (new \yii\db\Query())
            ->select(['username', 'last_login'])
            ->from('auth_user')
            ->where(['<', 'last_login', new \yii\db\Expression("NOW() - INTERVAL '180 days'")])
            ->orderBy(['last_login' => SORT_ASC])
            ->all(Yii::$app->db2);

        $loginMap = [];
        foreach ($inactiveLogins as $login) {
            $loginMap[$login['username']] = $login['last_login'];
        }

        foreach ($userData as &$user) {
            $user['last_login'] = isset($loginMap[$user['username']]) ? $loginMap[$user['username']] : null;
        }
        unset($user);


        $usersWithActiveResources = (new \yii\db\Query())
            ->select('u.id')
            ->distinct()
            ->from('user u')
            ->innerJoin('project_request pr', 'pr.submitted_by = u.id')
            ->innerJoin('project p', 'p.latest_project_request_id = pr.id')
            ->where(['or',
                ['<', 'p.project_end_date', new \yii\db\Expression('NOW()')],
                ['p.status' => 0]
            ])
            ->andWhere(['in', 'p.id', (new \yii\db\Query())
                ->select('project_id')
                ->from('vm')
                ->where(['active' => true])
                ->union((new \yii\db\Query())
                    ->select('project_id')
                    ->from('vm_machines')
                    ->where(['active' => true])
                )
            ])
            ->column();
        $userData = array_filter($userData, function ($user) use ($loginMap) {
            return isset($loginMap[$user['username']]);
        });


        $searchModel = new \app\models\InactiveUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $userData, $usersWithActiveResources);



        return $this->render('inactive', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'usersWithActiveResources' => $usersWithActiveResources,
        ]);
    }




    public function actionViewProjects($username)
    {
        $project_type = Project::TYPES;

        $projects = Project::find()
            ->alias('p')
            ->innerJoin('project_request pr', 'p.latest_project_request_id = pr.id')
            ->innerJoin('"user" u', 'pr.submitted_by = u.id')
            ->where(['u.username' => $username])
            ->select([
                'p.id AS project_id',
                'p.name AS project_name',
                'p.status',
                'p.start_date',
                'p.project_end_date',
                'p.project_type',
                'pr.id AS project_request_id',
                'u.username'
            ])
            ->asArray()
            ->all();

        // Fetch active resources (as you do in all-projects)
        [$active_jupyter, $active_vms, $active_machines, $active_volumes] = Project::getActiveResources();

        // Correct project_type to resource mapping:
        $active_resources = [
            0 => $active_vms,        // On-demand batch computation => VMs
            1 => $active_vms,        // 24/7 Service => VMs
            2 => $active_volumes,    // Storage volumes
            3 => $active_machines,   // Compute machines
            4 => $active_jupyter,    // Notebooks
        ];

        $searchModel = new ViewProjectSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $projects, $active_resources);

        return $this->render('view_projects', [
            'dataProvider' => $dataProvider,
            'username' => $username,
            'project_type' => $project_type,
            'active_resources' => $active_resources,
            'searchModel' => $searchModel,
        ]);
    }







    public function actionConfigure()
    {



        $userTypes=["gold"=>"Gold","silver"=>"Silver", "bronze"=>"Bronze"];
        $currentUser=(!isset($_POST['currentUserType'])) ? "bronze": $_POST['currentUserType'] ;

        //new models
        $service=ServiceAutoaccept::find()->where(['user_type'=>$currentUser])->one();
        $ondemand=OndemandAutoaccept::find()->where(['user_type'=>$currentUser])->one();
        $coldStorage=StorageAutoaccept::find()->where(['user_type'=>$currentUser])->one();
        $serviceLimits=ServiceLimits::find()->where(['user_type'=>$currentUser])->one();
        $ondemandLimits=OndemandLimits::find()->where(['user_type'=>$currentUser])->one();
        $machineComputationLimits=MachineComputeLimits::find()->where(['user_type'=>$currentUser])->one();
        $coldStorageLimits=StorageLimits::find()->where(['user_type'=>$currentUser])->one();
        $smtp= Smtp::find()->one();
        $openstack=Openstack::find()->one();
        $openstackMachines=OpenstackMachines::find()->one();
        $jupyter=JupyterAutoaccept::find()->where(['user_type'=>$currentUser])->one();
        $jupyterLimits=JupyterLimits::find()->where(['user_type'=>$currentUser])->one();





        $general=Configuration::find()->one();
        $pages=Page::getPagesDropdown();

        $activeButtons=['','','','','','','','',''];
        $activeTabs=['','','','','','','','',''];

        if (!isset($_POST['hidden-active-button']))
        {
            $activeButtons[0]='button-active';
            $activeTabs[0]='tab-active';
            $hiddenActiveButton='general-button';
        }

        $form_params =
            [
                'action' => URL::to(['administration/configure']),
                'options' =>
                    [
                        'class' => 'configuration_form',
                        'id'=> "configuration_form"
                    ],
                'method' => 'POST'
            ];


        $user_email=Userw::getCurrentUser()['email'];
        if(empty($user_email))
        {
            Yii::$app->session->setFlash('danger', "You must provide your email to receive email notifications.");
        }

        if ( ($service->load(Yii::$app->request->post())) && ($machineComputationLimits->load(Yii::$app->request->post())) && ($general->load(Yii::$app->request->post()))
            &&  ($ondemand->load(Yii::$app->request->post())) && ($coldStorage->load(Yii::$app->request->post()))
            && ($coldStorageLimits->load(Yii::$app->request->post())) && ($serviceLimits->load(Yii::$app->request->post()))
            && ($ondemandLimits->load(Yii::$app->request->post())) && ($smtp->load(Yii::$app->request->post()))
            && ($openstack->load(Yii::$app->request->post())) && $openstackMachines->load(Yii::$app->request->post())
            &&  ($jupyter->load(Yii::$app->request->post())) && ($jupyterLimits->load(Yii::$app->request->post()))
        )
        {

            $password=$smtp->password;
            $encrypted_password=base64_encode($password);
            $smtp->password=$encrypted_password;
            $smtp->update();

            $openstack->encode();
            $openstack->save();

            $openstackMachines->encode();
            $openstackMachines->save();



            $isValid = $general->validate();
            $isValid = $service->validate() && $isValid;
            $isValid = $ondemand->validate() && $isValid;
            $isValid = $coldStorage->validate() && $isValid;
            $isValid = $coldStorageLimits->validate() && $isValid;
            $isValid = $serviceLimits->validate() && $isValid;
            $isValid = $ondemandLimits->validate() && $isValid;
            $isValid = $jupyter->validate() && $isValid;
            $isValid = $jupyterLimits->validate() && $isValid;


            if ($isValid)
            {

                $previousUserType=$_POST['previousUserType'];
                $general->updateDB();
                $success='Configuration successfully updated';

                $max_autoaccepted_services=Project::getMaximumActiveAcceptedProjects(1,$previousUserType,2);
                $max_accepted_services=Project::getMaximumActiveAcceptedProjects(1,$previousUserType,[1,2]);
                if(($service->autoaccept_number > $serviceLimits->number_of_projects))
                {
                    Yii::$app->session->setFlash('danger', "The maximum number of 24/7 service projects should be greater than the number of autoaccepted projects");
                    $success='';
                }
                elseif($max_autoaccepted_services > $service->autoaccept_number)
                {

                    Yii::$app->session->setFlash('danger', "There is a $previousUserType user with $max_autoaccepted_services active autoaccepted 24/7 service projects");
                    $success='';
                }
                elseif($max_accepted_services > $serviceLimits->number_of_projects)
                {
                    Yii::$app->session->setFlash('danger', "There is a $previousUserType user with $max_accepted_services active 24/7 service projects");
                    $success='';
                }
                else
                {
                    $service->updateDB($previousUserType);
                    $serviceLimits->updateDB($previousUserType);
                }

                $max_autoaccepted_ondemand=Project::getMaximumActiveAcceptedProjects(0,$previousUserType,2);
                $max_accepted_ondemand=Project::getMaximumActiveAcceptedProjects(0,$previousUserType,[1,2]);

                if(($ondemand->autoaccept_number > $ondemandLimits->number_of_projects))
                {
                    Yii::$app->session->setFlash('danger', "The maximum number of on-demand batch computation projects should be greater than the number of autoaccepted projects");
                    $success='';
                }
                elseif($max_autoaccepted_ondemand > $ondemand->autoaccept_number)
                {

                    Yii::$app->session->setFlash('danger', "There is a $previousUserType user with $max_autoaccepted_ondemand active autoaccepted on-demand batch computation projects");
                    $success='';
                }
                elseif($max_accepted_ondemand > $ondemandLimits->number_of_projects)
                {
                    Yii::$app->session->setFlash('danger', "There is a $previousUserType user with $max_accepted_ondemand active on-demand batch computation projects");
                    $success='';
                }
                else
                {
                    $ondemand->updateDB($previousUserType);
                    $ondemandLimits->updateDB($previousUserType);
                }

                $jupyter->updateDB($previousUserType);
                $jupyterLimits->updateDB($previousUserType);




                $max_autoaccepted_volumes=Project::getMaximumActiveAcceptedProjects(2,$previousUserType,2);
                $max_accepted_volumes=Project::getMaximumActiveAcceptedProjects(2,$previousUserType,[1,2]);

                if(($coldStorage->autoaccept_number > $coldStorageLimits->number_of_projects))
                {
                    Yii::$app->session->setFlash('danger', "The maximum number of storage volumes projects should be greater than the number of autoaccepted projects");
                    $success='';
                }
                elseif($max_autoaccepted_volumes > $coldStorage->autoaccept_number)
                {

                    Yii::$app->session->setFlash('danger', "There is a $previousUserType user with $max_autoaccepted_volumes autoaccepted active storage volumes");
                    $success='';
                }
                elseif($max_accepted_volumes > $coldStorageLimits->number_of_projects)
                {
                    Yii::$app->session->setFlash('danger', "There is a $previousUserType user with $max_accepted_volumes active storage volumes");
                    $success='';
                }
                else
                {
                    $coldStorage->updateDB($previousUserType);
                    $coldStorageLimits->updateDB($previousUserType);
                }


                $max_accepted_machines=Project::getMaximumActiveAcceptedProjects(3,$previousUserType,[1,2]);
                if((($machineComputationLimits->number_of_projects==-1) && ($previousUserType=='gold')) ||
                    ($max_accepted_machines <= $machineComputationLimits->number_of_projects))
                {
                    // print_r($max_accepted_machines);
                    // exit(0);
                    $machineComputationLimits->updateDB($previousUserType);
                }
                else
                {
                    Yii::$app->session->setFlash('danger', "There is a $previousUserType user with $max_accepted_machines active on-demand computation machines projects");
                    $success='';
                }




                $service=ServiceAutoaccept::find()->where(['user_type'=>$currentUser])->one();
                $ondemand=OndemandAutoaccept::find()->where(['user_type'=>$currentUser])->one();
                $coldStorage=StorageAutoaccept::find()->where(['user_type'=>$currentUser])->one();
                $serviceLimits=ServiceLimits::find()->where(['user_type'=>$currentUser])->one();
                $ondemandLimits=OndemandLimits::find()->where(['user_type'=>$currentUser])->one();
                $coldStorageLimits=StorageLimits::find()->where(['user_type'=>$currentUser])->one();
                $machineComputationLimits=MachineComputeLimits::find()->where(['user_type'=>$currentUser])->one();
                $general=Configuration::find()->one();
                $jupyter=JupyterAutoaccept::find()->where(['user_type'=>$currentUser])->one();
                $jupyterLimits=JupyterLimits::find()->where(['user_type'=>$currentUser])->one();


                $activeButton=$_POST['hidden-active-button'];


                if ($activeButton=='ondemand-button')
                {
                    $activeButtons[1]='button-active';
                    $activeTabs[1]='tab-active';
                    $hiddenActiveButton='ondemand-button';
                }
                else if ($activeButton=='service-button')
                {
                    $activeButtons[2]='button-active';
                    $activeTabs[2]='tab-active';
                    $hiddenActiveButton='service-button';
                }
                else if ($activeButton=='machines-button')
                {
                    $activeButtons[3]='button-active';
                    $activeTabs[3]='tab-active';
                    $hiddenActiveButton='machines-button';
                }
                else if ($activeButton=='cold-button')
                {
                    $activeButtons[4]='button-active';
                    $activeTabs[4]='tab-active';
                    $hiddenActiveButton='cold-button';

                }
                else if ($activeButton=='email-button')
                {
                    $activeButtons[5]='button-active';
                    $activeTabs[5]='tab-active';
                    $hiddenActiveButton='email-button';
                }
                else if ($activeButton=='openstack-button')
                {
                    $activeButtons[6]='button-active';
                    $activeTabs[6]='tab-active';
                    $hiddenActiveButton='openstack-button';
                }
                else if ($activeButton=='openstack-machines-button')
                {
                    $activeButtons[7]='button-active';
                    $activeTabs[7]='tab-active';
                    $hiddenActiveButton='openstack-machines-button';



                } else if ($activeButton=='jupyter-button')
                {
                    $activeButtons[8]='button-active';
                    $activeTabs[8]='tab-active';
                    $hiddenActiveButton='jupyter-button';
                }
                else
                {
                    $activeButtons[0]='button-active';
                    $activeTabs[0]='tab-active';
                    $hiddenActiveButton='general-button';
                }

            }

            $smtp->password=base64_decode($smtp->password);
            $openstack->decode();
            $openstackMachines->decode();

            return $this->render('configure',['form_params'=>$form_params,'service'=>$service,
                'ondemand'=>$ondemand,'general'=>$general,
                'jupyter'=>$jupyter, 'jupyterLimits'=>$jupyterLimits,
                'coldStorage'=>$coldStorage, 'success'=>$success,
                "hiddenUser" => $currentUser,'userTypes'=>$userTypes, 'serviceLimits'=>$serviceLimits,
                'ondemandLimits'=>$ondemandLimits,'coldStorageLimits'=>$coldStorageLimits,
                'activeTabs'=>$activeTabs,'activeButtons' => $activeButtons,'hiddenActiveButton'=>$hiddenActiveButton, 'smtp'=>$smtp, 'machineComputationLimits'=>$machineComputationLimits,
                'openstack'=>$openstack,'openstackMachines'=>$openstackMachines,'pages'=>$pages]);
        }

        $smtp->password=base64_decode($smtp->password);
        $openstack->decode();
        $openstackMachines->decode();
        return $this->render('configure',['form_params'=>$form_params,'service'=>$service,
            'ondemand'=>$ondemand,'coldStorage'=>$coldStorage,'serviceLimits'=>$serviceLimits,
            'jupyter'=>$jupyter, 'jupyterLimits'=>$jupyterLimits,
            'ondemandLimits'=>$ondemandLimits,'coldStorageLimits'=>$coldStorageLimits,'general'=>$general,
            'userTypes'=>$userTypes, 'success'=>'',"hiddenUser" => $currentUser,
            'activeTabs'=>$activeTabs,'activeButtons' => $activeButtons,'hiddenActiveButton'=>$hiddenActiveButton, 'smtp'=>$smtp, 'machineComputationLimits'=>$machineComputationLimits,
            'openstack'=>$openstack,'openstackMachines'=>$openstackMachines,'pages'=>$pages]);
    }



    public function actionLoadTab($tab, $userType = 'bronze')
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;

        if ($tab === 'general') {
            $model = \app\models\Configuration::find()->one();
            $pages = \app\models\Page::getPagesDropdown();
            return $this->renderPartial('_tab_general', ['model' => $model, 'pages' => $pages]);
        }

        if ($tab === 'ondemand') {
            $userType = Yii::$app->request->get('userType', 'bronze');
            $userTypes = [
                'gold' => 'Gold',
                'silver' => 'Silver',
                'bronze' => 'Bronze',
            ];

            $ondemand = \app\models\OndemandAutoaccept::find()->where(['user_type' => $userType])->one();
            $ondemandLimits = \app\models\OndemandLimits::find()->where(['user_type' => $userType])->one();

            return $this->renderPartial('_tab_ondemand', [
                'ondemand' => $ondemand,
                'ondemandLimits' => $ondemandLimits,
                'userTypes' => $userTypes,
                'selectedUserType' => $userType,
            ]);
        }

        if ($tab === 'service') {
            $userType = Yii::$app->request->get('userType', 'bronze');
            $userTypes = [
                'gold' => 'Gold',
                'silver' => 'Silver',
                'bronze' => 'Bronze',
            ];

            $service = \app\models\ServiceAutoaccept::find()->where(['user_type' => $userType])->one();
            $serviceLimits = \app\models\ServiceLimits::find()->where(['user_type' => $userType])->one();

            return $this->renderPartial('_tab_service', [
                'service' => $service,
                'serviceLimits' => $serviceLimits,
                'userTypes' => $userTypes,
                'selectedUserType' => $userType,
            ]);
        }
        if ($tab === 'machines') {
            $userType = Yii::$app->request->get('userType', 'bronze');
            $userTypes = [
                'gold' => 'Gold',
                'silver' => 'Silver',
                'bronze' => 'Bronze',
            ];

            $machineComputationLimits = \app\models\MachineComputeLimits::find()->where(['user_type' => $userType])->one();

            return $this->renderPartial('_tab_machines', [
                'machineComputationLimits' => $machineComputationLimits,
                'userTypes' => $userTypes,
                'selectedUserType' => $userType,
            ]);
        }
        if ($tab === 'storage') {
            $userType = Yii::$app->request->get('userType', 'bronze');
            $userTypes = [
                'gold' => 'Gold',
                'silver' => 'Silver',
                'bronze' => 'Bronze',
            ];

            $storage = \app\models\StorageAutoaccept::find()->where(['user_type' => $userType])->one();
            $storageLimits = \app\models\StorageLimits::find()->where(['user_type' => $userType])->one();

            return $this->renderPartial('_tab_storage', [
                'storage' => $storage,
                'storageLimits' => $storageLimits,
                'userTypes' => $userTypes,
                'selectedUserType' => $userType,
            ]);
        }
        if ($tab === 'smtp') {
            $smtp = \app\models\Smtp::find()->one();
            $smtp->password = base64_decode($smtp->password);
            return $this->renderPartial('_tab_smtp', ['smtp' => $smtp]);
        }
        if ($tab === 'openstack') {
            $openstack = \app\models\Openstack::find()->one();
            $openstack->decode(); // assumes decode() sets plain-text for password fields
            return $this->renderPartial('_tab_openstack', ['openstack' => $openstack]);
        }
        if ($tab === 'openstack-machines') {
            $openstackMachines = \app\models\OpenstackMachines::find()->one();
            $openstackMachines->decode(); // Decode sensitive fields
            return $this->renderPartial('_tab_openstack_machines', [
                'openstackMachines' => $openstackMachines,
            ]);
        }

        if ($tab === 'jupyter') {
            $userType = Yii::$app->request->get('userType', 'bronze');
            $userTypes = [
                'gold' => 'Gold',
                'silver' => 'Silver',
                'bronze' => 'Bronze',
            ];

            $jupyter = \app\models\JupyterAutoaccept::find()->where(['user_type' => $userType])->one();
            $jupyterLimits = \app\models\JupyterLimits::find()->where(['user_type' => $userType])->one();

            return $this->renderPartial('_tab_jupyter', [
                'jupyter' => $jupyter,
                'jupyterLimits' => $jupyterLimits,
                'userTypes' => $userTypes,
                'selectedUserType' => $userType,
            ]);
        }
        if ($tab === 'extensions') {
            $projectType = (int) Yii::$app->request->get('projectType', 0);

            $projectTypes = \app\models\Project::TYPES;

            $limits = \app\models\ExtensionLimits::find()
                ->where(['project_type' => $projectType])
                ->indexBy('user_type')
                ->all();

            return $this->renderPartial('_tab_extension', [
                'limits' => $limits,
                'projectTypes' => $projectTypes,
                'selectedProjectType' => $projectType,
            ]);
        }






        return '<div class="alert alert-danger">Unknown tab</div>';
    }
    public function actionSaveGeneral()
    {
        $model = \app\models\Configuration::find()->one();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'General settings saved successfully.');
        } else {
            Yii::$app->session->setFlash('danger', 'Failed to save general settings.');
        }

        return $this->redirect(['administration/configure']);
    }
    public function actionSaveOndemand()
    {
        $request = Yii::$app->request;
        $userType = $request->post('user_type', 'bronze');

        $ondemand = \app\models\OndemandAutoaccept::find()->where(['user_type' => $userType])->one();
        $ondemandLimits = \app\models\OndemandLimits::find()->where(['user_type' => $userType])->one();

        if (
            $ondemand->load($request->post()) &&
            $ondemandLimits->load($request->post()) &&
            $ondemand->save() &&
            $ondemandLimits->save()
        ) {
            Yii::$app->session->setFlash('success', "On-demand settings saved for $userType.");
        } else {
            Yii::$app->session->setFlash('danger', "Failed to save On-demand settings.");
        }

        return $this->redirect(['administration/configure']);
    }
    public function actionSaveService()
    {
        $request = Yii::$app->request;
        $userType = $request->post('user_type', 'bronze');

        $service = \app\models\ServiceAutoaccept::find()->where(['user_type' => $userType])->one();
        $serviceLimits = \app\models\ServiceLimits::find()->where(['user_type' => $userType])->one();

        if (
            $service->load($request->post()) &&
            $serviceLimits->load($request->post()) &&
            $service->save() &&
            $serviceLimits->save()
        ) {
            Yii::$app->session->setFlash('success', "Service settings saved for $userType.");
        } else {
            Yii::$app->session->setFlash('danger', "Failed to save service settings.");
        }

        return $this->redirect(['administration/configure']);
    }

    public function actionSaveMachines()
    {
        $request = Yii::$app->request;
        $userType = $request->post('user_type', 'bronze');

        $machineComputationLimits = \app\models\MachineComputeLimits::find()->where(['user_type' => $userType])->one();

        if ($machineComputationLimits->load($request->post()) && $machineComputationLimits->save()) {
            Yii::$app->session->setFlash('success', "Machine limits saved for $userType.");
        } else {
            Yii::$app->session->setFlash('danger', "Failed to save machine limits for $userType.");
        }

        return $this->redirect(['administration/configure']);
    }

    public function actionSaveStorage()
    {
        $request = Yii::$app->request;
        $userType = $request->post('user_type', 'bronze');

        $storage = \app\models\StorageAutoaccept::find()->where(['user_type' => $userType])->one();
        $storageLimits = \app\models\StorageLimits::find()->where(['user_type' => $userType])->one();

        if (
            $storage->load($request->post()) &&
            $storageLimits->load($request->post()) &&
            $storage->save() &&
            $storageLimits->save()
        ) {
            Yii::$app->session->setFlash('success', "Cold storage settings saved for $userType.");
        } else {
            Yii::$app->session->setFlash('danger', "Failed to save cold storage settings for $userType.");
        }

        return $this->redirect(['administration/configure']);
    }

    public function actionSaveSmtp()
    {
        $smtp = \app\models\Smtp::find()->one();
        $smtp->password = base64_encode(Yii::$app->request->post('Smtp')['password']);

        if ($smtp->load(Yii::$app->request->post()) && $smtp->save()) {
            Yii::$app->session->setFlash('success', 'SMTP settings saved.');
        } else {
            Yii::$app->session->setFlash('danger', 'Failed to save SMTP settings.');
        }

        return $this->redirect(['administration/configure']);
    }

    public function actionSaveOpenstack()
    {
        $openstack = \app\models\Openstack::find()->one();

        if ($openstack->load(Yii::$app->request->post())) {
            $openstack->encode(); // securely encode sensitive fields
            if ($openstack->save()) {
                Yii::$app->session->setFlash('success', 'OpenStack settings saved.');
            } else {
                Yii::$app->session->setFlash('danger', 'Failed to save OpenStack settings.');
            }
        }

        return $this->redirect(['administration/configure']);
    }
    public function actionSaveOpenstackMachines()
    {
        $openstackMachines = \app\models\OpenstackMachines::find()->one();

        if ($openstackMachines->load(Yii::$app->request->post())) {
            $openstackMachines->encode(); // Encode sensitive fields
            if ($openstackMachines->save()) {
                Yii::$app->session->setFlash('success', 'OpenStack Machines settings saved.');
            } else {
                Yii::$app->session->setFlash('danger', 'Failed to save OpenStack Machines settings.');
            }
        }

        return $this->redirect(['administration/configure']);
    }
    public function actionSaveJupyter()
    {
        $request = Yii::$app->request;
        $userType = $request->post('user_type', 'bronze');

        $jupyter = \app\models\JupyterAutoaccept::find()->where(['user_type' => $userType])->one();
        $jupyterLimits = \app\models\JupyterLimits::find()->where(['user_type' => $userType])->one();

        if (
            $jupyter->load($request->post()) &&
            $jupyterLimits->load($request->post()) &&
            $jupyter->save() &&
            $jupyterLimits->save()
        ) {
            Yii::$app->session->setFlash('success', "Jupyter settings saved for $userType.");
        } else {
            Yii::$app->session->setFlash('danger', "Failed to save Jupyter settings for $userType.");
        }

        return $this->redirect(['administration/configure']);
    }
    public function actionSaveExtensionLimits()
    {
        $request = Yii::$app->request;
        $postData = $request->post('ExtensionLimits', []);
        $projectType = $request->post('project_type');

        $success = true;
        $errorMessages = [];

        foreach ($postData as $id => $attributes) {
            $model = \app\models\ExtensionLimits::findOne($id);

            if (!$model) {
                $success = false;
                $errorMessages[] = "[ID $id] Not found.";
                continue;
            }

            if (!$model->load(['ExtensionLimits' => $attributes]) || !$model->save()) {
                $success = false;
                $errors = $model->getFirstErrors();
                $formattedErrors = implode(', ', array_map(fn($e) => Html::encode($e), $errors));
                $errorMessages[] = ucfirst($model->user_type) . ": " . $formattedErrors;
            }
        }

        if ($success) {
            $projectTypeName = \app\models\Project::TYPES[$projectType] ?? "Unknown";
            Yii::$app->session->setFlash('success', "Extension limits saved for project type: {$projectTypeName}.");

        } else {
            Yii::$app->session->setFlash('danger', "Failed to save Extension limits:<br>" . implode('<br>', $errorMessages));
        }

        return $this->redirect(['administration/configure']);
    }





    public function actionAdministration()
    {
        return $this->render('administration');
    }

    public function actionPeriodStatistics()
    {
//        $schema=ProjectRequest::getSchemaPeriodUsage();
        $usage=ProjectRequest::getEgciPeriodUsage();
        $users=User::find()->where(['like','username','elixir-europe.org'])
            //->createCommand()->getRawSql();
            ->count();

//        $usage['o_jobs']=$schema['total_jobs'];
//        $usage['o_time']=$schema['total_time'];
        $usage['users']=$users;

        $metrics=Schema::getMetrics();
        $usage['task_executions'] = $metrics['num_of_executions'] ?? "n/a";
        $usage['running_tasks'] = $metrics['num_of_running_executions'] ?? "n/a";

        return $this->render('period_statistics',['usage'=>$usage]);
    }


    public function actionEmailNotifications()
    {

        $user=Userw::getCurrentUser();
        $user_id=$user->id;
        if (!Userw::hasRole('Admin',$superadminAllowed=true))
        {
            return $this->render('//project/error_unauthorized');
        }
        $user_notifications=EmailEventsAdmin::find()->where(['user_id'=>$user_id])->one();
        if(empty($user_notifications))
        {
            $user_notifications=new EmailEventsAdmin;
            $user_notifications->user_id=$user_id;
            $user_notifications->save();

        }
        $smtp_config=true;
        $smtp=Smtp::find()->one();
        if((empty($smtp->host)) || (empty($smtp->port)) || (empty($smtp->username)) || (empty($smtp->password)) || (empty($smtp->encryption)))
        {
            Yii::$app->session->setFlash('danger', "SMTP is not configured properly to enable email notifications");
            $smtp_config=false;
        }

        if($user->load(Yii::$app->request->post()) && $user_notifications->load(Yii::$app->request->post()))
        {
            $user->update();
            $user_notifications->update();
            Yii::$app->session->setFlash('success', "Your changes have been successfully submitted");
            return $this->redirect(['index']);
        }


        return $this->render('email_notifications', ['user'=>$user, 'user_notifications'=>$user_notifications, 'smtp_config'=>$smtp_config]);
    }

    public function actionTestSmtpConfiguration()
    {
        $user_email=Userw::getCurrentUser()['email'];
        $name=Yii::$app->params['name'];

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

        try {
            $r=Yii::$app->mailer->compose()
                ->setFrom("$smtp->username")
                ->setTo("$user_email")
                ->setSubject('Test')
                ->setTextBody('Plain text content')
                ->setHtmlBody("Dear Mr/Mrs,  <br> <br> This email is send as a test to the SMTP configuration. 
                 <br> <br> Sincerely, <br> $name")
                ->send();
            Yii::$app->session->setFlash('success', "SMTP is configured properly. A test email has been sent to you.");
        }
        catch (\Exception $e)
        {
            Yii::$app->session->setFlash('danger', "SMTP is not configured properly.");

        }


        return $this->redirect(['configure']);

    }

    public function actionManagePages()
    {
        if (!Userw::hasRole('Admin',$superadminAllowed=true))
        {
            return $this->render('//project/error_unauthorized');
        }
        $pages=Page::find()->all();

        return $this->render('manage-pages',['pages'=>$pages]);

    }

    public function actionAddPage()
    {
        if (!Userw::hasRole('Admin',$superadminAllowed=true))
        {
            return $this->render('//project/error_unauthorized');
        }
        $model=new Page;
        $form_params =
            [
                'action' => URL::to(['administration/add-page']),
                'options' =>
                    [
                        'class' => 'add_page_form',
                        'id'=> "add_page_form"
                    ],
                'method' => 'POST'
            ];

        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $model->save();
            $this->redirect(['administration/manage-pages']);
        }

        return $this->render('add-page',['model'=>$model,'form_params'=>$form_params]);

    }
    public function actionEditPage($id)
    {
        if (!Userw::hasRole('Admin',$superadminAllowed=true))
        {
            return $this->render('//project/error_unauthorized');
        }
        $page=Page::find()->where(['id'=>$id])->one();

        if (empty($page))
        {
            return $this->render('error_page_exist');
        }

        $form_params =
            [
                'action' => URL::to(['administration/edit-page', 'id'=>$id]),
                'options' =>
                    [
                        'class' => 'edit_page_form',
                        'id'=> "edit_page_form"
                    ],
                'method' => 'POST'
            ];

        if ($page->load(Yii::$app->request->post()) && $page->validate())
        {
            $page->save();
            $this->redirect(['administration/manage-pages']);
        }

        return $this->render('edit-page',['page'=>$page,'form_params'=>$form_params]);
    }
    public function actionDeletePage($id)
    {
        if (!Userw::hasRole('Admin',$superadminAllowed=true))
        {
            return $this->render('//project/error_unauthorized');
        }
        $page=Page::find()->where(['id'=>$id])->one();

        if (empty($page))
        {
            return $this->render('error_page_exist');
        }

        $page->delete();
        $this->redirect(['administration/manage-pages']);


    }
    public function actionViewPage($id)
    {
        if (!Userw::hasRole('Admin',$superadminAllowed=true))
        {
            return $this->render('//project/error_unauthorized');
        }
        $page=Page::find()->where(['id'=>$id])->one();

        if (empty($page))
        {
            return $this->render('error_page_exist');
        }

        return $this->render('view-page',['page'=>$page]);
    }

    public function actionAllProjects($exp = '-1', $ptype = '-1', $user = '', $project = '')
    {
        if (!Userw::hasRole('Admin', $superadminAllowed = true)) {
            return $this->render('//project/error_unauthorized');
        }

        $configuration = Configuration::find()->one();
        $schema_url = $configuration->schema_url;

        $project_types = Project::TYPES;
        $button_links = [
            0 => '/project/view-ondemand-request-user',
            1 => '/project/view-service-request-user',
            2 => '/project/view-cold-storage-request-user',
            3 => '/project/view-machine-compute-user',
            4 => '/project/view-jupyter-request-user'
        ];

        $filters = [
            'exp' => Yii::$app->request->post('expiry_date_t', $exp),
            'user' => Yii::$app->request->post('username', $user),
            'type' => Yii::$app->request->post('project_type', $ptype),
            'name' => Yii::$app->request->post('project_name', $project)
        ];

        $all_projects = Project::getAllActiveProjectsAdm($filters['user'], $filters['type'], $filters['exp'], $filters['name']);
        $expired_owner = Project::getAllExpiredProjects($filters['user'], $filters['type'], $filters['exp'], $filters['name']);

        $resources = Project::getActiveResources();  // This already returns [$jupyter, $vms, $machines, $volumes]

        $username = Userw::getCurrentUser()['username'];

        $active = [];
        foreach ($all_projects as $project) {
            $remaining_days = (strtotime($project['end_date']) - strtotime(date("Y-m-d"))) / 86400;
            $project['owner'] = ($username == $project['username']) ? '<b>You</b>' : $project['username'];
            $project['expires_in'] = $remaining_days;

            $project['id'] = $project['project_id'];

            $project['has_active_resources'] = isset($resources[$project['project_type']][$project['project_id']]);

            $active[] = $project;
        }

        $expired = [];
        foreach ($expired_owner as $project) {
            $project['owner'] = ($username == $project['username']) ? '<b>You</b>' : $project['username'];
            $project['expires_in'] = $project['end_date'];

            $project['id'] = $project['project_id'];

            $project['has_active_resources'] = isset($resources[$project['project_type']][$project['project_id']]);

            $expired[] = $project;
        }

        $searchModel = new ActiveProjectSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $active);

        $searchModelExpired = new ExpiredProjectSearch();
        $dataProviderExpired = $searchModelExpired->search(Yii::$app->request->queryParams, $expired, $resources);

        $types_dropdown = [
            '-1' => '',
            '0' => 'On-demand batch computations',
            '1' => '24/7 Services',
            '2' => 'Storage volumes',
            '3' => 'On-demand computation machines',
            '4' => 'On-demand notebooks'
        ];

        $expiry_date = ['-1' => '', '0' => 'Ascending', '1' => 'Descending'];


        return $this->render('all_projects', [
            'button_links' => $button_links,
            'project_types' => $project_types,
            'filters' => $filters,
            'deleted' => Project::getAllDeletedProjects(),
            'expired' => $expired,
            'active' => $active,
            'number_of_active' => count($active),
            'number_of_expired' => count($expired),
            'schema_url' => $schema_url,
            'active_resources' => $resources,
            'expiry_date' => $expiry_date,
            'ptype' => $ptype,
            'exp' => $exp,
            'user' => $user,
            'project' => $project,
            'role' => User::getRoleType(),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchModelExpired' => $searchModelExpired,
            'dataProviderExpired' => $dataProviderExpired,
        ]);
    }



    public function actionManageAnalytics()
    {
        $analytics=Analytics::find()->all();

        return $this->render('manage-analytics',['analytics'=>$analytics]);

    }

    public function actionAddAnalytics()
    {
        $model=new Analytics;
        $form_params =
            [
                'action' => URL::to(['administration/add-analytics']),
                'options' =>
                    [
                        'class' => 'add_analytics_form',
                        'id'=> "add_analytics_form"
                    ],
                'method' => 'POST'
            ];

        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $model->save();
            $this->redirect(['administration/manage-analytics']);
        }

        return $this->render('add-analytics',['model'=>$model,'form_params'=>$form_params]);

    }
    public function actionEditAnalytics($id)
    {
        if (!Userw::hasRole('Admin',$superadminAllowed=true))
        {
            return $this->render('//project/error_unauthorized');
        }

        $model=Analytics::find()->where(['id'=>$id])->one();

        if (empty($model))
        {
            return $this->render('error_analytics_exist');
        }

        $form_params =
            [
                'action' => URL::to(['administration/edit-analytics', 'id'=>$model->id]),
                'options' =>
                    [
                        'class' => 'edit_analytics_form',
                        'id'=> "edit_analytics_form"
                    ],
                'method' => 'POST'
            ];

        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $model->save();
            $this->redirect(['administration/manage-analytics']);
        }

        return $this->render('edit-analytics',['model'=>$model,'form_params'=>$form_params]);
    }
    public function actionDeleteAnalytics($id)
    {
        if (!Userw::hasRole('Admin',$superadminAllowed=true))
        {
            return $this->render('//project/error_unauthorized');
        }
        $model=Analytics::find()->where(['id'=>$id])->one();

        if (empty($model))
        {
            return $this->render('error_analytics_exist');
        }

        $model->delete();
        $this->redirect(['administration/manage-analytics']);


    }

    public  function actionStorageVolumes()
    {
        if (!Userw::hasRole('Admin',$superadminAllowed=true))
        {
            return $this->render('//project/error_unauthorized');
        }
        /*
         * Get storage active projects for user
         */
        $results_active=StorageRequest::getActiveProjectsAdmin();
        $active_services=$results_active[0];
        $active_machines=$results_active[1];
        $results_expired=StorageRequest::getExpiredProjectsAdmin();
        $expired_services=$results_expired[0];
        $expired_machines=$results_expired[1];

        return $this->render('storage_volumes', ['services'=>$active_services, 'machines'=>$active_machines, 'results'=>$results_active,'expired_services'=>$expired_services, 'expired_machines'=>$expired_machines, 'expired_results'=>$results_expired]);
    }

    public function actionReactivate($id)
    {
        $prequest=ProjectRequest::find()->where(['id'=>$id])->one();

        if (empty($prequest))
        {
            return $this->render('//project/error_unauthorized');
        }


        /*
         * If someone other than the project owner or an Admin are trying
         * to edit the request, then show an error.
         */
        if (!Userw::hasRole('Admin',$superadminAllowed=true))
        {
            return $this->render('//project/error_unauthorized');
        }
        /*
         * Check that project is expired.
         */
        $date1 = new \DateTime($prequest->end_date);
        $date2 = new \DateTime('now');

        /*
         * Since datetime involves time too
         * equality will not work. Instead, check that
         * the date strings are not the same
         */
        if (!(($date1->format("Y-m-d")!=$date2->format("Y-m-d")) && ($date2>$date1)))
        {
            Yii::$app->session->setFlash('danger', "Project is not expired");
            return $this->redirect(['administration/all-projects']);
        }

        $prequest->reactivate();
        if (!empty($prequest->errors))
        {
            Yii::$app->session->setFlash('danger', $prequest->errors);
            return $this->redirect(['administration/all-projects']);
        }

        Yii::$app->session->setFlash('success', "Project successfully re-activated");
        return $this->redirect(['administration/all-projects']);
    }

    public function actionUserStatistics($id)
    {
        $user=User::find()->where(['id'=>$id])->one();

        if (empty($user))
        {
            Yii::$app->session->setFlash('danger',"User not found in the Database.");
            return $this->redirect(['/administration/user-stats-list']);
        }
        $username=explode('@',$user->username)[0];
        $usage_owner=Project::userStatisticsOwner($user->id,$user->username);
        $usage_participant=Project::userStatisticsParticipant($user->id,$user->username);

        return $this->render('user_statistics', ['usage_participant'=>$usage_participant,'usage_owner'=>$usage_owner, 'username'=>$username]);
    }

    public function actionUserStatsList()
    {
        $username='';
        $activeFilterDrop=['all'=>'All', 'active'=>'Active', 'inactive'=>'Inactive'];
        $activeFilter='all';
        if (Yii::$app->request->post())
        {
            $username=Yii::$app->request->post('username');
            $activeFilter=Yii::$app->request->post('activeFilter');
        }
        $users=User::getActiveUserStats($username,$activeFilter);
        $activeUsers=User::getActiveUserNum();
        $totalUsers=User::find()->count();

        return $this->render('user_stats_list', ['users'=>$users,'username'=>$username, 'activeFilter'=>$activeFilter,
            'activeFilterDrop'=>$activeFilterDrop, 'activeUsers'=>$activeUsers, 'totalUsers'=>$totalUsers]);
    }

    public function actionViewActiveJupyters()
    {

        if (!Userw::hasRole("Admin", $superAdminAllowed = true))
        {
            return $this->render('unauthorized');
        }

        $servers=JupyterServer::find()->where(['active'=>true])->all();

        return $this->render('view_active_jupyters',['servers'=>$servers]);

    }
}
