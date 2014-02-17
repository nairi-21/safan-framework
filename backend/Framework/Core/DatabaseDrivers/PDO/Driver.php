<?php

namespace Framework\Core\DatabaseDrivers\PDO;

use \Framework\Core\DatabaseDrivers\PDO\Exceptions\PDOException;
use \Framework\Core\DatabaseDrivers\PDO\Exceptions\ConnectionParamsNotExistsException;
use \Framework\Core\DatabaseDrivers\PDO\Exceptions\NoConnectionException;
use \Framework\Core\DatabaseDrivers\PDO\Exceptions\QueryFailedException;

class Driver{
	
	protected $dbh;
	protected $sth;
	protected $config;
	public $debug=false;
	
	private static $instance=array();
	
	private function __construct(){}
	
	private function __clone(){}
	
	public static function getInstance(){
		$class_name = __CLASS__;
		if(!isset(self::$instance[$class_name]) ){
			self::$instance[$class_name] = new $class_name();
		}
		return self::$instance[$class_name];
	}
	
	/** 
	* Connect
	* @param (config) Array. Sets db connection info and optional debuggin boolean
	* @return (void)
	*/
	public function setup(array $config){
		$dbhost = isset($config['db_host']) ? $config['db_host'] : false;
		$dbname = isset($config['db_name']) ? $config['db_name'] : false;
		$dbuser = isset($config['db_user']) ? $config['db_user'] : false;
		$dbpass = isset($config['db_pass']) ? $config['db_pass'] : false;
	
		if(isset($config['db_debug'])){
			$this->debug = $config['db_debug'];
		}
	
		if(!$dbhost || !$dbname || !$dbuser || !$dbpass)
			throw new ConnectionParamsNotExistsException();
		
		try {
			$this->dbh = new \PDO('mysql:host='.$dbhost.';dbname='.$dbname, $dbuser, $dbpass, array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); //cp1251
			$this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
		}
		catch (PDOException $e) {
			if($this->debug === true){
				echo "\r\n<!-- PDO CONNECTION ERROR: ".$e->getMessage()."-->\r\n";
			}
			$this->connect_error = "Error!: " . $e->getMessage() . "<br/>";
			$this->dbh = null;
			return;
		}
	}
	
	/**
	 * Query
	 */
	public function query($query, $params=array())
	{
		if (is_null($this->dbh))
			throw new NoConnectionException();
        try{
            $this->sth = $this->dbh->prepare($query);
            if($this->sth->execute($params))
            	return $this->debug = false;
            return $this->debug = true;
        }
        catch (PDOException $e){
            return false;
        }
	}
	/**
	 * Select All
	 */
	public function selectAll()
	{
		if(is_null($this->sth))
			throw new QueryFailedException();	
		return $this->sth->fetchAll(\PDO::FETCH_OBJ);
	}
	/**
	 * Select once
	 */
	public function selectOnce()
	{
		if(is_null($this->sth))
			throw new QueryFailedException();
		$result = $this->sth->fetch(\PDO::FETCH_OBJ);
		if($result)
			return $result;
		return null;
	}
	/**
	 * Insert
	 */
	public function insert()
	{
		if(is_null($this->sth))
			throw new QueryFailedException();
		return ($this->dbh->lastInsertId() > 0) ? $this->dbh->lastInsertId() : false;
	}
	/**
	 * Update
	 */
	public function update()
	{
		if(is_null($this->sth))
			throw new QueryFailedException();
		if($this->sth->rowCount() > 0)
			return $this->sth->rowCount();
		return false; 
	}
	/**
	 * Update
	 */
	public function delete()
	{
		if(is_null($this->sth))
			throw new QueryFailedException();
		if($this->debug === true)
			return false;
		return true;
	}
	
}
