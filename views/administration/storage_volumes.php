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

$this->title="Storage volumes";

$this->registerJsFile('@web/js/project/storage-volumes.js', ['depends' => [\yii\web\JqueryAsset::className()]]);


$manage_icon='<i class="fas fa-server"></i>';
$create_icon='<i class="fas fa-database"></i>';
$back_icon='<i class="fas fa-arrow-left"></i>';
$delete_icon='<i class="fas fa-times"></i>';



Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'Storage volumes', 
	'buttons'=>
	[
		
		['fontawesome_class'=>'<i class="fas fa-arrow-left"></i>','name'=> 'Back', 'action'=>['/administration/index'],
		 'options'=>['class'=>'btn btn-default'], 'type'=>'a'],
		['fontawesome_class'=>'<i class="far fa-question-circle"></i>','name'=> 'Volume guide', 'action'=>['site/additional-storage-tutorial'],
		 'options'=>['class'=>'btn btn-primary', 'title'=>'Guide to partition, format and mount a volume, which is attached to a VM', 'target'=>'_blank'], 'type'=>'a']  
	],
])
?>
<?Headers::end()?>





<?php
if(!empty($services))
{?>
<div class="table-responsive"> <h2>Active volumes for 24/7 services</h2>
    <table class="table table-striped col-md-12">
		<thead>
			<tr>
				<th class="col-md-2" scope="col">Volume name</th>
				<th class="col-md-1" scope="col">Created at</th>
				<th class="col-md-2 text-center" scope="col">Attached to</th>
				<th class="col-md-2 text-center" scope="col">On</th>
				<th class="col-md-3" scope="col">&nbsp;</th>
			</tr>
		</thead>
		<tbody>

		<?php 
		foreach ($services as $pid => $res) 
		{
			$manage_button_class='';
			if(empty($res['vol_id']))
			{	
				$vol_id='';
				$manage_button_class='disabled';
			}
			else
			{
				$vol_id=$res['vol_id'];
			}
			
			if (!empty($res['created_at']))
			{
				$obj=new DateTime($res['created_at']);
				$cdate=$obj->format('d-m-Y');
			}
			else
			{
				$cdate='-';
			}
			
			
			?>

			<tr class="active">
			<td class="col-md-2"><?=$res['name']?><br /> (<?=$res['username']?>)</td>
			<td class="col-md-1"><?=$cdate?></td>
			<td class="col-md-2 text-center"><?=empty($res['vname'])?'-': $res['vname']?></td>
			<td class="col-md-2 text-center"><?=empty($res['mountpoint'])?'-': $res['mountpoint']?></td>
			<td class="col-md-3 text-right">
				
			<?php
				if(!empty($res['vol_id']))
				{
			?>
					<?=Html::a("$delete_icon Delete",null,['class'=>"btn btn-danger btn-md delete-volume-btn",
					'id'=>"delete-$vol_id"])?>
			<?php
				}
				else
				{
			?>
					<?=Html::a("$create_icon Create",['project/create-volume','id'=>$res['id'],'ret'=>'a'],['class'=>"btn btn-success btn-md"])?>
			<?php
				}
			?>
				<?=Html::a("$manage_icon Manage attachment",['/project/manage-volumes','id'=>$pid, 'vid'=>$vol_id, 'ret'=>'a'],['class'=>"btn btn-secondary btn-md $manage_button_class"])?>
			</td>	
			</tr>
		<?php
		}?>
		</tbody>
		</table>
</div>
<?php
}
else
{?>
	<div class="row"><div class="col-md-12">
		<h2> No active volumes for 24/7 serviced </h2>
	</div></div>	
<?php
}?>

