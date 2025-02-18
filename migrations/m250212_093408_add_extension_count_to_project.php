<?php

use yii\db\Migration;

/**
 * Class m250206091151_add_extension_count_to_project
 */
class m250212_093408_add_extension_count_to_project extends Migration
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
