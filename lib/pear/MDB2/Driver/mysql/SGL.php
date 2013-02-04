<?php
require_once 'MDB2/Driver/mysql.php';

/**
 * SGL mysql driver. Extends sequences functionality.
 *
 * @package seagull
 * @subpackage pear
 * @author Demian Turner <demian@phpkitchen.com>
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class MDB2_Driver_mysql_SGL extends MDB2_Driver_mysql
{
    function MDB2_Driver_mysql_SGL()
    {
//        $this->MDB2_Driver_mysql();
        parent::__construct();
        $this->phptype = 'mysql';
    }

    function nextID($name, $ondemand = true)
    {
        if (SGL_Config::get('db.sepTableForEachSequence')) {
            $ret = parent::nextID($name, $ondemand);
        } else {
            $ret = $this->_nextId($name);
        }
        return $ret;
    }

    /**
     * Creates new sequence in SGL `sequence` table.
     *
     * Note that REPLACE query below correctly creates a new sequence
     * when needed.
     *
     * @param string $name  sequence name
     *
     * @return integer
     *
     * @access private
     */
    function _nextId($name)
    {
        // try to get the 'sequence_lock' lock
        $ok = $this->query("SELECT GET_LOCK('sequence_lock', 10)");
        if (PEAR::isError($ok)) {
            return $this->raiseError($ok);
        }
        if (empty($ok)) {
            // failed to get the lock, bail with a DB_ERROR_NOT_LOCKED error
            return $this->raiseError(MDB2_ERROR_NOT_LOCKED);
        }

        // get current value of sequence
        $query = "
            SELECT id
            FROM   " . SGL_Config::get('table.sequence') . "
            WHERE  name = '$name'
        ";
        $id = $this->queryOne($query);
        if (PEAR::isError($id)) {
            return $this->raiseError($id);
        } else {
            $id += 1;
        }

        // increment sequence value
        $query = "
            REPLACE
            INTO    " . SGL_Config::get('table.sequence') . "
            VALUES  ('$name', '$id')
        ";
        $ok = $this->query($query);
        if (PEAR::isError($ok)) {
            return $this->raiseError($ok);
        }

        // release the lock
        $ok = $this->query("SELECT RELEASE_LOCK('sequence_lock')");
        if (PEAR::isError($ok)) {
            return $this->raiseError($ok);
        }

        return $id;
    }

    /**
     * Overwritten method from parent class to allow logging facility.
     *
     * @param string $query  the SQL query
     *
     * @return mixed returns a valid MySQL result for successful SELECT
     *               queries, DB_OK for other successful queries.
     *               A DB error is returned on failure.
     *
     * @access public
     */
    function simpleQuery($query)
    {
        @$GLOBALS['_SGL']['QUERY_COUNT'] ++;
        return parent::simpleQuery($query);
    }
}
?>
