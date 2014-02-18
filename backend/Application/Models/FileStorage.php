<?php
namespace Application\Models;

use Framework\Core\Mvc\Model\MysqlFields;
use Framework\Core\Mvc\Model\MysqlDataMaper;

class FileStorage extends MysqlDataMaper
{
	/**
	 * Constructor
	 */
	public function __construct(){
		$field = new MysqlFields($this->table(), 'id', MysqlDataMaper::FIELD_TYPE_INT);
		$field->pk(true);
		$field->noupdate(true);
		$this->addField($field);
		$field = new MysqlFields($this->table(), 'entityType', MysqlDataMaper::FIELD_TYPE_INT);
		$this->addField($field);
		$field = new MysqlFields($this->table(), 'entityID', MysqlDataMaper::FIELD_TYPE_INT);
		$this->addField($field);
		$field = new MysqlFields($this->table(), 'fileName', MysqlDataMaper::FIELD_TYPE_STR);
		$this->addField($field);
		$field = new MysqlFields($this->table(), 'fileExt', MysqlDataMaper::FIELD_TYPE_STR);
		$this->addField($field);
		$field = new MysqlFields($this->table(), 'creationDate', MysqlDataMaper::FIELD_TYPE_DATETIME);
		$this->addField($field);
	}
	/**
	 * @return string the associated database table name
	 */
	public static function model($className=__CLASS__)
	{
		return parent::record($className);
	}
	/**
	 * @return string the associated database table name
	 */
	public function table()
	{
		return 'fileStorage';
	}
}