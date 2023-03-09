<?php

use yii\helpers\Html;
use app\components\Headers;

echo Html::CssFile('@web/css/project/vm-details.css');
$this->registerJsFile('@web/js/project/vm-details.js', ['depends' => [\yii\web\JqueryAsset::className()]]);


$this->title="VM details";

$back_icon='<i class="fas fa-arrow-left"></i>';
$x_icon='<i class="fas fa-times"></i>';

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'Vm details', 
	'buttons'=>
	[
		
		['fontawesome_class'=>'<i class="fas fa-arrow-left"></i>','name'=> 'Back', 'action'=>['/project/vm-machines-list', 'filter'=>$filter],
		 'options'=>['class'=>'btn btn-default'], 'type'=>'a'] 
	],
])
?>
<?Headers::end()?>



<div class="row">&nbsp;</div>
<div class="credentials-box">
	<div class="credentials-box-content">
		<div class="row">
			<div class="col-md-5">
				<strong>Project name:</strong>
			</div>
			<div class="col-md-2">
				<?=$project->name?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-5">
				<strong>Project owner:</strong>
			</div>
			<div class="col-md-2">
				<?=$projectOwner?>
			</div>
		</div>

		<div class="row">&nbsp;</div>

		<div class="row">
			<div class="col-md-5">
				<strong>Status:</strong>
			</div>
			<div class="col-md-2">
				<?=$vm->active ? 'Active': 'Deleted'?>
			</div>
		</div>

		<div class="row">
			<div class="col-md-5">
				<strong>Cores:</strong>
			</div>
			<div class="col-md-2">
				<?=$service->num_of_cores?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-5">
				<strong>RAM:</strong>
			</div>
			<div class="col-md-2">
				<?=$service->ram?> GB
			</div>
		</div>
		<div class="row">
			<div class="col-md-5">
				<strong>VM disk:</strong>
			</div>
			<div class="col-md-2">
				<?=$service->disk?> GB
			</div>
		</div>
		<div class="row">
			<div class="col-md-5">
				<strong>Additional storage:</strong>
			</div>
			<div class="col-md-2">
				<?=$service->storage?> GB
			</div>
		</div>
		<div class="row">
			<div class="col-md-5">
				<strong>Image:</strong>
			</div>
			<div class="col-md-2">
				<?=$vm->image_name?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-5">
				<strong>IP Address (SSH):</strong>
			</div>
			<div class="col-md-2">
				<?=$vm->ip_address?>
			</div>
		</div>

		<div class="row">&nbsp;</div>

		<div class="row">
			<div class="col-md-5">
				<strong>Created by:</strong>
			</div>
			<div class="col-md-7">
				<?=$createdBy?> on <?=empty($vm->created_by)? '' : date("F j, Y, H:i:s",strtotime($vm->created_at))?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-5">
				<strong>Deleted by:</strong>
			</div>
			<div class="col-md-7">
				<?=empty($deletedBy) ?  '' : $deletedBy . ' at ' . date("F j, Y, H:i:s",strtotime($vm->deleted_at))?>
			</div>
		</div>
	</div>
</div>

<?php
if ($vm->active)
{
?>

<div class="row"><div class="col-md-12 text-center">&nbsp;</div></div>
<div class="row"><div class="col-md-12 text-center"><?= Html::tag("button","Delete VM", ['class'=>'btn btn-danger delete-vm-btn'])?></div></div>

<div class="modal delete fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="delete-modal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
   			<div class="modal-header">
			<h5 class="modal-title" id="exampleModalLongTitle">Confirm delete</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true" class="btn-cancel-modal">&times;</span>
			</button>
			</div>
			<div class="modal-body">Are you sure you want to delete this VM?</div>
			<div class="modal-loading"><b>Deleting <i class="fas fa-spinner fa-spin"></i></b></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary btn-cancel-modal" data-dismiss="modal">Cancel</button>
				<?=Html::a("$x_icon Delete",['/project/delete-vm-machines','id'=>$project_id],['class'=>"btn btn-danger confirm-delete"])?>
			</div>
		</div>
	</div>
</div>

<?php
}

