<?php

namespace Framework\Core\Mvc;

use \Framework\Safan;
use \Framework\Core\Globals\Get;

class Controller{
	/**
	 * Layout Page Title
	 */
	public $pageTitle = 'Safan - Simple application for all needs';
	/**
	 * Default Layout
	 */
	public $layout = 'default';
	/**
	 * Main theme
	 */
	protected $push_theme = 'push';
	/**
	 * View File path
	 */
	private $view;
	/**
	 * Vars for extract
	 */
	public $vars = array();
	/**
	 * Scripts
	 */
	private $scripts = array();
	/**
	 * Styles
	 */
	private $styles = array();
	
	
	/**
	 * Assign Vars
	 */
	public function assign($key, $value){
		$this->vars[$key] = $value;
	}
	
	/**
	 * Render Layout File
	 */
	public function render($view){
		$this->addScript();
		$this->addStyle();
		// external variables for view and layout
		if(!empty($this->vars)){
			extract($this->vars, EXTR_REFS);
		}
		$this->view = $view;
        $themeFile = BASE_PATH . DS . 'backend' . DS . 'Application' . DS . 'Themes' . DS . $this->push_theme . '.php';
        $this->loadFile($themeFile);
	}
	/**
	 * Load File
	 */
	private function loadFile($file){
		if(file_exists($file)){
			// Objects in layout & view
			$this->vars['widgetManager'] = Safan::app()->getObjectManager()->get('widget');
            $this->vars['logger'] = Safan::app()->getObjectManager()->get('logger');
			
			$T = Safan::app()->getObjectManager()->get('translate');
			
			extract($this->vars, EXTR_REFS);
			include $file;
			return;
		}
		return Safan::app()->getObjectManager()->get('dispatcher')->error($file . ' file Doesn`t exists in '. GET::exists('module') .' Controller');
	} 
	
	/**
	 * Render Layout File
	 */
	public function getLayout(){
		$layoutFile = BASE_PATH . DS . 'backend' . DS . 'Application' . DS . 'Layouts' . DS . $this->layout . '.php';
		$this->loadFile($layoutFile);
	}
	/**
	 * Set Layout
	 */
	protected function setLayout($layout=false){
		if($layout)
			$this->layout = $layout;
	}
	/**
	 * Render Layout File
	 */
	public function getContent(){
		return $this->renderPartial($this->view, false, false, true);		
	}
	
	/**
	 * Render Partial
	 */
	public function renderPartial($view, $module = false, $controller = false, $isView = false){
		// external variables for view and layout
		if(!empty($this->vars))
			extract($this->vars, EXTR_REFS);

		if(!$module)
			$module = Get::exists('module');
		if(!$controller)
			$controller = Get::exists('controller');
		$viewFile = BASE_PATH . DS . 'backend' . DS . 'Application' . DS . 'Modules' . DS . ucfirst($module) . DS . 'Resources' . DS . 'view' . DS . $controller . DS . $view . '.php';
		
		$T = Safan::app()->getObjectManager()->get('translate');
		if(file_exists($viewFile)){
			// Objects in layout & view
			$this->vars['widgetManager'] = Safan::app()->getObjectManager()->get('widget');
            $this->vars['logger'] = Safan::app()->getObjectManager()->get('logger');
            extract($this->vars, EXTR_REFS);

            if($isView){
				ob_start();
				include $viewFile;
				$outputBuffer = ob_get_clean();
				echo $outputBuffer;
				ob_end_flush();
			}
			else
				include $viewFile;
			return;
		}
		return Safan::app()->getObjectManager()->get('dispatcher')->error($view . ' file Doesn`t exists in '. Get::exists('module') .' Controller');
	}
	
	/**
	 * Render Json Content
	 */
	public function renderJson($params = array()){
		echo json_encode($params);
        exit;
	} 
	
	/**
	 * Redirect
	 */
	public function redirect($url = '', $globalUrl = false){
		if($globalUrl){
			header('location: ' . $url);
			exit;
		}
		if(!$url)
			header('location: ' . Safan::app()->baseUrl);
		else
			header('location: ' . Safan::app()->baseUrl . $url);
		exit;
	}
	
	/**
	 * Set Script 
	 * example - $this->addScript('default/main')
	 * default include file example users.script.js
	 */
	protected function addScript($scriptFile = false){
		if($scriptFile){
			$modulePath = BASE_PATH . DS . 'resource' . DS . 'js' . DS . $scriptFile . '.js';
			$scriptFile = Safan::app()->resourceUrl . DS . 'js' . DS . $scriptFile . '.js';
			if(file_exists($modulePath))
				$this->scripts[] = $scriptFile;
		} 
		else{
			$module = Get::exists('module');
			$modulePath = BASE_PATH . DS . 'resource' . DS . 'js' . DS . 'application' . DS . $module . '.script.js';
			$moduleScript = Safan::app()->resourceUrl . DS . 'js' . DS . 'application' . DS . $module . '.script.js';
			if(file_exists($modulePath))
				$this->scripts[] = $moduleScript;
		}
	}
	
	/**
	 * Set Style
	 * example - $this->addStyle('default/main')
	 * default include file example users.style.js
	 */
	protected function addStyle($styleFile = false){
		if($styleFile){
			$modulePath = BASE_PATH . DS . 'resource' . DS . 'css' . DS . $styleFile . '.css';
			$styleFile = Safan::app()->resourceUrl . DS . 'css' . DS . $styleFile . '.css';
			if(file_exists($modulePath))
				$this->styles[] = $styleFile;
		}
		else{
			$module = Get::exists('module');
			$modulePath = BASE_PATH . DS . 'resource' . DS . 'css' . DS . 'application' . DS . $module . '.style.css';
			$moduleStyle = Safan::app()->resourceUrl . DS . 'css' . DS . 'application' . DS . $module . '.style.css';
			if(file_exists($modulePath))
				$this->styles[] = $moduleStyle;
		}
	}
	
	public function __destruct(){
		
	}
	
}
