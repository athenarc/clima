<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Policy Update';
?>

<div class="policy-acceptance" style="text-align: center">
    <h2><?= Html::encode($this->title) ?></h2>
    <p style="text-align: center;">Please review the updated HYPATIA Resource Allocation and Access Policy <a href="/hypatias-policy.pdf">here</a>.</p>
    <p style="text-align: center;">Click the button below to accept the policy and ensure uninterrupted access to the platform.</p>
    <?php $form = ActiveForm::begin(); ?>
    <div class="form-group" style="text-align: center; width: 100%; max-width: 300px; margin: 0 auto;">
        <?= Html::submitButton('I accept', ['class' => 'btn btn-primary', 'style' => 'width: 100%; font-size: 15px;']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
