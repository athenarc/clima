<?php
use yii\helpers\Html;
use yii\widgets\LinkPager;
use app\components\Headers;

echo Html::CssFile('@web/css/project/index.css');
$this->registerJsFile('@web/js/project/index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$back_icon='<i class="fas fa-arrow-left"></i>';
$access_icon='<i class="fas fa-external-link-square-alt"></i>';
$update_icon='<i class="fas fa-pencil-alt"></i>';
$back_link='/project/index';
$delete_icon='<i class="fas fa-times"></i>';
$new_icon='<i class="fas fa-plus-circle"></i>';
$mode = 0;


Headers::begin() ?>
<?php
	echo Headers::widget(
	['title'=>$project->name,
		'buttons'=>
		[
			//added the access button that redirects you to schema
			['fontawesome_class'=>$access_icon,'name'=> 'Access', 'action'=> ['/site/index'], 'type'=>'a', 'options'=>['class'=>'btn btn-success btn-md'] ],
			
			['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>[$back_link], 'type'=>'a', 
			'options'=>['class'=>'btn btn-default']] 
		],
	]);
?>
<?Headers::end()?>


<div class="row"><h3 class="col-md-12">Access on demand batch computations</div>
<div class="row"><div class="col-md-12">If you want to access on demand batch computations please click on <b>Access</b> button.</div></div>
<div class="row">&nbsp;</div>

<div class="row"><div class="col-md-12"></div></div>

