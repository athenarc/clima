<?php

use app\components\Headers;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$exclamation_icon='<i class="fas fa-exclamation-triangle" style="color:orange" ></i>';
///* @var $this yii\web\View */
///* @var $model app\models\ServiceRequest */
/* @var $form ActiveForm */
/* @var $form_params array */
/* @var $email_verification \app\models\EmailVerification */
//echo Html::CssFile('@web/css/project/project-request.css');

//$this->registerJsFile('@web/js/project/project-request.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->title = "Email verification";
?>




<?php Headers::begin(); ?>
<?php echo Headers::widget(
    ['title' => 'Email verification',])
?>
<?php $this->registerJsFile('@web/js/components/email_verification.js'); ?>
<? Headers::end() ?>


<div class="row">
    <div class="col-md-12">
        <div class="alert alert-warning" role="alert">
            <td class="col-md-2 align-middle"><?=$exclamation_icon ?></td>
            Please provide an email address to facilitate the communication with the Hypatia support team. You can review our privacy policy <a href="https://hypatia.athenarc.gr/index.php?r=site%2Fprivacy" target="_blank">here</a>.<br>Note, that you will be asked to verify your email address.</br> </b>  </font>              
        </div>
    </div>
</div>


<?php $form = ActiveForm::begin($form_params); ?>
<?= $form->field($email_verification, 'email')->textInput(['placeholder'=>$email_verification->email])->label('Email address') ?>

<div class="col-md-11"><?= Html::submitButton('<i class="fas fa-check"></i> Submit', ['class' => 'btn btn-primary']) ?></div>
<?php ActiveForm::end(); ?>