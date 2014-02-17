<?php

namespace Framework\Core\FlashMessenger;

use \Framework\Safan;

class FlashMessenger
{
	private $sessionNameSpace = 'FlashMessenger';
	private $sessionObject;
	
	public function __construct(){
		if(is_null($this->sessionObject))
			$this->sessionObject = Safan::app()->getObjectManager()->get('session');
	}
	
	public function get($key){
		$flashSessions = $this->sessionObject->get('FlashMessenger');
		if($flashSessions && isset($flashSessions[$key]))
			return $flashSessions[$key];
		return false;
	}
	
	public function set($key, $value){
		$this->sessionObject->set($this->sessionNameSpace, array($key=>$value));
	}
	
	public function remove($key){
		$this->sessionObject->remove($this->sessionNameSpace);
	}
}
