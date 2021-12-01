<?php

use yii\helpers\Html;
use webvimark\modules\UserManagement\models\User;
use yii\widgets\ActiveForm;

$this->title='Add new analytics';
$back_icon='<i class="fas fa-arrow-left"></i>';
/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */
?>
<div class='title row'>
	<div class="col-md-11">
		<h1><?= Html::encode($this->title) ?></h1>
	</div>
	<div class="col-md-1 float-right">
		<?= Html::a("$back_icon Back", ['/administration/manage-analytics'], ['class'=>'btn btn-default']) ?>
	</div>
</div>

<?php $form = ActiveForm::begin($form_params);  ?>

<?= $form->field($model, 'name') ?>

<?= $form->field($model, 'code')->textarea(['rows'=>20,]) ?>

<?= $form->field($model, 'opt_out_code')->textarea(['rows'=>20,]) ?>

 <div class="row"><div class="col-md-12"><?= Html::errorSummary($model, ['encode' => false]) ?></div></div>
 <div class="row">
    <div class="col-md-1"><?= Html::submitButton('<i class="fas fa-check"></i> Submit', ['class' => 'btn btn-primary']) ?></div>
    <div class="col-md-1"><?= Html::a('<i class="fas fa-times"></i> Cancel', ['/administration/manage-analytics'], ['class'=>'btn btn-default']) ?></div>           
</div>

<?php ActiveForm::end(); ?>

