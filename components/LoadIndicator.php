<?php


namespace app\components;

use yii\base\Widget;

class LoadIndicator extends Widget
{
    protected $remaining;
    protected $exceeding;

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

    public function init()
    {
        $this->remaining = $this->total - $this->requested - $this->current;
        $this->exceeding = $this->current + $this->requested >= $this->total;
        $this->requested = $this->requested ?: 0;
        $this->bootstrap4CurrentClass = $this->bootstrap4CurrentClass ?: 'dark';
        $this->bootstrap4RequestedClass = $this->bootstrap4RequestedClass ?: 'dark';
        $this->exceedingMessage = $this->exceedingMessage ?: 'Insufficient resources';
    }

    public function run()
    {
        $currentRatio = $this->current / $this->total;

        /**
         * The core idea here is that at all times, the rendered widget should inform the user whether more load is
         * requested, regardless of the amount of it. Even for cases where the load is infinitesimal, a minimum-width
         * segment should be rendered to imply this information.
         *
         * At this point the minimum width is set to 1% of the load indicator. To account for this normalization,
         * the segment for the current load will always change accordingly:
         * - if current load is more than 99% and there is an amount of requested load, current-bar's width, will
         * drop to 99%, in order to accommodate for the requested-bar's normalization.
         * - if current load is infinitesimal as well (current_load<1%), then the same normalization for the
         * current-bar as well; both current-bar and requested-bar will have the minimum width of 1%.
         */
        $currentBarDrawRatio = $currentRatio < 0.99 ? ceil($currentRatio * 100) : floor($currentRatio * 100);

        $requestedBarDrawRatio = min(ceil(($this->requested / $this->total) * 100), 100 - $currentBarDrawRatio);

        $remainingBarDrawRatio = 100 - $currentBarDrawRatio - $requestedBarDrawRatio;

        return $this->render(
            'load-indicator',
            [
                'current' => $currentBarDrawRatio,
                'requested' => $requestedBarDrawRatio,
                'remaining' => $remainingBarDrawRatio,
                'exceeding' => $this->exceeding,
                'currentLabel' => $this->currentLabel,
                'requestedLabel' => $this->requestedLabel,
                'remainingLabel' => $this->remainingLabel,
                'currentMessage' => $this->currentMessage,
                'requestedMessage' => $this->requestedMessage,
                'remainingMessage' => $this->remainingMessage,
                'exceedingMessage' => $this->exceedingMessage,
                'bootstrap4CurrentClass' => $this->bootstrap4CurrentClass,
                'bootstrap4RequestedClass' => $this->bootstrap4RequestedClass
            ]
        );
    }
}
