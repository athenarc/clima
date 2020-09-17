<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ContactForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;

$this->title = 'Send notification to all users';
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>
        <div class="row">
            <div class="col-md-12">

                <?php $form = ActiveForm::begin($form_params); ?>

                    
                    <?= $form->field($notification, 'type')->dropdownList($notification->typeDropdown) ?>

                    <?= $form->field($notification, 'urlType')->dropdownList($notification->urlDropdown) ?>

                    <?= $form->field($notification, 'url')->textInput() ?>

                    <?= $form->field($notification, 'message')->textarea(['rows'=>6]) ?>


                    <div class="form-group">
                        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'message-button']) ?>
                    </div>

                <?php ActiveForm::end(); ?>

            </div>
        </div>
</div>
