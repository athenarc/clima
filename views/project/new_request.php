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






<!-- <?php

if (!empty($message))
{
?>
		<div class="alert alert-success row" role="alert"><?=$message?></div>
<?php
}


if (!empty($results))
{
	
?>


<div class="col-md-10 col-sm-8 main-content">
                  
<table class="table table-responsive table-striped">
	<thead>
		<tr>
			<th class="col-md-2" scope="col">Name</th>
			<th class="col-md-1" scope="col">Duration (months)</th>
			<th class="col-md-2" scope="col">Submitted on</th>
			<th class="col-md-1" scope="col">Project type</th>
			<th class="col-md-2" scope="col">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
<?php



foreach ($results as $res)
{
	$view_icon='<i class="fas fa-eye"></i>';
	$button_link=$button_links[$res['project_type']];


?>
			<tr class="active">
				<td class="col-md-2"><?=$res['name']?></td>
				<td class="col-md-2"><?=$res['duration']?></td>
				<td class="col-md-2"><?=date("F j, Y, H:i:s",strtotime($res['submission_date']))?></td>
				<td class="col-md-2"><?=$project_types[$res['project_type']] ?></td>
				
				<td class="col-md-2"><?=Html::a("$view_icon View",[$button_link,'id'=>$res['id']],['class'=>'btn btn-primary btn-md'])?></td>
			</tr>

<?php
}
?>
	</tbody>
</table>

</div> <!--main content-->


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