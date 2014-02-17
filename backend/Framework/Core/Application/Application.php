<?php
namespace Framework\Core\Application;

use \Framework\Safan;

abstract class Application{

    /**
     * @return mixed
     * Web Application request
     */
    abstract protected function processRequest();

    /**
     * @param null $config
     * Constructor
     */
    public function __construct($config=null){
        Safan::setApp($this);
		$this->runApplication();
	}

	/**
	 * Run Web Application
	 */
	public function run(){
		$this->processRequest();
	}
}
