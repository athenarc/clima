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
				<th class="col-md-6 text-right" scope="col">24/7 VMs</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['vms'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">24/7 Used virtual CPUs</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['s_cores'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">24/7 Used RAM (GB)</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['s_ram'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">24/7 Used storage (TB)</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['s_storage'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Active on-demand projects</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['active_ondemand'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">On-demand completed jobs</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['o_jobs'] ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">On-demand total execution time</th>
				<td class="col-md-6 text-left" scope="col"><?= $usage['o_time'] ?></td>
			</tr>
		</body>
	</table>
</div>

