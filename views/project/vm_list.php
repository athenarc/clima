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


echo Html::CssFile('@web/css/project/vm-list.css');

$this->title="List of VMs";


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
						<th class="col-md-2" scope="col">Project Name</th>
						<th class="col-md-2" scope="col">Created by</th>
						<th class="col-md-2" scope="col">Created at</th>
						<th class="col-md-2" scope="col">Deleted by</th>
						<th class="col-md-2" scope="col">Deleted at</th>
						<th class="col-md-1" scope="col">Status</th>
						<th class="col-md-1" scope="col">&nbsp;</th>
					</tr>
				</thead>
				<tbody>
<?php


	foreach ($results as $res)
	{

		$view_icon='<i class="fas fa-eye"></i>';
		$button_link='project/admin-vm-details';
		$userc=explode('@', $res['created_by'])[0];
		$userd=explode('@', $res['deleted_by'])[0];
		$status=($res['active'])? 'Active': 'Deleted';
		$class='vm-' . $status

		


	?>
						<tr class="<?=$class?>">
							<td class="col-md-2 align-middle"><?=$res['project_name']?></td>
							<td class="col-md-2 align-middle"><?=$userc?></td>
							<td class="col-md-2 align-middle"><?=empty($res['created_at'])? '' : date("F j, Y, H:i:s",strtotime($res['created_at']))?></td>

							<td class="col-md-2 align-middle"><?=$userd?></td>
							<td class="col-md-2 align-middle"><?=empty($res['deleted_at'])? '' : date("F j, Y, H:i:s",strtotime($res['deleted_at']))?></td>
							<td class="col-md-1 align-middle"><?=$status?></td>
							<td class="col-md-1 align-middle"><?=Html::a("$view_icon Details",[$button_link,'request_id'=>$res['project_id'],'filter'=>$filter, 'id'=>$res['vm_id']],['class'=>'btn btn-primary btn-md'])?></td>
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
		<div class="col-md-10"><h3 class="empty-message">There is no VM history</h3></div>
<?php
}
?>
	</div><!--row-->
	<div class="row">&nbsp;</div>
	<div class="row"><div class="col-md-12"><div class="float-right"><?= LinkPager::widget(['pagination' => $pages]) ?></div></div></div>

</div><!--container-fluid-->