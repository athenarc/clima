<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;
use yii\bootstrap\NavBar;
use yii\bootstrap\Nav;
use yii\helpers\Url;  
use app\components\ToolButton;
use app\components\Headers;
use webvimark\modules\UserManagement\models\User as Userw;


echo Html::CssFile('@web/css/project/new-request.css');

$this->title="Create new project";



$ondemand_icon='<i class="fa fa-bolt" aria-hidden="true"></i>';
$storage_icon='<i class="fa fa-database" aria-hidden="true"></i>';
$service_icon='<i class="fa fa-server" aria-hidden="true"></i>';

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'Create new project', 
])
?>
<?Headers::end()?>




<?php
?>
<div class="col-md-12 project">
<?= ToolButton::createButton("$ondemand_icon  On-demand batch computations", "",['/project/new-ondemand-request']) ?>
</div>
<?php
if (Userw::hasRole('Gold',$superAdminAllowed=true))
{
?>
<div class="col-md-12 project">
<?= ToolButton::createButton("$ondemand_icon  On-demand computation machines", "",['/project/new-machine-compute-request']) ?>
</div>
<?php
}
?>
<div class="col-md-12 project">
<?= ToolButton::createButton("$service_icon  24/7 service", "",['/project/new-service-request']) ?>
</div>

<?php

?>
<div class="col-md-12 project">
	<?= ToolButton::createButton("$storage_icon Storage volumes", "",['/project/new-cold-storage-request']) ?>
</div>
<?php
?>
 