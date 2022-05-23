<?php

namespace app\components;

use app\models\ProjectRequest;

class ContextualLoadIndicator extends LoadIndicator
{
    const MEMORY=0;
    const CPU=1;
    const IP=2;

    public $context;

    public function init()
    {
        $this->remaining = $this->total - $this->requested - $this->current;

        // Will not fill any property that has been explicitly defined
        $this->currentLabel = $this->currentLabel ?: ContextualLoadIndicator::contextualize($this->current, $this->context);
        $this->requestedLabel = $this->requestedLabel ?: ContextualLoadIndicator::contextualize($this->requested, $this->context);
        $this->remainingLabel = $this->remainingLabel ?: ContextualLoadIndicator::contextualize($this->remaining, $this->context);
        $this->currentMessage = $this->currentMessage ?: $this->currentLabel . ' reserved';
        $this->requestedMessage = $this->requestedMessage ?: $this->requestedLabel . ' requested';
        $this->remainingMessage = $this->remainingMessage ?: $this->remainingLabel . ' remaining';

        parent::init();
    }

    private static function contextualize($value, $context) {
        switch ($context) {
            case self::MEMORY:
                return self::toMemory($value);
            case self::CPU:
                return self::toCpu($value);
            case self::IP:
                return self::toIps($value);
            default:
                return $value;
        }
    }

    private static function toMemory($GBytes)
    {
        if ($GBytes / 1000 >= 1) return round($GBytes / 1000, 1) . ' TBs';
        return $GBytes . ' GBs';
    }

    private static function toCpu($numCpus){
        return $numCpus.' cores';
    }

    private static function toIps($numIps){
        return $numIps.' IPs';
    }
}