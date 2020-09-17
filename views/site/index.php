<?php
/**
 * View file for the index page
 * 
 * @author: Kostis Zagganas
 * First version: Dec 2018
 */

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'EG-CI';

echo Html::CssFile('@web/css/site/index.css');
?>
<div class="site-index">
	<div class="row">&nbsp;</div>
	<div class="row">
		<div class='col-md-7'>
			<h2>What is the EG-CI?</h2>
			The ELIXIR-GR Cloud Infrastructure (EG-CI) is a Cloud infrastructure being developed to support a broad spectrum of bioinformatic services which will be provided to the members of the ELIXIR-GR community and to the broader community of life scientists in Greece and abroad, as well. EG-CI consists of the following types of nodes:
			<ul class="square-list">
				<li><strong>Converged nodes:</strong> used for simple computational needs and to implement a Ceph-based storage.</li>
				<li><strong>Fat nodes:</strong> more powerful machines (more cores & memory) that are used for resource-intensive computations.</li>
				<li><strong>GPU nodes:</strong> machines including graphic accelerators, which can be used to speed-up several computation types.</li>
				<li><strong>I/O nodes:</strong> machines that can better serve computations involving many disk I/O operations.</li>
				<li><strong>Infrastructure nodes:</strong> machines dedicated to orchestrate converged and compute nodes.</li>
			</ul>
		</div>
		<div class="col-md-5"><?=Html::img('@web/img/site/index-unsplash-1.jpg',['class'=>'index-img'])?></div>
	</div>
	<div class="row">&nbsp;</div>
	<div class="row">
		<div class="col-md-5"><?=Html::img('@web/img/site/index-unsplash-2.jpg',['class'=>'index-img'])?></div>
		<div class='col-md-7'>
			<h2>How can I use the EG-CI’s resources?</h2>
			Based on the different uses that are supported by the EG-CI, the users will be able to make requests for the following types of projects:
			<ul class="square-list">
				<li><strong>“24/7 services” projects:</strong> This type of project is suitable for hosting 24/7 services, following the Virtual Private Server (VPS) model. There are two sub-categories, based on two major types of 24/7 services: “Precomputed databases” (hosting important data services) and “Computational servers” (hosting light on-demand computations). </li>
				<li><strong>“On-demand computation” projects:</strong> This type of project is suitable for batches of computational tasks to be executed. There are two sub-categories: “Containerised computations” (computations based on containerised software)  and “Non-containerised computations”.</li>
				<li><strong>Cold-storage projects:</strong> This type of project allocates a particular amount of cold storage (e.g., tape storage) used to backup a dataset (or a group of datasets).</li>
			</ul>

			Each accepted request provides access to an appropriate amount of EG-CI resources. Each request is followed by a review process, unless the project requirements are below a predefined limit. In this case, the project is automatically accepted and all the requested resources are automatically allocated to the project.
		</div>
	</div>

	<div class="row">&nbsp;</div>

	<div class="row">
		<div class='col-md-7'>
			<h2>How can I sign-in to the resource management system?</h2>
			<ul class="square-list">
				<li>To sign-in to the resource management system an ELIXIR-AAI account is required (just click on the “Register” button <?=Html::a('here','https://egci-beta.imsi.athenarc.gr/index.php?r=user-management%2Fauth%2Flogin')?>) and wait for an approval email).</li>
				<li>When your your ELIXIR-AAI account is ready, you can just sign-in using your credentials (click “Login” <?=Html::a('here','https://egci-beta.imsi.athenarc.gr/index.php?r=user-management%2Fauth%2Flogin')?>).</li>
				<li>During the previous steps you have to give your consent that ELIXIR-AAI and EG-CI’s resource management system will have access to basic account information required for the flawless operation of the system.</li> 
			</ul>
			Learn <?=Html::a('more','https://elixir-europe.org/services/compute/aai', ['target'=>'_blank'])?> about the ELIXIR-AAI.
		</div>
		<div class="col-md-5"><?=Html::img('@web/img/site/index-unsplash-3.jpg',['class'=>'index-img'])?></div>
	</div>
</div>
