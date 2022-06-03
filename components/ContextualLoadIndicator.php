<?php

namespace app\components;

use app\models\ProjectRequest;

class ContextualLoadIndicator extends LoadIndicator
{
    public $contextManager;
    public $currentMessageSuffix;
    public $requestedMessageSuffix;
    public $remainingMessageSuffix;

    public function init()
    {
        $this->remaining = $this->total - $this->requested - $this->current;

        // Will not fill any property that has been explicitly defined

        if (isset($this->contextManager)) {
            $contextManager = $this->contextManager;
            $this->currentLabel = $this->currentLabel ?: $contextManager($this->current);
            $this->requestedLabel = $this->requestedLabel ?: $contextManager($this->requested);
            $this->remainingLabel = $this->remainingLabel ?: $contextManager($this->remaining);
        }
        $this->currentMessage = $this->currentMessage ?: $this->currentLabel . $this->currentMessageSuffix ?? '';
        $this->requestedMessage = $this->requestedMessage ?: $this->requestedLabel . $this->requestedMessageSuffix ?? '';
        $this->remainingMessage = $this->remainingMessage ?: $this->remainingLabel . $this->remainingMessageSuffix ?? '';

        parent::init();
    }
}