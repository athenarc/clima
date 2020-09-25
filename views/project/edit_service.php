<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\components\MagicSearchBox;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\ServiceRequest */
/* @var $form ActiveForm */
echo Html::CssFile('@web/css/project/project-request.css');
$this->title="Edit 24/7 service project request";

$trl_label=" Technology readiness level (<a href='https://en.wikipedia.org/wiki/Technology_readiness_level' target='_blank'>TRL</a>)";
$participating_label="Participating users  <i class='fas fa-question-circle' title='Type 3 or more characters of the desired ELIXIR-AAI username to get suggestions'></i>";
$storage_label="Additional storage (in GBs) <span class=limits-label> [upper limits: $autoacceptlimits->storage (automatically accepted),  $upperlimits->storage (with RAC review)] </span>" ;
$flavour_label="Select VM configuration * <span class=limits-label> [upper limits: $autoacceptlimits->cores cores/$autoacceptlimits->ram GBs RAM (automatically accepted), $upperlimits->cores cores/$upperlimits->ram GBs RAM (with RAC review)] </span>" ;

if (!empty($errors))
{
    echo '<div class="alert alert-danger row" role="alert">';
    echo $errors;
    echo '</div>';

}

?>


<div class="row"><div class="col-md-12 headers"><?= Html::encode($this->title) ?></div></div>
<div class="new_service_request">

<div class="row"> <div class="col-md-12">* All fields marked with asterisk are mandatory</div></div>

    <?php $form = ActiveForm::begin($form_params); ?>
    <?= $form->errorSummary($project) ?>
    <?= $form->errorSummary($details) ?>
        <div class="row box">
            <div class="col-md-6">
                <h3>Project details</h3>
                <?= $form->field($project, 'name') ?>
                 <div style="margin-bottom: 20px;">
                <?php echo '<label>  Project end date *  </label>';
                    echo DatePicker::widget([
                    'model' => $project, 
                    'attribute' => 'end_date',
                    'pluginOptions' => [
                    'autoclose'=>true,
                    
                    ]
                ]);
                ?>
                </div>
        <?= $form->field($project, 'user_num') ?>

        <?= Html::label($participating_label, 'user_search_box', ['class'=>'blue-label']) ?>
        <br/>
        <?= MagicSearchBox::widget(
            ['min_char_to_start' => 3, 
             'expansion' => 'both', 
             'suggestions_num' => 5, 
             'html_params' => [ 'id' => 'user_search_box', 
             'name'=>'participants', 
             'class'=>'form-control blue-rounded-textbox'],
             'ajax_action' => Url::toRoute('project/auto-complete-names'),
             'participating' => $participating,
            ]);
        ?>
        <br />
        
        <?= $form->field($project, 'backup_services')->checkbox() ?>
        </div>
        
            <div class="col-md-6">
                <h3> Service details</h3>
            
        
        <?= $form->field($details, 'trl')->dropDownList($trls)->label($trl_label) ?>

        <?= $form->field($details, 'name') ?>
        <?= $form->field($details, 'version') ?>
        <?= $form->field($details, 'description')->textarea(['rows'=>6]); ?>
        <?= $form->field($details, 'url') ?>
    </div>
 </div>   
        <div class="row">
            <div class="col-md-12">
                <h3>Requested resources</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h5>(Note that prototype resource limits are not indicative of the limits to be applied to the final, production-ready EG-CI.)</h5>
            </div>
        </div>
        <div class="row">&nbsp;</div>

        <?php 
        if($vm_exists==1)
        {?>
            
                <?= $form->field($details,'flavour')->dropDownList($details->flavours, ['disabled'=>true])->label($flavour_label)?>
                <?= $form->field($details, 'storage')->textInput(['disabled'=>true])->label($storage_label) ?>
            
        <?php
        }
        else
        {?>
            <?= $form->field($details,'flavour')->dropDownList($details->flavours)->label($flavour_label)?>
            <?= $form->field($details, 'storage')->label($storage_label) ?>
        <?php
        }?>
        <div class="form-group">
            <?= Html::submitButton('<i class="fas fa-check"></i> Submit', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fas fa-times"></i> Cancel', ['/project/index'], ['class'=>'btn btn-default']) ?>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- new_service_request -->
