<?php

namespace Application\Modules\User\Controller;

use \Framework\Api;
use \Application\Models\Users;
use \Application\Models\UsersInfo;
use \Application\Models\Entities;
use \Application\Models\FileStorage;
use \Framework\Core\Globals\Post;
use \Framework\Core\Globals\Get;
use \Framework\Lib\Facebook\AuthenticationManager;

class IndexController extends \Framework\Core\Mvc\Controller{
	
	public $pageTitle = 'MyLiveKit - Social Network | Online Marketplace | Workstation';
	
	/**
	 * Home Page
	 */
	public function indexAction(){
        $auth = Api::app()->getObjectManager()->get('authentication');
		if($auth->isGuest())
			$this->render('welcome');
        else{
            $user = Users::model()->findByPk($auth->getUserID());
            if($auth->getRoleType() == $auth::ROLE_TYPE_USER)
                $this->assign('isCompanyRole', false);
            elseif($auth->getRoleType() == $auth::ROLE_TYPE_COMPANY)
                $this->assign('isCompanyRole', true);
			$this->assign('user', $user);
			$this->render('index');
		}
	}
	
	/**
	 * Login
	 */
	public function loginAction(){
		$auth = Api::app()->getObjectManager()->get('authentication');
        $isLoginSubmited = Post::int('isLoginSubmited');
        $email = Post::str('email');
        $password = Post::str('password');
        if($isLoginSubmited){
			if(!$email || !$password){
               $errorMessage = 'Incorrect email or password'; 
               $this->assign('errorMessage', $errorMessage);
               return $this->render('login');
            }
			$login = $auth->login($email, $password);
            if($login){
                $ref = Get::str('ref');
                if($ref)
                    $this->redirect('/' . $ref);
                else
                    $this->redirect();
            }
			else
                $errorMessage = 'Incorrect email or password';
            $this->assign('errorMessage', $errorMessage);
            $this->render('login'); 
			return;
		}
		if($auth->isGuest())
			$this->render('login');
		else 
			$this->redirect();
	}
	
	/* if(Post::exists('email') && Post::exists('password')){
	 $login = $auth->login(Post::str('email'), Post::str('password'));
	if($login){
	$this->redirect();
	}
	} */
	
	/**
	 * Register
	 */
	public function registerAction(){
        $this->pageTitle = 'Register';
        $authentication = Api::app()->getObjectManager()->get('authentication');
        if(!$authentication->isGuest())
            $this->redirect();
        $isSubmited = Post::int('isSubmited');
        if($isSubmited){
            $registerFromFacebook = Post::str('registerFromFacebook');
            if($registerFromFacebook){
                $fName = Post::str('fName');
                $lName = Post::str('lName');
                $email = Post::str('email');
                $fbOAuthID = Post::str('fbOAuthID');
		        $isCopyProfilePic = Post::str('isCopyProfilePic');
		        //Instances
                $usersModel = Users::model();
		        $usersInfoModel = UsersInfo::model();
                
                $fb = new AuthenticationManager(UsersInfo::FB_APP_ID, UsersInfo::FB_APP_SECRET);
		        $fbUserID = $fb->getUserID();
		        $fbProfile = $fb->getProfile();
		
                if (!$fbProfile){
                    $errorMessage =  'Data is not correct, Facebook profile is not exists';
                    $this->assign('errorMessage', $errorMessage);
                    return $this->render('register');
                }
		
                if($fbProfile['email'] != $email){
                    $errorMessage =  'Data is not correct, E-mail address not supported';
                    $this->assign('errorMessage', $errorMessage);
                    return $this->render('register');
                }
		
		        $user = $usersModel->where(array('email' => $email))->limit(1)->run();
                if(!empty($user)){
                    $errorMessage = 'User with this email address already exists';
                    $this->assign('errorMessage', $errorMessage);
			        return $this->render('register'); 
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
		        $userObj->displayName = $fName . ' ' . $lName;
		        $userObj->language = Api::app()->language;
		        $userObj->enabled = Users::USERS_ENABLED;
		        $userObj->creationDate = $creationDate;
		        $userObj->creationIp = $creationIp;
		        $userObj->modifiedDate = $creationDate;
		
		        $userID = $usersModel->save($userObj);
                if(!$userID){
                    $errorMessage = 'System Error, Please Contact from Administration';
                    $this->assign('errorMessage', $errorMessage);
			        return $this->render('register'); 
                }
		
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
                $success = $authentication->loginFB($fbUserID);
                if($success){
                    $this->redirect();
                }
            }
            else{
                $email = Post::str('email');
                $password = Post::str('password');
                $rePassword = Post::str('rePassword');
                $gender = Post::int('gender');
                $fName = Post::str('fName');
                $lName = Post::str('lName');
                $regParams = array(
                    'email' => $email,
                    'password' => $password,
                    're-password' => $rePassword,
                    'gender' => $gender,
                    'fname' => $fName,
                    'lname' => $lName,
                );
			    $reg = $authentication->register($regParams);
			    if($reg)
			        $this->redirect('/account/prompt/' . $reg);
			    else{
                    echo "ERROR";
                    return;
                }
            }
        }
		$this->render('register');
	}
	
	/**
	 * Logout
	 */
	public function logoutAction(){
		$auth = Api::app()->getObjectManager()->get('authentication');
		$auth->logout();
        $this->redirect();
        exit;
	}
	
}
