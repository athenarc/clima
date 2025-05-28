<?php

use yii\db\Migration;

/**
 * Class m250509_064935_extensions_limits
 */
class m250509_064935_extensions_limits extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%extension_limits}}', [
            'id' => $this->primaryKey(),
            'user_type' => $this->string(50)->notNull(),
            'project_type' => $this->integer()->notNull(),
            'max_percent' => $this->float()->notNull(),
            'max_days' => $this->integer()->notNull()->defaultValue(0),
            'max_extension' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex(
            'idx-extension_limits-user_project_type',
            '{{%extension_limits}}',
            ['user_type', 'project_type'],
            true
        );

        $now = date('Y-m-d H:i:s');

        // Limits per user_type (applied to all project_types for that user_type)
        $userLimits = [
            'bronze' => [0, 0, 0],
            'silver' => [33.33, 0, 1],
            'gold'   => [50, 0, 1],
        ];

        $projectTypes = [0, 1, 2, 3, 4];
        $rows = [];

        foreach ($userLimits as $userType => [$percent, $days, $extensions]) {
            foreach ($projectTypes as $projectType) {
                $rows[] = [$userType, $projectType, $percent, $days, $extensions, $now, $now];
            }
        }

        $this->batchInsert('{{%extension_limits}}',
            ['user_type', 'project_type', 'max_percent', 'max_days', 'max_extension', 'created_at', 'updated_at'],
            $rows
        );
    }



    public function safeDown()
    {
        $this->dropTable('{{%extension_limits}}');
    }

}
