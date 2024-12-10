<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\components\MagicSearchBox;
use kartik\date\DatePicker;
use app\components\Headers;



echo Html::CssFile('@web/css/project/tokens.css');
$this->registerJsFile('@web/js/project/project-request.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
echo Html::CssFile('@web/css/project/project-request.css');
$this->registerJsFile('@web/js/project/project-request.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$sub=$project->name;
if ($mode==0){
    $this->title='API keys creation'.'<br>';
}else {
    $this->title='API keys modification'.'<br>';

}

$exclamation_icon='<i class="fas fa-exclamation-triangle" style="color:orange" ></i>';

?>


<?php
Headers::begin() ?>
<?php echo Headers::widget(
        ['title'=>$this->title,
        'subtitle'=>$sub])

?>
<?Headers::end()?>
<br>
<?php
if ($mode == 0){
?>
<dd>Please provide the specifications for a new API keys to be created:</dd>
<div class="row">&nbsp;</div>


<?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->label('Name  <span class=limits-label>(select one that would be easy to remember, otherwise 8 first characters of the api keys will be selected)</span>')->textarea([ 'style'=>'width: 600px; height: 40px; resize:none', 'rows'=>1]) ?>

    
    <div style="margin-bottom: 20px;">
    <?php echo '<label>  Expiration date <span class=limits-label>(if not selected, project expiration date will apply)</span> </label>';
            echo DatePicker::widget([
            'model' => $model, 
            'attribute' => 'expiration_date',
            'options' => ['placeholder' => 'Enter Date', 'style'=>'width: 523px; height: 40px; resize:none'],
            'pluginOptions' => [
            'autoclose'=>true,
            'format'=>'yyyy-mm-dd'
            ]
        ]);?>
        </div>

    <div class="form-group">
            <div class="col-md-1"><?= Html::submitButton(' Submit', ['class' => 'btn btn-success', 'style'=>'width:90px']) ?></div>
            <div class="col-md-1"><?= Html::a(' Cancel', ['/project/token-management','id'=>$requestId], ['class'=>'btn btn-default', 'style'=>'width:90px; color:grey;']) ?></div>
    </div>

<?php ActiveForm::end(); ?>
<br>
<br/>
<br>
<br/>
<div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning" role="alert">
                <td class="col-md-2 align-middle"><?=$exclamation_icon ?></td>
                Please keep in mind that for security reasons HYPATIA stores only a description of your assigned API keys and not your exact API keys.
                 </div>
            </div>
        </div>

<?php 

} else {

?>
<div class="row">&nbsp;</div>


<?php $form = ActiveForm::begin(); ?>
<?= $form->errorSummary($model) ?>

    <?= $form->field($model, 'name')->textInput(['value' => $title, 'style'=>'width: 600px; height: 40px; resize:none', 'rows'=>1]) ?>

    <div style="margin-bottom: 20px;">
    <?php echo '<label>  API keys expiration date  </label>';
            echo DatePicker::widget([
            'model' => $model, 
            'attribute' => 'expiration_date',
            'options' => ['placeholder' => $exp_date, 'style'=>'width: 523px; height: 40px; resize:none'],
            'pluginOptions' => [
            'autoclose'=>true,
            'format'=>'yyyy-mm-dd'
            ]
        ]);?>
        </div>

    <div class="form-group">
            <div class="col-md-1"><?= Html::submitButton(' Submit', ['class' => 'btn btn-success', 'style'=>'width:90px']) ?></div>
            <div class="col-md-1"><?= Html::a(' Cancel', ['/project/token-management','id'=>$requestId], ['class'=>'btn btn-default', 'style'=>'width:90px; color:grey;']) ?></div>
    </div>
<div class="row"><div class="col-md-12"><?= Html::errorSummary($model, ['encode' => false]) ?></div></div>
<?php ActiveForm::end(); ?>


<br>
<br/>
<div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning" role="alert">
                <td class="col-md-2 align-middle"><?=$exclamation_icon ?></td>
                Please keep in mind that for security reasons HYPATIA stores only a description of your assigned API keys and not your exact API keys. <b><font color="black">Thus, you are responsible for
storing and retriving your API keys. </b>  </font>
                 </div>
            </div>
        </div>

<?php
}
?>
