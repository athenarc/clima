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


echo Html::CssFile('@web/css/project/request-list.css');

$this->title="Project requests";


/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */


?>

	<div class='title row'>
		<div class="col-md-12 headers">
			<?= Html::encode($this->title) ?>
		</div>
	</div>

	<div class="row">&nbsp;</div>


<?php

if (!empty($message))
{
?>
		<div class="row message"><div class="alert alert-success col-md-12" role="alert"><?=$message?></div></div>
<?php
}

?>
<div class="container-fluid">
  	<div class="row">
    	<div class="col-md-2 sidebar1 facet-sidebar">
        	<div class="card">			
				<div class="filter-content">
					<div class="list-group list-group-flush">
					<?php
						foreach ($sideItems as $item)
						{
							echo Html::a($item['text'],$item['link'],['class'=>$item['class']]);
						}
					?>
					</div>  <!-- list-group .// -->
				</div>
        	</div>
		</div>

<?php
if (!empty($results))
{
	
?>





		<div class="col-md-10 main-content">
                  
			<table class="table table-responsive">
				<thead>
					<tr>
						<th class="col-md-2" scope="col">Name</th>
						<th class="col-md-2" scope="col">Submitted by</th>
						<th class="col-md-3" scope="col">Submitted on</th>
						<th class="col-md-1" scope="col">Project type</th>
						<th class="col-md-2" scope="col">Status</th>
						<th class="col-md-1" scope="col">&nbsp;</th>
					</tr>
				</thead>
				<tbody>
<?php



foreach ($results as $res)
{
	$view_icon='<i class="fas fa-eye"></i>';
	// $button_link=$button_link=$button_links[$res['project_type']];
	$user=explode('@',$res['username'])[0];
	


?>
					<tr class="<?=$line_classes[$res['status']]?>">
						<td class="col-md-2 align-middle"><?=$res['name']?></td>
						<td class="col-md-2 align-middle"><?=$user?></td>
						<td class="col-md-2 align-middle"><?=date("F j, Y, H:i:s",strtotime($res['submission_date']))?></td>
						<td class="col-md-2 align-middle"><?=$project_types[$res['project_type']] ?></td>
						<td class="col-md-1 align-middle"><?=$statuses[$res['status']]?></td>
						<td class="col-md-1 align-middle"><?=Html::a("$view_icon Details",['/project/view-request','id'=>$res['id'],'filter'=>$filter],['class'=>'btn btn-primary btn-md'])?></td>
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
		<div class="col-md-10"><h3 class="empty-message">There are no recorded submissions of this type.</h3></div>
<?php
}
?>
	</div><!--row-->
	<div class="row">&nbsp;</div>
	<div class="row"><div class="col-md-12"><div class="float-right"><?= LinkPager::widget(['pagination' => $pages]) ?></div></div></div>

</div><!--container-fluid-->