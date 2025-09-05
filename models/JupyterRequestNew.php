<?php
namespace app\models;

use Yii;
use yii\db\Query;
use app\models\JupyterLimits;
use webvimark\modules\UserManagement\models\User as Userw;
use app\models\ProjectRequest;
use yii\helpers\Url;
use app\models\Notification;
use app\models\JupyterAutoaccept;
use app\models\User;
use app\models\EmailEventsUser;
use app\models\EmailEventsModerator;
/**
 * This is the model class for table "jupyter_request_n".
 *
 * @property int $id
 * @property int $request_id
 * @property string $description
 * @property bool $containerized
 * @property double $ram
 * @property int $cores
 * @property string $image
 * @property int $image_id
 * @property string $participant_view 
 * @property int $participants_number
 */
class JupyterRequestNew extends \yii\db\ActiveRecord
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

        $this->limits=JupyterLimits::find()->where(['user_type'=>$this->role])->one();

        

    }

    public static function tableName()
    {
        return 'jupyter_request_n';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['request_id', 'cores'], 'default', 'value' => null],
            [['request_id', 'cores'], 'integer'],
            [['description', 'image', 'participant_view'], 'string'],
            [['ram'], 'number','max'=>$this->limits->ram,'min'=>0],
            [['cores'], 'integer','max'=>$this->limits->cores,'min'=>0],
            [['containerized'], 'boolean'],
            [['ram'], 'number'],
            [['participants_number'], 'integer','max'=>$this->limits->participants,'min'=>0],
            [['description','cores','ram',
             'image', 'participants_number'],'required'],
            // [['description','num_of_jobs','cores','ram',
            // 'analysis_type','maturity'],'required'],


        ];
    }

   
    




    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {


        $autoaccept=JupyterAutoaccept::find()->where(['user_type'=>$this->role])->one();
        

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
            'containerized' => 'Project codes are based on containers',                                                
            'ram' => "",
            'cores' => "",
            'image' => 'Jupyter server type *',
            'image_id' => 'Jupyter image id',
            'participants_number' => 'Maximum number of users to participate in the project *',
            'participant_view' => 'Description for participants jupyter index page'
        ];
    }





    public function uploadNew($requestId)
    {
        $errors='';
        $success='';
        $warnings='';
        

        Yii::$app->db->createCommand()->insert('jupyter_request_n', [

                'description' => $this->description,
                'containerized' => $this->containerized,
                'ram' => $this->ram,
                'cores' => $this->cores,
                'additional_resources'=>$this->additional_resources,
                'request_id' => $requestId,
                'image' => $this->image,
                'image_id' => $this->image_id,
                'participants_number' => $this->participants_number,
                'participant_view' => $this->participant_view

            ])->execute();


        



        $query= new Query;
        $query->select(['ram','cores'])
              ->from('jupyter_autoaccept')
              ->where(['user_type'=>$this->role]);
         
        $row=$query->one();

        // $autoaccepted_num=Project::find()->where(['status'=>2,'project_type'=>0])->count();
        $role=User::getRoleType();
        $jupyter_autoaccept= JupyterAutoaccept::find()->where(['user_type'=>$role])->one();
        $jupyter_autoaccept_number=$jupyter_autoaccept->autoaccept_number;
        $autoaccepted_num=ProjectRequest::find()->where(['status'=>2,'project_type'=>4,'submitted_by'=>Userw::getCurrentUser()['id'],])->andWhere(['>=','end_date', date("Y-m-d")])->count();
        $autoaccept_allowed=($autoaccepted_num-$jupyter_autoaccept_number < 0) ? true :false;

        //get project request and project
        $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
        $project=Project::find()->where(['id'=>$request->project_id])->one();

        

        $message_autoaccept='';
        $message_autoaccept_mod = '';
        if (($this->cores<=$row['cores']) && ($this->ram <=$row['ram']) && ( $autoaccept_allowed))
        {
            // $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
            $request->status=2;
            $request->approval_date='NOW()';
            $request->approved_by=0;
            $request->save(false);
            
            
            $message="Project '$request->name' has been automatically approved.";
            

            foreach ($request->user_list as $user) 
            {
                
                Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'auto-approved']));
                // Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'approved']));
            }
            
            $project->latest_project_request_id=$request->id;
            $project->pending_request_id=null;
            $project->status=2;
            $project->name=$request->name;
            $project->save(false);
            $username = User::returnUsernameById($request->submitted_by);

            $message_autoaccept="We are happy to inform you that your project '$project->name' has been automatically approved. <br /> You can access the project resources via the " . Yii::$app->params['name'] . " website"; 
            // $message_autoaccept_mod="We would like to inform you that the On-demand computation project '$project->name', submitted by user $username, has been automatically approved.";
            $message_autoaccept_mod="We would like to inform you that the On-demand notebooks project '$project->name', submitted by user $username, has been automatically approved.";

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
        

        Yii::$app->db->createCommand()->insert('jupyter_request_n', [

                'description' => $this->description,
                'containerized' => $this->containerized,
                'ram' => $this->ram,
                'cores' => $this->cores,
                'additional_resources'=>$this->additional_resources,
                'request_id' => $requestId,
                'image' => $this->image,
                'image_id' => $this->image_id,
                'participants_number' => $this->participants_number,
                'participant_view' => $this->participant_view
            ])->execute();


        



        $query= new Query;
        $query->select(['ram','cores'])
              ->from('jupyter_autoaccept')
              ->where(['user_type'=>$this->role]);
         
        $row=$query->one();

        // $autoaccepted_num=Project::find()->where(['status'=>2,'project_type'=>0])->count();
        $role=User::getRoleType();
        $jupyter_autoaccept= JupyterAutoaccept::find()->where(['user_type'=>$role])->one();
        $jupyter_autoaccept_number=$jupyter_autoaccept->autoaccept_number;
        //add a new project type for jupyter notebooks
        $autoaccepted_num=ProjectRequest::find()->where(['status'=>2,'project_type'=>4,'submitted_by'=>Userw::getCurrentUser()['id'],])->andWhere(['>=','end_date', date("Y-m-d")])->count();
        $autoaccept_allowed=($autoaccepted_num-$jupyter_autoaccept_number < 0) ? 0 :1; 


        //get project request and project
        $request = ProjectRequest::find()->where(['id' => $requestId])->one();
        $project = $request ? Project::find()->where(['id' => $request->project_id])->one() : null;

