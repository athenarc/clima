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
        <?= $form->errorSummary($project) ?>
        <?= $form->errorSummary($service) ?>
        <div class="row box">
            <div class="col-md-6">
                <h3>Project details</h3>
            
        
        <?= $form->field($project, 'name') ?>
        
        <div style="margin-bottom: 20px;">
        <?php echo '<label>  Project end date *  </label>';
          echo  $form->field($project, 'end_date')->widget(DatePicker::className(),[
            'pluginOptions' => [
            'autoclose'=>true,
            'format'=>'yyyy-m-d',
            'endDate'=>'+30d'
            ]
        ])->label("");
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
        
        </div>
        
            <div class="col-md-6">
                <h3> Analysis description *</h3>
                <?= $form->field($service, 'description')->textarea(['rows'=>6])->label(""); ?>
            </div>
        </div>   
        <div class="row">
            <div class="col-md-12">
                <h3>Requested resources</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h5>(Note that prototype resource limits are not indicative of the limits to be applied to the final, production-ready <?=$name=Yii::$app->params['name']?>.)</h5>
            </div>
        </div>
        <div class="row">&nbsp;</div>

        <?= $form->field($service,'flavour')->dropDownList($service->flavours)->label($flavour_label)?>
        <?= $form->field($service,'num_of_vms')->dropDownList($num_vms_dropdown)?>

        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning" role="alert">
                  Currently HYPATIA does not posess a backup service. To ensure the safety of your data, you should backup your data in a source outside HYPATIA.
                </div>
            </div>
        </div>    
        <div class="row">
            <div class="col-md-1"><?= Html::submitButton('<i class="fas fa-check"></i> Submit', ['class' => 'btn btn-primary']) ?></div>
            <div class="col-md-1"><?= Html::a('<i class="fas fa-times"></i> Cancel', ['/project/index'], ['class'=>'btn btn-default']) ?></div>
        </div>
        
    <?php ActiveForm::end(); ?>

</div>
