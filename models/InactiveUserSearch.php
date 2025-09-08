<?php
namespace app\models;

use yii\base\Model;
use yii\data\ArrayDataProvider;

class InactiveUserSearch extends Model
{
    public $username;
    public $email;
    public $name;
    public $surname;
    public $has_active_resources;

    public function rules()
    {
        return [
            [['username', 'email', 'name', 'surname', 'has_active_resources'], 'safe'],
        ];
    }

    public function search($params, $userData, $usersWithActiveResources)
    {
        $this->load($params);

        if (!$this->validate()) {
            return new ArrayDataProvider([
                'allModels' => $userData,
                'pagination' => ['pageSize' => 10],
                'sort' => ['attributes' => ['username', 'email', 'name', 'surname', 'last_login']],
            ]);
        }

        $filtered = array_filter($userData, function ($user) use ($usersWithActiveResources) {
            // username
            if ($this->username && stripos($user['username'], $this->username) === false) {
                return false;
            }

            // email
            if ($this->email && stripos($user['email'], $this->email) === false) {
                return false;
            }

            // name
            if ($this->name && stripos($user['name'], $this->name) === false) {
                return false;
            }

            // surname
            if ($this->surname && stripos($user['surname'], $this->surname) === false) {
                return false;
            }

            // active resources
            if ($this->has_active_resources !== null && $this->has_active_resources !== '') {
                $hasActive = in_array($user['id'], $usersWithActiveResources);
                if ($this->has_active_resources === '1' && !$hasActive) {
                    return false;
                }
                if ($this->has_active_resources === '0' && $hasActive) {
                    return false;
                }
            }

            return true;
        });

        return new ArrayDataProvider([
            'allModels' => array_values($filtered),
            'pagination' => ['pageSize' => 10],
            'sort' => ['attributes' => ['username', 'email', 'name', 'surname', 'last_login']],
        ]);
    }
}


