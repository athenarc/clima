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
use app\models\EmailEvents;

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
            [['name','user_num','backup_services', 'end_date'],'required'],
            [['user_num'],'integer','min'=>0],
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

    public function uploadNew($participating,$project_type)
    {
        $errors='';
        $success='';
        $warnings='';
        $request_id=-1;

        if (empty($participating))
        {
            $errors="Error: You must specify at least one user participating in the project.";
        }
         if (count($participating)>$this->user_num)
        {
            $errors.="<br />The number of users specified is greater that the maximum number of users.";
        }
        
        if (empty($errors))
        {
            //remove duplicate participants
           
            $participant_ids_tmp=[];
            foreach ($participating as $participant)
            {
                // $name_exp=explode(' ',$participant);
                // $name=$name_exp[0];
                // $surname=$name_exp[1];
                $username=$participant . '@elixir-europe.org';
                $id=User::findByUsername($username)->id;
                $participant_ids_tmp[$id]=null;
            }

            $participant_ids=[];
            foreach ($participant_ids_tmp as $id => $dummy)
            {
                $participant_ids[]=$id;
            }
            // print_r($participant_ids);
            // exit(0);
            // print_r(User::findByUsername(Userw::getCurrentUser())->id);
            // exit(0);
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
            EmailEvents::NotifyByEmail('new_project', $project_id,$message);
        }

        return [$errors,$success,$warnings,$request_id];
    }

    public function uploadNewEdit($participating,$project_type,$modify_req_id='')
    {
        $errors='';
        $success='';
        $warnings='';
        $request_id=-1;

        if (empty($participating))
        {
            $errors="Error: You must specify at least one user participating in the project.";
        }
         if (count($participating)>$this->user_num)
        {
            $errors.="<br />The number of users specified is greater that the maximum number of users.";
        }
        
        if (empty($errors))
        {
           
            
            //if user is Admin, keep the owner of the project the same as in the old request
            if (!empty($modify_req_id))
            {
                $old_request=ProjectRequest::find()->where(['id'=>$modify_req_id])->one();
            }

            // print_r($modify_req_id);
            // exit(0);
            
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
            $request_id=$id = Yii::$app->db->getLastInsertID();

           
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
            
            

            $message="Project '$this->name' has been modified and is pending approval.";
            EmailEvents::NotifyByEmail('edit_project', $this->project_id,$message);
            
        }

        
        

        return [$errors,$success,$warnings,$request_id];
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
        $project_status=1;
        $project->save(false);

        if ($this->project_type==2)
        {

            $cold_storage_request=ColdStorageRequest::find()->where(['request_id'=>$this->id])->one();
            $vm_type=$cold_storage_request->vm_type;
            $size=$cold_storage_request->storage;
            $name=$this->name;
            if($cold_storage_request->type=='hot')
            {
                $hotvolume=new HotVolumes;
                $hotvolume->initialize($vm_type);
                $authenticate=$hotvolume->authenticate();
                $token=$authenticate[0];
                $message=$authenticate[1];
                if(!$token=='')
                {
                    $volume_id=$hotvolume->createVolume($size,$name,$token,$vm_type,$this->project_id);
                }
                
            }

        }

        $message="We are happy to inform you that project '$this->name' has been approved. <br /> You can access the project resources via the " . Yii::$app->params['name'] . " website";  

        foreach ($this->user_list as $user) 
        {
            Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'approved']));
        }

        EmailEvents::NotifyByEmail('project_decision', $this->project_id,$message);
             

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
        EmailEvents::NotifyByEmail('project_decision', $this->project_id,$message);


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


    public static function getVmList($filter)
    {
        $query=new Query;
        // print_r($filter);
        // exit(0);

        
        $query->select(['pr.id as request_id','pr.project_id as project_id', 'pr.name as project_name', 'pr.end_date',
                        'u1.username as created_by', 'u2.username as deleted_by', 'pr.project_type',
                        'v.created_at', 'v.deleted_at', 'v.active', 'v.id as vm_id'])
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
        
              
        // print_r($query->createCommand()->getRawSql());
        // exit(0);
       

        $pages = new Pagination(['totalCount' => $query->count()]);
        $pages->setPageSize(10);
        
        $results = $query->orderBy('v.created_at DESC')->offset($pages->offset)->limit($pages->limit)->all();
        
        return [$pages,$results];
    }


    public static function getVmMachinesList($filter)
    {
        $query=new Query;
        // print_r($filter);
        // exit(0);

        
        $query->select(['pr.id as request_id','pr.project_id as project_id', 'pr.name as project_name', 'pr.end_date',
                        'u1.username as created_by', 'u2.username as deleted_by', 'pr.project_type',
                        'v.created_at', 'v.deleted_at', 'v.active', 'v.id as vm_id'])
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
        
              
        // print_r($query->createCommand()->getRawSql());
        // exit(0);
       

        $pages = new Pagination(['totalCount' => $query->count()]);
        $pages->setPageSize(10);
        
        $results = $query->orderBy('v.created_at DESC')->offset($pages->offset)->limit($pages->limit)->all();
        
        return [$pages,$results];
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

    public static function modelChanged($model1, $model2)
    {
        $attr1=$model1->getAttributes();
        $attr2=$model2->getAttributes();
        // print_r($attr1);
        // print_r("<br />");
        // print_r($attr2);
        // print_r("<br />");
        // exit(0);


        foreach ($attr1 as $name => $value)
        {
        //     print_r($value);
        //     print_r("<br />");
        //     print_r($attr2[$name]);
        //     print_r("<br /><br />");
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
                // ->setUrl("http://83.212.72.66/schema/web/index.php?r=api/project-usage")
                ->setData($data)
                ->send();
        // if (!$response->getIsOk())
        // {
        //     return false;
        // }
        $usage=($response->data==false) ? [
                                            'count' => 0, 
                                            'total_time' => '00:00:00', 
                                            'avg_time' => '00:00:00', 
                                            'ram' => 0, 
                                            'cpu' => 0
                                          ] : $response->data;
        return $usage;
    }

    public static function getSchemaPeriodUsage()
    {

        $client = new Client();
        $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl(Yii::$app->params['schema_url'] . "/index.php?r=api/period-statistics")
                // ->setUrl("http://83.212.72.66/schema/web/index.php?r=api/project-usage")
                // ->setData($data)
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

        $vms_services_active=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('vm as v','v.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['v.active'=>true])
                        ->andWhere(['>','pr.end_date','NOW'])
                        ->count();
        $query=new Query;

        $vms_services_total=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('vm as v','v.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        // ->andWhere(['v.active'=>true])
                        ->count();
        $query=new Query;

        $vms_machines_active=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('vm_machines as v','v.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['v.active'=>true])
                        ->andWhere(['>','pr.end_date','NOW'])
                        ->count();
        $query=new Query;

        $vms_machines_total=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('vm_machines as v','v.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        // ->andWhere(['v.active'=>true])
                        ->count();
        $query=new Query;

        $vm_active_services_stats=$query->select(['sum(s.num_of_cores) as cores', 'sum(s.ram) as ram', 'sum(s.storage) as storage'])
                        ->from('project_request as pr')
                        ->innerJoin('vm as v','v.request_id=pr.id')
                        ->innerJoin('service_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['v.active'=>true])
                        ->andWhere(['>','pr.end_date','NOW'])
                        ->one();
        
        $query=new Query;

        $vm_active_machines_stats=$query->select(['sum(s.num_of_cores) as cores', 'sum(s.ram) as ram', 'sum(s.storage) as storage'])
                        ->from('project_request as pr')
                        ->innerJoin('vm_machines as v','v.request_id=pr.id')
                        ->innerJoin('service_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['v.active'=>true])
                        ->andWhere(['>','pr.end_date','NOW'])
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
                        ->innerJoin('vm_machines as v','v.request_id=pr.id')
                        ->innerJoin('service_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
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

        ];
        return $final;
    }
    
}
