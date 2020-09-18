<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title="Configure new VM for project $service->name";
echo Html::CssFile('@web/css/project/vm-configure.css');
$this->registerJsFile('@web/js/project/vm-configure.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$helpLink=Html::a('how-to','https://confluence.atlassian.com/bitbucketserver/creating-ssh-keys-776639788.html',["target"=>'_blank'])
?>

<div class="row">
	<div class="col-md-12 headers">
		<?=Html::encode($this->title)?>
	</div>
</div>

<div class="row"><div class="col-md-12"><h3>VM configuration:</h3></div></div>
<div class="row">
	<div class="col-md-2 tab-label">CPU cores:</div><div class="col-md-1 tab-value"><strong><?=$service->num_of_cores?></strong></div>
</div>
<div class="row">
	<div class="col-md-2 tab-label">RAM:</div><div class="col-md-1 tab-value"><strong><?=$service->ram?> GB</strong></div>
</div>
<div class="row">
	<div class="col-md-2 tab-label">VM disk:</div><div class="col-md-1 tab-value"><strong><?=$service->disk?> GB</strong></div>
</div>
<div class="row">
	<div class="col-md-2 tab-label">Additional storage:</div><div class="col-md-10 tab-value"><strong><?=$service->storage?> GB</strong></div>
</div>

<?php

	$form=ActiveForm::begin($form_params);
?>
	<h3>Please select an Operating System image for the new VM:</h3> 
	<?= $form->field($model, 'image_id')->dropDownList($imageDD)->label('') ?>

	<!-- <h3>Paste a public SSH key (<?= $helpLink?>) for access to the new VM, in the box:</h3>
	<?= $form->field($model, 'public_key')->textarea(['rows'=>10])->label('') ?> -->
	<h3>Please upload a public SSH key file (<?= $helpLink?>) for access to the new VM, in the box:</h3>
	<?= $form->field($model, 'keyFile')->fileInput()->label('') ?>
	
	<div class="loading">
		<div class="row"><div class="col-md-12"><div class="loading-inner"><b>Creating VM <i class="fas fa-spinner fa-spin"></i></b></div></div></div>
		<div class="row"><div class="col-md-12"><div class="loading-inner"><b>This may take a few minutes. Please do not navigate away from this page.</b></div></div></div>
		<div class="row">&nbsp;</div>
	</div>

	<div class="form-group">
            <?= Html::submitButton('<i class="fas fa-check"></i> Submit', ['class' => 'btn btn-primary create-vm-btn']) ?>
            <?= Html::a('<i class="fas fa-times"></i> Cancel', ['/project/index'], ['class'=>'btn btn-default']) ?>
    </div>
<?php
	ActiveForm::end();
?>
