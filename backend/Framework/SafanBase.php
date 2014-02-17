<?php

namespace Framework;

use \Framework\Core\Exceptions\FileNotFoundException;

class SafanBase{
	/**
	 * Application instance
	 */
	private static $_app;
    /**
	 * Create Application
	 */
	public function createApp($class)
	{
		return self::createWebApp($class);
	}
	/**
	 * Call Web Application
	 */
	public function createWebApp($class)
	{
		return new $class;
	}
	/**
	 * Returns the application singleton, null if the singleton has not been created yet.
	 * @return App the application singleton, null if the singleton has not been created yet.
	 */
	public static function app()
	{
		return self::$_app;
	}
	/**
	 * Setup App
	 */
	public static function setApp($app)
	{
		if(self::$_app===null || $app===null)
			self::$_app=$app;
		else
			throw new FileNotFoundException('Framework application can only be created once.');
	}
}
