<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;

$name=Yii::$app->params['name'];
$this->title="Current $name statistics";

echo Html::cssFile('@web/css/project/project_details.css');

$back_icon='<i class="fas fa-arrow-left"></i>';
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

<div class="table-responsive">
	<table class="table table-striped">
		<tbody>
			<tr>
				<th class="col-md-6 text-right" scope="col">Users</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['users'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Active 24/7 projects</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['active_services'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Total 24/7 projects</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['total_services'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col"> Active 24/7 VMs</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['vms_services_active'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col"> Total 24/7 VMs</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['vms_services_total'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Active 24/7 used virtual CPUs</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['active_services_cores'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col"> Active 24/7 used RAM (GB)</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['active_services_ram'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Total 24/7 used virtual CPUs</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['total_services_cores'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col"> Total 24/7 used RAM (GB)</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['total_services_ram'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Active on-demand computation machines  projects</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['active_machines'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Total on-demand computation machines projects</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['total_machines'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col"> Active on-demand computation machines VMs</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['vms_machines_active'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col"> Total on-demand computation machines VMs</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['vms_machines_total'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Active on-demand computation machines used virtual CPUs</th>
				<td class="col-md-6 text-left" scope="col"><?= empty($usage['active_machines_cores'])?'0': $usage['active_machines_cores'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col"> Active on-demand computation machines used RAM (GB)</th>
				<td class="col-md-6 text-left" scope="col"><?= empty($usage['active_machines_ram'])?'0': $usage['active_machines_ram'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Total on-demand computation machines used virtual CPUs</th>
				<td class="col-md-6 text-left" scope="col"><?= empty($usage['total_machines_cores'])?'0': $usage['total_machines_cores'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col"> Total on-demand computation machines used RAM (GB)</th>
				<td class="col-md-6 text-left" scope="col"><?= empty($usage['total_machines_ram'])?'0': $usage['total_machines_ram'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Active on-demand batch computations projects</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['active_ondemand'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Total on-demand batch computations projects</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['total_ondemand'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">On-demand batch computations completed jobs</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['o_jobs'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">On-demand batch computations total execution time</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['o_time'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Active used storage volumes (TB)</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['active_services_storage'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Total used storage volumes (TB)</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['total_services_storage'] ?></td>
			</tr>
		</body>
	</table>
</div>

