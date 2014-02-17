<?php
namespace Framework\Core\Mvc\Model;

class MysqlFields
{
	protected $table;
	protected $ident;            // identifier of the property
    protected $type;             // MysqlDataMaper::TYPE_... type of the property
    protected $sql;              // sql expression (if different from ident)
    protected $noupdate;         // not use for INSERT queries
    protected $noinsert;         // not use for INSERT queries
    protected $pk;               // fiels is Primary Key
    
    public function __construct($table, $ident, $type, $noupdate = false, $noinsert = false)
    {
        $this->ident = $ident;
        $this->type = $type;
        $this->sql = $ident;
        $this->noinsert = $noinsert;
        $this->noupdate = $noupdate;
        $this->pk = false;
        $this->table = $table;
    }
    
    public function noinsert($noinsert = NULL)
    {
        if (!is_null($noinsert))
            $this->noinsert = (bool)$noinsert;
        return $this->noinsert;
    }
    
    public function noupdate($noupdate = NULL)
    {
        if (!is_null($noupdate))
            $this->noupdate = (bool)$noupdate;
        return $this->noupdate;
    }
    
    public function pk($pk = NULL)
    {
        if (!is_null($pk))
            $this->pk = (bool)$pk;
        return $this->pk;
    }
    
    public function sql($sql = NULL)
    {
        if (!is_null($sql))
            $this->sql = $sql;
        return $this->sql;
    }
    
    public function ident($ident = NULL)
    {
        if (!is_null($ident))
            $this->ident = $ident;
        return $this->ident;
    }

    public function title($ident = NULL)
    {
        return $this->ident();
    }
    
    public function type($type = NULL)
    {
        if (!is_null($type))
            $this->type = $type;
        return $this->type;
    }
    
    public function table($table = NULL)
    {
    	if (!is_null($table))
    		$this->table = $table;
    	return $this->table;
    }
}
