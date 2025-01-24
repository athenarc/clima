<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "storage_autoaccept".
 *
 * @property double $storage
 */
class StorageAutoaccept extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'storage_autoaccept';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['storage'], 'number'],
            [['autoaccept_number'], 'integer', 'min'=>0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'storage' => 'Storage (in GB)',
            'user_type'=>'User category',
            'autoaccept_number'=>'Number of projects automatically accepted',
        ];
    }


    public function updateDB($user_type)
    {
        Yii::$app->db->createCommand()->update('storage_autoaccept',['storage'=>$this->storage, 'autoaccept_number'=>$this->autoaccept_number], "user_type='$user_type'")->execute();
    }

    


}
