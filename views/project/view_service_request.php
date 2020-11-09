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

$approve_icon='<i class="fas fa-check"></i>';
$reject_icon='<i class="fas fa-times"></i>';
$back_icon='<i class="fas fa-arrow-left"></i>';
$modify_icon='<i class="fas fa-pencil-alt"></i>';
/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */
Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'Project details', 
	'buttons'=>
	[
		
		['fontawesome_class'=>'<i class="fas fa-arrow-left"></i>','name'=> 'Back', 'action'=>['/project/request-list'],
		 'options'=>['class'=>'btn btn-default'], 'type'=>'a'] 
	],
])
?>
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
					<td class="col-md-6 text-left" scope="col"><?=$ends?> (<?=$remaining_time?> days remaining)</td>
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
				<th class="col-md-6 text-right" scope="col">CPU cores:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->num_of_cores ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">RAM:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->ram ?> GBs</td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Additional storage amount:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->storage ?> GBs</td>
			</tr>
			</body>
		</table>
	</div>
<div class="col-md-12 text-center"><h3 style="font-weight:bold;"> Additional info </h3></div>
	<div class="table-responsive">
		<table class="table table-striped">
			<tbody>
			<tr>
				<th class="col-md-6 text-right" scope="col">Backup service available:</th>
				<td class="col-md-6 text-left" scope="col"><?=($project->backup_services=='t')? 'Yes' : 'No'?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Service name:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->name ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Service version:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->version ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Service description:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->description ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Service URL:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->url ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Service TRL:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->trl ?></td>
			</tr>
			</body>
		</table>
	</div>
</div>
<div class="row">&nbsp;</div>

<!-- <div class="row">&nbsp;</div>

<div class="table-responsive">
	<table class="table table-striped">
		<tbody>
			<tr>
				<th class="col-md-6 text-right" scope="col">Name</th>
				<td class="col-md-6 text-left" scope="col"><?= $project->name ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Duration (in months)</th>
				<td class="col-md-6 text-left" scope="col"><?= $project->duration ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Maximum number of participating users</th>
				<td class="col-md-6 text-left" scope="col"><?= $project->user_num ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Participating users</th>
				<td class="col-md-6 text-left" scope="col"><?= $user_list ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Backup service available</th>
				<td class="col-md-6 text-left" scope="col"><?=($project->backup_services=='t')? 'Yes' : 'No'?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Service Name</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->name ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Service Version</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->version ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Service description</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->description ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Service URL</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->url ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Service TRL</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->trl ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Number of requested CPU cores</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->num_of_cores ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Requested memory (RAM) amount (in GBs)</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->ram ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Requested additional storage amount (in GBs)</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->storage ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Submitted by</th>
				<td class="col-md-6 text-left" scope="col"><?=$submitted->username ?></td>
			</tr>
			
		</body>
	</table>
</div>
<div class="row">&nbsp;</div>
 -->
<?php
if ($project->status==0)
{
?>

	<div class="row">
		<div class="col-md-12 text-center">
            <?= Html::a("$approve_icon Approve",['/project/approve', 'id'=>$request_id], ['class' => 'btn btn-success']) ?>&nbsp;&nbsp;&nbsp;&nbsp;
            <?= Html::a("$modify_icon Modify", ['/project/modify-request', 'id'=>$request_id], ['class'=>'btn btn-secondary']) ?>&nbsp;&nbsp;&nbsp;&nbsp;
            <?= Html::a("$reject_icon Reject", ['/project/reject', 'id'=>$request_id], ['class'=>'btn btn-danger']) ?>
    	</div>
	</div>
<?php
}
?>

<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
