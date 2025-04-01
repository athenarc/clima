<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "auth_user".
 *
 * @property int $id
 * @property string $password
 * @property string|null $last_login
 * @property bool $is_superuser
 * @property string $username
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property bool $is_staff
 * @property bool $is_active
 * @property string $date_joined
 */
class AuthUser extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['password', 'username', 'first_name', 'last_name', 'email', 'date_joined'], 'required'],
            [['last_login', 'date_joined'], 'safe'],
            [['is_superuser', 'is_staff', 'is_active'], 'boolean'],
            [['password'], 'string', 'max' => 128],
            [['username'], 'string', 'max' => 150],
            [['first_name', 'last_name'], 'string', 'max' => 150],
            [['email'], 'string', 'max' => 254],
            [['username'], 'unique'],
            [['email'], 'email'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'password' => 'Password',
            'last_login' => 'Last Login',
            'is_superuser' => 'Is Superuser',
            'username' => 'Username',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'is_staff' => 'Is Staff',
            'is_active' => 'Is Active',
            'date_joined' => 'Date Joined',
        ];
    }
}
