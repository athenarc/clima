<?php

use yii\helpers\Html;

echo Html::CssFile('@web/css/project/vm-details.css');
$this->registerJsFile('@web/js/project/vm-details.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$this->title="VM details";

$back_icon='<i class="fas fa-arrow-left"></i>';
$x_icon='<i class="fas fa-times"></i>';

?>
<div class="row"><div class="col-md-11 headers"><?=Html::encode($this->title)?></div><div class="col-md-1"><?= Html::a("$back_icon Back", ['/project/index'], ['class'=>'btn btn-default']) ?></div></div>

<div class="row">&nbsp;</div>
<div class="credentials-box">
	<div class="credentials-box-header"><div class='text-center'><h3>SSH login details</h3></div></div>
	<div class="credentials-box-content">
		<div class="row">
			<div class="col-md-5">
				<strong>Image:</strong>
			</div>
			<div class="col-md-2">
				<?=$model->image_name?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-5">
				<strong>IP Address (SSH):</strong>
			</div>
			<div class="col-md-2">
				<?=$model->ip_address?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-5">
				<strong>Username:</strong>
			</div>
			<div class="col-md-7">
				Use the name of the image selected e.g. (for Ubuntu use "ubuntu" as username, for Debian use "debian", etc.)
			</div>
		</div>
		<div class="row">
			<div class="col-md-5">
				<strong>Additional storage attached to (if applicable):</strong>
			</div>
			<div class="col-md-7">
				/dev/vdb
			</div>
		</div>
	</div>
</div>

<div class="row">&nbsp;</div>
<div class="row"><div class="col-md-12 text-center">In order to partition, format and mount the additional storage (if applicable), which is attached to /dev/vdb, please follow this <?=Html::a('guide','https://medium.com/@sh.tsang/partitioning-formatting-and-mounting-a-hard-drive-in-linux-ubuntu-18-04-324b7634d1e0', ['target'=>'_blank'])?></div></div>
<div class="row"><div class="col-md-12 text-center">It is possible to give access to the VM to other users by following this <?=Html::a('tutorial',['site/ssh-tutorial'], ['target'=>'_blank'])?></div></div>
<div class="row">&nbsp;</div>
<div class="row"><div class="col-md-12 text-center">If your VM supports a Graphical User Interface (GUI) you need to use a  <?=Html::a('VNC client','https://www.privateshell.com/docs/howto_vnc.htm', ['target'=>'_blank'])?> to connect to it.</div> </div>
<div class="row">&nbsp;</div>

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
<div class="row"><div class="col-md-12 text-center"><?= Html::a("Open console",$model->consoleLink, ['class'=>'btn btn-secondary', 'target'=>'_blank'])?></div></div>
<div class="row">&nbsp;</div>

<div class="row"><div class="col-md-12 text-center"><h3>I do not need this VM anymore:</h3></div></div>
<div class="row"><div class="col-md-12 text-center"><?= Html::tag("button","Delete VM", ['class'=>'btn btn-danger delete-vm-btn'])?></div></div>

<div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="delete-modal" aria-hidden="true">
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
				<?=Html::a("$x_icon Delete",['/project/delete-vm','id'=>$requestId],['class'=>"btn btn-danger confirm-delete"])?>
			</div>
		</div>
	</div>
</div>
