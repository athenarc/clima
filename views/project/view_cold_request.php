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

$this->title="Project details";

echo Html::cssFile('@web/css/project/project_details.css');

$approve_icon='<i class="fas fa-check"></i>';
$reject_icon='<i class="fas fa-times"></i>';
$back_icon='<i class="fas fa-arrow-left"></i>';
$modify_icon='<i class="fas fa-pencil-alt"></i>';

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
				<th class="col-md-6 text-right" scope="col">Allocated storage</th>
				<td class="col-md-6 text-left" scope="col"><?= $details->storage ?> GBs</td>
			</tr>
		</tbody>
	</table>
</div>
<div class="col-md-12 text-center"><h3 style="font-weight:bold;">Additional info </h3></tr></div>
<div class="table-responsive">
	<table class="table table-striped">
		<tbody>
			<tr>		
			<th class="col-md-6 text-right" scope="col">Description</th>
			<td class="col-md-6 text-left" scope="col"><?= $details->description ?></td>
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