<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\components\MagicSearchBox;
use kartik\date\DatePicker;
use app\components\Headers;

$this->title = "Edit storage volume request";

/* @var $this yii\web\View */
/* @var $model app\models\ServiceRequest */
/* @var $form ActiveForm */
echo Html::CssFile('@web/css/project/project-request.css');
$this->registerJsFile('@web/js/project/project-request.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$types=['hot'=>'Hot'];
$vm_types=[1=>'24/7 service', 2=>'On-demand computation machines'];


$participating_label="Participating users  <i class='fas fa-question-circle' title='Type 3 or more characters of the desired ELIXIR-AAI username to get suggestions'></i>";

if($autoacceptlimits->storage==$upperlimits->storage)
{
    $storage_label="Maximum allowed storage (in GBs) * <span class='limits-label'> [upper limits: $upperlimits->storage] </span>";
}
else
{
    $storage_label="Maximum allowed storage (in GBs) * <span class='limits-label'> [upper limits: $autoacceptlimits->storage (automatically accepted), $upperlimits->storage (with review)] </span>";
}

if (!empty($errors))
{
    echo '<div class="alert alert-danger row" role="alert">';
    echo $errors;
    echo '</div>';

}

/*
 * If multiple volumes allowed
 * show the dropdown
 */
if ($details->vm_type==1)
{
    $multClass='hidden';
}
else
{
    $multClass='';
}

Headers::begin() ?>
<?php echo Headers::widget(
    ['title'=>'Edit storage volume request',])
?>
<?Headers::end()?>




<div class="cold">

    <div class="row"><div class="col-md-12">* All fields marked with asterisk are mandatory</div></div>
    <?php $form = ActiveForm::begin($form_params); ?>
    <?= $form->errorSummary($project) ?>
    <?= $form->errorSummary($details) ?>
    <div class="row box">
        <div class="col-md-12">
            <h3>Project details</h3>

            <?= $form->field($project, 'name')->textInput(['readonly' => true, 'value' =>$project['name']]) ?>
            <div style="margin-bottom: 20px;">
                <?php

                $startDate = date('Y-m-d'); // Current date
                $endDate = date('Y-m-d', strtotime($project->end_date . " +$maxExtensionDays days"));
                if ($isModerator){
                    echo '<label>Project end date *</label>';
                    echo DatePicker::widget([
                        'model' => $project,
                        'attribute' => 'end_date',
                        'options' => ['readonly' => false, 'disabled' => false],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-m-d',
                        ],
                    ]);
                }
                else{
                    if ($extension_count <= $max_extension) {
                        if ($exceed_limits == 0) {
                            echo '<label>Project end date *</label>';
                            echo DatePicker::widget([
                                'model' => $project,
                                'attribute' => 'end_date',
                                'options' => ['readonly' => true], // Prevent direct typing
                                'pluginOptions' => [
                                    'endDate' => $endDate,
                                    'autoclose' => true,
                                    'format' => 'yyyy-m-d'
                                ]
                            ]);
                        } elseif ($exceed_limits == 1) {
                            echo '<label>Project end date *</label>';
                            echo DatePicker::widget([
                                'model' => $project,
                                'attribute' => 'end_date',
                                'pluginOptions' => [
                                    'startDate' => $startDate, // Start from today
                                    'endDate' => $endDate,    // Restrict to allowed extension range
                                    'autoclose' => true,
                                    'format' => 'yyyy-m-d'
                                ]
                            ]);
                        }
                    } else {
                        echo '<label>Project End Date</label>';
                        echo DatePicker::widget([
                            'model' => $project,
                            'attribute' => 'end_date',
                            'options' => ['readonly' => true, 'disabled' => true], // Fully disable modification
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-m-d'
                            ]
                        ]);
                        echo '<div class="alert alert-danger">You have reached the maximum number of extensions allowed.</div>';
                    }

                }
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


            <?= $form->field($details, 'description')->textarea(['rows'=>6]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <h3>Requested resources</h3>
        </div>
    </div>
    <div class="row">&nbsp;</div>


    <?= $form->field($details, 'type')->dropDownList($types, ['disabled'=>$volume_exists])->label('Volume type') ?>
    <?= $form->field($details, 'vm_type')->dropDownList($vm_types, ['disabled'=>$volume_exists])->label('I want to use this volume for:') ?>
    <?= $form->field($details, 'storage')->textInput(['disabled'=>$volume_exists])-> label($storage_label) ?>
    <span class="<?=$multClass?> num_of_volumes_dropdown"><?= $form->field($details, 'num_of_volumes')->dropDownList($num_vms_dropdown,['disabled'=>$volume_exists]) ?></span>
    <div class="col-md-10 autoaccept_not_allowed <?=(!$volume_exists) ? 'hidden' : ''?>"><i class="fa fa-asterisk" aria-hidden="true"></i> In order to change the size and type of the volume(s), you will need to delete them and try again.</div>



    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-check"></i> Submit', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<i class="fas fa-times"></i> Cancel', ['/project/index'], ['class'=>'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>