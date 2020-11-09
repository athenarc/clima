<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\Headers;



Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'Edit VM credentials for service $service->name',])
?>
<?Headers::end()?>

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
