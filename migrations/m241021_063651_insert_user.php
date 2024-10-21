<?php

use yii\db\Migration;

/**
 * Class m241021_063651_insert_user
 */
class m241021_063651_insert_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $username = 'test'; // Set the desired username
        $password = 'test'; // Set the desired plain text password
        $authKey = Yii::$app->security->generateRandomString(); // Generate an auth key
        $passwordHash = Yii::$app->security->generatePasswordHash($password); // Hash the password

        // Insert the user into the user table
        $this->insert('user', [
            'username' => $username,
            'auth_key' => $authKey,
            'password_hash' => $passwordHash,
            'email' => 'user@example.com', // Set an email if needed
            'status' => 1, // Active status
            'created_at' => time(), // Current timestamp
            'updated_at' => time(), // Current timestamp
            'email_confirmed' => 1, // Set as needed
            'superadmin' => 1, // Set superadmin status to 1 for this user
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Delete the user on migration rollback
        $this->delete('user', ['username' => 'superadmin']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241021_063651_insert_user cannot be reverted.\n";

        return false;
    }
    */
}
