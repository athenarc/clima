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
            [['home_page', 'privacy_page','help_page'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'reviewer_num' => 'Number of reviewers',
            'home_page' => 'Home page',
            'privacy_page' => 'Privacy policy page',
            'help_page' => 'Help page',
        ];
    }

    public function updateDB()
    {
        Yii::$app->db->createCommand()->update('configuration',[
            'reviewer_num'=>$this->reviewer_num, 
            'help_page'=>$this->help_page, 
            'home_page'=>$this->home_page,
            'privacy_page'=>$this->privacy_page,  
        ], "TRUE")->execute();
    }
}
