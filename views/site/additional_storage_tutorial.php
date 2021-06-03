<?php


/**
 * Simple volume partitioning tutorial
 * 
 * @author: Kostis Zagganas
 * First version: March 2019
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;
use app\components\Headers;

$this->title="Additional Storage";

$back_icon='<i class="fas fa-arrow-left"></i>';

 echo Headers::widget(
['title'=>$this->title, 
	'buttons'=>
	[
		
		['fontawesome_class'=>'<i class="fas fa-arrow-left"></i>', 'name'=>'Back', 'action'=>['project/index'], 'type'=>'a', 'options'=>['class'=>'btn btn-default']]
	]
])

?>



<div class="row">&nbsp;</div>
<div class="row"><div class="col-md-12">1. List all attached disk volumes: </div></div>
<div class="code">sudo fdisk -l</div>
<div class="row"><div class="col-md-12">and find the details for your attached but unmounted disk.</div></div>

<div class="row">&nbsp;</div>

<div class="row"><div class="col-md-12">2. Partition the unmounted volume (if it is device /dev/vdb):</div></div>
<div class="code">sudo parted /dev/vdb</div>
<div class="col-md-12">Allow for partitions larger than 2TB:</div>
<div class="code "> mklabel gpt </div>
<div class="col-md-12">Create the partition:</div>
<div class="code ">mkpart primary 2048s 100% </div>
<div class="col-md-12">See the created partition and exit:</div>
<div class="code ">print </div>
<div class="code ">exit </div>

<div class="row">&nbsp;</div>

<div class="row"><div class="col-md-12">3. Format the new partition:</div></div>
<div class="code">sudo mkfs.ext4 /dev/vdb1</div>

<div class="row">&nbsp;</div>

<div class="row"><div class="col-md-12">4. Create the directory mount point and mount the new disk:</div></div>
<div class="code">sudo mkdir /data </div>
<div class="code">sudo mount /dev/vdb1 /data</div>

<div class="row">&nbsp;</div>

<div class="row"><div class="col-md-12">5. Add a rule to automatically mount it after each reboot:</div></div>
<div class="code">sudo pico /etc/fstab</div>
<div class="col-md-12"> Add this at the end of file:</div>
<div class="code">/dev/vdb1     /data      ext4        defaults      0       0 </div> 



<div class="row">&nbsp;</div>

<div class="row"><div class="col-md-12">6. Check if mounted:</div></div>
<div class="code">mount | grep vdb</div>


