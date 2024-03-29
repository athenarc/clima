<?php

use yii\helpers\Html;
use app\components\Headers;

echo Html::CssFile('@web/css/project/vm-details.css');
$this->registerJsFile('@web/js/project/vm-details-machines.js', ['depends' => [\yii\web\JqueryAsset::className()]]);


$this->title="VM details";

$back_icon='<i class="fas fa-arrow-left"></i>';
$x_icon='<i class="fas fa-times"></i>';
$console_icon='<i class="fas fa-external-link-square-alt"></i>';
$start_icon='<i class="fas fa-play"></i>';
$shutdown_icon='<i class="fas fa-stop"></i>';
$reboot_icon='<i class="fas fa-power-off"></i>';
$info_icon='<i class="fas fa-question-circle"></i>';
$string = explode(" ", $model->image_name)[0];
$unlink_icon='<i class="fas fa-unlink"></i>';
$link_icon='<i class="fas fa-link"></i>';
$username='ubuntu';
$consoleLink=$model->consoleLink;
$disable_cons_btn='';
$disable_del_btn='';
$disable_start='';
$disable_shutdown='';
$disable_reboot='';

$back_action=($backTarget=='m')?['/project/machine-compute-access-project','id'=>$model->project_id]:['project/index'];

if (empty($consoleLink))
{
	$disable_cons_btn=' disabled';
}

if (!$model->serverExists)
{
	$disable_del_btn=' disabled';
}

if ($model->status=='ACTIVE')
{
	$disable_start=' disabled';
}

if ($model->status=='SHUTOFF')
{
	$disable_shutdown=' disabled';
	$disable_reboot=' disabled';
}


Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'VM details', 
	'buttons'=>
	[
		['fontawesome_class'=>$start_icon,'name'=> 'Start', 'action'=> 'javascript:void(0);', 
			'options'=>['class'=>'btn btn-secondary start-btn' . $disable_start], 'type'=>'a' ],
		['fontawesome_class'=>$shutdown_icon,'name'=> 'Shut down', 'action'=> 'javascript:void(0);', 
			'options'=>['class'=>'btn btn-secondary shutdown-btn' . $disable_shutdown], 'type'=>'a' ],
		['fontawesome_class'=>$reboot_icon,'name'=> 'Reboot', 'action'=> 'javascript:void(0);', 
			'options'=>['class'=>'btn btn-secondary reboot-btn' . $disable_reboot], 'type'=>'a' ],
		['fontawesome_class'=>$console_icon,'name'=> 'Console', 'action'=> $consoleLink, 
		'options'=>['class'=>'btn btn-secondary'. $disable_cons_btn, 'target'=>'_blank'], 'type'=>'a' ],
		['fontawesome_class'=>$x_icon,'name'=> 'Delete', 'button_name'=>"button", 'type'=>'tag', 
		'options'=>['class'=>'btn btn-danger delete-vm-btn' . $disable_del_btn]] ,
		['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>$back_action, 'type'=>'a', 
		'options'=>['class'=>'btn btn-default']] 
	],
])
?>
<?Headers::end()?>



<div class="row first-info">
	<span class="col-md-2">
		<strong>Image:</strong>
	</span>
	<span class="col-md-2">
		<?=$model->image_name?>
	</span>
</div>
<div class="row">
	<span class="col-md-2">
		<strong>IP (for SSH):</strong>
	</span>
	<span class="col-md-2">
		<?=$model->ip_address?>
	</span>
</div>
<?php
if (!isset(Yii::$app->params['windowsImageIDs'][$model->image_id]))
{
?>
	<div class="row">
		<span class="col-md-2">
			<strong>Username:</strong>
		</span>
		<span class="col-md-2">
			<?=$username?>
		</span>
	</div>
<?php
}?>

<div class="row">
	<span class="col-md-2">
		<strong>Status:</strong>
	</span>
	<span class="col-md-2" id='status-span-machines'>
		<?=$model->status?>
	</span>
</div>

<div class="row"><div class="col-md-12"><h3>Machine specification:</h3></div></div>
<div class="row">
	<div class="col-md-2 tab-label"><b>CPU cores:</b></div><div class="col-md-1 tab-value"><?=$service->num_of_cores?></div>
</div>
<div class="row">
	<div class="col-md-2 tab-label"><b>RAM:</b></div><div class="col-md-1 tab-value"><?=$service->ram?> GB</div>
</div>
<div class="row">
	<div class="col-md-2 tab-label"><b>Operating system disk:</b></div><div class="col-md-1 tab-value"><?=$service->disk?> GB</div>
</div>
<div class="row">
	<div class="col-md-2 tab-label"><b>Additional storage:</b></div>
	<div class="col-md-10 tab-value">
		<?php 
		if(!empty($additional_storage))
		{
			foreach($additional_storage as $storage)
			{?>
				<span>
					<?=$storage['name']?> (<?=$storage['size']?> GB) on <?=$storage['mountpoint']?> &nbsp;
					<!-- <?=Html::a("$unlink_icon",['project/detach'], ['class'=>'btn btn-danger btn-sm','title'=>'Detach' ])?> -->	
				</span> 
				<br>
			<?php
			}
		}
		else
		{?>
			<span>
				No volume attached <?=Html::a("$info_icon",null, ['class'=>'instructions-for-volume'])?>
				<!-- <?=Html::a("$link_icon Attach",['project/detach'], ['class'=>'btn btn-danger'])?> -->
			</span>
			<br>
		<?php
		}?>
	</div>
</div>
<?=Html::hiddenInput('hidden_vm_id',$model->vm_id,['id'=>'hidden_vm_machines_field']) ?>

<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>

