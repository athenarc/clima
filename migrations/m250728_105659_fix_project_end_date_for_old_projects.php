<?php

use app\models\ProjectRequest;
use yii\db\Migration;
use app\models\Project;

/**
 * Class m250728_105659_fix_project_end_date_for_old_projects
 */
class m250728_105659_fix_project_end_date_for_old_projects extends Migration
{
    public function safeUp()
    {
        $projects = Project::find()->all();
        $updated = 0;
        $skipped = 0;

        foreach ($projects as $project) {
            $firstApproved = ProjectRequest::find()
                ->where(['project_id' => $project->id])
                ->andWhere(['not', ['approval_date' => null]])
                ->andWhere(['not', ['approved_by' => null]])
                ->orderBy(['approval_date' => SORT_ASC])
                ->limit(1)
                ->one();

            if (!$firstApproved || empty($firstApproved->end_date)) {
                $skipped++;
                continue;
            }

            $firstEndDate = new \DateTime($firstApproved->end_date);
            $cutoff = new \DateTime('2100-01-01');

            if ($firstEndDate < $cutoff) {
                $project->project_end_date = $firstEndDate->format('Y-m-d');
                if ($project->save(false)) {
                    $updated++;
                }
            } else {
                $submissionDate = new \DateTime($firstApproved->submission_date);
                $newEndDate = (clone $submissionDate)->modify('+24 months');
                $project->project_end_date = $newEndDate->format('Y-m-d');
                if ($project->save(false)) {
                    $updated++;
                }
            }
        }

        echo "Updated $updated projects.\n";
        echo "Skipped $skipped projects (no valid approved request).\n";
    }

    public function safeDown()
    {
        echo "This migration cannot be reverted.\n";
        return false;
    }
}
