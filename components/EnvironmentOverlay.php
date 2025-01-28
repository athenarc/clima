<?php

namespace app\components;

use Yii;
use yii\base\Widget;

class EnvironmentOverlay extends Widget
{

    public function run() {
        $environmentOverlayConfig = Yii::$app->params['environmentOverlay'] ?? [];
        if (!$environmentOverlayConfig) return '';
        $environmentType = strtolower($environmentOverlayConfig['environmentType'] ?? 'production');
        if ($environmentType === 'production') return '';

        $environmentName = $environmentOverlayConfig['environmentName'] ?? $environmentType;
        $environmentRefUrl = $environmentOverlayConfig['environmentRefUrl'] ?? '';

        return $this->render('environment-overlay', [
            'environmentType' => $environmentType,
            'environmentName' => $environmentName,
            'environmentRefUrl' => $environmentRefUrl
        ]);
    }
}