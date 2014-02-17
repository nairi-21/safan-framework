<?php
namespace Framework\Core\Mvc\Model;

use Framework\Core\DatabaseDrivers\PDO\Driver;
use Framework\Core\Mvc\Model\Exceptions\WrongFieldClassException;
use Framework\Core\Mvc\Model\Exceptions\TypeNotExistException;
use Framework\Safan;
use Framework\Core\Mvc\Model\Exceptions\NoPkException;

class MysqlDataMaper
{    
	CONST FIELD_TYPE_BOOL = 1;
	CONST FIELD_TYPE_INT = 2;
	CONST FIELD_TYPE_FLOAT = 3;
	CONST FIELD_TYPE_STR = 4;
	CONST FIELD_TYPE_DATETIME = 5;
	CONST FIELD_TYPE_STRARR = 6;
	CONST FIELD_TYPE_INTARR = 7;
	CONST FIELD_TYPE_OBJ = 8;
	
	public $fields = array();    
	protected static $records = array();
	protected $recordClass = '\stdClass';
	public $dumpQueryOnce = false;
	
	/*
	 * For Custom Query
	 */
	private $q = '';
	private $params = array();
	private $joins = array();
	private $where;
	private $in;
	private $orderBy;
	private $groupBy;
	private $limit;
	private $limitStart = false;
	private $limitEnd = false;

	/**
	 * @return Record Class Instance
	 */
    public static function record($className=__CLASS__){
		if(!isset(self::$records[$className]))
			self::$records[$className] = new $className;
		return self::$records[$className];
	}
	
