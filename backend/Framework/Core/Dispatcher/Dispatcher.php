<?php

namespace Framework\Core\Dispatcher;

use \Framework\Safan;
use \Framework\Core\Globals\Get;
use \Framework\Core\Router\Router;
use \Framework\Core\Exceptions\ErrorRouteNotFoundException;

class Dispatcher
{
/**
	 * Codes
	 */
	const ERROR_CODE_404 = 404;
	const ERROR_CODE_500 = 500;
	/**
	 * Default Module
	 */
	private $defaulModule = 'Statics';
	/**
	 * Default Controller
	 */
	private $defaultController = 'IndexController';
	/**
	 * Default Action
	 */
	private $defaultAction = 'indexAction';
	/**
	 * Route
	 */
	private $route = array();
	
	/**
	 * Set Route
	 */
	public function setRoute($route){
		$this->route = $route;
	}
	/**
	 * Get Route
	 */
	public function getRoute(){
		return $this->route;
	}

	/**
	 * Dispatch
	 */
	public function dispatch($route = false){
		if($route){
			$module = Get::exists('module');
			$controller = Get::exists('controller');
			$action = Get::exists('action');

			if(!$module)
				return $this->dispatchToError(404, 'Module Global Variable doesn`t exists');
			if(!$controller)
				return $this->dispatchToError(404, 'Controller Global Variable doesn`t exists');
			if(!$action)
				return $this->dispatchToError(404, 'Action Global Variable doesn`t exists');
			
			$this->loadModule($module, $controller, $action); 	
		}	
		else 
			return $this->dispatchToError(404, 'Route doesn`t exists in Config RegExp');
	}

	/**
	 * Set Error Page Params
	 */
	private function getErrorPage(){
        $route = $this->getRoute();
        if(!isset($route['module']) || !isset($route['controller']) || !isset($route['action']))
            throw new ErrorRouteNotFoundException();
		Get::setParams('module', $route['module']);
		Get::setParams('controller', $route['controller']);
		Get::setParams('action', $route['action']);
		return $this->dispatch(true);
        ob_end_flush();
        exit;
	}
	
	/**
	 * Dispatch Error
	 */
	public function dispatchToError($code, $message=false){
		if(Safan::app()->getDebugMode() && $message)
			Safan::app()->_dump($message);
		return $this->getErrorPage();
	}

	/**
	 * Load Module
	 */
	public function loadModule($module, $controller, $action){
		$moduleName = ucfirst(strtolower($module));
		$modulePath = BASE_PATH . DS . 'backend' . DS .  'Application' . DS . 'Modules' . DS . $moduleName;
		$moduleController = ucfirst(strtolower($controller)) . 'Controller';
		
		if(!file_exists($modulePath . DS . 'Controller' . DS . $moduleController . '.php')){
			$route = Router::route($this->getRoute(), Safan::app()->baseUrl . DS . $module, true);
			return $this->dispatch($route);
		}
		
		include($modulePath . DS . 'Controller' . DS . $moduleController . '.php');
		$controllerClass = '\\Application\\Modules\\' . $moduleName . '\\Controller\\' . $moduleController;
		
		if(!class_exists($controllerClass))
			return $this->dispatchToError(404, $controllerClass .' Controller Class doesn`t exists');
		
		$moduleControllerObject = new $controllerClass;
		$actionMethod = strtolower($action) . 'Action';
		
		if(!method_exists($moduleControllerObject, $actionMethod))
			return $this->dispatchToError(404, $actionMethod . ' Action Method doesn`t exists in Controller Class');
		
		return $moduleControllerObject->$actionMethod();
	}
}
