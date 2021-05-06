<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;
use yii\bootstrap\NavBar;
use yii\bootstrap\Nav;
use yii\helpers\Url;  
use app\components\Headers;


echo Html::CssFile('@web/css/project/index.css');
$this->registerJsFile('@web/js/project/index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$this->title="Dashboard";


/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */
$history_icon='<i class="fas fa-history"></i>';
$new_icon='<i class="fas fa-plus-circle"></i>';
$roles=['temporary'=>'Temporary','gold'=>'Gold','silver'=>'Silver'];

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>"Dashboard", 
	'buttons'=>
	[
		['fontawesome_class'=>$history_icon,'name'=> 'Project requests', 'action'=> ['/project/user-request-list'], 'type'=>'a', 'options'=>['class'=>'btn btn-default'] ],
		['fontawesome_class'=>$new_icon,'name'=> 'New project', 'action'=>['/project/new-request'], 'type'=>'a', 
		'options'=>['class'=>'btn btn-success']] 
	],
	'special_content'=>'<div class="col-md-12 text-right">
							<h5><span class='.$role.'-user><i class="fa fa-certificate fa-xs" aria-hidden="true"></i>
							</span> <span>'. $roles[$role]. ' user &nbsp;</span><span title="Type of user"><i class="fa fa-question-circle" aria-hidden="true"></i></h5>
							</span>
						</div>'
])
?>
<?Headers::end()?>



<?php

if (!empty($success))
{
?>
		<div class="message row"><div class="col-md-12 alert alert-success" role="alert"><?=$success?></div></div>

<?php
}

if (!empty($warnings))
{
?>
		<div class="message row"><div class="col-md-12 alert alert-warning" role="alert"><?=$warnings?></div></div>
<?php
}
?>






<div class="row"><h3 class="col-md-12">Active projects (<?=$number_of_active?>)</h3></div>
<div class="row main-content">
<?php
if (!empty($active))
{
?>

<div class="table-responsive">
    <table class="table table-striped col-md-12">
		<thead>
			<tr>
				<th class="col-md-2" scope="col">Project</th>
				<th class="col-md-2" scope="col">Type</th>
				<th class="col-md-2 text-center" scope="col">Owner</th>
				<th class="col-md-2 text-center" scope="col">Expires in</th>
				<th class="col-md-3" scope="col">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
<?php



foreach ($active as $res)
{


	$user=explode('@',$res['username'])[0];

	// print_r($user);
	// exit(0);
	

	$view_icon='<i class="fas fa-eye"></i>';
	$usage_icon='<i class="fas fa-chart-pie"></i>';
	$access_icon='<i class="fas fa-external-link-square-alt"></i>';
	$button_link=$button_links[$res['project_type']];
	$update_icon='<i class="fas fa-pencil-alt"></i>';
	$edit_button_class='';

	if ($res['project_type']==0)
	{
		$projectLink="https://schema.imsi.athenarc.gr?r=software/index&selected_project=". $res['name'];
		//$projectLink="http://62.217.82.57/schema_test/web/index.php?r=software%2Findex&selected_project=". $res['name'];
		$projectTarget='_blank';
		$project_icon='<i class="fa fa-bolt" aria-hidden="true"></i>';
		$title='On-demand batch computation';
	}
	else if ($res['project_type']==1) 
	{
		$projectLink=Url::to(['/project/configure-vm','id'=>$res['project_id']]);
		$projectTarget='_self';
		$project_icon='<i class="fa fa-server" aria-hidden="true"></i>';
		$title='24/7 Service';
		if($res['louros']==true)
		{
			$edit_button_class="disabled";
		}
		else
		{
			$edit_button_class='';
		}
	}
	else if ($res['project_type']==3) 
	{
		$projectLink=Url::to(['/project/machine-compute-configure-vm','id'=>$res['project_id']]);
		$projectTarget='_self';
		$project_icon='<i class="fa fa-bolt" aria-hidden="true"></i>';
		$title='On-demand computation machines';
	}
	else
	{
		$projectLink=Url::to(['/site/under-construction']);
		$projectTarget='_self';
		$project_icon='<i class="fa fa-database" aria-hidden="true"></i>';
		$title="Cold-Storage";
	}



?>
			<tr class="active" style="font-size: 14px;">
				<td class="col-md-2" style="vertical-align: middle!important;"><?=$res['name']?></td>
				<td class="col-md-2" style="padding-left: 20px; vertical-align: middle!important;" title="<?=$title?>"><?=$project_icon ?></td>
				<td class="col-md-2 text-center" style="vertical-align: middle!important;"><?=$res[0]?></td>
				<td class="col-md-2 text-center" style="vertical-align: middle!important;"><?=$res[1]?> days</td>
				<td class="col-md-3 text-right">
					<?=Html::a("$update_icon Update",['/project/edit-project','id'=>$res['id']],['class'=>"btn btn-secondary btn-md $edit_button_class"])?>
					<?=Html::a("$view_icon Details",['/project/view-request-user','id'=>$res['id'],'return'=>'index','expired'=>0],['class'=>'btn btn-secondary btn-md'])?> 
					<?=Html::a("$access_icon Access", $projectLink,['class'=>'btn btn-success btn-md','target'=>$projectTarget])?>
				</td>	
			</tr>

<?php
}
?>
			</tbody>
		</table>	
</div> <!--table-responsive-->

	



<?php
}
else
{
?>
	<div class="col-md-12"><h4>You do not currently have any active projects.</h4></div>
<?php
}
?>
</div> <!-- main-content-->





