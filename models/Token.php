<?php

namespace app\models;


use Yii;

class Token extends \yii\db\ActiveRecord
{
    public static function ProjectRegistered($URL, $headers){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $project_exists = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        // echo $status;
        // ob_flush();
        if ($status==200){
            return 1;
        } elseif ($status==404){
            return 0;
        } else {
            return -1;
        }
        // if(strpos($project_exists, "No context exists with name ") == true){
        //     return 0;
        // } else {
        //     return 1;
        // }
        //return  $project_existst

    }

    public static function UserRegistered($URL, $headers){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $user_exists = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($status==200){
            return 1;
        } elseif ($status==404){
            return 0;
        } else {
            return -1;
        }
        // if(strpos($user_exists, "No user exists with username") == true){
        //     return 0;
        // } else {
        //     return 1;
        // }
        //return  $user_exists;

    }

    public static function Register($URL, $headers, $post_body){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $return = curl_exec($ch);
        curl_close($ch);
        // echo $return;
        // ob_flush();
        return $return;
    }

    public static function IsUserRegisteredProject($URL, $headers, $username){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $users_list = curl_exec($ch);
        curl_close($ch);
        //$users_list = '[{"username":"user0"},{"username":"test"}]';
        if(strcmp($users_list, "[]") == 0){
            return 0;
        } else {
            $users_list = trim($users_list,'[]');
            $temp1 = explode(',', $users_list);
            foreach ($temp1 as $pair) {
                $user_b = explode(':', $pair);
                //$user = str_replace(array('"','"','}'), '',$user_b[1]);
                $user = str_replace(array('"','"','}'), '',$user_b[1]);
                if (strcmp($user, $username) == 0){
                    return 1;
                }
            }
        }
    }

    public static function GetTokens($URL, $headers){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $tokens = curl_exec($ch);
        curl_close($ch);
        //echo "$tokens";
        $tokens_temp = $tokens;
        $tokens = trim($tokens,'[]');
        $strArray = explode('}',$tokens);
        $issued_tokens = count($strArray)-1;
        $tok = '';
        foreach ($strArray as $temp){
            $temp = trim($temp,',');
            $tok .= $temp;
        }
        $strArray = explode('{',$tok);
        return [$issued_tokens, $strArray];
    }


