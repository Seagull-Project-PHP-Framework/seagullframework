<?php
require_once 'DB/mysqli.php';

/**
 * SGL mysqli driver. Extends sequences functionality and multiquering.
 *
 * @package seagull
 * @subpackage pear
 * @author Demian Turner <demian@phpkitchen.com>
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class DB_mysqli_SGL extends DB_mysqli
{
    function DB_mysqli_SGL()
    {
        $this->DB_mysqli();
        $this->phptype = 'mysqli_SGL';
    }

    function nextId($name, $ondemand = true)
    {
        if (SGL_Config::get('db.sepTableForEachSequence')) {
            $ret = parent::nextId($name, $ondemand);
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
        $ok = $this->getOne("SELECT GET_LOCK('sequence_lock', 10)");
        if (DB::isError($ok)) {
            return $this->raiseError($ok);
        }
        if (empty($ok)) {
            // failed to get the lock, bail with a DB_ERROR_NOT_LOCKED error
            return $this->mysqlRaiseError(DB_ERROR_NOT_LOCKED);
        }

        // get current value of sequence
        $query = "
            SELECT id
            FROM   " . SGL_Config::get('table.sequence') . "
            WHERE  name = '$name'
        ";
        $id = $this->getOne($query);
        if (DB::isError($id)) {
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
        if (DB::isError($ok)) {
            return $this->raiseError($ok);
        }

        // release the lock
        $ok = $this->getOne("SELECT RELEASE_LOCK('sequence_lock')");
        if (DB::isError($ok)) {
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

    function multiQuery($query)
    {
        if (!mysqli_multi_query($this->connection, $query)) {
            $this->raiseError();
        }
        $res = mysqli_store_result($this->connection);
        if (mysqli_more_results($this->connection)) {
            while (mysqli_next_result($this->connection)) {
                //  aggregate results
            }
        }
        $aRes = array();

        if ($res) {
            while ($oRow = mysqli_fetch_object($res)) {
                $aRes[] = $oRow;
            }
        }
        return $aRes;
    }

    function getMultiCol($query)
    {
        $aRes = array();
        if (mysqli_multi_query($this->connection, $query)) {
            do {
                /* store first result set */
                if ($result = mysqli_store_result($this->connection)) {
                    while ($aRow = mysqli_fetch_row($result)) {
                        $aRes[] = $aRow[0];
                    }
                    mysqli_free_result($result);
                }
                /* print divider */
                if (mysqli_more_results($this->connection)) {

                }
            } while (mysqli_next_result($this->connection));
        }
        //  test for errors
        if ($err = mysqli_error($this->connection)) {
            $aRes = PEAR::raiseError($err);
        }
        return $aRes;
    }
}
?>
