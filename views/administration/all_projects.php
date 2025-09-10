<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\Headers;

$this->title = "All projects";

$this->registerCssFile('@web/css/administration/all_projects.css');
$this->registerJsFile('@web/js/administration/all_projects.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$back_icon = '<i class="fas fa-arrow-left"></i>';
$expired_active_resources_icon = '<i class="fa fa-exclamation-triangle" title="This expired project has active resources"></i>';

Headers::begin();
echo Headers::widget([
    'title' => "All projects",
    'buttons' => [
        [
            'fontawesome_class' => $back_icon,
            'name' => 'Admin options',
            'action' => ['/administration/index'],
            'type' => 'a',
            'options' => ['class' => 'btn btn-default']
        ],
    ],
]);
Headers::end();
?>

<div class="row">
    <h3 class="col-md-12">Active projects (<?= $number_of_active ?>)</h3>
</div>
<div class="row main-content">
    <div class="col-md-12">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'filterUrl' => ['all-projects'],
            'filterSelector' => 'input[name^="ActiveProjectSearch"], select[name^="ActiveProjectSearch"]',
            'sorter' => ['sortParam' => 'activeSort'],
            'options' => ['id' => 'active-grid'],
            'columns' => [
                [
                    'attribute' => 'name',
                    'contentOptions' => [
                        'class' => 'col-md-1 text-center',
                        'style' => 'vertical-align: middle!important;',
                    ],
                    'enableSorting' => true,
                ],
                [
                    'attribute' => 'project_type',
                    'label' => 'Type',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $icons = [
                            0 => '<i class="fa fa-rocket"></i> On-demand batch',
                            1 => '<i class="fa fa-leaf"></i> 24/7 service',
                            2 => '<i class="fa fa-database"></i> Storage volume',
                            3 => '<i class="fa fa-bolt"></i> Compute machines',
                            4 => '<i class="fa fa-book"></i> Notebooks',
                        ];
                        return $icons[$model['project_type']] ?? $model['project_type'];
                    },
                    'filter' => [
                        0 => 'On-demand batch',
                        1 => '24/7 service',
                        2 => 'Storage volume',
                        3 => 'Compute machines',
                        4 => 'Notebooks',
                    ],
                    'contentOptions' => [
                        'class' => 'col-md-2 text-center',
                        'style' => 'vertical-align: middle!important;',
                    ],
                ],
                [
                    'attribute' => 'owner',
                    'label' => 'Owner',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return ($model['owner'] === 'You') ? "<b>You</b>" : $model['owner'];
                    },
                    'contentOptions' => ['class' => 'col-md-3 text-center', 'style' => 'vertical-align: middle!important;'],
                ],
                [
                    'attribute' => 'expires_in',
                    'label' => 'Expires in',
                    'value' => fn($model) => $model['expires_in'] . ' days',
                    'contentOptions' => ['class' => 'col-md-2 text-center', 'style' => 'vertical-align: middle!important;'],
                    'filter' => false,
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{details}',
                    'header' => 'Actions',
                    'buttons' => [
                        'details' => function ($url, $model) use ($filters) {
                            return Html::a('<i class="fas fa-eye"></i> Details', [
                                '/project/view-request-user',
                                'id' => $model['project_request_id'], // <- inspect this
                                'return' => 'admin',
                                'expired' => 0,
                                'ptype' => $filters['type'],
                                'exp' => $filters['exp'],
                                'puser' => $filters['user'],
                                'pproject' => $filters['name']
                            ], ['class' => 'btn btn-secondary btn-md']);
                        },
                    ],
                ],
            ],
        ]) ?>
    </div>
</div>

<div class="row">
    <h3 class="col-md-12">Expired projects (<?= $number_of_expired ?>)</h3>
