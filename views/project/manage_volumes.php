<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\components\Headers;



// $this->registerJsFile('@web/js/project/project-request.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->title="Manage volume";

if ($ret == 'a'){
	$return='administration/storage-volumes';
}else{
	$return='project/storage-volumes';
}
$unlink_icon='<i class="fas fa-unlink"></i>';
$link_icon='<i class="fas fa-link"></i>'; 
?>




Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'Manage volume',
'buttons'=>
    [
        
        ['fontawesome_class'=>'<i class="fas fa-arrow-left"></i>','name'=> 'Back', 'action'=>[$return],
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
    if(empty($volume->vm_id))
    { 
        $form = ActiveForm::begin($form_params); 
    ?>
        <div class="row">
            <div class="col-md-offset-3 col-md-7"> <h2> Select VM to attach the volume</h2>
                <?= $form->field($volume, 'new_vm_id')->dropDownList($volume->vm_dropdown)->label('') ?>
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
                <?= Html::textInput('detach',$vm_name,['class'=>'form-control', 'disabled'=>true])?>
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-offset-3 col-md-7"><?= Html::a("$unlink_icon Detach",
                ['project/detach-volume-from-vm', 'id'=>$pid, 'vid'=>$volume->id,'ret'=>$ret],
                ['class' => 'btn btn-danger']) ?>
            </div>
        </div>
    <?php
    }?>
</div>
