<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

echo Html::CssFile('@web/css/project/vm-details.css');

$this->title="Authorization error";

$back_icon='<i class="fas fa-arrow-left"></i>';


?>

<div class="row"><div class="col-md-11"><h1><?=Html::encode($this->title)?></h1></div><div class="col-md-1"><?= Html::a("$back_icon Back", ['/project/index'], ['class'=>'btn btn-default']) ?></div></div>
<div class="row">
	<div class="col-md-12"><h3>You are not authorized to perform this action. Please contact an administrator.</h3></div>
</div>
