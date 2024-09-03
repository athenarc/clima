<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;
use app\components\Headers;
use app\models\Token;
use yii\helpers\Url;

echo Html::CssFile('@web/css/project/tokens.css');
$this->registerJsFile('@web/js/project/index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$back_icon = '';
$access_icon = '<i class="fas fa-external-link-square-alt"></i>';
$update_icon = '<i class="fas fa-pencil-alt"></i>';
$back_link = '/project/index';
$delete_icon = '<i class="fas fa-times"></i>';
$new_icon = '<i class="fas fa-plus-circle"></i>';
$exclamation_icon = '<i class="fas fa-exclamation-triangle" style="color:orange" title="The Vm belongs to an expired project"></i>';
$mode = 0;
$edit_button_class = '';
$access_button_class = '';
$ondemand_access_class = '';


Headers::begin() ?>
<?php
echo Headers::widget(
    ['title' => 'Project management ' . "</br>",
        'subtitle' => $project->name,
        'buttons' =>
            [
                ['fontawesome_class' => $back_icon, 'name' => 'Back', 'action' => [$back_link], 'type' => 'a',
                    'options' => ['class' => 'btn btn-default', 'style' => 'width: 100px; color:grey ']]
            ],
    ]);
?>
<? Headers::end() ?>
<br>
<h4><b> Approved resources</b></h4>
<div id="containerIntro">
    <h4><b> &emsp; Jobs: &nbsp;</b></h4>
    <p><?= $initial_jobs ?>&nbsp;(remaining: &nbsp;<?= $remaining_jobs ?>)</p> <br>
    <h4><b> &emsp; Cores: &nbsp;</b></h4>
    <p><?= $details->cores ?>&nbsp;(average use: &nbsp;<?= round($usage['cpu'] / 1000, 2) ?>)</p><br>
    <h4><b> &emsp; Ram: &nbsp;</b></h4>
    <p><?= $details->ram ?>GB&nbsp;(average use: &nbsp;<?= round($usage['ram'] / 1000, 2) ?>GB)</p><br><br>

    <p>&nbsp;*These computational resources are available for the execution of containerized software packages and
        workflows.</p>
</div>

<h4><b>Gaining access</b></h4>
<p>
    In order to use the task execution infrastructure an API key is required. You can manage your API keys in the
    page:
</p>
<div class="text-center">
    <?= Html::a("API keys management", ['/project/token-management', 'id' => $id], ['class' => "btn btn-success btn-md $edit_button_class"]) ?>
</div>

<br>

<h4><b> Using the resources</b></h4>
<p>&emsp;&emsp;There are two ways to schedule task executions:</p> <br>
<div class="p-4 row">
    <div class="col-6 border-right border-muted">
        <h5><b>SCHEMA api</b></h5>
        <p>
            Use our Job execution REST API to programmatically run computational analysis tasks
        </p>
        <div class="text-center">
            <?= Html::a("SCHEMA api Swagger", Yii::$app->params["schema_api_url"] ?? "#", ['class' => "btn btn-success btn-md $access_button_class $ondemand_access_class"]) ?>
        </div>
    </div>
    <div class="col-6">
        <h5><b>HYPATIA lab</b></h5>
        <p>
            Use our Job execution User Interface (UI) to manually run
            computational analysis tasks
        </p>
        <div class="text-center">
            <?= Html::a("HYPATIA lab", Yii::$app->params["hypatia_lab_url"] ?? "#", ['class' => "btn btn-md $access_button_class $ondemand_access_class text-white", 'style' => 'background-color: #e6833b', 'target' => '_blank']) ?>
        </div>
    </div>
</div>

<br>

<h4><b>How to schedule on-demand batch computation tasks</b></h4>
<p>
    Learn about the task execution infrastructure and how to run tasks on the provided documentation:
<div class="text-center">
    <?= Html::a("Documentation", Yii::$app->params["schema_api_docs_url"] ?? "#", ['class' => "btn btn-success btn-md $access_button_class $ondemand_access_class", 'target' => '_blank']) ?>
</div>
</p>