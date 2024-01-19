<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\Headers;
use app\models\Project;


/*
 * Add stylesheet
 */

// echo Html::cssFile('@web/css/project/index.css');
// $this->registerJsFile('@web/js/software/index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$this->title="Active Jupyter servers";

$back_icon='<i class="fas fa-arrow-left"></i>';
$expired_icon='<i class="fas fa-exclamation-triangle"></i>';
Headers::begin() ?>
<?php echo Headers::widget(
['title'=>$this->title, 
    'buttons'=>
    [

        ['fontawesome_class'=>$expired_icon, "name"=> 'Clear expired servers','action'=>['project/stop-expired-jupyter-servers'],'options'=>['class'=>"btn btn-danger btn-md delete-volume-btn",'data' => [
            'confirm' => 'Are you sure you want to delete all the expired servers ?',
            'method' => 'post',
            ],], 'type'=>'a'],
        ['fontawesome_class'=>$back_icon,'name'=> 'Back', 'action'=>['administration/index'],
        'options'=>['class'=>'btn btn-default'], 'type'=>'a']
        
    ],
])
?>
<?php Headers::end()?>

<div class=" table-responsive">
    <table class="table table-striped">
        <thead>
            <th class="col-md-1">Created by</th>
            <th class="col-md-2">Project</th>
            <th class="col-md-3">Image</th>
            <th class="col-md-1">Created on</th>
            <th class="col-md-2">Expires on</th>
            <th class="col-md-2"></th>
        </thead>
        <tbody>
        <?php
        $now=new DateTime();

        foreach ($servers as $server)
        {
            $creation=new DateTime($server->created_at);
            $creation=$creation->format('d-m-Y');
            $expiration=new DateTime($server->expires_on);
            if ($now>$expiration)
            {
                $expiration=$expiration->format('d-m-Y') . '&nbsp;' . $expired_icon;
            }
            else
            {
                $expiration=$expiration->format('d-m-Y');
            }
            
        ?>
        <tr>
            <td class="col-md-1"><?=explode('@',$server->created_by)[0]?></td>
            <td class="col-md-2"><?=$server->project?></td>
            <td class="col-md-3"><?=$server->image?></td>
            <td class="col-md-1"><?=$creation?></td>
            <td class="col-md-2"><?=$expiration?></td>
            <td class="col-md-2">
                <?php

                    $pid = Project::find('id')->where(['name'=>$server->project])->one();
                    $id =  Project::find('latest_project_request_id')->where(['name'=>$server->project])->one();
                    $stop_icon='<i class="fas fa-stop"></i>';
                    $access_icon='<i class="fas fa-external-link-alt"></i>';
                    $stop_url=Url::to(['project/jupyter-stop-server','project'=>$server->project,'return'=>'a', 'id'=>$id['latest_project_request_id'], 'pid'=>$pid['id'], 'user_delete'=>explode('@',$server->created_by)[0]]);
                    $stop_class="btn stop-btn";
                    $access_class="btn access-btn";
                    $access_url=$server->url;
                ?>
                <?=Html::a($stop_icon,$stop_url,['class'=>$stop_class, 'title'=> "Stop server",  'data' => [
                                'confirm' => 'Are you sure you want to delete the server ?',
                                'method' => 'post',
                                ],])?>
                <?=Html::a($access_icon,$access_url,['class'=>$access_class, 'title'=> "Access server", "target"=>"_blank"])?>
            </td>

        </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
</div>