	/**
	 * Add Fields
	 */
	public function addField($field)
	{
		if (!$field instanceof MysqlFields)
			throw new WrongFieldClassException();
		$this->fields[] = $field;
	}
	/**
	 * @return Fields
	 */
	public function getFields()
	{
		return $this->fields;
	}
	/**
	 * Check & convert Fields from Select
	 */
	private function convertTypesFromDB($obj)
	{
        foreach ($this->fields as $field) {
			switch ($field->type()) {
				case self::FIELD_TYPE_BOOL :
					$obj->{$field->ident()} = (bool)$obj->{$field->ident()};
					break;
				case self::FIELD_TYPE_INT :
					$obj->{$field->ident()} = (int)$obj->{$field->ident()};
					break;
				case self::FIELD_TYPE_FLOAT :
					$obj->{$field->ident()} = (float)$obj->{$field->ident()};
					break;
				case self::FIELD_TYPE_STR :
					break;
				case self::FIELD_TYPE_DATETIME :
					$d = new \DateTime();
					$d->setTimestamp($obj->{$field->ident()});
					$obj->{$field->ident()} = $d;
					break;
				case self::FIELD_TYPE_STRARR :
					$delimiter = '(^!)';
					if (empty($obj->{$field->ident()}))
						$obj->{$field->ident()} = array();
					else
						$obj->{$field->ident()} = array_map(function($v) use ($delimiter) {
						return stripslashes($v);
					}, explode($delimiter, $obj->{$field->ident()}));
					break;
				case self::FIELD_TYPE_INTARR :
					$arr = empty($obj->{$field->ident()})
					? array() : explode(',', $obj->{$field->ident()});
					$obj->{$field->ident()} = array_map(function($v) { return (int)str_replace('\'', '', $v); }, $arr);
					break;
				case self::FIELD_TYPE_OBJ :
					$obj->{$field->ident()} = empty($obj->{$field->ident()})
					? new \StdClass()
					: json_decode($obj->{$field->ident()});
					break;
				default :
					throw new TypeNotExistException();
			}
		}
		return $obj;
	}
	/**
	 * Check & convert Fields from Insert, Update
	 */
	private function convertTypesToDB($obj)
	{
		$o = new \stdClass();
		foreach ($this->fields as $field) {
			/*$val = property_exists($obj, $field->ident()) ?
			 $obj->{$field->ident()} : $this->getEmptyValue($field->type());*/
			if (!property_exists($obj, $field->ident()))
				continue;
			if (is_null($obj->{$field->ident()})) {
				$o->{$field->ident()} = 'NULL';
				continue;
			}
			$val = $obj->{$field->ident()};
	
			// WARNING: Don not modify $val! it may contain reference
			// to external variable and cause side effects!
			switch ($field->type()) {
				case self::FIELD_TYPE_BOOL :
					$o->{$field->ident()} = $val ? 1 : 0;
					break;
				case self::FIELD_TYPE_INT :
					$o->{$field->ident()} = (int)$val;
					break;
				case self::FIELD_TYPE_FLOAT :
					$o->{$field->ident()} = (float)$val;
					break;
				case self::FIELD_TYPE_STR :
					$o->{$field->ident()} = $this->escape($val);
					break;
				case self::FIELD_TYPE_DATETIME :
					$o->{$field->ident()} = $val->getTimestamp();
					break;
				case self::FIELD_TYPE_STRARR :
					$delimiter = '(^!)';
					$dbDriver = $this->dbDriver;
					$o->{$field->ident()} = '"' . implode($delimiter, array_map(function($v) use($delimiter) {
						$v = str_replace($delimiter, '', $v);
					}, $val)) . '"';
					break;
				case self::FIELD_TYPE_INTARR :
					$val = array_map(
					function($v){ return "'" . (int)$v . "'"; }, $val);
					$o->{$field->ident()} = '"' . implode(',', $val) . '"';
					break;
				case self::FIELD_TYPE_OBJ :
					$o->{$field->ident()} = '"' . addslashes(json_encode($val)) . '"';
					break;
				default :
					throw new TypeNotExistException();
			}
		}
		return $o;
	}
	/**
	 * @return empty object
	 */
	public function getEmptyObject()
	{
		return new $this->recordClass();
	}
	/**
	 * @return Pk
	 */
	public function getPK($require = true)
	{
		foreach ($this->fields as $field) {
			if ($field->pk()) {
				return $field;
			}
        }
		if ($require)
			throw new NoPkException();
	}
	/**
	 * Add Info from Join
	 */
	public function addJoin($tableName, $joinType = 'left', $on)
	{
		switch (strtolower($joinType)){
			case 'left':
				$joinType = ' LEFT JOIN ';
				break;
			case 'right':
				$joinType = ' RIGHT JOIN ';
				break;
			case 'inner':
				$joinType = ' INNER JOIN ';
				break;
			default:
				$joinType = ' LEFT JOIN ';
				break;
		}
		$this->joins[$tableName] = $joinType . $on;
	}
	/**
	 * Create Query
	 */
	private function createQuery($cache = false){
		$this->q = '';
		$uniqueArray = array();
        $i = 0;
		foreach ($this->fields as $field){
			if(in_array($field->ident(), $uniqueArray))
				$this->q .= ',' . $field->table() . '.' . $field->ident() . ' AS ' . $field->table() . ucfirst($field->ident());
			else{
				if($i > 0)
					$this->q .= ',' . $field->table() . '.' . $field->ident();
				else
					$this->q .= 'SELECT ' . $field->table() . '.' . $field->ident();
			}
			$uniqueArray[] = $field->ident();
			$i++;
		}
		$this->q .= ' FROM ' . $this->table();
		if(!$cache){
			if(!empty($this->joins)){
				foreach ($this->joins as $joinKey => $joinValue){
					$this->q .= $joinValue;
				}
			}
		}
			
    }

    private function clearParams(){
        $this->limit = null;
        $this->limitStart = false;
        $this->limitEnd = false;
        $this->where = null; 
        $this->orderBy = null;
        $this->groupBy = null;
        $this->in = null;
        $this->params = array();
        $this->join = array();
        $this->q = '';
        //$this->fields = array();
    } 

