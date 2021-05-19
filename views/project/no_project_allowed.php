<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\Headers;

// echo Html::CssFile('@web/css/project/vm-details.css');

$this->title="No project allowed";

$back_icon='<i class="fas fa-arrow-left"></i>';

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>"Maximum number of projects", 
	'buttons'=>
	[
		['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=> ['/project/new-request'], 'type'=>'a', 'options'=>['class'=>'btn btn-default'] ],
		
	]
])
?>
<?Headers::end()?>



<div class="row">
	<div class="col-md-12"><h3>You have reached the maximum number of <b><?=$project?></b> projects for <b><?=$user_type?> users</b>. Please contact the administrators</h3></div>
</div>
