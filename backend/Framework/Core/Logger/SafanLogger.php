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
            return isset($this->logs[$key]) ? $this->logs[$key] : false;
		return $this->logs;
    }
    
    /**
     * Set Log
     */
    public function setLog($logKey, $logParams){
        $this->logs[$logKey] = $logParams;
    }

    /**
     * check logs
     * @return boolean
     */
    public function checkLogs(){
        if(empty($this->logs))
            return false;
        return true;
    }

    /**
     * Print Log
     */
    public function printLogs(){
        if(!$this->checkLogs())
            echo "<div class='DUMP' style='border: 2px solid #000000;
                                                background-color: green;
                                                color:#fff;position: relative;
                                                z-index: 100000;
                                                margin:5px;
                                                padding: 5px;
                                                -webkit-border-radius:5px;
                                                -moz-border-radius:5px;
                                                -o-border-radius:5px;
                                                border-radius:5px;
                                                word-wrap:break-word'>All requirements successfully installed</div>";
        else{
            foreach($this->logs as $value)
                echo "<div class='DUMP' style='border: 2px solid #cc9966;
                                                background-color: #f6efb9;
                                                color:#000000;position: relative;
                                                z-index: 100000;
                                                margin:5px;
                                                padding: 5px;
                                                -webkit-border-radius:5px;
                                                -moz-border-radius:5px;
                                                -o-border-radius:5px;
                                                border-radius:5px;
                                                word-wrap:break-word'>". $value ."</div>";
        }
    }
	
}
