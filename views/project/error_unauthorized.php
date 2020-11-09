<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\Headers;

echo Html::CssFile('@web/css/project/vm-details.css');

$this->title="Authorization error";

$back_icon='<i class="fas fa-arrow-left"></i>';

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>"Authorization error", 
	'buttons'=>
	[
		['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=> ['/project/index'], 'type'=>'a', 'options'=>['class'=>'btn btn-default'] ],
		
	]
])
?>
<?Headers::end()?>



<div class="row">
	<div class="col-md-12"><h3>You are not authorized to perform this action. Please contact an administrator.</h3></div>
</div>
