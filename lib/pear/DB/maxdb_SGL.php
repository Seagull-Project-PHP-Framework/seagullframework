<?php
require_once 'DB/odbc.php';

/**
 * SGL extension to maxdb driver
 *
 */
class DB_maxdb_SGL extends DB_odbc
{
    // {{{ constructor

    function DB_maxdb_SGL()
    {
        // call constructor of parent class
        $this->DB_odbc();

        $this->phptype = 'odbc';
        $this->dbsyntax = 'sql92';

        // set special options
        $this->setOption('portability',DB_PORTABILITY_LOWERCASE | DB_PORTABILITY_RTRIM);
    }

    /**
     * overrides DB_odbc::getSpecialQuery() and returns query that returns
     * the tablename in lowercase.
     *
     * Returns the query needed to get some backend info
     * @param string $type What kind of info you want to retrieve
     * @return string The SQL query string
     */
    function getSpecialQuery($type)
    {

        switch ($type) {
            case 'databases':
                if (!function_exists('odbc_data_source')) {
                    return null;
                }
                $res = @odbc_data_source($this->connection, SQL_FETCH_FIRST);
                if (is_array($res)) {
                    $out = array($res['server']);
                    while($res = @odbc_data_source($this->connection,
                                                   SQL_FETCH_NEXT))
                    {
                        $out[] = $res['server'];
                    }
                    return $out;
                } else {
                    return $this->odbcRaiseError();
                }
                break;
            case 'tables':
            case 'schema.tables':
                $keep = 'TABLE';
                break;
            case 'views':
                $keep = 'VIEW';
                break;
            default:
                return null;
        }

        /*
         * Removing non-conforming items in the while loop rather than
         * in the odbc_tables() call because some backends choke on this:
         *     odbc_tables($this->connection, '', '', '', 'TABLE')
         */
        $res  = @odbc_tables($this->connection);
        if (!$res) {
            return $this->odbcRaiseError();
        }
        $out = array();

        if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE) {
            $case_func = 'strtolower';
        } else {
            $case_func = 'strval';
        }

        while ($row = odbc_fetch_array($res)) {
            if ($row['TABLE_TYPE'] != $keep) {
                continue;
            }
            if ($type == 'schema.tables') {
                $out[] = $case_func($row['TABLE_SCHEM']) . '.' . $case_func($row['TABLE_NAME']);
            } else {
                $out[] = $case_func($row['TABLE_NAME']);
            }
        }
        return $out;
    }

    /**
     * overrides DB_odbc::tableInfo() and adds support for primary key flags
     * needed by DataObjects. It also substitutes some datatypes that aren't
     * known by DataObjects.
     *
     * Returns information about a table or a result set.
     *
     * @param object|string  $result  DB_result object from a query or a
     *                                 string containing the name of a table.
     *                                 While this also accepts a query result
     *                                 resource identifier, this behavior is
     *                                 deprecated.
     * @param int            $mode    a valid tableInfo mode
     *
     * @return array  an associative array with the information requested.
     *                 A DB_Error object on failure.
     *
     * @see DB_common::tableInfo()
     * @since Method available since Release 1.7.0
     */
    function tableInfo($result, $mode = null)
    {
        if (is_string($result)) {
            /*
             * Probably received a table name.
             * Create a result resource identifier.
             */
            $id = @odbc_exec($this->connection, "select * from COLUMNS where TABLENAME = '".strtoupper($result)."'");
            if (!$id) {
                return $this->odbcRaiseError();
            }
            $got_string = true;
        } elseif (isset($result->result)) {
            /*
             * Probably received a result object.
             * Extract the result resource identifier.
             */
            $id = $result->result;
            $got_string = false;
        } else {
            /*
             * Probably received a result resource identifier.
             * Copy it.
             * Deprecated.  Here for compatibility only.
             */
            $id = $result;
            $got_string = false;
        }

        if (!is_resource($id)) {
            return $this->odbcRaiseError(DB_ERROR_NEED_MORE_DATA);
        }

        if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE) {
            $case_func = 'strtolower';
        } else {
            $case_func = 'strval';
        }

        $count = 0;
        $res = array();
        while ( $aColumn = odbc_fetch_array($id) ) {
            $res[$count] = array(
                'table' => $got_string ? $case_func($result) : '',
                'name'  => $case_func($aColumn[COLUMNNAME]),
                'type'  => $aColumn[DATATYPE],
                'len'   => $aColumn[LEN],
                'flags' => ($aColumn[NULLABLE] == "YES") ? '' : 'not_null '
            );

            if ( $aColumn[MODE] == "KEY" )
                $res[$count]['flags'] .= 'primary_key';

            if ($mode & DB_TABLEINFO_ORDER) {
                $res['order'][$res[$count]['name']] = $count;
            }
            if ($mode & DB_TABLEINFO_ORDERTABLE) {
                $res['ordertable'][$res[$count]['table']][$res[$count]['name']] = $count;
            }

            $count++;
        }

        if ($mode) {
            $res['num_fields'] = $count;
        }

        // free the result only if we were called on a table
        if ($got_string) {
            @odbc_free_result($id);
        }
        return $res;
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

        // AS: angepasst für MaxDB statt COALESCE -> VALUE im SQL-Statement
        $query = str_replace('COALESCE(', 'VALUE(', $query);

        // replace ; for MaxDB
        $query = preg_replace("/;\s*$/", '', $query);

        //echo "Query(".$this->phptype."): ".$query."<br>";

        return parent::simpleQuery($query);
    }

}

?>
