<?php

namespace app\models;

use app\models\ProjectRequest;
use Yii;
use yii\db\Query;
use yii\httpclient\Client;
use webvimark\modules\UserManagement\models\User as Userw;
use app\models\User;

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

    const TYPES=[0=>'On-demand batch computation', 1=>'24/7 Service', 2=>'Storage', 3=>'On-demand computation machines', 4=>'On-demand notebooks'];
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
            [['favorite'], 'boolean'],
            [['project_end_date'], 'date', 'format' => 'php:Y-m-d'],
            [['extension_count'], 'default', 'value' => 0],
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
            'project_end_date' => 'End Date'
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
        $query = new Query;
        $date = date("Y-m-d");

        $query->select([
            'p.id as project_id',
            'pr.id as project_request_id',
            'pr.name',
            'pr.duration',
            'pr.end_date',
            'pr.status',
            'pr.viewed',
            'pr.approval_date',
            'pr.project_type',
            'pr.submitted_by',
            'u.username'
        ])
            ->from('project as p')
            ->innerJoin('project_request as pr', 'p.latest_project_request_id = pr.id')
            ->innerJoin('user as u', 'pr.submitted_by = u.id')
            ->where(['<', 'pr.end_date', $date]);  // <-- ensure pr.end_date (not p.project_end_date)

        if (!empty($user)) {
            $query->andWhere(['like', 'u.username', $user]);
        }

        if (intval($type) >= 0) {
            $query->andWhere(['p.project_type' => $type]);
        }
        if (intval($exp)==1){

            $query->orderBy('pr.end_date DESC');
        } elseif (intval($exp) == 0) {
            $query->orderBy('pr.end_date ASC');
        }

        if (!empty($name)) {
            $query->andWhere(['like', 'pr.name', $name]);
        }

        $results = $query->all();

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
            $active_volumes[$vol->project_id]=true;
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


        return [
            0 => $active_jupyter,   // On-demand batch computations
            1 => $active_vms,       // 24/7 Services
            2 => $active_volumes,   // Storage volumes
            3 => $active_machines,  // Compute machines
            4 => $active_jupyter,   // On-demand notebooks
        ];
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
            ->from('storage_request as c')
            ->innerJoin('project_request as pr','pr.id=c.request_id')
            ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
            ->innerJoin('hot_volumes as v','v.project_id=p.id')
            ->where(['v.vm_type'=>1,'v.active'=>true])
            ->andWhere("$uid = ANY(pr.user_list)")
            ->andWhere(['<>','pr.submitted_by',$uid])
            ->one();
        $query=new Query;
        $volumes_machines=$query->select(['count(v.id) as number','sum(c.storage) as total'])
            ->from('storage_request as c')
            ->innerJoin('project_request as pr','pr.id=c.request_id')
            ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
            ->innerJoin('hot_volumes as v','v.project_id=p.id')
            ->where(['v.vm_type'=>2,'v.active'=>true])
            ->andWhere("$uid = ANY(pr.user_list)")
            ->andWhere(['<>','pr.submitted_by',$uid])
            ->one();

        $query=new Query;
        $number_storage_service_projects=$query->select(['id'])
            ->from('storage_request as c')
            ->innerJoin('project_request as pr','pr.id=c.request_id')
            ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
            ->where(['c.vm_type'=>1,'p.status'=>[1,2]])
            ->andWhere("$uid = ANY(pr.user_list)")
            ->andWhere(['<>','pr.submitted_by',$uid])
            ->count();

        $query=new Query;
        $number_storage_machines_projects=$query->select(['id'])
            ->from('storage_request as c')
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
            ->from('storage_request as c')
            ->innerJoin('project_request as pr','pr.id=c.request_id')
            ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
            ->innerJoin('hot_volumes as v','v.project_id=p.id')
            ->where(['v.vm_type'=>1,'v.active'=>true])
            ->andWhere(['pr.submitted_by'=>$uid])
            ->one();
        $query=new Query;
        $volumes_machines=$query->select(['count(v.id) as number','sum(c.storage) as total'])
            ->from('storage_request as c')
            ->innerJoin('project_request as pr','pr.id=c.request_id')
            ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
            ->innerJoin('hot_volumes as v','v.project_id=p.id')
            ->where(['v.vm_type'=>2,'v.active'=>true])
            ->andWhere(['pr.submitted_by'=>$uid])
            ->one();

        $query=new Query;
        $number_storage_service_projects=$query->select(['id'])
            ->from('storage_request as c')
            ->innerJoin('project_request as pr','pr.id=c.request_id')
            ->innerJoin('project as p', 'p.latest_project_request_id=pr.id' )
            ->where(['c.vm_type'=>1,'p.status'=>[1,2]])
            ->andWhere(['pr.submitted_by'=>$uid])
            ->count();

        $query=new Query;
        $number_storage_machines_projects=$query->select(['id'])
            ->from('storage_request as c')
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

    //to 'delete' a project set the end date of the latest request to a previous date
    //if the project has active resources delete them
    public static function DeleteProject($pid){

        $project=Project::find()->where(['id'=>$pid])->one();
        $latest_pr = $project['latest_project_request_id'];

        //on demand batch computation
        if ($project['project_type']==0){
            if (!empty($project['pending_request_id'])) {
                $pending_req=ProjectRequest::find()->where(['id'=>$project['pending_request_id']])->one();
                $pending_req->cancel();
            }
            Yii::$app->db->createCommand()->update('project_request',['end_date'=>date('Y-m-d',strtotime("-1 days"))], "id='$latest_pr'")->execute();
            return 0;

            //24-7 service
        } elseif ($project['project_type']==1){
            $vm=Vm::find()->where(['project_id'=>$pid])->andwhere(['active'=>'t'])->one();
            $owner=Project::userInProject($pid);
            if ( (empty($owner)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) ){
                return $this->render('error_unauthorized');
            }
            if (!empty($vm)){
                $volumes=HotVolumes::find()->where(['vm_id'=>$vm->id])->all();
                foreach ($volumes as $volume)
                {
                    $volume->vm_id=null;
                    $volume->mountpoint=null;
                    $volume->save();
                }

                $result=$vm->deleteVM();
                $error=$result[0];
                $message=$result[1];
                $openstackMessage=$result[2];
                if ($error!=0) {
                    $eror_message = 'The project was not deleted. '.$message.'. Please contact an administrator with the following error code:'.$error.' ,'.$openstackMessage;
                    Yii::$app->session->setFlash('danger', $eror_message);
                    return 1;
                }
            }
            if (!empty($project['pending_request_id'])) {
                $pending_req=ProjectRequest::find()->where(['id'=>$project['pending_request_id']])->one();
                $pending_req->cancel();
            }
            Yii::$app->db->createCommand()->update('project_request',['end_date'=>date('Y-m-d',strtotime("-1 days"))], "id='$latest_pr'")->execute();
            return 0;

            //storage volume
        } elseif($project['project_type']==2){
            $results=StorageRequest::getActiveProjects();
            $cold_storage_request=StorageRequest::find()->where(['request_id'=>$latest_pr])->one();
            //services
            if ($cold_storage_request['vm_type']==1){
                $volumes=$results[0];
                //machines
            } elseif($cold_storage_request['vm_type']==2){
                $volumes=$results[1];
            }
            $created_at='';
            $mountpoint='';
            $vol_id='';
            $mult_order='';
            $vname='';
            if ($cold_storage_request['vm_type']==1){
                foreach($volumes as $volume => $res){
                    if ($res['name']==$project->name){
                        $created_at=$res['created_at'];
                        $mountpoint=$res['mountpoint'];
                        $vol_id=$res['vol_id'];
                        $mult_order=$res['mult_order'];
                        $vname=$res['vname'];
                    }
                }
            } else{
                //on demand machines storage volumes can have more than one volumes, delete all of them
                foreach($volumes as $volume => $proj){
                    for ($i=1; $i<=$proj['count']; $i++) {
                        if ($proj[$i]['name']==$project->name){
                            $created_at=$proj[$i]['created_at'];
                            $mountpoint=$proj[$i]['mountpoint'];
                            $vol_id=$proj[$i]['vol_id'];
                            $vname=$proj[$i]['vmachname'];
                            if (!empty($created_at)){
                                //if that volume is attached to a vm, first detach it, then delete it
                                $participant=Project::userInProject($pid);
                                if ( (empty($participant)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) ){
                                    return $this->render('error_unauthorized');
                                }
                                $volume=HotVolumes::find()->where(['id'=>$vol_id, 'project_id'=>$pid])->one();
                                if (empty($volume)){
                                    Yii::$app->session->setFlash('danger', "Volume does not exist. Please create it and try again");
                                    return 1;
                                }
                                if (!empty($vname)){
                                    $volume->initialize($volume->vm_type);
                                    $volume->authenticate();
                                    if (!empty($volume->errorMessage))
                                    {
                                        Yii::$app->session->setFlash('danger', $volume->errorMessage);
                                        return 1;
                                    }
                                    $volume->detach();
                                    if (!empty($volume->errorMessage))
                                    {
                                        Yii::$app->session->setFlash('danger', $volume->errorMessage);
                                        return 1;
                                    }
                                }
                                //delete the volume
                                $volume->initialize($volume->vm_type);
                                $volume->authenticate();
                                if (!empty($hotvolume->errorMessage)){
                                    Yii::$app->session->setFlash('danger', $volume->errorMessage);
                                    return 1;
                                }
                                $volume->deleteVolume();
                                if (!empty($volume->errorMessage)){
                                    Yii::$app->session->setFlash('danger', $volume->errorMessage);
                                    return 1;
                                }
                            }
                        }
                    }
                }
                if (!empty($project['pending_request_id'])) {
                    $pending_req=ProjectRequest::find()->where(['id'=>$project['pending_request_id']])->one();
                    $pending_req->cancel();
                }
                Yii::$app->db->createCommand()->update('project_request',['end_date'=>date('Y-m-d',strtotime("-1 days"))], "id='$latest_pr'")->execute();
                return 0;
            }
            //if the project has a created volume
            if (!empty($created_at)){
                //if that volume is attached to a vm, first detach it, then delete it
                $participant=Project::userInProject($pid);
                if ( (empty($participant)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) ){
                    return $this->render('error_unauthorized');
                }
                $volume=HotVolumes::find()->where(['id'=>$vol_id, 'project_id'=>$pid])->one();
                if (empty($volume)){
                    Yii::$app->session->setFlash('danger', "Volume does not exist. Please create it and try again");
                    return 1;
                }
                if (!empty($vname)){
                    $volume->initialize($volume->vm_type);
                    $volume->authenticate();
                    if (!empty($volume->errorMessage))
                    {
                        Yii::$app->session->setFlash('danger', $volume->errorMessage);
                        return 1;
                    }
                    $volume->detach();
                    if (!empty($volume->errorMessage))
                    {
                        Yii::$app->session->setFlash('danger', $volume->errorMessage);
                        return 1;
                    }
                }
                //delete the volume
                $volume->initialize($volume->vm_type);
                $volume->authenticate();
                if (!empty($hotvolume->errorMessage)){
                    Yii::$app->session->setFlash('danger', $volume->errorMessage);
                    return 1;
                }
                $volume->deleteVolume();
                if (!empty($volume->errorMessage)){
                    Yii::$app->session->setFlash('danger', $volume->errorMessage);
                    return 1;
                }
            }
            if (!empty($project['pending_request_id'])) {
                $pending_req=ProjectRequest::find()->where(['id'=>$project['pending_request_id']])->one();
                $pending_req->cancel();
            }
            Yii::$app->db->createCommand()->update('project_request',['end_date'=>date('Y-m-d',strtotime("-1 days"))], "id='$latest_pr'")->execute();
            return 0;

            //on demand computation machines
        } elseif($project['project_type']==3){
            $vms=VmMachines::find()->where(['project_id'=>$pid])->andwhere(['active'=>'t'])->all();
            $owner=Project::userInProject($pid);
            if ( (empty($owner)) && (!Userw::hasRole('Admin',$superadminAllowed=true)) ){
                return $this->render('error_unauthorized');
            }
            if (!empty($vms)){
                foreach ($vms as $vm){
                    $volumes=HotVolumes::find()->where(['vm_id'=>$vm->id])->all();
                    foreach ($volumes as $volume)
                    {
                        $volume->vm_id=null;
                        $volume->mountpoint=null;
                        $volume->save();
                    }

                    $result=$vm->deleteVM();
                    $error=$result[0];
                    $message=$result[1];
                    $openstackMessage=$result[2];
                    if ($error!=0) {
                        $eror_message = 'The project was not deleted. '.$message.'. Please contact an administrator with the following error code:'.$error.' ,'.$openstackMessage;
                        Yii::$app->session->setFlash('danger', $eror_message);
                        return 1;
                    }
                }
            }
            if (!empty($project['pending_request_id'])) {
                $pending_req=ProjectRequest::find()->where(['id'=>$project['pending_request_id']])->one();
                $pending_req->cancel();
            }
            Yii::$app->db->createCommand()->update('project_request',['end_date'=>date('Y-m-d',strtotime("-1 days"))], "id='$latest_pr'")->execute();
            return 0;

            //on demand notebooks
        }elseif($project['project_type']==4){
            $all_servers=JupyterServer::find()->where(['active'=>true,'project'=>$project->name])->all();
            if (!empty($all_servers)){
                foreach ($all_servers as $server){
                    $server->Stopserver();
                }
            }
            if (!empty($project['pending_request_id'])) {
                $pending_req=ProjectRequest::find()->where(['id'=>$project['pending_request_id']])->one();
                $pending_req->cancel();
            }
            Yii::$app->db->createCommand()->update('project_request',['end_date'=>date('Y-m-d',strtotime("-1 days"))], "id='$latest_pr'")->execute();
            return 0;
        }

    }

    public static function getOndemandQuotasApi($project_id){
        $query=new Query;

        $query->select(['odr.num_of_jobs','odr.ram','odr.cores'])
            ->from('project as p')
            ->innerJoin('project_request as pr','p.latest_project_request_id=pr.id')
            ->innerJoin('ondemand_request as odr','pr.id=odr.request_id')
            ->where(['p.id'=>$project_id])
            ->one();
        $results=$query->all();
        return $results;
    }


    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $user = Yii::$app->user->identity ?? null;
        $isModerator = $user && (Userw::hasRole('Admin', true) || Userw::hasRole('Moderator', true));


        $firstApproved = ProjectRequest::find()
            ->where(['project_id' => $this->id])
            ->andWhere(['not', ['approval_date' => null]])
            ->orderBy(['approval_date' => SORT_ASC])
            ->one();

        // Last approved request for this project (AUTOAPPROVED or APPROVED)
        $lastApprovedRequest = ProjectRequest::find()
            ->where(['project_id' => $this->id])
            ->andWhere(['not', ['approval_date' => null]])
            ->andWhere(['status' => [ProjectRequest::AUTOAPPROVED, ProjectRequest::APPROVED]])
            ->orderBy(['approval_date' => SORT_DESC])
            ->one();

        if ($lastApprovedRequest == null) {
            return;
        }

        if ($firstApproved->id == $lastApprovedRequest->id) {
            $this->updateAttributes(['extension_count' => 0]);

        }


        $previousEndDate = $this->project_end_date;
        $newEndDate      = $lastApprovedRequest->end_date;

        // Only act if the approved request actually extends the project end date
        if (strtotime($newEndDate) > strtotime($previousEndDate)) {
            // Always keep the canonical end date in sync
            $this->updateAttributes(['project_end_date' => $newEndDate]);

            // Count all approved requests for this project
            $approvedCount = (int) ProjectRequest::find()
                ->where([
                    'project_id' => $this->id,
                    'status' => [ProjectRequest::AUTOAPPROVED, ProjectRequest::APPROVED],
                ])
                ->count();

            // First approval should NOT count as an extension
            $effectiveExtensionCount = max(0, $approvedCount - 1);

            if ($isModerator) {
                // Moderators do not change the counter
                $this->updateAttributes(['extension_count' => $this->extension_count]);
            }
            else
            {
                // Set to effective value (first approval -> 0; each further approval -> +1)
                $this->updateAttributes(['extension_count' => $effectiveExtensionCount]);
            }
        }

    }






}