<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;
use yii\bootstrap\NavBar;
use yii\bootstrap\Nav;
use yii\helpers\Url;
use app\components\Headers;

echo Html::CssFile('@web/css/project/inactive.css');


$this->title = "Inactive Users";

?>

<div class="row"><h3 class="col-md-12">Inactive Users</h3></div>
<div class="row main-content">
    <div class="table-responsive">
        <?= \yii\grid\GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                'id',
                'username',
                'email',
                'first_name',
                'last_name',
                'last_login',
            ],
        ]); ?>
    </div>
</div>
