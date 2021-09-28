<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\components\Headers;

$this->title = "Multiple machine management";


// echo Html::CssFile('@web/css/project/vm-configure.css');
// $this->registerJsFile('@web/js/project/vm-configure.js', ['depends' => [\yii\web\JqueryAsset::className()]]);



Headers::begin() ?>
<?php echo Headers::widget(
['title'=>'Multiple VM management', 
	'buttons'=>
	[
		['fontawesome_class'=>'<i class="fas fa-arrow-left"></i>','name'=> 'Back', 'action'=>['/project/index'],
		 'options'=>['class'=>'btn btn-default'], 'type'=>'a'] 
	],
])
?>
<?Headers::end()?>


<table class="table table-striped">
  <thead>
    <tr>
      <th class="col-md-1 text-center" scope="col">#</th>
      <th class="col-md-3 text-center" scope="col">IP Address</th>
      <th class="col-md-5 text-center" scope="col">Attachments</th>
      <th class="col-md-3 text-center" scope="col"></th>
    </tr>
  </thead>
  <tbody>
<?php
	for ($i=1; $i<=$num_of_vms; $i++)
	{
		$manage_link=Url::to(['/project/machine-compute-configure-vm','id'=>$project_id,'backTarget'=>'m','multOrder'=>$i]);
		$btn_name=isset($vms[$i])?'Details':'Create';
		$btn_class=isset($vms[$i])?'btn btn-default':'btn btn-secondary';
		$manage_btn=Html::a($btn_name,$manage_link,['class'=>$btn_class]);
		if (isset($vms[$i]))
		{
			/*
			 * VM has been created
			 */
			if (isset($storage[$i]))
			{
				/*
				 * There are volume attachments
				 */
				$attachments='';
				foreach ($storage[$i] as $vol)
				{
					$attachments.=$vol['name'] . ' on ' . $vol['mountpoint'] . "<br />";
				}
			}
			else
			{
				/*
				 * No volume attachments
				 */
				$attachments="None";

			}

		}
		else
		{
			/*
			 * There is no VM created
			 */
			$attachments='N/A';
		}
?>
		<tr>
	      <th class="col-md-1 text-center" scope="row">VM<?=$i?></th>
	      <td class="col-md-3 text-center"><?=isset($vms[$i])?$vms[$i]->ip_address:'N/A'?></td>
	      <td class="col-md-5 text-center"><?=$attachments?></td>
	      <td class="col-md-3 text-center"><?=$manage_btn?></td>
	    </tr>
<?php
	}
?>
  </tbody>
</table>

 

