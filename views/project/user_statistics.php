<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;

echo Html::CssFile('@web/css/project/index.css');
$this->registerCssFile("@web/css/project/index.css");

$this->title="Statistics for user $username";

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
        <?= Html::a("$back_icon Back", ['/project/index'], ['class'=>'btn btn-default']) ?>
    </div>
</div>

<div class="row">&nbsp;</div>

<div class="text-center"><h2><strong>24/7 services</strong></h2></div>

<div class="table table-bordered">
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="text-center" scope="col" style="width: 23.333%"></th>
                <th class="text-center" scope="col" style="width: 38.3333% text-align: center">As owner</th>
                <th class="text-center" scope="col" style="width: 38.3333%">As participant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th class="text-left" scope="row">Projects</th>
                <td class="text-center" ><?=$usage_owner['total_services']." (".$usage_owner['active_services']." active, ". $usage_owner['expired_services']. " expired)" ?></td>
                <td class="text-center"><?= $usage_participant['total_services']." (".$usage_participant['active_services']." active, ". $usage_participant['expired_services']. " expired)"  ?></td>
            </tr>
            <tr>
                <th class="text-left" scope="row">VMs</th>
                <td class="text-center" ><?= $usage_owner['vms_services_total']." (". $usage_owner['vms_services_active']." active)" ?></td>
                <td class="text-center"><?= $usage_participant['vms_services_total']." (". $usage_participant['vms_services_active']." active)" ?></td>
            </tr>
            <tr>
                <th class="text-left" scope="row">Virtual CPUs</th>
                <td class="text-center" ><?= (empty($usage_owner['total_services_cores'])?'0':$usage_owner['total_services_cores']) . " (" . (empty($usage_owner['active_services_cores'])?'0':$usage_owner['active_services_cores']) . " active)" ?></td>
                <td class="text-center"><?= (empty($usage_participant['total_services_cores'])?'0':$usage_participant['total_services_cores']) . " (" . (empty($usage_participant['active_services_cores'])?'0':$usage_participant['active_services_cores']) . " active)" ?></td>
            </tr>
            <tr>
                <th class="text-left" scope="row">RAM(GBs)</th>
                <td class="text-center" ><?= (empty($usage_owner['total_services_ram'])?'0':$usage_owner['total_services_ram']) . " (" . (empty($usage_owner['active_services_ram'])?'0':$usage_owner['active_services_ram']) . " active)" ?></td>
                <td class="text-center"><?= (empty($usage_participant['total_services_ram'])?'0':$usage_participant['total_services_ram']) . " (" . (empty($usage_participant['active_services_ram'])?'0':$usage_participant['active_services_ram']) . " active)" ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="row">&nbsp;</div>

<div class="text-center"><h2><strong>On-demand computation machines</strong></h2></div>

<div class="table table-bordered">
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="text-center" scope="col" style="width: 23.333%"></th>
                <th class="text-center" scope="col" style="width: 38.3333% text-align: center">As owner</th>
                <th class="text-center" scope="col" style="width: 38.3333%">As participant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th class="text-left" scope="row">Projects</th>
                <td class="text-center" ><?=$usage_owner['total_machines']." (".$usage_owner['active_machines']." active, ". $usage_owner['expired_machines']. " expired)" ?></td>
                <td class="text-center"><?= $usage_participant['total_machines']." (".$usage_participant['active_machines']." active, ". $usage_participant['expired_machines']. " expired)"  ?></td>
            </tr>
            <tr>
                <th class="text-left" scope="row">VMs</th>
                <td class="text-center" ><?= $usage_owner['vms_machines_total']." (". $usage_owner['vms_machines_active']." active)" ?></td>
                <td class="text-center"><?= $usage_participant['vms_machines_total']." (". $usage_participant['vms_machines_active']." active)" ?></td>
            </tr>
            <tr>
                <th class="text-left" scope="row">Virtual CPUs</th>
                <td class="text-center" ><?= (empty($usage_owner['total_machines_cores'])?'0':$usage_owner['total_machines_cores']) . " (" . (empty($usage_owner['active_machines_cores'])?'0':$usage_owner['active_machines_cores']) . " active)" ?></td>
                <td class="text-center"><?= (empty($usage_participant['total_machines_cores'])?'0':$usage_participant['total_machines_cores']) . " (" . (empty($usage_participant['active_machines_cores'])?'0':$usage_participant['active_machines_cores']) . " active)" ?></td>
            </tr>
            <tr>
                <th class="text-left" scope="row">RAM(GBs)</th>
                <td class="text-center" ><?= (empty($usage_owner['total_machines_ram'])?'0':$usage_owner['total_machines_ram']) . " (" . (empty($usage_owner['active_machines_ram'])?'0':$usage_owner['active_machines_ram']) . " active)" ?></td>
                <td class="text-center"><?= (empty($usage_participant['total_machines_ram'])?'0':$usage_participant['total_machines_ram']) . " (" . (empty($usage_participant['active_machines_ram'])?'0':$usage_participant['active_machines_ram']) . " active)" ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="row">&nbsp;</div>

