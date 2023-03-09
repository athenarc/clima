<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use app\components\ColorClassedLoadIndicator;
use app\components\ContextualLoadIndicator;
use app\components\ProjectDiff;
use app\components\ProjectValueDisplay;
use app\components\ResourceContext;
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
/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */
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
                <tr>
                    <th class="col-md-6 text-right" scope="col">Type:</th>
                    <td class="col-md-6 text-left" scope="col"><?= $type ?>
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
                <?php
                if ($remaining_time == 0) {
                    ?>
                    <tr>
                        <th class="col-md-6 text-right" scope="col">Ended on:</th>
                        <td class="col-md-6 text-left" scope="col"><?= $ends ?> (<?= $remaining_time ?> days remaining)</td>
                    </tr>
                    <?php
                } else {
                    ?>
                    <tr>
                        <th class="col-md-6 text-right" scope="col">Ends on:</th>
                        <td class="col-md-6 text-left" scope="col">
                            <?= ProjectValueDisplay::endDate($ends, $remaining_time, $requestHistory) ?>
                        </td>
                    </tr>
                    <?php
                } ?>
                <tr>
                    <th class="col-md-6 text-right" scope="col">Participating users:</th>
                    <td class="col-md-6 text-left" scope="col">
                        <?= ProjectValueDisplay::userList($user_list, $number_of_users, $maximum_number_users, $requestHistory) ?>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-6 text-right" scope="col">Owner:</th>
                    <td class="col-md-6 text-left" scope="col"><?= $submitted->username ?></td>
                </tr>
            </tbody>
		</table>
	</div>
<div class="col-md-12 text-center"><h3 style="font-weight:bold;">Resources </h3></tr></div>
	<div class="table-responsive">
		<table class="table table-striped">
			<tbody>
                <tr>
                    <th class="col-md-6 text-right" scope="col">Number of VMs:</th>
                    <td class="col-md-6" scope="col">
                        <?= ProjectValueDisplay::simpleValue($details->num_of_vms, 'num_of_vms', $requestHistory); ?>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-6 text-right" scope="col">CPU cores per VM:</th>
                    <td class="col-md-6" scope="col">
                        <?=ProjectValueDisplay::resource($details->num_of_cores,'num_of_cores',$requestHistory, $resourcesStats, ResourceContext::CPU);?>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-6 text-right" scope="col">RAM per VM:</th>
                    <td class="col-md-6" scope="col">
                        <?=ProjectValueDisplay::resource($details->ram,'ram',$requestHistory, $resourcesStats, ResourceContext::MEMORY);?>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-6 text-right" scope="col">Number of IPs per VM:</th>
                    <td class="col-md-6" scope="col">
                        <?=ProjectValueDisplay::resource($details->num_of_ips,'num_of_ips',$requestHistory, $resourcesStats, ResourceContext::IP);?>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-6 text-right" scope="col">Disk per VM:</th>
                    <td class="col-md-6" scope="col">
                        <?=ProjectValueDisplay::resource($details->disk,'disk',$requestHistory, $resourcesStats, ResourceContext::MEMORY);?>
                    </td>
                </tr>
			</tbody>
		</table>
	</div>
<div class="col-md-12 text-center"><h3 style="font-weight:bold;"> Additional info </h3></div>
	<div class="table-responsive">
		<table class="table table-striped">
			<tbody>
                <tr>
                    <th class="col-md-6 text-right" scope="col">Service name:</th>
                    <td class="col-md-6 text-left" scope="col">
                        <?= ProjectValueDisplay::simpleValue($details->name, 'name', $requestHistory); ?>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-6 text-right" scope="col">Service version:</th>
                    <td class="col-md-6 text-left" scope="col">
                        <?= ProjectValueDisplay::simpleValue($details->version, 'version', $requestHistory); ?>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-6 text-right" scope="col">Service description:</th>
                    <td class="col-md-6 text-left" scope="col">
                        <?= ProjectValueDisplay::simpleValue($details->description, 'description', $requestHistory); ?>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-6 text-right" scope="col">Service URL:</th>
                    <td class="col-md-6 text-left" scope="col">
                        <?= ProjectValueDisplay::simpleValue($details->url, 'url', $requestHistory); ?>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-6 text-right" scope="col">Service TRL:</th>
                    <td class="col-md-6 text-left" scope="col">
                        <?= ProjectValueDisplay::simpleValue($details->trl, 'trl', $requestHistory); ?>
                    </td>
                </tr>
			</tbody>
		</table>
	</div>
</div>

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
