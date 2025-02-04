<?php

use yii\db\Migration;

/**
 * Class m250121073119_update_guide
 */
class m250121_073119_update_guide extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Define the content to be updated
        $content = file_get_contents('migrations/guide.html');

        // Update the content where the title is "Help page"
        $this->update('pages', ['content' => $content], ['title' => 'Help page']);
    }





    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('pages', [
            'content' => '<h1>Original Help Page Content</h1><p>This is the original content of the Help page.</p>'
        ], ['title' => 'Help page']);
    }

}
