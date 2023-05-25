<?php

use yii\helpers\Html;
use app\components\ToolButton;
use webvimark\modules\UserManagement\models\User;
use app\components\Headers;

echo Html::CssFile('@web/css/personal-account-settings.css');
$this->title = "New Request";


$icon_tickets='<i class="fas fa-ticket-alt" aria-hidden="true"></i>';
$email_icon='<i class="fa fa-envelope" aria-hidden="true"></i>';
$upgrade_icon='<i class="fa fa-arrow-circle-up" aria-hidden="true"></i>';

Headers::begin() ?>

<?php echo Headers::widget(
['title'=>"", 
])
?>
<?Headers::end()?>

<?php
if (empty($errors))
{
	if (!empty($success))
	{
		echo '<div class="alert alert-success row" role="alert">';
		echo $success;
		echo '</div>';
	}
	if (!empty($warning))
	{
		echo '<div class="alert alert-warning row" role="alert">';
		echo $warning;
		echo '</div>';
	}

}
else
{
	echo '<div class="alert alert-danger row" role="alert">';
	echo $errors;
	echo '</div>';

}



?>
<div class="row justify-content-center">
    <div class="col-md-6">
		<?= ToolButton::createButton("$icon_tickets View my tickets", "",['/ticket-user/index']) ?>
		<br />
		<?= ToolButton::createButton("$upgrade_icon User upgrade", "",['/ticket-user/open', 'upgrade'=>1]) ?>
		<br />
		<?= ToolButton::createButton("$email_icon Email notifications", "",['/personal/email-notifications']) ?>
		<br />
	</div>
</div>
