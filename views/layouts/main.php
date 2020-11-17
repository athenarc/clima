<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\components\SupportWindow;
use webvimark\modules\UserManagement\models\User;
use app\components\NotificationWidget;


AppAsset::register($this);

$twitter_icon='<i class="fab fa-twitter fa-2x"></i>';
$twitter_link=Html::a($twitter_icon,'https://twitter.com/ELIXIRGR_Comp',
    ['target'=>'_blank']);
$youtube_icon='<i class="fab fa-youtube fa-2x" style="color:red"></i>';
$youtube_link=Html::a($youtube_icon,'https://www.youtube.com/channel/UC6ek-jYFfq0FDEcSJF4UEuw',
    ['target'=>'_blank']);

//Include font-awsome icons
echo Html::cssFile('https://use.fontawesome.com/releases/v5.5.0/css/all.css', ['integrity'=> 'sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU', 'crossorigin'=> 'anonymous']);
echo Html::cssFile('@web/css/components/notificationWidget.css');
$this->registerJsFile('@web/js/components/notificationWidget.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>


<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php

    if(Yii::$app->user->getIsGuest() == false)
    {
        SupportWindow::show(Yii::$app->request->absoluteUrl);
    }

   NavBar::begin([
        // 'brandLabel' => Yii::$app->name,
        'brandLabel' => Html::img('@web/img/layouts/eg-ci-logo-h.png',['class'=>"navbar-logo"]),
        'brandUrl' => Yii::$app->homeUrl,
		'options' => [
            //'class' => 'navbar-default navbar-fixed-top',
            'class' => 'navbar navbar-default navbar-fixed-top navbar-expand-md bg-light', 
        ],
    ]);

    // print_r(User::getCurrentUser());
    // exit(0);
    $menuItems=[];
            // ['label' => 'Home', 'url' => ['/site/index']],
            // ['label' => 'About', 'url' => ['/site/about']],
            // ['label' => 'Service', 'url' => ['/request/index']]
            
    // ];

    // if(User::hasRole("Admin", $superAdminAllowed = true)){
    //    $menuItems[]=['label' => Html::tag('i', '', ['class'=> 'fas fa-cog']) . ' Configure', 'url' => ['/site/configure'], ];
    // }
            // ['label' => 'Contact', 'url' => ['/site/contact']]];
    if(Yii::$app->user->getIsGuest() == true)
    {
        $menuItems[]=['label' => 'Login', 'url'=> ['/user-management/auth/login']];
    }
    
    // if(User::hasRole("SystemUser", $superAdminAllowed = true) || 
    //     User::hasRole("Admin", $superAdminAllowed = true))
    // {
    //     // $menuItems[]=['label' => 'Software', 'url' => ['/software/index']];
    //     // // $menuItems[]=['label' => 'Workflows', 'url' => ['/workflows/index']];
    //     // $menuItems[]=['label' => 'Files','url' => ['/filebrowser/index']];
    //     // $menuItems[]=['label' => 'My history','url' => ['/software/history']];
    //     // $menuItems[]=['label' => 'Account settings', 'url' => ['/personal/index']];
    // }

    if(User::hasRole("Temporary", $superAdminAllowed = true) || User::hasRole("Silver", $superAdminAllowed = true) || User::hasRole("Gold", $superAdminAllowed = true))
    {
        $menuItems[]=['label' => 'Dashboard', 'url' => ['/project/index']];
        $menuItems[]=['label' => 'User options', 'url' => ['/personal/user-options']];
        
    }

    if(User::hasRole("Admin", $superAdminAllowed = true) || User::hasRole("Moderator", $superAdminAllowed = true))
    {
        
        $menuItems[]=['label' => 'Moderator options', 'url' => ['/project/moderator-options']];
    }

    if(User::hasRole("Admin", $superAdminAllowed = true))
    {
        $menuItems[]=['label' => 'Admin options', 'url' => ['/administration/index']];
    }

    if(Yii::$app->user->getIsGuest() == false)
    {
        $menuItems[]=[
                        'label' => 'Help', 
                        'items' =>[
                            [
                                'label'=>'Documentation',
                                'url' => 'https://docs.google.com/document/d/1BLANG3SWOulkcNuM0TEI0etZF_d1qWwO3HeZ9PkR3jc/edit?usp=sharing',
                                'linkOptions' => ['target'=>'_blank']
                            ],
                            [
                                'label'=>'Support',
                                'url' => ['ticket-user/index']
                            ]
                        ]
                    ];
        // $menuItems[]=['label' => 'Help', 
        //               'url' => 'https://docs.google.com/document/d/1BLANG3SWOulkcNuM0TEI0etZF_d1qWwO3HeZ9PkR3jc/edit?usp=sharing',
        //               'linkOptions' => ['target'=>'_blank'] ];
        $username=explode('@',User::getCurrentUser()['username'])[0];
        $menuItems[]=[
            'label' => 'Logout (' . $username . ')',
            'url' => ['/user-management/auth/logout'],
            'linkOptions' => ['data-method' => 'post']
        ];
        $notifications=NotificationWidget::createMenuItem();

        $menuItems[]=
        [
            'label'=>$notifications[0],
            'items'=>$notifications[1],
        ];


    }

    // print_r(Yii::$app->request->url);
    // exit(0);
   

    echo Nav::widget([

        'options' => ['class' => 'navbar-nav navbar-right treeview' ],
        'items' => $menuItems,
		'encodeLabels' => false,
		//'route'=>Yii::$app->request->url,
		//'activateItems' => true,
		//'activateParents' => true,
        // [
        //     ['label' => 'Home', 'url' => ['/site/index']],
        //     ['label' => 'About', 'url' => ['/site/about']],
        //     ['label' => 'Contact', 'url' => ['/site/contact']],
        //     Yii::$app->user->isGuest ? (
        //         ['label' => 'Login', 'url' => ['/site/login']]
        //     ) : (
        //         '<li>'
        //         . Html::beginForm(['/site/logout'], 'post')
        //         . Html::submitButton(
        //             'Logout (' . Yii::$app->user->identity->username . ')',
        //             ['class' => 'btn btn-link logout']
        //         )
        //         . Html::endForm()
        //         . '</li>'
        //     )
        // ],
    ]);
    NavBar::end();

    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="text-left copyright">&copy; ELIXIR-GR <?= date('Y') ?></div>
            <div class="col-md-offset-4 col-md-1 text-center"><?= Html::img('@web/img/layouts/elixir-dark.png',['class'=>"navbar-logo"]) ?> </div>
            <div class="col-md-offset-2 col-md-2 text-right"><?=Html::a('Privacy & cookie policy',['site/privacy'])?></div>
            <div class="pull-right"><?=$twitter_link?>&nbsp;<?=$youtube_link?></div>
        </div>
        <div class="row">
            <div class="col-md-12 text-center espa-wrapper"><?= Html::img('@web/img/layouts/espa-logo.png',['class'=>'espa-logo'])?></div>
        </div>
    </div>
</footer>



<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
