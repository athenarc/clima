<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\components\MagicSearchBox;
use webvimark\modules\UserManagement\models\User as Userw;

/* @var $this yii\web\View */
/* @var $model app\models\ServiceRequest */
/* @var $form ActiveForm */

echo Html::CssFile('@web/css/site/configure.css');
$this->registerJsFile('@web/js/site/configure.js', ['depends' => [\yii\web\JqueryAsset::className()]] );


$this->title = 'System Configuration';
$this->registerJsFile('@web/js/administration/configure.js', ['depends' => [\yii\web\JqueryAsset::class]]);

?>

<h1><?= Html::encode($this->title) ?></h1>

<!-- Tabs -->
<div class="row category-tabs mb-3">
    <div class="tab-button btn btn-light me-2" data-tab="general" style="margin-right:10px;">General</div>
    <div class="tab-button btn btn-light me-2" data-tab="jupyter" style="margin-right:10px;">Jupyter Projects</div>
    <div class="tab-button btn btn-light me-2" data-tab="machines" style="margin-right:10px;">On-Demand Machines</div>
    <div class="tab-button btn btn-light me-2" data-tab="ondemand" style="margin-right:10px;">On-Demand</div>
    <div class="tab-button btn btn-light me-2" data-tab="service" style="margin-right:10px;">24/7 Service</div>
    <div class="tab-button btn btn-light me-2" data-tab="storage" style="margin-right:10px;">Storage</div>



</div>
<div class="row category-tabs mb-3">
        <div class="tab-button btn btn-light me-2" data-tab="smtp" style="margin-right:10px;">SMTP Configuration</div>
        <div class="tab-button btn btn-light me-2" data-tab="openstack" style="margin-right: 10px;">OpenStack Configuration</div>
        <div class="tab-button btn btn-light me-2" data-tab="openstack-machines" style="margin-right:10px;">OpenStack Machines</div>
        <div class="tab-button btn btn-light me-2" data-tab="extensions" style="margin-right:10px;">Extension Limits</div>


</div>


<div id="config-tab-content">
</div>
<?php
$this->registerJs(<<<JS
    // Auto-load General tab on page load
    $(function () {
        $.get('index.php?r=administration/load-tab&tab=general', function (html) {
            $('#config-tab-content').html(html);
        });
    });
JS);
?>

