<?php

use yii\db\Migration;

/**
 * Class m250528_071128_add_email_events
 */
class m250528_071128_add_email_events extends Migration
{
    public function safeUp()
    {
        $this->addColumn('email_events_user', 'expired_resources_notify_1', $this->boolean()->defaultValue(true));
        $this->addColumn('email_events_user', 'expired_resources_notify_15', $this->boolean()->defaultValue(true));
        $this->addColumn('email_events_user', 'expired_resources_notify_30', $this->boolean()->defaultValue(true));
        $this->addColumn('email_events_user', 'expired_resources_notify_over_30', $this->boolean()->defaultValue(true));

        $this->addColumn('email_events_user', 'inactive_user_notify_181', $this->boolean()->defaultValue(true));
        $this->addColumn('email_events_user', 'inactive_user_notify_195', $this->boolean()->defaultValue(true));
        $this->addColumn('email_events_user', 'inactive_user_notify_210', $this->boolean()->defaultValue(true));
        $this->addColumn('email_events_user', 'inactive_user_notify_over_210', $this->boolean()->defaultValue(true));
    }

    public function safeDown()
    {
        $this->dropColumn('email_events_user', 'expired_resources_notify_1');
        $this->dropColumn('email_events_user', 'expired_resources_notify_15');
        $this->dropColumn('email_events_user', 'expired_resources_notify_30');
        $this->dropColumn('email_events_user', 'expired_resources_notify_over_30');

        $this->dropColumn('email_events_user', 'inactive_user_notify_181');
        $this->dropColumn('email_events_user', 'inactive_user_notify_195');
        $this->dropColumn('email_events_user', 'inactive_user_notify_210');
        $this->dropColumn('email_events_user', 'inactive_user_notify_over_210');
    }
}
