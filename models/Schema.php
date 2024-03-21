<?php

namespace app\models;

use Yii;
use yii\base\Model;

class Schema extends Model
{
    public $url;
    public $token;

    public function rules()
    {
        return [
            [['url', 'token'], 'safe']
        ];
    }
}