</div>
<div class="row main-content">
    <div class="col-md-12">
        <?= GridView::widget([
            'dataProvider' => $dataProviderExpired,
            'filterModel' => $searchModelExpired,
            'filterUrl' => ['all-projects'],
            'filterSelector' => 'input[name^="ExpiredProjectSearch"], select[name^="ExpiredProjectSearch"]',
            'sorter' => ['sortParam' => 'expiredSort'],
            'options' => ['id' => 'expired-grid'],
            'columns' => [
                [
                    'attribute' => 'name',
                    'format' => 'raw',
                    'value' => function ($model) use ($active_resources, $expired_active_resources_icon) {
                        $active = isset($active_resources[$model['project_type']][$model['project_id']]);
                        return Html::encode($model['name']) . ($active ? '&nbsp;' . $expired_active_resources_icon : '');
                    },
                    'contentOptions' => ['class' => 'col-md-2 text-center', 'style' => 'vertical-align: middle!important;'],
                ],
                [
                    'attribute' => 'project_type',
                    'label' => 'Type',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $icons = [
                            0 => '<i class="fa fa-rocket"></i> On-demand batch',
                            1 => '<i class="fa fa-leaf"></i> 24/7 service',
                            2 => '<i class="fa fa-database"></i> Storage volume',
                            3 => '<i class="fa fa-bolt"></i> Compute machines',
                            4 => '<i class="fa fa-book"></i> Notebooks',
                        ];
                        return $icons[$model['project_type']] ?? $model['project_type'];
                    },
                    'filter' => [
                        0 => 'On-demand batch',
                        1 => '24/7 service',
                        2 => 'Storage volume',
                        3 => 'Compute machines',
                        4 => 'Notebooks',
                    ],
                    'contentOptions' => ['class' => 'col-md-2 text-center', 'style' => 'vertical-align: middle!important;'],
                ],
                [
                    'attribute' => 'owner',
                    'label' => 'Owner',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return ($model['owner'] === 'You') ? "<b>You</b>" : $model['owner'];
                    },
                    'contentOptions' => ['class' => 'col-md-2 text-center', 'style' => 'vertical-align: middle!important;'],
                ],
                [
                    'attribute' => 'expires_in',
                    'label' => 'Expired on',
                    'value' => 'expires_in',
                    'contentOptions' => ['class' => 'col-md-2 text-center', 'style' => 'vertical-align: middle!important;'],
                    'filter' => false,
                ],
                [
                    'attribute' => 'has_active_resources',
                    'label' => 'Active Resources',
                    'value' => function ($model) use ($active_resources) {
                        $active = isset($active_resources[$model['project_type']][$model['project_id']]);
                        return $active ? 'Yes' : 'No';
                    },
                    'filter' => ['1' => 'Yes', '0' => 'No'],
                    'contentOptions' => ['class' => 'col-md-1 text-center', 'style' => 'vertical-align: middle!important;'],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{details} {reactivate}',
                    'header' => 'Actions',
                    'buttons' => [
                        'details' => function ($url, $model) use ($filters) {
                            return Html::a('<i class="fas fa-eye"></i> Details', [
                                '/project/view-request-user',
                                'id' => $model['project_request_id'],
                                'return' => 'admin',
                                'expired' => 1,
                                'ptype' => $filters['type'],
                                'exp' => $filters['exp'],
                                'puser' => $filters['user'],
                                'pproject' => $filters['name']
                            ], ['class' => 'btn btn-secondary btn-md']);
                        },
                        'reactivate' => function ($url, $model) {
                            return Html::a('<i class="fas fa-sync-alt"></i> Re-activate', ['/administration/reactivate', 'id' => $model['project_id']], [
                                'class' => 'btn btn-primary btn-md',
                                'title' => 'Re-activate project',
                                'data-confirm' => 'Are you sure you want to re-activate project "' . Html::encode($model['name']) . '"?',
                                'data-method' => 'post',
                            ]);
                        },

                    ],
                ],
            ],
        ]) ?>
    </div>
</div>

<?php foreach ($dataProviderExpired->getModels() as $res): ?>
    <?php $modalId = 'reactivate-' . $res['project_id'] . '-modal'; ?>
    <div class="modal fade" id="<?= Html::encode($modalId) ?>" tabindex="-1" role="dialog" aria-labelledby="reactivate-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm re-activation</h5>
                    <button type="button" class="close btn-cancel-modal" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to re-activate project '<?= Html::encode($res['name']) ?>'?
                </div>
                <div class="modal-footer">
                    <?= Html::beginForm(['/administration/reactivate', 'id' => $res['project_id']], 'post') ?>
                    <?= Html::submitButton('<i class="fas fa-sync-alt"></i> Re-activate', [
                        'class' => 'btn btn-primary btn-md',
                        'title' => 'Re-activate project',
                        'onclick' => 'this.disabled=true;this.form.submit();'
                    ]) ?>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
    $(document).on('click', '.reactivate_btn', function (e) {
        e.preventDefault();
        const modalId = $(this).data('modal-id');
        if (modalId) {
            $('#' + modalId).modal('show');
        }
    });
</script>
