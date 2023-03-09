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


echo Html::CssFile('@web/css/administration/all_projects.css');
$this->registerJsFile('@web/js/administration/all_projects.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerCssFile("@web/css/administration/all_projects.css");

$this->title="All projects";


/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */
$back_icon='<i class="fas fa-arrow-left"></i>';
$new_icon='<i class="fas fa-plus-circle"></i>';
$roles=['bronze'=>'Bronze','gold'=>'Gold','silver'=>'Silver'];
$expired_active_resources_icon='<i class="fa fa-exclamation-triangle", title="This expired project has active resources"></i>';


Headers::begin() ?>
<?php echo Headers::widget(
['title'=>"All projects", 
	'buttons'=>
	[
		['fontawesome_class'=>$back_icon,'name'=> 'Project requests', 'action'=> ['/administration/index'], 'type'=>'a', 'options'=>['class'=>'btn btn-default'] ],
	],
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
				<th class="col-md-3" scope="col">Project</th>
				<th class="col-md-1" scope="col">Type</th>
				<th class="col-md-3 text-center" scope="col">Owner</th>
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
	$access_button_class='';
	$edit_button_class='';
	$triangle_icon='';
	$ondemand_access_class='';


	if ($res['project_type']==0)
	{
		
		$projectLink=$schema_url;
		if(empty($schema_url))
        {
            $ondemand_access_class='disabled';
        }
		$projectTarget='_blank';
		$project_icon='<i class="fa fa-rocket" aria-hidden="true"></i>';
		$title='On-demand batch computation project';
	}
	else if ($res['project_type']==1) 
	{
		$projectLink=Url::to(['/project/configure-vm','id'=>$res['project_id']]);
		$projectTarget='_self';
		$project_icon='<i class="fa fa-leaf" aria-hidden="true"></i>';
		$title='24/7 service project';
		if($res['louros']==true)
		{
			$edit_button_class="disabled";
			$access_button_class="disabled";
			$triangle_icon='<i class="fa fa-exclamation-triangle" aria-hidden="true" title="This project cannot be updated right now since it has been transferred from the old infrastructure to HYPATIA. This is a temporary issue and it will be resolved in a while. In the meantime, if a modification is required, please contact the HYPATIA administrators."></i>';
		}
	}
	else if ($res['project_type']==3) 
	{
		$projectLink=Url::to(['/project/machine-compute-configure-vm','id'=>$res['project_id']]);
		$projectTarget='_self';
		$project_icon='<i class="fa fa-bolt" aria-hidden="true"></i>';
		$title='On-demand computation machines project';
	}
	else if ($res['project_type']==2) 
	{
		$projectLink=Url::to(['/project/storage-volumes-admin']);
		$projectTarget='_self';
		$project_icon='<i class="fa fa-database" aria-hidden="true"></i>';
		$title="Storage volume project";
	}



?>
			<tr class="active" style="font-size: 14px;">
				<td class="col-md-3" style="vertical-align: middle!important;"> <?=$res['name']?> &nbsp; <?=$triangle_icon?> </td>
				<td class="col-md-1" style="padding-left: 20px; vertical-align: middle!important;" title="<?=$title?>"><?=$project_icon ?></td>
				<td class="col-md-3 text-center" style="vertical-align: middle!important;"><?=$res[0]?></td>
				<td class="col-md-2 text-center" style="vertical-align: middle!important;"><?=$res[1]?> days</td>
				<td class="col-md-3 text-right">
					<?=Html::a("$view_icon Details",['/project/view-request-user','id'=>$res['id'],'return'=>'admin','expired'=>0],['class'=>'btn btn-secondary btn-md'])?> 
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
				<th class="col-md-3" scope="col">Project</th>
				<th class="col-md-1" scope="col">Type</th>
				<th class="col-md-3 text-center" scope="col">Owner</th>
				<th class="col-md-2 text-center" scope="col">Expired on</th>
				<th class="col-md-3" scope="col">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
<?php



foreach ($expired as $res)
{
	
	$view_icon='<i class="fas fa-eye"></i>';
	$reactivate_icon='<i class="fas fa-sync-alt"></i>';
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
		if (isset($active_resources[0][$res['name']]))
		{
			$active=true;
		}
		else
		{
			$active=false;
		}
		
	}
	else if ($res['project_type']==1) 
	{
		$projectLink=Url::to(['/project/configure-vm','id'=>$res['id']]);
		$projectTarget='_self';
		$project_icon='<i class="fa fa-server" aria-hidden="true"></i>';
		$title='24/7 Service';
		if (isset($active_resources[1][$res['project_id']]))
		{
			$active=true;
		}
		else
		{
			$active=false;
		}

	}
	else if ($res['project_type']==3) 
	{
		$projectLink=Url::to(['/project/machine-compute-configure-vm','id'=>$res['id']]);
		$projectTarget='_self';
		$project_icon='<i class="fa fa-server" aria-hidden="true"></i>';
		$title='On-demand computation machines';
		if (isset($active_resources[2][$res['project_id']]))
		{
			$active=true;
		}
		else
		{
			$active=false;
		}

	}
	else
	{
		$projectLink=Url::to(['/site/under-construction']);
		$projectTarget='_self';
		$project_icon='<i class="fa fa-database" aria-hidden="true"></i>';
		$title='Cold Storage';
		$active=false;

	}


?>
			<tr class="active" style="font-size: 14px;">
				<td class="col-md-3" style="vertical-align: middle!important;"> <?=$res['name']?>&nbsp;<?=$active ? $expired_active_resources_icon : ''?></td>
				<td class="col-md-1" style="padding-left: 20px;vertical-align: middle!important;" title="<?=$title?>"><?=$project_icon?></td>
				<td class="col-md-3 text-center" style="vertical-align: middle!important;"><?=$res[0]?></td>
				<td class="col-md-2 text-center" style="vertical-align: middle!important;"><?=$res[1]?></td>
				<td class="col-md-3 text-right">
					<?=Html::a("$view_icon Details",['/project/view-request-user','id'=>$res['id'],'return'=>'index','return'=>'admin','expired'=>1],['class'=>'btn btn-secondary btn-md'])?>
					<?=Html::a("$reactivate_icon Re-activate",null,['class'=>'btn btn-primary btn-md reactivate_btn', 'title'=>'Re-activate project', 'data-modal-id'=> "reactivate-$res[name]-modal"])?> 
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

<?php
foreach ($expired as $res) 
{
	$pname=$res['name'];
	?>
    <div class="modal reactivate-<?=$pname?>-modal fade" id="reactivate-<?=$pname?>-modal" tabindex="-1" role="dialog" aria-labelledby="reactivate-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Confirm re-activation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="btn-cancel-modal">&times;</span>
                </button>
                </div>
                <div class="modal-body">Are you sure you want to re-activate project '<?=$res['name']?>'</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-cancel-modal" data-dismiss="modal">Cancel</button>
                    <?=Html::a("$reactivate_icon Re-activate",['/administration/reactivate','id'=>$res['id']],['class'=>'btn btn-primary btn-md', 'title'=>'Re-activate project'])?>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>

<html>
	<head>
	<meta name="viewport" content="width=device-width">

<div class="filters-div">
	<h4 class="text-center">Filter</h4>
	<?=Html::beginForm(['administration/all-projects'],'post',['id'=>'filters-form'])?>
		<div class="row">
			<div class="col-md-12 text-center">
				<?=Html::label('By project type:')?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 text-center">
				<?=Html::dropDownList('project_type',$filters['type'],$types_dropdown, ['class'=>'types_dropdown','id'=>'types_dropdown'])?>
			</div>
		</div>

		<div class="row">&nbsp;</div>

		<div class="row">
			<div class="col-md-12 text-center">
				<?=Html::label('By owner:')?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 text-center">
				<?=Html::input(null,'username',$filters['user'],['class'=>'username_field'])?>
			</div>
		</div>

		<div class="row">&nbsp;</div>


	<?=Html::endForm()?>
</div>
</html>
</head>

