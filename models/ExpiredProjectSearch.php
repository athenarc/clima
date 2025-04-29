<?php


namespace app\models;

use yii\base\Model;
use yii\data\ArrayDataProvider;

class ExpiredProjectSearch extends Model
{
    public $name;
    public $project_type;
    public $owner;
    public $expires_in;
    public $has_active_resources;

    public function rules()
    {
        return [
            [['name', 'project_type', 'owner', 'expires_in', 'has_active_resources'], 'safe'],
        ];
    }

    public function search($params, $data, $active_resources)
    {
        $this->load($params);

        $filtered = array_filter($data, function ($project) use ($active_resources) {
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

            if ($this->has_active_resources !== null && $this->has_active_resources !== '') {
                $active = isset($active_resources[$project['project_type']][$project['id']]);
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
            'pagination' => ['pageSize' => 20],
            'sort' => new \yii\data\Sort([
                'attributes' => ['name', 'owner', 'expires_in'],
                'sortParam' => 'expiredSort',
            ]),
        ]);
    }

}
