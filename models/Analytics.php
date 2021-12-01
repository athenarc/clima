<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "analytics".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $code
 * @property string|null $opt_out_code
 */
class Analytics extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'analytics';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'opt_out_code'], 'string'],
            [['name'], 'string', 'max' => 100],
            [['name','code'],'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'code' => 'Analytics code',
            'opt_out_code' => 'Opt-out Code',
        ];
    }
}