<?php
if(!empty($expired_services))
{?>
<div class="table-responsive"> <h2>Expired volumes for 24/7 services</h2>
    <table class="table table-striped col-md-12">
		<thead>
			<tr>
				<th class="col-md-2" scope="col">Volume name</th>
				<th class="col-md-1" scope="col">Created at</th>
				<th class="col-md-2 text-center" scope="col">Attached to</th>
				<th class="col-md-2 text-center" scope="col">On</th>
				<th class="col-md-3" scope="col">&nbsp;</th>
			</tr>
		</thead>
		<tbody>

		<?php 
		foreach ($expired_services as $pid => $res) 
		{
			$manage_button_class='';
			if(empty($res['vol_id']))
			{	
				$vol_id='';
				$manage_button_class='disabled';
			}
			else
			{
				$vol_id=$res['vol_id'];
			}
			
			if (!empty($res['created_at']))
			{
				$obj=new DateTime($res['created_at']);
				$cdate=$obj->format('d-m-Y');
			}
			else
			{
				$cdate='-';
			}
			
			
			?>

			<tr class="active">
			<td class="col-md-2"><?=$res['name']?><br /> (<?=$res['username']?>)</td>
			<td class="col-md-1"><?=$cdate?></td>
			<td class="col-md-2 text-center"><?=empty($res['vname'])?'-': $res['vname']?></td>
			<td class="col-md-2 text-center"><?=empty($res['mountpoint'])?'-': $res['mountpoint']?></td>
			<td class="col-md-3 text-right">
				
			<?php
				if(!empty($res['vol_id']))
				{
			?>
					<?=Html::a("$delete_icon Delete",null,['class'=>"btn btn-danger btn-md delete-volume-btn",
					'id'=>"delete-$vol_id"])?>
			<?php
				}
				else
				{
			?>
					<?=Html::a("$create_icon Create",['project/create-volume','id'=>$res['id'],'ret'=>'a'],['class'=>"btn btn-success btn-md"])?>
			<?php
				}
			?>
				<?=Html::a("$manage_icon Manage attachment",['/project/manage-volumes','id'=>$pid, 'vid'=>$vol_id, 'ret'=>'a'],['class'=>"btn btn-secondary btn-md $manage_button_class"])?>
			</td>	
			</tr>
		<?php
		}?>
		</tbody>
		</table>
</div>
<?php
}
else
{?>
	<div class="row"><div class="col-md-12">
		<h2> No expired volumes for 24/7 services </h2>
	</div></div>	
<?php
}?>



<?php
if(!empty($machines))
{?>
	<div class="table-responsive"><h2>Active volumes for on-demand computation machines</h2>
    	<table class="table table-striped col-md-12">
			<thead>
				<tr>
					<th class="col-md-2" scope="col">Volume name</th>
					<th class="col-md-1" scope="col">Created at</th>
					<th class="col-md-2 text-center" scope="col">Attached to</th>
					<th class="col-md-2 text-center" scope="col">On</th>
					<th class="col-md-3" scope="col">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			<?php 
			foreach ($machines as $pid => $proj) 
			{
				for ($i=1; $i<=$proj['count']; $i++)
				{	
					$manage_button_class='';
					if(empty($proj[$i]['vol_id']))
					{
						$manage_button_class='disabled';
					}
					
					$vol_id='';
					if(!empty($proj[$i]['vol_id']))
					{
						$vol_id=$proj[$i]['vol_id'];
					}

					if ($proj['count']>1)
					{
						$pname=$proj['name'] . '_' . $i;
					}
					else
					{
						$pname=$proj['name'];
					}
					
					if (!empty($proj[$i]['created_at']))
					{
						$obj=new DateTime($proj[$i]['created_at']);
						$cdate=$obj->format('d-m-Y');
					}
					else
					{
						$cdate='-';
					}
					?>
					<tr class="active">
						<td class="col-md-2"><?=$pname?><br /><?=$proj[$i]['username']?></td>
						<td class="col-md-1"><?=$cdate?></td>
						<td class="col-md-2 text-center"><?=(empty($proj[$i]['vmachname'])) ? '-': $proj[$i]['vmachname']?></td>
						<td class="col-md-2 text-center"><?=(empty($proj[$i]['mountpoint'])) ? '-': $proj[$i]['mountpoint']?></td>
						<td class="col-md-3 text-right">
						
						<?php
							if(!empty($vol_id))
							{
						?>
								<?=Html::a("$delete_icon Delete",null,['class'=>"btn btn-danger btn-md delete-volume-btn",
								'id'=>"delete-$vol_id"])?>
						<?php
							}
							else
							{
						?>
								<?=Html::a("$create_icon Create",['project/create-volume','id'=>$pid,'order'=>$i,'ret'=>'a'],['class'=>"btn btn-success btn-md"])?>
						<?php
							}
						?>
						<?=Html::a("$manage_icon Manage attachment",['/project/manage-volumes','id'=>$pid,'vid'=>$vol_id,'ret'=>'a'], ['class'=>"btn btn-secondary btn-md $manage_button_class"])?>
						</td>	
					</tr>
				<?php
					}
				?>

			<?php
			}?>
		</tbody>
		</table>
</div>
<?php
}
else
{?>
	<div class="row"><div class="col-md-12">
		<h2> No active volumes for on-demand computation machines </h2>
	</div></div>	
<?php
}

