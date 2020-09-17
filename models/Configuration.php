<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "configuration".
 *
 * @property int $reviewer_num
 */
class Configuration extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'configuration';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['reviewer_num'], 'default', 'value' => null],
            [['reviewer_num'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'reviewer_num' => 'Number of reviewers',
        ];
    }

    public function updateDB()
    {
        Yii::$app->db->createCommand()->update('configuration',['reviewer_num'=>$this->reviewer_num], "TRUE")->execute();
    }
}
