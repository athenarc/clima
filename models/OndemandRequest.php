<?php

namespace app\models;

use Yii;
use yii\db\Query;
use app\models\OndemandLimits;
use webvimark\modules\UserManagement\models\User as Userw;
use app\models\ProjectRequest;
use yii\helpers\Url;
use app\models\Notification;
use app\models\OndemandAutoaccept;
use app\models\User;
use app\models\EmailEventsUser;
use app\models\EmailEventsModerator;
/**
 * This is the model class for table "ondemand_request".
 *
 * @property int $id
 * @property int $request_id
 * @property string $description
 * @property string $maturity
 * @property string $analysis_type
 * @property bool $containerized
 * @property int $num_of_jobs
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
            $this->role='bronze';
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
            [['ram'], 'number','max'=>$this->limits->ram,'min'=>0],
            [['cores'], 'integer','max'=>$this->limits->cores,'min'=>0],
            [['containerized'], 'boolean'],
            [['ram'], 'number'],
            [['analysis_type'], 'string', 'max' => 200],
            [['additional_resources'],'string'],
            [['description','num_of_jobs','cores','ram',
              'analysis_type','maturity','containerized'],'required'],


        ];
    }

   
    




    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {


        $autoaccept=OndemandAutoaccept::find()->where(['user_type'=>$this->role])->one();
        

        $maxram=$this->limits->ram;
        $autoacceptram=$autoaccept->ram;

        $maxcores=$this->limits->cores;
        $autoacceptcores=$autoaccept->cores;
        
        $maxjobs=$this->limits->num_of_jobs;
        $autoacceptjobs=$autoaccept->num_of_jobs;
        

        



        return [
            'id' => 'ID',
            'request_id' => 'Request ID',
            'description' => 'Description *',
            'maturity' => 'Maturity',
            'analysis_type' => 'Type of analysis *',
            'containerized' => 'Project codes are based on containers',                                                
            'num_of_jobs' => "",
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
                'num_of_jobs' => $this->num_of_jobs,
                'ram' => $this->ram,
                'cores' => $this->cores,
                'additional_resources'=>$this->additional_resources,
                'request_id' => $requestId

            ])->execute();


        



        $query= new Query;
        $query->select(['num_of_jobs','ram','cores'])
              ->from('ondemand_autoaccept')
              ->where(['user_type'=>$this->role]);
         
        $row=$query->one();

        // $autoaccepted_num=Project::find()->where(['status'=>2,'project_type'=>0])->count();
        $role=User::getRoleType();
        $ondemand_autoaccept= OndemandAutoaccept::find()->where(['user_type'=>$role])->one();
        $ondemand_autoaccept_number=$ondemand_autoaccept->autoaccept_number;
        $autoaccepted_num=ProjectRequest::find()->where(['status'=>2,'project_type'=>0,'submitted_by'=>Userw::getCurrentUser()['id'],])->andWhere(['>=','end_date', date("Y-m-d")])->count();
        $autoaccept_allowed=($autoaccepted_num-$ondemand_autoaccept_number < 0) ? true :false;

        //get project request and project
        $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
        $project=Project::find()->where(['id'=>$request->project_id])->one();

        

        $message_autoaccept='';
        $message_autoaccept_mod = '';
        if (($this->cores<=$row['cores']) && ($this->ram <=$row['ram']) && ($this->num_of_jobs <=$row['num_of_jobs']) && $autoaccept_allowed)
        {
            // $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
            $request->status=2;
            $request->approval_date='NOW()';
            $request->approved_by=0;
            $request->save(false);
            
            
            $message="Project '$request->name' has been automatically approved.";
            

            foreach ($request->user_list as $user) 
            {
                
            
                // Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'approved']));
                Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'auto-approved']));
            }
            
            $project->latest_project_request_id=$request->id;
            $project->pending_request_id=null;
            $project->status=2;
            $project->name=$request->name;
            $project->save(false);
            $username = User::returnUsernameById($request->submitted_by);

            $message_autoaccept="We are happy to inform you that your project '$project->name' has been automatically approved. <br /> You can access the project resources via the " . Yii::$app->params['name'] . " website"; 
            $message_autoaccept_mod="We would like to inform you that the On-demand computation project '$project->name', submitted by user $username, has been automatically approved.";
            
        }

        else
        {
            $warnings='Your request will be reviewed.';
        }

            

        $success='Successfully added project request!';
    
        return [$errors,$success,$warnings,$message_autoaccept,$project->id, $message_autoaccept_mod];
    }

    public function uploadNewEdit($requestId,$uchanged)
    {
        $errors='';
        $success='';
        $warnings='';
        

        Yii::$app->db->createCommand()->insert('ondemand_request', [

                'description' => $this->description,
                'maturity' => $this->maturity,
                'analysis_type' => $this->analysis_type,
                'containerized' => $this->containerized,
                'num_of_jobs' => $this->num_of_jobs,
                'ram' => $this->ram,
                'cores' => $this->cores,
                'additional_resources'=>$this->additional_resources,
                'request_id' => $requestId
            ])->execute();


        



        $query= new Query;
        $query->select(['num_of_jobs','ram','cores'])
              ->from('ondemand_autoaccept')
              ->where(['user_type'=>$this->role]);
         
        $row=$query->one();

        // $autoaccepted_num=Project::find()->where(['status'=>2,'project_type'=>0])->count();
        $role=User::getRoleType();
        $ondemand_autoaccept= OndemandAutoaccept::find()->where(['user_type'=>$role])->one();
        $ondemand_autoaccept_number=$ondemand_autoaccept->autoaccept_number;
        $autoaccepted_num=ProjectRequest::find()->where(['status'=>2,'project_type'=>0,'submitted_by'=>Userw::getCurrentUser()['id'],])->andWhere(['>=','end_date', date("Y-m-d")])->count();
        $autoaccept_allowed=($autoaccepted_num-$ondemand_autoaccept_number < 0) ? true :false; 

        //get project request and project
        $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
        $project=Project::find()->where(['id'=>$request->project_id])->one();


        if ((($this->cores<=$row['cores']) && ($this->ram <=$row['ram']) && ($this->num_of_jobs <=$row['num_of_jobs']) && $autoaccept_allowed) || $uchanged)
        {
            
            
            
            $message="Updates to project '$request->name' have been automatically approved.";
            

            foreach ($request->user_list as $user) 
            {
                Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'auto-approved']));
                // Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'approved']));
            }


            // $project=Project::find()->where(['id'=>$request->project_id])->one();

            //set status for old request to -3 (modified)
            $old_request=ProjectRequest::find()->where(['id'=>$project->latest_project_request_id])->one();
            

            $request->status=$old_request->status;
            $request->approval_date='NOW()';
            $request->approved_by=$old_request->approved_by;
            $request->save(false);

            if (!empty($old_request))
            {
                $old_request->status=-3;
                $old_request->save(false);
            }
            
            $project->latest_project_request_id=$request->id;
            $project->pending_request_id=null;
            $project->status=$old_request->status;
            $project->name=$request->name;
            $project->save(false);
        }

        else
        {
            $prev = null;
            if (!empty($project->latest_project_request_id)) {
                $prev = ProjectRequest::find()->where(['id' => $project->latest_project_request_id])->one();
            }

            // Safely resolve submitted_by
            $submitted_by = $prev->submitted_by
                ?? $request->submitted_by
                ?? (Yii::$app->user->id ?? null);

            $username   = $submitted_by ? User::returnUsernameById($submitted_by) : '(unknown user)';
            $warnings   = 'Your request will be reviewed.';
            $project_id = $project->id;
            $message    = "The on-demand computation project '{$project->name}', created by user $username, has been modified and is pending approval.";
            EmailEventsModerator::NotifyByEmail('edit_project', $project_id, $message);
        }

            

        $success="Successfully modified project '$request->name'.";
    
        return [$errors,$success,$warnings];
    }

    public function getFormattedDiff($other)
    {
        $diff = $this->getDiff($other);
        return $diff;
    }

    public function getDiff($other) {
        $diff=[];
        $otherAttributes = $other->getAttributes();
        foreach ($otherAttributes as $attributeName => $attributeValue)
        {
            if($this->$attributeName !== $attributeValue) {
                $diff[$attributeName]=[
                    'current'=>$this->$attributeName,
                    'other'=>$attributeValue
                ];
            }
        }
        return $diff;
    }
}
