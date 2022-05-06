<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\Headers;

$this->title = "Machine Creation";


echo Html::CssFile('@web/css/project/vm-configure.css');
$this->registerJsFile('@web/js/project/vm-configure.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$helpLink=Html::a('&nbsp;guide&nbsp;','https://docs.rightscale.com/faq/How_Do_I_Generate_My_Own_SSH_Key_Pair.html',["target"=>'_blank']);
$info='<i class="fas fa-info-circle"></i>';

$back_action=($backTarget=='m')?['/project/machine-compute-access-project','id'=>$project_id]:['project/index'];

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'Machine Creation', 
	'buttons'=>
	[
		['fontawesome_class'=>'<i class="fas fa-check"></i>','name'=> 'Create',
		'options'=>['class'=>'btn btn-primary create-vm-btn'], 'type'=>'submitButton' ],
		['fontawesome_class'=>'<i class="fas fa-arrow-left"></i>','name'=> 'Back', 'action'=>$back_action,
		 'options'=>['class'=>'btn btn-default'], 'type'=>'a'] 
	],
])
?>
<?Headers::end()?>




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
 

<?php $form=ActiveForm::begin($form_params);
?>
	<h3>Select Operating System:</h3> 
	<?= $form->field($model, 'image_id')->dropDownList($imageDD)->label('') ?>

	
	<!-- <?= $form->field($model, 'public_key')->textarea(['rows'=>10])->label('') ?> -->
	<h3>Upload a public SSH key:</h3>
	<div class="row"><span style="padding-top: 3px; margin-right: 5px; padding-left: 15px;"> <?=$info?></span> A public SSH key (RSA or PEM) is required to access the new machine. Follow this <?=$helpLink?> to create a public SSH key.</div>
	<?= $form->field($model, 'keyFile')->fileInput()->label('') ?>
	
	<div class="loading">
		<div class="row"><div class="col-md-12"><div class="loading-inner"><b>Creating VM <i class="fas fa-spinner fa-spin"></i></b></div></div></div>
		<div class="row"><div class="col-md-12"><div class="loading-inner"><b>This may take a few minutes. Please do not navigate away from this page.</b></div></div></div>
		<div class="row">&nbsp;</div>
	</div>
<?php
	ActiveForm::end();
?>
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-warning" role="alert">
          Currently HYPATIA does not possess a backup service. To ensure the safety of your data, you should backup your data in a source outside HYPATIA.
        </div>
    </div>
</div>  

<div class="modal instructions fade"  tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
   			<div class="modal-header">
			<h5 class="modal-title" id="exampleModalLongTitle"><strong>Create additional storage</strong></h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true" class="btn-cancel-modal">&times;</span>
			</button>
			</div>
			<div class="modal-body">
				<li>Add a request for a storage volume. Select hot in the volume type dropdown.</li>
				<li>Upon approval of the request, the volume with specified size is created.</li>
				<li>You may then manage the storage volume by clicking the 'Access' button of the created colume in the main page. </li>
				<li> The 'Access' button opens a page, where storage volumes can be attached to active VMs. </li>
			</div>
		</div>
	</div>
</div>