<?php

namespace app\models;

use Yii;
use yii\db\Query;
use webvimark\modules\UserManagement\models\User as Userw;
use app\models\ProjectRequest;
use app\models\Project;
use yii\helpers\Url;
use app\models\Notification;
use app\models\ColdStorageAutoaccept;
use app\models\HotVolumes;
use app\models\User;

/**
 * This is the model class for table "cold_storage_request".
 *
 * @property int $id
 * @property int $request_id
 * @property double $storage
 * @property string $description
 */
class ColdStorageRequest extends \yii\db\ActiveRecord
{
    private $limits;
    private $role;

    public function init()
    {
        parent::init();

        $gold=Userw::hasRole('Gold',$superadminAllowed=false);
        $silver=Userw::hasRole('Silver',$superadminAllowed=false);
        if ($gold)
        {
            $this->role='gold';
        }
        else if ($silver)
        {
            $this->role='silver';
        }
        else
        {
            $this->role='bronze';
        }


        $this->limits=ColdStorageLimits::find()->where(['user_type'=>$this->role])->one();
        

    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cold_storage_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['request_id'], 'default', 'value' => null],
            [['request_id', 'vm_type'], 'integer'],
            [['description', 'type'], 'string'],
            [['storage','description', 'type', 'vm_type'],'required'],
            [['additional_resources'],'string'],
            [['storage'], 'number','max'=>$this->limits->storage,'min'=>0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {

       
        return [
            'id' => 'ID',
            'request_id' => 'Request ID',
            'description' => 'Project description * ',
            'storage'=>"",
        ];
    }



    public function uploadNew($requestId)
    {
        $errors='';
        $success='';
        $warnings='';

        Yii::$app->db->createCommand()->insert('cold_storage_request', [

                'description' => $this->description,
                'storage' => $this->storage,
                'additional_resources'=>$this->additional_resources,
                'request_id' => $requestId,
                'type'=>$this->type,
                'vm_type'=>$this->vm_type,
            ])->execute();



        $query= new Query;
        $query->select(['storage'])
              ->from('cold_storage_autoaccept')
              ->where(['user_type'=>$this->role]);
         
        $row=$query->one();
        
        $role=User::getRoleType();
        $cold_autoaccept= ColdStorageAutoaccept::find()->where(['user_type'=>$role])->one();
        $cold_autoaccept_number=$cold_autoaccept->autoaccept_number;
        $autoaccepted_num=ProjectRequest::find()->where(['status'=>2,'project_type'=>2,'submitted_by'=>Userw::getCurrentUser()['id'],])->andWhere(['>=','end_date', date("Y-m-d")])->count();
        $autoaccept_allowed=($autoaccepted_num-$cold_autoaccept_number < 0) ? true :false;


        if (($this->storage<=$row['storage']) && $autoaccept_allowed)
        {
            $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
            $request->status=2;
            $request->approval_date='NOW()';
            $request->approved_by=0;
            $request->save(false);
            
            $message="Project '$request->name' has been automatically approved.";

            foreach ($request->user_list as $user) 
            {            
                Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'auto-approved']));
            }


            $project=Project::find()->where(['id'=>$request->project_id])->one();
            $project->latest_project_request_id=$request->id;
            $project->pending_request_id=null;
            $project->status=2;
            $project->save();


