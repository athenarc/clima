<?php

$this->title = 'Support';

/** @var \ricco\ticket\models\TicketHead $ticketHead */
/** @var \ricco\ticket\models\TicketBody $ticketBody */
?>
<div class="panel page-block">
    <div class="col-sx-12">
        <div class=col-md-offset-11 col-md-1>
            <?php
            if ($upgrade == 0){

            ?>
            <a class="btn btn-default col-md-offset-11" href="<?= \yii\helpers\Url::toRoute(['/ticket-user/index']) ?>"
               style="margin-bottom: 10px; margin-left: 15px">Back</a>

            <?php
            } else {
            ?>
            <a class="btn btn-default col-md-offset-11" href="<?= \yii\helpers\Url::toRoute(['/personal/user-options']) ?>"
               style="margin-bottom: 10px; margin-left: 15px">Back</a>

            <?php
            }
            ?>
            
        </div>
            <?php
                if ($upgrade == 1){
            ?>
        <div class="col-md-12">
            <div class="alert alert-warning" role="alert">
                
                 Currently, based on the position of each individual in their organisation, HYPATIA users are classified in one of the following user types:<br>
                &#x2022; <b>Bronze:</b> This type of user is the one with the less privileges and the most limitations. It is assigned as the default type upon registration. It can be used for testing or educational purposes during training events (indicatively).<br>
                &#x2022; <b>Silver user:</b> This type of user is suitable for regular lab members.<br>
                &#x2022; <b>Gold user:</b> This type of user is for Principal Investigators and it comes with extended quotas and permissions.<br><br>
                <b>If you wish to request for an upgrade please use the following form:</b>
        </div>
    </div>


            <?php
                }
            ?>
        <?php $form = \yii\widgets\ActiveForm::begin([]) ?>

        <div class="col-xs-12">
            <?= $form->field($ticketHead, 'department')->textInput()->label('Ticket category')->dropDownList($qq) ?>
        </div>
        <div class="col-xs-12">
            <?= $form->field($ticketHead, 'topic')->textInput()->label('Ticket subject')->error() ?>
        </div>
        <div class="col-xs-12">
            <?= $form->field($ticketBody, 'text')->textarea([
                'style' => 'height: 150px; resize: none;',
            ]) ?>
        </div>
        <div class="col-xs-12">
            <?= $form->field($fileTicket, 'fileName[]')->fileInput([
                'multiple' => true,
                'accept'   => 'image/*',
            ])->label('Attach a screenshot (optional)'); ?>
        </div>
        <div class="text-center">
            <button class='btn btn-primary'>Submit</button>
        </div>
        <?php $form->end() ?>
    </div>
</div>
