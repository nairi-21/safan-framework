<?php

namespace Framework\Core\Logger;

class SafanLogger{

    /**
     * All Logs for framework
     */
    private $logs = array();

    /**
     * Get Logs
     */
    public function getLogs($key = false){
        if($key)
            return isset($this->logs[$key]) ? $this->logs[key] : false;
		return $this->logs;
    }
    
    /**
     * Set Log
     */
    public function setLog($logKey, $logParams){
        $this->logs[$logKey] = $logParams;
    }
	
	
}