            $cold_storage_request=ColdStorageRequest::find()->where(['request_id'=>$project->latest_project_request_id])->one();
            $vm_type=$cold_storage_request->vm_type;
            $size=$cold_storage_request->storage;
            $name=$project->name;
            if($cold_storage_request->type=='hot')
            {
                $hotvolume=new HotVolumes;
                $hotvolume->initialize($vm_type);
                $authenticate=$hotvolume->authenticate();
                $token=$authenticate[0];
                $message=$authenticate[1];
                if(!$token=='')
                {
                    $volume_id=$hotvolume->createVolume($size,$name,$token,$vm_type,$project->id);
                }
               
            }

        }
             
        else
        {
            $warnings='Your request will be reviewed.';
        }

            
        $success='Successfully added project request!';
        return [$errors, $success, $warnings];
    

    }

    public function uploadNewEdit($requestId)
    {
        $errors='';
        $success='';
        $warnings='';

        Yii::$app->db->createCommand()->insert('cold_storage_request', [

                'description' => $this->description,
                'storage' => $this->storage,
                'additional_resources'=>$this->additional_resources,
                'request_id' => $requestId,
                'type'=>$this->type,
                'vm_type'=>$this->vm_type,
            ])->execute();



        $query= new Query;
        $query->select(['storage'])
              ->from('cold_storage_autoaccept')
              ->where(['user_type'=>$this->role]);
         
        $row=$query->one();

        // $autoaccepted_num=Project::find()->where(['status'=>2,'project_type'=>2])->count();
        $role=User::getRoleType();
        $cold_autoaccept= ColdStorageAutoaccept::find()->where(['user_type'=>$role])->one();
        $cold_autoaccept_number=$cold_autoaccept->autoaccept_number;
        $autoaccepted_num=ProjectRequest::find()->where(['status'=>2,'project_type'=>2,'submitted_by'=>Userw::getCurrentUser()['id'],])->andWhere(['>=','end_date', date("Y-m-d")])->count();
        $autoaccept_allowed=($autoaccepted_num-$cold_autoaccept_number < 0) ? true :false;
        
        $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
        $project=Project::find()->where(['id'=>$request->project_id])->one();

        

        if (($this->storage<=$row['storage']) && $autoaccept_allowed)
        {
            Yii::$app->db->createCommand()->update('project_request',['status'=>'2'], "id='$requestId'")->execute();


            /*
             * Get project_request from request id in order to get the project_id 
             * in order to update the latest active request 
             */
            // $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
            $request->status=2;
            $request->approval_date='NOW()';
            $request->approved_by=0;
            $request->save();
            
            $message="Updates to project '$request->name' have been automatically approved.";

            foreach ($request->user_list as $user) 
            {            
                Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'auto-approved']));
            }


            // $project=Project::find()->where(['id'=>$request->project_id])->one();

            //set status for old request to -3 (modified)
            $old_request=ProjectRequest::find()->where(['id'=>$project->latest_project_request_id])->one();
            if (!empty($old_request))
            {
                $old_request->status=-3;
                $old_request->save(false);
            }
            
            $project->latest_project_request_id=$request->id;
            $project->pending_request_id=null;
            $project->status=2;
            $project->save();
            // Yii::$app->db->createCommand()->update('project',['latest_project_request_id'=>$request->id, 'pending_request_id'=>null,'status'=>2],"id=$request->project_id")->execute();

            // $cold_storage_request=ColdStorageRequest::find()->where(['request_id'=>$project->latest_project_request_id])->one();
            // $vm_type=$cold_storage_request->vm_type;
            // $size=$cold_storage_request->storage;
            // $name=$project->name;
            // if($cold_storage_request->type=='hot')
            // {
            //     $hotvolume=HotVolumes::find()->where();
            //     $authenticate=$hotvolume->authenticate();
            //     $token=$authenticate[0];
            //     $message=$authenticate[1];
            //     if(!$token=='')
            //     {
            //         $volume_id=$hotvolume->createVolume($size,$name,$token);
            //     }
               
            // }

            // Yii::$app->db->createCommand()->update('hot_volumes', [
            //                 'name' => $name . '-volume',
            //                 'accepted_at'=>'NOW()',
            //                 'project_id' => $project->id,
            //                 'volume_id'=>$volume_id,
            //                 'vm_type'=>$vm_type,
            //                 'active'=>true,
            //             ], "id='$project_id'")->execute();
        }
             
        else
        {
            $warnings='Your request will be reviewed.';
        }

            
        $success="Successfully modified project '$request->name'.";
        return [$errors, $success, $warnings];
    

    }
}
