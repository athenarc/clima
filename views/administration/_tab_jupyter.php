<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var $jupyter \app\models\JupyterAutoaccept */
/** @var $jupyterLimits \app\models\JupyterLimits */
/** @var $userTypes array */
/** @var $selectedUserType string */
?>

<!-- User type dropdown -->
<div class="mb-3">
    <?= Html::dropDownList('user_type', $selectedUserType, $userTypes, [
        'id' => 'jupyter-user-type',
        'class' => 'form-select',
    ]) ?>
</div>

<div id="jupyter-form-container">
    <?php $form = ActiveForm::begin([
        'id' => 'jupyter-form',
        'action' => ['administration/save-jupyter'],
        'method' => 'post',
    ]); ?>

    <?= Html::hiddenInput('user_type', $selectedUserType) ?>

    <h4>Automatically accepted projects</h4>
    <?= $form->field($jupyter, 'autoaccept_number')->label("") ?>

    <h4>Maximum number of accepted projects</h4>
    <?= $form->field($jupyterLimits, 'number_of_projects')->label("") ?>

    <h4>Upper limits for approval without review</h4>
    <?= $form->field($jupyter, 'cores') ?>
    <?= $form->field($jupyter, 'ram') ?>

    <h4>Upper limits for resources</h4>
    <?= $form->field($jupyterLimits, 'participants') ?>
    <?= $form->field($jupyterLimits, 'duration') ?>
    <?= $form->field($jupyterLimits, 'cores') ?>
    <?= $form->field($jupyterLimits, 'ram') ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
