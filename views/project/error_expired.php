<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\Headers;

echo Html::CssFile('@web/css/project/vm-details.css');

$this->title="Request expired.";

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
	<div class="col-md-12"><h3>This project request has expired. To update it, please contact an administrator.</h3></div>
</div>
