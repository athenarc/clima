<?php

use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'User Projects';
?>

<h1><?= Html::encode($this->title) ?></h1>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'project_id',
            'label' => 'Project ID',
        ],
        [
            'attribute' => 'project_name',
            'label' => 'Project',
        ],
        [
            'attribute' => 'project_type',
            'label' => 'Type',
            'format' => 'raw',
            'value' => function ($model) {
                $icons = [
                    0 => '<i class="fa fa-rocket" aria-hidden="true"></i>',
                    1 => '<i class="fa fa-leaf" aria-hidden="true"></i>',
                    2 => '<i class="fa fa-database" aria-hidden="true"></i>',
                    3 => '<i class="fa fa-bolt" aria-hidden="true"></i>',
                    4 => '<i class="fa fa-book" aria-hidden="true"></i>',
                ];

                return $icons[$model['project_type']] ?? '<i class="fa fa-question-circle" aria-hidden="true"></i>';
            },
            'filter' => [
                0 => 'On-demand batch',
                1 => '24/7 Service',
                2 => 'Storage volume',
                3 => 'Compute machines',
                4 => 'Notebooks',
            ],
        ],
        [
            'attribute' => 'username',
            'label' => 'Owner',
        ],
        [
            'attribute' => 'project_end_date',
            'label' => 'Project End Date',
        ],
        [
            'attribute' => 'has_active_resources',
            'label' => 'Active Resources',
            'value' => function ($model) use ($active_resources) {
                $active = isset($active_resources[$model['project_type']][$model['project_id']]);
                return $active ? 'Yes' : 'No';
            },
            'filter' => [
                '1' => 'Yes',
                '0' => 'No',
            ],
            'contentOptions' => ['class' => 'col-md-1 text-center', 'style' => 'vertical-align: middle!important;'],
        ],
        [
            'class' => ActionColumn::className(),
            'template' => '{view} {delete}',
            'buttons' => [
                'view' => function ($url, $model) {
                    return Html::a('<i class="fa fa-eye"></i>',
                        Url::to(['project/view-request-user', 'id' => $model['project_request_id'], 'return' => 'index']),
                        ['title' => 'View Project Request', 'class' => 'btn btn-primary btn-sm']
                    );
                },
                'delete' => function ($url, $model) {
                    $url = Yii::$app->urlManager->createUrl([
                        'project/delete-project',
                        'pid' => $model['project_id'],
                        'pname' => $model['project_name'],
                    ]);

                    return Html::a('<i class="fa fa-trash"></i>', $url, [
                        'title' => 'Delete Project',
                        'data-confirm' => "Are you sure you want to delete the project '{$model['project_name']}'?\nIf you have active resources, all of them will be deleted as well.",
                        'data-method' => 'post',
                        'class' => 'btn btn-danger btn-sm',
                    ]);
                },
            ],
        ],
    ],
]); ?>
