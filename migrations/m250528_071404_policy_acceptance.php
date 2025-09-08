<?php

use yii\db\Migration;

/**
 * Class m250528_071404_policy_acceptance
 */
class m250528_071404_policy_acceptance extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add the new column, defaulting to false
        $this->addColumn('{{%user}}', 'policy_accepted', $this->boolean()->defaultValue(false)->notNull());

        // Set all existing users to false
        $this->update('{{%user}}', ['policy_accepted' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'policy_accepted');
    }
}
