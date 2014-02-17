<?php

namespace Framework\Core\Logger;

class Logger{
	
	public function __construct(){
		
    }

    public function log($logFile, $message)
    {
        if (!is_readable(dirname($logFile)))
            mkdir(dirname($logFile), 0777, true);
        $fh = fopen($logFile, 'a');
        if ($fh) {
            $date = date('Y-m-d H:i:s', time());
            $message = "\n+++++----------------------------------+++++\n$date\n$message";
            fwrite($fh, $message);
            fclose($fh);
            return true;
        }
        return false;
    }
	
	
}