if(!empty($expired_machines))
{?>
	<div class="table-responsive"><h2>Expired volumes for on-demand computation machines</h2>
    	<table class="table table-striped col-md-12">
			<thead>
				<tr>
					<th class="col-md-2" scope="col">Volume name</th>
					<th class="col-md-1" scope="col">Created at</th>
					<th class="col-md-2 text-center" scope="col">Attached to</th>
					<th class="col-md-2 text-center" scope="col">On</th>
					<th class="col-md-3" scope="col">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			<?php 
			foreach ($expired_machines as $pid => $proj) 
			{
				for ($i=1; $i<=$proj['count']; $i++)
				{	
					$manage_button_class='';
					if(empty($proj[$i]['vol_id']))
					{
						$manage_button_class='disabled';
					}
					
					$vol_id='';
					if(!empty($proj[$i]['vol_id']))
					{
						$vol_id=$proj[$i]['vol_id'];
					}

					if ($proj['count']>1)
					{
						$pname=$proj['name'] . '_' . $i;
					}
					else
					{
						$pname=$proj['name'];
					}
					
					if (!empty($proj[$i]['created_at']))
					{
						$obj=new DateTime($proj[$i]['created_at']);
						$cdate=$obj->format('d-m-Y');
					}
					else
					{
						$cdate='-';
					}
					?>
					<tr class="active">
						<td class="col-md-2"><?=$pname?><br /><?=$proj[$i]['username']?></td>
						<td class="col-md-1"><?=$cdate?></td>
						<td class="col-md-2 text-center"><?=(empty($proj[$i]['vmachname'])) ? '-': $proj[$i]['vmachname']?></td>
						<td class="col-md-2 text-center"><?=(empty($proj[$i]['mountpoint'])) ? '-': $proj[$i]['mountpoint']?></td>
						<td class="col-md-3 text-right">
						
						<?php
							if(!empty($vol_id))
							{
						?>
								<?=Html::a("$delete_icon Delete",null,['class'=>"btn btn-danger btn-md delete-volume-btn",
								'id'=>"delete-$vol_id"])?>
						<?php
							}
							else
							{
						?>
								<?=Html::a("$create_icon Create",['project/create-volume','id'=>$pid,'order'=>$i,'ret'=>'a'],['class'=>"btn btn-success btn-md"])?>
						<?php
							}
						?>
						<?=Html::a("$manage_icon Manage attachment",['/project/manage-volumes','id'=>$pid,'vid'=>$vol_id,'ret'=>'a'], ['class'=>"btn btn-secondary btn-md $manage_button_class"])?>
						</td>	
					</tr>
				<?php
					}
				?>

			<?php
			}?>
		</tbody>
		</table>
</div>
<?php
}
else
{?>
	<div class="row"><div class="col-md-12">
		<h2> No expired volumes for on-demand computation machines </h2>
	</div></div>	
<?php
}


foreach ($services as $pid =>$res) 
{
	if (empty($res['vol_id']))
	{
		continue;
	}
	else
	{
		$vol_id=$res['vol_id'];
	}
?>
    <div class="modal delete-<?=$vol_id?>-modal fade" id="delete-<?=$vol_id?>-modal" tabindex="-1" role="dialog" aria-labelledby="delete-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Confirm delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="btn-cancel-modal">&times;</span>
                </button>
                </div>
                <div class="modal-body">Are you sure you want to delete this Volume?</div>
                <div class="modal-loading">&nbsp;&nbsp;<b>Deleting <i class="fas fa-spinner fa-spin"></i></b></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-cancel-modal" data-dismiss="modal">Cancel</button>
                    <?=Html::a("$delete_icon Delete",['/project/delete-volume','vid'=>$vol_id,'ret'=>'a' ],['class'=>"btn btn-danger confirm-delete"])?>
                </div>
            </div>
        </div>
    </div>
<?php
}

