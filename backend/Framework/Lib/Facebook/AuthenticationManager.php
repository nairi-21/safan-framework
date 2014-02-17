<?php
namespace Framework\Lib\Facebook;

use Framework\Lib\Facebook\sdk\Facebook;
use Framework\Core\Globals\Get;

class AuthenticationManager{
	
	protected static $fbInstance = NULL;
	protected static $fbScope = 'email, user_birthday, read_friendlists, user_interests, user_website, user_activities';
	
	public function __construct($appID, $appSecret) {
		if (is_null(self::$fbInstance)) {
			self::$fbInstance = new Facebook(array(
					'appId' => $appID,
					'secret' => $appSecret,
			));
		}
	}
	
	public function getUserID()
	{
		$userID = self::$fbInstance->getUser();
		return $userID ? $userID : 0;
	}
	
	public function getProfile()
	{
		$profile = NULL;
	
		$userID = $this->getUserID();
		if ($userID > 0) {
			try {
				$profile = self::$fbInstance->api('/me');
			} catch(\Exception $e){
				$profile = NULL;
			}
		}
	
		return $profile;
	}
	
	public function isConnected()
	{
		$profile = $this->getProfile();
		if (is_null($profile))
			return false;
		return true;
	}
	
	public function getLoginUrl()
	{
		return self::$fbInstance->getLoginUrl(array('scope' => self::$fbScope));
	}
	
	public function getError()
	{
		$errDescription = Get::str('error_description');
		if (!empty($errDescription))
			return 'Facebook responded with error: ' . $errDescription;
		return false;
	}
}
