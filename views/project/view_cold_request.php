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
                    <th class="col-md-6 text-right" scope="col">Owner: </th>
                    <td class="col-md-6 text-left" scope="col"><?=$submitted->username ?></td>
                </tr>
			</tbody>
		</table>
	</div>
<div class="col-md-12 text-center"><h3 style="font-weight:bold;">Resources </h3></tr></div>
<div class="table-responsive">
	<table class="table table-striped">
		<tbody>
			<tr>
				<th class="col-md-6 text-right" scope="col">Storage type:</th>
				<td class="col-md-6 text-left" scope="col"><?php
                    if (isset($requestHistory['diff']['details']['type'])) {
                        ProjectDiff::str($requestHistory['diff']['details']['type']['other'], $requestHistory['diff']['details']['type']['current']);
                    } else echo ($details->type=='hot')?'Hot':'Cold';
                    ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">VM type:</th>
				<td class="col-md-6 text-left" scope="col"><?php
                    if (isset($requestHistory['diff']['details']['vm_type'])) {
                        ProjectDiff::str($requestHistory['diff']['details']['vm_type']['other'], $requestHistory['diff']['details']['vm_type']['current']);
                    } else echo ($details->vm_type==1)?'24/7 Service':'On-demand computation machines';
                    ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Number of volumes:</th>
				<td class="col-md-6 text-left" scope="col"><?php
                    if (isset($requestHistory['diff']['details']['num_of_volumes'])) {
                        ProjectDiff::str($requestHistory['diff']['details']['num_of_volumes']['other'], $requestHistory['diff']['details']['num_of_volumes']['current']);
                    } else echo $details->num_of_volumes;
                    ?></td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Allocated storage per volume:</th>
                <td class="col-md-6" scope="col">
                    <div class="row mr-0">
                        <div class="col-4 text-left"><?php
                            if (isset($requestHistory['diff']['details']['storage'])) {
                                if (isset($requestHistory['diff']['details']['storage']['current']) && isset($requestHistory['diff']['details']['storage']['other'])) {
                                    ProjectDiff::str($requestHistory['diff']['details']['storage']['other'], $requestHistory['diff']['details']['storage']['current'].'GBs');
                                }
                                echo ' (<span class="text-'
                                    .(($requestHistory['diff']['details']['storage']['difference']>0)?'danger':'success')
                                    .'">'
                                    .abs($requestHistory['diff']['details']['storage']['difference'])
                                    .'GBs of disk in total to be '
                                    .(($requestHistory['diff']['details']['storage']['difference']>0)?'allocated':'released')
                                    .'</span>)';
                            } else echo $details->storage.'GBs';
                            ?></div>
                        <div class="col-8 text-right pr-0"><?= (isset($resourcesStats['storage']) && $project->status==0)
                                ? ColorClassedLoadIndicator::widget([
                                    'current'=>$resourcesStats['storage']['current'],
                                    'requested'=>$resourcesStats['storage']['requested'],
                                    'total'=>$resourcesStats['storage']['total'],
                                    'context'=>ContextualLoadIndicator::MEMORY,
                                    'loadBreakpoint0'=>$resourcesStats['general']['loadBreakpoint0'],
                                    'loadBreakpoint1'=>$resourcesStats['general']['loadBreakpoint1'],
                                    'bootstrap4RequestedClassPositive'=>$resourcesStats['general']['bootstrap4RequestedClassPositive'],
                                    'bootstrap4RequestedClassNegative'=>$resourcesStats['general']['bootstrap4RequestedClassNegative']])
                                : ''?></div>
                    </div>
                </td>
			</tr>
		</tbody>
	</table>
</div>
<div class="col-md-12 text-center"><h3 style="font-weight:bold;">Additional info </h3></tr></div>
<div class="table-responsive">
	<table class="table table-striped">
		<tbody>
			<tr>		
				<th class="col-md-6 text-right" scope="col">Description:</th>
				<td class="col-md-6 text-left" scope="col"><?php
                    if (isset($requestHistory['diff']['details']['description'])) {
                        ProjectDiff::str($requestHistory['diff']['details']['description']['other'], $requestHistory['diff']['details']['description']['current']);
                    } else echo $details->description;
                    ?></td>
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
		<div class="col-md-12 text-center">
            <?php
            if (!$resourcesStats['general']['excessiveRequest']) {
                echo Html::a("$approve_icon Approve", ['/project/approve', 'id' => $request_id], ['class' => 'btn btn-success']);
            } ?>
            &nbsp;&nbsp;&nbsp;&nbsp;
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