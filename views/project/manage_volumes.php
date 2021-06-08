<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\components\Headers;



// $this->registerJsFile('@web/js/project/project-request.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->title="Manage volume";

$unlink_icon='<i class="fas fa-unlink"></i>';
$link_icon='<i class="fas fa-link"></i>'; 
?>




Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'Manage volume',
'buttons'=>
    [
        
        ['fontawesome_class'=>'<i class="fas fa-arrow-left"></i>','name'=> 'Back', 'action'=>['/project/storage-volumes', 'project_id'=>$project_id],
         'options'=>['class'=>'btn btn-default'], 'type'=>'a'] 
    ],
])
?>
<?Headers::end()?>



<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="col-md-12 text-center">

<div class="row"><div class="col-md-12"></div></div>
    
    <?php
    if(empty($vm_id))
    { 
        $form = ActiveForm::begin(); 
    ?>
        <div class="row">
            <div class="col-md-offset-3 col-md-7"> <h2> Select VM to attach the volume</h2>
                <?= $form->field($hotvolume, 'vm_id')->dropDownList($vms_dropdown)->label('') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-offset-3 col-md-7"><?= Html::submitButton("$link_icon Attach",['class' => 'btn btn-success']) ?>
            </div>
        </div>
    <?php ActiveForm::end(); 
    }
    else
    {?>
        <div class="row">
            <div class="col-md-offset-3 col-md-7"> <h2> Detach the volume from the VM</h2>
                <?= Html::textInput('detach',$project_name,['class'=>'form-control', 'disabled'=>true])?>
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-offset-3 col-md-7"><?= Html::a("$unlink_icon Detach",
                ['project/detach-volume-from-vm', 'volume_id'=>$volume_id, 'vm_id'=>$vm_id],
                ['class' => 'btn btn-danger']) ?>
            </div>
        </div>
    <?php
    }?>
</div>
