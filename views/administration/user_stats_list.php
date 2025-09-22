<?php
use app\models\User;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\grid\ActionColumn;

$this->title = 'User list';

echo Html::cssFile('@web/css/administration/user-stats-list.css');
$this->registerJsFile('@web/js/administration/user-stats-list.js', [
    'depends' => [\yii\web\JqueryAsset::class],
]);

$backIcon  = '<i class="fas fa-arrow-left"></i>';
$statsIcon = '<i class="fas fa-chart-line"></i>';
$checkIcon = '<i class="fas fa-check text-success"></i>';
$crossIcon = '<i class="fas fa-times text-danger"></i>';
?>

<div class="title row">
    <div class="col-md-11">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>
    <div class="col-md-1 text-right">
        <?= Html::a("$backIcon&nbsp;Back", ['/administration/index'],
            ['class' => 'btn btn-default']) ?>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12 text-right">
        <strong>Active users:</strong> <?= $activeUsers ?>,
        <strong>Total users:</strong> <?= $totalUsers ?>
    </div>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'  => $searchModel,
    'tableOptions' => ['class' => 'table table-striped table-bordered'],
    'columns'      => [

        /* Username (without @domain) ------------------------------ */
        [
            'attribute' => 'username',
            'value'     => fn($m) => explode('@', $m['username'])[0],
        ],

        /* E-mail -------------------------------------------------- */
        [
            'attribute' => 'email',
            'format'    => 'raw',
            'value'     => fn($m) => $m['email'] ?? '',
            'filter' => true,
        ],

        /* Active? (✓ / ✗ icon) ----------------------------------- */
        [
            'attribute'      => 'is_active',
            'label'          => 'Active',
            'format'         => 'raw',
            'value'          => fn($m) => $m['is_active'] ? $checkIcon : $crossIcon,
            'filter'         => ['1' => 'Active', '0' => 'Inactive'],
            'contentOptions' => ['class' => 'text-center'],
        ],

        /* # Active Projects --------------------------------------- */
        [
            'attribute'      => 'active_projects',
            'label'          => '# Active Projects',
            'contentOptions' => ['class' => 'text-center'], 'filter' => true,
        ],

        /* User Type ---------------------------------------------- */
        [
            'attribute'   => 'user_type',
            'label'       => 'User&nbsp;Type',
            'encodeLabel' => false,
            'value' => fn($m) => ucfirst($m['user_type'] ?? ''),

            'filter'      => [
                'bronze' => 'Bronze',
                'silver' => 'Silver',
                'gold'   => 'Gold',
            ],
        ],


        /* Policy Accepted? --------------------------------------- */
        [
            'attribute'      => 'policy_accepted',
            'label'          => 'Policy Accepted',
            'encodeLabel'    => false,
            'format'         => 'raw',
            'value'          => fn($m) => $m['policy_accepted'] ? $checkIcon : $crossIcon,
            'filter'         => ['1' => 'Yes', '0' => 'No'],
            'contentOptions' => ['class' => 'text-center'],
        ],

        /* Statistics button -------------------------------------- */
        [
            'class'    => ActionColumn::class,
            'template' => '{statistics}',
            'buttons'  => [
                'statistics' => fn($url, $model) =>
                Html::a("$statsIcon&nbsp;Statistics",
                    ['administration/user-statistics', 'id' => $model['id']],
                    ['class' => 'btn btn-sm btn-primary']),
            ],
        ],
    ],
]); ?>
