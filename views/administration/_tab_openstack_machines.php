<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var $openstackMachines \app\models\OpenstackMachines */
?>

<h2>Machine Projects OpenStack API options</h2>

<div class="col-md-8">
    <?php $form = ActiveForm::begin([
        'id' => 'openstack-machines-form',
        'action' => ['administration/save-openstack-machines'],
        'method' => 'post',
    ]); ?>

    <?= $form->field($openstackMachines, 'keystone_url') ?>
    <?= $form->field($openstackMachines, 'nova_url') ?>
    <?= $form->field($openstackMachines, 'glance_url') ?>
    <?= $form->field($openstackMachines, 'neutron_url') ?>
    <?= $form->field($openstackMachines, 'cinder_url') ?>
    <?= $form->field($openstackMachines, 'tenant_id')->passwordInput() ?>
    <?= $form->field($openstackMachines, 'internal_net_id')->passwordInput() ?>
    <?= $form->field($openstackMachines, 'floating_net_id')->passwordInput() ?>
    <?= $form->field($openstackMachines, 'cred_id')->passwordInput() ?>
    <?= $form->field($openstackMachines, 'cred_secret')->passwordInput() ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
