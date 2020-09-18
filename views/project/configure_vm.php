<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title="Machine Creation";
echo Html::CssFile('@web/css/project/vm-configure.css');
$this->registerJsFile('@web/js/project/vm-configure.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$helpLink=Html::a('&nbsp;guide&nbsp;','https://confluence.atlassian.com/bitbucketserver/creating-ssh-keys-776639788.html',["target"=>'_blank']);
$info='<i class="fas fa-info-circle"></i>';

?>

<div class="row">
	<div class="col-md-9 headers">
		<?=Html::encode($this->title)?>
	</div>
	<div class="col-md-3 form-group" style="text-align: right; padding-top: 5px;">
            <?= Html::submitButton('<i class="fas fa-check"></i> Create', ['class' => 'btn btn-primary create-vm-btn']) ?>
            <?= Html::a('<i class="fas fa-arrow-left"></i> Back', ['/project/index'], ['class'=>'btn btn-default']) ?>
    </div>
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
	<div class="col-md-2 tab-label"><b>Additional storage:</b></div><div class="col-md-10 tab-value"><?=$service->storage?> GB</div>
</div>

<?php $form=ActiveForm::begin($form_params);
?>
	<h3>Select Operating System:</h3> 
	<?= $form->field($model, 'image_id')->dropDownList($imageDD)->label('') ?>

	
	<!-- <?= $form->field($model, 'public_key')->textarea(['rows'=>10])->label('') ?> -->
	<h3>Upload a public SSH key:</h3>
	<div class="row"><span style="padding-top: 3px; margin-right: 5px; padding-left: 15px;"> <?=$info?></span> A public SSH key is required to access the new machine. Follow this <?=$helpLink?> to create a public SSH key.</div>
	<?= $form->field($model, 'keyFile')->fileInput()->label('') ?>
	
	<div class="loading">
		<div class="row"><div class="col-md-12"><div class="loading-inner"><b>Creating VM <i class="fas fa-spinner fa-spin"></i></b></div></div></div>
		<div class="row"><div class="col-md-12"><div class="loading-inner"><b>This may take a few minutes. Please do not navigate away from this page.</b></div></div></div>
		<div class="row">&nbsp;</div>
	</div>
<?php
	ActiveForm::end();
?>
