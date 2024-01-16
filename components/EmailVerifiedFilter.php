<?php

namespace app\components;

use app\models\EmailVerificationRequest;
use webvimark\modules\UserManagement\models\User;
use Yii;
use yii\base\ActionFilter;

class EmailVerifiedFilter extends ActionFilter {

    public function beforeAction($action) {
        // 1. Get current user
        $currentUser = User::getCurrentUser();

        // 2. Check whether the user is authenticated. If the current user is a guest, then move on
        if (!$currentUser) return parent::beforeAction($action);
        // 3. Check if the user has already a pending email verification request
        $email_request=EmailVerificationRequest::find()->where(['user_id'=>$currentUser->id, 'status'=>0])->andwhere(['>', 'expiry', date('c')])->orderBy('created_at DESC')->one();
        // if the user has no valid pending email verification request && his email is not set or confirmed
        // redirect him to enter his email
        if (empty($email_request) && (trim($currentUser->email)=="" || !$currentUser->email_confirmed)) {
            return $this->owner->redirect(['personal/email-verification'])->send();
        // if the user has a valid (not expired) pending request
        // inform him that a verification email has been sent to his address
        } elseif (!empty($email_request)) {
            return $this->owner->redirect(['personal/email-verification-sent','email'=>$email_request->email, 'resend'=>0])->send();
        }
        return parent::beforeAction($action);
    }
}

?>