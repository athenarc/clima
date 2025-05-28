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
            [['user_type', 'project_type', 'max_percent', 'max_days', 'max_extension'], 'required'],
            [['max_percent'], 'number'],
            [['max_days', 'max_extension', 'project_type'], 'integer'],
            [['user_type'], 'string', 'max' => 50],
            [['user_type', 'project_type'], 'unique', 'targetAttribute' => ['user_type', 'project_type']],
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
            'project_type' => 'ProjectType',
            'max_percent' => 'Maximum Percent',
            'max_extension' => 'Maximum Extension',
            'max_days' => 'Maximum Days',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
