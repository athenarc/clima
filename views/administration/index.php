<?php

use yii\helpers\Html;
use app\components\ToolButton;
use webvimark\modules\UserManagement\models\User;
use app\components\Headers;

echo Html::CssFile('@web/css/personal-account-settings.css');
$this->title = "Administration";


$icon_tickets='<i class="fas fa-ticket-alt" aria-hidden="true"></i>';
$email_icon='<i class="fa fa-envelope" aria-hidden="true"></i>';
$projects_icon='<i class="fa fa-briefcase" aria-hidden="true"></i>';
$statistics_icon='<i class="fas fa-chart-line"></i>';
$users_icon='<i class="fa fa-users" aria-hidden="true"></i>';
$vm_history_icon='<i class="fa fa-history" aria-hidden="true"></i>';
$configuration_icon='<i class="fa fa-cogs" aria-hidden="true"></i>';
$static_pages_icon='<i class="fas fa-file-alt"></i>';
$notifications_icon='<i class="fa fa-bell" aria-hidden="true"></i>';


Headers::begin() ?>
<?php echo Headers::widget(
['title'=>"", 
])
?>
<?Headers::end()?>

<?php
$name=Yii::$app->params['name'];

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

<!--  <div class="text-center container-fluid">
 	<div class="row">
 		<div class="col-md-12 account-settings-title">
 			<h1>Select a </h1>
 		</div>
 	</div>
 </div> -->

<?= ToolButton::createButton("$notifications_icon Mass notification", "",['/site/mass-notification']) ?>
<br />
<?= ToolButton::createButton("$statistics_icon Current $name statistics", "",['/administration/period-statistics']) ?>
<br />
<?= ToolButton::createButton("$icon_tickets Ticket management", "",['/ticket-admin/index']) ?>
<br />
<?= ToolButton::createButton("$users_icon User management", "",['/personal/superadmin-actions']) ?>
<br />
<?= ToolButton::createButton("$vm_history_icon  VM 24/7 history", "",['/project/vm-list']) ?>
<br />
<?= ToolButton::createButton("$vm_history_icon  VM machines history", "",['/project/vm-machines-list']) ?>
<br />
<?= ToolButton::createButton("$projects_icon  View all projects", "",['administration/all-projects']) ?>
<br />
<?= ToolButton::createButton("$configuration_icon System configuration", "",['administration/configure']) ?>
<br />
<?= ToolButton::createButton("$email_icon Email notifications", "",['administration/email-notifications']) ?>
<br />
<?= ToolButton::createButton("$static_pages_icon Manage static pages", "",['administration/manage-pages']) ?>
<br />
