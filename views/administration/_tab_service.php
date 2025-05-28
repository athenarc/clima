<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var $service \app\models\ServiceAutoaccept */
/** @var $serviceLimits \app\models\ServiceLimits */
/** @var $userTypes array */
/** @var $selectedUserType string */
?>

<!-- User type dropdown -->
<div class="mb-3">
    <?= Html::dropDownList('user_type', $selectedUserType, $userTypes, [
        'id' => 'service-user-type',
        'class' => 'form-select',
    ]) ?>
</div>

<div id="service-form-container">
    <?php $form = ActiveForm::begin([
        'id' => 'service-form',
        'action' => ['administration/save-service'],
        'method' => 'post',
    ]); ?>

    <?= Html::hiddenInput('user_type', $selectedUserType) ?>

    <h4>Automatically accepted projects</h4>
    <?= $form->field($service, 'autoaccept_number')->label("") ?>

    <h4>Maximum number of accepted projects</h4>
    <?= $form->field($serviceLimits, 'number_of_projects')->label("") ?>

    <h4>Upper limits for approval without review for 24/7 service projects</h4>
    <?= $form->field($service, 'vms') ?>
    <?= $form->field($service, 'cores') ?>
    <?= $form->field($service, 'ips') ?>
    <?= $form->field($service, 'ram') ?>
    <?= $form->field($service, 'storage') ?>

    <h4>Upper limits for resources for 24/7 service projects</h4>
    <?= $form->field($serviceLimits, 'vms') ?>
    <?= $form->field($serviceLimits, 'cores') ?>
    <?= $form->field($serviceLimits, 'ips') ?>
    <?= $form->field($serviceLimits, 'ram') ?>
    <?= $form->field($serviceLimits, 'storage') ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
