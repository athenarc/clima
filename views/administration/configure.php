<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\components\MagicSearchBox;
use webvimark\modules\UserManagement\models\User as Userw;

/* @var $this yii\web\View */
/* @var $model app\models\ServiceRequest */
/* @var $form ActiveForm */

echo Html::CssFile('@web/css/site/configure.css');
$this->registerJsFile('@web/js/site/configure.js', ['depends' => [\yii\web\JqueryAsset::className()]] );

$this->title="System Configuration";

if (!empty($success))
{
	echo '<div class="alert alert-success" role="alert"><div class="row"><div class="col-md-12">';
	echo $success;
	echo '</div></div></div>';
}

// print_r($activeButtons);
// print_r($activeTabs);
// exit(0);

?>

<div class="row"><h1 class="col-md-12"><?= Html::encode($this->title) ?></h1></div>

<div class="row">&nbsp;</div>

<div class="form_container">

	<?php $form = ActiveForm::begin($form_params); ?>
	<div class="row category-tabs">
		<div class="col-md-2 tab-button <?=$activeButtons[0]?>" data-controlling="tab-general" id='general-button' ><div class="button-text">General</div></div>
		<div class="col-md-3 tab-button <?=$activeButtons[1]?>" data-controlling="tab-ondemand-autoaccept"  id='ondemand-button' ><div class="button-text"> On-demand computation projects</div></div>
		<div class="col-md-3 tab-button <?=$activeButtons[2]?>" data-controlling="tab-service-autoaccept"  id='service-button'><div class="button-text"> 24/7 service projects</div></div>
		<div class="col-md-2 tab-button <?=$activeButtons[3]?>" data-controlling="tab-cold-storage-autoaccept" id='cold-button'><div class="button-text"> Cold storage projects</div></div>
		<div class="col-md-2 tab-button <?=$activeButtons[4]?>" data-controlling="tab-email-configuration" id='email-button'><div class="button-text"> SMTP configuration</div></div>

	</div>

	<div class="row">&nbsp;</div>
	
	<div class="row">

		<div class="col-md-3"><?=Html::dropDownList('currentUserType',$hiddenUser,$userTypes,
		['id'=>'typeDropdown', 'class'=>(empty($activeTabs[0]) && empty($activeTabs[4]) ? '' : 'dropdown-hidden'), ])?>
		<?=Html::hiddenInput('previousUserType', $hiddenUser,['id'=>'hidden_user_type'])?> 
		<?=Html::hiddenInput('hidden-active-button', $hiddenActiveButton,['id'=>'hidden_active_button'])?> 
		</div>
	</div>
	<div class="tab-general tab <?=$activeTabs[0]?>">
		<div class="row"><h2 class="col-md-12">General request options</h2></div>
		<?= $form->field($general, 'reviewer_num') ?>
		<?= $form->field($general,'home_page')->dropDownList($pages,['prompt'=>'Please select a page', 'disabled'=>(empty($pages))? true : false ])?>
		<?= $form->field($general,'privacy_page')->dropDownList($pages,['prompt'=>'Please select a page', 'disabled'=>(empty($pages))? true : false ])?>
		<?= $form->field($general,'help_page')->dropDownList($pages,['prompt'=>'Please select a page', 'disabled'=>(empty($pages))? true : false ])?>
		<?= Html::a('Manage pages', ['/administration/manage-pages'], ['class'=>'btn btn-secondary']) ?>
		<div class="row">&nbsp;</div>
		<div class="row"><h2 class="col-md-12">OpenStack API options</h2></div>
		<?=$form->field($general, 'os_keystone_url') ?>
		<?=$form->field($general, 'os_nova_url') ?>
		<?=$form->field($general, 'os_glance_url') ?>
		<?=$form->field($general, 'os_neutron_url') ?>
		<?=$form->field($general, 'os_cinder_url') ?>
		<?=$form->field($general, 'os_tenant_id')->passwordInput() ?>
		<?=$form->field($general, 'os_floating_net_id')->passwordInput() ?>
		<?=$form->field($general, 'os_cred_id')->passwordInput() ?>
		<?=$form->field($general, 'os_cred_secret')->passwordInput() ?>


	</div>

	<div class="tab-ondemand-autoaccept tab <?=$activeTabs[1]?>">
		<div class="row"><h2 class="col-md-12">Upper limits for approval without RAC review for on-demand computation projects</h2></div>
		<?= $form->field($ondemand, 'num_of_jobs') ?>
		<?= $form->field($ondemand, 'time_per_job') ?>
		<?= $form->field($ondemand, 'cores') ?>
		<?= $form->field($ondemand, 'ram') ?>
		<?= $form->field($ondemand, 'storage') ?>
		
		

	
	<div class="row"><h2 class="col-md-12">Upper limits for resources for on-demand computation projects</h2></div>
		<?= $form->field($ondemandLimits, 'num_of_jobs') ?>
		<?= $form->field($ondemandLimits, 'time_per_job') ?>
		<?= $form->field($ondemandLimits, 'cores') ?>
		<?= $form->field($ondemandLimits, 'ram') ?>
		<?= $form->field($ondemandLimits, 'storage') ?>
	</div>

	<div class="tab-service-autoaccept tab <?=$activeTabs[2]?>">
		<div class="row"><h2 class="col-md-12">Upper limits for approval without RAC review for 24/7 service projects</h2></div>
		<?= $form->field($service, 'vms') ?>
		<?= $form->field($service, 'cores') ?>
		<?= $form->field($service, 'ips') ?>
		<?= $form->field($service, 'ram') ?>
		<?= $form->field($service, 'storage') ?>


		<div class="row"><h2 class="col-md-12">Upper limits for resources  for 24/7 service projects</h2></div>
		<?= $form->field($serviceLimits, 'vms') ?>
		<?= $form->field($serviceLimits, 'cores') ?>
		<?= $form->field($serviceLimits, 'ips') ?>
		<?= $form->field($serviceLimits, 'ram') ?>
		<?= $form->field($serviceLimits, 'storage') ?>
	</div>
	
	<div class="tab-cold-storage-autoaccept tab <?=$activeTabs[3]?>">
		<div class="row"><h2 class="col-md-12">Upper limits for approval without RAC review for cold storage projects</h2></div>
		<?= $form->field($coldStorage, 'storage') ?>
		<div class="row"><h2 class="col-md-12">Upper limits for resources for cold storage projects</h2></div>
		<?= $form->field($coldStorageLimits, 'storage') ?>
	</div>

	<div class="row tab-email-configuration tab <?=$activeTabs[4]?>">
		<div class="row">&nbsp;</div>
		<div class='col-md-8'>
		<?= $form->field($smtp, 'encryption') ?>
		<?= $form->field($smtp, 'host') ?>
		<?= $form->field($smtp, 'port') ?>
		<?= $form->field($smtp, 'username') ?>
		<?= $form->field($smtp, 'password')->passwordInput()?>
		</div>
		<div class="col-md-12" style="margin-bottom: 50px;">	<?= Html::a('<i class="fas fa-envelope-open-text"></i> Test Configuration', ['/administration/test-smtp-configuration'], ['class'=>'btn btn-secondary']) ?>
		</div>

	</div> 

	<div class="form-group">
        <?= Html::submitButton('<i class="fas fa-check"></i> Save', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<i class="fas fa-times"></i> Cancel', ['/administration/index'], ['class'=>'btn btn-default']) ?>
    </div>
	<?php ActiveForm::end(); ?>
</div>