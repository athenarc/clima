<?php

use yii\helpers\Html;
use app\components\ToolButton;
use webvimark\modules\UserManagement\models\User;
use app\components\Headers;

echo Html::CssFile('@web/css/personal-account-settings.css');
$this->title = "New Request";

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>"", 
])
?>
<?Headers::end()?>

<?= ToolButton::createButton("View project requests", "",['/project/request-list']) ?>
<br />
<?= ToolButton::createButton("Email notifications", "",['/project/moderator-email-notifications']) ?>
<br />
