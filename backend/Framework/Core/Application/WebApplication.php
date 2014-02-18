<?php
namespace Framework\Core\Application;

use \Framework\Core\Exceptions\FileNotFoundException;
use \Framework\Core\Dispatcher\Dispatcher;
use \Framework\Core\ObjectManager\ObjectManager;
use \Framework\Core\Router\Router;
use \Framework\Core\Widget\WidgetManager;
use \Framework\Core\FlashMessenger\FlashMessenger;
use \Framework\Core\Globals\CookieManager;
use \Framework\Core\Globals\SessionManager;
use \Framework\Core\LanguageManager\LanguageManager;
use \Framework\Lib\Memcache\DBCache;
use \Framework\Lib\Authentication\AuthenticationManager;
use \Framework\Core\FileSystem\FileSystem;
use \Framework\Core\Loader\SplClassLoader;
use \Framework\Core\Logger\SafanLogger;

class WebApplication extends Application{
	/**
	 * Debug Mode
	 */
	private static $debug = false;
	/**
	 * Object Manager
	 */
	private static $objectManager;

	/**
	 * Base Url
	 */
	public $baseUrl;

	/**
	 * Media files Url
	 */
	public $resourceUrl;

	/**
	 * Current Language
	 */
	public $language;

	/**
	 * Run All Applications
	 */
	public function runApplication(){
        // register application namespace
		$loader = new SplClassLoader('Application', 'backend');
		$loader->register();
		// Main config
		$configFile = BASE_PATH . DS . 'backend' . DS . 'Application' . DS . 'Settings' . DS . 'main.config.php';
		if(file_exists($configFile))
			$config = include($configFile);
		else 
			throw new FileNotFoundException('Main Config File doesn`t exists'); 
		// Site Debug mode
		if(isset($config['debug']) && $config['debug'] === true)
			$this->setDebugMode(true);
		else
			$this->setDebugMode();
		// Db connection
		$dbConfigFile = BASE_PATH . DS . 'backend' . DS . 'Application' . DS . 'Settings' . DS . 'db.config.php';
		if(file_exists($dbConfigFile))
			$dbConfigFile = include($dbConfigFile);
		else
			throw new FileNotFoundException('Database Config File doesn`t exists');
		if(isset($dbConfigFile['connections'])){
            foreach($dbConfigFile['connections'] as $value){
                if($value['start'] === true && $value['type'] === 'pdo')
                    \Framework\Core\DatabaseDrivers\PDO\Driver::getInstance()->setup($value['config']);
            }
        }
		// Object manager
		self::$objectManager = $om = new ObjectManager();
		// Safan logger object
		$logger = new SafanLogger();
		$om->setObject('logger', $logger);
		// Dispatcher object
		$dispatcher = new Dispatcher();
		$om->setObject('dispatcher', $dispatcher);
		// Session Object
		$session = new SessionManager();
		$om->setObject('session', $session);
        if(isset($config['session_start']) && $config['session_start'] === true)
		    $session->start();
		// FileSystem Object
        if(!class_exists('Imagick'))
            $om->get('logger')->setLog('imagick', 'Imagick class not found');
		$fileSystem = new FileSystem();
		$om->setObject('fileSystem', $fileSystem);
		// FlashMessenger Object
		$flashMessenger = new FlashMessenger();
		$om->setObject('flashMessenger', $flashMessenger);
		// WidgetManager Object
		$widgetManager = new WidgetManager();
		$om->setObject('widget', $widgetManager);
		// Cookie Object
		$cookie = new CookieManager();
		$om->setObject('cookie', $cookie);
        // Memcache & Authentication Object
        if(!class_exists('Memcache')){
            $om->get('logger')->setLog('memcache', 'Memcache class is not found');
            $om->get('logger')->setLog('authentication', 'Authentication not running, please install Memcache class');
        }
        else{
		    $memcache = new DBCache();
		    $om->setObject('memcache', $memcache);
				/***** Authentication Object ******/
		    $auth = new AuthenticationManager();
		    $auth->checkStatus();
		    $om->setObject('authentication', $auth);
        }
		// Router
		$routeConfigFile = BASE_PATH . DS . 'backend' . DS . 'Application' . DS . 'Settings' . DS . 'route.config.php';
		if(file_exists($routeConfigFile))
			$routeConfigFile = include($routeConfigFile);
		else
			throw new FileNotFoundException('Route Config File doesn`t exists');
		$dispatcher->setRoute($routeConfigFile);
		// Base url
		if(isset($config['base_url']))
			$this->setBaseUrl($config['base_url']);
		else 
			$this->setBaseUrl(null);
		// Translation Object
		$translate = new LanguageManager();
		if(isset($config['default_language']))
			$translate->set($config['default_language']);
		else 
			$translate->set();
		$om->setObject('translate', $translate);
		// optimization
        unset($dbConfigFile);
        unset($routeConfigFile);
		unset($config);
		unset($om);
	}
	
	/**
	 * Error
	 */
	public function runError($code = false){
		return $this->getObjectManager()->get('dispatcher')->dispatchToError($code);
	}
	
	/**
	 * Set Debug Mode
	 */
	private function setDebugMode($mode=false){
		if($mode){
			error_reporting(E_ALL);
			self::$debug = true;
		}
		else
			error_reporting(0);
	}
	/**
	 * Get Debug Mode
	 * @return boolean
	 */
	public function getDebugMode(){
		return self::$debug;
	}

	/**
	 * Set Base Url
	 */
	private function setBaseUrl($url = false){
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		if($url && $url != "")
			$this->baseUrl = $protocol . $_SERVER['HTTP_HOST'] . '/' . $url;
		else
			$this->baseUrl = $protocol . $_SERVER['HTTP_HOST'];
		$this->resourceUrl = $this->baseUrl . DS . 'resource';
		$this->getObjectManager()->get('cookie')->set('m_ref', $this->baseUrl);
	}

	/**
	 * Get Object Manager Instance
     *
     * @return array
	 */
	public function getObjectManager(){
		return self::$objectManager;
	}

	/**
	 * Dump variables
	 */
	public function _dump($var){
		if(self::$debug){
			echo "<div class='DUMP' style='border: 2px solid #cc9966; background-color: #f6efb9;margin-top: 30px; color:#000000;position: relative;z-index: 100000; padding: 10px; word-wrap:break-word'><pre>";
			var_dump($var);
			echo "<pre></div>";
		}
	}

	/**
	 * For PHP Request
	 */
	protected function processRequest(){
		$route = Router::route(self::$objectManager->get('dispatcher')->getRoute());
		self::$objectManager->get('dispatcher')->dispatch($route);
	}
}
