<?php

namespace Framework\Lib\Memcache;

class DBCache
{
	public $memcache;
	
	/* ========= Connecting to DB =============*/
	public function __construct()
	{
		$this->memcache = new \Memcache;
		$this->memcache->connect('localhost', 11211) or die ("Could not connect");  
	}
	/*========= End of Connecting to DB =========== */
	
	
	/* public function Connect()
	{
		$this->memcache = new Memcache;
		$this->memcache->connect('localhost', 11211) or die ("Could not connect");
	} */
	
	
	/* public static function GetInstance() {
        static $instance;
        if (!isset($instance)) {
            $instance = new self();
        } 
        return $instance;
    } */
	
	public function GetCache($key) 
	{
        return ($this->memcache) ? $this->memcache->get($key) : false;
	}
	

	public function SetCache($key, $object, $timeout = 60) 
	{
        return ($this->memcache) ? $this->memcache->set($key, $object, MEMCACHE_COMPRESSED, $timeout) : false;
    }
	
	
	public function DeleteCache($key) 
	{
        return ($this->memcache) ? $this->memcache->delete($key) : false;
    }
	
	
	public function QueryCache($sql, $linkIdentifier = false, $timeout = 60) 
	{
        if (($cache = $this->GetCache(md5("mysql_query" . $sql))) !== false) {
            $cache = false;
            $r = ($linkIdentifier !== false) ? mysql_query($sql,$linkIdentifier) : mysql_query($sql);
            if (is_resource($r) && (($rows = mysql_num_rows($r)) !== 0)) {
                for ($i=0;$i<$rows;$i++) {
                    $fields = mysql_num_fields($r);
                    $row = mysql_fetch_array($r);
                    for ($j=0;$j<$fields;$j++) {
                        if ($i === 0) {
                            $columns[$j] = mysql_field_name($r,$j);
                        }
                        $cache[$i][$columns[$j]] = $row[$j];
                    }
                }
                if (!$this->SetCache(md5("mysql_query" . $sql),$cache,$timeout)) {
                    # If we get here, there isn't a memcache daemon running or responding
                }
            }
        }
        return $cache;
    }
}
?>
