<?php

namespace app\models;

use Yii;
use yii\db\Query;
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

    const TYPES=[0=>'On-demand batch computation', 1=>'24/7 Service', 2=>'Cold-Storage', 3=>'On-demand computation machines'];
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

    public static function getAllExpiredProjects($user='',$type='-1')
    {
        $query=new Query;

       
        $date=date("Y-m-d");
        

        $query->select(['pr.id','pr.name','pr.duration',"pr.end_date",'pr.status','pr.viewed', 'pr.approval_date','pr.project_type','u.username'])
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

        $results=$query->all();

         
        
        return $results;

    }

     public static function getAllActiveProjectsAdm($user='',$type='-1')
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

}
