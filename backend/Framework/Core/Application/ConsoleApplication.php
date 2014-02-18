<?php
namespace Framework\Core\Application;

use \Framework\Core\Loader\SplClassLoader;
use \Framework\Core\Cli\CliManager;

class ConsoleApplication extends Application{
    
    public function runApplication(){
		$loader = new SplClassLoader('Application', 'backend');
		$loader->register();
    }

    public function processRequest(){
        $interfaceType = php_sapi_name();
        //Check interface type
        if($interfaceType != 'cli')
            return CliManager::getErrorMessage('Interface type not permitted');
        // Set Environments
        $env = $_SERVER['argv'];
        if(sizeof($env) != 2 || !strpos($env[1], ":"))
            return CliManager::getErrorMessage("Unknown Command \nview help:commands");
        return new CliManager($env[1]);
    }    
}
