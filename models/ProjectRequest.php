<?php

namespace app\models;

use Yii;
use app\models\User;
use app\models\Notification;
use webvimark\modules\UserManagement\models\User as Userw;
use yii\db\Query;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\httpclient\Client;
use app\models\Smtp;
use app\models\ColdStorageRequest;
use app\models\HotVolumes;
use app\models\EmailEventsUser;
use app\models\EmailEventsModerator;


/**
 * This is the model class for table "project".
 *
 * @property int $id
 * @property string $name
 * @property int $duration
 * @property int $user_num
 * @property int $user_list
 * @property bool $backup_services
 * @property bool $viewed
 * @property int $status
 * @property int $submitted_by
 * @property string $submission_date
 * @property int $assigned_to
 */
class ProjectRequest extends \yii\db\ActiveRecord
{
    public $usernameList;
    public $errors='';

    const TYPES=[0=>'On-demand computation', 1=>'24/7 Service', 2=>'Cold-Storage', 3=>'Machine Compute'];
    const STATUSES=[-5=>'Expired',-4 =>'Deleted',-3 =>'Invalid due to modification',-2=>'Inactive',-1=>'Rejected',0=>'Pending', 1=>'Approved', 2=>'Auto-approved'];

    /**
     * Project status constants
     */
    const EXPIRED=-5;
    const DELETED=-4;
    const MODIFIED=-3;
    const INACTIVE=-2;
    const REJECTED=-1;
    const PENDING=0;
    const APPROVED=1;
    const AUTOAPPROVED=2;

