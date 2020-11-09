<?php

use yii\helpers\Html;
use app\components\ToolButton;
use webvimark\modules\UserManagement\models\User;
use app\components\Headers;

echo Html::CssFile('@web/css/personal-account-settings.css');

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>"New Request", 
])
?>
<?Headers::end()?>

<?= ToolButton::createButton("View project requests", "",['/project/request-list']) ?>
<br />
