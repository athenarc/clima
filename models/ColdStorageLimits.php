<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cold_storage_limits".
 *
 * @property double $storage
 * @property string $user_type
 * @property int $duration
 */
class ColdStorageLimits extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cold_storage_limits';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['storage'], 'number'],
            [['duration'], 'default', 'value' => null],
            [['duration'], 'integer'],
            [['number_of_projects'], 'integer', 'min'=>0],
            [['user_type'], 'string', 'max' => 15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'storage' => 'Storage (in GB)',
            'user_type' => 'User Type',
            'duration' => 'Duration',
            'number_of_projects'=>"Maximum number of projects",
        ];
    }


    public function updateDB($user_type)
    {
        // print_r($user_type);
        // exit(0);
        Yii::$app->db->createCommand()->update('cold_storage_limits',['storage'=>$this->storage, 'number_of_projects'=>$this->number_of_projects], "user_type='$user_type'")->execute();
    }
}
