<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ondemand_autoaccept".
 *
 * @property int $num_of_jobs
 * @property int $cores
 * @property double $ram

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
            [['ram',], 'number'],
            [['autoaccept_number'], 'integer', 'min'=>0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'num_of_jobs' => 'Number of jobs',
            'cores' => 'Number of CPU cores per job',
            'ram' => 'Memory (RAM) amount (in GBs) per job',
            'user_type'=>'User category',
            'autoaccept_number'=>'Number of projects automatically accepted',
        ];
    }



    public function updateDB($user_type)
    {

        Yii::$app->db->createCommand()->update('ondemand_autoaccept',['num_of_jobs'=>$this->num_of_jobs,'ram'=>$this->ram, 'cores'=>$this->cores, 'autoaccept_number'=>$this->autoaccept_number], "user_type='$user_type'")->execute();

        // print_r($sql);
        // exit(0);

        
    }
}


