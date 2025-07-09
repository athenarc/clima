<?php

namespace app\models;

use Yii;
use app\components\UserIdentity;
use yii\db\Query;
use webvimark\modules\UserManagement\models\User as Userw;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $confirmation_token
 * @property int $status
 * @property int $superadmin
 * @property int $created_at
 * @property int $updated_at
 * @property string $registration_ip
 * @property string $bind_to_ip
 * @property string $email
 * @property int $email_confirmed
 * @property string $name
 * @property string $surname
 *
 * @property AuthAssignment[] $authAssignments
 * @property AuthItem[] $itemNames
 * @property UserVisitLog[] $userVisitLogs
 */
class User extends UserIdentity
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_BANNED = -1;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'auth_key', 'password_hash', 'created_at', 'updated_at'], 'required'],
            [['status', 'superadmin', 'created_at', 'updated_at', 'email_confirmed'], 'default', 'value' => null],
            [['status', 'superadmin', 'created_at', 'updated_at', 'email_confirmed'], 'integer'],
            [['username', 'password_hash', 'confirmation_token', 'bind_to_ip'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['registration_ip'], 'string', 'max' => 15],
            [['email'], 'string', 'max' => 128],
            [['name', 'surname'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'confirmation_token' => 'Confirmation Api Keys',
            'status' => 'Status',
            'superadmin' => 'Superadmin',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'registration_ip' => 'Registration Ip',
            'bind_to_ip' => 'Bind To Ip',
            'email' => 'Email',
            'email_confirmed' => 'Email Confirmed',
            'name' => 'Name',
            'surname' => 'Surname',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemNames()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'item_name'])->viaTable('auth_assignment', ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserVisitLogs()
    {
        return $this->hasMany(UserVisitLog::className(), ['user_id' => 'id']);
    }

    public static function getNamesAutoComplete($expansion, $max_num = 5, $term)
    {
        $query=new Query;

        $expansion = strtolower($expansion);
        if($expansion == "left")
        {
            $search_type = "%$term";
        }
        else if($expansion == 'right')
        {
            $search_type = "$term%";
        }
        else
        {
            $search_type = $term;
        }
        $rows=$query->select(["levenshtein(username, '$term') as dist","username"])
              ->from('user')
              ->where(['ilike',"username",$search_type, false])
              ->limit($max_num)
              ->orderBy(['dist' => SORT_ASC])
              ->all();
        
        $results=[];
        foreach ($rows as $row)
        {
            $results[]=explode('@',$row['username'])[0];
        }        return json_encode($results);
    }

    public static function returnIdByName($name,$surname)
    {
        $query=new Query;

        $row=$query->select(['id'])
                   ->from('user')
                   ->where(['name'=>$name, 'surname'=>$surname])
                   ->one();

        return $row['id'];
    }

    public static function returnIdByUsername($username)
    {
        $query=new Query;

        $row=$query->select(['id'])
                   ->from('user')
                   ->where(['username'=>$username])
                   ->one();

        return $row['id'];
    }

    public static function createNewUser($username, $persistent_id)
    {
        Yii::$app->db->createCommand()->insert('user', 
        [ 
            'username' => $username,
            'auth_key' => 'dummy',
            'password_hash' => $persistent_id,
            'status' => self::STATUS_ACTIVE,
            'created_at' => time(),
            'updated_at' => time(),
            // 'email_confirmed' => 1,
            // when a new user is created, the email is not confirmed
            'email_confirmed' => 0,
        ])->execute();

        $userId=Yii::$app->db->getLastInsertID();

        Userw::assignRole($userId, 'Bronze');
        

    }

    public static function updateUsername()
    {
        Yii::$app->db->createCommand()->update('user',['username'=>$this->username], "id=$this->id")->execute();
    }

    public static function getAdminIds()
    {
        $results=AuthAssignment::find()->where(['item_name'=> 'Admin'])->all();

        $ids=[];
        foreach ($results as $res)
        {
            $ids[]=$res->user_id;
        }

        return $ids;
    }

    public static function returnUsernameById($id)
    {
        $query=new Query;

        $row=$query->select(['username'])
                   ->from('user')
                   ->where(['id'=>$id])
                   ->one();

        return $row['username'];
    }

    public static function getRoleType()
    {
        $gold=Userw::hasRole('Gold',$superadminAllowed=false);
        $silver=Userw::hasRole('Silver',$superadminAllowed=false);

        if ($gold)
        {
            return 'gold';
        }
        else if ($silver)
        {
            return 'silver';
        }
        else
        {
            return 'bronze';
        }
    }

    public static function getActiveUserStats($username='', $activeFilter='all')
    {
        $query=new Query;

        $query->select(['count(pr.id) as active', 'u.id','u.username', 'u.email','u.policy_accepted' ])
                ->from('user as u')
                ->leftJoin('project_request pr',"u.id = ANY(pr.user_list) AND pr.end_date>=NOW() AND pr.status IN (1,2)")
                ->groupBy('u.id')
                ->orderBy(['active'=>SORT_DESC,'u.username'=>SORT_ASC]);

        if (!empty($username))
        {
            $query->where(['LIKE','u.username',$username]);
        }
        
        if ($activeFilter=='inactive')
        {
            $query->having(['count(pr.id)'=>0]);
        }
        else if ($activeFilter=='active')  
        {
            $query->having('count(pr.id)>0');
        }
        
        $results=$query->all();

        
        return $results;
    }

    public static function getActiveUserNum($username='', $activeFilter='all')
    {
        $query=new Query;

        $query->select(['count(pr.id) as active', 'u.id','u.username', 'u.email'])
                ->from('user as u')
                ->leftJoin('project_request pr',"u.id = ANY(pr.user_list) AND pr.end_date>=NOW() AND pr.status IN (1,2)")
                ->groupBy('u.id')
                ->having(['>', 'count(pr.id)', 0])
                ->orderBy(['active'=>SORT_DESC,'u.username'=>SORT_ASC]);

        $results=$query->count();

        
        return $results;
    }
    // public static function returnRegistrationIP($username)
    // {
    //     $query=new Query;
    //     $row=$query->select(['registration_ip'])
    //                ->from('user')
    //                ->where(['username'=>$username])
    //                ->one();

    //     return $row['registration_ip'];
    // }
    /**
     * Check if the user has accepted the latest policy.
     *
     * @return bool
     */
    public function hasAcceptedPolicy(): bool
    {
        return (bool) $this->policy_accepted;
    }

    /**
     * Mark the policy as accepted.
     *
     * @return bool
     */
    public function acceptPolicy(): bool
    {
        $this->policy_accepted = true;
        return $this->save(false, ['policy_accepted']);
    }

}
