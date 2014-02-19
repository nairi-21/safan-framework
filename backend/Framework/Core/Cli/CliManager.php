<?php

namespace Framework\Core\Cli;

use \Framework\Safan;
use \Framework\Core\Exceptions\FileNotException;

class CliManager
{
    /**
     * string (example database:create)
     */
    private $command;

    public function __construct($command){
        $this->command = $command;
        return $this->dispatchCommand();
    }

    /**
     * @var $command is array
     */
    private function dispatchCommand(){
        $route = $this->route();

        $modulePath = BASE_PATH . DS . 'backend' . DS . 'Framework' . DS . 'Core' . DS . 'Cli' . DS . 'Modules' . DS .  ucfirst($route['module']);
        $moduleController = $modulePath . DS . 'Controller' . DS . ucfirst($route['controller']) . 'Controller.php';
        if(!file_exists($moduleController))
            return $this->getErrorMessage($route['module'] . 'Module controller doesn`t exists in CLI');

        include_once($moduleController);
        $controllerClass = '\\Framework\\Core\\Cli\\Modules\\' . ucfirst($route['module']) . '\\Controller\\' . ucfirst($route['controller']) . 'Controller';

        if(!class_exists($controllerClass))
	        return $this->getErrorMessage($route['controller'] .' Controller Class doesn`t exists in CLI module');
    
        $moduleControllerObject = new $controllerClass;
        $actionMethod = strtolower($route['action']) . 'Action';
	    	
		if(!method_exists($moduleControllerObject, $actionMethod))
			return $this->getErrorMessage($actionMethod . ' Action Method doesn`t exists in CLI Controller');

        return $moduleControllerObject->$actionMethod();
    }
    
    
    /**
     * Search command route in route.config.php file
     * @return array
     */
    private function route(){
        $routeFile = BASE_PATH . DS . 'backend' . DS . 'Framework' . DS . 'Core' . DS . 'Cli' . DS . 'Settings' . DS . 'route.config.php';
        if(!file_exists($routeFile))
            throw new FileNotException('Route config file is not exists');
        $route = require($routeFile);
        foreach($route as $rule => $settings){
		    $matches = $params = array();
		    if (preg_match($rule, $this->command, $matches)) {
			    $params['module'] = $settings['module'];
			    $params['controller'] = $settings['controller'];
                $params['action'] = $settings['action'];

			    foreach ($settings['matches'] as $key => $varName) {
				    if (empty($varName))
				    	continue;
				    if (isset($matches[$key]))
					    $params[$varName] = $matches[$key];
			    }
		    }
        }
        if(empty($params))
            return self::getErrorMessage('Command Routing not found');
        return $params;
    }
    /**
     * Get Message
     * @color green
     */
    private function getMessage($message){
        echo '\e[00;34m' . $message . '\e[0m \n\r';
    }

    /**
     * Get Error
     * @color red
     */
    public static function getErrorMessage($message){
        echo '\033[' . $message . '\033[0m' . PHP_EOL;
        exit;
    }
}
