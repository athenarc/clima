<?php

/**
 * View file for the About page 
 * 
 * @author: Kostis Zagganas
 * First version: Dec 2018
 */

use yii\helpers\Html;

$this->title = 'Privacy statement';
// $this->params['breadcrumbs'][] = $this->title;
?>
    
 <?=$page->content?>

<?php
if (!empty($analytics))
{
    echo"<h3>Analytics</h4>";
    foreach($analytics as $analytic)
    {
        echo "<h4>$analytic->name</h4>";
        echo $analytic->opt_out_code;
    }
}
?>
