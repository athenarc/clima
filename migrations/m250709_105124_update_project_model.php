<?php

use yii\db\Migration;

/**
 * Class m250709_105124_update_project_model
 */
class m250709_105124_update_project_model extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Step 1: Add the `end_date` column to the `project` table
        $this->addColumn('{{%project}}', 'project_end_date', $this->date()->null());

        // Step 2: Update the `end_date` for existing projects
        $projects = (new \yii\db\Query())
            ->select(['id', 'latest_project_request_id'])
            ->from('{{%project}}')
            ->all();

        foreach ($projects as $project) {
            $projectEndDate = (new \yii\db\Query())
                ->select(['end_date'])
                ->from('{{%project_request}}')
                ->where(['id' => $project['latest_project_request_id']])
                ->scalar();

            if ($projectEndDate) {
                // Update the `end_date` column for the project
                $this->update('{{%project}}', ['project_end_date' => $projectEndDate], ['id' => $project['id']]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%project}}', 'project_end_date');
    }


}