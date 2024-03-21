<?php

namespace app\models;

use Yii;
use yii\base\Model;

class NewTokenRequestForm extends Model
{
    public $name;
    public $expiration_date;

    public function rules()
    {
        return [
            [['name', 'expiration_date'], 'safe'],
            ['expiration_date', 'validateDates'],
        ];
    }

    public function validateDates($date_e, $given_date, $mode)
    {
        if (!empty($this->expiration_date)){

            if (strtotime($this->expiration_date) < strtotime(date("Y-m-d"))) {
                if ($mode==0){
                    return 'Token creation was unsuccessful: please provide a valid date.';
                } else {
                    return 'Token edit was unsuccessful: please provide a valid date.';
                }
            } elseif ($given_date->format("Y-m-d") > $date_e->format("Y-m-d")) {
                if ($mode==0){
                    return 'Token creation was unsuccessful: please provide a date that does not exceed the expiration date of the project.';
                } else {
                    return 'Token edit was unsuccessful: please provide a date that does not exceed the expiration date of the project.';
                }
            } else {
                return "ok";
            }
        } else {
            return 'empty';
        }

	}

}