    public static function SplitTokens($token){
        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL,$URL);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // $token_details = curl_exec($ch);
        // curl_close($ch);
        $strArray_td = explode(',',$token);
			foreach ($strArray_td as $detail) {
				$detail_t = $detail;
				if (strpos($detail_t, 'title') == true){
					//echo "$piece". "<br>";
					$strArray_title = explode(':',$detail_t);
					$title = $strArray_title[1];
					$title = str_replace('"', '', $title);
					//echo "$title". "<br>";
				} elseif (strpos($detail_t, 'expiry') == true){
					$strArray_expiry = explode('T',$detail_t);
					$expiry_t = $strArray_expiry[0];
					$strArray_expiry = explode(':',$expiry_t);
					$expiry = $strArray_expiry[1];
					$expiry = str_replace('"', '', $expiry);
					//echo "$expiry". "<br>";
					$today = date("Y-m-d");
					$today_date = new \DateTime(date("Y-m-d"));
					$expiry_date = new \DateTime($expiry);
					if ($expiry > $today) {
						$interval = $expiry_date->diff($today_date);
						//echo "difference " . $interval->days . " days "."<br>";
						$exp_days = $interval->days;
						$active = "Yes";
					} else {
						$exp_days = 0;
						$active = "No";
						
					}
				} elseif (strpos($detail_t, 'uuid') == true){
					$strArray_uuid = explode(':',$detail_t);
					$uuid = $strArray_uuid[1];
					$uuid = str_replace('"', '', $uuid);
					//echo "$uuid"."<br>";
				}
			}
        return [$title, $expiry_date, $exp_days, $active, $uuid];

    }

    public static function GetTokenDetails($URL, $headers){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $token_details = curl_exec($ch);
        curl_close($ch);
        $strArray_td = explode(',',$token_details);
			foreach ($strArray_td as $detail) {
				$detail_t = $detail;
				if (strpos($detail_t, 'title') == true){
					//echo "$piece". "<br>";
					$strArray_title = explode(':',$detail_t);
					$title = $strArray_title[1];
					$title = str_replace('"', '', $title);
					//echo "$title". "<br>";
				} elseif (strpos($detail_t, 'expiry') == true){
					$strArray_expiry = explode('T',$detail_t);
					$expiry_t = $strArray_expiry[0];
					$strArray_expiry = explode(':',$expiry_t);
					$expiry = $strArray_expiry[1];
					$expiry = str_replace('"', '', $expiry);
					//echo "$expiry". "<br>";
					$today = date("Y-m-d");
					$today_date = new \DateTime(date("Y-m-d"));
					$expiry_date = new \DateTime($expiry);
					if ($expiry > $today) {
						$interval = $expiry_date->diff($today_date);
						//echo "difference " . $interval->days . " days "."<br>";
						$exp_days = $interval->days;
						$active = "Yes";
					} else {
						$exp_days = 0;
						$active = "No";
						
					}
				} elseif (strpos($detail_t, 'uuid') == true){
					$strArray_uuid = explode(':',$detail_t);
					$uuid = $strArray_uuid[1];
					$uuid = str_replace('"', '', $uuid);
					//echo "$uuid"."<br>";
				}
			}
        return [$title, $expiry_date, $exp_days, $active, $uuid];

    }

    public static function EditToken($URL, $headers, $patch){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $patch);
        $out = curl_exec($ch);
        curl_close($ch);
        return $out;

    }

    public static function CreateNewToken($URL, $headers, $pname, $exp_date, $project_name, $username){

        $schema_api_url=Yii::$app->params['schema_api_url'];
        $schema_api_token=Yii::$app->params['schema_api_token'];
        $time = date('h:i:s');
        $status = 0;
        $token=[];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if (empty($pname)){
            $post = '{"expiry":"'.$exp_date->format("Y-m-d")."T".$time.'.000000"}';
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            $token[0] = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
        } 
        else{
            //if the user provided a name then make the api call with that name 
            $temp1 = '{"title":';
            $temp2 = '"';
            $temp4 = $temp2.$pname.$temp2;
            $post = $temp1.$temp4.',"expiry":"'.$exp_date->format("Y-m-d")."T".$time.'.000000"}';
    
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            $token[0] = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        }
        curl_close($ch);
        if ($status==201){
            $strArray = explode(':',$token[0]);
            $token[0] = $strArray[11];
            $token[0] = str_replace('"', '', $token[0]);
            $token[0] = str_replace('}', '', $token[0]);
            if (empty($pname)) {
                $uuid = $strArray[1];
                $uuid = str_replace('"', '', $uuid);
                $uuid = str_replace(',title', '', $uuid);
                $URL = $schema_api_url."/api_auth/contexts/{$project_name}/users/{$username}/tokens/{$uuid}";
                $hint = $strArray[3];
                $hint = str_replace('"', '', $hint);
                $hint = str_replace(',expiry', '', $hint);
                $temp1 = '{"title":';
                $temp2 = '"';
                $temp3 = '"}';
                $patch = $temp1.$temp2.$hint.$temp3;
                $out = Token::EditToken($URL, $headers, $patch);
            }
        } else {
            $token[1]= $token[0];
            $token[0]= '';
        }
        return $token;

    }

    public static function DeleteToken($URL, $headers){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $out = curl_exec($ch);
        curl_close($ch);
        return $out;

    }

    public static function DeleteUserFromProject($URL, $headers){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $out = curl_exec($ch);
        curl_close($ch);
        return $out;

    }
}
?>