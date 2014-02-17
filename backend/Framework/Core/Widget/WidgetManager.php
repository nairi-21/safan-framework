<?php

namespace Framework\Core\Widget;

use \Framework\Safan;
use Framework\Core\Globals\Get;

class WidgetManager
{
	private static $instance = array();
	private static $widgetName = '';
	public $vars = array();
	public $params = array();
	/**
	 * Scripts
	 */
	private $scripts = array();
	/**
	 * Styles
	*/
	private $styles = array();
	
	
	public function begin($widgetName, $params = array()){
		if(!empty($params))
			$this->params = $params;
		$this->loadWidget($widgetName);
	}
	
	private function loadWidget($widgetName){
		self::$widgetName = $widgetName;
		$widget = BASE_PATH . DS . 'backend' . DS . 'Application' . DS . 'Widgets' . DS . $widgetName . DS . 'Widget.php';
		if(!file_exists($widget))
			throw new \Framework\Core\Exceptions\FileNotFoundException($widgetName . ' File doesn`t exists');
		
		if(isset(self::$instance[$widgetName])){
			$widgetObject = self::$instance[$widgetName];
			return $widgetObject->run($this->params);
		}
		
		include $widget;
		
		$widgetClass = '\\Application\\Widgets\\' . $widgetName . '\\Widget';
		if(!class_exists($widgetClass))
			throw new \Framework\Core\Exceptions\ObjectDoesntExistsException($widgetName . ' Class doesn`t exists');
		
		self::$instance[$widgetName] = $widgetObject = new $widgetClass;
		
		if(!method_exists($widgetObject, 'run'))
			throw new \Framework\Core\Exceptions\ObjectDoesntExistsException($widgetName . ' Run Method doesn`t exists');
		return $widgetObject->run($this->params);
	}
	
	public function end(){
		$this->params = array();
	}
	
	/**
	 * Assign Vars
	 */
	public function assign($key, $value){
		$this->vars[$key] = $value;
	}
	
	/**
	 * Render view
	 */
	public function render($view){
		$this->addScript();
		$this->addStyle();
		// external variables for view and layout
		if(!empty($this->vars)){
			extract($this->vars, EXTR_REFS);
			$this->vars = array();
		}
		$viewFile = BASE_PATH . DS . 'backend' . DS . 'Application' . DS . 'Widgets' . DS . self::$widgetName . DS . 'Helpers' . DS . $view . '.php';
		
		$T = Safan::app()->getObjectManager()->get('translate');
		
		if(file_exists($viewFile)){
			//Set Scripts
			foreach($this->scripts as $value)
				echo '<script>Safan.setScript("' . $value . '")</script>';
			//Set Styles
			foreach($this->styles as $value)
				echo '<script>Safan.setStylesheet("' . $value . '")</script>';
			include $viewFile;
			return;
		}
		return Safan::app()->getObjectManager()->get('dispatcher')->error($view . ' file Doesn`t exists in '. self::$widgetName .' Widget');
	}
	/**
	 * Set Script
	 * example - $this->addScript('default/main')
	 * default include file example w.users.script.js
	 */
	protected function addScript($scriptFile = false){
		if($scriptFile){
			$modulePath = BASE_PATH . DS . 'resource' . DS . 'js' . DS . $scriptFile . '.js';
			$scriptFile = Safan::app()->resourceUrl . DS . 'js' . DS . $scriptFile . '.js';
			if(file_exists($modulePath))
				$this->scripts[] = $scriptFile;
		}
		else{
			$modulePath = BASE_PATH . DS . 'resource' . DS . 'js' . DS . 'application' . DS . 'w.' . strtolower(self::$widgetName) . '.script.js';
			$moduleScript = Safan::app()->resourceUrl . DS . 'js' . DS . 'application' . DS . 'w.' . strtolower(self::$widgetName) . '.script.js';
			if(file_exists($modulePath))
				$this->scripts[] = $moduleScript;
		}
	}
	
	/**
	 * Set Style
	 * example - $this->addStyle('default/main')
	 * default include file example w.users.style.js
	 */
	protected function addStyle($styleFile = false){
		if($styleFile){
			$modulePath = BASE_PATH . DS . 'resource' . DS . 'css' . DS . $styleFile . '.css';
			$styleFile = Safan::app()->resourceUrl . DS . 'css' . DS . $styleFile . '.css';
			if(file_exists($modulePath))
				$this->styles[] = $styleFile;
		}
		else{
			$modulePath = BASE_PATH . DS . 'resource' . DS . 'css' . DS . 'application' . DS . 'w.' . strtolower(self::$widgetName) . '.style.css';
			$moduleStyle = Safan::app()->resourceUrl . DS . 'css' . DS . 'application' . DS . 'w.' . strtolower(self::$widgetName) . '.style.css';
			if(file_exists($modulePath))
				$this->styles[] = $moduleStyle;
		}
	}
}
