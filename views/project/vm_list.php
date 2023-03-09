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
use app\components\Headers; 


echo Html::CssFile('@web/css/project/vm-list.css');
$this->registerJsFile('@web/js/administration/all_projects.js', ['depends' => [\yii\web\JqueryAsset::className()]]);


$this->title="VM history";


/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */


Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'VM history', 
])

?>
<?Headers::end()?>




<div class="container-fluid">
  	<div class="row">
    	<div class="col-md-2 sidebar1 facet-sidebar">
        	<div class="card">			
				<div class="filter-content">
					<div class="list-group list-group-flush">
					<?php
						foreach ($sideItems as $item)
						{
							//echo $k;
							// echo $user;
							
							if ($item['text']== "All"){
								echo Html::a($item['text']." (".$count_all.")",$item['link'],['class'=>$item['class']]);
							} elseif ($item['text']== "Active"){
								echo Html::a($item['text']." (".$count_active.")",$item['link'],['class'=>$item['class']]);
							} else {
								echo Html::a($item['text']." (".$count_deleted.")",$item['link'],['class'=>$item['class']]);
							}
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
						<th class="col-md-2 text-center" scope="col">Project Name</th>
						<th class="col-md-2 text-center" scope="col">IP Address</th>
						<th class="col-md-2 text-center" scope="col">Created by</th>
						<th class="col-md-2 text-center" scope="col">Created at</th>
						<!--th class="col-md-2 text-center" scope="col">Deleted by</th-->
						<th class="col-md-1 text-center" scope="col">Status</th>
						<th class="col-md-1 text-center" scope="col">&nbsp;</th>
					</tr>
				</thead>
				<tbody>
<?php

	$index = 0;
	foreach ($results as $res)
	{
		$view_icon='<i class="fas fa-eye"></i>';
		$exclamation_icon='<i class="fas fa-exclamation-triangle" style="color:orange" title="The Vm belongs to an expired project"></i>';
		$button_link='project/admin-vm-details';
		$username = $res['created_by'];
		$userc=explode('@', $res['created_by'])[0];
		$user = $userc;
		$userd=explode('@', $res['deleted_by'])[0];
		$status=($res['active'])? 'Active': 'Deleted';
		$class='vm-' . $status

		


	?>
						<tr class="<?=$class?>">
							<td class="col-md-2 align-middle"><?=$res['project_name']?> <?=($res['expired']==1 &&
							$res['active']==1) ? $exclamation_icon : ''?></td>
							<td class="col-md-2 align-middle"><?=$ips[$index]?></td>
							<td class="col-md-2 align-middle"><?=$userc?></td>
							<td class="col-md-2 align-middle"><?=empty($res['created_at'])? '' : date("F j, Y, H:i:s",strtotime($res['created_at']))?></td>

							<!--td class="col-md-2 align-middle"><!?=$userd?></td-->
							<!--td class="col-md-2 align-middle"><--?=empty($res['deleted_at'])? '' : date("F j, Y, H:i:s",strtotime($res['deleted_at']))?></td-->
							<td class="col-md-1 align-middle"><?=$status?></td>
							<td class="col-md-1 align-middle"><?=Html::a("$view_icon Details",[$button_link,'project_id'=>$res['project_id'],'filter'=>$filter, 'id'=>$res['vm_id']],['class'=>'btn btn-primary btn-md'])?></td>
					</tr>
		
	<?php
	$index = $index+1;
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

</div><!--container-fluid-->

<div class="filters-div">
	<h4 class="text-center">Filter</h4>
	<?=Html::beginForm(['project/vm-list', 'filter'=>$filter],'get',['id'=>'filters-form'])?>

		<div class="row">
			<div class="col-md-12 text-center">
				<?=Html::label('By user:')?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 text-center">
				<?=Html::input($search_user,'username',$filters['user'],['class'=>'username_field'])?>
			</div>
		</div>

		<div class="row">&nbsp;</div>

		<div class="row">
			<div class="col-md-12 text-center">
				<?=Html::label('By project name:')?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 text-center">
				<?=Html::input($search_project,'project_name',$filters['project'],['class'=>'project_field'])?>
				
			</div>
		</div>
				<div class="row">&nbsp;</div>


		<div class="row">
			<div class="col-md-12 text-center">
				<?=Html::label('By IP address:')?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 text-center">
				<?=Html::input($ip_address,'ip_address',$filters['ip'],['class'=>'ip_field'])?>
		
			</div>
		</div>

	<div class="row">&nbsp;</div>
	<INPUT type="submit" value="Submit">

	<?=Html::endForm()?>
</div>


	</div><!--row-->
	<div class="row">&nbsp;</div>
	<div class="row"><div class="col-md-12"><div class="float-right"><?= LinkPager::widget(['pagination' => $pages]) ?></div></div></div>

