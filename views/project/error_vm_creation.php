<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\Headers;

echo Html::CssFile('@web/css/project/vm-details.css');

$this->title="VM creation error";

$back_icon='<i class="fas fa-arrow-left"></i>';

Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'VM creation error', 
	'buttons'=>
	[
	
		['fontawesome_class'=>'<i class="fas fa-arrow-left"></i>','name'=> 'Back', 'action'=>['/project/index'], 
		'options'=>['class'=>'btn btn-default'], 'type'=>'a'] 
	],
])
?>
<?Headers::end()?>

<div class="row">
	<div class="col-md-12"><h3><?=$message?>Please contact an administrator with the following error code and message: <?= $error ?>, <?=$openstackMessage?>.</h3></div>
</div>
