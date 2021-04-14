<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\components\MagicSearchBox;
use kartik\date\DatePicker;
use app\components\Headers;



echo Html::CssFile('@web/css/project/project-request.css');
$this->registerJsFile('@web/js/project/project-request.js', ['depends' => [\yii\web\JqueryAsset::className()]]);


$this->title="Request a new on-demand computation machine";

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'Request a new on-demand computation machine',])
?>
<?Headers::end()?>

<?php

$participating_label="Participating users  <i class='fas fa-question-circle' title='Type 3 or more characters of the desired ELIXIR-AAI username to get suggestions'></i>";
$storage_label="Additional storage (in GBs)" ;
$flavour_label="Select VM configuration " ;

if (!empty($errors))
{
    echo '<div class="alert alert-danger row" role="alert">';
    echo $errors;
    echo '</div>';

}



?>



<div class="new_service_request">

<div class="row"> <div class="col-md-12">* All fields marked with asterisk are mandatory</div></div>

    <?php $form = ActiveForm::begin($form_params); ?>

        <div class="row box">
            <div class="col-md-6">
                <h3>Project details</h3>
            
        
        <?= $form->field($project, 'name') ?>
        
        <div style="margin-bottom: 20px;">
        <?php echo '<label>  Project end date *  </label>';
            echo DatePicker::widget([
            'model' => $project, 
            'attribute' => 'end_date',
            'options' => ['placeholder' => 'Enter date'],
            'pluginOptions' => [
            'autoclose'=>true,
            'padding-top'=>"500px;",
            'endDate'=>"+30d"
            ]
        ]);?>
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
                <?= $form->field($service, 'name') ?>
                <?= $form->field($service, 'version') ?>
                <?= $form->field($service, 'description')->textarea(['rows'=>6]); ?>
                <?= $form->field($service, 'url') ?>
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

        <?= $form->field($service,'flavour')->dropDownList($service->flavours)->label($flavour_label)?>
        <?= $form->field($service, 'storage')->label($storage_label) ?>

    
        <div class="row">
            <div class="col-md-1"><?= Html::submitButton('<i class="fas fa-check"></i> Submit', ['class' => 'btn btn-primary']) ?></div>
            <div class="col-md-1"><?= Html::a('<i class="fas fa-times"></i> Cancel', ['/project/index'], ['class'=>'btn btn-default']) ?></div>
        </div>
    <?php ActiveForm::end(); ?>

</div>