<div class="row">
    <div class="col-md-12">
        <div class="alert alert-warning" role="alert">
          Currently HYPATIA does not possess a backup service. To ensure the safety of your data, you should backup your data in a source outside HYPATIA.
        </div>
    </div>
</div>  

<div class="row"><h3 style="padding-left: 15px;">Useful tips:</h3></div>

<div class="row">
	<div class="col-md-4">
		<div class="credentials-box">
			<div class="credentials-box-header"><div class='text-center'><h3><?=$info_icon?> Additional Storage</h3></div></div>
			<div class="credentials-box-content">
				<div class="row" style="padding-left: 15px;">
				In order to partition, format and mount the additional storage, which is attached to /dev/vdb, follow this <?=Html::a('guide',['site/additional-storage-tutorial'], ['target'=>'_blank'])?>.
				</div>
				<div class="row">&nbsp;</div>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="credentials-box">
			<div class="credentials-box-header"><div class='text-center'><h3> <?=$info_icon?> Graphical UI</h3></div></div>
			<div class="credentials-box-content">
				If your VM supports a Graphical User Interface (GUI) you need to use a  <?=Html::a('VNC client','https://www.privateshell.com/docs/howto_vnc.htm', ['target'=>'_blank'])?> to connect to it.
			</div> 
						
		</div>
	</div>
	<div class="col-md-4">
		<div class="credentials-box">
			<div class="credentials-box-header"><div class='text-center'><h3> <?=$info_icon?> Extra Users</h3></div></div>
			<div class="credentials-box-content">
				It is possible to give access to the VM to other users by following this <?=Html::a('tutorial',['site/ssh-tutorial'], ['target'=>'_blank'])?>
			</div>
		</div>
	</div>
</div>
<div class="row">&nbsp;</div>
<!-- <div class="row"><div class="col-md-12 text-center">In order to partition, format and mount the additional storage (if applicable), which is attached to /dev/vdb, please follow this <?=Html::a('guide','https://medium.com/@sh.tsang/partitioning-formatting-and-mounting-a-hard-drive-in-linux-ubuntu-18-04-324b7634d1e0', ['target'=>'_blank'])?></div></div>
<div class="row"><div class="col-md-12 text-center">It is possible to give access to the VM to other users by following this <?=Html::a('tutorial',['site/ssh-tutorial'], ['target'=>'_blank'])?></div></div>
<div class="row">&nbsp;</div>
<div class="row"><div class="col-md-12 text-center">If your VM supports a Graphical User Interface (GUI) you need to use a  <?=Html::a('VNC client','https://www.privateshell.com/docs/howto_vnc.htm', ['target'=>'_blank'])?> to connect to it.</div> </div>
<div class="row">&nbsp;</div> -->

<?php
if (isset(Yii::$app->params['windowsImageIDs'][$model->image_id]))
{
?>

<div class="row"><div class="col-md-12 text-center">The VM you configured contains a trial version of Windows and you must <?=Html::a('activate it','https://www.checkyourlogs.net/how-to-activate-windows-server-2019-evaluation-edition-with-vlsc-mak-key-or-retail-key-windowsserver-mvphour/', ['target'=>'_blank'])?> it using your own licence key.</div> </div>
<div class="row">&nbsp;</div>



<?php
	if (!$model->read_win_password)
	{
?>
		<div class="row"><div class="col-md-12 text-center"><?= Html::tag("button","Retrieve VM password", ['class'=>'btn btn-info retrieve-pass-btn'])?></div></div>
		<div class="row">&nbsp;</div>
		<div class="row">
			<div class="col-md-12 text-center password-div">
				<div class="row retrieving-loading">
					<div class="col-md-12">
						<b>Retrieving password.<i class="fas fa-spinner fa-spin"></i></b>
					</div>
				</div>
				<div class="row retrieving-loading">
					<div class="col-md-12">
						<b>This may take a few minutes. Please do not navigate away from this page.</b>
					</div>
				</div>
			</div>
		</div>
		<div class="row">&nbsp;</div>

		<div class='pass-warning-div'>
			<div class="row text-center"><div class="col-md-offset-2 col-md-8 alert alert-danger">Please note this password now. Once you navigate away from this page you will not be able to retrieve it again.</div></div>
			<div class="row pass-warning">&nbsp;</div>
		</div>
		<?=Html::hiddenInput('request_id',$requestId,['id'=>'hidden_request_id'])?>

<?php
	}
}
?>


<div class="modal delete fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="delete-modal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
   			<div class="modal-header">
			<h5 class="modal-title" id="exampleModalLongTitle">Confirm delete</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true" class="btn-cancel-modal">&times;</span>
			</button>
			</div>
			<div class="modal-body">Are you sure you want to delete this VM?</div>
			<div class="modal-loading"><b>Deleting <i class="fas fa-spinner fa-spin"></i></b></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary btn-cancel-modal" data-dismiss="modal">Cancel</button>
				<?=Html::a("$x_icon Delete",['/project/delete-vm-machines','id'=>$requestId],['class'=>"btn btn-danger confirm-delete"])?>
			</div>
		</div>
	</div>
</div>

<div class="modal instructions fade" tabindex="-1" role="dialog"  aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
   			<div class="modal-header">
			<h5 class="modal-title" id="exampleModalLongTitle"><strong>Create additional storage</strong></h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true" class="btn-cancel-modal">&times;</span>
			</button>
			</div>
			<div class="modal-body">
				<li>Add a request for a storage volume.</li>
				<li>Upon approval of the request, the volume with the specified size is created.</li>
				<li>You may then manage the storage volume by clicking the 'Access' button of the created colume in the main page. </li>
				<li> The 'Access' button opens a page, where storage volumes can be attached to active VMs. </li>
			</div>
		</div>
	</div>
</div>