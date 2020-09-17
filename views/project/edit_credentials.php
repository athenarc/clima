<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title="Edit VM credentials for service $service->name";

?>

<div class="row">
	<div class="col-md-12 headers">
		<?=Html::encode($this->title)?>
	</div>
</div>

<?php

	$form=ActiveForm::begin($form_params);
?>
	<?= $form->field($creds, 'ip') ?>
	<?= $form->field($creds, 'username') ?>
    <?= $form->field($creds, 'password') ?>
    <?= Html::submitButton('<i class="fas fa-check"></i> Submit', ['class' => 'btn btn-primary']) ?>
<?php
	ActiveForm::end();
?>
