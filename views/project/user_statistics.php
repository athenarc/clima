<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title="Statistics for user $username";

echo Html::cssFile('@web/css/project/project_details.css');

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

<div class="col-md-12 text-center"><h2><strong>24/7 services (as Owner)</strong></h2></div>
<div class="table-responsive">
    <table class="table table-striped">
        <tbody>
            <tr>
                <th class="col-md-6 text-right" scope="col">Active projects</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['active_services'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Expired projects</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['expired_services'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Total  projects</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['total_services'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col"> Active VMs</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['vms_services_active'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col"> Total VMs</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['vms_services_total'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Active used virtual CPUs</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['active_services_cores'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col"> Active used RAM (GB)</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['active_services_ram'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Total used virtual CPUs</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['total_services_cores'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col"> Total used RAM (GB)</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['total_services_ram'] ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="col-md-12 text-center"><h2><strong>24/7 services (as Participant)</strong></h2></div>
<div class="table-responsive">
    <table class="table table-striped">
        <tbody>
            <tr>
                <th class="col-md-6 text-right" scope="col">Active projects</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['active_services'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Expired projects</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['expired_services'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Total  projects</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['total_services'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col"> Active VMs</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['vms_services_active'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col"> Total VMs</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['vms_services_total'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Active used virtual CPUs</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['active_services_cores'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col"> Active used RAM (GB)</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['active_services_ram'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Total used virtual CPUs</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['total_services_cores'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col"> Total used RAM (GB)</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['total_services_ram'] ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="col-md-12 text-center"><h2><strong>On-demand computation machines (as Owner)</strong></h2></div>
<div class="table-responsive">
    <table class="table table-striped">
        <tbody>
            <tr>
                <th class="col-md-6 text-right" scope="col">Active projects</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['active_machines'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Expired projects</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['expired_machines'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Total projects</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['total_machines'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col"> Active  VMs</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['vms_machines_active'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col"> Total VMs</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['vms_machines_total'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Active used virtual CPUs</th>
                <td class="col-md-6 text-left" scope="col"><?= empty($usage_owner['active_machines_cores'])?'0': $usage_owner['active_machines_cores'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col"> Active  used RAM (GB)</th>
                <td class="col-md-6 text-left" scope="col"><?= empty($usage_owner['active_machines_ram'])?'0': $usage_owner['active_machines_ram'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Total used virtual CPUs</th>
                <td class="col-md-6 text-left" scope="col"><?= empty($usage_owner['total_machines_cores'])?'0': $usage_owner['total_machines_cores'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col"> Total  used RAM (GB)</th>
                <td class="col-md-6 text-left" scope="col"><?= empty($usage_owner['total_machines_ram'])?'0': $usage_owner['total_machines_ram'] ?></td>
            </tr>
    </tbody>
    </table>
</div>

<div class="col-md-12 text-center"><h2><strong>On-demand computation machines (as Participant)</strong></h2></div>
<div class="table-responsive">
    <table class="table table-striped">
        <tbody>
            <tr>
                <th class="col-md-6 text-right" scope="col">Active projects</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['active_machines'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Expired projects</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['expired_machines'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Total projects</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['total_machines'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col"> Active  VMs</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['vms_machines_active'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col"> Total VMs</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['vms_machines_total'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Active used virtual CPUs</th>
                <td class="col-md-6 text-left" scope="col"><?= empty($usage_participant['active_machines_cores'])?'0': $usage_participant['active_machines_cores'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col"> Active  used RAM (GB)</th>
                <td class="col-md-6 text-left" scope="col"><?= empty($usage_participant['active_machines_ram'])?'0': $usage_participant['active_machines_ram'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Total used virtual CPUs</th>
                <td class="col-md-6 text-left" scope="col"><?= empty($usage_participant['total_machines_cores'])?'0': $usage_participant['total_machines_cores'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col"> Total  used RAM (GB)</th>
                <td class="col-md-6 text-left" scope="col"><?= empty($usage_participant['total_machines_ram'])?'0': $usage_participant['total_machines_ram'] ?></td>
            </tr>
    </tbody>
    </table>
</div>


<div class="col-md-12 text-center"><h2><strong>On-demand batch computations (as Owner)</strong></h2></div>
<div class="table-responsive">
    <table class="table table-striped">
        <tbody>

            <tr>
                <th class="col-md-6 text-right" scope="col">Active projects</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['active_ondemand'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Expired projects</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['expired_ondemand'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Total projects</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['total_ondemand'] ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="col-md-12 text-center"><h2><strong>On-demand batch computations (as Participant)</strong></h2></div>
<div class="table-responsive">
    <table class="table table-striped">
        <tbody>

            <tr>
                <th class="col-md-6 text-right" scope="col">Active projects</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['active_ondemand'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Expired projects</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['expired_ondemand'] ?></td>
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Total projects</th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['total_ondemand'] ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="col-md-12 text-center"><h2><strong>Storage volumes (as Owner)</strong></h2></div>
<div class="table-responsive">
    <table class="table table-striped">
        <tbody>
            <tr>
                <th class="col-md-6 text-right" scope="col">Total projects </th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['total_storage_projects'] ?> (<?= $usage_owner['number_storage_service_projects']?> for 24/7 service, <?= $usage_owner['number_storage_machines_projects']?> for on-demand compute machines)</td> 
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Total volumes </th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_owner['total_volumes'] ?> (<?= $usage_owner['number_volumes_service']?> for 24/7 service, <?= $usage_owner['number_volumes_machines']?> for on-demand compute machines)</td> 
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Total used storage (TB)</th>
                <td class="col-md-6 text-left" scope="col"><?= number_format($usage_owner['total_storage_size'],2) ?> TB (<?= number_format($usage_owner['size_storage_service'],2)?> TB for 24/7 service, <?= number_format($usage_owner['size_storage_machines'],2)?> TB for compute machines)</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="col-md-12 text-center"><h2><strong>Storage volumes (as Participant)</strong></h2></div>
<div class="table-responsive">
    <table class="table table-striped">
        <tbody>
            <tr>
                <th class="col-md-6 text-right" scope="col">Total projects </th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['total_storage_projects'] ?> (<?= $usage_participant['number_storage_service_projects']?> for 24/7 service, <?= $usage_participant['number_storage_machines_projects']?> for on-demand compute machines)</td> 
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Total volumes </th>
                <td class="col-md-6 text-left" scope="col"><?= $usage_participant['total_volumes'] ?> (<?= $usage_participant['number_volumes_service']?> for 24/7 service, <?= $usage_participant['number_volumes_machines']?> for on-demand compute machines)</td> 
            </tr>
            <tr>
                <th class="col-md-6 text-right" scope="col">Total used storage (TB)</th>
                <td class="col-md-6 text-left" scope="col"><?= number_format($usage_participant['total_storage_size'],2) ?> TB (<?= number_format($usage_participant['size_storage_service'],2)?> TB for 24/7 service, <?= number_format($usage_participant['size_storage_machines'],2)?> TB for compute machines)</td>
            </tr>
        </body>
    </table>
</div>
