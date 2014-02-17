<?php

namespace Framework\Lib\Authentication;

use \Framework\Safan;
use \Framework\Lib\Mail\PHPMailer\PHPMailer;
use \Application\Models\Users;
use \Application\Models\UsersInfo;
use Framework\Core\Exceptions\EmailNotVerifiedException;

// User Memcache example -  m_user_5('auth'=>array('id'=>5, 'xs'=>'dsan545dsa454ds2h@!fkdsnnkfdsn'));
// User Cookie example -  m_user = 5, xs = 'dsan545dsa454ds2h@!fkdsnnkfdsn'
class AuthenticationManager{
	
	const MEMCACHE_CODE_TIMEOUT = 50400; // 2 * 24 * 60 * 60
	const COOKIE_REMEMBER_DATE = 31536000; // 365 * 24 * 60 * 60
	const CODE_PREFIX = 'xs'; 
	const ID_PREFIX = 'm_user';
	const ROLE_TYPE_PREFIX = 'r_type'; // For Role Type
	const ROLE_ID_PREFIX = 'r_id'; // For Role Company ID
	const MEMCACHE_AUTH_CONTAINER = 'auth';
	const FOLDER_USERS = 'users';
	
	const ROLE_TYPE_USER = 1;
	const ROLE_TYPE_COMPANY = 2;
	
	private $flashMessengerObject;
	private $cookieObject;
	private $memcacheObject;
	private $fileSystemObject;
	private $salt = '/saCo`oWw$Oe#W!0Tp$rEl$`^di#w|S[Kv8D{gfQ%X2.L-p<R]=OJ{lZX,.|neN_';
	private $db_table = 'users';
	private $db_info_table = 'usersInfo';

	private $roleType = 0;
	private $roleID = 0;
	private $id=0;	
    private $currentUser; //coming soon

	public function __construct(){
		$this->flashMessenger = Safan::app()->getObjectManager()->get('flashMessenger');
		$this->cookieObject = Safan::app()->getObjectManager()->get('cookie');
		$this->memcacheObject = Safan::app()->getObjectManager()->get('memcache');
		$this->fileSystemObject = Safan::app()->getObjectManager()->get('fileSystem');
	}
	
	/**
	 * @return boolean
	 */
	public function isGuest(){
		return $this->id < 1;
	}
	/**
	 * @return user cache
	 * 
	 */
	public function getUserCache($userID=false){
		if($this->id < 1)
			return false;
		if($userID)
			return $this->memcacheObject->GetCache(self::ID_PREFIX . '_' . $userID);
		return $this->memcacheObject->GetCache(self::ID_PREFIX . '_' . $this->id);
	}
	/**
	 * @return boolean
	 */
	public function checkStatus(){
		$userCookieID = $this->cookieObject->get(self::ID_PREFIX);
		$userCookieCode = $this->cookieObject->get(self::CODE_PREFIX);
		$userCookieRoleType = $this->cookieObject->get(self::ROLE_TYPE_PREFIX);
		$userCookieRoleID = $this->cookieObject->get(self::ROLE_ID_PREFIX);
		if(!$userCookieID || !$userCookieCode || !$userCookieRoleType || !$userCookieRoleID){
			$this->roleID = 0;
			$this->roleType = 0;
			$this->id = 0;
			return;
		}
		$user_memcache = $this->checkMemcacheFromCookie($userCookieID, $userCookieCode);
		if($user_memcache){
			$this->roleID = $userCookieRoleID;
			$this->roleType = $userCookieRoleType;
			$this->id = $userCookieID;
			return;
		}
		$this->roleID = 0;
		$this->roleType = 0;
		$this->id = 0;
	}
	
	private function checkMemcacheFromCookie($id, $code){
		$memcacheData = $this->memcacheObject->GetCache(self::ID_PREFIX . '_' . $id);
		if(!$memcacheData){
			if($this->loginFromCookie($id, $code))
				return true;
			else 
				return false;
		}
		elseif($memcacheData[self::MEMCACHE_AUTH_CONTAINER][self::CODE_PREFIX] == $code){
			return true;
		}
		return false;
	}
		
