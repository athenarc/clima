<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "service_autoaccept".
 *
 * @property int $num_of_vms
 * @property int $cores
 * @property int $ips
 * @property double $ram
 * @property double $storage
 */
class ServiceAutoaccept extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'service_autoaccept';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vms', 'cores', 'ips'], 'default', 'value' => null],
            [['vms', 'cores', 'ips'], 'integer'],
            [['ram', 'storage'], 'number'],
            [['autoaccept_number'], 'integer'],
            
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'vms' => 'Number of VMs',
            'cores' => 'Number of CPU cores',
            'ips' => 'Number of public IP addresses',
            'ram' => 'Memory (RAM) amount (in GBs)',
            'storage' => 'Storage (in GBs)',
            'user_type'=>'User category',
            'autoaccept_number'=>'Number of projects automatically accepted',
        ];
    }

    public function updateDB($user_type)
    {
    
        Yii::$app->db->createCommand()->update('service_autoaccept',['vms'=>$this->vms, 'storage'=>$this->storage, 'ips'=>$this->ips, 'ram'=>$this->ram, 'cores'=>$this->cores, 'autoaccept_number'=>$this->autoaccept_number], "user_type='$user_type'")->execute();

    }
}
