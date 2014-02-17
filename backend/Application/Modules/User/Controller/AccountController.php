<?php

namespace Application\Modules\User\Controller;

use \Framework\Api;
use \Framework\Core\Globals\Get;
use \Application\Models\Users;
use \Application\Models\UsersInfo;
use \Application\Models\Positions;
use \Application\Models\UserExperiences;
use \Application\Models\Companies;
use \Application\Models\CompanyRoles;
use \Application\Models\Locations;
use \Application\Models\Entities;

class AccountController extends \Framework\Core\Mvc\Controller{

    /**
     * User Public profile
     */
	public function profileAction(){
		$auth = Api::app()->getObjectManager()->get('authentication');
		$userID = Get::str('id');
		if(!$userID)
            return Api::app()->runError(404);
        if(!$auth->isGuest() && $auth->getUserID() == $userID)
            $this->redirect('/user/edit/account');
        $userModel = Users::model();
        $usersInfoModel = UsersInfo::model();
        $locationModel = Locations::model();
        
        $user = $userModel->join('usersInfo', 
                                 'left', 
                                 'usersInfo.userID = users.id', 
                                 $usersInfoModel->getFields())
                            ->where(array('users.id' => $userID))
                            ->run();
        
        if(empty($user))
            return Api::app()->runError(404);  

        $location = $locationModel->where(array('entityID' => $userID, 'entityType' => Entities::ENTITY_TYPE_USER))->limit(1)->run();
        if(!empty($location))
            $this->assign('location', $location[0]);
        else
            $this->assign('location', null);
        $this->assign('user', $user[0]);
	    $this->render('profile');
    }
    
    /**
    * All Users
    */ 
    public function listAction(){
        $userModel = Users::model();
        $positionModel = Positions::model();
        $userExperienceModel = UserExperiences::model();
        $companyModel = Companies::model();
        
        $users = $userModel->where()->limit(10)->run();
        if(!empty($users)){
            $userIDs = array();
            foreach($users as $value)
                $userIDs[] = $value->id;
            $userExperiences = $userExperienceModel->in('userID', $userIDs)
                                                    ->where(array('isCurrentlyWork' => UserExperiences::USER_EXPERIENCE_IS_CURRENTLY_WORK_TRUE))
                                                    ->orderBy(array('fromDate' => 'DESC'))
                                                    ->run();
            if(!empty($userExperiences)){
                $experiences = $companyIDs = $positionIDs = array();
                foreach($userExperiences as $value){
                    if(!isset($experiences[$value->userID])) // For not rewrite
                        $experiences[$value->userID] = $value;
                    $companyIDs[] = $value->companyID;
                    $positionIDs[] = $value->positionID;
                }
                $companies = $companyModel->beginAllInArray('id', $companyIDs);
                $positions = $positionModel->beginAllInArray('id', $positionIDs);
                $this->assign('experiences', $experiences);
                $this->assign('companies', $companies);
                $this->assign('positions', $positions);
            }
            $profilePics = $userModel->getProfilePicsFromIds($userIDs, 180);
            $this->assign('profilePics', $profilePics);
        }
        $this->assign('users', $users);
        $this->render('list');     
    }

    /**
     * Prompt
     */
    public function promptAction(){
        $auth = Api::app()->getObjectManager()->get('authentication');
        $userID = Get::int('id');
        $user = Users::model()->findByPk($userID);
        if($userID && $auth->isGuest() && !is_null($user)){
            if(!$auth->isEmailVerified($userID)){
                $this->assign('email', $user->email);
                $this->render('confirmMessage');
            }
            else
                $this->redirect();
        }
        else
            $this->redirect();
    }
    
    /**
     * Confirm Registration
     */
    public function confirmAction(){
        $email = Get::str('email');
        $key = Get::str('key');
        
        if($email && $key){
            $users = Users::model();
            $auth = Api::app()->getObjectManager()->get('authentication');
            
            $user = $users->where(array('email' => $email, 'emailVerify' => $key))->limit(1)->run();
            if(!empty($user)){
                $userObj = $users->getEmptyObject();
                $userObj->id = $user[0]->id;
                $userObj->emailVerify = '';
                $userObj->enabled = Users::USERS_ENABLED;
                $isUpdated = $users->save($userObj);
                if($isUpdated){
                	$this->render('confirmedMessage');
                }
                else 
                    $this->redirect();
            }
            else
                $this->redirect();
        }
        else
            $this->redirect();
    }

  
    /**
     * CheckUsername
     */
    public function checkusernameAction(){
    	
    }
    
    /**
     * User Settings
     */
    public function settingsAction(){
        $this->pageTitle = 'Account Settings';
    	$auth = Api::app()->getObjectManager()->get('authentication');
    	if($auth->isGuest())
    		$this->redirect();
    	$user = Users::model()->findByPK($auth->getUserID());
    	$this->assign('user', $user);
    	$this->render('settings');
    }

    /**
    ** Change Role
    **/
    public function changeroleAction(){
        $entityType = Get::int('entityType');
        $entityID = Get::int('entityID');
        
        $auth = Api::app()->getObjectManager()->get('authentication');
        $currentUserID = $auth->getUserID();

        if($auth->isGuest() || !$entityID || !$entityType)
            $this->redirect();

        if($entityType == $auth::ROLE_TYPE_COMPANY){
            $companyRoles = CompanyRoles::model();
            $roleExists = $companyRoles->where(array('companyID' => $entityID, 'userID' => $currentUserID))->limit(1)->run();
            if(!is_null($roleExists))
                $changes = $auth->setRole($entityType, $entityID);
        }
        elseif(($entityType == $auth::ROLE_TYPE_USER) && $entityID == $currentUserID)
            $changes = $auth->setRole($entityType, $entityID);
        $this->redirect();
        //header('Location: ' . $_SERVER['HTTP_REFERER']);
    }


}







