<?php
/** @var \ricco\ticket\models\TicketHead $dataProvider */
use app\models\TicketHead;
use app\models\TicketBody;
$u=1;
?>

<div class="panel page-block">
    <div class="container-fluid row">
    <div class="col-md-1">
            <a href="<?= \yii\helpers\Url::toRoute(['ticket-admin/open']) ?>" class="btn btn-primary" style="margin-bottom: 15px; margin-left:15px;">Open new ticket</a></div>
    <br><br>
    <div class="container-fluid">
        <div class="col-lg-12">
            <?= \yii\grid\GridView::widget([
                'dataProvider' => $dataProvider,
                'rowOptions'   => function ($model) {
                    $background = '';
                    if ($model->status == 0 || $model->status == 1) {
                        $background = 'background:#E6E6FA';
                    }
                    return [
                        'style'   => "c" . $background,
                    ];
                },
                'columns'      => [
                    [
                        'attribute' => 'userName',
                        //$user = 'userName.username',
                        'value'     => function($model){
                            $user = $model['userName'];
                            return explode('@',$user->username)[0];
                        }
                    ],
                    [
                        'attribute' => 'department',
                        'format' => 'text',
                        'label' => 'Ticket category',
                    ],
                    [
                        'attribute' => 'topic',
                        'format' => 'text',
                        'label' => 'Ticket subject',
                    ],
                    [
                        
                        'enableSorting' => true,
                        'format' => 'integer',
                        'label' => 'No of answers',
                        'value' => function($model){
                            $tickets = TicketBody::find()->where(['id_head' => $model['id']])->joinWith('file')->asArray()->orderBy('date DESC')->all();
                            $answers = 0;
                            foreach ($tickets as $t){
                                $who = $t['client'] == 1 ? 'Administrator' : 'User';
                                if ($who == 'Administrator'){
                                    $answers++;
                                }
                            }
                            if (count($tickets) == $answers) {
                                $answers-- ;
                            }
                            return $answers;
                        },
                    ],
                    [
                        'attribute' => 'status',
                        'value'     => function ($model) {
                            switch ($model->body['client']) {
                                case 0 :
                                    if ($model->status == TicketHead::CLOSED) {
                                        return '<div class="label label-success">User</div>&nbsp;<div class="label label-default">Closed</div>';
                                    }

                                    return '<div class="label label-success">User</div>';
                                case 1 :
                                    if ($model->status == TicketHead::CLOSED) {
                                        return '<div class="label label-primary">Administrator</div>&nbsp;<div class="label label-default">Closed</div>';
                                    }

                                    return '<div class="label label-primary">Open</div>';
                            }
                        },
                        'format'    => 'html',
                    ],
                    [
                        'attribute' => 'date_update',
                        'value'     => 'date_update',
                    ],
                    [
                        'class'         => 'yii\grid\ActionColumn',
                        'template'      => '{update}&nbsp;{delete}&nbsp;{closed}&nbsp;{reopen}',
                        'headerOptions' => [
                            'style' => 'width:230px',
                        ],
                        'buttons'       => [
                            'update' => function ($url, $model) {
                                return \yii\helpers\Html::a('Answer',
                                    \yii\helpers\Url::toRoute(['/ticket-admin/answer', 'url1' => $_SERVER['REQUEST_URI'], 'mode' => 0, 'id' => $model['id']]),
                                    ['class' => 'btn-xs btn-info']);
                            },
                            'delete' => function ($url, $model) {
                                return \yii\helpers\Html::a('Delete',
                                    \yii\helpers\Url::to(['/ticket-admin/delete', 'id' => $model['id']]),
                                    [
                                        'class'   => 'btn-xs btn-danger',
                                        'onclick' => 'return confirm("Are you sure you want to delete the ticket?")',
                                    ]
                                );
                            },
                            'closed' => function ($url, $model) {
                                return \yii\helpers\Html::a('Close',
                                    \yii\helpers\Url::to(['/ticket-admin/closed', 'id' => $model['id']]),
                                    [
                                        'class'   => 'btn-xs btn-dark',
                                        'onclick' => 'return confirm("Are you sure you want to close the ticket?")',
                                    ]
                                );
                            },
                            'reopen' => function ($url, $model) {
                                return \yii\helpers\Html::a('Re-open',
                                    \yii\helpers\Url::to(['ticket-admin/reopen', 'id' => $model['id']]),
                                    [
                                        'class'   => 'btn-xs btn-warning',
                                        'onclick' => 'return confirm("Are you sure you want to re-open the ticket?")',
                                    ]
                                );
                            },
                        ],
                    ],
                    [
                        'class'         => 'yii\grid\ActionColumn',
                        'template'      => '{view}&nbsp',
                        'headerOptions' => [
                            'style' => 'width:230px',
                        ],
                        'buttons'       => [
                            'view' => function ($url, $model) {
                                return \yii\helpers\Html::a('View',
                                    \yii\helpers\Url::toRoute(['/ticket-admin/answer', 'url1' => $_SERVER['REQUEST_URI'], 'id' => $model['id'], 'mode' => 1]),
                                    ['class' => 'btn-xs btn-primary']);
                            },
                        ]   
                    ]
                ],
            ]) ?>
        </div>
    </div>
</div></div>
