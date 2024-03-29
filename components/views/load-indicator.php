<?php

use yii\bootstrap4\Progress;
use yii\helpers\Html;

echo Html::cssFile('@web/css/components/load-indicator.css');

$this->registerJsFile('@web/js/components/load-indicator.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>

<div>
    <div class="progress load-indicator">
        <div class="progress-bar bg-<?=$bootstrap4CurrentClass?>" role="progressbar" data-container="body" data-toggle="tooltip" title="<?= $currentMessage ?>"
             style="width: <?= $current ?>%;min-width: <?=($current<=0)?0:2?>px" aria-valuenow="<?= $current ?>" aria-valuemin="0" aria-valuemax="100">
            <span><?= $currentLabel ?></span></div>
        <div class="progress-bar bg-<?=$bootstrap4RequestedClass?> progress-bar-striped progress-bar-animated" role="progressbar"
             data-container="body" data-toggle="tooltip" title="<?= $requestedMessage ?>" style="width: <?= $requested ?>%;min-width: <?=($requested<=0)?0:2?>px"
             aria-valuenow="<?= $requested ?>" aria-valuemin="0" aria-valuemax="100"><span><?= $requestedLabel ?></span>
        </div>
        <div class="progress-bar bg-transparent text-dark" role="progressbar" data-toggle="tooltip" data-container="body"
             title="<?= $remainingMessage ?>" style="width: <?= $remaining ?>%;min-width: <?=($current<=0)?0:2?>px" aria-valuenow="<?= $remaining ?>"
             aria-valuemin="0" aria-valuemax="100"><span><?= $remainingLabel ?></span></div>
    </div>
    <?php if ($exceeding) {?>
    <div class="invalid-feedback text-left d-block text-break">
        <i class="fas fa-exclamation"></i> <?=$exceedingMessage?>
    </div>
    <?php }?>
</div>
