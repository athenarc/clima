<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\Headers;

echo Html::CssFile('@web/css/project/vm-details.css');


$back_icon='<i class="fas fa-arrow-left"></i>';


Headers::begin() ?>
<?php echo Headers::widget(
['title'=>"VM deletion error", 
	'buttons'=>
	[
		['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=> ['/project/index'], 'type'=>'a', 'options'=>['class'=>'btn btn-default'] ],
		
	]
])
?>
<?Headers::end()?>

<div class="row">
	<div class="col-md-12"><h3><?=$message?> Please contact an administrator with the following error code: <?= $error ?>.</h3></div>
</div>
