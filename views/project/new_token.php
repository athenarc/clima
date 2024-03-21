<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;
use app\components\Headers;

echo Html::cssFile('@web/css/project/new_token.css');
$this->registerJsFile('@web/js/project/view-request-user.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/project/new-token.js');

$back_icon='<i class="fas fa-arrow-left"></i>';
// $back_link='/project/index';
$access_icon='<i class="fas fa-external-link-square-alt"></i>';


Headers::begin() ?>
<?php
	echo Headers::widget(
	['title'=>$project->name,
		'buttons'=>
		[
			['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>['/project/on-demand-lp','id'=>$requestId], 'type'=>'a', 
			'options'=>['class'=>'btn btn-default']] 
		],
	]);
?>
<?Headers::end()?>

<?php
if ($mode == 0){
?>
<div class="row"><h2 class="col-md-12">New token request</div>
<?php
    $temp1 = '{"context":';
    $temp2 = '"';
    $pname = $project->name;
    $temp3 = '"}';
    $temp4 = $temp2.$pname.$temp3;
    $project_post = $temp1.$temp4;
    $headers = [
        "Authorization: Token e2b5e57c47d8bd073ce2b02e49ab2ddeb869837559138e134d3ed13a714c6ac9a236381e83b22e22e7849b04bef7d25ba3be9db33c67a7cbf239e9bd199bd04ca602f1baf1db7018eb231f574a48d9e90e19ac4c9fb1acfead568fe749fe4c94",
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    $URL1 = "http://62.217.122.242/api_auth/contexts/";
    $URL = $URL1.$pname;

    curl_setopt($ch, CURLOPT_URL,$URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    echo $URL;
    $project_exists = curl_exec($ch);
    curl_close($ch);

    //first check if the project has already been registered

    if(strpos($project_exists, "No context with name ") == true){

        //if the project is not registered then register it to the api

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://62.217.122.242:80/api_auth/contexts");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $project_post);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_exec($ch);
        curl_close($ch);
    }   

    //get new token

    $ch = curl_init();
    $URL2 = "/tokens";
    $URL = $URL1.$pname.$URL2;

    curl_setopt($ch, CURLOPT_URL,$URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if (empty($model->name)){

        $token = curl_exec($ch);
        curl_close($ch);

    } 
    else {
        //if the user provided a name then make the api call with that name 

        $temp1 = '{"title":';
        $temp2 = '"';
        $pname = $model->name;
        $temp3 = '"}';
        $temp4 = $temp2.$pname.$temp3;
        $tname_post = $temp1.$temp4;

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $tname_post);
        $token = curl_exec($ch);
        curl_close($ch);

    }

    $strArray = explode(':',$token);
    $token = $strArray[1];
    $token = str_replace('"', '', $token);
    $token = str_replace('}', '', $token);
?>

    <div class="row">&nbsp;</div>
    <div class="row"><div class="col-md-12">Your new token is the following: </div></div>
<!--    <p>-->
<!--        <em class="ow-break-word"><b>--><?php //echo "$token" ?><!--</b></em>-->
<!--    </p>-->
    <div class="row" id="token-parent">

    </div>
    <script>
        const token = '<?=$token?>'
        const parent = document.getElementById("token-parent");
        generateSourceContentContainer(parent, token, "token-container", true);
    </script>

    <div class="row"><div class="col-md-12"></div></div>
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning" role="alert">
		    You can create more than one tokens, but please keep in mind that for security reasons HYPATIA stores only a description of your assigned tokens and not your exact tokens. Thus, you are responsible for storing and retriving your tokens.
            </div>
        </div>
    </div>  
<?php
} elseif ($mode == 1) {
?>
<div class="row"><h2 class="col-md-12">Token update</div>
<?php
    if (empty($model->name) and empty($model->expiration_date)){
?>
<div class="row"><h3 class="col-md-12">Token update was unsuccessful: </div>
<div class="row"><p class="col-md-12">you didn't provide any of the requested information.</div>
<?php
    } elseif (!empty($model->name)) {
        $headers = [
            "Authorization: Token e2b5e57c47d8bd073ce2b02e49ab2ddeb869837559138e134d3ed13a714c6ac9a236381e83b22e22e7849b04bef7d25ba3be9db33c67a7cbf239e9bd199bd04ca602f1baf1db7018eb231f574a48d9e90e19ac4c9fb1acfead568fe749fe4c94",
            'Content-Type: application/json'
        ];
        $URL1 = "http://62.217.122.242/api_auth/contexts/";
        $pname = $project->name;
        $URL = $URL1.$pname."/tokens"."/".$uuid;
        //echo "$URL"."<br>";
        $temp1 = '{"title":';
        $temp2 = '"';
        $temp3 = '"}';
        $temp4 = $temp2.$model->name.$temp3;
        $patch = $temp1.$temp4;
        //echo "$patch"."<br>";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $patch);
        $out = curl_exec($ch);
        curl_close($ch);
        //echo "$out"."<br>";
?>
<div class="row"><div class="col-md-12">Your token information was updated succesfully. </div></div>
<?php
    }

}


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://62.217.122.242:80/api_auth/contexts");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Token e2b5e57c47d8bd073ce2b02e49ab2ddeb869837559138e134d3ed13a714c6ac9a236381e83b22e22e7849b04bef7d25ba3be9db33c67a7cbf239e9bd199bd04ca602f1baf1db7018eb231f574a48d9e90e19ac4c9fb1acfead568fe749fe4c94"
]);
$output = curl_exec($ch);
curl_close($ch);

?>