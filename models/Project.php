<?php

namespace app\models;

use Yii;
use yii\db\Query;
use yii\httpclient\Client;
use webvimark\modules\UserManagement\models\User as Userw;
use app\models\User;
use app\models\ProjectRequest;

/**
 * This is the model class for table "project".
 *
 * @property int $id
 * @property string $name
 * @property int $status
 * @property int $latest_project_request_id
 */
class Project extends \yii\db\ActiveRecord
{

    const TYPES=[0=>'On-demand batch computation', 1=>'24/7 Service', 2=>'Cold-Storage', 3=>'On-demand computation machines', 4=>'On-demand notebooks'];
    const STATUSES=[-5=>'Εxpired',-4 =>'Deleted',-1=>'Rejected',0=>'Pending', 1=>'Approved', 2=>'Auto-approved'];  
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'latest_project_request_id'], 'default', 'value' => null],
            [['status', 'latest_project_request_id'], 'integer'],
            [['name'], 'string', 'max' => 200],
            [['favorite'], 'boolean']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'status' => 'Status',
            'latest_project_request_id' => 'Latest Project Request ID',
        ];
    }

    public static function getActiveProjectsOwner()
    {
        $query=new Query;

        $status=[1,2];
        $date=date("Y-m-d");

        $user=Userw::getCurrentUser()['id'];

        $query->select(['pr.id','p.id as project_id','pr.name','pr.end_date', 'pr.duration','pr.submission_date','pr.approval_date','pr.status','pr.viewed', 'pr.project_type','username', 'pr.louros', 'p.favorite'])
              ->from('project as p')
              ->innerJoin('project_request as pr','p.latest_project_request_id=pr.id')
              ->innerJoin('user as u','pr.submitted_by=u.id')
              ->where(['IN','pr.status',$status])
              ->andWhere(['>=', 'end_date', $date])
              ->andWhere(['pr.submitted_by'=>$user]);
              
        
        $results=$query->all();
      
        return $results;



    }

    public static function getActiveProjectsParticipant()
    {
        $query=new Query;

        $status=[1,2];
        $date=date("Y-m-d");

        $user=Userw::getCurrentUser()['id'];
        // print_r($user);
        // exit(0);

        $query->select(['pr.id','p.id as project_id','pr.name','pr.end_date', 'pr.duration','pr.submission_date','pr.approval_date','pr.status','pr.viewed', 'pr.project_type','u.username','pr.louros', 'p.favorite'])
              ->from('project as p')
              ->innerJoin('project_request as pr','p.latest_project_request_id=pr.id')
              ->innerJoin('user as u','pr.submitted_by=u.id')
              ->where(['IN','pr.status',$status])
              ->andWhere(['>=', 'end_date', $date])
              ->andWhere("$user = ANY(pr.user_list)")
              ->andWhere(['<>','pr.submitted_by',$user]);

        
        $results=$query->all();

        return $results;

    }


    public static function getProjectsParticipants()
    {
        $query=new Query;

        $status=[1,2,-1,0];

        $user=Userw::getCurrentUser()['id'];
        // print_r($user);
        // exit(0);

        $query->select(['pr.id','pr.name','pr.duration','pr.submission_date','pr.status','pr.viewed', 'pr.project_type','username'])
              ->from('project as p')
              ->innerJoin('project_request as pr','p.latest_project_request_id=pr.id')
              ->innerJoin('user as u','pr.submitted_by=u.id')
              ->where(['IN','pr.status',$status])
              ->andWhere("$user = ANY(pr.user_list)")
              ->andWhere(['<>','pr.submitted_by',$user])
              ->orderBy('pr.submission_date DESC');
        // print_r($query->createCommand()->getRawSql());
        // exit(0);
        
        $results=$query->all();
        
        return $results;

    }

    public static function getActiveProjectsApi($username)
    {
        $query=new Query;

        $status=[1,2];

        $user=User::findByUsername($username);
        $user=$user->id;
        
          // print_r($user);
          // exit(0);

        $query->select(['pr.name'])
                ->from('project as p')
                ->innerJoin('project_request as pr','p.latest_project_request_id=pr.id')
                ->where(['IN','pr.status',$status])
                ->andWhere(['pr.project_type'=>0])
                ->andFilterWhere([

                'or',

                ['pr.submitted_by'=>$user],

                "$user = ANY(pr.user_list)"

              ])

              ->orderBy('pr.submission_date DESC');
        
        $results=$query->all();
        // print_r($query->createCommand()->getRawSql());
        // exit(0);
        
        return $results;

    }

    public static function getActiveOndemandQuotasApi($username)
    {
        $query=new Query;

        $status=[1,2];

        $user=User::findByUsername($username);
        if (empty($user))
        {
            return [];
        }
        $user=$user->id;
        
          // print_r($user);
          // exit(0);

        $query->select(['pr.name','pr.approval_date', 'pr.duration','pr.end_date','odr.num_of_jobs','odr.ram','odr.cores'])
                ->from('project as p')
                ->innerJoin('project_request as pr','p.latest_project_request_id=pr.id')
                ->innerJoin('ondemand_request as odr','pr.id=odr.request_id')
                ->where(['IN','pr.status',$status])
                ->andWhere(['pr.project_type'=>0])
                ->andFilterWhere([

                'or',

                ['pr.submitted_by'=>$user],

                "$user = ANY(pr.user_list)"

              ])

              ->orderBy('pr.submission_date DESC');
        // print_r($query->createCommand()->getRawSql());
        // exit(0);
        
        $results=$query->all();

        
        $active=[];
        foreach ($results as $project) 
        {
            
            $start=date('Y-m-d',strtotime($project['approval_date']));
            $duration=$project['duration'];
            if(is_null($project['end_date']))
            {
                $end=date('Y-m-d', strtotime($start. " + $duration months"));
            }
            else
            {
              $end=$project['end_date'];
            }
            
            $now = strtotime(date("Y-m-d"));
            $end_project = strtotime($end);
            $remaining_secs=$end_project-$now;
            $remaining_days=$remaining_secs/86400;
            if($remaining_days>0)
            {

                 $active[]=$project;

            }
        }
        //$myJson=json_encode($active);
        
        
        return $active;

    }

    public static function getAllOndemandQuotasApi($username)
    {
        $query=new Query;

        $status=[1,2];

        $user=User::findByUsername($username);
        if (empty($user))
        {
            return [];
        }
        $user=$user->id;
        

        $query->select(['pr.name','pr.approval_date', 'pr.duration','pr.end_date','odr.num_of_jobs','odr.ram','odr.cores'])
                ->from('project as p')
                ->innerJoin('project_request as pr','p.latest_project_request_id=pr.id')
                ->innerJoin('ondemand_request as odr','pr.id=odr.request_id')
                ->where(['IN','pr.status',$status])
                ->andWhere(['pr.project_type'=>0])
                ->andFilterWhere([

                'or',

                ['pr.submitted_by'=>$user],

                "$user = ANY(pr.user_list)"

              ])

              ->orderBy('pr.submission_date DESC');
        
        $results=$query->all();        
        
        return $results;

    }

    public static function getOndemandProjectQuotas($username,$project)
    {
        $query=new Query;

        $status=[1,2];

        // print_r($username);
        // exit(0);
        $user=User::findByUsername($username);
        if (empty($user))
        {
            return [];
        }
        $user=$user->id;
        
          // print_r($user);
          // exit(0);

        $query->select(['odr.num_of_jobs','odr.ram','odr.cores','end_date'])
                ->from('project as p')
                ->innerJoin('project_request as pr','p.latest_project_request_id=pr.id')
                ->innerJoin('ondemand_request as odr','pr.id=odr.request_id')
                ->where(['IN','pr.status',$status])
                ->andWhere(['pr.project_type'=>0])
                ->andWhere(['pr.name'=>$project])
                ->andFilterWhere([

                'or',

                ['pr.submitted_by'=>$user],

                "$user = ANY(pr.user_list)"

              ])

              ->orderBy('pr.submission_date DESC');
        // print_r($query->createCommand()->getRawSql());
        // exit(0);
        
        $results=$query->all();
        
        return $results;

    }

    public static function userInProject($projectId)
    {
        $query=new Query;

        $status=[0,1,2];

        $user=Userw::getCurrentUser()['id'];

        $query->select(['pr.id','pr.name','pr.duration','pr.submission_date','pr.status','pr.viewed', 'pr.project_type','u.username'])
              ->from('project_request as pr')
              ->innerJoin('user as u','pr.submitted_by=u.id')
              // ->where(['IN','pr.status',$status])
              ->where(['or', ['pr.submitted_by'=>$user],"$user = ANY(pr.user_list)"])
              ->andWhere(['pr.project_id'=>$projectId])
              ->orderBy('pr.submission_date DESC');
        
        $results=$query->all();

        return $results;



    }

    public static function getDeletedProjects()
    {
        $query=new Query;

        $status=ProjectRequest::DELETED;

        $user=Userw::getCurrentUser()['id'];
        // print_r($user);
        // exit(0);

        $query->select(['pr.id','pr.name','pr.duration','pr.deletion_date','pr.status','pr.viewed', 'pr.project_type','u.username'])
              ->from('project as p')
              ->innerJoin('project_request as pr','p.latest_project_request_id=pr.id')
              ->innerJoin('user as u','pr.submitted_by=u.id')
              ->where(['IN','pr.status',$status])
              ->andWhere("$user = ANY(pr.user_list)")
              ->orderBy('pr.submission_date DESC');
        // print_r($query->createCommand()->getRawSql());
        // exit(0);
        
        $results=$query->all();

        // print_r($results);
        // exit(0);
        
        return $results;

    }

    public static function getExpiredProjects()
    {
        $query=new Query;

       // $status=ProjectRequest::EXPIRED;
        $date=date("Y-m-d");
        $user=Userw::getCurrentUser()['id'];
        // print_r($user);
        // exit(0);

        $query->select(['pr.id','pr.name','pr.duration',"pr.end_date",'pr.status','pr.viewed', 'pr.approval_date','pr.project_type','u.username'])
              ->from('project as p')
              ->innerJoin('project_request as pr','p.latest_project_request_id=pr.id')
              ->innerJoin('user as u','pr.submitted_by=u.id')
              ->where(['<','end_date',$date])
              ->andWhere("$user = ANY(pr.user_list)")
              ->orderBy('pr.submission_date DESC');
        // print_r($query->createCommand()->getRawSql());
        // exit(0);
        
        $results=$query->all();

         //print_r($results);
        //exit(0);
        
        return $results;

    }

    public static function getAllActiveProjects()
    {
        $query=new Query;
        $date=date("Y-m-d");
        $status=[1,2];
        $query->select(['pr.id','pr.name', 'p.id as project_id', 'pr.duration','pr.status','pr.viewed','pr.end_date', 'pr.approval_date','pr.project_type', 'pr.submission_date', 'pr.submitted_by' //'u.username'
          ])
              ->from('project as p')
              ->innerJoin('project_request as pr','p.latest_project_request_id=pr.id')
              //->innerJoin('user as u','pr.submitted_by=u.id')
              ->where(['>=', 'end_date', $date])
              ->andWhere(['IN','pr.status',$status])
              ->orderBy('pr.submission_date DESC');
        
        
        $results=$query->all();
        return $results;

    }

    public static function getAllDeletedProjects()
    {
        $query=new Query;

        $status=ProjectRequest::DELETED;

        $user=Userw::getCurrentUser()['id'];
        // print_r($user);
        // exit(0);

        $query->select(['pr.id','pr.name','pr.duration','pr.deletion_date','pr.status','pr.viewed', 'pr.project_type','u.username'])
              ->from('project as p')
              ->innerJoin('project_request as pr','p.latest_project_request_id=pr.id')
              ->innerJoin('user as u','pr.submitted_by=u.id')
              ->where(['IN','pr.status',$status])
              ->orderBy('pr.submission_date DESC');
        
        $results=$query->all();
        
        return $results;

    }

    public static function getAllExpiredProjects($user='',$type='-1',$exp='-1', $name='')
    {
        $query=new Query;

       
        $date=date("Y-m-d");
        

        $query->select(['p.id as project_id','pr.id','pr.name','pr.duration',"pr.end_date",'pr.status','pr.viewed', 'pr.approval_date','pr.project_type','u.username'])
              ->from('project as p')
              ->innerJoin('project_request as pr','p.latest_project_request_id=pr.id')
              ->innerJoin('user as u','pr.submitted_by=u.id')
              ->where(['<','end_date',$date])
              ->orderBy('pr.submission_date DESC');
        
        if (!empty($user))
        {
            $query->andWhere("u.username like '%$user%'");
        }
        if (intval($type)>=0)
        {
            $query->andWhere("p.project_type=$type");
        }
        if (intval($exp)==1){

            $query->orderBy('pr.end_date DESC');
            
        }
        if (intval($exp)==0){

            $query->orderBy('pr.end_date ASC');
        }
        if (!empty($name)){
            $query->andWhere("p.name like '%$name%'");
        }
        $results=$query->all();
        
        
        
        return $results;

    }

    public static function getActiveResources()
    {
        /*
         * Get active services VMs, machine VMs, volumes and Jupyter servers
         */
        $active_vms=[];
        $active_machines=[];
        $active_volumes=[];

        $vms=Vm::find()->select(['project_id'])->where(['active'=>'t'])->distinct()->all();

        foreach ($vms as $vm)
        {
            $active_vms[$vm->project_id]=true;
        }

        $machines=VmMachines::find()->select(['project_id'])->where(['active'=>'t'])->distinct()->all();

        foreach ($machines as $mach)
        {
            $active_machines[$mach->project_id]=true;
        }
        
        $volumes=HotVolumes::find()->select(['project_id'])->where(['active'=>'t'])->distinct()->all();

        foreach ($volumes as $vol)
        {
            $active_volumes[$vm->project_id]=true;
        }

        try
        {
            $client = new Client();
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl(Yii::$app->params['schema_url'] . "/index.php?r=api/active-jupyter-projects")
                ->send();

            if (!$response->getIsOk())
            {
                $active_jupyter=[];
            }
            else
            {
                $active_jupyter=$response->data;
            }
            
        }
        catch (\Exception $e)
        {   
            $active_jupyter=[];
        }
        

        return [$active_jupyter, $active_vms, $active_machines, $active_volumes];
    }

    public static function getAllActiveProjectsAdm($user='',$type='-1', $exp='-1', $name='')
    {
        $query=new Query;
        $date=date("Y-m-d");
        $status=[1,2];
        $query->select(['pr.id','pr.name', 'p.id as project_id', 'pr.duration','pr.status','pr.viewed','pr.end_date', 'pr.approval_date','pr.project_type', 
            'pr.submission_date', 'pr.submitted_by','u.username', 'pr.louros'
          ])
              ->from('project as p')
              ->innerJoin('project_request as pr','p.latest_project_request_id=pr.id')
              ->innerJoin('user as u','pr.submitted_by=u.id')
              ->where(['>=', 'end_date', $date])
              ->andWhere(['IN','pr.status',$status])
              ->orderBy('pr.submission_date DESC');
        

        if (!empty($user))
        {
            $query->andWhere("u.username like '%$user%'");
        }
        if (intval($type)>=0)
        {
            $query->andWhere("p.project_type=$type");
        }
        if (intval($exp)==1){

            $query->orderBy('pr.end_date DESC');
            
        }
        if (intval($exp)==0){

            $query->orderBy('pr.end_date ASC');
        }
        if(!empty($name)){
            $query->andWhere("p.name like '%$name%'");
        }

        $results=$query->all();
        return $results;

    }


    public static function getMaximumActiveAcceptedProjects($project_type, $requested_role, $status)
    {
        
        $date=date("Y-m-d");
        $all_projects=ProjectRequest::find()->all();
        $project_owners=[];
        $roles_per_owner=[];
        foreach($all_projects as $project)
        {
            $user=User::find()->where(['id'=>$project->submitted_by])->one();
            $query=new Query;
            $query->select(['item_name'])
              ->from('auth_assignment')
              ->where(['user_id'=>$project->submitted_by]);
            $roles=$query->all();

            $roles_per_owner[$project->submitted_by]=$roles;
        }

        
        $max_gold=0;
        $max_silver=0;
        $max_bronze=0;        
        $max_per_role=['gold'=>$max_gold, 'silver'=>$max_silver, 'bronze'=>$max_bronze];
        foreach($roles_per_owner as $id=>$role)
        {
            $user_role='bronze';
            foreach($role as $rol)
            {
                if(in_array("Gold",$rol))
                {
                    $user_role='gold';
                }
                elseif(in_array("Silver",$rol))
                {
                    $user_role='silver';
                }
            }
            $roles_per_owner[$project->submitted_by]=$user_role;
            $user_projects=ProjectRequest::find()->where(['submitted_by'=>$id])
            ->andWhere(['>=','end_date',$date])
            ->andWhere(['project_type'=>$project_type])->andWhere(['in','status',$status])->count();

            if($user_role=='gold')
            {
                $current_gold=$max_per_role['gold'];
                if($user_projects>$current_gold)
                {
                    $max_gold=$user_projects;
                    $max_per_role['gold']=$user_projects;

                }
            }
            if($user_role=='silver')
            {
                $current_silver=$max_per_role['silver'];
                if($user_projects>$current_silver)
                {
                    $max_silver=$user_projects;
                    $max_per_role['silver']=$user_projects;

                }
            }
            if($user_role=='bronze')
            {
                $current_bronze=$max_per_role['bronze'];
                if($user_projects>$current_bronze)
                {
                    $max_bronze=$user_projects;
                    $max_per_role['bronze']=$user_projects;

                }
            }
            

        }
        
        return $max_per_role[$requested_role];
    }

    public static function userStatisticsParticipant($uid, $username)
    {
        $query=new Query;

        $active_services=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('service_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['>','pr.end_date','NOW()'])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();
        $query=new Query;

        $expired_services=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('service_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['<=','pr.end_date','NOW()'])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();
        $query=new Query;

        $total_services=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('service_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();
        $query=new Query;

        $active_machines=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('machine_compute_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['>','pr.end_date','NOW()'])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();
        $query=new Query;

        $expired_machines=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('machine_compute_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['<=','pr.end_date','NOW()'])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();
        $query=new Query;

        $total_machines=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('machine_compute_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();
        $query=new Query;

        $active_ondemand=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('ondemand_request as o','o.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['>','pr.end_date','NOW()'])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();

        $query=new Query;

        $expired_ondemand=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('ondemand_request as o','o.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['<=','pr.end_date','NOW()'])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();

        $query=new Query;

        $total_ondemand=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('ondemand_request as o','o.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();

        $query=new Query;

        $vms_services_active=$query->select(['p.id'])
                        ->from('project as p')
                        ->innerJoin('project_request as pr','pr.id=p.latest_project_request_id')
                        ->innerJoin('vm as v','v.project_id=p.id')
                        ->where(['IN','p.status',[1,2]])
                        ->andWhere(['v.active'=>true])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        // ->andWhere(['>','pr.end_date','NOW()'])
                        ->count();
        $query=new Query;

        $vms_services_total=$query->select(['p.id'])
                        ->from('project as p')
                        ->innerJoin('vm as v','v.project_id=p.id')
                        ->innerJoin('project_request as pr','pr.id=p.latest_project_request_id')
                        ->where(['IN','p.status',[1,2]])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();
        $query=new Query;

        $vms_machines_active=$query->select(['p.id'])
                        ->from('project as p')
                        ->innerJoin('vm_machines as v','v.project_id=p.id')
                        ->innerJoin('project_request as pr','pr.id=p.latest_project_request_id')
                        ->where(['IN','p.status',[1,2]])
                        ->andWhere(['v.active'=>true])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();

        $query=new Query;

        $vms_machines_total=$query->select(['pr.id'])
                        ->from('project as p')
                        ->innerJoin('vm_machines as v','v.project_id=p.id')
                        ->innerJoin('project_request as pr','pr.id=p.latest_project_request_id')
                        ->where(['IN','p.status',[1,2]])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();
        $query=new Query;

        $vm_active_services_stats=$query->select(['sum(s.num_of_cores) as cores', 'sum(s.ram) as ram', 'sum(s.storage) as storage'])
                        ->from('project_request as pr')
                        ->innerJoin('vm as v','v.project_id=pr.project_id')
                        ->innerJoin('service_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['v.active'=>true])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->one();
        
        $query=new Query;

        $vm_active_machines_stats=$query->select(['sum(s.num_of_cores) as cores', 'sum(s.ram) as ram', 'sum(s.storage) as storage'])
                        ->from('project_request as pr')
                        ->innerJoin('vm_machines as v','v.project_id=pr.project_id')
                        ->innerJoin('machine_compute_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['v.active'=>true])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        // ->andWhere(['>','pr.end_date','NOW()'])
                        ->one();

        $query=new Query;             
        $vm_total_services_stats=$query->select(['sum(s.num_of_cores) as cores', 'sum(s.ram) as ram', 'sum(s.storage) as storage'])
                        ->from('project_request as pr')
                        ->innerJoin('vm as v','v.request_id=pr.id')
                        ->innerJoin('service_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->one();
        
        $query=new Query;

        $vm_total_machines_stats=$query->select(['sum(s.num_of_cores) as cores', 'sum(s.ram) as ram', 'sum(s.storage) as storage'])
                        ->from('project_request as pr')
                        ->innerJoin('vm_machines as v','v.project_id=pr.project_id')
                        ->innerJoin('machine_compute_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->one();

        $query=new Query;

        $volumes_service=$query->select(['count(v.id) as number','sum(c.storage) as total'])
                        ->from('cold_storage_request as c')
                        ->innerJoin('project_request as pr','pr.id=c.request_id')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->innerJoin('hot_volumes as v','v.project_id=p.id')
                        ->where(['v.vm_type'=>1,'v.active'=>true])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->one();
        $query=new Query;
        $volumes_machines=$query->select(['count(v.id) as number','sum(c.storage) as total'])
                        ->from('cold_storage_request as c')
                        ->innerJoin('project_request as pr','pr.id=c.request_id')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->innerJoin('hot_volumes as v','v.project_id=p.id')
                        ->where(['v.vm_type'=>2,'v.active'=>true])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->one();
        
        $query=new Query;
        $number_storage_service_projects=$query->select(['id'])
                        ->from('cold_storage_request as c')
                        ->innerJoin('project_request as pr','pr.id=c.request_id')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->where(['c.vm_type'=>1,'p.status'=>[1,2]])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();
        
        $query=new Query;
        $number_storage_machines_projects=$query->select(['id'])
                        ->from('cold_storage_request as c')
                        ->innerJoin('project_request as pr','pr.id=c.request_id')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->where(['c.vm_type'=>2,'p.status'=>[1,2]])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();

        $query=new Query;
        $active_notebooks=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('jupyter_request_n as o','o.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['>','pr.end_date','NOW()'])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();

        $query=new Query;
        $expired_notebooks=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('jupyter_request_n as o','o.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['<=','pr.end_date','NOW()'])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();

        $query=new Query;
        $total_notebooks=$query->select(['pr.id'])
                        ->from('project_request as pr')
                        ->innerJoin('jupyter_request_n as o','o.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();

        $query=new Query;
        $active_servers=$query->select(['s.id'])
                        ->from('project_request as pr')
                        ->innerJoin('jupyter_request_n as o','o.request_id=pr.id')
                        ->innerJoin('jupyter_server as s','pr.name=s.project')
                        ->where(['s.created_by'=>$username])
                        ->andwhere(['IN','pr.status',[1,2]])
                        ->andWhere(['s.active'=>'t'])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();

        $query=new Query;
        $inactive_servers=$query->select(['s.id'])
                        ->from('project_request as pr')
                        ->innerJoin('jupyter_request_n as o','o.request_id=pr.id')
                        ->innerJoin('jupyter_server as s','pr.name=s.project')
                        ->andwhere(['IN','pr.status',[1,2]])
                        ->where(['s.created_by'=>$username])
                        ->andWhere(['s.active'=>'f'])
                        ->andWhere("$uid = ANY(pr.user_list)")
                        ->andWhere(['<>','pr.submitted_by',$uid])
                        ->count();

        $total_servers=$active_servers+$inactive_servers;
                                

        

        $final=[
            'active_services'=>$active_services,
            'expired_services'=>$expired_services,
            'total_services'=>$total_services,
            'active_machines'=>$active_machines,
            'expired_machines'=>$expired_machines,
            'total_machines'=>$total_machines,
            'active_ondemand'=>$active_ondemand,
            'expired_ondemand'=>$expired_ondemand,
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
            'total_storage_projects'=>$number_storage_machines_projects + $number_storage_service_projects,
            'number_storage_service_projects' => $number_storage_service_projects,
            'number_storage_machines_projects' => $number_storage_machines_projects,
            'total_volumes'=>$volumes_machines['number'] + $volumes_service['number'],
            'total_storage_size'=>($volumes_machines['total'] + $volumes_service['total'])/1024.0,
            'number_volumes_service'=>$volumes_service['number'],
            'number_volumes_machines'=>$volumes_machines['number'],
            'size_storage_service'=>$volumes_service['total']/1024.0,
            'size_storage_machines'=>$volumes_machines['total']/1024.0,
            'active_notebooks'=>$active_notebooks,
            'expired_notebooks'=>$expired_notebooks,
            'total_notebooks'=>$total_notebooks,
            'active_servers'=>$active_servers,
            'inactive_servers'=>$inactive_servers,
            'total_servers'=>$total_servers,


        ];

        foreach ($final as $f){
            if (empty($f)){
                $f=0;
            }
        }
        return $final;
    }

    public static function userStatisticsOwner($uid, $username)
    {
        $query=new Query;

        $active_services=$query->select(['p.id'])
                        ->from('project_request as pr')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->innerJoin('service_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['>','pr.end_date','NOW()'])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->count();
        $query=new Query;

        $expired_services=$query->select(['p.id'])
                        ->from('project_request as pr')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->innerJoin('service_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['<=','pr.end_date','NOW()'])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->count();
        $query=new Query;

        $total_services=$query->select(['p.id'])
                        ->from('project_request as pr')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->innerJoin('service_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->count();
        $query=new Query;

        $active_machines=$query->select(['p.id'])
                        ->from('project_request as pr')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->innerJoin('machine_compute_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['>','pr.end_date','NOW()'])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->count();
        $query=new Query;

        $expired_machines=$query->select(['p.id'])
                        ->from('project_request as pr')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->innerJoin('machine_compute_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['<=','pr.end_date','NOW()'])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->count();
        $query=new Query;

        $total_machines=$query->select(['p.id'])
                        ->from('project_request as pr')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->innerJoin('machine_compute_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->count();
        $query=new Query;

        $active_ondemand=$query->select(['p.id'])
                        ->from('project_request as pr')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->innerJoin('ondemand_request as o','o.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['>','pr.end_date','NOW()'])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->count();

        $query=new Query;

        $expired_ondemand=$query->select(['p.id'])
                        ->from('project_request as pr')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->innerJoin('ondemand_request as o','o.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['<','pr.end_date','NOW()'])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->count();

        $query=new Query;

        $total_ondemand=$query->select(['p.id'])
                        ->from('project_request as pr')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->innerJoin('ondemand_request as o','o.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->count();

        $query=new Query;

        $vms_services_active=$query->select(['p.id'])
                        ->from('project as p')
                        ->innerJoin('project_request as pr','pr.id=p.latest_project_request_id')
                        ->innerJoin('vm as v','v.project_id=p.id')
                        ->where(['IN','p.status',[1,2]])
                        ->andWhere(['v.active'=>true])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        // ->andWhere(['>','pr.end_date','NOW()'])
                        ->count();
        $query=new Query;

        $vms_services_total=$query->select(['p.id'])
                        ->from('project as p')
                        ->innerJoin('vm as v','v.project_id=p.id')
                        ->innerJoin('project_request as pr','pr.id=p.latest_project_request_id')
                        ->where(['IN','p.status',[1,2]])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->count();
        $query=new Query;

        $vms_machines_active=$query->select(['p.id'])
                        ->from('project as p')
                        ->innerJoin('vm_machines as v','v.project_id=p.id')
                        ->innerJoin('project_request as pr','pr.id=p.latest_project_request_id')
                        ->where(['IN','p.status',[1,2]])
                        ->andWhere(['v.active'=>true])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->count();

        $query=new Query;

        $vms_machines_total=$query->select(['pr.id'])
                        ->from('project as p')
                        ->innerJoin('vm_machines as v','v.project_id=p.id')
                        ->innerJoin('project_request as pr','pr.id=p.latest_project_request_id')
                        ->where(['IN','p.status',[1,2]])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->count();
        $query=new Query;

        $vm_active_services_stats=$query->select(['sum(s.num_of_cores) as cores', 'sum(s.ram) as ram', 'sum(s.storage) as storage'])
                        ->from('project_request as pr')
                        ->innerJoin('vm as v','v.project_id=pr.project_id')
                        ->innerJoin('service_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['v.active'=>true])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->one();
        
        $query=new Query;

        $vm_active_machines_stats=$query->select(['sum(s.num_of_cores) as cores', 'sum(s.ram) as ram', 'sum(s.storage) as storage'])
                        ->from('project_request as pr')
                        ->innerJoin('vm_machines as v','v.project_id=pr.project_id')
                        ->innerJoin('machine_compute_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['v.active'=>true])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        // ->andWhere(['>','pr.end_date','NOW()'])
                        ->one();

        $query=new Query;             
        $vm_total_services_stats=$query->select(['sum(s.num_of_cores) as cores', 'sum(s.ram) as ram', 'sum(s.storage) as storage'])
                        ->from('project_request as pr')
                        ->innerJoin('vm as v','v.request_id=pr.id')
                        ->innerJoin('service_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->one();
        
        $query=new Query;

        $vm_total_machines_stats=$query->select(['sum(s.num_of_cores) as cores', 'sum(s.ram) as ram', 'sum(s.storage) as storage'])
                        ->from('project_request as pr')
                        ->innerJoin('vm_machines as v','v.project_id=pr.project_id')
                        ->innerJoin('machine_compute_request as s','s.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->one();
        $query=new Query;

        $volumes_service=$query->select(['count(v.id) as number','sum(c.storage) as total'])
                        ->from('cold_storage_request as c')
                        ->innerJoin('project_request as pr','pr.id=c.request_id')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->innerJoin('hot_volumes as v','v.project_id=p.id')
                        ->where(['v.vm_type'=>1,'v.active'=>true])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->one();
        $query=new Query;
        $volumes_machines=$query->select(['count(v.id) as number','sum(c.storage) as total'])
                        ->from('cold_storage_request as c')
                        ->innerJoin('project_request as pr','pr.id=c.request_id')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->innerJoin('hot_volumes as v','v.project_id=p.id')
                        ->where(['v.vm_type'=>2,'v.active'=>true])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->one();
        
        $query=new Query;
        $number_storage_service_projects=$query->select(['id'])
                        ->from('cold_storage_request as c')
                        ->innerJoin('project_request as pr','pr.id=c.request_id')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->where(['c.vm_type'=>1,'p.status'=>[1,2]])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->count();
        
        $query=new Query;
        $number_storage_machines_projects=$query->select(['id'])
                        ->from('cold_storage_request as c')
                        ->innerJoin('project_request as pr','pr.id=c.request_id')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->where(['c.vm_type'=>2,'p.status'=>[1,2]])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->count();

        $query=new Query;
        $active_notebooks=$query->select(['p.id'])
            ->from('project_request as pr')
            ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
            ->innerJoin('jupyter_request_n as o','o.request_id=pr.id')
            ->where(['IN','pr.status',[1,2]])
            ->andWhere(['>','pr.end_date','NOW()'])
            ->andWhere(['pr.submitted_by'=>$uid])
            ->count();

        $query=new Query;

        $expired_notebooks=$query->select(['p.id'])
                        ->from('project_request as pr')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->innerJoin('jupyter_request_n as o','o.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['<','pr.end_date','NOW()'])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->count();

        $query=new Query;

        $total_notebooks=$query->select(['p.id'])
                        ->from('project_request as pr')
                        ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
                        ->innerJoin('jupyter_request_n as o','o.request_id=pr.id')
                        ->where(['IN','pr.status',[1,2]])
                        ->andWhere(['pr.submitted_by'=>$uid])
                        ->count();

        $query=new Query;
        $active_servers=$query->select(['s.id'])
                        ->from('jupyter_server as s')
                        ->where(['s.created_by'=>$username])
                        ->andWhere(['s.active'=>'t'])
                        ->count();

        $query=new Query;
        $inactive_servers=$query->select(['s.id'])
                        ->from('jupyter_server as s')
                        ->where(['s.created_by'=>$username])
                        ->andWhere(['s.active'=>'f'])
                        ->count();

        $total_servers=$active_servers+$inactive_servers;
                

        $final=[
            'active_services'=>$active_services,
            'expired_services'=>$expired_services,
            'total_services'=>$total_services,
            'active_machines'=>$active_machines,
            'expired_machines'=>$expired_machines,
            'total_machines'=>$total_machines,
            'active_ondemand'=>$active_ondemand,
            'expired_ondemand'=>$expired_ondemand,
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
            'total_storage_projects'=>$number_storage_machines_projects + $number_storage_service_projects,
            'number_storage_service_projects' => $number_storage_service_projects,
            'number_storage_machines_projects' => $number_storage_machines_projects,
            'total_volumes'=>$volumes_machines['number'] + $volumes_service['number'],
            'total_storage_size'=>($volumes_machines['total'] + $volumes_service['total'])/1024.0,
            'number_volumes_service'=>$volumes_service['number'],
            'number_volumes_machines'=>$volumes_machines['number'],
            'size_storage_service'=>$volumes_service['total']/1024.0,
            'size_storage_machines'=>$volumes_machines['total']/1024.0,
            'active_notebooks'=>$active_notebooks,
            'expired_notebooks'=>$expired_notebooks,
            'total_notebooks'=>$total_notebooks,
            'active_servers'=>$active_servers,
            'inactive_servers'=>$inactive_servers,
            'total_servers'=>$total_servers,


        ];
        foreach ($final as $f){
            if (empty($f)){
                $f=0;
            }
        }
        return $final;
    }

    public static function getProjectOwner($project_name)
    {
        $query=new Query;


        $query->select(['pr.submitted_by'])
              ->from('project as p')
              ->innerJoin('project_request as pr','p.latest_project_request_id=pr.id')
              ->Where(['p.name'=>$project_name]);
              
        
        $results=$query->one();
      
        return $results;



    }

    
}
