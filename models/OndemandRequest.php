<?php

namespace app\models;

use Yii;
use yii\db\Query;
use app\models\OndemandLimits;
use webvimark\modules\UserManagement\models\User as Userw;
use app\models\ProjectRequest;
use yii\helpers\Url;
use app\models\Notification;
/**
 * This is the model class for table "ondemand_request".
 *
 * @property int $id
 * @property int $request_id
 * @property string $description
 * @property string $maturity
 * @property string $analysis_type
 * @property bool $containerized
 * @property double $storage
 * @property int $num_of_jobs
 * @property double $time_per_job
 * @property double $ram
 * @property int $cores
 */
class OndemandRequest extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */

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

        $this->limits=OndemandLimits::find()->where(['user_type'=>$this->role])->one();
        

    }

    public static function tableName()
    {
        return 'ondemand_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['request_id', 'num_of_jobs', 'cores'], 'default', 'value' => null],
            [['request_id', 'num_of_jobs', 'cores'], 'integer'],
            [['description', 'maturity'], 'string'],
            [['num_of_jobs'], 'integer','max'=>$this->limits->num_of_jobs,'min'=>0],
            [['time_per_job'], 'integer','max'=>$this->limits->time_per_job,'min'=>0],
            [['ram'], 'number','max'=>$this->limits->ram,'min'=>0],
            [['storage'], 'number','max'=>$this->limits->storage,'min'=>0],
            [['cores'], 'integer','max'=>$this->limits->cores,'min'=>0],
            [['containerized'], 'boolean'],
            [['storage', 'time_per_job', 'ram'], 'number'],
            [['analysis_type'], 'string', 'max' => 200],
            [['additional_resources'],'string'],
            [['description','num_of_jobs','time_per_job','cores','ram',
              'analysis_type','maturity','storage','containerized'],'required'],


        ];
    }

   
    




    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {


        $autoaccept=OndemandAutoaccept::find()->where(['user_type'=>$this->role])->one();
        

        $maxstorage=$this->limits->storage;
        $autoacceptstorage=$autoaccept->storage;

        $maxram=$this->limits->ram;
        $autoacceptram=$autoaccept->ram;

        $maxcores=$this->limits->cores;
        $autoacceptcores=$autoaccept->cores;
        
        $maxjobs=$this->limits->num_of_jobs;
        $autoacceptjobs=$autoaccept->num_of_jobs;
        
        

        $maxtime=$this->limits->time_per_job;
        $autoaccepttime=$autoaccept->time_per_job;

        



        return [
            'id' => 'ID',
            'request_id' => 'Request ID',
            'description' => 'Description *',
            'maturity' => 'Maturity',
            'analysis_type' => 'Type of analysis *',
            'containerized' => 'Project codes are based on containers',                                                
            'storage' => "" ,
            'num_of_jobs' => "",
            'time_per_job' => "",
            'ram' => "",
            'cores' => "",
        ];
    }





    public function uploadNew($requestId)
    {
        $errors='';
        $success='';
        $warnings='';
        

        Yii::$app->db->createCommand()->insert('ondemand_request', [

                'description' => $this->description,
                'maturity' => $this->maturity,
                'analysis_type' => $this->analysis_type,
                'containerized' => $this->containerized,
                'storage' => $this->storage,
                'num_of_jobs' => $this->num_of_jobs,
                'time_per_job' => $this->time_per_job,
                'ram' => $this->ram,
                'cores' => $this->cores,
                'additional_resources'=>$this->additional_resources,
                'request_id' => $requestId

            ])->execute();


        



        $query= new Query;
        $query->select(['num_of_jobs','time_per_job','ram','cores', 'storage'])
              ->from('ondemand_autoaccept')
              ->where(['user_type'=>$this->role]);
         
        $row=$query->one();

        $autoaccepted_num=Project::find()->where(['status'=>2,'project_type'=>0])->count();

        //get project request and project
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

        if (($this->cores<=$row['cores']) && ($this->ram <=$row['ram']) && ($this->storage<=$row['storage']) 
            && ($this->num_of_jobs <=$row['num_of_jobs']) && ($this->time_per_job <=$row['time_per_job']) && $autoaccept_allowed)
        {
            // $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
            $request->status=2;
            $request->approval_date='NOW()';
            $request->approved_by=0;
            $request->save(false);
            
            
            $message="Project '$request->name' has been automatically approved.";
            

            foreach ($request->user_list as $user) 
            {
                
            
                Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'approved']));
            }
            // $result=$query->select(['project_id'])
            //           ->from('project_request')
            //           ->where(['id'=>$requestId])
            //           ->one();
            // $projectId=$result['project_id'];


            // $project=Project::find()->where(['id'=>$request->project_id])->one();
            $project->latest_project_request_id=$request->id;
            $project->pending_request_id=null;
            $project->status=2;
            $project->save(false);
        }

        else
        {
            $warnings='Your request will be examined by the RAC.';
        }

            

        $success='Successfully added project request!';
    
        return [$errors,$success,$warnings];
    }

    public function uploadNewEdit($requestId)
    {
        $errors='';
        $success='';
        $warnings='';
        

        Yii::$app->db->createCommand()->insert('ondemand_request', [

                'description' => $this->description,
                'maturity' => $this->maturity,
                'analysis_type' => $this->analysis_type,
                'containerized' => $this->containerized,
                'storage' => $this->storage,
                'num_of_jobs' => $this->num_of_jobs,
                'time_per_job' => $this->time_per_job,
                'ram' => $this->ram,
                'cores' => $this->cores,
                'additional_resources'=>$this->additional_resources,
                'request_id' => $requestId
            ])->execute();


        



        $query= new Query;
        $query->select(['num_of_jobs','time_per_job','ram','cores', 'storage'])
              ->from('ondemand_autoaccept')
              ->where(['user_type'=>$this->role]);
         
        $row=$query->one();

        $autoaccepted_num=Project::find()->where(['status'=>2,'project_type'=>0])->count();

        //get project request and project
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

        if (($this->cores<=$row['cores']) && ($this->ram <=$row['ram']) && ($this->storage<=$row['storage']) 
            && ($this->num_of_jobs <=$row['num_of_jobs']) && ($this->time_per_job <=$row['time_per_job']) && $autoaccept_allowed)
        {
            // $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
            $request->status=2;
            $request->approval_date='NOW()';
            $request->approved_by=0;
            $request->save(false);
            
            
            $message="Updates to project '$request->name' have been automatically approved.";
            

            foreach ($request->user_list as $user) 
            {
                
                Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'approved']));
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
            $project->save(false);
        }

        else
        {
            $warnings='Your request will be examined by the RAC.';
        }

            

        $success="Successfully modified project '$request->name'.";
    
        return [$errors,$success,$warnings];
    }


}
