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

<div class="col-md-12 text-center"><h2><strong>General</strong></h2></div>
<div class="table-responsive">
	<table class="table table-striped">
		<tbody>
			<tr>
				<th class="col-md-6 text-right" scope="col">Users</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['users'] ?></td>
			</tr>
		</tbody>
	</table>
</div>
<div class="col-md-12 text-center"><h2><strong>24/7 services</strong></h2></div>
<div class="table-responsive">
	<table class="table table-striped">
		<tbody>
			<tr>
				<th class="col-md-6 text-right" scope="col">Active projects</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['active_services'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Total  projects</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['total_services'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col"> Active VMs</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['vms_services_active'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col"> Total VMs</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['vms_services_total'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Active used virtual CPUs</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['active_services_cores'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col"> Active used RAM (GB)</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['active_services_ram'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Total used virtual CPUs</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['total_services_cores'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col"> Total used RAM (GB)</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['total_services_ram'] ?></td>
			</tr>
		</tbody>
	</table>
</div>
<div class="col-md-12 text-center"><h2><strong>On-demand computation machines</strong></h2></div>
<div class="table-responsive">
	<table class="table table-striped">
		<tbody>
			<tr>
				<th class="col-md-6 text-right" scope="col">Active projects</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['active_machines'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Total projects</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['total_machines'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col"> Active  VMs</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['vms_machines_active'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col"> Total VMs</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['vms_machines_total'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Active used virtual CPUs</th>
				<td class="col-md-6 text-left" scope="col"><?= empty($usage['active_machines_cores'])?'0': $usage['active_machines_cores'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col"> Active  used RAM (GB)</th>
				<td class="col-md-6 text-left" scope="col"><?= empty($usage['active_machines_ram'])?'0': $usage['active_machines_ram'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Total used virtual CPUs</th>
				<td class="col-md-6 text-left" scope="col"><?= empty($usage['total_machines_cores'])?'0': $usage['total_machines_cores'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col"> Total  used RAM (GB)</th>
				<td class="col-md-6 text-left" scope="col"><?= empty($usage['total_machines_ram'])?'0': $usage['total_machines_ram'] ?></td>
			</tr>
	</tbody>
	</table>
</div>
<div class="col-md-12 text-center"><h2><strong>On-demand batch computations</strong></h2></div>
<div class="table-responsive">
	<table class="table table-striped">
		<tbody>

			<tr>
				<th class="col-md-6 text-right" scope="col">Active projects</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['active_ondemand'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Total projects</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['total_ondemand'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Total task executions</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['task_executions'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Running tasks</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['running_tasks'] ?></td>
			</tr>
		</tbody>
	</table>
</div>
<div class="col-md-12 text-center"><h2><strong>Storage volumes</strong></h2></div>
<div class="table-responsive">
	<table class="table table-striped">
		<tbody>
			<tr>
				<th class="col-md-6 text-right" scope="col">Total projects </th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['total_storage_projects'] ?> (<?= $usage['number_storage_service']?> for 24/7 service, <?= $usage['number_storage_machines']?> for on-demand compute machines)</td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Total used storage (TB)</th>
				<td class="col-md-6 text-left" scope="col"><?= number_format($usage['total_storage_size'],2) ?> TB (<?= number_format($usage['size_storage_service'],2)?> TB for 24/7 service, <?= number_format($usage['size_storage_machines'],2)?> TB for compute machines)</td>
			</tr>
		</body>
	</table>
</div>

