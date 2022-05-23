<?php


namespace app\components;

/*
 * Includes
 */

use yii\base\Widget;

/*
 * The widget class
 */

class ColorClassedLoadIndicator extends ContextualLoadIndicator
{
    public $loadBreakpoint0;
    public $loadBreakpoint1;
    public $considerRequested;

    public function init()
    {
        // Will not run if theme is explicitly defined
        if (!$this->bootstrap4CurrentClass) {
            if ($this->loadBreakpoint0 && $this->loadBreakpoint1) {
                $loadToConsider = ($this->current + ($this->considerRequested * $this->requested)) / $this->total;

                if ($loadToConsider < $this->loadBreakpoint0) $qualifiedBootstrapClass='success';
                elseif ($loadToConsider < $this->loadBreakpoint1) $qualifiedBootstrapClass='warning';
                else $qualifiedBootstrapClass = 'danger';

                $this->bootstrap4CurrentClass = $qualifiedBootstrapClass;
                if ($this->considerRequested) $this->bootstrap4RequestedClass = $qualifiedBootstrapClass;
            }
        }

        parent::init();
    }
}
