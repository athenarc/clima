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

$this->title="Storage volumes";

$this->registerJsFile('@web/js/project/storage-volumes.js', ['depends' => [\yii\web\JqueryAsset::className()]]);


$manage_icon='<i class="fas fa-pencil-alt"></i>';
$back_icon='<i class="fas fa-arrow-left"></i>';
$delete_icon='<i class="fas fa-times"></i>';



Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'Storage volumes', 
	'buttons'=>
	[
		
		['fontawesome_class'=>'<i class="fas fa-arrow-left"></i>','name'=> 'Back', 'action'=>['/project/index'],
		 'options'=>['class'=>'btn btn-default'], 'type'=>'a'],
		['fontawesome_class'=>'<i class="far fa-question-circle"></i>','name'=> 'Volume guide', 'action'=>['site/additional-storage-tutorial'],
		 'options'=>['class'=>'btn btn-primary', 'title'=>'Guide to partition, format and mount a volume, which is attached to a VM', 'target'=>'_blank'], 'type'=>'a']  
	],
])
?>
<?Headers::end()?>





<?php
if(!empty($services))
{?>
<div class="table-responsive"> <h2>Volumes for 24/7 services</h2>
    <table class="table table-striped col-md-12">
		<thead>
			<tr>
				<th class="col-md-2" scope="col">Volume name</th>
				<th class="col-md-1" scope="col">Created at</th>
				<th class="col-md-2 text-center" scope="col">Attached to</th>
				<th class="col-md-2 text-center" scope="col">On</th>
				<th class="col-md-3" scope="col">&nbsp;</th>
			</tr>
		</thead>
		<tbody>

		<?php 
		foreach ($services as $res) 
		{
			$manage_button_class='';
			if($res['active']==false)
			{
				$manage_button_class='disabled';
			}?>
		
			<tr class="active">
			<td class="col-md-2"><?=$res['name']?> </td>
			<td class="col-md-1"><?=explode(' ',$res['accepted_at'])[0]?></td>
			<td class="col-md-2 text-center"><?=empty($res['vm_id'])?'-': $res['24/7 name']?></td>
			<td class="col-md-2 text-center"><?=empty($res['mountpoint'])?'-': $res['mountpoint']?></td>
			<td class="col-md-3 text-right">
				<?=Html::a("$manage_icon Manage attachment",['/project/manage-volume','id'=>$res['id'], 'service'=>$res['vm_type']],['class'=>"btn btn-secondary btn-md $manage_button_class"])?>
				<!-- <?php
				if($res['active']==true)
				{?>
					<?=Html::a("$delete_icon Delete",null,['class'=>"btn btn-danger btn-md delete-volume-btn",
					'id'=>$res['name']])?>
				<?php
				}?> -->
			</td>	
			</tr>
		<?php
		}?>
		</tbody>
		</table>
</div>
<?php
}
else
{?>
	<div class="row"><div class="col-md-12">
		<h2> No active VMs for on-demand computation machines </h2>
	</div></div>	
<?php
}?>


<?php
if(!empty($machines))
{?>
	<div class="table-responsive"><h2>Volumes for on-demand computation machines</h2>
    	<table class="table table-striped col-md-12">
			<thead>
				<tr>
					<th class="col-md-2" scope="col">Volume name</th>
					<th class="col-md-1" scope="col">Created at</th>
					<th class="col-md-2 text-center" scope="col">Attached to</th>
					<th class="col-md-2 text-center" scope="col">On</th>
					<th class="col-md-3" scope="col">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			<?php 
			foreach ($machines as $res) 
			{
				$manage_button_class='';
				if($res['active']==false)
				{
					$manage_button_class='disabled';
				}?>
			
				<tr class="active">
			<td class="col-md-2"><?=$res['name']?> </td>
			<td class="col-md-1"><?=explode(' ',$res['accepted_at'])[0]?></td>
			<td class="col-md-2 text-center"><?=empty($res['vm_id'])?'-': $res['machine name']?></td>
			<td class="col-md-2 text-center"><?=empty($res['mountpoint'])?'-': $res['mountpoint']?></td>
			<td class="col-md-3 text-right">
				<?=Html::a("$manage_icon Manage attachment",['/project/manage-volume','id'=>$res['id'], 'service'=>$res['vm_type']],['class'=>"btn btn-secondary btn-md $manage_button_class"])?>
				<!-- <?php
				if($res['active']==true)
				{?>
					<?=Html::a("$delete_icon Delete",null,['class'=>"btn btn-danger btn-md delete-volume-btn",
					'id'=>$res['name']])?>
				<?php
				}?> -->
			</td>	
			</tr>
			<?php
			}?>
		</tbody>
		</table>
</div>
<?php
}
else
{?>
	<div class="row"><div class="col-md-12">
		<h2> No active Vms for on-demand computation machines </h2>
	</div></div>	
<?php
}?>

<?php 
foreach ($results as $res) 
{?>
	<div class="modal <?=$res['name']?> fade" id="<?=$res['name']?>" tabindex="-1" role="dialog" aria-labelledby="delete-modal" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
	   			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLongTitle">Confirm delete</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true" class="btn-cancel-modal">&times;</span>
				</button>
				</div>
				<div class="modal-body">Are you sure you want to delete this Volume?</div>
				<div class="modal-loading">&nbsp;&nbsp;<b>Deleting <i class="fas fa-spinner fa-spin"></i></b></div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary btn-cancel-modal" data-dismiss="modal">Cancel</button>
					<?=Html::a("$delete_icon Delete",['/project/delete-volume','id'=>$res['id'], 'service'=>$res['vm_type'] ],['class'=>"btn btn-danger confirm-delete"])?>
				</div>
			</div>
		</div>
	</div>
<?php
}?>

<div class="modal guide fade" id="<?=$res['name']?>" tabindex="-1" role="dialog" aria-labelledby="delete-modal" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
	   			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLongTitle">Instructions for additional storage</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true" class="btn-cancel-modal">&times;</span>
				</button>
				</div>
				<div class="modal-body">In order to partition, format and mount the additional storage, which is attached to /dev/vdb, follow this <?=Html::a('guide',['site/additional-storage-tutorial'], ['target'=>'_blank'])?>.</div>
				</div>
		</div>
	</div>

