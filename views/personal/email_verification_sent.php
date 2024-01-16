
<?php

use app\components\Headers;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$button_class = '';
$this->title = "Email verification";
?>




<?php Headers::begin(); ?>
<?php $this->registerJsFile('@web/js/components/email_verification.js'); ?>
<? Headers::end() ?>
<center>
<div class="col-md-12">
            <div class="alert alert-success" role="alert">
            We've sent you a verification email at: <b><?=$email?></b> <br>
            Please visit your email provider to verify your email (check your <b>spam folder</b> too).
            </div>
        </div>

</center>
<br>
<div style="float: left; width: 49.5%;text-align:right">
<?=Html::a("Resend verification email",['/personal/email-verification-sent', 'email'=>$email, 'resend'=>1],['class'=>"btn btn-success btn-md $button_class", 'style'=>'width:270px; height:60px; text-align: center; line-height: 45px; font-size:20px; '])?>
</div>
<div style="float: right; width: 49.5%;text-align:left">
<?=Html::a("Change email address",['/personal/email-verification'],['class'=>"btn btn btn-secondary btn-md $button_class", 'style'=>'width:270px; height:60px; text-align: center; line-height: 45px; font-size:20px;'])?>
</div>



