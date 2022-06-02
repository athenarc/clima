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
                    <td class="col-md-6 text-left" scope="col"><?php
                        if (isset($requestHistory['diff']['project']['submission_date']) && isset($requestHistory['diff']['project']['approval_date'])) {
                            ProjectDiff::str($requestHistory['diff']['project']['approval_date']['other'], $requestHistory['diff']['project']['submission_date']['current']);
                        } else echo $start;
                        ?>
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
                        <td class="col-md-6 text-left" scope="col"><?php
                            if (isset($requestHistory['diff']['project']['end_date'])) {
                                ProjectDiff::str($requestHistory['diff']['project']['end_date']['other'], $requestHistory['diff']['project']['end_date']['current']);
                            } else echo $ends;
                            ?> (<?= $remaining_time ?> days remaining
                            <?php if (isset($requestHistory['diff']['project']['end_date'])) {
                                echo '- <span class="text-'
                                    . (($requestHistory['diff']['project']['end_date']['difference'] > 0) ? 'danger' : 'success')
                                    . '">' . abs($requestHistory['diff']['project']['end_date']['difference'])
                                    . ' days '
                                    . (($requestHistory['diff']['project']['end_date']['difference'] > 0) ? ' to be extended' : 'to be shortened')
                                    . '</span>';
                            } ?>)
                        </td>
                    </tr>
                    <?php
                } ?>
                <tr>
                    <th class="col-md-6 text-right" scope="col">Participating users:</th>
                    <td class="col-md-6 text-left" scope="col">
                        <?php
                        if (isset($requestHistory['diff']['project']['user_list'])) {
                            ProjectDiff::arr($requestHistory['diff']['project']['user_list']['other'], $requestHistory['diff']['project']['user_list']['current']);
                        } else echo $user_list;
                        ?>(<?= $number_of_users ?> out of
                        <?php
                        if (isset($requestHistory['diff']['project']['user_num'])) {
                            ProjectDiff::str($requestHistory['diff']['project']['user_num']['other'], $requestHistory['diff']['project']['user_num']['current']);
                        } else echo $maximum_number_users;
                        ?>)
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
                        <div class="row mr-0">
                            <div class="col-4 text-left"><?php
                                if (isset($requestHistory['diff']['details']['num_of_vms'])) {
                                    ProjectDiff::str($requestHistory['diff']['details']['num_of_vms']['other'], $requestHistory['diff']['details']['num_of_vms']['current']);
                                } else echo $details->num_of_vms;
                                ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-6 text-right" scope="col">CPU cores per VM:</th>
                    <td class="col-md-6" scope="col">
                        <div class="row mr-0">
                            <div class="col-4 text-left">
                                <?php
                                if (isset($requestHistory['diff']['details']['num_of_cores'])) {
                                    if (isset($requestHistory['diff']['details']['num_of_cores']['current']) && isset($requestHistory['diff']['details']['num_of_cores']['other'])) {
                                        ProjectDiff::str($requestHistory['diff']['details']['num_of_cores']['other'], $requestHistory['diff']['details']['num_of_cores']['current']);
                                    }
                                    echo ' (<span class="text-'
                                        .(($requestHistory['diff']['details']['num_of_cores']['difference']>0)?'danger':'success')
                                        .'">'
                                        .abs($requestHistory['diff']['details']['num_of_cores']['difference'])
                                        .' cores in total to be '
                                        .(($requestHistory['diff']['details']['num_of_cores']['difference']>0)?'allocated':'released')
                                        .'</span>)';
                                } else echo $details->num_of_cores;
                                ?>
                            </div>
                            <div class="col-8 text-right pr-0"><?php
                                if ($project->status== 0 && isset($resourcesStats['num_of_cores'])) {
                                    echo ColorClassedLoadIndicator::widget([
                                        'current' => $resourcesStats['num_of_cores']['current'],
                                        'requested' => $resourcesStats['num_of_cores']['requested'],
                                        'total' => $resourcesStats['num_of_cores']['total'],
                                        'context' => ContextualLoadIndicator::CPU,
                                        'loadBreakpoint0' => $resourcesStats['general']['loadBreakpoint0'],
                                        'loadBreakpoint1' => $resourcesStats['general']['loadBreakpoint1'],
                                        'bootstrap4RequestedClassPositive'=>$resourcesStats['general']['bootstrap4RequestedClassPositive'],
                                        'bootstrap4RequestedClassNegative'=>$resourcesStats['general']['bootstrap4RequestedClassNegative']]);
                                }
                            ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-6 text-right" scope="col">RAM per VM:</th>
                    <td class="col-md-6" scope="col">
                        <div class="row mr-0">
                            <div class="col-4 text-left">
                                <?php
                                if (isset($requestHistory['diff']['details']['ram'])) {
                                    if (isset($requestHistory['diff']['details']['ram']['current']) && isset($requestHistory['diff']['details']['ram']['other'])) {
                                        ProjectDiff::str($requestHistory['diff']['details']['ram']['other'], $requestHistory['diff']['details']['ram']['current'].'GBs');
                                    }
                                    echo ' (<span class="text-'
                                        .(($requestHistory['diff']['details']['ram']['difference']>0)?'danger':'success')
                                        .'">'
                                        .abs($requestHistory['diff']['details']['ram']['difference'])
                                        .'GBs RAM in total to be '
                                        .(($requestHistory['diff']['details']['ram']['difference']>0)?'allocated':'released')
                                        .'</span>)';
                                } else echo $details->ram.'GBs';
                                ?>
                            </div>
                            <div class="col-8 text-right pr-0"><?= (isset($resourcesStats['ram']) && $project->status==0)
                                    ? ColorClassedLoadIndicator::widget([
                                        'current' => $resourcesStats['ram']['current'],
                                        'requested' => $resourcesStats['ram']['requested'],
                                        'total' => $resourcesStats['ram']['total'],
                                        'context' => ContextualLoadIndicator::MEMORY,
                                        'loadBreakpoint0'=>$resourcesStats['general']['loadBreakpoint0'],
                                        'loadBreakpoint1'=>$resourcesStats['general']['loadBreakpoint1'],
                                        'bootstrap4RequestedClassPositive'=>$resourcesStats['general']['bootstrap4RequestedClassPositive'],
                                        'bootstrap4RequestedClassNegative'=>$resourcesStats['general']['bootstrap4RequestedClassNegative']])
                                    : '' ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-6 text-right" scope="col">Number of IPs per VM:</th>
                    <td class="col-md-6" scope="col">
                        <div class="row mr-0">
                            <div class="col-4 text-left"><?php
                                if (isset($requestHistory['diff']['details']['num_of_ips'])) {
                                    if (isset($requestHistory['diff']['details']['num_of_ips']['current']) && isset($requestHistory['diff']['details']['num_of_ips']['other'])) {
                                        ProjectDiff::str($requestHistory['diff']['details']['num_of_ips']['other'], $requestHistory['diff']['details']['num_of_ips']['current']);
                                    }
                                    echo ' (<span class="text-'
                                        .(($requestHistory['diff']['details']['num_of_ips']['difference']>0)?'danger':'success')
                                        .'">'
                                        .abs($requestHistory['diff']['details']['num_of_ips']['difference'])
                                        .'IPs in total to be '
                                        .(($requestHistory['diff']['details']['num_of_ips']['difference']>0)?'bound':'released')
                                        .'</span>)';
                                } else echo $details->num_of_ips;
                                ?></div>
                            <div class="col-8 text-right pr-0"><?= (isset($resourcesStats['num_of_ips']) && $project->status==0)
                                    ? ColorClassedLoadIndicator::widget([
                                        'current' => $resourcesStats['num_of_ips']['current'],
                                        'requested' => $resourcesStats['num_of_ips']['requested'],
                                        'total' => $resourcesStats['num_of_ips']['total'],
                                        'context' => ContextualLoadIndicator::IP,
                                        'loadBreakpoint0'=>$resourcesStats['general']['loadBreakpoint0'],
                                        'loadBreakpoint1'=>$resourcesStats['general']['loadBreakpoint1'],
                                        'bootstrap4RequestedClassPositive'=>$resourcesStats['general']['bootstrap4RequestedClassPositive'],
                                        'bootstrap4RequestedClassNegative'=>$resourcesStats['general']['bootstrap4RequestedClassNegative']])
                                    : '' ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-6 text-right" scope="col">Disk per VM:</th>
                    <td class="col-md-6" scope="col">
                        <div class="row mr-0">
                            <div class="col-4 text-left">
                                <?php
                                if (isset($requestHistory['diff']['details']['disk'])) {
                                    if (isset($requestHistory['diff']['details']['disk']['current']) && isset($requestHistory['diff']['details']['disk']['other'])) {
                                        ProjectDiff::str($requestHistory['diff']['details']['disk']['other'], $requestHistory['diff']['details']['disk']['current'].'GBs');
                                    }
                                    echo ' (<span class="text-'
                                        .(($requestHistory['diff']['details']['disk']['difference']>0)?'danger':'success')
                                        .'">'
                                        .abs($requestHistory['diff']['details']['disk']['difference'])
                                        .'GBs of disk in total to be '
                                        .(($requestHistory['diff']['details']['disk']['difference']>0)?'allocated':'released')
                                        .'</span>)';
                                } else echo $details->disk.'GBs';
                                ?>
                            </div>
                            <div class="col-8 text-right pr-0"><?= (isset($resourcesStats['storage']) && $project->status==0)
                                    ? ColorClassedLoadIndicator::widget([
                                        'current' => $resourcesStats['storage']['current'],
                                        'requested' => $resourcesStats['storage']['requested'],
                                        'total' => $resourcesStats['storage']['total'],
                                        'context' => ContextualLoadIndicator::MEMORY,
                                        'loadBreakpoint0'=>$resourcesStats['general']['loadBreakpoint0'],
                                        'loadBreakpoint1'=>$resourcesStats['general']['loadBreakpoint1'],
                                        'bootstrap4RequestedClassPositive'=>$resourcesStats['general']['bootstrap4RequestedClassPositive'],
                                        'bootstrap4RequestedClassNegative'=>$resourcesStats['general']['bootstrap4RequestedClassNegative']])
                                    : '' ?></div>
                        </div>
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
                        <?php
                        if (isset($requestHistory['diff']['details']['name'])) {
                            ProjectDiff::str($requestHistory['diff']['details']['name']['other'], $requestHistory['diff']['details']['name']['current']);
                        } else echo $details->name;
                        ?>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-6 text-right" scope="col">Service version:</th>
                    <td class="col-md-6 text-left" scope="col">
                        <?php
                        if (isset($requestHistory['diff']['details']['version'])) {
                            ProjectDiff::str($requestHistory['diff']['details']['version']['other'], $requestHistory['diff']['details']['version']['current']);
                        } else echo $details->version;
                        ?>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-6 text-right" scope="col">Service description:</th>
                    <td class="col-md-6 text-left" scope="col">
                        <?php
                        if (isset($requestHistory['diff']['details']['description'])) {
                            ProjectDiff::str($requestHistory['diff']['details']['description']['other'], $requestHistory['diff']['details']['description']['current']);
                        } else echo $details->description;
                        ?>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-6 text-right" scope="col">Service URL:</th>
                    <td class="col-md-6 text-left" scope="col">
                        <?php
                        if (isset($requestHistory['diff']['details']['url'])) {
                            ProjectDiff::str($requestHistory['diff']['details']['url']['other'], $requestHistory['diff']['details']['url']['current']);
                        } else echo $details->url;
                        ?>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-6 text-right" scope="col">Service TRL:</th>
                    <td class="col-md-6 text-left" scope="col">
                        <?php
                        if (isset($requestHistory['diff']['details']['trl'])) {
                            ProjectDiff::str($requestHistory['diff']['details']['trl']['other'], $requestHistory['diff']['details']['trl']['current']);
                        } else echo $details->trl;
                        ?>
                    </td>
                </tr>
			</tbody>
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