	/**
	 * @return object
	 */
	public function findByPK($pk){
		$this->createQuery();
		$this->q .=  ' WHERE ' . $this->table() . '.' . $this->getPK()->ident() . '=?';
        Driver::getInstance()->query($this->q, array($pk));
		$obj = Driver::getInstance()->selectOnce();
		if(is_null($obj))
			return null;
        $obj = $this->convertTypesFromDB($obj);
        $this->clearParams();
		if($obj)
			return $obj;
		return null;
	}
	/**
	 * @return array
	 */
	public function beginAllInArray($fieldName, $fieldArray = array()){
		$toReturn = array();
		$this->createQuery();
		if(empty($fieldArray))
			return $toReturn;
		$objects = $this->in($fieldName, $fieldArray)->run();
		if(empty($objects))
			return $toReturn;
		foreach($objects as $object)
			$toReturn[$object->{$fieldName}] = $object;
		return $toReturn;
	}
	/**
	 * @return array
	 */
	public function beginAll(){
		$this->createQuery();
		Driver::getInstance()->query($this->q, array());
		$objects = Driver::getInstance()->selectAll();
        $this->clearParams();
        if(empty($objects))
			return array();
		$toReturn = array();
		foreach($objects as $object){
			$obj = $this->convertTypesFromDB($object);
			if($obj)
				$toReturn[] = $obj;
		}
		return $toReturn;
	}
	/**
	 * @return object
	 */
	public function begin(){
		$this->createQuery();
		Driver::getInstance()->query($this->q, array());
		$obj = Driver::getInstance()->selectOnce();
		$obj = $this->convertTypesFromDB($obj);
        $this->clearParams();
		if($obj)
			return $obj;
		return null;
    }
    /**
     * Delete
     */
    public function delete($obj){
        $o = $this->convertTypesToDB($obj);
        $pk = $this->getPK();
        $sql = 'DELETE FROM ' . $this->table() . ' WHERE ' . $pk->ident() . '=?';      
        $params = array($o->{$pk->ident()});
        Driver::getInstance()->query($sql, $params);
        $query = Driver::getInstance()->delete(); 
        if($query)
            return true;
        return false;
    }

