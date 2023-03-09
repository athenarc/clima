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


echo Html::CssFile('@web/css/project/request-list.css');

$this->title="Project requests";


Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'Project requests', 
])
?>
<?Headers::end()?>




<?php

if (!empty($message))
{
?>
		<div class="row"><div class="alert alert-success col-md-12" role="alert"><?=$message?></div></div>
<?php
}

?>
<div class="container-fluid">
	<div class="row">&nbsp;</div>
  	<div class="row">
    	<div class="col-md-2 col-sm-4 sidebar1 facet-sidebar">
        	<div class="card">			
				<div class="filter-content">
					<div class="list-group list-group-flush">
					<?php
						foreach ($sideItems as $item)
						{
							echo Html::a($item['text'],$item['link'],['class'=>$item['class']]);
						}
					?>
					</div>  <!-- list-group .// -->
				</div>
        	</div>
		</div>

<?php
if (!empty($results))
{
	
?>





<div class="col-md-10 main-content">
                  
<table class="table table-responsive">
	<thead>
		<tr>
			<th class="col-md-2 text-center" scope="col">Name</th>
			<th class="col-md-3 text-center" scope="col">Submitted on</th>
			<th class="col-md-2 text-center" scope="col">Project type</th>
			<th class="col-md-2 text-center" scope="col">Status</th>
			<th class="col-md-1" scope="col">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
<?php



foreach ($results as $res)
{
	$view_icon='<i class="fas fa-eye"></i>';
	// $button_link=$button_links[$res['project_type']];
	// print_r($res['name']);
	// exit(0);


?>
			<tr class="<?=$line_classes[$res['status']]?>">
				<td class="col-md-2"><?=$res['name']?></td>
				<td class="col-md-3"><?=date("F j, Y, H:i:s",strtotime($res['submission_date']))?></td>
				<td class="col-md-2"><?=$project_types[$res['project_type']] ?></td>
				<td class="col-md-2"><?=$statuses[$res['status']]?></td>
				<td class="col-md-1"><?=Html::a("$view_icon Details",['/project/view-request-user','id'=>$res['id'],'filter'=>$filter, 'expired'=>$expired, 'return'=>'user_request'],['class'=>'btn btn-primary btn-md'])?></td>
			</tr>

<?php
}
?>
	</tbody>
</table>

</div> <!--main content-->


<?php
}
else
{
?>
	<div class="col-md-10"><br /><br /><h3>There are no recorded submissions of this type.</h3></div>
<?php
}
?>
	</div><!--row-->
</div> <!--container-fluid-->

<div class="row" style='text-align: center;'><div class="col-md-12"><?= LinkPager::widget(['pagination' => $pages]) ?></div></div>