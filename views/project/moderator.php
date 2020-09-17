<?php

use yii\helpers\Html;
use app\components\ToolButton;
use webvimark\modules\UserManagement\models\User;

echo Html::CssFile('@web/css/personal-account-settings.css');
$this->title = "New Request";


<?= ToolButton::createButton("View requests", "",['/project/request_list']) ?>
<br />
<?= ToolButton::createButton("View VM history", "",['/project/vm-list']) ?>
<br />

