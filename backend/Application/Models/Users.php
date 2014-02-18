<?php
namespace Application\Models;

use Framework\Core\Mvc\Model\MysqlFields;
use Framework\Core\Mvc\Model\MysqlDataMaper;
use Framework\Safan;

class Users extends MysqlDataMaper
{
	const USERS_DISABLED = 0;
	const USERS_ENABLED = 1;

    /**
     * Constructor
     */
    public function __construct(){
		$field = new MysqlFields($this->table(), 'id', MysqlDataMaper::FIELD_TYPE_INT);
		$field->pk(true);
		$field->noinsert(true);
		$field->noupdate(true);
        $this->addField($field);
        $field = new MysqlFields($this->table(), 'fbOAuthID', MysqlDataMaper::FIELD_TYPE_STR);
        $field->noupdate(true);
        $this->addField($field);
        $field = new MysqlFields($this->table(), 'email', MysqlDataMaper::FIELD_TYPE_STR);
        $this->addField($field);
        $field = new MysqlFields($this->table(), 'emailVerify', MysqlDataMaper::FIELD_TYPE_STR);
        $this->addField($field);
        $field = new MysqlFields($this->table(), 'username', MysqlDataMaper::FIELD_TYPE_STR);
        $this->addField($field);
        $field = new MysqlFields($this->table(), 'displayName', MysqlDataMaper::FIELD_TYPE_STR);
        $this->addField($field);
        $field = new MysqlFields($this->table(), 'password', MysqlDataMaper::FIELD_TYPE_STR);
        $this->addField($field);
        $field = new MysqlFields($this->table(), 'hash', MysqlDataMaper::FIELD_TYPE_STR);
        $this->addField($field);
        $field = new MysqlFields($this->table(), 'hashDate', MysqlDataMaper::FIELD_TYPE_DATETIME);
        $this->addField($field);
        $field = new MysqlFields($this->table(), 'language', MysqlDataMaper::FIELD_TYPE_STR);
        $this->addField($field);
        $field = new MysqlFields($this->table(), 'enabled', MysqlDataMaper::FIELD_TYPE_INT);
        $this->addField($field);
        $field = new MysqlFields($this->table(), 'creationDate', MysqlDataMaper::FIELD_TYPE_DATETIME);
        $this->addField($field);
        $field = new MysqlFields($this->table(), 'creationIp', MysqlDataMaper::FIELD_TYPE_INT);
        $this->addField($field);
        $field = new MysqlFields($this->table(), 'modifiedDate', MysqlDataMaper::FIELD_TYPE_DATETIME);
        $this->addField($field);
        $field = new MysqlFields($this->table(), 'lastLoginDate', MysqlDataMaper::FIELD_TYPE_DATETIME);
        $this->addField($field);
        $field = new MysqlFields($this->table(), 'lastLoginIp', MysqlDataMaper::FIELD_TYPE_INT);
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
		return 'users';
	}
}
