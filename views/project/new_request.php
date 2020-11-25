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
<div class="col-md-12" style="margin-bottom: 5px;">
<?= ToolButton::createButton("$ondemand_icon  Ondemand-computation", "",['/project/new-ondemand-request']) ?>
</div>
<div class="col-md-12" style="margin-bottom: 5px;">
<?= ToolButton::createButton("$service_icon  24/7 service", "",['/project/new-service-request']) ?>
</div>
<div class="col-md-12" style="margin-bottom: 5px;">
<?php
if (Userw::hasRole('Gold',$superadminAllowed=false) || Userw::hasRole("Silver", $superAdminAllowed = false))
{?>
	<?= ToolButton::createButton("$storage_icon Cold-storage", "",['/project/new-cold-storage-request']) ?>
<?php
}?>
</div>




<?php
}
else
{
?>
	<div class="col-md-5"><br /><br /><h3>You do not currently have any active projects.</h3></div>
<?php
}
?>
	</div><!--row--> 