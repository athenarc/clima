<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use app\components\ProjectDiff;
use app\components\ProjectValueDisplay;
use rmrevin\yii\fontawesome\FA;
use yii\helpers\Html;
use yii\widgets\LinkPager;
use app\components\Headers;


$this->title="Project details";

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
['title'=>'Project details', 'subtitle'=>$project->name,
	'buttons'=>
	[
		
		['fontawesome_class'=>'<i class="fas fa-arrow-left"></i>','name'=> 'Back', 'action'=>['/project/request-list', 'filter'=>$filter],
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
                <th class="col-md-6 text-right" scope="col">Status:</th>
                <td class="col-md-6 text-left" scope="col"><?= $project_status ?>
                    <?php if ($project->status == 0) {
                        echo ($requestHistory['isMod'])
                            ? '<span class="text-secondary" title="Modification">' . FA::icon('pencil-alt') . '</span>'
                            : '<span class="text-warning" title="New project">' . FA::icon('star') . '</span>';
                    }
                    ?>
                </td>
            </tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Started on:</th>
				<td class="col-md-6 text-left" scope="col">
                    <?= ProjectValueDisplay::startDate($start,$requestHistory); ?>
                </td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Ends on: </th>
				<td class="col-md-6 text-left" scope="col">
                    <?= ProjectValueDisplay::endDate($ends, $remaining_time, $requestHistory) ?>
                </td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Participating users:</th>
				<td class="col-md-6 text-left" scope="col">
                    <?= ProjectValueDisplay::userList($user_list, $number_of_users, $maximum_number_users, $requestHistory) ?>
                </td>
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
				<td class="col-md-6 text-left" scope="col"><div class="col-md-3" style="padding-left: 0px;"><?= $remaining_jobs?> (out of
                        <?= ProjectValueDisplay::simpleValue($details->num_of_jobs, 'num_of_jobs', $requestHistory); ?>
                    )</div><div class="col-md-9"><div class="progress"><div class="progress-bar <?=$bar_class?>" role="progressbar" style="width:<?=$bar_percentage?>%" aria-valuenow="$bar_percentage" aria-valuemin="0" aria-valuemax="100"></div></div></div></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">CPU cores for job:</th>
				<td class="col-md-6 text-left" scope="col">
                    <?= ProjectValueDisplay::simpleValue($details->cores, 'cores', $requestHistory); ?>
                    (used <?=round($usage['cpu']/1000,2)?> cores/job οn average)</td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">RAM for jobs:</th>
				<td class="col-md-6 text-left" scope="col">
                    <?= ProjectValueDisplay::simpleValue($details->ram, 'ram', $requestHistory); ?>
                    GBs (used <?=round($usage['ram'],2)?> GBs/job οn average)</td>
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
				<td class="col-md-6 text-left" scope="col">
                    <?= ProjectValueDisplay::simpleValue($details->analysis_type, 'analysis_type', $requestHistory); ?>
                </td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Maturity:</th>
				<td class="col-md-6 text-left" scope="col">
                    <?= ProjectValueDisplay::simpleValue($details->maturity, 'maturity', $requestHistory); ?>
                </td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Description:</th>
				<td class="col-md-6 text-left" scope="col">
                    <?= ProjectValueDisplay::simpleValue($details->description, 'description', $requestHistory); ?>
                </td>
			</tr>
		</body>
	</table>
</div>

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
