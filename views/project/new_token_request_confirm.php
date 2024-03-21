<?php
use yii\helpers\Html;
?>
<p>You have entered the following information:</p>

<ul>
    <li><label>Name</label>: <?= Html::encode($model->name) ?></li>
    <li><label>Expiration date</label>: <?= Html::encode($model->expiration_date) ?></li>
</ul>