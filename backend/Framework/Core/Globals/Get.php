<?php

namespace Framework\Core\Globals;

class Get
{
	public static function exists($name, $default = false)
	{
		return isset($_GET[$name]) ? strtolower($_GET[$name]) : $default;
	}
	
    public static function str($name, $default = '')
    {
        return isset($_GET[$name]) ? $_GET[$name] : $default;
    }
    
    public static function int($name, $default = 0)
    {
        return isset($_GET[$name]) ? (int)$_GET[$name] : $default;
    }
    
    public static function strArr($name, $default = array())
    {
        if (!isset($_GET[$name]) || !is_array($_GET[$name]))
            return $default;
        else
            return $_GET[$name];
    }
    
    public static function intArr($name, $default = array())
    {
        if (!isset($_GET[$name]) || !is_array($_GET[$name]))
            return $default;
        else
            return array_map(function($v){ return (int)$v; }, $_GET[$name]);
    }
    
    public static function bool($name, $default = false)
    {
        return isset($_GET[$name]) ? (bool)$_GET[$name] : $default;
    }
    
    public static function float($name, $default = 0.0)
    {
        return isset($_GET[$name]) ? (float)$_GET[$name] : $default;
    }
    
    public static function setParams($key, $value)
    {
    	$_GET[$key] = $value;	
    }
}