// If we don't have a previous request, skip comparison
        $old_image = null;
        if ($project && $project->latest_project_request_id) {
            $old_jupyter_request = JupyterRequestNew::find()
                ->where(['request_id' => $project->latest_project_request_id])
                ->orderBy(['id' => SORT_DESC])
                ->one();

            // use object access and guard null
            $old_image = $old_jupyter_request ? $old_jupyter_request->image : null;
        }

// if the user changed the image, and we know the previous image, stop active servers
        if ($old_image !== null && $this->image !== $old_image) {
            // Assuming the JupyterServer has a foreign key like project_id (use the correct column name)
            $active_servers = JupyterServer::find()
                ->where(['active' => true, 'project_id' => $project->id])
                ->all();
            foreach ($active_servers as $server) {
                $server->stopServer();
            }
        }


        if (((($this->cores<=$row['cores']) && ($this->ram <=$row['ram']) && ( $autoaccept_allowed)) || $uchanged))
        {
            
            //when autoapproved change the active servers end date

            $all_servers=JupyterServer::find()->where(['active'=>true,'project'=>$request->name])->all();
            if(!empty($all_servers)){
                foreach ($all_servers as $server){
                    $server->expires_on = $request->end_date;
                    $server->save(false);
                }
            }


            
            $message="Updates to project '$request->name' have been automatically approved.";
            

            foreach ($request->user_list as $user) 
            {
                Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'auto-approved']));
                // Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'approved']));
            }


            // $project=Project::find()->where(['id'=>$request->project_id])->one();

            //set status for old request to -3 (modified)
            $old_request = null;
            if (!empty($project->latest_project_request_id)) {
                $old_request = ProjectRequest::find()
                    ->where(['id' => $project->latest_project_request_id])
                    ->one();
            }

            if ($old_request) {
                $request->status        = $old_request->status;
                $request->approval_date = new \yii\db\Expression('NOW()');
                $request->approved_by   = $old_request->approved_by;
                $request->save(false);

                // mark old request as modified
                $old_request->status = -3;
                $old_request->save(false);

                $project->pending_request_id = null;
                $project->status             = $old_request->status;
            } else {
                $request->approval_date = null;
                $request->approved_by   = null;

                $request->save(false);

                $project->pending_request_id = $request->id;
                $project->status = $request->status ?? $project->status;
            }

            $project->name = $request->name;
            $project->save(false);
        }

        else
        {
            $request=ProjectRequest::find()->where(['id'=>$project->latest_project_request_id])->one();
            $submitted_by = $request->submitted_by;
            $username = User::returnUsernameById($submitted_by);
            $warnings=  'Your request will be reviewed.';
            $project_id=$project->id;
            $message="The On-demand notebooks project '$project->name', created by user $username, has been modified and is pending approval.";
            EmailEventsModerator::NotifyByEmail('edit_project', $project_id,$message);
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

    public function GetProjectQuotas($pid) {
        $query=new Query;
        $query->select(['jup.ram','jup.cores','jup.description','jup.request_id', 'jup.image', 'jup.participant_view'])
              ->from('project as p')
              ->innerJoin('jupyter_request_n as jup','jup.request_id=p.latest_project_request_id')
              ->where(['=', 'p.id', $pid]);
        $result = $query->one();
        return $result;
    }
}
