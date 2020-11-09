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

$approve_icon='<i class="fas fa-check"></i>';
$reject_icon='<i class="fas fa-times"></i>';
$back_icon='<i class="fas fa-arrow-left"></i>';
$modify_icon='<i class="fas fa-pencil-alt"></i>';
$bar_percentage=round(($usage['count'])/$details->num_of_jobs*100);

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
				<th class="col-md-6 text-right" scope="col">Remaining jobs:</th>
				<td class="col-md-6 text-left" scope="col"><div class="col-md-3" style="padding-left: 0px;"><?= $remaining_jobs?> (out of <?=$details->num_of_jobs ?>)</div><div class="col-md-9"><div class="progress"><div class="progress-bar <?=$bar_class?>" role="progressbar" style="width:<?=$bar_percentage?>%" aria-valuenow="$bar_percentage" aria-valuemin="0" aria-valuemax="100"></div></div></div></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Time/job:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->time_per_job ?> min (used <?=round($usage['avg_time'])?> min/job οn average)</td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">CPU cores for job:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->cores ?> (used <?=round($usage['cpu']/1000,2)?> cores/job οn average)</td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">RAM for jobs:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->ram ?> GBs (used <?=round($usage['ram'],2)?> GBs/job οn average)</td>
			</tr>
			<!-- <tr>
				<th class="col-md-6 text-right" scope="col">Allocated storage</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->storage ?> GBs</td>
			</tr> -->
		</tbody>
	</table>
</div>
<div class="col-md-12 text-center"><h3 style="font-weight:bold;">Additional info </h3></tr></div>
<div class="table-responsive">
	<table class="table table-striped">
		<tbody>		
			<!-- <tr>
				<th class="col-md-6 text-right" scope="col">Backup service available:</th>
				<td class="col-md-6 text-left" scope="col"><?=($project->backup_services=='t')? 'Yes' : 'No'?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Type of software:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->containerized ?></td>
			</tr> -->
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
				<th class="col-md-6 text-right" scope="col">Type of software</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->containerized ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Type of analysis</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->analysis_type ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Maturity</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->maturity ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Project description</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->description ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Number of jobs</th>
				<td class="col-md-6 text-left" scope="col"><div class="col-md-3"><?= $usage['count']?>/<?=$details->num_of_jobs ?></div><div class="col-md-9"><div class="progress"><div class="progress-bar <?=$bar_class?>" role="progressbar" style="width:<?=$bar_percentage?>%" aria-valuenow="$bar_percentage" aria-valuemin="0" aria-valuemax="100"></div></div></div></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">CPU cores</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->cores ?> (used <?=round($usage['cpu']/1000,2)?> per job, οn average)</td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Time per job (in minutes)</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->time_per_job ?> (took <?=$usage['avg_time']?> per job, οn average)</td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Requested memory (RAM) amount (in GBs)</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->ram ?> (used <?=round($usage['ram'],2)?> GBs per job, οn average)</td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Requested storage amount (in GBs)</th>
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
