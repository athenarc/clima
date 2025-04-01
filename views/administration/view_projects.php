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
                    3 => '<i class="fa fa-bolt" aria-hidden="true"></i>',
                    4 => '<i class="fa fa-book" aria-hidden="true"></i>',
                ];

                $project_icon = $icons[$model['project_type']] ?? '<i class="fa fa-database" aria-hidden="true"></i>';
                return $project_icon;
            },
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
            'class' => ActionColumn::className(),
            'template' => '{view} {delete}', // Add delete action
            'buttons' => [
                'view' => function ($url, $model) {
                    return Html::a('<i class="fa fa-eye"></i>',
                        Url::to(['project/view-request-user',
                            'id' => $model['project_request_id'],
                            'return' => 'index',
                        ]),
                        ['title' => 'View Project Request', 'class' => 'btn btn-primary btn-sm']
                    );
                },
                'delete' => function ($url, $model) {
                    // Ensure the model is an array and has the correct keys
                    if (!isset($model['project_id']) || !isset($model['project_name'])) {
                        return ''; // Return empty string if data is missing to prevent errors
                    }

                    $url = Yii::$app->urlManager->createUrl([
                        'project/delete-project',  // Ensure the correct controller/action
                        'pid' => $model['project_id'],  // Use correct project ID field
                        'pname' => $model['project_name'], // Use correct project name field
                    ]);

                    return Html::a(
                        '<i class="fa fa-trash"></i>',
                        $url,
                        [
                            'title' => 'Delete Project',
                            'data-confirm' => "Are you sure you want to delete the project '{$model['project_name']}'?\nIf you have active resources, all of them will be deleted as well.",
                            'data-method' => 'post',
                            'class' => 'btn btn-danger btn-sm',
                        ]
                    );
                },
            ],
        ],
    ],
]); ?>