	/**
	 * Login From Cookie Data
	 */
	private function loginFromCookie($id, $code){
		$code = sha1(md5($this->salt) . $code);
		$usersModel = Users::model();
		$userInfo = $usersModel->where(array('id'=>$id, 'hash'=>$code))->limit(1)->run();
		if(!empty($userInfo)){
			$id = $userInfo[0]->id;
			$lastLoginDate = new \DateTime();;
			//IP
			$ip = ip2long($_SERVER['REMOTE_ADDR']);
			if(($ip != -1) && ($ip !== false))
				$lastLoginIp = sprintf('%u', $ip);
			else 
				$lastLoginIp = 0;
			$dbHash = sha1(md5($this->salt) . sha1($userInfo[0]->email . $this->salt . $lastLoginDate->format('Y-m-d H:i:s')));
			$cookieHash = sha1($userInfo[0]->email . $this->salt . $lastLoginDate->format('Y-m-d H:i:s'));
			$usersObj = Users::model()->getEmptyObject();
			$usersObj->id = $id;
			$usersObj->hash = $dbHash;
			$usersObj->hashDate = $lastLoginDate;
			$usersObj->lastLoginDate = $lastLoginDate;
			$usersObj->lastLoginIp = $lastLoginIp;
			/* $usersObj->attributes = array(
					'id' => $id,
					'hash' =>$dbHash,
					'hashDate' =>$lastLoginDate,
					'lastLoginDate' =>$lastLoginDate,
					'lastLoginIp' => $lastLoginIp,
			); */
			$setDb = $usersModel->save($usersObj);
			if($setDb){
				$memcacheData = array(self::MEMCACHE_AUTH_CONTAINER => array('id' => $id, self::CODE_PREFIX => $cookieHash, self::ROLE_TYPE_PREFIX => self::ROLE_TYPE_USER, self::ROLE_ID_PREFIX => $id ));
				$setMemcacheData = $this->memcacheObject->SetCache(self::ID_PREFIX . '_' . $id, $memcacheData, self::MEMCACHE_CODE_TIMEOUT);
				$setCookieID = $this->cookieObject->set(self::ID_PREFIX, $id);
				$setCookieCode = $this->cookieObject->set(self::CODE_PREFIX, $cookieHash);
				$setCookieRoleID = $this->cookieObject->set(self::ROLE_ID_PREFIX, $id);
				$setCookieRoleType = $this->cookieObject->set(self::ROLE_TYPE_PREFIX, self::ROLE_TYPE_USER);
				if($setMemcacheData && $setCookieID && $setCookieCode && $setCookieRoleID && $setCookieRoleType){
					$this->roleType = self::ROLE_TYPE_USER;
					$this->roleID = $id;
					$this->id = $id;
					//$this->flashMessenger->remove('auth');
					return true;
				}
				//$this->flashMessenger->set('auth', 'Cookies are not enabled on your browser. Please adjust this in your security preferences before continuing.');
				return false;
			}
			return false;
		}
		return false;
	} 
	
	
	/**
	 * Check Authentication status, logined or no
	 */
	public function login($email, $password, $remember=false){
		$email = trim(strip_tags($email));
		$password = trim(strip_tags($password));
		$password = md5(md5($password) . $this->salt);
		$usersModel = Users::model();
		$userInfo = $usersModel->where(array('email'=>$email, 'password'=>$password))->limit(1)->run();
		if(!empty($userInfo)){
			if($remember)
				$cookieDate = time() + self::COOKIE_REMEMBER_DATE;
			else 
				$cookieDate = time() + self::MEMCACHE_CODE_TIMEOUT;
			$id = $userInfo[0]->id;
			$lastLoginDate = new \DateTime();
			//IP
			$ip = ip2long($_SERVER['REMOTE_ADDR']);
			if(($ip != -1) && ($ip !== false))
				$lastLoginIp = sprintf('%u', $ip);
			else 
				$lastLoginIp = 0;
			$dbHash = sha1(md5($this->salt) . sha1($email . $this->salt . $lastLoginDate->format('Y-m-d H:i:s')));
			$cookieHash = sha1($email . $this->salt . $lastLoginDate->format('Y-m-d H:i:s'));
			$usersObj = $usersModel->getEmptyObject();
			$usersObj->id = $id;
			$usersObj->hash = $dbHash;
			$usersObj->hashDate = $lastLoginDate;
			$usersObj->lastLoginDate = $lastLoginDate;
			$usersObj->lastLoginIp = $lastLoginIp;
			/* $usersObj->attributes = array(
				'id' => $id,
				'hash' => $dbHash,
				'hashDate' => $lastLoginDate,
				'lastLoginDate' => $lastLoginDate,	
				'lastLoginIp' => $lastLoginIp,
			); */
			$setDb = $usersModel->save($usersObj);
			if($setDb){
				$memcacheData = array(self::MEMCACHE_AUTH_CONTAINER=>array('id' => $id, self::CODE_PREFIX => $cookieHash, self::ROLE_ID_PREFIX => $id, self::ROLE_TYPE_PREFIX => self::ROLE_TYPE_USER));
				$setMemcacheData = $this->memcacheObject->SetCache(self::ID_PREFIX . '_' . $id, $memcacheData, self::MEMCACHE_CODE_TIMEOUT);
				$setCookieID = $this->cookieObject->set(self::ID_PREFIX, $id, $cookieDate, '/', null, null, true);
				$setCookieCode = $this->cookieObject->set(self::CODE_PREFIX, $cookieHash, $cookieDate, '/', null, null, true);
				$setCookieRoleID = $this->cookieObject->set(self::ROLE_ID_PREFIX, $id, $cookieDate, '/', null, null, true);
				$setCookieRoleType = $this->cookieObject->set(self::ROLE_TYPE_PREFIX, self::ROLE_TYPE_USER, $cookieDate, '/', null, null, true);
				if($setMemcacheData && $setCookieID && $setCookieCode && $setCookieRoleID && $setCookieRoleType){
					$this->roleType = self::ROLE_TYPE_USER;
					$this->roleID = $id;
					$this->id = $id;
					//$this->flashMessenger->remove('auth');
					return true;
				}
				//$this->flashMessenger->set('auth', 'Cookies are not enabled on your browser. Please adjust this in your security preferences before continuing.');
				return false;
			}
			//$this->flashMessenger->set('auth', 'Ooops!!! Server error, please Contact to Administrator');
			return false;
		}
		//$this->flashMessenger->set('auth', 'Username or Password is not valid');
		return false;
	}
	/**
	 * Login From Facebook
	 */
	public function loginFB($fbOAuthID)
	{
		$usersModel = new Users;
		$user = $usersModel->where(array('fbOAuthID' => $fbOAuthID))->limit(1)->run();
		//var_dump($user);
		if (empty($user))
			return false;
		//Generate record
		$lastLoginDate = new \DateTime();
		//IP
		$ip = ip2long($_SERVER['REMOTE_ADDR']);
		if(($ip != -1) && ($ip !== false))
			$lastLoginIp = sprintf('%u', $ip);
		else
			$lastLoginIp = 0;
		$dbHash = sha1(md5($this->salt) . sha1($user[0]->email . $this->salt . $lastLoginDate->format('Y-m-d H:i:s')));
		$cookieHash = sha1($user[0]->email . $this->salt . $lastLoginDate->format('Y-m-d H:i:s'));
		$cookieDate = time() + self::MEMCACHE_CODE_TIMEOUT;
		$usersObj = $usersModel->getEmptyObject();
		$usersObj->id = $user[0]->id;
		$usersObj->hash = $dbHash;
		$usersObj->hashDate = $lastLoginDate;
		$usersObj->lastLoginDate = $lastLoginDate;
		$usersObj->lastLoginIp = $lastLoginIp;
		
		$memcacheData = array(self::MEMCACHE_AUTH_CONTAINER=>array('id' => $user[0]->id, 
																	self::CODE_PREFIX => $cookieHash, 
																	self::ROLE_ID_PREFIX => $user[0]->id, 
																	self::ROLE_TYPE_PREFIX => self::ROLE_TYPE_USER));
		$setMemcacheData = $this->memcacheObject->SetCache(self::ID_PREFIX . '_' . $user[0]->id, $memcacheData, self::MEMCACHE_CODE_TIMEOUT);
		$setCookieID = $this->cookieObject->set(self::ID_PREFIX, $user[0]->id, $cookieDate, '/', null, null, true);
		$setCookieCode = $this->cookieObject->set(self::CODE_PREFIX, $cookieHash, $cookieDate, '/', null, null, true);
		$setCookieRoleID = $this->cookieObject->set(self::ROLE_ID_PREFIX, $user[0]->id, $cookieDate, '/', null, null, true);
		$setCookieRoleType = $this->cookieObject->set(self::ROLE_TYPE_PREFIX, self::ROLE_TYPE_USER, $cookieDate, '/', null, null, true);
		if($setMemcacheData && $setCookieID && $setCookieCode && $setCookieRoleID && $setCookieRoleType){
			$this->roleType = self::ROLE_TYPE_USER;
			$this->roleID = $user[0]->id;
			$this->id = $user[0]->id;
			return true;
		}
		return false;
	}
	
