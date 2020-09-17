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
use webvimark\modules\UserManagement\models\User as Userw;


echo Html::CssFile('@web/css/project/new-request.css');

$this->title="Create new project";

$ondemandImage=Html::img('@web/img/project/on-demand.png', ['alt' => 'New ondemand project request', 'class'=> 'button-image']);
$serviceImage=Html::img('@web/img/project/24_7-service.png', ['alt' => 'New 24/7 service project request', 'class'=> 'button-image']);
$coldImage=Html::img('@web/img/project/cold-storage.png', ['alt' => 'New cold-storage project request', 'class'=> 'button-image']);
/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */
$view_icon='<i class="fas fa-eye"></i>';

?>

<div class='title row'>
	<div class="col-md-30 headers">
		<?= Html::encode($this->title) ?>
		<br><br><br>
		<div class="col-md-offset-0">
			<?= Html::a($serviceImage, ['/project/new-service-request'], ['class'=>'image-button']) ?>
			<?= Html::a($ondemandImage, ['/project/new-ondemand-request'], ['class'=>'image-button']) ?>
		<?php
			if (Userw::hasRole('Gold',$superadminAllowed=false) || Userw::hasRole("Silver", $superAdminAllowed = false))
			{
				echo Html::a($coldImage, ['/project/new-cold-storage-request'], ['class'=>'image-button']);
			}
		?>
			
			
	</div>
	</div>
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