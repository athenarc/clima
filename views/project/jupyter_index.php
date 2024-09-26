<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\Headers;
use app\models\User;
use yii\widgets\ActiveForm;


$delete_icon = '<i class="fas fa-times"></i>';
$back_icon = '<i class="fas fa-arrow-left"></i>';
$back_link = 'index';
$start_icon = '<i class="fas fa-play"></i>';
$stop_icon = '<i class="fas fa-stop"></i>';

$this->title = $name['name'];
$this->registerJsFile('@web/js/administration/edit-text-area.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
// echo Html::CssFile('@web/css/project/project-request.css');
// $this->registerJsFile('@web/js/project/project-request.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
Headers::begin()
    ?>
<script src="https://cdn.ckeditor.com/ckeditor5/41.3.0/classic/ckeditor.js"></script>
<?php
echo Headers::widget(
    [
        'title' => $this->title,
        'buttons' =>
            [
                [
                    'fontawesome_class' => $back_icon,
                    'name' => 'Back',
                    'action' => [$back_link],
                    'type' => 'a',
                    'options' => ['class' => 'btn btn-default']
                ]
            ],
    ]
);
Headers::end();
$owner_username = User::returnUsernameById($owner['submitted_by']);
// $remove_url=Url::to(['index.php?r=project%2Findex','id'=>$id]);
$remove_url = Url::to(['delete-user', 'id' => $id, 'pid' => $pid]);
//participants view

if ($owner['submitted_by'] != $current_user) {
    ?>

    <font size="4"> <b>Project description</b><br></font>

    <div class="row">
        <div class="col-md-12">
            <div class="well details-form" style="margin: 2px;
                                                           background-color: white !important;
                                                           -webkit-appearance: none;
                                                           ">
                <div><?= $view ?></div>
            </div>
        </div>
    </div>

    <div class=" table-responsive">
        <table class="table table-striped">
            <thead>
                <th class="col-md-1">Owner</th>
                <th class="col-md-1">Cores</th>
                <th class="col-md-1">RAM</th>
                <th class="col-md-3">Image</th>
                <th class="col-md-2">Expires on</th>
                <th class="col-md-1"></th>
            </thead>
            <tbody>
                <tr>
                    <td class="col-md-1"><?= explode('@', $owner_username)[0] ?></td>
                    <td class="col-md-1"><?= $cpu ?></td>
                    <td class="col-md-1"><?= $ram ?></td>
                    <?php
                    $start_url = Url::to(['jupyter-start-server', 'project' => $name['name'], 'pid' => $pid, 'id' => $id]);
                    $stop_url = Url::to(['jupyter-stop-server', 'project' => $name['name'], 'id' => $id, 'pid' => $pid, 'user_delete' => '']);
                    if (!empty($server)) {
                        $image = $server['image'];
                        $started = true;
                        $start_class = "btn start-btn disabled";
                        $stop_class = "btn stop-btn";
                        $access_class = "btn access-btn";

                        $access_url = $server->url;
                        $access_title = 'Access server';
                        $access_icon = '<i class="fas fa-external-link-alt"></i>';
                        $access_target = '_blank';
                    } else {
                        $image = $image_id;
                        $started = false;
                        $start_class = "btn start-btn";
                        $stop_class = "btn stop-btn disabled";
                        $access_class = "btn access-btn disabled";
                        $access_url = '';
                        $access_title = 'Please start the server';
                        $access_icon = '<i class="fas fa-external-link-alt"></i>';
                        $access_target = '';
                    }
                    ?>

                    <td class="col-md-3"><?= $image ?></td>
                    <td class="col-md-2"><?= $end_date ?></td>
                    <td class="col-md-2">
                        <?= $started ? '' : Html::a($start_icon, $start_url, ['class' => $start_class, 'title' => "Start server"]) ?>
                        <?= $started ? Html::a($stop_icon, $stop_url, [
                            'class' => $stop_class,
                            'title' => "Delete server",
                            'data' => [
                                'confirm' => 'Are you sure you want to delete your server ?',
                                'method' => 'post',
                            ],
                        ]) : '' ?>
                        <?= Html::a($access_icon, $access_url, ['class' => $access_class, 'title' => $access_title, "target" => $access_target]) ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php

    // owner's view 
} else {
    ?>
    <div class="row">
        <div class="col-md-12">
            <font size="4" style="display: inline-block;">
                <b>Project description</b>
            
                <!-- <?= Html::button('<i class="fas fa-edit"></i> Edit', [
                    'class' => 'btn btn-default',
                    'id' => 'Edit',
                    'style' => '',
                    'value' => 'Edit'
                ]) ?> -->
            </font>

            <div class='btn-toolbar pull-right'>
                <div class="btn-toolbar">
                    <div class="btn-group">
                        <?php $form = ActiveForm::begin(); ?>
                        <?= Html::submitButton('<i class="fas fa-save"></i> Save', [
                            'class' => 'btn btn-link',
                            'id' => 'save',
                            'style' => 'display:none; padding-right: 0!important; color: #069;',
                            'value' => 'Save'
                        ]) ?>
                        <?= Html::button('<i class="fas fa-window-close"></i> Cancel', [
                            'class' => 'btn btn-link',
                            'id' => 'cancel',
                            'style' => 'display:none; padding-right: 0!important; color: #069;',
                            'value' => 'Cancel'
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="well details-form" id="tag"
                style="background-color: white !important; -webkit-appearance: none; cursor: pointer;">
                <div><?=$view?></div>
            </div>
            <?= $form->field($jup, 'participant_view')->label('')->textarea(['style' => 'width: 100%; display: none;', 'id' => 'mytextarea']) ?>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <div class=" table-responsive">
        <table class="table table-striped">
            <thead>
                <th class="col-md-1">Cores</th>
                <th class="col-md-1">RAM</th>
                <th class="col-md-3">Image</th>
                <th class="col-md-2">Expires on</th>
            </thead>
            <tbody>
                <tr>
                    <td class="col-md-1"><?= $cpu ?></td>
                    <td class="col-md-1"><?= $ram ?></td>
                    <?php
                    if (!empty($server)) {
                        $image = $server['image'];
                    } else {
                        $image = $image_id;
                    }
                    ?>
                    <td class="col-md-3"><?= $image ?></td>
                    <td class="col-md-2"><?= $end_date ?></td>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php

    $users = explode(",", $participants);
    $start_url = Url::to(['jupyter-start-server', 'project' => $name['name'], 'pid' => $pid, 'id' => $id]);
    $stop_url = Url::to(['jupyter-stop-server', 'project' => $name['name'], 'id' => $id, 'pid' => $pid, 'user_delete' => '']);
    $remove_class = "btn remove-btn disabled";
    ?>

    <div class=" table-responsive">
        <table class="table table-striped">
            <thead>
                <th class="col-md-1">Participants</th>
                <th class="col-md-1">Server running</th>
                <th class="col-md-1"></th>
            </thead>
            <tbody>
                <td class="col-md-1" style="font-weight:bold"><?= explode('@', $owner_username)[0] . ' (you)' ?></td>
                <?php

                //first find the owner of the project
                if (!empty($server)) {

                    $started = true;
                    $start_class = "btn start-btn disabled";
                    $stop_class = "btn stop-btn";
                    $access_class = "btn access-btn";
                    $stop_url = Url::to(['jupyter-stop-server', 'project' => $name['name'], 'id' => $id, 'pid' => $pid, 'user_delete' => explode('@', $owner_username)[0]]);
                    $access_url = $server->url;
                    $access_title = 'Access server';
                    $access_icon = '<i class="fas fa-external-link-alt"></i>';
                    $access_target = '_blank';
                    ?>
                    <td class="col-md-1"><?= 'yes' ?></td>
                    <?php

                } else {

                    $started = false;
                    $start_class = "btn start-btn";
                    $stop_class = "btn stop-btn disabled";
                    $access_class = "btn access-btn disabled";
                    $access_url = '';
                    $access_title = 'Please start the server';
                    $access_icon = '<i class="fas fa-external-link-alt"></i>';
                    $access_target = '';
                    ?>
                    <td class="col-md-1"><?= 'no' ?></td>
                    <?php
                }

                ?>

                <td class="col-md-1">
                    <?= $started ? '' : Html::a($start_icon, $start_url, ['class' => $start_class, 'title' => "Start server"]) ?>
                    <?= $started ? Html::a($stop_icon, $stop_url, [
                        'class' => $stop_class,
                        'title' => "Delete server",
                        'data' => [
                            'confirm' => 'Are you sure you want to delete your server?',
                            'method' => 'post',
                        ],
                    ]) : '' ?>
                    <?= Html::a($access_icon, $access_url, ['class' => $access_class, 'title' => $access_title, "target" => $access_target]) ?>
                    <?= Html::a($delete_icon, $remove_url, ['class' => $remove_class, 'title' => "Remove user"]) ?>

                    <tr>

                        <?php

                        if (!empty($all_servers)) {
                            foreach ($users as $user) {

                                //print all the other participants
                                $found = 0;
                                if ($user . '@elixir-europe.org' != $owner_username) {
                                    $remove_class = "btn start-btn";
                                    ?>
                                    <td class="col-md-1"><?= $user ?></td>
                                    <?php

                                    foreach ($all_servers as $one_server) {
                                        $server_user = explode('@', $one_server['created_by'])[0];

                                        $user_delete = $user;
                                        $remove_url = Url::to(['delete-user', 'id' => $id, 'pid' => $pid, 'user' => $user]);
                                        if (explode('@', $user)[0] == $server_user) {
                                            $started = true;
                                            $start_class = "btn start-btn disabled";
                                            $stop_class = "btn stop-btn";
                                            $stop_url = Url::to(['jupyter-stop-server', 'project' => $name['name'], 'id' => $id, 'pid' => $pid, 'user_delete' => $user_delete]);
                                            $access_class = "btn access-btn";
                                            $access_url = $one_server->url;
                                            $access_title = 'Access server';
                                            $access_icon = '<i class="fas fa-external-link-alt"></i>';
                                            $access_target = '_blank';
                                            $found = 1;
                                            ?>
                                            <td class="col-md-1"><?= 'yes' ?></td>
                                            <?php
                                        }
                                    }
                                    if ($found == 0) {
                                        $started = false;
                                        $start_class = "btn start-btn disabled";
                                        $stop_class = "btn stop-btn disabled";
                                        $access_class = "btn access-btn disabled";
                                        $access_url = '';
                                        $access_title = 'Please start the server';
                                        $access_icon = '<i class="fas fa-external-link-alt"></i>';
                                        $access_target = '';
                                        ?>
                                        <td class="col-md-1"><?= 'no' ?></td>
                                        <?php
                                    }
                                    ?>
                                    <td class="col-md-1">
                                        <?= $started ? '' : Html::a($start_icon, $start_url, ['class' => $start_class, 'title' => "Start server"]) ?>
                                        <?= $started ? Html::a($stop_icon, $stop_url, [
                                            'class' => $stop_class,
                                            'title' => "Delete server",
                                            'data' => [
                                                'confirm' => 'Are you sure you want to delete the server of user ' . $user . ' ?',
                                                'method' => 'post',
                                            ],
                                        ]) : '' ?>
                                        <?= Html::a($access_icon, $access_url, ['class' => $access_class, 'title' => $access_title, "target" => $access_target]) ?>
                                        <?= Html::a($delete_icon, $remove_url, [
                                            'class' => $remove_class,
                                            'title' => "Remove user",
                                            'data' => [
                                                'confirm' => 'Are you sure you want to remove user ' . $user . ' from the project?',
                                                'method' => 'post',
                                            ],
                                        ]) ?>


                                <tr>
                            </td>


                            <?php
                                }
                            }

                        } else {
                            $started = false;
                            $start_class = "btn start-btn disabled";
                            $stop_class = "btn stop-btn disabled";
                            $access_class = "btn access-btn disabled";
                            $access_url = '';
                            $access_title = 'Please start the server';
                            $access_icon = '<i class="fas fa-external-link-alt"></i>';
                            $access_target = '';
                            $remove_class = "btn start-btn";
                            foreach ($users as $user) {
                                if ($user . '@elixir-europe.org' != $owner_username) {
                                    $remove_class = "btn start-btn";
                                    $remove_url = Url::to(['delete-user', 'id' => $id, 'pid' => $pid, 'user' => $user]);
                                    ?>
                            <td class="col-md-2"><?= $user ?></td>
                            <td class="col-md-1"><?= 'no' ?></td>
                            <td class="col-md-1">

                                <?= $started ? '' : Html::a($start_icon, $start_url, ['class' => $start_class, 'title' => "Start server"]) ?>
                                <?= $started ? Html::a($stop_icon, $stop_url, ['class' => $stop_class, 'title' => "Delete server"]) : '' ?>
                                <?= Html::a($access_icon, $access_url, ['class' => $access_class, 'title' => $access_title, "target" => $access_target]) ?>
                                <?= Html::a($delete_icon, $remove_url, [
                                    'class' => $remove_class,
                                    'title' => "Remove user",
                                    'data' => [
                                        'confirm' => 'Are you sure you want to remove user ' . $user . ' from the project?',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                                <tr>
                            </td>
                            <?php

                                }
                            }
                            ?>

                    <?php
                        }
                        ?>
            </tbody>
        </table>

    </div>
    <?php
}
?>