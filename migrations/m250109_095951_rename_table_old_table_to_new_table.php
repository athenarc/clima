<?php

use yii\db\Migration;

/**
 * Class m250109_095951_rename_table_old_table_to_new_table
 */
class m250109_095951_rename_table_old_table_to_new_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameTable('cold_storage_autoaccept', 'storage_autoaccept');
        $this->renameTable('cold_storage_limits', 'storage_limits');
        $this->renameTable('cold_storage_request', 'storage_request');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameTable('storage_autoaccept', 'cold_storage_autoaccept');
        $this->renameTable('storage_limits', 'cold_storage_limits');
        $this->renameTable('storage_request', 'cold_storage_request');
    }


}
