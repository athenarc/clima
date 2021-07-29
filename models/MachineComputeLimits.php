<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "machine_compute_limits".
 *
 * @property string $user_type

 */
class MachineComputeLimits extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'machine_compute_limits';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['number_of_projects'], 'integer', 'min'=>-1],
            [['user_type'], 'string', 'max' => 15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
        
            'user_type' => 'User Type',
            'number_of_projects'=>"Maximum number of projects",
           
        ];
    }

    public function updateDB($user_type)
    {
        Yii::$app->db->createCommand()->update('machine_compute_limits',['number_of_projects'=>$this->number_of_projects], "user_type='$user_type'")->execute();
    }
}
