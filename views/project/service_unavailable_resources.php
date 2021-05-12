<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\Headers;

echo Html::CssFile('@web/css/project/vm-details.css');

$this->title="Not enouch resources";

$back_icon='<i class="fas fa-arrow-left"></i>';

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'Not enough resources',
	'buttons'=>
	[
		['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=> ['/project/index'], 'type'=>'a', 'options'=>['class'=>'btn btn-default'] ],
		
	],
]
)
?>
<?Headers::end()?>



<div class="row">
	<div class="col-md-12"><h3>We are sorry for the inconvenience, but there are currently not enough resources available in the cloud infrastructure. Please try again later or contact an administrator.</h3></div>
</div>
<div class="row">
	<div class="col-md-12"><h3>Thank you.</h3></div>
</div>