<div class="row"><h3 class="col-md-12">Expired Projects (<?=$number_of_expired?>) 
	<i class="fas fa-chevron-down" id="arrow" title="Show projects" style="cursor: pointer" ></i></h3> 
</div>
<div class="row main-content">
<?php
if (!empty($expired))
{


	
?>

<div class="table-responsive" style="display:none;" id="expired-table">
   	<table class="table table-striped">
		<thead>
			<tr>
				<th class="col-md-2" scope="col">Project</th>
				<th class="col-md-2" scope="col">Type</th>
				<th class="col-md-2 text-center" scope="col">Owner</th>
				<th class="col-md-2 text-center" scope="col">Expired on</th>
				<th class="col-md-3" scope="col">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
<?php



foreach ($expired as $res)
{
	
	$view_icon='<i class="fas fa-eye"></i>';
	$usage_icon='<i class="fas fa-chart-pie"></i>';
	$access_icon='<i class="fas fa-external-link-square-alt"></i>';
	$button_link=$button_links[$res['project_type']];
	$update_icon='<i class="fas fa-pencil-alt"></i>';

	if ($res['project_type']==0)
	{
		$projectLink="https://schema.imsi.athenarc.gr?r=software/index&selected_project=". $res['name'];
		$projectTarget='_blank';
		$project_icon='<i class="fa fa-bolt" aria-hidden="true"></i>';
		$title='On-demand batch computation';
		
	}
	else if ($res['project_type']==1) 
	{
		$projectLink=Url::to(['/project/configure-vm','id'=>$res['id']]);
		$projectTarget='_self';
		$project_icon='<i class="fa fa-server" aria-hidden="true"></i>';
		$title='24/7 Service';

	}
	else if ($res['project_type']==3) 
	{
		$projectLink=Url::to(['/project/machine-compute-configure-vm','id'=>$res['id']]);
		$projectTarget='_self';
		$project_icon='<i class="fa fa-server" aria-hidden="true"></i>';
		$title='On-demand computation machines';

	}
	else
	{
		$projectLink=Url::to(['/site/under-construction']);
		$projectTarget='_self';
		$project_icon='<i class="fa fa-database" aria-hidden="true"></i>';
		$title='Cold Storage';

	}


?>
			<tr class="active" style="font-size: 14px;">
				<td class="col-md-2" style="vertical-align: middle!important;"> <?=$res['name']?></td>
				<td class="col-md-2" style="padding-left: 20px;vertical-align: middle!important;" title="<?=$title?>"><?=$project_icon?></td>
				<td class="col-md-2 text-center" style="vertical-align: middle!important;"><?=$res[0]?></td>
				<td class="col-md-2 text-center" style="vertical-align: middle!important;"><?=$res[1]?></td>
				<td class="col-md-3 text-right">
					<?=Html::a("$view_icon Details",['/project/view-request-user','id'=>$res['id'],'return'=>'index','expired'=>1],['class'=>'btn btn-secondary btn-md'])?> 
					
				</td>
			</tr>

<?php
}
?>
			</tbody>
		</table>
	</div> <!--table-responsive-->

<?php
}
else
{
?>
	<div class="col-md-12"><h4>You do not currently have any expired projects.</h4></div>
<?php
}
?>

</div> 
