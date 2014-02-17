<?php

namespace Framework\Core\Globals;

class CookieManager
{
	public function __construct(){
		//Check Cookie Enabled
	}
	
	public function set($name, $value, $expire=0, $path='/', $domain=null, $secure=null, $httponly=false){
		if(setcookie($name, $value, $expire, $path, $domain, $secure, $httponly))
			return true;
		return false;
	}
	
	public function get($name){
		if(isset($_COOKIE[$name]))
			return $_COOKIE[$name];
		return false;
	}
	
	public function remove($name){
		if(isset($_COOKIE[$name])){ $this->set($name, 'false', time() - 1); }
	}
}
