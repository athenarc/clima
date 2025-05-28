<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var $openstack \app\models\Openstack */
?>

<h2>OpenStack API options</h2>

<div class="col-md-8">
    <?php $form = ActiveForm::begin([
        'id' => 'openstack-form',
        'action' => ['administration/save-openstack'],
        'method' => 'post',
    ]); ?>

    <?= $form->field($openstack, 'keystone_url') ?>
    <?= $form->field($openstack, 'nova_url') ?>
    <?= $form->field($openstack, 'glance_url') ?>
    <?= $form->field($openstack, 'neutron_url') ?>
    <?= $form->field($openstack, 'cinder_url') ?>
    <?= $form->field($openstack, 'tenant_id')->passwordInput() ?>
    <?= $form->field($openstack, 'internal_net_id')->passwordInput() ?>
    <?= $form->field($openstack, 'floating_net_id')->passwordInput() ?>
    <?= $form->field($openstack, 'cred_id')->passwordInput() ?>
    <?= $form->field($openstack, 'cred_secret')->passwordInput() ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
