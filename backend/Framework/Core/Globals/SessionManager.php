<?php

namespace Framework\Core\Globals;

class SessionManager
{
	private $sessionKey;
	private $sessionStarted = false;
	
	public function __construct(){
		$this->sessionKey = 'MpSessionKey_' . str_replace('.', '_', $_SERVER['HTTP_HOST']);
	}
	
	public function start(){
		if (!$this->sessionStarted) {
			$this->sessionStarted = true;
			session_start();
			// save persistent data at the end of execution
			$sessionManager = $this;
			register_shutdown_function(
			function() use($sessionManager){ 
				$sessionManager->endSession(); }
			);
		}
	}
	
	public function set($name, $value){
		if ($this->sessionStarted){
			$_SESSION[$name] = $value;
			return true;
		}
		return false;
	}
	
	public function get($name){
		if(isset($_SESSION[$name]))
			return $_SESSION[$name];
		return false;
	}
	
	public function remove($name){
		if(isset($_SESSION[$name]))
			unset($_SESSION[$name]);
	}
	
	public function endSession(){
		$this->sessionStarted = false;
		session_unset();
		session_destroy();
	}
}