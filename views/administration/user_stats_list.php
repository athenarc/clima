<?php


/**
 * This view file prints the history of software runs made by a user
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title="User list";

echo Html::cssFile('@web/css/administration/user-stats-list.css');
$this->registerJsFile('@web/js/administration/user-stats-list.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$back_icon='<i class="fas fa-arrow-left"></i>';
$stats_icon='<i class="fas fa-chart-line"></i>';
$active_icon='<i class="fas fa-check"></i>';
$inactive_icon='<i class="fas fa-times"></i>';
/*
 * Users are able to view the name, version, start date, end date, mountpoint 
 * and running status of their previous software executions. 
 */
?>
<div class='title row'>
    <div class="col-md-11">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>
    <div class="col-md-1 float-right">
        <?= Html::a("$back_icon Back", ['/administration/index'], ['class'=>'btn btn-default']) ?>
    </div>
</div>

<div class="row">&nbsp;</div>

<div class="filters-div">
    <h4 class="text-center">Filter</h4>
    <?=Html::beginForm(['administration/user-stats-list'],'post',['id'=>'filters-form'])?>
        <div class="row">
            <div class="col-md-12 text-center">
                <?=Html::label('By username:')?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-center">
                <?=Html::input(null,'username',$username,['class'=>'username_field'])?>
            </div>
        </div>

        <div class="row">&nbsp;</div>

        <div class="row">
            <div class="col-md-12 text-center">
                <?=Html::label('By status:')?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-center">
                <?=Html::dropDownList('activeFilter',$activeFilter,$activeFilterDrop, ['id'=>'activeDrop'])?>
            </div>
        </div>

    <?=Html::endForm()?>
</div>

<div class="row">
    <div class="col-md-12 text-right">
        <strong>Active users:</strong> <?=$activeUsers?>, <strong>Total users:</strong> <?=$totalUsers?>
    </div>
</div>
<div class="row">&nbsp;</div>
<?php
if (!empty($users))
{
?>
<table class="table table-striped">
  <thead>
    <tr>
      <th class="col-md-3" scope="col">Username</th>
      <th scope="col-md-3" scope="col">E-mail</th>
      <th scope="col-md-2" scope="col">Active</th>
      <th scope="col-md-2" scope="col"># Active Projects</th>
      <th scope="col-md-2" scope="col">View statistics</th>
    </tr>
  </thead>
  <tbody>
  <?php
    foreach ($users as $name => $user)
    {
  ?>
    <tr>
      <td class="col-md-3"><?=explode('@',$user['username'])[0]?></td>
      <td scope="col-md-3"><?=$user['email']?></td>
      <td scope="col-md-2"><?=$user['active']==0 ?$inactive_icon:$active_icon?></td>
      <td scope="col-md-2"><?=$user['active']?></td>
      <td scope="col-md-2"><?=Html::a("$stats_icon Statistics", ['/administration/user-statistics', 'id'=>$user['id']],['class'=>'btn btn-primary'])?></td>
    </tr>
  <?php
    }
  ?>
  </tbody>
</table>
<?php
}
else
{
?>
    <div class="row">
        <div class="col-md-12 text-center">
            <h3>No users matching the search criteria were found.</h3>
        </div>
    </div>
<?php
}
?>
