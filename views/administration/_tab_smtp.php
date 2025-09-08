<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var $smtp \app\models\Smtp */
?>

<div class="row mb-3">
    <div class="col-md-8">
        <?php $form = ActiveForm::begin([
            'id' => 'smtp-form',
            'action' => ['administration/save-smtp'],
            'method' => 'post',
        ]); ?>

        <?= $form->field($smtp, 'encryption') ?>
        <?= $form->field($smtp, 'host') ?>
        <?= $form->field($smtp, 'port') ?>
        <?= $form->field($smtp, 'username') ?>
        <?= $form->field($smtp, 'password')->passwordInput() ?>

        <div class="form-group mt-3">
            <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-5">
        <?= Html::a('<i class="fas fa-envelope-open-text"></i> Test Configuration', ['/administration/test-smtp-configuration'], ['class'=>'btn btn-secondary']) ?>
    </div>
</div>
