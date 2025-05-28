<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var $ondemand \app\models\OndemandAutoaccept */
/** @var $ondemandLimits \app\models\OndemandLimits */
/** @var $userTypes array */
/** @var $selectedUserType string */

?>

<div class="mb-3">
    <?= Html::dropDownList('user_type', $selectedUserType, $userTypes, [
        'id' => 'ondemand-user-type',
        'class' => 'form-select',
    ]) ?>
</div>

<div id="ondemand-form-container">
    <?php
    $form = ActiveForm::begin([
        'id' => 'ondemand-form',
        'action' => ['administration/save-ondemand'],
        'method' => 'post',
    ]);
    ?>

    <?= Html::hiddenInput('user_type', $selectedUserType) ?>

    <h3>Automatically accepted projects</h3>
    <?= $form->field($ondemand, 'autoaccept_number')->label("") ?>

    <h3>Maximum number of accepted projects</h3>
    <?= $form->field($ondemandLimits, 'number_of_projects')->label("") ?>

    <h3>Upper limits for approval without review</h3>
    <?= $form->field($ondemand, 'num_of_jobs') ?>
    <?= $form->field($ondemand, 'cores') ?>
    <?= $form->field($ondemand, 'ram') ?>

    <h3>Upper limits for resources</h3>
    <?= $form->field($ondemandLimits, 'num_of_jobs') ?>
    <?= $form->field($ondemandLimits, 'cores') ?>
    <?= $form->field($ondemandLimits, 'ram') ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