foreach ($expired_services as $pid =>$res) 
{
	if (empty($res['vol_id']))
	{
		continue;
	}
	else
	{
		$vol_id=$res['vol_id'];
	}
?>
    <div class="modal delete-<?=$vol_id?>-modal fade" id="delete-<?=$vol_id?>-modal" tabindex="-1" role="dialog" aria-labelledby="delete-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Confirm delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="btn-cancel-modal">&times;</span>
                </button>
                </div>
                <div class="modal-body">Are you sure you want to delete this Volume?</div>
                <div class="modal-loading">&nbsp;&nbsp;<b>Deleting <i class="fas fa-spinner fa-spin"></i></b></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-cancel-modal" data-dismiss="modal">Cancel</button>
                    <?=Html::a("$delete_icon Delete",['/project/delete-volume','vid'=>$vol_id,'ret'=>'a' ],['class'=>"btn btn-danger confirm-delete"])?>
                </div>
            </div>
        </div>
    </div>
<?php
}

foreach ($machines as $pid => $proj) 
{
	for ($i=1; $i<=$proj['count']; $i++)
	{
		if (empty($proj[$i]['vol_id']))
		{
			continue;
		}
		else
		{
			$vol_id=$proj[$i]['vol_id'];
		}
	?>
	    <div class="modal delete-<?=$vol_id?>-modal fade" id="delete-<?=$vol_id?>-modal" tabindex="-1" role="dialog" aria-labelledby="delete-modal" aria-hidden="true">
	        <div class="modal-dialog modal-dialog-centered" role="document">
	            <div class="modal-content">
	                <div class="modal-header">
	                <h5 class="modal-title" id="exampleModalLongTitle">Confirm delete</h5>
	                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	                    <span aria-hidden="true" class="btn-cancel-modal">&times;</span>
	                </button>
	                </div>
	                <div class="modal-body">Are you sure you want to delete this Volume?</div>
	                <div class="modal-loading">&nbsp;&nbsp;<b>Deleting <i class="fas fa-spinner fa-spin"></i></b></div>
	                <div class="modal-footer">
	                    <button type="button" class="btn btn-secondary btn-cancel-modal" data-dismiss="modal">Cancel</button>
	                    <?=Html::a("$delete_icon Delete",['/project/delete-volume','vid'=>$vol_id,'ret'=>'a' ],['class'=>"btn btn-danger confirm-delete"])?>
	                </div>
	            </div>
	        </div>
	    </div>
	<?php
	}
}

foreach ($expired_machines as $pid => $proj) 
{
	for ($i=1; $i<=$proj['count']; $i++)
	{
		if (empty($proj[$i]['vol_id']))
		{
			continue;
		}
		else
		{
			$vol_id=$proj[$i]['vol_id'];
		}
	?>
	    <div class="modal delete-<?=$vol_id?>-modal fade" id="delete-<?=$vol_id?>-modal" tabindex="-1" role="dialog" aria-labelledby="delete-modal" aria-hidden="true">
	        <div class="modal-dialog modal-dialog-centered" role="document">
	            <div class="modal-content">
	                <div class="modal-header">
	                <h5 class="modal-title" id="exampleModalLongTitle">Confirm delete</h5>
	                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	                    <span aria-hidden="true" class="btn-cancel-modal">&times;</span>
	                </button>
	                </div>
	                <div class="modal-body">Are you sure you want to delete this Volume?</div>
	                <div class="modal-loading">&nbsp;&nbsp;<b>Deleting <i class="fas fa-spinner fa-spin"></i></b></div>
	                <div class="modal-footer">
	                    <button type="button" class="btn btn-secondary btn-cancel-modal" data-dismiss="modal">Cancel</button>
	                    <?=Html::a("$delete_icon Delete",['/project/delete-volume','vid'=>$vol_id,'ret'=>'a' ],['class'=>"btn btn-danger confirm-delete"])?>
	                </div>
	            </div>
	        </div>
	    </div>
	<?php
	}
}
?>