	/**
	 * Check Authentication status, logined or no
	 */
	public function logout(){
		$cookie = Safan::app()->getObjectManager()->get('cookie');
		$memcache = Safan::app()->getObjectManager()->get('memcache');
		
		$id = $cookie->get(self::ID_PREFIX);
		
		$cookie->remove(self::ID_PREFIX);
		$cookie->remove(self::CODE_PREFIX);
		$cookie->remove(self::ROLE_ID_PREFIX);
		$cookie->remove(self::ROLE_TYPE_PREFIX);
		$data = $memcache->GetCache(self::ID_PREFIX . '_' . $id);
		if($data){
			$memcache->DeleteCache(self::ID_PREFIX . '_' . $id);
			return true;
		}
		return false;
	}
	/**
	 * Account Status type in database
	 */
	private function checkAccountStatus(){
		
	}
	
	/**
	 * @return integer
	 */
	public function getUserID(){
		return (int)$this->id;
	}
		
	/**
	 * Switch Role
	 */
	public function setRole($roleType, $roleID){
		$memcacheData = $this->memcacheObject->GetCache(self::ID_PREFIX . '_' . $this->id);
		$memcacheData[self::MEMCACHE_AUTH_CONTAINER][self::ROLE_ID_PREFIX] = $roleID;
		$memcacheData[self::MEMCACHE_AUTH_CONTAINER][self::ROLE_TYPE_PREFIX] = $roleType;
		$setMemcacheData = $this->memcacheObject->SetCache(self::ID_PREFIX . '_' . $this->id, $memcacheData, self::MEMCACHE_CODE_TIMEOUT);
		$cookieDate = time() + self::MEMCACHE_CODE_TIMEOUT;
		$setCookieRoleID = $this->cookieObject->set(self::ROLE_ID_PREFIX, $roleID, $cookieDate, '/', null, null, true);
		$setCookieRoleType = $this->cookieObject->set(self::ROLE_TYPE_PREFIX, $roleType, $cookieDate, '/', null, null, true);
		if($setMemcacheData && $setCookieRoleID && $setCookieRoleType)
			return true;
		return false;
	}
	
