<?php

use yii\db\Migration;
use app\models\Project;

/**
 * Class m250728_105659_fix_project_end_date_for_old_projects
 */
class m250728_105659_fix_project_end_date_for_old_projects extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $projects = Project::find()
            ->where(['project_end_date' => '2100-01-01'])
            ->all();

        $updatedCount = 0;

        foreach ($projects as $project) {
            if (!empty($project->start_date)) {
                $start = new \DateTime($project->start_date);
                $newEnd = (clone $start)->modify('+2 years');

                $project->project_end_date = $newEnd->format('Y-m-d');
                if ($project->save(false)) {
                    $updatedCount++;
                }
            }
        }

        echo "✔ Updated $updatedCount project(s) with new project_end_date (start_date + 2 years)\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "Cannot revert fix_project_end_date_for_old_projects migration.\n";
        return false;
    }
}
