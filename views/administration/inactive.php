<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Inactive Users';
?>

<h1><?= Html::encode($this->title) ?></h1>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'username',
        [
            'attribute' => 'last_login',
            'format' => ['datetime', 'php:Y-m-d H:i:s'],
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view-projects}', // ✅ Custom action
            'buttons' => [
                'view-projects' => function ($url, $model) {
                    return Html::a('<i class="fas fa-folder-open"></i> View Projects',
                        ['administration/view-projects', 'username' => $model['username']], // ✅ Pass user ID
                        ['class' => 'btn btn-primary btn-sm']
                    );
                }

            ]
        ],
    ],
]); ?>
