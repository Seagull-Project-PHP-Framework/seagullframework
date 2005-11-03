<?php
require_once "DB/mysql.php";

class DB_mysql_SGL extends DB_mysql
{
    
    function DB_mysql_SGL() 
    {
        $this->DB_mysql();    
        $this->phptype = 'mysql_SGL';
    }
    
    function nextId($name, $null=false) 
    {
      /*
      ** Note that REPLACE query below correctly creates a new sequence
      ** when needed
      */
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        $result = $this->getOne("SELECT GET_LOCK('sequence_lock',10)");
        if (DB::isError($result)) {
            return $this->raiseError($result);
        }
        if ($result == 0) {
            // Failed to get the lock, bail with a DB_ERROR_NOT_LOCKED error
            return $this->mysqlRaiseError(DB_ERROR_NOT_LOCKED);
        }
        
        $id = $this->getOne("SELECT id FROM {$conf['table']['sequence']} WHERE name = '$name'");
        if (DB::isError($id)) {
            return $this->raiseError($id);
        } else {
            $id += 1;
        }

        $result = $this->query("REPLACE INTO {$conf['table']['sequence']} VALUES ('$name', '$id')");
        if (!$result) {
            return $this->raiseError($result);
        }

        // Release the lock
        $result = $this->getOne("SELECT RELEASE_LOCK('sequence_lock')");
        if (DB::isError($result)) {
            return $this->raiseError($result);
        }
        return $id;
    }
    
    /**
     * Overwritten method from parent class to allow logging facility.
     *
     * @param the SQL query
     * @access public
     * @return mixed returns a valid MySQL result for successful SELECT
     * queries, DB_OK for other successful queries.  A DB error is
     * returned on failure.
     */
    function simpleQuery($query)
    {
        @$GLOBALS['_SGL']['QUERY_COUNT'] ++;
        return parent::simpleQuery($query);
    }    
}
?>
