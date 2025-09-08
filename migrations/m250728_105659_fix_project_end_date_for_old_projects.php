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
            $firstApprovedQuery = ProjectRequest::find()
                ->where(['project_id' => $project->id])
                ->andWhere(['not', ['approval_date' => null]])
                ->andWhere(['not', ['approved_by' => null]])
                ->orderBy(['approval_date' => SORT_ASC])
                ->limit(1);
            $firstApproved = $firstApprovedQuery->one();
            if (!$firstApproved || empty($firstApproved->end_date)) {
                $skipped++;
                continue;
            }
            $sql = $firstApprovedQuery->createCommand()->getRawSql();
            $firstEndDate = new \DateTime($firstApproved->end_date);
            $cutoff = new \DateTime('2100-01-01');
            $firstEndDateStr = $firstEndDate->format('Y-m-d');
            if ($firstEndDate < $cutoff) {
                Yii::$app->db->createCommand()
                    ->update('project', ['project_end_date' => $firstEndDateStr], ['id' =>
                        $project->id])
                    ->execute();
                $updated++;
            } else {
                $submissionDate = new \DateTime($firstApproved->submission_date);
                $newEndDate = (clone $submissionDate)->modify('+24 months');
                $newEndDateStr = $newEndDate->format('Y-m-d');
                Yii::$app->db->createCommand()
                    ->update('project', ['project_end_date' => $newEndDateStr], ['id' =>
                        $project->id])
                    ->execute();
                $updated++;
            }
        }
        echo "Updated $updated projects.\n";
        echo "Skipped $skipped projects (no valid approved request).\n";
    }

    public function safeDown()
    {
        return true;
    }
}
