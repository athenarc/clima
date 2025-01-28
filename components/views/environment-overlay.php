<?php

/* @var $environmentType string */
/* @var $environmentName string */
/* @var $environmentRefUrl string */

use yii\helpers\Html;

echo Html::cssFile('@web/css/components/environment-overlay.css');
?>

<?php
$staticΟverlayClasses = 'environment-overlay';
$dynamicOverlayClasses = '';

if ($environmentType === 'test') {
    $dynamicOverlayClasses .= ' environment-overlay-test';
}
else if ($environmentType === 'dev') {
    $dynamicOverlayClasses .= ' environment-overlay-dev';
}

$overlayClasses = $staticΟverlayClasses . ' ' . $dynamicOverlayClasses;
?>

<div class="<?= $overlayClasses ?>">
    <div>
    <?php
    if ($environmentRefUrl) {
    ?>
        <h4><a target="_blank" href="<?= $environmentRefUrl ?>"><?= $environmentName ?></a></h4>
    <?php
    }
    else {
    ?>
        <h3><?= $environmentName ?></h3>
    <?php
    }?>
    </div>
</div>
