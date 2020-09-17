<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

echo Html::CssFile('@web/css/project/vm-details.css');

$this->title="Project error";

$back_icon='<i class="fas fa-arrow-left"></i>';


?>

<div class="row"><div class="col-md-11"><h1><?=Html::encode($this->title)?></h1></div><div class="col-md-1"><?= Html::a("$back_icon Back", ['/project/index'], ['class'=>'btn btn-default']) ?></div></div>
<div class="row">
	<div class="col-md-12"><h3>There is currently an active VM for this project. You cannot modify the project until you delete the VM.</h3></div>
</div>
