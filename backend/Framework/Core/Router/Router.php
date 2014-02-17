<?php

namespace Framework\Core\Router;

use \Framework\Safan;
use \Framework\Core\Globals\Get;

class Router{
	
	public static function route($cfgRoutes, $uri=false, $loadError = false)
	{
		if($uri){
			if($uri == Safan::app()->baseUrl)
				$uri = '/';
		}
		else{
            $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $uri = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        $uriRequest = strpos($uri, Safan::app()->baseUrl);
        if($uriRequest !== false){
            $uri = substr($uri, strlen(Safan::app()->baseUrl) - $uriRequest);
        }

		//Get Variables
		if (strpos($uri, '?') !== false){
			$uriVars = parse_str(substr(strstr($uri, '?'), 1), $outPutVars);
			//Generate Get variables
			foreach($outPutVars as $key => $value){
				if(($key != 'module') && ($key != 'controller') && ($key != 'action'))
					Get::setParams($key, $value);
			}
			//Generate main uri
			$uri = strstr($uri, '?', true);
		}

        // error 404
        if($loadError){
            Get::setParams('module',$cfgRoutes[404]['module']);
            Get::setParams('controller', $cfgRoutes[404]['controller']);
            Get::setParams('action', $cfgRoutes[404]['action']);

            $route['matches'] = array();
            foreach ($cfgRoutes[404]['matches'] as $key => $varName) {
                if (empty($varName))
                    continue;
                if (isset($matches[$key]))
                    $_GET[$varName] = $matches[$key];
            }
            return true;
        }

        // load route
		foreach ($cfgRoutes as $rule => $settings) {
			$matches = array();
			if (($rule !== 404) && preg_match($rule, $uri, $matches)) {
				Get::setParams('module',$settings['module']);
				Get::setParams('controller', $settings['controller']);
				Get::setParams('action', $settings['action']);
				
				$route['matches'] = array();
				foreach ($settings['matches'] as $key => $varName) {
					if (empty($varName))
						continue;
					if (isset($matches[$key]))
						$_GET[$varName] = $matches[$key];
				}
				return true;
			}
		}
		// 404 Route Doesn't Exist
		return false;
	}
	
	
}
