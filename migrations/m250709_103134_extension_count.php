<?php

use yii\db\Migration;

/**
 * Class m250709_103134_extension_count
 */
class m250709_103134_extension_count extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%project}}', 'extension_count', $this->integer()->notNull()->defaultValue(0));

        $this->update('{{%project}}',['extension_count' => 0]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%project}}','extension_count');
    }

}