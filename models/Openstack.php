<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "openstack".
 *
 * @property string|null $nova_url
 * @property string|null $keystone_url
 * @property string|null $cinder_url
 * @property string|null $neutron_url
 * @property string|null $glance_url
 * @property string|null $tenant_id
 * @property string|null $floating_net_id
 * @property string|null $cred_id
 * @property string|null $cred_secret
 */
class Openstack extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'openstack';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nova_url','neutron_url','cinder_url','keystone_url','glance_url'],'url'],
            [['tenant_id','floating_net_id','cred_id','cred_secret'],'string']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'nova_url' => 'Nova API URL',
            'neutron_url'=>'Neutron API URL',
            'cinder_url'=>'Cinder API URL',
            'keystone_url' => 'Keystone API URL',
            'glance_url' => 'Glance API URL',
            'tenant_id'=>'Tenant (project) ID',
            'floating_net_id'=>'Floating network ID',
            'cred_id'=>'Application credential ID',
            'cred_secret'=>'Application credential secret'
        ];
    }
}
