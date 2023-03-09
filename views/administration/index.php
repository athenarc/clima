<?php

use yii\helpers\Html;
use app\components\ToolButton;
use webvimark\modules\UserManagement\models\User;
use app\components\Headers;

echo Html::CssFile('@web/css/administration/index.css');
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
$analytics_icon='<i class="fas fa-chart-pie"></i>';
$volumes_icon='<i class="fas fa-hdd"></i>';


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
<div class="row justify-content-center">
	<div class="fieldset col-md-6">
		<div class="fieldset-label ">Users</div>
		<br />
		<?= ToolButton::createButton("$users_icon User management", "",['/personal/superadmin-actions']) ?>
		<br />
		<?= ToolButton::createButton("$users_icon User statistics", "",['/administration/user-stats-list']) ?>
		<br />
	</div>
</div>

<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>

<div class="row justify-content-center">
	<div class="fieldset col-md-6">
		<div class="fieldset-label ">Projects</div>
		<br />
		<?= ToolButton::createButton("$projects_icon  View all projects", "",['administration/all-projects']) ?>
		<br />
	</div>
</div>

<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>

<div class="row justify-content-center">
	<div class="fieldset col-md-6">
		<div class="fieldset-label ">Notifications</div>
		<br />
		<?= ToolButton::createButton("$notifications_icon Mass notification", "",['/site/mass-notification']) ?>
		<br />
		<?= ToolButton::createButton("$email_icon Email notifications", "",['administration/email-notifications']) ?>
		<br />
	</div>
</div>

<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>

<div class="row justify-content-center">
	<div class="fieldset col-md-6">
		<div class="fieldset-label ">Resources</div>
		<br />
		<?= ToolButton::createButton("$vm_history_icon  View VMs (24/7 projects)", "",['/project/vm-list']) ?>
		<br />
		<?= ToolButton::createButton("$vm_history_icon  View VMs (on demand computation machines)", "",['/project/vm-machines-list']) ?>
		<br />
		<?= ToolButton::createButton("$volumes_icon  View storage volumes", "",['administration/storage-volumes']) ?>
		<br />
	</div>
</div>

<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>

<div class="row justify-content-center">
	<div class="fieldset col-md-6">
		<div class="fieldset-label">System</div>
		<br />
		<?= ToolButton::createButton("$statistics_icon Current $name statistics", "",['/administration/period-statistics']) ?>
		<br />
		<?= ToolButton::createButton("$icon_tickets Ticket management", "",['/ticket-admin/index']) ?>
		<br />
		<?= ToolButton::createButton("$configuration_icon System configuration", "",['administration/configure']) ?>
		<br />
		<?= ToolButton::createButton("$static_pages_icon Manage static pages", "",['administration/manage-pages']) ?>
		<br />
		<?= ToolButton::createButton("$analytics_icon Manage Analytics", "",['administration/manage-analytics']) ?>
		<br />
	</div>
</div>


		
