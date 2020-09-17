<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cold_storage_autoaccept".
 *
 * @property double $storage
 */
class ColdStorageAutoaccept extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cold_storage_autoaccept';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['storage'], 'number'],
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
        ];
    }


    public function updateDB($user_type)
    {
        Yii::$app->db->createCommand()->update('cold_storage_autoaccept',['storage'=>$this->storage], "user_type='$user_type'")->execute();
    }

    


}
