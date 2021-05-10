<?php

use yii\helpers\Html;
use app\components\ToolButton;
use webvimark\modules\UserManagement\models\User;
use app\components\Headers;

echo Html::CssFile('@web/css/personal-account-settings.css');
$this->title = "New Request";


$projects_icon='<i class="fa fa-briefcase" aria-hidden="true"></i>';
$email_icon='<i class="fa fa-envelope" aria-hidden="true"></i>';

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>"", 
])
?>
<?Headers::end()?>

<?= ToolButton::createButton("$projects_icon View project requests", "",['/project/request-list']) ?>
<br />
<?= ToolButton::createButton("$email_icon Email notifications", "",['/project/moderator-email-notifications']) ?>
<br />