	/**
	 * @return Role ID
	 */
	public function getRoleID(){
		return $this->roleID;
	}
	
	/**
	 * @return Role type
	 */
	public function getRoleType(){
		return $this->roleType;
	}
	
	/**
	 * Registration 
	 * @var data is array
	 * 		- First Name
	 * 		- Last Name
	 * 		- Gender
	 * 		- E-mail
	 * 		- Password
	 * 		- Re-password	
	 * 
	 */
	public function register($data){
		$cookie = \Framework\Safan::app()->getObjectManager()->get('cookie');
		$language = ($cookie->get('m_language')) ? $cookie->get('m_language') : \Framework\Safan::app()->language ;
				
		$fname = isset($data['fname']) ? strip_tags($data['fname']) : false;
		$lname = isset($data['lname']) ? strip_tags($data['lname']) : false;
		$gender = isset($data['gender']) ? (int)$data['gender'] : false;
		$email = isset($data['email']) ? trim(strip_tags($data['email'])) : false;
		$password = isset($data['password']) ? trim(strip_tags($data['password'])) : false;
		$rePassword = isset($data['re-password']) ? trim(strip_tags($data['re-password'])) : false;
		
		if($fname && $lname && $gender && $email && $password && $rePassword){
			if($this->checkEmailforUnique($email)){
			    $emailVerify = md5(crypt(rand(90000, 1000000)));
				$password = md5(md5($password) . $this->salt);
				$creationDate = new \DateTime();
				$dbHash = sha1(md5($this->salt) . sha1($email . $this->salt . $creationDate->format('Y-m-d H:i:s')));
				$cookieHash = sha1($email . $this->salt . $creationDate->format('Y-m-d H:i:s'));
				$ip = ip2long($_SERVER['REMOTE_ADDR']);
				if(($ip != -1) && ($ip !== false))
					$creationIp = sprintf('%u', $ip);
				else 
					$creationIp = 0;
				//Conf info
				$usersModel = Users::model();
				$usersObj = $usersModel->getEmptyObject();
				$usersObj->email = $email;
				$usersObj->emailVerify = $emailVerify;
				$usersObj->displayName = $fname . ' ' . $lname;
				$usersObj->password = $password;
				$usersObj->hash = $dbHash;
				$usersObj->hashDate = $creationDate;
				$usersObj->language = $language;
				$usersObj->creationDate = $creationDate;
				$usersObj->creationIp = $creationIp;
                $setDb = $usersModel->save($usersObj);
				if($setDb){
                    $usersInfoModel = UsersInfo::model();
                    $usersInfoObj = $usersInfoModel->getEmptyObject();
                    $usersInfoObj->userID = $setDb;
                    $usersInfoObj->gender = $gender;
                    $usersInfoModel->save($usersInfoObj);
					//Mail Send
					$mailer = new PHPMailer();
					$mailer->From = "info@mylivekit.com";
					$mailer->FromName = "MyLiveKit - Collectors Social Network";
					$mailer->Subject = "Confirm Email Address";
					$mailer->IsHTML(true);
					$mailer->Body = '<p>Dear '. $fname . ' ' . $lname .'<p>
					<p>Thank you for registering with MyLiveKit.com<p>
					<p>Please click on this link to confirm the registration:<br/>
					http://www.myLivekit.com/account/confirm/'. $email .'/' . $emailVerify . ' <p>
					<p></p><p></p>
					Sincerely,<br/>
					Support Team at MyLiveKit.com<br/>
					www.myLivekit.com<br/>
					admin@myLivekit.com';
					$mailer->AddAddress($email, $fname . ' ' . $lname);
					if(!$mailer->Send()){
    					//$this->error['message'] = "!There has been a mail error". $mailer->ErrorInfo();
    					return false;
					}
					else 
					    return $setDb;
				}
			}
        }
		return false;
	}
	
