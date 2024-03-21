<?php
use yii\helpers\Html;
use yii\widgets\LinkPager;
use app\components\Headers;
use app\models\Token;

echo Html::CssFile('@web/css/project/tokens.css');
$this->registerJsFile('@web/js/project/index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$back_icon='';
$access_icon='<i class="fas fa-external-link-square-alt"></i>';
$update_icon='';
$back_link='/project/on-demand-access';
$delete_icon='';
$new_icon='<i class="fas fa-plus-circle"></i>';
$exclamation_icon='<i class="fas fa-exclamation-triangle" style="color:orange" title="The Vm belongs to an expired project"></i>';
$mode = 0;


Headers::begin() ?>
<?php
	echo Headers::widget(
	['title'=>'API tokens management'."</br>",
		'subtitle'=>$project->name,
		'buttons'=>
		[
			// //added the access button that redirects you to schema
			// ['fontawesome_class'=>$access_icon,'name'=> 'Access', 'action'=> ['/site/index'], 'type'=>'a', 'options'=>['class'=>'btn btn-success btn-md', 'style'=>'width: 100px'] ],
			// //added the token button
			
			//['fontawesome_class'=>$new_icon,'name'=> 'New Token', 'action'=> ['/project/new-token-request','id'=>$requestId, 'mode'=>$mode, 'uuid'=>$mode], 'type'=>'a', 'options'=>['class'=>'btn btn-secondary btn-md'] ],
			//['fontawesome_class'=>$update_icon,'name'=> 'Update', 'action'=> ['/project/edit-project','id'=>$request_id], 'type'=>'a', 'options'=>['class'=>'btn btn-secondary btn-md'] ],
			['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>['project/on-demand-access', 'id'=>$project->id], 'type'=>'a', 
			'options'=>['class'=>'btn btn-default', 'style'=>'width: 100px; color:grey ']] 
		],
	]);
?>
<?Headers::end()?>
<br>
<dd>This page allows for the creation and management of API authentication & authorization tokens that are used for running computational jobs in the context 
	of an approved project. Keep in mind that the creation of multiple tokens for the same project is supported.
</dd>

<?php
?>
<br>

<div style="float: right; ;text-align:center">
			<?=Html::a("$new_icon New token",['/project/new-token-request','id'=>$requestId, 'mode'=>$mode, 'uuid'=>$mode],['class'=>'btn btn-success create-vm-btn', 'style'=>'width:110px'])?> 
					
</div>

<div class="row"><h3 class="col-md-12">Issued tokens(<?=$issued_tokens?>) 
	<i class="fas fa-chevron-up" id="arrow" title="Hide tokens" style="cursor: pointer" ></i></h3> 
</div>

<div class="row">&nbsp;</div>

<?php
if ($issued_tokens!=0) {
?>

<div class="table-responsive"  id="expired-table" style="outline: thin solid">
   	<table class="table table-striped">
		<thead>
			<tr>
				<th class="col-md-2" scope="col">Token name</th>
				<th class="col-md-2 ">Expires in</th>
				<th class="col-md-2" scope="col">&nbsp;</th>
			</tr>
		</thead>
		<tbody>

<?php
	foreach ($strArray as $token){
		if (empty($token) == false ){
		//if (strcmp($token_name, "[{") != 0 and strcmp($token_name, "uuid") != 0 and strcmp($token_name, ":") != 0 and strcmp($token_name, "}]") != 0 and strcmp($token_name, "},{") != 0){
			//echo "$token_name". "<br>";
			$token_details = Token::SplitTokens($token);
			$title = $token_details[0];
			$exp_days = $token_details[2];
			$active = $token_details[3];
			$uuid = $token_details[4];
			//echo "$token_details". "<br>";
			// $token_details[1] = $token_details[1]->format('d/m/Y');
			// echo $token_details[1];


?>
	<tr class="active" style="font-size: 14px;">
		<td class="col-md-2" style="vertical-align: middle!important;"> <?=$title?></td>
		<td class="col-md-2 " style="vertical-align: middle!important;"><?=$exp_days. " days "?></td>
		<td class="col-md-3 text-right">
			<?=Html::a("$update_icon Edit",['/project/new-token-request','id'=>$requestId, 'mode'=>1, 'uuid'=>$uuid],['class'=>'btn btn-secondary create-vm-btn', 'style'=>'width:90px'])?> 
			<?=Html::a("$delete_icon Delete",['/project/new-token-request','id'=>$requestId, 'mode'=>2, 'uuid'=>$uuid],['class'=>"btn btn-secondary btn-md delete-volume-btn", 'style'=>'width:90px','data' => [
                                'confirm' => 'Are you sure you want to delete the token with name '.$title. ' ?',
                                'method' => 'post',
                                ],])?>
					
		</td>
	</tr>
<?php
				
			
		}
	}
} 
?>
			</tbody>
		</table>
	</div> <!--table-responsive-->
