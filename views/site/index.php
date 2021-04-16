<?php
/**
 * View file for the index page
 * 
 * @author: Kostis Zagganas
 * First version: Dec 2018
 */

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'EG-CI';

if(empty($page->content))
{
	echo "";
}
else
{
	echo $page->content;
}
