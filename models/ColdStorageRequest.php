<?php

namespace app\models;

use Yii;
use yii\db\Query;
use webvimark\modules\UserManagement\models\User as Userw;
use app\models\ProjectRequest;
use app\models\Project;
use yii\helpers\Url;
use app\models\Notification;

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
            $this->role='temporary';
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
            [['request_id'], 'integer'],
            [['description'], 'string'],
            [['storage','description'],'required'],
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
                'request_id' => $requestId,
            ])->execute();



        $query= new Query;
        $query->select(['storage'])
              ->from('cold_storage_autoaccept')
              ->where(['user_type'=>$this->role]);
         
        $row=$query->one();

        $autoaccepted_num=Project::find()->where(['status'=>2,'project_type'=>2])->count();
        $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
        $project=Project::find()->where(['id'=>$request->project_id])->one();

        if ($autoaccepted_num<1)
        {
            $autoaccept_allowed=true;
        }
        else
        {
            $autoaccept_allowed=false;
        }

        if (($this->storage<=$row['storage']) && $autoaccept_allowed)
        {
            // $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
            $request->status=2;
            $request->approval_date='NOW()';
            $request->approved_by=0;
            $request->save(false);
            // print_r($request);
            // exit(0);
            
            $message="Project '$request->name' has been automatically approved.";

            foreach ($request->user_list as $user) 
            {            
                Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'auto-approved']));
            }


            // $project=Project::find()->where(['id'=>$request->project_id])->one();
            $project->latest_project_request_id=$request->id;
            $project->pending_request_id=null;
            $project->status=2;
            $project->save();
        }
             
        else
        {
            $warnings='Your request will be examined by the RAC.';
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
                'request_id' => $requestId,
            ])->execute();



        $query= new Query;
        $query->select(['storage'])
              ->from('cold_storage_autoaccept')
              ->where(['user_type'=>$this->role]);
         
        $row=$query->one();

        $autoaccepted_num=Project::find()->where(['status'=>2,'project_type'=>2])->count();
        $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
        $project=Project::find()->where(['id'=>$request->project_id])->one();

        if (($project->status==2) || ($autoaccepted_num<1))
        {
            $autoaccept_allowed=true;
        }
        else
        {
            $autoaccept_allowed=false;
        }

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
        }
             
        else
        {
            $warnings='Your request will be examined by the RAC.';
        }

            
        $success="Successfully modified project '$request->name'.";
        return [$errors, $success, $warnings];
    

    }
}
