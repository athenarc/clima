<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

echo Html::CssFile('@web/css/project/vm-details.css');

$this->title="VM details for project $project->name";

$back_icon='<i class="fas fa-arrow-left"></i>';


?>

<div class="row"><div class="col-md-11 headers"><?=Html::encode($this->title)?></div><div class="col-md-1"><?= Html::a("$back_icon Back", ['/project/index'], ['class'=>'btn btn-default']) ?></div></div>
<?php

if (!empty($creds->ip) && !empty($creds->username) && !empty($creds->password))
{
?>
	<div class="row">&nbsp;</div>
	<div class="credentials-box">
		<div class="credentials-box-header"><div class='text-center'><h3>SSH login details</h3></div></div>
		<div class="credentials-box-content">
		
			<div class="row">
				<div class="col-md-5">
					<strong>IP Address (SSH):</strong>
				</div>
				<div class="col-md-2">
					<?=$creds->ip?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-5">
					<strong>Username:</strong>
				</div>
				<div class="col-md-2">
					<?=$creds->username?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-5">
					<strong>Password (please change immediately after login):</strong>
				</div>
				<div class="col-md-2">
					<?=$creds->password?>
				</div>
			</div>
		</div>
	</div>
<?php
}
else
{
?>
	<h2>Credentials for this VM are not available yet.</h2>
<?php
}
?>
