<?php

use yii\db\Migration;

/**
 * Class m250527_122731_add_related_user_id_to_email
 */
class m250527_122731_add_related_user_id_to_email extends Migration
{

    public function up()
    {
        $this->addColumn('email', 'related_user_id', $this->integer());
    }

    public function down()
    {
        $this->dropColumn('email', 'related_user_id');
    }
}
