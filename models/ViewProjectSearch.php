<?php

namespace app\models;

use yii\base\Model;
use yii\data\ArrayDataProvider;

class ViewProjectSearch extends Model
{
    public $project_name;
    public $project_type;
    public $owner;
    public $project_end_date;
    public $has_active_resources;

    public function rules()
    {
        return [
            [['project_name', 'project_type', 'owner', 'project_end_date', 'has_active_resources'], 'safe'],
        ];
    }

    public function search($params, $data, $active_resources)
    {
        $this->load($params);

        $filtered = array_filter($data, function ($project) use ($active_resources) {
            $match = true;

            if ($this->project_name && stripos($project['project_name'], $this->project_name) === false) {
                $match = false;
            }

            if ($this->project_type !== null && $this->project_type !== '' && $project['project_type'] != $this->project_type) {
                $match = false;
            }

            if ($this->owner && stripos($project['username'], $this->owner) === false) {
                $match = false;
            }

            if ($this->has_active_resources !== null && $this->has_active_resources !== '') {
                $active = isset($active_resources[$project['project_type']][$project['project_id']]);
                if ($this->has_active_resources == '1' && !$active) {
                    $match = false;
                }
                if ($this->has_active_resources == '0' && $active) {
                    $match = false;
                }
            }

            return $match;
        });

        return new ArrayDataProvider([
            'allModels' => array_values($filtered),
            'pagination' => ['pageSize' => 10],
            'sort' => new \yii\data\Sort([
                'attributes' => ['project_name', 'owner', 'project_end_date'],
                'sortParam' => 'viewProjectSort',
            ]),
        ]);
    }
}
