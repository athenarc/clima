<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title="Enable user login via SSH (Ubuntu)";

$back_icon='<i class="fas fa-arrow-left"></i>';

/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */
?>
<div class='title row'>
	<div class="col-md-10">
		<h1><?= Html::encode($this->title) ?></h1>
	</div>
	<div class="col-md-1 float-right">
		<?= Html::a("$back_icon Back", ['project/index'], ['class'=>'btn btn-default']) ?>
	</div>
</div>

<div class="row"><div class="col-md-12">1. Connect to the VM via SSH:</div></div>
<div class="code">ssh ubuntu@vm-ip</div>
<div class="row"><div class="col-md-12">where vm-ip is the IP address of the VM you just created.</div></div>

<div class="row">&nbsp;</div>

<div class="row"><div class="col-md-12">2. Enable SSH password authentication by editing the following file as root:</div></div>
<div class="code">sudo nano /etc/ssh/sshd_config</div>

<div class="row"><div class="col-md-12">3. Change the line</div></div>
<div class="code">PasswordAuthentication no</div>
<div class="row"><div class="col-md-12">to</div></div>
<div class="code">PasswordAuthentication yes</div>

<div class="row"><div class="col-md-12">4. After changing the line and saving the file, you need to restart the sshd server:</div></div>
<div class="code">sudo systemctl restart sshd</div>

<div class="row"><div class="col-md-12">5. Add a new user:</div></div>
<div class="code">sudo adduser newuser</div>
<div class="row"><div class="col-md-12">and follow the steps specified by the system.</div></div>

<div class="row">&nbsp;</div>

<div class="row"><div class="col-md-12">6. Give a user access to sudo (optional):</div></div>
<div class="code">sudo usermod -aG sudo newuser</div>