	/**
	 * Check User Activation
	 */
	public function isEmailVerified($id){
		$usersObj = Users::model();
		$result = $usersObj->where(array('id'=>$id, 'emailVerify'=>''))->limit(1)->run();
	    if(empty($result)){
	        return false;
	    }
	    return true;
	}
	
	/**
	 * @return true or false
	 */
	public function checkEmail($mail){
		$mail=trim($mail);
		if (strlen($mail)==0) return false;
		if (!preg_match("/^[a-z0-9_-]{1,20}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/is",$mail))
			return false;
		return true;
	}
	
	
	/**
	 * @return true or false
	 */
	private function checkEmailforUnique($mail){
		$usersObj = Users::model();
		$result = $usersObj->where(array('email'=>$mail))->limit(1)->run();
		if(empty($result))
			return true;
		return false;
	}
	
	/**
	 * Create Folder for User 
	 */
	public function createFolderForUser($pathName){
		$fileSystem = $this->fileSystemObject;
		$isLimited = $fileSystem->checkFoldersCountFromCreated(self::FOLDER_USERS . DS . $pathName); 
		
		if($isLimited){
			$isCreated = $fileSystem->createFolder(self::FOLDER_USERS . DS . $pathName);
			return $pathName;
		}
		else{
			$i = 0;
			$longPath = '0';
			while($i == 0){
				if($fileSystem->dirExists(self::FOLDER_USERS . DS . $longPath)){
					$isLimited = $fileSystem->checkFoldersCountFromCreated(self::FOLDER_USERS . DS . $longPath . DS . $pathName);
					if($isLimited){
						$isCreated = $fileSystem->createFolder(self::FOLDER_USERS . DS . $longPath . DS . $pathName);
						$i++;
					}
					else{
						$longPath .= '/0';
					}
				}
				else{
					$isCreated = $fileSystem->createFolder(self::FOLDER_USERS . DS . $longPath);
					if($isCreated){
						$isCreated = $fileSystem->createFolder(self::FOLDER_USERS . DS . $longPath . DS . $pathName);
						return '0' . DS . $pathName;
					}
					$i++;
				}
			}
			return $longPath . DS . $pathName;
		}
	}
}
