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

<h4 class="mt-4"><b>Approved resources</b></h4>
<div class="row text-center">
    <div class="col text-left">
        <p class="h4"><strong>Jobs:</strong> <?= $initial_jobs ?><br>
            <medium class="text-muted">(remaining: <?= $remaining_jobs ?>)</medium></p>
    </div>
    <div class="col">
        <p class="h4"><strong>Cores:</strong> <?= $details->cores ?><br>
            <medium class="text-muted">(avg. use: <?= round($usage['cpu'] / 1000, 2) ?>)</medium></p>
    </div>
    <div class="col text-right">
        <p class="h4"><strong>RAM:</strong> <?= $details->ram ?> GB<br>
            <medium class="text-muted">(avg. use: <?= round($usage['ram'] / 1000, 2) ?> GB)</medium></p>
    </div>
</div>
<div class="row mb-4">
    <div class="col">
        <p class="h5">*These computational resources are available for the execution of containerized software packages and workflows.</p>
    </div>
</div>

<h4 class="mt-4"><b>Gaining access</b></h4>
<div class="row ">
    <div class="col">
        <p class="h4">To use the task execution infrastructure, an API key is required. You can manage your API keys on the page below:</p>
    </div>
</div>
<div class="row text-center">
      <div class="col text-center">
        <?= Html::a("API keys management", ['/project/token-management', 'id' => $id], ['class' => "btn btn-success btn-md $edit_button_class"]) ?>
      </div>
</div>


<h4 class="mt-4"><b>Using the resources</b></h4>
<div class="row ">
    <div class="col">
        <p class="h4">There are two ways to schedule task executions:</p>
    </div>
</div>

<div class="row p-4">
    <div class="col-md-6 border-end border-muted">
        <h5 class="mb-3"><strong>SCHEMA API</strong></h5>
        <p>Use our Job execution REST API to programmatically run computational analysis tasks.</p>
        <div class="text-center mt-4">
            <?= Html::a("SCHEMA API Swagger", Yii::$app->params["schema_api_url"] ?? "#", [
                'class' => "btn btn-success btn-md $access_button_class $ondemand_access_class"
            ]) ?>
        </div>
    </div>

    <div class="col-md-6 mt-4 mt-md-0">
        <h5 class="mb-3"><strong>HYPATIA Lab</strong></h5>
        <p>Use our Job execution User Interface (UI) to manually run computational analysis tasks.</p>
        <div class="text-center mt-4">
            <?= Html::a("HYPATIA Lab", Yii::$app->params["hypatia_lab_url"] ?? "#", [
                'class' => "btn btn-md text-white $access_button_class $ondemand_access_class",
                'style' => 'background-color: #e6833b',
                'target' => '_blank'
            ]) ?>
        </div>
    </div>
</div>

<h4 class="mt-4"><b>How to schedule on-demand batch computation tasks</b></h4>
<div class="row ">
    <div class="col">
        <p class="h4">Learn about the task execution infrastructure and how to run tasks in our documentation:</p>
    </div>
</div>
<div class="row text-center">
    <div class="col text-center">
        <?= Html::a("Documentation", Yii::$app->params["schema_api_docs_url"] ?? "#", [
            'class' => "btn btn-success btn-md $access_button_class $ondemand_access_class",
            'target' => '_blank'
        ]) ?>
    </div>
</div>

