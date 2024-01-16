<?php

use app\components\Headers;
use yii\helpers\Html;
use yii\widgets\ActiveForm;


///* @var $this yii\web\View */
///* @var $model app\models\ServiceRequest */
/* @var $form ActiveForm */
/* @var $form_params array */
/* @var $email_verification \app\models\EmailVerification */
//echo Html::CssFile('@web/css/project/project-request.css');

//$this->registerJsFile('@web/js/project/project-request.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->title = "Token: ".$token;
?>




<?php Headers::begin(); ?>
<?php echo Headers::widget(
    ['title' => 'Token: '.$token,])
?>
<?php $this->registerJsFile('@web/js/components/email_verification.js'); ?>

<? Headers::end() ?>