	/************************* Custom Query ****************************/
	/**
	 * WHERE
	 * Example - Users::model()->where(array('id' => '1'), array('!='))->run()
	 */
	public function where($query = array(), $differential = array(), $or = array()){
		if(!empty($query)){
			$i = 0;
            foreach($query as $key => $value){
                $isLike = false; // Is Differencial like
				if(isset($differential[$i])){
                    if($differential[$i] == 'like')
                        $isLike = true;
                    $different = $differential[$i];
                }
				else 
					$different = '=';
				//For left join tables
				$tableName = '';
				if(strpos($key, '.') === false)
					$tableName = $this->table() . '.';
                if($i>0){
                    if($isLike)
                        $this->where .= ' AND ' . $tableName . $key . ' LIKE "%' . $this->escape($value)  . '%"';
                    else    
                        $this->where .= ' AND ' . $tableName . $key . $different . '?';
                }
                else{
                    if($isLike)
                        $this->where .= ' WHERE ' . $tableName . $key . ' LIKE "%' . $this->escape($value)  . '%"';
                    else    
                        $this->where .= ' WHERE ' . $tableName . $key . $different . '?';
                }
                $i++;
                if(!$isLike)
				    $this->params[] = $value;
            }
            //Or
            if(!empty($or)){
                $i = 0;
                foreach($or as $key => $value){
                    if($i>0)
                        $this->where .= ' AND ' . $tableName . $key . '=' . '?';
                    else    
                        $this->where .= ' OR (' . $tableName . $key . '=' . '?';
                    $this->params[] = $value;
                    $i++;
                }
                $this->where .= ')';
            }
		}
		return $this;
	}
	/**
	 * IN
	 * Example - Users::model()->where(array('id' => '1'), array('!='))->in('id', $IDs)->run()
	 * $IDs - array
	 */
    public function in($fieldName, $fieldArray = array()){
        $fieldArray = array_map(function($v) { return sprintf('"%s"', $v); }, $fieldArray);
        $tableName = '';
        if(strpos($fieldName, '.') === false)
			$tableName = $this->table() . '.';
        if(!empty($fieldArray)){
			$this->in = $tableName . $fieldName . ' IN (' . implode(', ', $fieldArray) . ')';
		}
		return $this;
	}
	/**
	 * JOIN
	 * Example - 
	 * 		$usersInfoFields = UsersInfo::model()->getFields();	
	 * 		Users::model()->join('usersInfo', 'left', 'usersInfo.userID = users.id', $usersInfoFields)->where(array('id' => '1'), array('!='))->run()
	 */
	public function join($tableName, $joinType = 'left', $on, $joinTableFields = array()){
		switch (strtolower($joinType)){
			case 'left':
				$joinType = ' LEFT JOIN ';
				break;
			case 'right':
				$joinType = ' RIGHT JOIN ';
				break;
			case 'inner':
				$joinType = ' INNER JOIN ';
				break;
			default:
				$joinType = ' LEFT JOIN ';
				break;
		}
		if(!empty($joinTableFields))
			$this->fields = array_merge($this->fields, $joinTableFields);
		$this->joins[$tableName] = $joinType . $tableName . ' ON ' . $on;
		return $this;
	}
	/**
	 * ORDER BY
	 * example - Users::model()->orderBy(array('enabled'))->run()
	 */
	public function orderBy($query = array()){
		if(!empty($query)){
			$i = 0;
			foreach($query as $key => $value){
				$tableName = '';
				if(strpos($key, '.') === false)
					$tableName = $this->table() . '.';
				if($i>0)
					$this->orderBy .= ', ' . $tableName . $key . ' ' . $value;
				else
					$this->orderBy .= ' ORDER BY ' . $tableName . $key . ' ' . $value;
				$i++;
			}
		}
		return $this;
	}
	/**
	 * GROUP BY
	 * example - Users::model()->groupBy(array('enabled'))->run()
	 */
	public function groupBy($query = array()){
		if(!empty($query)){
			$i = 0;
			foreach($query as $value){
				if($i>0)
					$this->groupBy .= ', ' . $this->table() . '.' . $value;
				else
					$this->groupBy .= ' GROUP BY ' . $this->table() . '.' . $value;
				$i++;
			}
		}
		return $this;
	}
	/**
	 * LIMIT
	 * example - Users::model()->groupBy(array('enabled'))->limit(0, 3)->run()
	 */
	public function limit($limit, $startFrom = false){
		if($startFrom !== false)
			$this->limit = ' LIMIT ' . $startFrom . ', ' . $limit;
		else 
			$this->limit = ' LIMIT ' . $limit;
		return $this;
	}
	/**
	 * RUN
	 * example - Users::model()->where(array('enabled' => '1'))->groupBy(array('enabled'))->run()
	 */
	public function run($oneRecord = false){
		$this->createQuery(true);
		if(!empty($this->joins)){
			foreach ($this->joins as $joinKey => $joinValue){
				$this->q .= $joinValue;
			}
        }
		if(!is_null($this->where)){
			$this->q .= $this->where;
			if(!is_null($this->in))
				$this->q .= ' AND ' . $this->in;
		}
		elseif(!is_null($this->in))
			$this->q .= ' WHERE ' . $this->in;
		if(!is_null($this->groupBy))
			$this->q .= $this->groupBy;
		if(!is_null($this->orderBy))
			$this->q .= $this->orderBy;
		if(!is_null($this->limit))
            $this->q .= $this->limit;
		Driver::getInstance()->query($this->q, $this->params);
		if($this->dumpQueryOnce)
			Safan::app()->_dump($this->q);
        if($oneRecord)
            $objects = Driver::getInstance()->selectOnce();
        else
            $objects = Driver::getInstance()->selectAll();
        $this->clearParams();
		if(empty($objects))
            return array();
		$toReturn = array();
		foreach($objects as $object){
			$obj = $this->convertTypesFromDB($object);
			if($obj)
				$toReturn[] = $obj;
        }
		return $toReturn;
	}
	
