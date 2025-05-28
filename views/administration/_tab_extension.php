<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Project;

/** @var $limits \app\models\ExtensionLimits[] indexed by user_type */
/** @var $projectTypes array */
/** @var $selectedProjectType int */
?>

<div class="mb-3">
    <?= Html::dropDownList('project_type', $selectedProjectType, $projectTypes, [
        'id' => 'project-type-dropdown',
        'class' => 'form-select',
        'onchange' => '
            const projectType = $(this).val();
            $.get("index.php?r=administration/load-tab&tab=extensions&projectType=" + projectType, function(data) {
                $("#config-tab-content").html(data);
            });
        ',
    ]) ?>
</div>

<?php $form = ActiveForm::begin([
    'action' => ['administration/save-extension-limits'],
    'method' => 'post',
]); ?>

<?= Html::hiddenInput('project_type', $selectedProjectType) ?>

<?php foreach (['bronze', 'silver', 'gold'] as $userType): ?>
    <?php $limit = $limits[$userType] ?? null; ?>
    <?php if ($limit): ?>
        <div class="border rounded p-3 mb-4">
            <h4><?= ucfirst($userType) ?> Limits</h4>

            <?= Html::hiddenInput("ExtensionLimits[{$limit->id}][id]", $limit->id) ?>
            <?= Html::hiddenInput("ExtensionLimits[{$limit->id}][user_type]", $limit->user_type) ?>
            <?= Html::hiddenInput("ExtensionLimits[{$limit->id}][project_type]", $limit->project_type) ?>

            <div class="row">
                <div class="col-md-6">
                    <?= Html::label('Max % Period', null, ['class' => 'form-label']) ?>
                    <?= Html::textInput("ExtensionLimits[{$limit->id}][max_percent]", $limit->max_percent, ['class' => 'form-control']) ?>
                </div>
                <div class="col-md-6">
                    <?= Html::label('Max Extensions', null, ['class' => 'form-label']) ?>
                    <?= Html::textInput("ExtensionLimits[{$limit->id}][max_extension]", $limit->max_extension, ['class' => 'form-control']) ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">No data for <?= $userType ?></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="form-group mt-3">
    <?= Html::submitButton('Save Extension Limits', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>
