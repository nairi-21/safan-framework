<?php

namespace Application\Modules\User\Controller;

use \Framework\Api;
use \Application\Models\Users;
use \Framework\Core\Globals\Post;
use \Framework\Core\Globals\Get;
use Framework\Lib\Authentication\FacebookApi;
use Framework\Lib\Facebook\Facebook;
use Framework\Lib\Facebook\AuthenticationManager;
use Framework\Core\Exceptions\EmailNotVerifiedException;
use Application\Models\UsersInfo;
use Application\Models\FileStorage;
use Application\Models\Entities;

class FacebookController extends \Framework\Core\Mvc\Controller{
	
    public function connectAction(){
        $this->pageTitle = 'Connect with Facebook';
		if (!function_exists('curl_init')) {
			echo "CURL error"; 
			exit;
		}
		
		$fb = new AuthenticationManager(UsersInfo::FB_APP_ID, UsersInfo::FB_APP_SECRET);
		$fbUserID = $fb->getUserID();
		$fbProfile = $fb->getProfile();
        if (!$fbProfile){
            $this->redirect($fb->getLoginUrl(), true);
            exit;
        }
		
		if ($fb->isConnected()) {
			$authenticationService = Api::app()->getObjectManager()->get('authentication');
            $success = $authenticationService->loginFB($fbUserID);
            //Api::app()->_dump($fbProfile);exit;
            if($success){
			    $this->redirect();
				exit;
			}
			else{
				// check for errors
				$fbErr = $fb->getError();
				if ($fbErr !== false) {
					//$flashMessenger = \App::get('FlashMessenger');
					//$flashMessenger->pushMessage($fbErr, FlashMessenger::TYPE_ERROR);
					return $this->redirect();
					exit;
				}
				$authRegisterWidgetParams = array(
						'id' => $fbProfile['id'],
						'firstName' => $fbProfile['first_name'],
						'lastName' => $fbProfile['last_name'],
						'gender' => $fbProfile['gender'],
						'birthdate' => date('d.m.Y', strtotime($fbProfile['birthday'])),
						'email' => $fbProfile['email'],
                 );
                
				if(isset($fbProfile['username'])){
                    $authRegisterWidgetParams['username'] = $fbProfile['username'];
                    $authRegisterWidgetParams['fbPic'] = 'https://graph.facebook.com/' . $fbProfile['username'] . '/picture?return_ssl_resources=1&type=large';
               	    $authRegisterWidgetParams['fbUrl'] = 'https://facebook.com/' . $fbProfile['username'];
                }
                else{             
               	    $authRegisterWidgetParams['fbUrl'] = 'https://facebook.com/profile.php?id=' . $fbProfile['id'];
                    $authRegisterWidgetParams['fbPic'] = 'https://graph.facebook.com/' . $fbProfile['id'] . '/picture?return_ssl_resources=1&type=large';
                }

				$this->assign('authRegisterWidgetParams', $authRegisterWidgetParams);
				$this->assign('fbProfile', $fbProfile);
				$this->render('register');
			}
		} 
		else
            $this->redirect($fb->getLoginUrl(), true);
        exit;
	}
	
	
	public function registerAction(){
		$toReturn = array('status' => 'ERROR', 'message' => 'Data is not correct');
		
		$data = Post::str('data');
		$data = json_decode($data);
		
		$fbOAuthID = $data->fbOAuthID;
		$email = $data->email;
		$firstName = $data->fName;
		$lastName = $data->lName;
		$isCopyProfilePic = $data->isCopyProfilePic;
		
		$usersModel = Users::model();
		$usersInfoModel = UsersInfo::model();
        $authentication = Api::app()->getObjectManager()->get('authentication');
        $fb = new AuthenticationManager(self::FB_APP_ID, self::FB_APP_SECRET);
		$fbUserID = $fb->getUserID();
		$fbProfile = $fb->getProfile();
		
		if (!$fbProfile)
			return $this->renderJson(array('status' => 'ERROR', 'message' => 'Data is not correct, Facebook profile is not exists')); 
		
		if($fbProfile['email'] != $data->email)
			return $this->renderJson(array('status' => 'ERROR', 'message' => 'Data is not correct, E-mail address not supported')); 
		
		$user = $usersModel->where(array('email' => $data->email))->limit(1)->run();
		if(!empty($user)){
			$toReturn = array('status' => 'ERROR', 'message' => 'User with this email address already exists');
			return $this->renderJson($toReturn); 
		}
		
		$creationDate = new \DateTime();
		$ip = ip2long($_SERVER['REMOTE_ADDR']);
		if(($ip != -1) && ($ip !== false))
			$creationIp = sprintf('%u', $ip);
		else
			$creationIp = 0;
		
		$userObj = $usersModel->getEmptyObject();
		$userObj->fbOAuthID = $fbProfile['id'];
		$userObj->email = $fbProfile['email'];
		$userObj->displayName = $firstName . ' ' . $lastName;
		$userObj->language = Api::app()->language;
		$userObj->enabled = Users::USERS_ENABLED;
		$userObj->creationDate = $creationDate;
		$userObj->creationIp = $creationIp;
		$userObj->modifiedDate = $creationDate;
		
		$userID = $usersModel->save($userObj);
		if(!$userID)
			return $this->renderJson($toReturn); 
		
		//ProfilePic import
		if ($isCopyProfilePic > 0) {
			$fileSystem = Api::app()->getObjectManager()->get('fileSystem');
            if(isset($fbProfile['username'])){
                $fileSystem->storeFileFromUrl(Users::USERS_FILE_PROFILE_PIC_PATH, $userID . '_profilePic.jpeg', 'https://graph.facebook.com/' . $fbProfile['username']
					. '/picture?return_ssl_resources=1&type=large');
            }
            else{
			    $fileSystem->storeFileFromUrl(Users::USERS_FILE_PROFILE_PIC_PATH, $userID . '_profilePic.jpeg', 'https://graph.facebook.com/' . $fbProfile['id']
					. '/picture?return_ssl_resources=1&type=large');
            }

			$fileStorageModel = FileStorage::model();
			$fsRecord = $fileStorageModel->getEmptyObject();
			$fsRecord->entityType = Entities::ENTITY_TYPE_USER;
			$fsRecord->entityID = $userID;
			$fsRecord->fileName = 'profilePic';
			$fsRecord->fileExt = 'jpeg';
			$fsRecord->creationDate = $creationDate;
			$fileStorageModel->save($fsRecord);
		}
		
		$usersInfoObj = $usersInfoModel->getEmptyObject();
		$usersInfoObj->userID = $userID;
		$usersInfoObj->gender = ($fbProfile['gender'] == 'male') ? UsersInfo::USERSINFO_GENDER_MALE : UsersInfo::USERSINFO_GENDER_FEMALE;
		$usersInfoObj->birthday = new \DateTime($fbProfile['birthday']);
		$usersInfoObj->fbInfo = $fbProfile;

        $usersInfoModel->save($usersInfoObj);
		//if($usersInfoModel->save($usersInfoObj)){
		$toReturn = array('status' => 'OK');
        $success = $authentication->loginFB($fbUserID);
        if($success){
    	    $toReturn = array('status' => 'OK');
            $this->connectAction();
	    }
        return $this->renderJson($toReturn);
		//}
		//return $this->renderJson($toReturn);
	}
	
	
}