	/**
	 * UPDATE
	 * example - Users::model()->save($obj);
	 */
	public function save($obj, $where = array(), $isUpdate = false){
		$obj = $this->convertTypesToDB($obj);
		$PK = $this->getPK();
		$params = array();
		if(!empty($where)){
			$i = 0;
			$paramsForSqlString = '';
			foreach ($this->fields as $field) {
				if ($field->noupdate() || !isset($obj->{$field->ident()}))
					continue;
				$params[':' . $field->ident()] = $obj->{$field->ident()};
				if($i>0)
					$paramsForSqlString .= ', ' . $field->ident() . '=:' . $field->ident();
				else
					$paramsForSqlString .= $field->ident() . '=:' . $field->ident();
				$i++;
			}
			//where attributes
			$j = 0;
			foreach($where as $key => $value){
				if($j>0){
					$whereString = ' AND ' . $key . '=:' . $key;
				}
				else{
					$whereString = ' WHERE ' . $key . '=:' . $key;
				}
				$params[':' . $key] = $value;
			}
			$sql = 'UPDATE ' . $this->table() . ' SET ' . $paramsForSqlString . $whereString;
		    if($this->dumpQueryOnce)
			    Safan::app()->_dump($sql);
			Driver::getInstance()->query($sql, $params);
			$query = Driver::getInstance()->update();
            $this->clearParams();
			if($query)
				return $query;
			return false;
        }
		if((property_exists($obj, $PK->ident()) && $PK->noinsert()) || $isUpdate){
			//Update
			$i = 0;
			$paramsForSqlString = '';
			foreach ($this->fields as $field) {
				if ($field->noupdate() || !isset($obj->{$field->ident()}))
					continue;
				$params[':' . $field->ident()] = $obj->{$field->ident()};
				if($i>0)
					$paramsForSqlString .= ', ' . $field->ident() . '=:' . $field->ident();
				else
					$paramsForSqlString .= $field->ident() . '=:' . $field->ident();
				$i++;
			}
			$params[':' . $PK->ident()] = $obj->{$PK->ident()};
			$sql = 'UPDATE ' . $this->table() . ' SET ' . $paramsForSqlString . ' WHERE '. $PK->ident() .'=:'. $PK->ident();
		    if($this->dumpQueryOnce)
			    Safan::app()->_dump($sql);
			Driver::getInstance()->query($sql, $params);
			$query = Driver::getInstance()->update();
            $this->clearParams();
			if($query)
				return $query;
			return false;
		}
		else{ 
			//Insert
			$i = 0;
			$paramsForSqlString = '(';
			$valueForSqlString = '(';
			foreach ($this->fields as $field) {
				if ($field->noinsert() || !isset($obj->{$field->ident()}))
					continue;
				$params[':' . $field->ident()] = $obj->{$field->ident()};
				if($i>0){
					$paramsForSqlString .= ', ' . $field->ident();
					$valueForSqlString .= ', :' . $field->ident();
				}
				else{
					$paramsForSqlString .= $field->ident();
					$valueForSqlString .= ':' . $field->ident();
				}
				$i++;
			}
			$paramsForSqlString .= ')';
			$valueForSqlString .= ')';
			$sql = 'INSERT INTO ' . $this->table() . ' ' . $paramsForSqlString . ' VALUES ' . $valueForSqlString;
            if($this->dumpQueryOnce)
                Safan::app()->_dump($sql);
            Driver::getInstance()->query($sql, $params);
			$query = Driver::getInstance()->insert();
            $this->clearParams();
			//$this->attributes = array();
			if($query)
				return $query;
			return false;
		}
		
	}
	/**
	 * Escape
	 */
	public function escape($str, $quotes = true)
	{
		return $quotes ? sprintf('%s', $str) : $str;
	}
	
	
}
