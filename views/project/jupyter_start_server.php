<?php
/************************************************************************************
 *
 *  Copyright (c) 2018 Thanasis Vergoulis & Konstantinos Zagganas &  Loukas Kavouras
 *  for the Information Management Systems Institute, "Athena" Research Center.
 *  
 *  This file is part of SCHeMa.
 *  
 *  SCHeMa is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  SCHeMa is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with Foobar.  If not, see <https://www.gnu.org/licenses/>.
 *
 ************************************************************************************/
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\Headers;
use yii\widgets\ActiveForm;



/*
 * Add stylesheet
 */

echo Html::cssFile('@web/css/project/start-server.css');
$this->registerJsFile('@web/js/project/start_server.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$this->title="Start Jupyter server";

$back_icon='<i class="fas fa-arrow-left"></i>';
$start_icon='<i class="fas fa-play"></i>';
$exclamation_icon='<i class="fas fa-exclamation-triangle" style="color:orange" title="The Vm belongs to an expired project"></i>';


Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
    'buttons'=>
    [
        ['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>['index'],
        'options'=>['class'=>'btn btn-default'], 'type'=>'a'], 
    ],
])
?>
<?php Headers::end();?>

<div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning" role="alert">
                <td class="col-md-2 align-middle"><?=$exclamation_icon ?></td>
                Please remember your password; you'll need it every time you access your Jupyter server, and it can't be recovered later. Your security and access are important to us!
                </div>
            </div>
        </div>
<?php $form=ActiveForm::begin($form_params); ?>

    <?=$form->field($model,'image_id')->textInput(['readonly' => true, 'value' =>$imageDrop[$image__id]])->label("Jupyter server type")?>
    <?=$form->field($model,'password')->passwordInput()?>

    <?=Html::submitButton($start_icon . '&nbsp;Start',['class'=> 'btn btn-success submit-btn'])?>
<?php ActiveForm::end(); ?>



<div class="modal fade" id="creatingModal" tabindex="-1" role="dialog" aria-labelledby="server-being-created" aria-hidden="true">
  <div class="modal-dialog modal-xl" >
    <div class="modal-content">
      <div class="modal-body text-center">
            <h3 class="modal-text "><i class="fas fa-spinner fa-spin"></i>&nbsp; Please wait while the server is being created...<br /></h3>
            <h4>This process may take a few minutes. <br /> You will be redirected automatically.</h4>
      </div>
    </div>
  </div>
</div>