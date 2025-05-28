<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;

/** @var $model \app\models\Configuration */
/** @var $pages array */

$form = ActiveForm::begin([
    'id' => 'general-form',
    'action' => ['administration/save-general'],
    'method' => 'post',
]);
?>

<h3>General Configuration</h3>

<?= $form->field($model, 'reviewer_num') ?>
<?= $form->field($model, 'schema_url') ?>
<?= $form->field($model, 'home_page')->dropDownList($pages, ['prompt' => 'Select page']) ?>
<?= $form->field($model, 'privacy_page')->dropDownList($pages, ['prompt' => 'Select page']) ?>
<?= $form->field($model, 'help_page')->dropDownList($pages, ['prompt' => 'Select page']) ?>

<?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>

<?php ActiveForm::end(); ?>
