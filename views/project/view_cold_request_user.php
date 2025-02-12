<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;
use app\components\Headers;

$this->title="Project details";


echo Html::cssFile('@web/css/project/project_details.css');
$this->registerJsFile('@web/js/project/view-request-user.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$approve_icon='<i class="fas fa-check"></i>';
$reject_icon='<i class="fas fa-times"></i>';
$back_icon='<i class="fas fa-arrow-left"></i>';
if ($return == 'user_request'){
	$back_link='/project/user-request-list';
}elseif ($return == 'index'){
	$back_link='/project/index';
}else {
	$back_link='/administration/all-projects';
}
#$back_link=($return=='index') ? '/project/index' : '/administration/all-projects';
$cancel_icon='<i class="fas fa-times"></i>';
$edit_icon='<i class="fas fa-pencil-alt"></i>';
$update_icon='<i class="fas fa-pencil-alt"></i>';
$delete_icon='<i class="fa fa-trash" aria-hidden="true"></i>';
//added the next line
//$access_icon='<i class="fas fa-external-link-square-alt"></i>';


Headers::begin() ?>
<?php
if (($project_owner || $superAdmin) && $expired!=1)
{
	if ($return == 'user_request'){
		echo Headers::widget(
			['title'=>"Project details", 'subtitle'=>$project->name,
				'buttons'=>
				[
					//added the next line
					// ['fontawesome_class'=>$access_icon,'name'=> 'Access','action'=> ['/project/storage-volumes'], 'type'=>'a', 
					// 'options'=>['class'=>'btn btn-success']],
					//['fontawesome_class'=>$update_icon,'name'=> 'Update', 'action'=> ['/project/edit-project','id'=>$request_id], 'type'=>'a', 'options'=>['class'=>'btn btn-secondary btn-md'] ],
					['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>[$back_link, 'filter'=>$filter, 'page'=>$page], 'type'=>'a', 
					'options'=>['class'=>'btn btn-default']]
				],
			]);
	} elseif ($return == 'admin') {
		echo Headers::widget(
			['title'=>"Project details", 'subtitle'=>$project->name,
				'buttons'=>
				[
					//added the next line
					// ['fontawesome_class'=>$access_icon,'name'=> 'Access','action'=> ['/project/storage-volumes'], 'type'=>'a', 
					// 'options'=>['class'=>'btn btn-success']],
					//['fontawesome_class'=>$update_icon,'name'=> 'Update', 'action'=> ['/project/edit-project','id'=>$request_id], 'type'=>'a', 'options'=>['class'=>'btn btn-secondary btn-md'] ],
					['fontawesome_class'=>$delete_icon, "name"=> 'Delete','action'=>['project/delete-project', 'pid'=>$project->project_id, 'pname'=>$project->name],'options'=>['class'=>"btn btn-danger btn-md delete-volume-btn",'data' => [
						'confirm' => 'Are you sure you want to delete the project with name '.$project->name.'?'."\r\n".'If you have active resources, all of them will be deleted as well.',
						'method' => 'post',
						],], 'type'=>'a'],
					['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>[$back_link, 'ptype'=>$ptype, 'exp'=>$exp, 'user'=>$puser, 'project'=>$pproject], 'type'=>'a', 
					'options'=>['class'=>'btn btn-default']] 
				],
			]);
	} else{
		echo Headers::widget(
			['title'=>"Project details", 'subtitle'=>$project->name,
				'buttons'=>
				[
					//added the next line
					// ['fontawesome_class'=>$access_icon,'name'=> 'Access','action'=> ['/project/storage-volumes'], 'type'=>'a', 
					// 'options'=>['class'=>'btn btn-success']],
					//['fontawesome_class'=>$update_icon,'name'=> 'Update', 'action'=> ['/project/edit-project','id'=>$request_id], 'type'=>'a', 'options'=>['class'=>'btn btn-secondary btn-md'] ],
					['fontawesome_class'=>$delete_icon, "name"=> 'Delete','action'=>['project/delete-project', 'pid'=>$project->project_id, 'pname'=>$project->name],'options'=>['class'=>"btn btn-danger btn-md delete-volume-btn",'data' => [
						'confirm' => 'Are you sure you want to delete the project with name '.$project->name.'?'."\r\n".'If you have active resources, all of them will be deleted as well.',
						'method' => 'post',
						],], 'type'=>'a'],
					['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>[$back_link], 'type'=>'a', 
					'options'=>['class'=>'btn btn-default']] 
				],
			]);
	}

}
else
{
	echo Headers::widget(
	['title'=>"Project details", 'subtitle'=>$project->name,
		'buttons'=>
		[
			['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>[$back_link], 'type'=>'a', 
			'options'=>['class'=>'btn btn-default']] 
		],
	]);
}?>
<?Headers::end()?>





<div class="col-md-12 text-center"><h3 style="font-weight:bold;">Basic info </h3></tr></div>
	<div class="table-responsive">
		<table class="table table-striped">
			<tbody>
				<th class="col-md-6 text-right" scope="col">Type:</th>
				<td class="col-md-6 text-left" scope="col"><?=$type?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Started on:</th>
				<td class="col-md-6 text-left" scope="col"><?=$start?></td>
			</tr>
			<?php
			if($remaining_time==0)
			{?>
				<tr>
					<th class="col-md-6 text-right" scope="col">Ended on: </th>
					<td class="col-md-6 text-left" scope="col"><?=$ends?></td>
				</tr>
			<?php
			}
			else
			{?>
				<tr>
					<th class="col-md-6 text-right" scope="col">Ends on: </th>
					<td class="col-md-6 text-left" scope="col"><?=$ends?> (<?=$remaining_time?> days remaining)</td>
				</tr>
			<?php
			}?>
			<tr>
				<th class="col-md-6 text-right" scope="col">Participating users:</th>
				<td class="col-md-6 text-left" scope="col"><?= $user_list ?> (<?=$number_of_users?> out of <?=$maximum_number_users?>)</td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Owner: </th>
				<td class="col-md-6 text-left" scope="col"><?=$submitted->username ?></td>
			</tr>
			</body>
		</table>
	</div>
<div class="col-md-12 text-center"><h3 style="font-weight:bold;">Resources </h3></tr></div>
<div class="table-responsive">
	<table class="table table-striped">
		<tbody>
			<tr>
				<th class="col-md-6 text-right" scope="col">Storage type:</th>
				<td class="col-md-6 text-left" scope="col"><?=($details->type=='hot')?'Hot':'Cold'?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">VM type:</th>
				<td class="col-md-6 text-left" scope="col"><?=($details->vm_type==1)?'24/7 Service':'On-demand computation machines'?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Active volumes:</th>
				<td class="col-md-6 text-left" scope="col"><?=$usage['active_volumes']?> out of <?= $details->num_of_volumes ?> allowed</td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Total created volumes:</th>
				<td class="col-md-6 text-left" scope="col"><?=$usage['total_volumes']?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Allocated storage per volume:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->storage ?> GBs</td>
			</tr>
		</tbody>
	</table>
</div>
<div class="col-md-12 text-center"><h3 style="font-weight:bold;">Additional info </h3></tr></div>
<div class="table-responsive">
	<table class="table table-striped">
		<tbody>
			<tr>		
			<th class="col-md-6 text-right" scope="col">Description:</th>
			<td class="col-md-6 text-left" scope="col"><?= $details->description ?></td>
			</tr>
		</body>
	</table>
</div>
<div class="row">&nbsp;</div>
<?php
if ($project->status==0)
{
?>

	<div class="row">
		<div class="col-md-offset-4 col-md-3">
            <?= Html::A("$edit_icon Modify",['/project/modify-request', 'id'=>$request_id], ['class' => 'btn btn-secondary']) ?>
    	</div>
    	<div class="col-md-3">
            <?= Html::a("$cancel_icon Delete", ['javascript:void(0);', 'id'=>$request_id], ['class'=>'btn btn-secondary delete-request-btn']) ?>
    	</div>
	</div>
<?php
}?>


<div class="modal fade" id="delete-request-modal" tabindex="-1" role="dialog" aria-labelledby="delete-modal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
   			<div class="modal-header">
			<h5 class="modal-title" id="exampleModalLongTitle">Confirm request deletion</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true" class="btn-cancel-modal">&times;</span>
			</button>
			</div>
			<div class="modal-body">Are you sure you want to delete this request?</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary btn-cancel-modal" data-dismiss="modal">Cancel</button>
				<?=Html::a("$cancel_icon Delete",['/project/cancel-request','id'=>$request_id],['class'=>"btn btn-danger confirm-delete"])?>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="delete-project-modal" tabindex="-1" role="dialog" aria-labelledby="delete-modal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
   			<div class="modal-header">
			<h5 class="modal-title" id="exampleModalLongTitle">Confirm project deletion</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true" class="btn-cancel-modal">&times;</span>
			</button>
			</div>
			<div class="modal-body">Are you sure you want to delete this project?</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary btn-cancel-modal" data-dismiss="modal">Cancel</button>
				<?=Html::a("$cancel_icon Delete",['/project/cancel-project','id'=>$request_id],['class'=>"btn btn-danger confirm-delete"])?>
			</div>
		</div>
	</div>
</div>