<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "extension_limits".
 *
 * @property int $id
 * @property string $user_type
 * @property float $max_percent
 * @property int $min_days
 * @property int $max_days
 * @property string $created_at
 * @property string $updated_at
 */
class ExtensionLimits extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%extension_limits}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_type', 'max_percent', 'max_days', 'min_days'], 'required'],
            [['user_type'], 'string', 'max' => 50],
            [['max_percent'], 'number', 'min' => 0, 'max' => 100],
            [['max_extension', 'max_days'], 'integer', 'min' => 0],
            [['user_type'], 'unique'],
            [['created_at', 'updated_at'], 'safe'], // For handling timestamps
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_type' => 'User Type',
            'max_percent' => 'Maximum Percent',
            'max_extension' => 'Maximum Extension',
            'max_days' => 'Maximum Days',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
