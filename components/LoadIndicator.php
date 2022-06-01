<?php


namespace app\components;

use yii\base\Widget;

class LoadIndicator extends Widget
{
    protected $remaining;
    protected $exceeding;
    protected $isPositive;

    public $current;
    public $total;
    public $requested;
    public $currentLabel;
    public $requestedLabel;
    public $remainingLabel;
    public $currentMessage;
    public $requestedMessage;
    public $remainingMessage;
    public $exceedingMessage;
    public $bootstrap4CurrentClass;
    public $bootstrap4RequestedClass;
    public $bootstrap4RequestedClassPositive;
    public $bootstrap4RequestedClassNegative;

    public function init()
    {
        $this->remaining = $this->total - $this->requested - $this->current;
        $this->exceeding = $this->current + $this->requested >= $this->total;
        $this->requested = $this->requested ?: 0;
        $this->isPositive = $this->requested > 0;
        $this->bootstrap4CurrentClass = $this->bootstrap4CurrentClass ?: 'dark';
        $this->bootstrap4RequestedClassPositive = $this->bootstrap4RequestedClassPositive ?: ($this->bootstrap4RequestedClass ?: 'danger');
        $this->bootstrap4RequestedClassNegative = $this->bootstrap4RequestedClassNegative ?: ($this->bootstrap4RequestedClass ?: 'success');
        $this->exceedingMessage = $this->exceedingMessage ?: 'Insufficient resources';
    }

    public function run()
    {
        $currentRatio = 100 * ($this->isPositive? $this->current : $this->current+$this->requested) / $this->total;
        $requestedRatio = 100 * abs($this->requested)/$this->total;
        $remainingRatio = 100 - $currentRatio - $requestedRatio;

        return $this->render(
            'load-indicator',
            [
                'current' => $currentRatio,
                'requested' => $requestedRatio,
                'remaining' => $remainingRatio,
                'exceeding' => $this->exceeding,
                'currentLabel' => $this->currentLabel,
                'requestedLabel' => $this->requestedLabel,
                'remainingLabel' => $this->remainingLabel,
                'currentMessage' => $this->currentMessage,
                'requestedMessage' => $this->requestedMessage,
                'remainingMessage' => $this->remainingMessage,
                'exceedingMessage' => $this->exceedingMessage,
                'bootstrap4CurrentClass' => $this->bootstrap4CurrentClass,
                'bootstrap4RequestedClass' => $this->isPositive? $this->bootstrap4RequestedClassPositive : $this->bootstrap4RequestedClassNegative
            ]
        );
    }
}
