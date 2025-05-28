<?php
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $usersWithActiveResources array */

/* @var $searchModel app\models\InactiveUserSearch */
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        'username',
        [
            'attribute' => 'last_login',
            'format' => ['datetime', 'php:Y-m-d H:i:s'],
            'value' => function ($model) {
                return $model['last_login'] ?? null;
            },
        ],

        [
            'attribute' => 'has_active_resources',
            'label' => 'Active Resources',
            'format' => 'raw',
            'value' => function ($model) use ($usersWithActiveResources) {
                return in_array($model['id'], $usersWithActiveResources) ? 'Yes' : 'No';
            },
            'filter' => [
                '1' => 'Yes',
                '0' => 'No',
            ],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view-projects}',
            'buttons' => [
                'view-projects' => function ($url, $model) {
                    return Html::a('View Projects', ['administration/view-projects', 'username' => $model['username']], ['class' => 'btn btn-sm btn-primary']);
                },
            ],

        ],
    ],
]);

