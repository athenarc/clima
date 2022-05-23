<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use app\components\ColorClassedLoadIndicator;
use app\components\ContextualLoadIndicator;
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
['title'=>'Project details', 'subtitle'=>$project->name,
	'buttons'=>
	[
		
		['fontawesome_class'=>'<i class="fas fa-arrow-left"></i>','name'=> 'Back', 'action'=>['/project/request-list'],
		 'options'=>['class'=>'btn btn-default'], 'type'=>'a'] 
	],
])
?>
<?Headers::end()?>
<?php
$loadIndicatorColorClassBreakpoints = ['loadBreakpoint0'=>0.33, 'loadBreakpoint1'=>0.66];
?>

<div class="col-md-12 text-center"><h3 style="font-weight:bold;">Basic info </h3></div>
	<div class="table-responsive">
		<table class="table table-striped">
			<tbody>
			<tr>
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
<div class="col-md-12 text-center"><h3 style="font-weight:bold;">Resources </h3></div>
	<div class="table-responsive">
		<table class="table table-striped">
			<tbody>
			<tr>
				<th class="col-md-6 text-right" scope="col">Number of VMs:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->num_of_vms ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">CPU cores:</th>
                <td class="col-md-6" scope="col">
                    <div class="row mr-0">
                        <div class="col-4 text-left"><?= $details->num_of_cores ?></div>
                        <div class="col-8 text-right pr-0"><?= (isset($resourcesStats['cpu']))
                                ? ColorClassedLoadIndicator::widget(array_merge([
                                    'current' => $resourcesStats['cpu']['current'],
                                    'requested' => $resourcesStats['cpu']['requested'],
                                    'total' => $resourcesStats['cpu']['total'],
                                    'context' => ContextualLoadIndicator::CPU,
                                ], $loadIndicatorColorClassBreakpoints))
                                : '' ?></div>
                    </div>
                </td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">RAM:</th>
                <td class="col-md-6" scope="col">
                    <div class="row mr-0">
                        <div class="col-4 text-left"><?= $details->ram ?> GBs</div>
                        <div class="col-8 text-right pr-0"><?= (isset($resourcesStats['ram']))
                                ? ColorClassedLoadIndicator::widget(array_merge([
                                    'current' => $resourcesStats['ram']['current'],
                                    'requested' => $resourcesStats['ram']['requested'],
                                    'total' => $resourcesStats['ram']['total'],
                                    'context' => ContextualLoadIndicator::MEMORY,
                                ], $loadIndicatorColorClassBreakpoints))
                                : '' ?></div>
                    </div>
                </td>
			</tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Number of IPs:</th>
                <td class="col-md-6" scope="col">
                    <div class="row mr-0">
                        <div class="col-4 text-left"><?= $details->num_of_ips ?></div>
                        <div class="col-8 text-right pr-0"><?= (isset($resourcesStats['ips']))
                                ? ColorClassedLoadIndicator::widget(array_merge([
                                    'current' => $resourcesStats['ips']['current'],
                                    'requested' => $resourcesStats['ips']['requested'],
                                    'total' => $resourcesStats['ips']['total'],
                                    'context' => ContextualLoadIndicator::IP,
                                ], $loadIndicatorColorClassBreakpoints))
                                : '' ?></div>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Disk:</th>
                <td class="col-md-6" scope="col">
                    <div class="row mr-0">
                        <div class="col-4 text-left"><?= $details->disk ?> GBs</div>
                        <div class="col-8 text-right pr-0"><?= (isset($resourcesStats['storage']))
                                ? ColorClassedLoadIndicator::widget(array_merge([
                                    'current' => $resourcesStats['storage']['current'],
                                    'requested' => $resourcesStats['storage']['requested'],
                                    'total' => $resourcesStats['storage']['total'],
                                    'context' => ContextualLoadIndicator::MEMORY,
                                ], $loadIndicatorColorClassBreakpoints))
                                : '' ?></div>
                    </div>
                </td>
            </tr>
			</body>
		</table>
	</div>
<div class="col-md-12 text-center"><h3 style="font-weight:bold;"> Additional info </h3></div>
	<div class="table-responsive">
		<table class="table table-striped">
			<tbody>
			<tr>
				<th class="col-md-6 text-right" scope="col">Analysis description:</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->description ?></td>
			</tr>
			</body>
		</table>
	</div>
</div>
<div class="row">&nbsp;</div>

<?php
if ($project->status==0)
{
?>

	<div class="row">
		<div class="col-md-12 text-center">
            <?php
            if (!$resourcesStats['general']['excessiveRequest']) {
                echo Html::a("$approve_icon Approve", ['/project/approve', 'id' => $request_id], ['class' => 'btn btn-success']);
            } ?>&nbsp;&nbsp;&nbsp;&nbsp;
            <?= Html::a("$modify_icon Modify", ['/project/modify-request', 'id'=>$request_id], ['class'=>'btn btn-secondary']) ?>&nbsp;&nbsp;&nbsp;&nbsp;
            <?= Html::a("$reject_icon Reject", ['/project/reject', 'id'=>$request_id], ['class'=>'btn btn-danger']) ?>
    	</div>
	</div>
<?php
}
elseif(($project->status==1) ||($project->status==2))
{?>
<div class="row">
		<div class="col-md-12 text-center">
            <?= Html::a("$modify_icon Modify", ['/project/modify-request', 'id'=>$request_id], ['class'=>'btn btn-secondary']) ?>&nbsp;&nbsp;&nbsp;&nbsp;
    	</div>
	</div>
<?php
}
?>

<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
