<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ondemand_autoaccept".
 *
 * @property int $num_of_jobs
 * @property double $time_per_job
 * @property int $cores
 * @property double $ram
 * @property double $storage
 */
class OndemandAutoaccept extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ondemand_autoaccept';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['num_of_jobs', 'cores'], 'default', 'value' => null],
            [['num_of_jobs', 'cores'], 'integer'],
            [['time_per_job', 'ram', 'storage'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'num_of_jobs' => 'Number of jobs',
            'time_per_job' => 'Time per job (in minutes)',
            'cores' => 'Number of CPU cores per job',
            'ram' => 'Memory (RAM) amount (in GBs) per job',
            'storage' => 'Storage (in GBs)',
            'user_type'=>'User category',
        ];
    }



    public function updateDB($user_type)
    {

        Yii::$app->db->createCommand()->update('ondemand_autoaccept',['num_of_jobs'=>$this->num_of_jobs, 'storage'=>$this->storage, 'time_per_job'=>$this->time_per_job, 'ram'=>$this->ram, 'cores'=>$this->cores], "user_type='$user_type'")->execute();

        // print_r($sql);
        // exit(0);

        
    }
}


