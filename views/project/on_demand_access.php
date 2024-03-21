<?php
use yii\helpers\Html;
use yii\widgets\LinkPager;
use app\components\Headers;
use app\models\Token;
use yii\helpers\Url;  

echo Html::CssFile('@web/css/project/tokens.css');
$this->registerJsFile('@web/js/project/index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$back_icon='';
$access_icon='<i class="fas fa-external-link-square-alt"></i>';
$update_icon='<i class="fas fa-pencil-alt"></i>';
$back_link='/project/index';
$delete_icon='<i class="fas fa-times"></i>';
$new_icon='<i class="fas fa-plus-circle"></i>';
$exclamation_icon='<i class="fas fa-exclamation-triangle" style="color:orange" title="The Vm belongs to an expired project"></i>';
$mode = 0;
$edit_button_class='';
$access_button_class ='';
$ondemand_access_class = '';


Headers::begin() ?>
<?php
	echo Headers::widget(
	['title'=>'Project management '."</br>",
        'subtitle'=>$project->name,
		'buttons'=>
		[
			['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>[$back_link], 'type'=>'a', 
			'options'=>['class'=>'btn btn-default', 'style'=>'width: 100px; color:grey ']] 
		],
	]);
?>
<?Headers::end()?>
<br>
<h4><b> Approved resources</b></h4>
<div id="containerIntro">
<h4><b> &emsp; Jobs: &nbsp;</b></h4><p><?=$initial_jobs?>&nbsp;(remaining: &nbsp;<?=$remaining_jobs?>)</p> <br>
<h4><b> &emsp; Cores: &nbsp;</b></h4><p><?=$details->cores?>&nbsp;(average use: &nbsp;<?=round($usage['cpu']/1000,2)?>)</p><br>
<h4><b> &emsp; Ram: &nbsp;</b></h4><p><?=$details->ram?>GB&nbsp;(average use: &nbsp;<?=round($usage['ram']/1000,2)?>GB)</p><br><br>

<p>&nbsp;*These computational resources are available for the execution of containerized software packages and workflows.</p>
</div>

<br>
<h4><b> Using the resources</b></h4>
<div id="containerIntro">
<p>&emsp;&emsp;There are two ways to use the approved computational resources of this project:</p> <br>
<p>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&#8208; Use our Job execution User Interface (UI) to manually run computational analysis tasks leveraging Web-based wizards.</p> <br>
<p>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&#8208; Use our Job execution API to programmatically run computational analysis tasks (API tokens need to be issued).</p> <br><br>

<p>&emsp;&emsp;You can select the way that better fits your needs (but it's possible to consume your approved resources using both ways):</p><br>
</div>
<br>
<div style="float: left; width: 49.5%;text-align:right">
<?=Html::a("Job execution UI",$compute,['class'=>"btn btn-success btn-md $access_button_class $ondemand_access_class", 'style'=>'width:270px; height:60px; text-align: center; line-height: 45px; font-size:20px; ','target'=>'_blank'])?>
<br/>
<br/>
<?=Html::a("API tokens management",['/project/token-management', 'id'=>$id],['class'=>"btn btn-success btn-md $edit_button_class", 'style'=>'width:270px; height:60px; text-align: center; line-height: 45px; font-size:20px;'])?>
</div>

<div style="float: right; width: 49.5%;text-align:left">
<?=Html::a("UI user guide",'index.php?r=site%2Fhelp',['class'=>"btn btn btn-secondary btn-md $access_button_class $ondemand_access_class", 'style'=>'width:270px; height:60px; text-align: center; line-height: 45px; font-size:20px;'])?>
<br/>
<br/>
<?=Html::a("API documentation",'https://schema.athenarc.gr/docs/schema-api/',['class'=>"btn btn btn-secondary btn-md $access_button_class $ondemand_access_class", 'style'=>'width:270px; height:60px; text-align: center; line-height: 45px; font-size:20px;','target'=>'_blank'])?>
</div>

<br>
<br/>
<br>
<br/>
<p class="h4"><b> Monitoring job execution</b></p>
<div id="containerIntro">
<p>&emsp;&emsp;API calls can used to retrieve the status of the computational jobs related to this project (for details, see API documentation).
<br>&emsp;&emsp;You can also examine the status of any job from the respective page of the Job execution UI:</p> <br>
</div>
<br>
<center>
<?=Html::a("Job monitoring UI",'https://hypatia-comp.athenarc.gr/index.php?r=software%2Fhistory',['class'=>"btn btn-success btn-md $access_button_class $ondemand_access_class", 'style'=>'width:270px; height:60px; text-align: center; line-height: 45px; font-size:20px;','target'=>'_blank'])?>
</center>