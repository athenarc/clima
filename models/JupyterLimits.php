<?php

namespace app\models;

use Yii;


/**
 * This is the model class for table "jupyter_limits".
 *
 * @property int $num_of_jobs
 * @property int $cores
 * @property double $ram
 * @property double $storage
 * @property string $user_type
 * @property int $duration
 */
class JupyterLimits extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jupyter_limits';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['num_of_jobs', 'cores', 'duration'], 'default', 'value' => null],
            [['num_of_jobs', 'cores', 'duration'], 'integer'],
            [['ram'], 'number'],
            [['number_of_projects', 'participants'], 'integer', 'min'=>0],
            [['user_type'], 'string', 'max' => 15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'num_of_jobs' => 'Number of jobs',
            'cores' => 'Number of CPU cores per server',
            'ram' => 'Memory (RAM) amount (in GBs) per server',
            'user_type'=>'User category',
            'number_of_projects'=>"Maximum number of projects",
            'duration'=>'Project duration in days',
            'participants'=>'Number of participants'
        ];
    }


    public function updateDB($user_type)
    {
        
        Yii::$app->db->createCommand()->update('jupyter_limits',['num_of_jobs'=>$this->num_of_jobs, 'ram'=>$this->ram, 'cores'=>$this->cores, 'number_of_projects'=>$this->number_of_projects, 'participants'=>$this->participants, 'duration'=>$this->duration], "user_type='$user_type'")->execute();
    }
}
