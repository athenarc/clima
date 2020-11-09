<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\Headers;

echo Html::CssFile('@web/css/project/vm-details.css');



$back_icon='<i class="fas fa-arrow-left"></i>';


Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'VM details for project $project->name',]
	'buttons'=>
	[
		['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=> ['/project/index'], 'type'=>'a', 'options'=>['class'=>'btn btn-default'] ],
		
	],
)
?>
<?Headers::end()?>


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
