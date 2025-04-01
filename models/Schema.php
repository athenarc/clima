<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\httpclient\Client;

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

    public static function getMetrics() {
        $client = new Client();

        try {
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl(Yii::$app->params['schema_api_url']."/monitor/application-service/metrics")
                ->addHeaders([
                    'Authorization' => Yii::$app->params['schema_api_token'],
                    'Accept' => 'application/json',
                ])
                ->send();

            if ($response->isOk) {
                $data = $response->getData(); // decoded JSON as array
                // Do something with $data
                return $data;
            } else {
                return [];
            }

        } catch (\Exception $e) {
            return [];
        }
    }
}