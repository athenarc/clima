<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var $storage \app\models\StorageAutoaccept */
/** @var $storageLimits \app\models\StorageLimits */
/** @var $userTypes array */
/** @var $selectedUserType string */
?>

<!-- User type dropdown -->
<div class="mb-3">
    <?= Html::dropDownList('user_type', $selectedUserType, $userTypes, [
        'id' => 'storage-user-type',
        'class' => 'form-select',
    ]) ?>
</div>

<div id="storage-form-container">
    <?php $form = ActiveForm::begin([
        'id' => 'storage-form',
        'action' => ['administration/save-storage'],
        'method' => 'post',
    ]); ?>

    <?= Html::hiddenInput('user_type', $selectedUserType) ?>

    <h4>Automatically accepted projects</h4>
    <?= $form->field($storage, 'autoaccept_number')->label("") ?>

    <h4>Maximum number of accepted projects</h4>
    <?= $form->field($storageLimits, 'number_of_projects')->label("") ?>

    <h4>Upper limits for approval without review</h4>
    <?= $form->field($storage, 'storage') ?>

    <h4>Upper limits for resources</h4>
    <?= $form->field($storageLimits, 'storage') ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
