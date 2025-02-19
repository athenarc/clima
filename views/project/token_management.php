<?php
use yii\helpers\Html;
use app\components\Headers;
use app\models\Token;

echo Html::CssFile('@web/css/project/tokens.css');
$this->registerJsFile('@web/js/project/index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$back_icon = '';
$new_icon = '<i class="fas fa-plus-circle"></i>';
$mode = 0;
Headers::begin();
?>
<?php
echo Headers::widget([
    'title' => 'API keys management',
    'subtitle' => $project->name,
    'buttons' => [
        [
            'fontawesome_class' => $back_icon,
            'name' => 'Back',
            'action' => ['project/on-demand-access', 'id' => $project->id],
            'type' => 'a',
            'options' => ['class' => 'btn btn-default', 'style' => 'width: 100px; color: grey']
        ]
    ],
]);
?>
<?php Headers::end(); ?>

<br>
<p>This page allows for the creation and management of API authentication & authorization keys that are used for running computational jobs in the context of an approved project. Keep in mind that the creation of multiple tokens for the same project is supported.</p>
<br>

<div style="float: right; text-align: center;">
    <?=Html::a("$new_icon New API key",['/project/new-token-request','id'=>$requestId, 'mode'=>$mode, 'uuid'=>$mode],['class'=>'btn btn-success create-vm-btn', 'style'=>'width:120px'])?>
</div>

<!-- Active API Keys Section -->
<div class="row">
    <h3 class="col-md-12">
        Active API keys (<?= is_array($active_tokens) ? count($active_tokens) : 0 ?>)
        <i class="fas fa-chevron-up" id="active-arrow" title="Hide active API keys" style="cursor: pointer" onclick="toggleSection('active-table', 'active-arrow')"></i>
    </h3>
</div>

<?php if (!empty($active_tokens)): ?>
    <div class="table-responsive" id="active-table" >
        <table class="table table-striped">
            <thead>
            <tr>
                <th class="col-md-2">Token name</th>
                <th class="col-md-2">Expires in</th>
                <th class="col-md-3">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($active_tokens as $token): ?>
                <?php
                $title = $token['title'] ?? 'Unknown';
                $expiry_date = new DateTime($token['expiry']);
                $remaining_days = $expiry_date->diff(new DateTime())->days;
                $uuid = $token['uuid'];
                ?>
                <tr>
                    <td><?= Html::encode($title) ?></td>
                    <td><?= Html::encode($remaining_days) . " days" ?></td>
                    <td>
<!--                        --><?php //= Html::a("Edit", ['/project/new-token-request', 'id' => $requestId, 'mode' => 1, 'uuid' => $uuid], ['class' => 'btn btn-secondary', 'style' => 'width:90px']) ?>
<!--                        --><?php //= Html::a("Delete", ['/project/new-token-request', 'id' => $requestId, 'mode' => 2, 'uuid' => $uuid], [
//                            'class' => "btn btn-secondary",
//                            'style' => 'width:90px',
//                            'data' => [
//                                'confirm' => "Are you sure you want to delete the token {$title}?",
//                                'method' => 'post',
//                            ],
//                        ]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Expired API Keys Section -->
<div class="row">
    <h3 class="col-md-12">
        Expired API keys (<?= is_array($expired_tokens) ? count($expired_tokens) : 0 ?>)
        <i class="fas fa-chevron-up" id="expired-arrow" title="Hide expired API keys" style="cursor: pointer" onclick="toggleSection('expired-table', 'expired-arrow')"></i>
    </h3>
</div>

<?php if (!empty($expired_tokens)): ?>
    <div class="table-responsive" id="expired-table" >
        <table class="table table-striped">
            <thead>
            <tr>
                <th class="col-md-2">Token name</th>
                <th class="col-md-2">Expired since</th>
                <th class="col-md-3">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($expired_tokens as $token): ?>
                <?php
                $title = $token['title'] ?? 'Unknown';
                $expiry_date = new DateTime($token['expiry']);
                $expired_days = $expiry_date->diff(new DateTime())->days;
                $uuid = $token['uuid'];
                ?>
                <tr>
                    <td><?= Html::encode($title) ?></td>
                    <td><?= Html::encode($expired_days) . " days ago" ?></td>
                    <td>
<!--                        --><?php //= Html::a("Edit", ['/project/new-token-request', 'id' => $requestId, 'mode' => 1, 'uuid' => $uuid], ['class' => 'btn btn-secondary', 'style' => 'width:90px']) ?>
<!--                        --><?php //= Html::a("Delete", ['/project/new-token-request', 'id' => $requestId, 'mode' => 2, 'uuid' => $uuid], [
//                            'class' => "btn btn-secondary",
//                            'style' => 'width:90px',
//                            'data' => [
//                                'confirm' => "Are you sure you want to delete the token {$title}?",
//                                'method' => 'post',
//                            ],
//                        ]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script>
    function toggleSection(tableId, arrowId) {
        const table = document.getElementById(tableId);
        const arrow = document.getElementById(arrowId);

        if (table.style.display === "none") {
            table.style.display = "block";
            arrow.classList.remove("fa-chevron-down");
            arrow.classList.add("fa-chevron-up");
        } else {
            table.style.display = "none";
            arrow.classList.remove("fa-chevron-up");
            arrow.classList.add("fa-chevron-down");
        }
    }
</script>
