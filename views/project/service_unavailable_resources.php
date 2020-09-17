<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

echo Html::CssFile('@web/css/project/vm-details.css');

$this->title="Not enouch resources";

$back_icon='<i class="fas fa-arrow-left"></i>';


?>

<div class="row"><div class="col-md-11"><h1><?=Html::encode($this->title)?></h1></div><div class="col-md-1"><?= Html::a("$back_icon Back", ['/project/index'], ['class'=>'btn btn-default']) ?></div></div>
<div class="row">
	<div class="col-md-12"><h3>We are sorry for the inconvenience, but there are currently not enough resources available in the cloud infrastructure. Please try again later or contact an administrator.</h3></div>
</div>
<div class="row">
	<div class="col-md-12"><h3>Thank you.</h3></div>
</div>
