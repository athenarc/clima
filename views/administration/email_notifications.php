<?php

use yii\helpers\Html;
use app\components\ToolButton;
use webvimark\modules\UserManagement\models\User;
use app\components\Headers;
use yii\widgets\ActiveForm;

echo Html::CssFile('@web/css/personal-account-settings.css');
$this->title = "Email notifications";
?>
<?php $form = ActiveForm::begin();  ?>

<?php ob_start(); Headers::begin(); ob_get_clean(); ?>

<?php echo Headers::widget(
['title'=>$this->title, 
	'buttons'=>
	[
		['fontawesome_class'=>'<i class="fas fa-check"></i>', 'name'=>'Submit', 'action'=>'', 'type'=>'submitButton', 'options'=>['class'=>'btn btn-primary']],
		['fontawesome_class'=>'<i class="fas fa-arrow-left"></i>', 'name'=>'Back', 'action'=>['administration/index'], 'type'=>'a', 'options'=>['class'=>'btn btn-default']]
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
{?>
<div class="col-md-5 col-md-offset-4">
	<?= $form->field($user_notifications, 'user_creation')->checkBox(['label'=>'', 'disabled'=>true])->label('User creation') ?>
</div>
<div class="col-md-5 col-md-offset-4">
	<?= $form->field($user_notifications, 'new_ticket')->checkBox(['label'=>'', 'disabled'=>true])->label('New ticket') ?>
</div>
<?php
}
else
{?>
<div class="col-md-5 col-md-offset-4">
	<?= $form->field($user_notifications, 'user_creation')->checkBox(['label'=>''])->label('User creation') ?>
</div>
<div class="col-md-5 col-md-offset-4">
	<?= $form->field($user_notifications, 'new_ticket')->checkBox(['label'=>'',])->label('New ticket') ?>
</div>
<?php
}?>
<?php ActiveForm::end(); ?>

