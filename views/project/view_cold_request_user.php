<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title="Project details";

echo Html::cssFile('@web/css/project/project_details.css');
$this->registerJsFile('@web/js/project/view-request-user.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$approve_icon='<i class="fas fa-check"></i>';
$reject_icon='<i class="fas fa-times"></i>';
$back_icon='<i class="fas fa-arrow-left"></i>';
$back_link=($return=='index') ? '/project/index' : '/project/user-request-list';
$cancel_icon='<i class="fas fa-times"></i>';
$edit_icon='<i class="fas fa-pencil-alt"></i>';
$update_icon='<i class="fas fa-pencil-alt"></i>';

/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */
?>
<div class='title row'>
	<div class="col-md-8 headers">
		<span><?= Html::encode($this->title) ?></span>/<span class="subtitle"><?=$project->name?></span>
	</div>
	<div class="col-md-4" style="text-align: right; padding-top: 5px;">
		<?php
		if ($project_owner & (($project->status==1) || ($project->status==2)) )
		{?>
		<?=Html::a("$update_icon Update",['/project/edit-project','id'=>$request_id],['class'=>'btn btn-secondary btn-md'])?>
		<?= Html::A("$cancel_icon Delete",['/project/cancel-project', 'id'=>$request_id], ['class' => 'btn btn-secondary delete-project-btn']) ?>
		<?php
		}?>
		<?= Html::a("$back_icon Back", [$back_link], ['class'=>'btn btn-default']) ?>
	</div>
</div>

<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>

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
			<tr>
				<th class="col-md-6 text-right" scope="col">Ends on: </th>
				<td class="col-md-6 text-left" scope="col"><?=$ends?> (<?=$remaining_time?> days remaining)</td>
			</tr>
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
				<th class="col-md-6 text-right" scope="col">Allocated storage:</th>
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
			<th class="col-md-6 text-right" scope="col">Description</th>
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