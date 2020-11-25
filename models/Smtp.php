<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "smtp".
 *
 * @property int $id
 * @property string $encryption
 * @property string $host
 * @property string $port
 * @property string $username
 * @property string $password
 */
class Smtp extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    

    public static function tableName()
    {
        return 'smtp';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['encryption', 'host', 'port', 'username', 'password'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'encryption' => 'Encryption',
            'host' => 'Host',
            'port' => 'Port',
            'username' => 'Username',
            'password' => 'Password',
        ];
    }
    
    

}