<div class="text-center"><h2><strong>On-demand batch computations</strong></h2></div>

<div class="table table-bordered">
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="text-center" scope="col" style="width: 23.333%"></th>
                <th class="text-center" scope="col" style="width: 38.3333% text-align: center">As owner</th>
                <th class="text-center" scope="col" style="width: 38.3333%">As participant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th class="text-left" scope="row">Projects</th>
                <td class="text-center" ><?=$usage_owner['total_ondemand']." (".$usage_owner['active_ondemand']." active, ". $usage_owner['expired_ondemand']. " expired)" ?></td>
                <td class="text-center"><?= $usage_participant['total_ondemand']." (".$usage_participant['active_ondemand']." active, ". $usage_participant['expired_ondemand']. " expired)"  ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="row">&nbsp;</div>

<div class="text-center"><h2><strong>On-demand notebooks</strong></h2></div>

<div class="table table-bordered">
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="text-center" scope="col" style="width: 23.333%"></th>
                <th class="text-center" scope="col" style="width: 38.3333% text-align: center">As owner</th>
                <th class="text-center" scope="col" style="width: 38.3333%">As participant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th class="text-left" scope="row">Projects</th>
                <td class="text-center" ><?=$usage_owner['total_notebooks']." (".$usage_owner['active_notebooks']." active, ". $usage_owner['expired_notebooks']. " expired)" ?></td>
                <td class="text-center"><?= $usage_participant['total_notebooks']." (".$usage_participant['active_notebooks']." active, ". $usage_participant['expired_notebooks']. " expired)"  ?></td>
            </tr>
            <tr>
                <th class="text-left" scope="row">Servers</th>
                <td class="text-center" ><?=$usage_owner['total_servers']." (".$usage_owner['active_servers']." active, ". $usage_owner['inactive_servers']. " deleted)" ?></td>
                <td class="text-center"><?= $usage_participant['total_servers']." (".$usage_participant['active_servers']." active, ". $usage_participant['inactive_servers']. " deleted)"  ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="row">&nbsp;</div>

<div class="text-center"><h2><strong>Storage volumes</strong></h2></div>

<div class="table table-bordered">
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="text-center" scope="col" style="width: 23.333%"></th>
                <th class="text-center" scope="col" style="width: 38.3333% text-align: center">As owner</th>
                <th class="text-center" scope="col" style="width: 38.3333%">As participant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th class="text-left" scope="row">Total projects</th>
                <td class="text-center" ><?= $usage_owner['total_storage_projects'] ?> (<?= $usage_owner['number_storage_service_projects']?> for 24/7 service, <?= $usage_owner['number_storage_machines_projects']?> for on-demand compute machines)</td>
                <td class="text-center"><?= $usage_participant['total_storage_projects'] ?> (<?= $usage_participant['number_storage_service_projects']?> for 24/7 service, <?= $usage_participant['number_storage_machines_projects']?> for on-demand compute machines)</td>
            </tr>
            <tr>
                <th class="text-left" scope="row">Total volumes</th>
                <td class="text-center" ><?= $usage_owner['total_volumes'] ?> (<?= $usage_owner['number_volumes_service']?> for 24/7 service, <?= $usage_owner['number_volumes_machines']?> for on-demand compute machines)</td>
                <td class="text-center"><?= $usage_participant['total_volumes'] ?> (<?= $usage_participant['number_volumes_service']?> for 24/7 service, <?= $usage_participant['number_volumes_machines']?> for on-demand compute machines)</td>
            </tr>
            <tr>
                <th class="text-left" scope="row">Total used storage (TB)</th>
                <td class="text-center" ><?= number_format($usage_owner['total_storage_size'],2) ?> TB (<?= number_format($usage_owner['size_storage_service'],2)?> TB for 24/7 service, <?= number_format($usage_owner['size_storage_machines'],2)?> TB for compute machines)</td>
                <td class="text-center"><?= number_format($usage_participant['total_storage_size'],2) ?> TB (<?= number_format($usage_participant['size_storage_service'],2)?> TB for 24/7 service, <?= number_format($usage_participant['size_storage_machines'],2)?> TB for compute machines)</td>
            </tr>
        </tbody>
    </table>
</div>

