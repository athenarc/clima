<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\components\MagicSearchBox;
use kartik\date\DatePicker;
use app\components\Headers;

/* @var $this yii\web\View */
/* @var $model app\models\ServiceRequest */
/* @var $form ActiveForm */
echo Html::CssFile('@web/css/project/project-request.css');


Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'Submit a new cold storage request',])
?>
<?Headers::end()?>
<?php

$participating_label="Participating users  <i class='fas fa-question-circle' title='Type 3 or more characters of the desired ELIXIR-AAI username to get suggestions'></i>";
$storage_label="Maximum allowed storage (in GBs) * <span class='limits-label'> [upper limits: $autoacceptlimits->storage (automatically accepted), $upperlimits->storage (with RAC review)] </span>";

if (!empty($errors))
{
    echo '<div class="alert alert-danger row" role="alert">';
    echo $errors;
    echo '</div>';

}
?>



<div class="ondemand_project">

<div class="row"><div class="col-md-12">* All fields marked with asterisk are mandatory</div></div>
    <?php $form = ActiveForm::begin($form_params); ?>
    <!-- <?= $form->errorSummary($project) ?> -->
    <!-- <?= $form->errorSummary($coldStorage) ?> -->
        <div class="row box">
            <div class="col-md-12">
                <h3>Project details</h3>
        
        <?= $form->field($project, 'name') ?>
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

        
        <?= $form->field($coldStorage, 'description')->textarea(['rows'=>6]) ?>
        <?= $form->field($project, 'duration')->hiddenInput()->label('') ?>
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


        
        <?= $form->field($coldStorage, 'storage')-> label($storage_label) ?>
        
        
    
        <div class="row">
            <div class="col-md-1"><?= Html::submitButton('<i class="fas fa-check"></i> Submit', ['class' => 'btn btn-primary']) ?></div>
            <div class="col-md-1"><?= Html::a('<i class="fas fa-times"></i> Cancel', ['/project/index'], ['class'=>'btn btn-default']) ?></div>
            <?php
            if (!$autoaccept_allowed)
            {
            ?>

            <div class="col-md-10 autoaccept_not_allowed"><i class="fa fa-asterisk" aria-hidden="true"></i> You already have an active cold storage project that was automatically accepted. <br />Your current project request will need to be examined and approved by the RAC.</div>
            
            <?php
            }
            ?>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- new_ondemand_project -->
