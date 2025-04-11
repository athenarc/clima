<?php

namespace app\models;

use yii\base\Model;
use yii\data\ArrayDataProvider;

class ActiveProjectSearch extends Model
{
    public $name;
    public $project_type;
    public $owner;
    public $expires_in;

    public function rules()
    {
        return [
            [['name', 'project_type', 'owner', 'expires_in'], 'safe'],
        ];
    }

    public function search($params, $data)
    {
        $this->load($params);

        $filtered = array_filter($data, function ($project) {
            $match = true;

            if ($this->name && stripos($project['name'], $this->name) === false) {
                $match = false;
            }
            if ($this->project_type !== null && $this->project_type !== '' && $project['project_type'] != $this->project_type) {
                $match = false;
            }
            if ($this->owner && stripos($project['owner'], $this->owner) === false) {
                $match = false;
            }

            if ($this->expires_in && stripos($project[1], $this->expires_in) === false) {
                $match = false;
            }

            return $match;
        });

        return new ArrayDataProvider([
            'allModels' => array_values($filtered),
            'pagination' => ['pageSize' => 20],
            'sort' => new \yii\data\Sort([
                'attributes' => ['name',  'owner', 'expires_in'],
                'sortParam' => 'activeSort',
            ]),
        ]);
    }
}