    /**
     * Project type constants
     */
    const ONDEMAND=0;
    const SERVICE=1;
    const COLD=2;
    const MACHINECOMPUTE=3;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['duration', 'user_num', 'status', 'submitted_by', 'assigned_to'], 'default', 'value' => null],
            [['status', 'submitted_by', 'assigned_to'], 'integer'],
            [['backup_services', 'viewed'], 'boolean'],
            [['submission_date', 'end_date'], 'safe'],
            ['end_date', 'validateDates'],
            [['additional_resources'], 'string'],
            [['name'],'sameOrUnique'],
            [['name'], 'string', 'max' => 30],
            [['name'],'allowed_name_chars'],
            [['name','user_num', 'end_date'],'required'],
            [['user_num'],'integer','min'=>0],
            [['user_list'],'more_users_than_allowed'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validateDates()
    {
    	if(strtotime($this->end_date) < strtotime(date("Y-m-d")))
    	{
    		Yii::$app->session->setFlash('danger', 'Please give correct End date');
        	$this->addError('end_date','Project end date cannot precede today');
    	}
	}

    public function machinesDuration30()
    {
        if(strtotime($this->end_date) > (strtotime(date("Y-m-d")) + 2592000))
        {
            $this->addError('end_date','The maximum duration of the project  is 30 days');
            return false;
        }
        return true;
    }

    public function getFormattedDiff($other, $shallow=false) {
        // Get diff
        $diff = $this->getDiff($other, true);

        if (isset($diff['project']['user_list'])) {
            $currentProjectUserIds = $diff['project']['user_list']['current'];
            $otherProjectUserIds = $diff['project']['user_list']['other'];

            $allRelatedUsers = User::find()->where(['IN', 'id',
                array_merge($currentProjectUserIds, $otherProjectUserIds)
            ])->all();

            // Create a user id-to-username registry for O(1) mapping of usernames
            $allRelatedUsersRegistry = [];
            foreach ($allRelatedUsers as $relatedUser) {
                $allRelatedUsersRegistry[$relatedUser->id] = explode('@', $relatedUser->username)[0];
            }
            $mapIdsToUsernames = function ($id) use ($allRelatedUsersRegistry) {
                return $allRelatedUsersRegistry[$id];
            };
            $diff['project']['user_list']['current'] = array_map($mapIdsToUsernames, $currentProjectUserIds);
            $diff['project']['user_list']['other'] = array_map($mapIdsToUsernames, $otherProjectUserIds);
        }

        if (isset($diff['project']['user_num'])) {
            $differenceMaxUsers = $diff['project']['user_num']['current'] - $diff['project']['user_num']['other'];
            $diff['project']['user_num']['difference'] = $differenceMaxUsers;
        }

        // 2. Timestamps to day or minute resolutions
        if (isset($diff['project']['submission_date']) && isset($diff['project']['approval_date'])) {
            // This is expected always to run since, obviously, different requests have different submission
            // timestamps. Furthermore, since the older transmission is asserted as a previously approved request
            // and the current request is pending, approval timestamps will be different (current's will be null)

            $otherStartDateTs = strtotime($diff['project']['approval_date']['other']);
            $currStartDateTs = strtotime($diff['project']['submission_date']['current']);
            $otherStartDate = date('Y-m-d', $otherStartDateTs);
            $currStartDate = date('Y-m-d', $currStartDateTs);
            if ($otherStartDate == $currStartDate) {
                $otherStartDate .= ' ' . date('H:i:s', $otherStartDateTs);
                $currStartDate .= ' ' . date('H:i:s', $currStartDateTs);
            }

            $diff['project']['submission_date']['current'] = $currStartDate;
            $diff['project']['approval_date']['other'] = $otherStartDate;
        }
        if (isset($diff['project']['end_date'])) {

            $currentEndDateTs = strtotime($diff['project']['end_date']['current']);
            $otherEndDateTs = strtotime($diff['project']['end_date']['other']);

            $diff_in_seconds = $currentEndDateTs - $otherEndDateTs;
            $diff_in_days = $diff_in_seconds / (60 * 60 * 24);

            $diff['project']['end_date']['difference'] = $diff_in_days;
        }

        if (!$shallow) {
            $details = null;
            $otherDetails = null;
            switch ($this->project_type) {
                case 0:
                    $details = OndemandRequest::find()->where(['request_id' => $this->id])->one();
                    $otherDetails = OndemandRequest::find()->where(['request_id' => $other->id])->one();
                    break;
                case 1:
                    $details = ServiceRequest::find()->where(['request_id' => $this->id])->one();
                    $otherDetails = ServiceRequest::find()->where(['request_id' => $other->id])->one();
                    break;
                case 2:
                    $details = ColdStorageRequest::find()->where(['request_id' => $this->id])->one();
                    $otherDetails = ColdStorageRequest::find()->where(['request_id' => $other->id])->one();
                    break;
                case 3:
                    $details = MachineComputeRequest::find()->where(['request_id' => $this->id])->one();
                    $otherDetails = MachineComputeRequest::find()->where(['request_id' => $other->id])->one();
                    break;
            }

            $diff['details'] = $details->getFormattedDiff($otherDetails);
        }

        return $diff;
    }

    public function getDiff($other, $shallow=false){
        $exclude = ['id','user_list'];
        $diff=[
            'project'=>[]
        ];
        $otherRequestAttributes = $other->getAttributes();

        foreach ($otherRequestAttributes as $attributeName => $attributeValue)
        {
            if (in_array($attributeName,$exclude)) continue;
            if($this->$attributeName !== $attributeValue) {
                $diff['project'][$attributeName]=[
                    'current'=>$this->$attributeName,
                    'other'=>$attributeValue
                ];
            }
        }

        $userList=$this->user_list->getValue();
        $otherUserList=$other->user_list->getValue();
        sort($userList);
        sort($otherUserList);
        if ($userList!=$otherUserList) {
            $diff['project']['user_list']=[
                'current'=>$userList,
                'other'=>$otherUserList
            ];
        }

        if (!$shallow) {
            $details = null;
            $otherDetails = null;
            switch ($this->project_type) {
                case 0:
                    $details = OndemandRequest::find()->where(['request_id' => $this->id])->one();
                    $otherDetails = OndemandRequest::find()->where(['request_id' => $other->id])->one();
                    break;
                case 1:
                    $details = ServiceRequest::find()->where(['request_id' => $this->id])->one();
                    $otherDetails = ServiceRequest::find()->where(['request_id' => $other->id])->one();
                    break;
                case 2:
                    $details = ColdStorageRequest::find()->where(['request_id' => $this->id])->one();
                    $otherDetails = ColdStorageRequest::find()->where(['request_id' => $other->id])->one();
                    break;
                case 3:
                    $details = MachineComputeRequest::find()->where(['request_id' => $this->id])->one();
                    $otherDetails = MachineComputeRequest::find()->where(['request_id' => $other->id])->one();
                    break;
            }
            if ($details && $otherDetails) $diff['details'] = $details->getDiff($otherDetails);
        }

        return $diff;
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name *',
            'duration' => 'Duration (in months) *',
            'user_num' => 'Maximum number of users to participate in the project *',
            'user_list' => 'Participating users *',
            'backup_services' => 'A backup service is required (if yes, check box) ',
            'viewed' => 'Viewed',
            'status' => 'Status',
            'submitted_by' => 'Submitted By',
            'submission_date' => 'Submission Date',
            'end_date'=>'Ending Date',
            'assigned_to' => 'Assigned To',
        ];
    }

    public function uploadNew($project_type)
    {
        $errors='';
        $success='';
        $warnings='';
        $request_id=-1;
        $message='';
        $project_id='';

        

        if (empty($this->user_list))
        {
            $errors="Error: You must specify at least one user participating in the project.";
        }
        
        
        if (empty($errors))
        {            
            $submitted_by=User::findByUsername(Userw::getCurrentUser()['username'])->id;

            Yii::$app->db->createCommand()->insert('project', ['name' => $this->name,'project_type'=> $project_type])->execute();
            $project_id=$id = Yii::$app->db->getLastInsertID();

            Yii::$app->db->createCommand()->insert('project_request', [
            
                        'name' => $this->name,
                        // 'duration' => $this->duration,
                        'end_date'=>$this->end_date,
                        'user_num' => $this->user_num,
                        'user_list' => $this->user_list,
                        'backup_services' => ($this->backup_services=='1') ? true : false,
                        'submission_date' => 'NOW()',
                        'project_type' => $project_type,
                        'submitted_by' => $submitted_by,
                        'project_id' => $project_id,
                    ])->execute();
            
            
          
            $success='';
            $request_id=$id = Yii::$app->db->getLastInsertID();

            Yii::$app->db->createCommand()->update('project',['pending_request_id'=>$request_id], "id='$project_id'")->execute();

            $message="A new project named '$this->name' has been submitted and is pending moderator approval.";
           
        }

        return [$errors,$success,$warnings,$request_id, $message, $project_id];
    }

    public function uploadNewEdit($project_type,$uchanged,$modify_req_id='')
    {
        $errors='';
        $success='';
        $warnings='';
        $request_id=-1;

        if (empty($this->user_list))
        {
            $errors="Error: You must specify at least one user participating in the project.";
        }
        
        if (empty($errors))
        {
           
            
            //if user is Admin, keep the owner of the project the same as in the old request
            if (!empty($modify_req_id))
            {
                $old_request=ProjectRequest::find()->where(['id'=>$modify_req_id])->one();
            }

            
            
            if ( (Userw::hasRole('Admin',$superadminAllowed=true)) || (Userw::hasRole('Moderator',$superadminAllowed=true)) )
            {
                $submitted_by=$old_request->submitted_by;
            }
            else
            {
                $submitted_by=User::findByUsername(Userw::getCurrentUser()['username'])->id;
            }
            

            Yii::$app->db->createCommand()->insert('project_request', [
            
                        'name' => $this->name,
                        // 'duration' => $this->duration,
                        'end_date'=>$this->end_date,
                        'user_num' => $this->user_num,
                        'user_list' => $this->user_list,
                        'backup_services' => ($this->backup_services=='1') ? true : false,
                        'submission_date' => 'NOW()',
                        'project_type' => $project_type,
                        'submitted_by' => $submitted_by,
                        //project_id is preloaded since the model was updated by the form
                        'project_id' => $this->project_id,
                    ])->execute();
            
           
          
            $success='';
            $request_id=Yii::$app->db->getLastInsertID();

           
            //invalidate old request if it is a modification

            $project=Project::find()->where(['id'=>$this->project_id])->one();

            if(!empty($project->pending_request_id))
            {
                $pending=ProjectRequest::find()->where(['id'=>$project->pending_request_id])->one();
                $pending->status=-3;
                $pending->save();
            }

            $project->pending_request_id=$request_id;
            $project->save();
            if (!$uchanged)
            {
                $message="Project '$this->name' has been modified and is pending approval.";
            }
            else
            {
                $message="Project '$this->name' has been modified.";   
            }
            
        }

        
        

        return [$errors,$success,$warnings,$request_id];
    }

    public function reactivate()
    {        
        $newRequest=clone $this;
        $newRequest->isNewRecord = true;
        unset($newRequest->id);
        $newRequest->end_date=new \DateTime('NOW');
        $newRequest->end_date=$newRequest->end_date->modify('+2 days')->format('m-d-Y H:i:s');
        $newRequest->submission_date="NOW()";
        $newRequest->approval_date='NOW()';

        if (!$newRequest->save(false))
        {
            $this->errors='Failed to write to database';
        }
        
        //invalidate old request if it is a modification

        $project=Project::find()->where(['id'=>$this->project_id])->one();

        if(!empty($project->pending_request_id))
        {
            $pending=ProjectRequest::find()->where(['id'=>$project->pending_request_id])->one();
            $pending->status=-3;
            $pending->save();
        }

        $project->pending_request_id='';
        $project->status=2;
        $project->latest_project_request_id=$newRequest->id;
        $project->save();

        if ($this->project_type==0)
        {
            $details=OndemandRequest::find()->where(['request_id'=>$this->id])->one();
        }
        else if ($this->project_type==1)
        {
            $details=ServiceRequest::find()->where(['request_id'=>$this->id])->one();
        }
        else if ($this->project_type==2)
        {
            $details=ColdStorageRequest::find()->where(['request_id'=>$this->id])->one();
        }
        else if ($this->project_type==3)
        {
            $details=MachineComputeRequest::find()->where(['request_id'=>$this->id])->one();
            
        }

        $newDetails=clone $details;
        $newDetails->isNewRecord = true;
        unset($newDetails->id);
        $newDetails->request_id=$newRequest->id;
        $newDetails->save(false);
        
        return;
    }

    public static function getRequestList($filter)
    {
        $query=new Query;
        

        $filters=['rejected'=>-1,'pending'=>0, 'approved'=>1, 'auto-approved'=>2];

        $query->select(['pr.id','pr.name','username',
                        'pr.duration','pr.submission_date','pr.status','pr.viewed', 'pr.project_type'])
              ->from('project_request as pr')
              ->innerJoin('user','pr.submitted_by="user".id');


        if (isset($filters[$filter]))
        {
            $query->where(['pr.status'=>$filters[$filter]]);
        }

       

        $pages = new Pagination(['totalCount' => $query->count()]);
        $pages->setPageSize(10);
        
        $results = $query->orderBy('pr.submission_date DESC')->offset($pages->offset)->limit($pages->limit)->all();
        
        return [$pages,$results];
    }

    public static function getUserRequestList($filter)
    {
        $query=new Query;

        $filters=['rejected'=>-1,'pending'=>0, 'approved'=>1, 'auto-approved'=>2];

        $user=Userw::getCurrentUser()['username'];

        $query->select(['pr.id','pr.name',"username",
                        'pr.duration','pr.submission_date','pr.status','pr.viewed', 'pr.project_type'])
              ->from('project_request as pr')
              ->innerJoin('user','pr.submitted_by="user".id')
              ->where(['user.username'=>$user]);

        if (isset($filters[$filter]))
        {
            $query->andwhere(['pr.status'=>$filters[$filter]]);
        }

        $pages = new Pagination(['totalCount' => $query->count()]);
        $pages->setPageSize(20);
        
        $results = $query->orderBy('pr.submission_date DESC')->offset($pages->offset)->limit($pages->limit)->all();
        
        return [$pages,$results];
    }

    public function recordViewed($id)
    {
        Yii::$app->db->createCommand()->update('project_request',['viewed'=>'0'], "id='$id'")->execute();
    }

    public function approve()
    {
        
        $this->status=1;
        $this->approval_date='NOW()';
        $this->approved_by=Userw::getCurrentUser()['id'];
        $this->save(false);

        $project=Project::find()->where(['id'=>$this->project_id])->one();
        //set status for old request to -3 (modified)
        
        if (!empty($project->latest_project_request_id))
        {
            $old_request=ProjectRequest::find()->where(['id'=>$project->latest_project_request_id])->one();
            $old_request->status=-3;
            $old_request->save(false);
        }
        

        $project->latest_project_request_id=$this->id;
        $project->pending_request_id=null;
        $project->status=1;
        $project->name=$this->name;
        $project->save(false);

        if ($this->project_type==2)
        {

            $cold_storage_request=ColdStorageRequest::find()->where(['request_id'=>$this->id])->one();
            $vm_type=$cold_storage_request->vm_type;
            $size=$cold_storage_request->storage;
            $name=$this->name;

        }

        $message="We are happy to inform you that project '$this->name' has been approved. <br /> You can access the project resources via the " . Yii::$app->params['name'] . " website";  

        foreach ($this->user_list as $user) 
        {
            Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'approved']));
        }

        EmailEventsModerator::NotifyByEmail('project_decision', $this->project_id,$message);
        EmailEventsUser::NotifyByEmail('project_decision', $this->project_id,$message);
             

    }
    
    public function reject()
    {
        $this->status=-1;
        $this->approval_date='NOW()';
        $this->approved_by=Userw::getCurrentUser()['id'];
        $this->save(false);

        $project=Project::find()->where(['id'=>$this->project_id])->one();
        if(empty($project->latest_project_request_id))
        {
            $project->latest_project_request_id=$this->id;
            $project->pending_request_id=null;
            $project->status=-1;
            
        }
        else
        {
            $project->pending_request_id=null;
        }

        $project->save(false);
        

        $message="We are sorry to inform you that your request for project '$this->name' has been rejected by the Resource Allocation Committee.";
        
        foreach ($this->user_list as $user) 
        {
                
            Notification::notify($user,$message,-1,Url::to(['project/user-request-list','filter'=>'rejected']));
        }
        EmailEventsUser::NotifyByEmail('project_decision', $this->project_id,$message);
        EmailEventsModerator::NotifyByEmail('project_decision', $this->project_id,$message);


    }

    public static function invalidateExpiredProjects()
    {
        $query=new Query;

        $query->select(['r.id as rid',
                        // "( ((DATE_PART('year',NOW())-DATE_PART('year',r.approval_date)) * 12) + (DATE_PART('month',NOW())-DATE_PART('month',r.approval_date))) as datediff",
                        'project_id as pid',
                    ])
              ->from('project as p')
              ->join('INNER JOIN', 'project_request as r', 'p.latest_project_request_id = r.id')
              ->where("( ((DATE_PART('year',NOW())-DATE_PART('year',r.approval_date)) * 12) + (DATE_PART('month',NOW())-DATE_PART('month',r.approval_date))) > r.duration")
              ->andWhere(['p.status'=>[ProjectRequest::APPROVED, ProjectRequest::AUTOAPPROVED]]);
        // print_r($query->createCommand()->getRawSql());
        // exit(0);
        $rows=$query->all();

        foreach ($rows as $row)
        {
            $pid=$row['pid'];
            $rid=$row['rid'];
            // print_r($pid);
            // exit(0);
            Yii::$app->db->createCommand()->update('project',['status'=>ProjectRequest::EXPIRED], "id=$pid")->execute();
            Yii::$app->db->createCommand()->update('project_request',['status'=>ProjectRequest::EXPIRED], "id=$rid")->execute();
        }

        
    }

    public function cancel()
    {
        $this->status=-4;
        $this->save(false);

        $project=Project::find()->where(['id'=>$this->project_id])->one();
        if (empty($project->latest_project_request_id))
        {
            $project->status=-4;
        }
        else
        {
            $project->pending_request_id=null;
        }

        $project->save();


    }

    public function cancelActiveProject()
    {
        $this->status=self::DELETED;
        $this->deletion_date='NOW()';
        $this->save(false);

        $project=Project::find()->where(['id'=>$this->project_id])->one();
        $project->status=-4;
        $project->save();


    }

    public function getPreviouslyApprovedProjectRequest() {
        $project = Project::find()->where(['pending_request_id' => $this->id])->one();
        return ProjectRequest::find()->where(['id' => $project->latest_project_request_id])->one();
    }

    public static function getVmList($filter, $user='', $project='', $ip='')
    {
        $query=new Query;
        // print_r($filter);
        // exit(0);

        
        $query->select(['pr.id as request_id','pr.project_id as project_id', 'pr.name as project_name', 'pr.end_date',
                        'u1.username as created_by', 'u2.username as deleted_by', 'pr.project_type',
                        'v.created_at', 'v.deleted_at', 'v.active', 'v.id as vm_id', 'v.ip_address'])
              ->from('project_request as pr')
              ->join('LEFT JOIN','vm as v', 'pr.id=v.request_id')
              ->join('INNER JOIN', '"user" as u1', 'u1.id=v.created_by')
              //->join('INNER JOIN', 'project as p', 'p.latest_project_request_id=pr.id')
              ->join('LEFT JOIN', '"user" as u2', 'u2.id=v.deleted_by')
              ->where(['pr.project_type'=>1]);
        if ($filter=='active')
        {
            $query->andWhere(['v.active'=>true]);
        }
        else if ($filter=='deleted')
        {
            $query->andWhere(['v.active'=>false]);
        }

        if (!empty($user))
        {
            $query->andWhere("u1.username like '%$user%'");
        }
        if (!empty($project))
        {
            $query->andWhere("pr.name like '%$project%'");
        }
        if (!empty($ip))
        {
            $query->andWhere("v.ip_address like '%$ip%'");
        }
        
              
        // print_r($query->createCommand()->getRawSql());
        // exit(0);
       

        $pages = new Pagination(['totalCount' => $query->count()]);
        $pages->setPageSize(10);
        
        $results = $query->orderBy('v.created_at DESC')->offset($pages->offset)->limit($pages->limit)->all();
        
        return [$pages,$results];
    }

    public static function getVmCount($filter)
    {
        $query=new Query;
        
        $query->select(['pr.id as request_id','pr.project_id as project_id', 'pr.name as project_name', 'pr.end_date',
                        'u1.username as created_by', 'u2.username as deleted_by', 'pr.project_type',
                        'v.created_at', 'v.deleted_at', 'v.active', 'v.id as vm_id'])
              ->from('project_request as pr')
              ->join('LEFT JOIN','vm as v', 'pr.id=v.request_id')
              ->join('INNER JOIN', '"user" as u1', 'u1.id=v.created_by')
              ->join('LEFT JOIN', '"user" as u2', 'u2.id=v.deleted_by')
              ->where(['pr.project_type'=>1]);
        if ($filter=='active')
        {
            $query->andWhere(['v.active'=>true]);
        }
        else if ($filter=='deleted')
        {
            $query->andWhere(['v.active'=>false]);
        }

        $results = $query->count();

        return $results;
    }



    public static function getVmMachinesList($filter, $user='', $project='', $ip='')
    {
        $query=new Query;
        // print_r($filter);
        // exit(0);

        
        $query->select(['pr.id as request_id','pr.project_id as project_id', 'pr.name as project_name', 'pr.end_date',
                        'u1.username as created_by', 'u2.username as deleted_by', 'pr.project_type',
                        'v.created_at', 'v.deleted_at', 'v.active', 'v.id as vm_id', 'v.ip_address'])
              ->from('project_request as pr')
              ->join('LEFT JOIN','vm_machines as v', 'pr.id=v.request_id')
              ->join('INNER JOIN', '"user" as u1', 'u1.id=v.created_by')
              //->join('INNER JOIN', 'project as p', 'p.latest_project_request_id=pr.id')
              ->join('LEFT JOIN', '"user" as u2', 'u2.id=v.deleted_by')
              ->where(['pr.project_type'=>3]);
        if ($filter=='active')
        {
            $query->andWhere(['v.active'=>true]);
        }
        else if ($filter=='deleted')
        {
            $query->andWhere(['v.active'=>false]);
        }
        if (!empty($user))
        {
            $query->andWhere("u1.username like '%$user%'");
        }
        if (!empty($project))
        {
            $query->andWhere("pr.name like '%$project%'");
        }
        if (!empty($ip))
        {
            $query->andWhere("v.ip_address like '%$ip%'");
        }
        
              
        // print_r($query->createCommand()->getRawSql());
        // exit(0);
       

        $pages = new Pagination(['totalCount' => $query->count()]);
        $pages->setPageSize(10);
        
        $results = $query->orderBy('v.created_at DESC')->offset($pages->offset)->limit($pages->limit)->all();
        
        return [$pages,$results];
    }

    public static function getVmMachinesCount($filter)
    {
        $query=new Query;

        $query->select(['pr.id as request_id','pr.project_id as project_id', 'pr.name as project_name', 'pr.end_date',
                        'u1.username as created_by', 'u2.username as deleted_by', 'pr.project_type',
                        'v.created_at', 'v.deleted_at', 'v.active', 'v.id as vm_id'])
              ->from('project_request as pr')
              ->join('LEFT JOIN','vm_machines as v', 'pr.id=v.request_id')
              ->join('INNER JOIN', '"user" as u1', 'u1.id=v.created_by')
              ->join('LEFT JOIN', '"user" as u2', 'u2.id=v.deleted_by')
              ->where(['pr.project_type'=>3]);
        if ($filter=='active')
        {
            $query->andWhere(['v.active'=>true]);
        }
        else if ($filter=='deleted')
        {
            $query->andWhere(['v.active'=>false]);
        }
        
        $results = $query->count();
        
        return $results;
    }

    public function fillUsernameList()
    {
        $this->usernameList=[];

        foreach ($this->user_list as $id)
        {
            $username=User::returnUsernameById($id);
            $username=explode('@',$username)[0];
            $this->usernameList[]=$username;
        }
    }

    public function allowed_name_chars($attribute, $params, $validator)
    {
        if(preg_match('/[^a-z_\-0-9]/', $this->$attribute))
        {
                $this->addError($attribute, "Name can only contain lowercase characters, numbers, hyphens( - ) and underscores( _ ).");
                return false;
        }
        return true;
    }

    public function sameOrUnique($attribute, $params, $validator)
    {
        if (!empty($this->id))
        {
             $old=ProjectRequest::find()->where(['id'=>$this->id])->one();
             if ($old->name!=$this->name)
             {
                $existing=ProjectRequest::find()->where(['name'=>$this->name])->one();
                if (!empty($existing))
                {
                    $this->addError($attribute, "Name " . $this->name . " already exists.");
                    return false;
                }
             }
        }
        else
        {
            $existing=ProjectRequest::find()->where(['name'=>$this->name])->one();
            if (!empty($existing))
            {
                $this->addError($attribute, "Name " . $this->name . " already exists.");
                return false;
            }
        }
        return true;
    }

    public function more_users_than_allowed($attribute, $params, $validator)
    {
        
        if ($this->user_num<count($this->user_list))
        {
            $this->addError($attribute, "You cannot add more users that the number specified.");
        }
        return true;
    }

    public static function projectModelChanged($model1, $model2)
    {
        $attr1=$model1->getAttributes();
        $attr2=$model2->getAttributes();

        $changed=false;
        $listChanged=false;

        /*
         * Check if any of the attributes except user_list where changed
         */
        foreach ($attr1 as $name => $value)
        {
            if ($name=='user_list')
            {
                continue;
            }
            if ($name=='user_num')
            {
                continue;
            }
            if ($value!=$attr2[$name])
            {
                $changed=true;
            }
        }

        /*
         * Check if the user list was changed
         */
        $ulist1=$attr1['user_list']->getValue();
        $ulist2=$attr2['user_list']->getValue();

        sort($ulist1);
        sort($ulist2);
        
        if ($ulist1!=$ulist2)
        {
            $listChanged=true;
        }
        /*
         * Check if only the user list was changed and return appropriate values
         */
        if ($changed)
        {
            return [true,false];
        }
        else
        {
            if ($listChanged)
            {
                return [true,true];
            }
            else
            {
                return [false,false];
            }
        }
    }

    public static function modelChanged($model1, $model2)
    {
        $attr1=$model1->getAttributes();
        $attr2=$model2->getAttributes();


        foreach ($attr1 as $name => $value)
        {
        
            if ($value!=$attr2[$name])
            {
                // exit(0);
                return true;
            }
        }
        return false;
    }

    public static function getProjectSchemaUsage($project)
    {
        $data=['project'=>$project];

        $client = new Client();
        $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl(Yii::$app->params['schema_url'] . "/index.php?r=api/project-usage")
                ->setData($data)
                ->send();
        
        $usage=($response->data==false) ? [
                                            'count' => 0, 
                                            'total_time' => '00:00:00', 
                                            'avg_time' => '00:00:00', 
                                            'ram' => 0, 
                                            'cpu' => 0, 
                                            'active_jupyter' => 0,
                                            'total_jupyter' => 0
                                          ] : $response->data;
        return $usage;
    }

    public static function getSchemaPeriodUsage()
    {

        $client = new Client();
        $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl(Yii::$app->params['schema_url'] . "/index.php?r=api/period-statistics")
                ->send();
        // if (!$response->getIsOk())
        // {
        //     return false;
        // }
        $usage=($response->data==false) ? [
                                            'total_time'=>'0',
                                            'total_jobs'=>'0'
                                          ] : $response->data;
        return $usage;
    }

    public static function getEgciPeriodUsage()
    {
        $query=new Query;

        $active_services=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('service_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['>','pr.end_date','NOW'])
                        ->count();
        $query=new Query;

        $total_services=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('service_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->count();
        $query=new Query;

        $active_machines=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('machine_compute_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['>','pr.end_date','NOW'])
                        ->count();
        $query=new Query;

        $total_machines=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('machine_compute_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->count();
        $query=new Query;

        $active_ondemand=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('ondemand_request as o','o.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['>','pr.end_date','NOW'])
                        ->count();

        $query=new Query;

        $total_ondemand=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('ondemand_request as o','o.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->count();

        $query=new Query;

        $vms_services_active=$query->select(['p.id'])
                        ->from('project as p')
                        ->innerJoin('vm as v','v.project_id=p.id')
                        ->where(['IN','p.status',[1,2]])
                        ->andWhere(['v.active'=>true])
                        // ->andWhere(['>','pr.end_date','NOW'])
                        ->count();
        $query=new Query;

        $vms_services_total=$query->select(['p.id'])
                        ->from('project as p')
                        ->innerJoin('vm as v','v.project_id=p.id')
                        ->where(['IN','p.status',[1,2]])
                        // ->andWhere(['v.active'=>true])
                        ->count();
        $query=new Query;

        $vms_machines_active=$query->select(['p.id'])
                        ->from('project as p')
                        ->innerJoin('vm_machines as v','v.project_id=p.id')
                        ->where(['IN','p.status',[1,2]])
                        ->andWhere(['v.active'=>true])
                        // ->andWhere(['>','pr.end_date','NOW'])
                        ->count();

        $query=new Query;

        $vms_machines_total=$query->select(['pr.id'])
                        ->from('project as p')
                        ->innerJoin('vm_machines as v','v.project_id=p.id')
                        ->where(['IN','p.status',[1,2]])
                        // ->andWhere(['v.active'=>true])
                        ->count();
        $query=new Query;

        $vm_active_services_stats=$query->select(['sum(s.num_of_cores) as cores', 'sum(s.ram) as ram', 'sum(s.storage) as storage'])
                        ->from('project_request as pr')
                        ->innerJoin('vm as v','v.project_id=pr.project_id')
                        ->innerJoin('service_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['v.active'=>true])
                        // ->andWhere(['>','pr.end_date','NOW'])
                        ->one();
        
        $query=new Query;

        $vm_active_machines_stats=$query->select(['sum(s.num_of_cores) as cores', 'sum(s.ram) as ram', 'sum(s.storage) as storage'])
                        ->from('project_request as pr')
                        ->innerJoin('vm_machines as v','v.project_id=pr.project_id')
                        ->innerJoin('machine_compute_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['v.active'=>true])
                        // ->andWhere(['>','pr.end_date','NOW'])
                        ->one();

        $query=new Query;             
        $vm_total_services_stats=$query->select(['sum(s.num_of_cores) as cores', 'sum(s.ram) as ram', 'sum(s.storage) as storage'])
                        ->from('project_request as pr')
                        ->innerJoin('vm as v','v.request_id=pr.id')
                        ->innerJoin('service_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->one();
        
        $query=new Query;

        $vm_total_machines_stats=$query->select(['sum(s.num_of_cores) as cores', 'sum(s.ram) as ram', 'sum(s.storage) as storage'])
                        ->from('project_request as pr')
                        ->innerJoin('vm_machines as v','v.project_id=pr.project_id')
                        ->innerJoin('machine_compute_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->one();
        $query=new Query;

        $volumes_service=$query->select(['count(v.id) as number','sum(c.storage) as total'])
                        ->from('cold_storage_request as c')
                        ->innerJoin('project_request as pr','pr.id=c.request_id')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->innerJoin('hot_volumes as v','v.project_id=p.id')
                        ->where(['v.vm_type'=>1,'v.active'=>true])
                        ->one();
        $query=new Query;
        $volumes_machines=$query->select(['count(v.id) as number','sum(c.storage) as total'])
                        ->from('cold_storage_request as c')
                        ->innerJoin('project_request as pr','pr.id=c.request_id')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->innerJoin('hot_volumes as v','v.project_id=p.id')
                        ->where(['v.vm_type'=>2,'v.active'=>true])
                        ->one();
        

        $final=[
            'active_services'=>$active_services,
            'total_services'=>$total_services,
            'active_machines'=>$active_machines,
            'total_machines'=>$total_machines,
            'active_ondemand'=>$active_ondemand,
            'total_ondemand'=>$total_ondemand,
            'vms_services_active'=>$vms_services_active,
            'vms_services_total'=>$vms_services_total,
            'vms_machines_active'=>$vms_machines_active,
            'vms_machines_total'=>$vms_machines_total,
            'active_services_cores'=>$vm_active_services_stats['cores'],
            'active_services_ram'=>$vm_active_services_stats['ram'],
            'active_services_storage'=>$vm_active_services_stats['storage']/1000.0,
            'total_services_cores'=>$vm_total_services_stats['cores'],
            'total_services_ram'=>$vm_total_services_stats['ram'],
            'total_services_storage'=>$vm_total_services_stats['storage']/1000.0,
            'active_machines_cores'=>$vm_active_machines_stats['cores'],
            'active_machines_ram'=>$vm_active_machines_stats['ram'],
            'active_machines_storage'=>$vm_active_machines_stats['storage']/1000.0,
            'total_machines_cores'=>$vm_total_machines_stats['cores'],
            'total_machines_ram'=>$vm_total_machines_stats['ram'],
            'total_machines_storage'=>$vm_total_machines_stats['storage']/1000.0,
            'total_storage_projects'=>$volumes_machines['number'] + $volumes_service['number'],
            'total_storage_size'=>($volumes_machines['total'] + $volumes_service['total'])/1024.0,
            'number_storage_service'=>$volumes_service['number'],
            'number_storage_machines'=>$volumes_machines['number'],
            'size_storage_service'=>$volumes_service['total']/1024.0,
            'size_storage_machines'=>$volumes_machines['total']/1024.0,


        ];
        return $final;
    }
    
}
