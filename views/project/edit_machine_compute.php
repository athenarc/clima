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
$this->registerJsFile('@web/js/project/project-request.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->title="Edit on-demand computation machines request";


$participating_label="Participating users  <i class='fas fa-question-circle' title='Type 3 or more characters of the desired ELIXIR-AAI username to get suggestions'></i>";
$storage_label="Additional storage (in GBs)" ;
$flavour_label="Select VM configuration";

if (!empty($errors))
{
    echo '<div class="alert alert-danger row" role="alert">';
    echo $errors;
    echo '</div>';

}

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'Edit on-demand computation machines request',])
?>
<?Headers::end()?>



<div class="new_service_request">

<div class="row"> <div class="col-md-12">* All fields marked with asterisk are mandatory</div></div>

    <?php $form = ActiveForm::begin($form_params); ?>
    <?= $form->errorSummary($project) ?>
    <?= $form->errorSummary($details) ?>
        <div class="row box">
            <div class="col-md-6">
                <h3>Project details</h3>
                <?= $form->field($project, 'name')->textInput(['readonly' => true, 'value' =>$project['name']]) ?>
                 <div style="margin-bottom: 20px;">
                <?php echo '<label>  Project end date *  </label>';
                      if($exceed_limits == 0){echo  $form->field($project, 'end_date')->widget(DatePicker::className(),[
                        'options' => array('readonly' => 'readonly'),
                        'pluginOptions' => [
                        'autoclose'=>true,
                        'format'=>'yyyy-m-d',
                        'endDate'=>'+'.(90-$interval)."d"
                        ]
                    ])->label("");}
                    if($exceed_limits == 1){echo  $form->field($project, 'end_date')->widget(DatePicker::className(),[
                        'options' => array('readonly' => 'readonly'),
                        'pluginOptions' => [
                        'autoclose'=>true,
                        'format'=>'yyyy-m-d'
                        ]
                    ])->label("");}
                ?>
                </div>
        <?= $form->field($project, 'user_num') ?>

        <?= Html::label($participating_label, 'user_search_box', ['class'=>'blue-label']) ?>
        <br/>
        <?= MagicSearchBox::widget(
            ['min_char_to_start' => Yii::$app->params["minUsernameLength"] ?? 1,
             'expansion' => 'right', 
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
        <h3> Analysis description</h3>
        <?= $form->field($details, 'description')->textarea(['rows'=>6])->label(""); ?>
    </div>
 </div>   
        <div class="row">
            <div class="col-md-12">
                <h3>Requested resources</h3>
            </div>
        </div>
        <div class="row">&nbsp;</div>

      
            <?= $form->field($details,'flavour')->dropDownList($details->flavours, ['disabled'=>$vm_exists])->label($flavour_label)?>
            <?= $form->field($details,'num_of_vms')->dropDownList($num_vms_dropdown,['disabled'=>$vm_exists])?>
           
        
        <div class="form-group">
            <?= Html::submitButton('<i class="fas fa-check"></i> Submit', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fas fa-times"></i> Cancel', ['/project/index'], ['class'=>'btn btn-default']) ?>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- new_service_request -->
