<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title="Manage analytics";

// echo Html::cssFile('@web/css/project/project_details.css');
$this->registerJsFile('@web/js/administration/manage-analytics.js', ['depends' => [\yii\web\JqueryAsset::className()]] );


$back_icon='<i class="fas fa-arrow-left"></i>';
$add_icon='<i class="fas fa-plus"></i>';
$view_icon='<i class="fas fa-eye"></i>';
$delete_icon='<i class="fas fa-times"></i>';
$edit_icon='<i class="fas fa-edit"></i>'
/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */
?>
<div class='title row'>
	<div class="col-md-11">
		<h1><?= Html::encode($this->title) ?></h1>
	</div>
	<div class="col-md-1 float-right">
		<?= Html::a("$back_icon Back", ['/administration/index'], ['class'=>'btn btn-default']) ?>
	</div>
</div>

<div class="row">&nbsp;</div>
<?= Html::a("$add_icon Add analytics", ['/administration/add-analytics'], ['class'=>'btn btn-primary']) ?>
<div class="row">&nbsp;</div>
<?php
if (empty($analytics))
{
?>
	<h2>No analytics available yet.</h2>
<?php
}
else
{
?>
<div class="table-responsive">
	<table class="table table-striped">
		<tbody>
			<tr>
				<th class="col-md-5 text-left" scope="col">Name</th>
				<th class="col-md-3 text-center" scope="col"></th>
			</tr>
<?php
		foreach ($analytics as $analytic)
		{
?>
			<tr>
				<td class="col-md-5 text-left" scope="col"><?=$analytic->name?></td>
				<td class="col-md-3 text-center" scope="col">
					<?=Html::a($edit_icon,['administration/edit-analytics','id'=>$analytic->id],['class'=>'btn btn-warning','title'=>'Edit'])?>
					<?=Html::a($delete_icon,'javascript:void(0);',['class'=>'btn btn-danger delete-button','title'=>'Delete'])?>
					<?=Html::hiddenInput('hidden_id',$analytic->id,['class'=>'hidden_analytics_id'])?>
				</td>
			</tr>
<?php
		}
?>
		</body>
	</table>
</div>
<?php
	foreach($analytics as $analytic)
	{
?>
		<div class="modal fade" id="delete-modal-<?=$analytic->id?>" tabindex="-1" role="dialog" aria-labelledby="delete-modal" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
		    	<div class="modal-content">
			   		<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLongTitle">Confirm delete</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						Are you sure you want to delete analytics for "<?=$analytic->name?>"?
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
							<?=Html::a("$delete_icon Delete",['administration/delete-analytics','id'=>$analytic->id],['class'=>'btn btn-danger'])?>
					</div>
				</div>
			</div>
		</div>
<?php
	}
}
?>