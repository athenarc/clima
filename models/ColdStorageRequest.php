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
use app\models\EmailEventsUser;
use app\models\EmailEventsModerator;

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
            [['request_id', 'vm_type', 'num_of_volumes'], 'integer'],
            [['description', 'type'], 'string'],
            [['storage','description', 'type', 'vm_type','num_of_volumes'],'required'],
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
            'num_of_volumes' => 'Number of volumes',
            'storage'=>"",
        ];
    }



    public function uploadNew($requestId)
    {
        $errors='';
        $success='';
        $warnings='';
        $message_autoaccept_mod='';

        Yii::$app->db->createCommand()->insert('cold_storage_request', [

                'description' => $this->description,
                'storage' => $this->storage,
                'additional_resources'=>$this->additional_resources,
                'request_id' => $requestId,
                'type'=>$this->type,
                'num_of_volumes'=>$this->num_of_volumes,
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
        /*
         * Volumes for machine VMs should not be auto-accepted
         */
        if ($this->vm_type=='2')
        {
            $autoaccept_allowed=false;
        }

        $message_autoaccept='';
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
            $project->name=$request->name;
            $project->save();
            $username = User::returnUsernameById($request->submitted_by);


            $message_autoaccept="We are happy to inform you that your project '$project->name' has been automatically approved. <br /> You can access the project resources via the " . Yii::$app->params['name'] . " website";  
            $message_autoaccept_mod="We would like to inform you that the cold storage project '$project->name', submitted by user $username, has been automatically approved.";


            $cold_storage_request=ColdStorageRequest::find()->where(['request_id'=>$project->latest_project_request_id])->one();
            $vm_type=$cold_storage_request->vm_type;
            $size=$cold_storage_request->storage;
            $name=$project->name;

        }
             
        else
        {
            $warnings='Your request will be reviewed.';
        }

            
        $success='Successfully added project request!';
        return [$errors, $success, $warnings, $message_autoaccept, $message_autoaccept_mod];
    

    }

    public function uploadNewEdit($requestId,$uchanged)
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
                'num_of_volumes'=>$this->num_of_volumes,
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
        
        $request=ProjectRequest::find()->where(['id'=>$requestId])->one();
        $project=Project::find()->where(['id'=>$request->project_id])->one();

        /*
         * Volumes for machine VMs should not be auto-accepted
         */
        if ($this->vm_type=='2')
        {
            $autoaccept_allowed=false;
        }
        

        if ((($this->storage<=$row['storage']) && $autoaccept_allowed) || $uchanged)
        {

            /*
             * Get project_request from request id in order to get the project_id 
             * in order to update the latest active request 
             */
            
            
            $message="Updates to project '$request->name' have been automatically approved.";

            foreach ($request->user_list as $user) 
            {            
                Notification::notify($user,$message,2,Url::to(['project/user-request-list','filter'=>'auto-approved']));
            }


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
            $project->save();
        }
             
        else
        {
            $request=ProjectRequest::find()->where(['id'=>$project->latest_project_request_id])->one();
            $submitted_by = $request->submitted_by;
            $username = User::returnUsernameById($submitted_by);
            $warnings='Your request will be reviewed.';
            $project_id=$project->id;
            $message="The cold storage project '$project->name', created by user $username, has been modified and is pending approval.";
            EmailEventsModerator::NotifyByEmail('edit_project', $project_id,$message);
        }

            
        $success="Successfully modified project '$request->name'.";
        return [$errors, $success, $warnings];
    

    }

    public static function getActiveProjects()
    {
        $user=Userw::getCurrentUser()['id'];
        $query=new Query;
        $date=date("Y-m-d");
        $status=[1,2];
        $results=$query->select(['p.id','c.vm_type','p.name', 'c.num_of_volumes as vnum'])
                          ->from('project as p')
                          ->innerJoin('project_request as pr','p.latest_project_request_id=pr.id')
                          ->innerJoin('cold_storage_request as c','c.request_id=pr.id')
                          ->where(['>=', 'end_date', $date])
                          ->andWhere(['or', ['pr.submitted_by'=>$user],"$user = ANY(pr.user_list)"])
                          ->andWhere(['IN','pr.status',$status])
                          ->orderBy('pr.submission_date DESC')
                          ->all();
        $machines=[];
        $services=[];
        $project_ids=[];

        foreach ($results as $res) 
        {   
            $project_ids[]=$res['id'];
            if($res['vm_type']==1)
            {
                $services[$res['id']]=$res;
                $services[$res['id']]['created_at']='';
                $services[$res['id']]['mountpoint']='';
                $services[$res['id']]['vol_id']='';
                $services[$res['id']]['mult_order']='';
                $services[$res['id']]['vname']='';

            }
            else
            {
                if (!isset($machines[$res['id']]))
                {
                    $machines[$res['id']]=['count'=>$res['vnum'],'name'=>$res['name']];
                    for ($i=1; $i<=$res['vnum']; $i++)
                    {
                        $machines[$res['id']][$i]=$res;
                        $machines[$res['id']][$i]['created_at']='';
                        $machines[$res['id']][$i]['mountpoint']='';
                        $machines[$res['id']][$i]['vol_id']='';
                        $machines[$res['id']][$i]['vmachname']='';
                    }

                }

            }

        }

        $query=new Query;
        $date=date("Y-m-d");

        $results=$query->select(['h.project_id', 'h.id as vol_id', 'h.created_at', 'h.mountpoint', 
                'h.active', 'h.mult_order', 'v.name as vname', 'h.vm_type', 'vmach.name as vmachname'])
                          ->from('hot_volumes as h')
                          ->leftJoin('vm as v','v.id=h.vm_id')
                          ->leftJoin('vm_machines as vmach','vmach.id=h.vm_id')
                          ->where(['IN','h.project_id',$project_ids])
                          ->andWhere(['h.active'=>true])
                          ->all();

        foreach ($results as $res)
        {
            if($res['vm_type']==1)
            {
                $services[$res['project_id']]['created_at']=$res['created_at'];
                $services[$res['project_id']]['mountpoint']=$res['mountpoint'];
                $services[$res['project_id']]['vol_id']=$res['vol_id'];
                $services[$res['project_id']]['mult_order']=$res['mult_order'];
                $services[$res['project_id']]['vname']=$res['vname'];

            }
            else
            {
                $machines[$res['project_id']][$res['mult_order']]['created_at']=$res['created_at'];
                $machines[$res['project_id']][$res['mult_order']]['mountpoint']=$res['mountpoint'];
                $machines[$res['project_id']][$res['mult_order']]['vol_id']=$res['vol_id'];
                $machines[$res['project_id']][$res['mult_order']]['vmachname']=$res['vmachname'];
            }

        }

        return [$services,$machines];

    }

    public static function getActiveProjectsAdmin()
    {
        $user=Userw::getCurrentUser()['id'];
        $query=new Query;
        $date=date("Y-m-d");
        $status=[1,2];
        $results=$query->select(['p.id','u.username','c.vm_type','p.name', 'c.num_of_volumes as vnum'])
                          ->from('project as p')
                          ->innerJoin('project_request as pr','p.latest_project_request_id=pr.id')
                          ->innerJoin('cold_storage_request as c','c.request_id=pr.id')
                          ->innerJoin('user as u','pr.submitted_by=u.id')
                          ->where(['>=', 'end_date', $date])
                          ->andWhere(['IN','pr.status',$status])
                          ->orderBy('pr.submission_date DESC')
                          ->all();
        $machines=[];
        $services=[];
        $project_ids=[];

        foreach ($results as $res) 
        {   
            $project_ids[]=$res['id'];
            if($res['vm_type']==1)
            {
                $services[$res['id']]=$res;
                $services[$res['id']]['created_at']='';
                $services[$res['id']]['mountpoint']='';
                $services[$res['id']]['vol_id']='';
                $services[$res['id']]['mult_order']='';
                $services[$res['id']]['vname']='';
                $services[$res['id']]['username']=explode('@',$res['username'])[0];


            }
            else
            {
                if (!isset($machines[$res['id']]))
                {
                    $machines[$res['id']]=['count'=>$res['vnum'],'name'=>$res['name']];
                    for ($i=1; $i<=$res['vnum']; $i++)
                    {
                        $machines[$res['id']][$i]=$res;
                        $machines[$res['id']][$i]['created_at']='';
                        $machines[$res['id']][$i]['mountpoint']='';
                        $machines[$res['id']][$i]['vol_id']='';
                        $machines[$res['id']][$i]['vmachname']='';
                        $machines[$res['id']][$i]['username']=explode('@',$machines[$res['id']][$i]['username'])[0];
                    }

                }

            }

        }

        $query=new Query;
        $date=date("Y-m-d");

        $results=$query->select(['h.project_id', 'h.id as vol_id', 'h.created_at', 'h.mountpoint', 
                'h.active', 'h.mult_order', 'v.name as vname', 'h.vm_type', 'vmach.name as vmachname'])
                          ->from('hot_volumes as h')
                          ->leftJoin('vm as v','v.id=h.vm_id')
                          ->leftJoin('vm_machines as vmach','vmach.id=h.vm_id')
                          ->where(['IN','h.project_id',$project_ids])
                          ->andWhere(['h.active'=>true])
                          ->all();

        foreach ($results as $res)
        {
            if($res['vm_type']==1)
            {
                $services[$res['project_id']]['created_at']=$res['created_at'];
                $services[$res['project_id']]['mountpoint']=$res['mountpoint'];
                $services[$res['project_id']]['vol_id']=$res['vol_id'];
                $services[$res['project_id']]['mult_order']=$res['mult_order'];
                $services[$res['project_id']]['vname']=$res['vname'];

            }
            else
            {
                $machines[$res['project_id']][$res['mult_order']]['created_at']=$res['created_at'];
                $machines[$res['project_id']][$res['mult_order']]['mountpoint']=$res['mountpoint'];
                $machines[$res['project_id']][$res['mult_order']]['vol_id']=$res['vol_id'];
                $machines[$res['project_id']][$res['mult_order']]['vmachname']=$res['vmachname'];
            }

        }

        return [$services,$machines];

    }

    public static function getExpiredProjectsAdmin()
    {
        $user=Userw::getCurrentUser()['id'];
        $query=new Query;
        $date=date("Y-m-d");
        $status=[1,2];
        $results=$query->select(['p.id','u.username','c.vm_type','p.name', 'c.num_of_volumes as vnum'])
                          ->from('project as p')
                          ->innerJoin('project_request as pr','p.latest_project_request_id=pr.id')
                          ->innerJoin('cold_storage_request as c','c.request_id=pr.id')
                          ->innerJoin('user as u','pr.submitted_by=u.id')
                          ->where(['<', 'end_date', $date])
                          ->andWhere(['IN','pr.status',$status])
                          ->orderBy('pr.submission_date DESC')
                          ->all();
        $machines=[];
        $services=[];
        $project_ids=[];

        foreach ($results as $res) 
        {   
            $project_ids[]=$res['id'];
            if($res['vm_type']==1)
            {
                $services[$res['id']]=$res;
                $services[$res['id']]['created_at']='';
                $services[$res['id']]['mountpoint']='';
                $services[$res['id']]['vol_id']='';
                $services[$res['id']]['mult_order']='';
                $services[$res['id']]['vname']='';
                $services[$res['id']]['username']=explode('@',$res['username'])[0];


            }
            else
            {
                if (!isset($machines[$res['id']]))
                {
                    $machines[$res['id']]=['count'=>$res['vnum'],'name'=>$res['name']];
                    for ($i=1; $i<=$res['vnum']; $i++)
                    {
                        $machines[$res['id']][$i]=$res;
                        $machines[$res['id']][$i]['created_at']='';
                        $machines[$res['id']][$i]['mountpoint']='';
                        $machines[$res['id']][$i]['vol_id']='';
                        $machines[$res['id']][$i]['vmachname']='';
                        $machines[$res['id']][$i]['username']=explode('@',$machines[$res['id']][$i]['username'])[0];
                    }

                }

            }

        }

        $query=new Query;
        $date=date("Y-m-d");

        $results=$query->select(['h.project_id', 'h.id as vol_id', 'h.created_at', 'h.mountpoint', 
                'h.active', 'h.mult_order', 'v.name as vname', 'h.vm_type', 'vmach.name as vmachname'])
                          ->from('hot_volumes as h')
                          ->leftJoin('vm as v','v.id=h.vm_id')
                          ->leftJoin('vm_machines as vmach','vmach.id=h.vm_id')
                          ->where(['IN','h.project_id',$project_ids])
                          ->andWhere(['h.active'=>true])
                          ->all();

        foreach ($results as $res)
        {
            if($res['vm_type']==1)
            {
                $services[$res['project_id']]['created_at']=$res['created_at'];
                $services[$res['project_id']]['mountpoint']=$res['mountpoint'];
                $services[$res['project_id']]['vol_id']=$res['vol_id'];
                $services[$res['project_id']]['mult_order']=$res['mult_order'];
                $services[$res['project_id']]['vname']=$res['vname'];

            }
            else
            {
                $machines[$res['project_id']][$res['mult_order']]['created_at']=$res['created_at'];
                $machines[$res['project_id']][$res['mult_order']]['mountpoint']=$res['mountpoint'];
                $machines[$res['project_id']][$res['mult_order']]['vol_id']=$res['vol_id'];
                $machines[$res['project_id']][$res['mult_order']]['vmachname']=$res['vmachname'];
            }

        }

        return [$services,$machines];

    }

    public function changed($new)
    {
        $changed=false;
        if (($new->type!=$this->type) || ($new->vm_type!=$this->vm_type) || ($new->storage!=$this->storage) || ($new->num_of_volumes!=$this->num_of_volumes))
        {
            $changed=true;
        }
        return $changed;

    }

    public function getFormattedDiff($other) {
        $diff = $this->getDiff($other);

        // 3. For each resource type calculate difference and fetch from OpenStack corresponding resource
        // status
        $otherProjectNumOfVolumes = null;
        if (isset($diff['num_of_volumes'])) {
            // Store num_of_volumes as well for the other project request for the potential evaluation of a new
            // project's storage
            $otherProjectNumOfVolumes = $diff['num_of_volumes']['other'];

            $diff['num_of_volumes']['difference'] = $diff['num_of_volumes']['current'] - $diff['num_of_volumes']['other'];;
        }
        if (isset($diff['storage']) || isset($diff['num_of_volumes'])) {
            $currentProjectStorage = $diff['storage']['current'] ?? $this->storage;
            $otherProjectStorage = $diff['storage']['other'] ?? $currentProjectStorage;
            $differenceStorage = $this->num_of_volumes * $currentProjectStorage - ($otherProjectNumOfVolumes ?: $this->num_of_volumes) * $otherProjectStorage;

            $diff['storage']['difference'] = $differenceStorage;
        }
        if (isset($diff['vm_type'])) {
            $diff['vm_type']['current'] = ($diff['vm_type']['current'] == 1 ? "24/7 Service" : "On-demand computation machines");
            $diff['vm_type']['other'] = ($diff['vm_type']['other'] == 1 ? "24/7 Service" : "On-demand computation machines");
        }

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
