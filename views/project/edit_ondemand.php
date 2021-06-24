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
$this->title="Edit on-demand computation project request";

$participating_label="Participating users  <i class='fas fa-question-circle' title='Type 3 or more characters of the desired ELIXIR-AAI username to get suggestions'></i>";
$cancel_icon='<i class="fas fa-times"></i>';

if($autoacceptlimits->ram==$upperlimits->ram)
{
    $ram_label= "Maximum allowed memory per job (in GBs) * <span class=limits-label> [upper limits: $upperlimits->ram] </span>";
}
else
{
    $ram_label= "Maximum allowed memory per job (in GBs) * <span class=limits-label> [upper limits: $autoacceptlimits->ram (automatically accepted),  $upperlimits->ram (with review)] </span>";
}

if($autoacceptlimits->num_of_jobs==$upperlimits->num_of_jobs)
{
    $jobs_label="Maximum number of jobs in project's lifetime * <span class=limits-label> [upper limits: $upperlimits->num_of_jobs] </span>";
}
else
{
    $jobs_label="Maximum number of jobs in project's lifetime * <span class=limits-label> [upper limits:  $autoacceptlimits->num_of_jobs (automatically accepted),  $upperlimits->num_of_jobs (with review)] </span>";
}

if($autoacceptlimits->cores==$upperlimits->cores)
{
    $cores_label= "Available cores per job * <span class=limits-label> [upper limits: $autoacceptlimits->cores] </span>" ;
}
else
{
    $cores_label= "Available cores per job * <span class=limits-label> [upper limits: $autoacceptlimits->cores (automatically accepted), $upperlimits->cores (with review)] </span>" ;
}






if (!empty($errors))
{
    echo '<div class="alert alert-danger row" role="alert">';
    echo $errors;
    echo '</div>';

}
Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'Edit on-demand computation project request',])
?>
<?Headers::end()?>



<div class="ondemand_project">

<div class="row"><div class="col-md-12"> * All fields marked with asterisk are mandatory</div></div>

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
            'pluginOptions' => [
            'autoclose'=>true,
            'format'=>'yyyy-m-d'
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

        

        </div>
            <div class="col-md-6">
                <h3>Analysis information details</h3>
            
        
        <?= $form->field($details, 'analysis_type') ?>
        <?= $form->field($details, 'maturity')->dropDownList($maturities)  ?>
        <?= $form->field($details, 'description')->textarea(['rows'=>6]); ?>
        <?= $form->field($details, 'containerized')->checkbox(['checked'=>true]); ?>
        
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

                <?= $form->field($details, "num_of_jobs")->label($jobs_label) ?>
                <?= $form->field($details, 'cores')->label($cores_label) ?>
                <?= $form->field($details, 'ram')->label($ram_label) ?>
        
        
    
        <div class="form-group">
            <?= Html::submitButton('<i class="fas fa-check"></i> Submit', ['class' => 'btn btn-primary']) ?>
            <?= Html::a("$cancel_icon Cancel", ['/project/index'], ['class'=>'btn btn-default']) ?>
        </div>
    <?php ActiveForm::end(); ?>
</div>