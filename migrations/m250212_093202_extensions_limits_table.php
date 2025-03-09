<?php

use yii\db\Migration;

/**
 * Class m250206093350_extensions_limits_table
 */
class m250212_093202_extensions_limits_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%extension_limits}}', [
            'id' => $this->primaryKey(),
            'user_type' => $this->string(50)->notNull()->unique(),
            'max_percent' => $this->float()->notNull(),
            'max_days' => $this->integer()->notNull()->defaultValue(0),
            'max_extension' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
        // Insert initial data
        $this->batchInsert('{{%extension_limits}}',
            ['user_type', 'max_percent', 'max_days', 'max_extension', 'created_at', 'updated_at'],
            [
                ['bronze', 0, 0, 0, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')], // Bronze users: no extensions
                ['silver', 33.33, 0, 1, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')], // Silver users: up to 1/3 or 30 days
                ['gold', 50, 0, 1, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')], // Gold users: up to 1/2 or 60 days
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop the table
        $this->dropTable('{{%extension_limits}}');
    }

    }
