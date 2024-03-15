<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;
use webvimark\modules\UserManagement\models\User as Userw;
use app\models\Project;
use app\models\ProjectRequest;

$this->title="Notification History";

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
		<?= Html::a("$back_icon Back to Projects", ['project/index'], ['class'=>'btn btn-default']) ?>
	</div>
</div>

<div class="row">&nbsp;</div>

<div class="row"><div class="col-md-12 text-center"><?= LinkPager::widget(['pagination' => $pages]) ?></div></div>
<div class="row">&nbsp;</div>

<?php

if (!empty($notifications))
{
//table-striped didn't show the correct color for every second same type notification
?>

<table class="table table-responsive table">
	<thead>
		<tr>
			<th class="col-md-10">Notification message</th>
			<th class="col-md-2">Created at</th>
		</tr>
	</thead>
	<tbody>
		<?php

			
		foreach ($notifications as $notif)
		{
		?>
			
			<tr class="<?=$typeClass[$notif['type']]?>">
			<?php 
			if ($notif['type']==0 && Userw::hasRole('Admin')){
				$url_split = explode('=', $notif['url']);
				$ticket_id = $url_split[2];
				$url = 'index.php?r=ticket-admin%2Fanswer&id='.$ticket_id;
            ?>
                <td class="col-md-10"><?=Html::a($notif['message'],$url)?></th>
				<td class="col-md-2"><?= date("j-m-Y, H:i:s",strtotime($notif['created_at']))?></td>
            <?php 
			}elseif ($notif['type']==0) {
				$url_split = explode('=', $notif['url']);
				$ticket_id = $url_split[2];
				$url = 'index.php?r=ticket-user%2Fview&id='.$ticket_id;
			?>
                <td class="col-md-10"><?=Html::a($notif['message'],$url)?></th>
				<td class="col-md-2"><?= date("j-m-Y, H:i:s",strtotime($notif['created_at']))?></td>
			<?php
			}elseif($notif['type']==-1 || $notif['type']==2){
				$current_user=Userw::getCurrentUser()['id'];
				$message_split = explode("'", $notif['message']);
				$project_name = $message_split[1];
				$project=Project::find()->where(['name'=>$project_name])->one();
				$latest_request=ProjectRequest::find()->where(['id'=>$project['latest_project_request_id']])->one();
				$owner=$latest_request['submitted_by'];
				//if the current user is the owner of the project, redirect him to his requests
				if ($owner==$current_user){
					?>
						<td class="col-md-10"><?=Html::a($notif['message'],$notif['url'])?>
						<td class="col-md-2"><?= date("j-m-Y, H:i:s",strtotime($notif['created_at']))?></td>
					<?php
				//if the currect user is not the owner redirect him to project details page
				} else{
					?>
						<td class="col-md-10"><?=Html::a($notif['message'],['/project/view-request-user','id'=>$project['latest_project_request_id']])?>
						<td class="col-md-2"><?= date("j-m-Y, H:i:s",strtotime($notif['created_at']))?></td>
					<?php
				}
			}elseif($notif['type']!=0){
			?>
				<td class="col-md-10"><?=Html::a($notif['message'],$notif['url'])?>
				<td class="col-md-2"><?= date("j-m-Y, H:i:s",strtotime($notif['created_at']))?></td>
			<?php
			}?>   
			</tr>
		<?php 		
		}
		?>
		
	</body>
</table>

<?php
}

else
{
?>

	<div class="row"><div class='col-md-12'><h3>There are no notifications in your history.</h3></div></div>

<?php	
}



