<?php
namespace Application\Models;

use Framework\Core\Mvc\Model\MysqlFields;
use Framework\Core\Mvc\Model\MysqlDataMaper;

class Languages extends MysqlDataMaper
{
    public function __construct(){
        $field = new MysqlFields($this->table(), 'id', MysqlDataMaper::FIELD_TYPE_INT);
        $field->pk(true);
        $field->noinsert(true);
        $field->noupdate(true);
        $this->addField($field);
        $field = new MysqlFields($this->table(), 'title', MysqlDataMaper::FIELD_TYPE_STR);
        $this->addField($field);
        $field = new MysqlFields($this->table(), 'iso', MysqlDataMaper::FIELD_TYPE_STR);
        $this->addField($field);
        $field = new MysqlFields($this->table(), 'prefix', MysqlDataMaper::FIELD_TYPE_STR);
        $this->addField($field);
    }
    /**
    ** @return string the associated database table name
    **/
    public static function model($className=__CLASS__)
    {
        return parent::record($className);
    }
    /**
    ** @return string the associated database table name
    **/
    public function table()
    {
        return 'languages';
    }
    /**
    ** @return array customized attribute labels (name=>label)
    **/
    public function attributes()
    {
        return array(
            'id' => 'ID',
            'title' => 'Title',
            'iso' => 'ISO',
            'prefix' => 'Prefix',
        );
    }
}
