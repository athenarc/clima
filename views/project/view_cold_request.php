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
				<td class="col-md-6 text-left" scope="col">
                    <?= ProjectValueDisplay::simpleValue(($details->type=='hot')?'Hot':'Cold', 'type', $requestHistory); ?>
                </td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">VM type:</th>
				<td class="col-md-6 text-left" scope="col">
                    <?= ProjectValueDisplay::simpleValue(($details->vm_type==1)?'24/7 Service':'On-demand computation machines', 'vm_type', $requestHistory); ?>
                </td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Number of volumes:</th>
				<td class="col-md-6 text-left" scope="col">
                    <?= ProjectValueDisplay::simpleValue($details->num_of_volumes, 'num_of_volumes', $requestHistory); ?>
                </td>
			</tr>
			<tr>
				<th class="col-md-6 text-right" scope="col">Allocated storage per volume:</th>
                <td class="col-md-6" scope="col">
                    <?=ProjectValueDisplay::resource($details->storage,'storage',$requestHistory, $resourcesStats, ResourceContext::MEMORY);?>
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