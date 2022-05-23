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
        if (!$this->bootstrap4Class) {
            if ($this->loadBreakpoint0 && $this->loadBreakpoint1) {
                $loadToConsider = ($this->current + ($this->considerRequested * $this->requested)) / $this->total;
                if ($loadToConsider < $this->loadBreakpoint0) $this->bootstrap4Class = 'success';
                elseif ($loadToConsider < $this->loadBreakpoint1) $this->bootstrap4Class = 'warning';
                else $this->bootstrap4Class = 'danger';
            }
        }

        parent::init();
    }
}
