<?php

use yii\helpers\Html;
use app\components\ToolButton;
use webvimark\modules\UserManagement\models\User;
use app\components\Headers;
use yii\widgets\ActiveForm;


$this->title = "Email notifications";
?>
<?php $form = ActiveForm::begin();  ?>

<?php ob_start(); Headers::begin(); ob_get_clean(); ?>

<?php echo Headers::widget(
['title'=>$this->title, 
	'buttons'=>
	[
		['fontawesome_class'=>'<i class="fas fa-check"></i>', 'name'=>'Submit', 'action'=>'', 'type'=>'submitButton', 'options'=>['class'=>'btn btn-primary']],
		['fontawesome_class'=>'<i class="fas fa-arrow-left"></i>', 'name'=>'Back', 'action'=>['project/index'], 'type'=>'a', 'options'=>['class'=>'btn btn-default']]
	]
])
?>
<?php ob_start(); Headers::end(); ob_get_clean();?>



<div class="row"></div>
<div class="col-md-5 col-md-offset-4" style="margin-top: 50px;">
	<?= $form->field($user, 'email')->label('Email address to receive notifications') ?>
</div>
<div class="col-md-5 col-md-offset-4"><h3>Send an email notification when:</h3></div>
<?php
if($smtp_config==false)
{
	$disabled=true;
}
else
{
	$disabled=false;
}
?>

<div class="col-md-5 col-md-offset-4">
	<?= $form->field($user_notifications, 'new_project')->checkBox(['label'=>'','disabled'=>$disabled])->label('New project request') ?>
</div>
<div class="col-md-5 col-md-offset-4">
	<?= $form->field($user_notifications, 'edit_project')->checkBox(['label'=>'','disabled'=>$disabled])->label('Project modification') ?>
</div>
<div class="col-md-5 col-md-offset-4">
	<?= $form->field($user_notifications, 'project_decision')->checkBox(['label'=>'','disabled'=>$disabled])->label('Project decision') ?>
</div>
<div class="col-md-5 col-md-offset-4">
	<?= $form->field($user_notifications, 'expires_30')->checkBox(['label'=>'','disabled'=>$disabled])->label('Project expires in 30 days') ?>
</div>
<div class="col-md-5 col-md-offset-4">
	<?= $form->field($user_notifications, 'expires_15')->checkBox(['label'=>'','disabled'=>$disabled])->label('Project expires in 15 days') ?>
</div>

<div class="col-md-5 col-md-offset-4">
	<?= $form->field($user_notifications, 'expires_5')->checkBox(['label'=>'','disabled'=>$disabled])->label('Project expires in 5 days') ?>
</div>
<div class="col-md-5 col-md-offset-4">
	<?= $form->field($user_notifications, 'expires_1')->checkBox(['label'=>'','disabled'=>$disabled])->label('Project expires in one day') ?>
</div>


<?php ActiveForm::end(); ?>

