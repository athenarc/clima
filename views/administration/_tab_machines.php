<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var $machineComputationLimits \app\models\MachineComputeLimits */
/** @var $userTypes array */
/** @var $selectedUserType string */
?>

<!-- User type dropdown -->
<div class="mb-3">
    <?= Html::dropDownList('user_type', $selectedUserType, $userTypes, [
        'id' => 'machines-user-type',
        'class' => 'form-select',
    ]) ?>
</div>

<div id="machines-form-container">
    <?php $form = ActiveForm::begin([
        'id' => 'machines-form',
        'action' => ['administration/save-machines'],
        'method' => 'post',
    ]); ?>

    <?= Html::hiddenInput('user_type', $selectedUserType) ?>

    <h4>Maximum number of accepted projects</h4>
    <?= $form->field($machineComputationLimits, 'number_of_projects')->label("") ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
