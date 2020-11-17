<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "email".
 *
 * @property int $id
 * @property int $recipient_ids
 * @property string $message
 * @property bool $sent
 * @property string $type
 * @property string $sent_at
 */
class Email extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'email';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['recipient_ids'], 'default', 'value' => null],
            [['recipient_ids'], 'integer'],
            [['message', 'type'], 'string'],
            [['sent'], 'boolean'],
            [['sent_at'], 'safe'],
            [['project_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'recipient_ids' => 'Recipient Ids',
            'message' => 'Message',
            'sent' => 'Sent',
            'type' => 'Type',
            'sent_at' => 'Sent At',
        ];
    }
}
