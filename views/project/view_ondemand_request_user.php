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



echo Html::cssFile('@web/css/project/project_details.css');
$this->registerJsFile('@web/js/project/view-request-user.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$approve_icon='<i class="fas fa-check"></i>';
$reject_icon='<i class="fas fa-times"></i>';
$back_icon='<i class="fas fa-arrow-left"></i>';
if ($return == 'user_request'){
	$back_link='/project/user-request-list';
} elseif ($return == 'index'){
	$back_link='/project/index';
} else {
	$back_link='administration/all-projects';
}

$bar_percentage=round(($usage['count'])/$details->num_of_jobs*100);
$cancel_icon='<i class="fas fa-times"></i>';
$edit_icon='<i class="fas fa-pencil-alt"></i>';
$update_icon='<i class="fas fa-pencil-alt"></i>';
//added the next line
//$access_icon='<i class="fas fa-external-link-square-alt"></i>';


if ($bar_percentage<=25)
{
	$bar_class='bg-success';
}
else if (($bar_percentage>25) && ($bar_percentage<=50))
{
	$bar_class='bg-info';
}
else if (($bar_percentage>50) && ($bar_percentage<=75))
{
	$bar_class='bg-warning';
}
else if (($bar_percentage>75) && ($bar_percentage<=100))
{
	$bar_class='bg-danger';
}

/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */
Headers::begin() ?>
<?php
if ($project_owner & (($project->status==1) || ($project->status==2)) & $expired!=1)
{
	if ($return == 'user_request') {
		echo Headers::widget(
			['title'=>"Project details", 'subtitle'=>$project->name,
				'buttons'=>
				[
					//added the access button that redirects you to schema
					//['fontawesome_class'=>$access_icon,'name'=> 'Access', 'action'=> ['/site/index','id'=>$request_id], 'type'=>'a', 'options'=>['class'=>'btn btn-success btn-md'] ],
					['fontawesome_class'=>$update_icon,'name'=> 'Update', 'action'=> ['/project/edit-project','id'=>$request_id], 'type'=>'a', 'options'=>['class'=>'btn btn-secondary btn-md'] ],
					['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>[$back_link, 'filter'=>$filter], 'type'=>'a', 
					'options'=>['class'=>'btn btn-default']] 
				],
			]);
	} else {
		echo Headers::widget(
			['title'=>"Project details", 'subtitle'=>$project->name,
				'buttons'=>
				[
					//added the access button that redirects you to schema
					//['fontawesome_class'=>$access_icon,'name'=> 'Access', 'action'=> ['/site/index','id'=>$request_id], 'type'=>'a', 'options'=>['class'=>'btn btn-success btn-md'] ],
					['fontawesome_class'=>$update_icon,'name'=> 'Update', 'action'=> ['/project/edit-project','id'=>$request_id], 'type'=>'a', 'options'=>['class'=>'btn btn-secondary btn-md'] ],
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
				<th class="col-md-6 text-right" scope="col">Remaining jobs:</th>
				<td class="col-md-6 text-left" scope="col"><div class="col-md-3" style="padding-left: 0px;"><?= $remaining_jobs?> (out of <?=$details->num_of_jobs ?>)</div><div class="col-md-9"><div class="progress"><div class="progress-bar <?=$bar_class?>" role="progressbar" style="width:<?=$bar_percentage?>%" aria-valuenow="$bar_percentage" aria-valuemin="0" aria-valuemax="100"></div></div></div></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">CPU cores for job:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->cores ?> (used <?=round($usage['cpu']/1000,2)?> cores/job οn average)</td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">RAM for jobs:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->ram ?> GBs (used <?=round($usage['ram'],2)?> GBs/job οn average)</td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Active Jupyter Servers:</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['active_jupyter'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Total Jupyter Servers:</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['total_jupyter']?></td>
			</tr>
		</tbody>
	</table>
</div>
<div class="col-md-12 text-center"><h3 style="font-weight:bold;">Additional info </h3></tr></div>
<div class="table-responsive">
	<table class="table table-striped">
		<tbody>		
			<tr>
				<th class="col-md-6 text-right" scope="col">Analysis type:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->analysis_type ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Maturity:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->maturity ?></td>
			</tr>
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