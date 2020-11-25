<?php

namespace app\models;

use Yii;
use Yii\db\Query;

/**
 * This is the model class for table "service_vm_credentials".
 *
 * @property int $request_id
 * @property string $ip
 * @property string $username
 * @property string $password
 */
class ServiceVmCredentials extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'service_vm_credentials';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['request_id'], 'required'],
            [['request_id'], 'default', 'value' => null],
            [['request_id'], 'integer'],
            [['ip', 'password'], 'string', 'max' => 20],
            [['username'], 'string', 'max' => 15],
            [['request_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'request_id' => 'Request ID',
            'ip' => 'VM IP',
            'username' => 'VM username',
            'password' => 'VM password',
        ];
    }

    public static function createEmpty($request_id)
    {
        Yii::$app->db->createCommand()->insert('service_vm_credentials', [
                        'request_id' => $request_id,
                        'ip' => '',
                        'username' => '',
                        'password' => '',
                    ])->execute();
    }

    public function updateInfo($id)
    {
         Yii::$app->db->createCommand()->update('service_vm_credentials',['ip'=>$this->ip, 'username'=>$this->username, 'password'=>$this->password, ], "request_id=$id")->execute();
    }
}
