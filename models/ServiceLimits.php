<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "service_limits".
 *
 * @property int $vms
 * @property int $cores
 * @property int $ips
 * @property double $ram
 * @property double $storage
 * @property string $user_type
 * @property int $duration
 */
class ServiceLimits extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'service_limits';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vms', 'cores', 'ips', 'duration'], 'default', 'value' => null],
            [['vms', 'cores', 'ips', 'duration'], 'integer'],
            [['ram', 'storage'], 'number'],
            [['user_type'], 'string', 'max' => 15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'vms' => 'Number of VMs',
            'cores' => 'Number of CPU Cores',
            'ips' => 'Number of public IP addresses',
            'ram' => 'Memory (RAM) amount (in GBs)',
            'storage' => 'Storage (in GBs)',
            'user_type' => 'User Type',
            'duration' => 'Duration',
        ];
    }

    public function updateDB($user_type)
    {
        Yii::$app->db->createCommand()->update('service_limits',['vms'=>$this->vms, 'storage'=>$this->storage, 'ips'=>$this->ips, 'ram'=>$this->ram, 'cores'=>$this->cores], "user_type='$user_type'")->execute();
    }
}
