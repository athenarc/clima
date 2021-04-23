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
            [['os_nova_url','os_neutron_url','os_cinder_url','os_keystone_url','os_glance_url'],'url'],
            [['os_tenant_id','os_floating_net_id','os_cred_id','os_cred_secret'],'string']
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
            'os_nova_url' => 'Nova API URL',
            'os_neutron_url'=>'Neutron API URL',
            'os_cinder_url'=>'Cinder API URL',
            'os_cinder_url' => 'Keystone API URL',
            'os_glance_url' => 'Glance API URL',
            'os_tenant_id'=>'Tenant (project) ID',
            'os_floating_net_id'=>'Floating network ID',
            'os_cred_id'=>'Application credential ID',
            'os_cred_secret'=>'Application credential secret'
        ];
    }

    public function updateDB()
    {
        Yii::$app->db->createCommand()->update('configuration',[
            'reviewer_num'=>$this->reviewer_num, 
            'help_page'=>$this->help_page, 
            'home_page'=>$this->home_page,
            'privacy_page'=>$this->privacy_page, 
            'os_nova_url'=>$this->os_nova_url,
            'os_cinder_url'=>$this->os_cinder_url,
            'os_neutron_url'=>$this->os_neutron_url,
            'os_keystone_url'=>$this->os_keystone_url,
            'os_glance_url'=>$this->os_glance_url,
            'os_tenant_id'=>$this->os_tenant_id,
            'os_floating_net_id'=>$this->os_floating_net_id,
            'os_cred_id'=>$this->os_cred_id,
            'os_cred_secret'=>$this->os_cred_secret,
        ], "TRUE")->execute();
    }
}
