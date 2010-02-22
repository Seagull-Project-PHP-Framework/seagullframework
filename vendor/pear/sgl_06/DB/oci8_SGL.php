<?php
require_once 'DB/oci8.php';

/**
 * SGL extension to oci8 driver
 *
 */
class DB_oci8_SGL extends DB_oci8
{
    // {{{ constructor

    function DB_oci8_SGL()
    {
        // call constructor of parent class
        $this->DB_oci8();
        
        // set special options
        $this->setOption('portability',DB_PORTABILITY_LOWERCASE | DB_PORTABILITY_NUMROWS);
    }

    // }}}
    // {{{ modifyLimitQuery()

    /**
     * overrides DB_oci8::modifyLimitQuery(). Adds support for omitting
     * the parameter $count.
     *
     * @param string $query The query to treat
     * @param int    $from  The row to start to fetch from
     * @param int    $count The offset
     * @return string The modified query
     *
     * @author Tobias Kuckuck <kuckuck@rancon.de>
     */
    function modifyLimitQuery($query, $from, $count=-1, $params = array())
    {
        // Let Oracle return the name of the columns instead of
        // coding a "home" SQL parser

        if (count($params)) {
            $result = $this->prepare("SELECT * FROM ($query) "
                                     . 'WHERE NULL = NULL');
            $tmp =& $this->execute($result, $params);
        } else {
            $q_fields = "SELECT * FROM ($query) WHERE NULL = NULL";

            if (!$result = @OCIParse($this->connection, $q_fields)) {
                $this->last_query = $q_fields;
                return $this->oci8RaiseError();
            }
            if (!@OCIExecute($result, OCI_DEFAULT)) {
                $this->last_query = $q_fields;
                return $this->oci8RaiseError($result);
            }
        }

        $ncols = OCINumCols($result);
        $cols  = array();
        for ( $i = 1; $i <= $ncols; $i++ ) {
            $cols[] = '"' . OCIColumnName($result, $i) . '"';
        }
        $fields = implode(', ', $cols);
        // XXX Test that (tip by John Lim)
        //if (preg_match('/^\s*SELECT\s+/is', $query, $match)) {
        //    // Introduce the FIRST_ROWS Oracle query optimizer
        //    $query = substr($query, strlen($match[0]), strlen($query));
        //    $query = "SELECT /* +FIRST_ROWS */ " . $query;
        //}

        // Construct the query
        // more at: http://marc.theaimsgroup.com/?l=php-db&m=99831958101212&w=2
        // Perhaps this could be optimized with the use of Unions
        if ($count >= 0) {
            $query = "SELECT $fields FROM".
                 "  (SELECT rownum as linenum, $fields FROM".
                 "      ($query)".
                 '  WHERE rownum <= '. ($from + $count) .
                 ') WHERE linenum >= ' . ++$from;
        }
        else {
            $query = "SELECT $fields FROM".
                 "      ($query)".
                 "  WHERE rownum <= $from";
        }
        return $query;
    }

    // }}}
    // {{{ getSpecialQuery()

    /**
     * overrides DB_oci8::getSpecialQuery() and returns query that returns
     * the tablename in lowercase.
     *
     * Returns the query needed to get some backend info
     * @param string $type What kind of info you want to retrieve
     * @return string The SQL query string
     */
    function getSpecialQuery($type)
    {
        switch ($type) {
            case 'tables':
                return 'SELECT lower(table_name) FROM user_tables';
            case 'views':
                return 'SELECT lower(view_name) FROM user_views';
            case 'sequences':
                return 'SELECT lower(sequence_name) FROM user_sequences';
            default:
                return null;
        }
    }

    // }}}
    // {{{ tableInfo()

    /**
     * overrides DB_oci8::tableInfo() and adds support for primary key flags
     * needed by DataObjects. It also substitutes some datatypes that aren't
     * known by DataObjects. 
     *
     * Returns information about a table or a result set.
     *
     * NOTE: only supports 'table' and 'flags' if <var>$result</var>
     * is a table name.
     *
     * NOTE: flags won't contain index information.
     *
     * @param object|string  $result  DB_result object from a query or a
     *                                string containing the name of a table
     * @param int            $mode    a valid tableInfo mode
     * @return array  an associative array with the information requested
     *                or an error object if something is wrong
     * @access public
     * @internal
     * @see DB_common::tableInfo()
     */
    function tableInfo($result, $mode = null)
    {
        if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE) {
            $case_func = 'strtolower';
        } else {
            $case_func = 'strval';
        }

        if (is_string($result)) {
            /*
             * Probably received a table name.
             * Create a result resource identifier.
             */
            $result = strtoupper($result);
            $q_fields = 'SELECT column_name, data_type, data_length, '
                        . 'nullable '
                        . 'FROM user_tab_columns '
                        . "WHERE table_name='$result' ORDER BY column_id";

            $this->last_query = $q_fields;

            if (!$stmt = @OCIParse($this->connection, $q_fields)) {
                return $this->oci8RaiseError(DB_ERROR_NEED_MORE_DATA);
            }
            if (!@OCIExecute($stmt, OCI_DEFAULT)) {
                return $this->oci8RaiseError($stmt);
            }

            $i = 0;
            while (@OCIFetch($stmt)) {
                $res[$i]['table'] = $case_func($result);
                $res[$i]['name']  = $case_func(@OCIResult($stmt, 1));
                $res[$i]['type']  = @OCIResult($stmt, 2);
                $res[$i]['len']   = @OCIResult($stmt, 3);
                $res[$i]['flags'] = (@OCIResult($stmt, 4) == 'N') ? 'not_null ' : '';
      
                $res[$i]['type']  = preg_replace("/NUMBER/","DECIMAL",$res[$i]['type']);
                $res[$i]['type']  = preg_replace("/LONG/","LONGTEXT",$res[$i]['type']);
                $res[$i]['type']  = preg_replace("/CLOB/","LONGTEXT",$res[$i]['type']);
                
                $q_pk = "select count(*) anzahl 
                        from user_cons_columns a,user_constraints b
                        where a.constraint_name = b.constraint_name 
                        and a.column_name = '".@OCIResult($stmt, 1)."'
                        and a.table_name = '$result'
                        and b.constraint_type = 'P'";

                $this->last_query = $q_pk;

                if (!$stmt_pk = @OCIParse($this->connection, $q_pk)) {
                    return $this->oci8RaiseError(DB_ERROR_NEED_MORE_DATA);
                }
                if (!@OCIExecute($stmt_pk, OCI_DEFAULT)) {
                    return $this->oci8RaiseError($stmt_pk);
                }
                if (@OCIFetch($stmt_pk) && @OCIResult($stmt_pk, 1)) {
                    $res[$i]['flags']  .= 'primary_key';
                }
                @OCIFreeStatement($stmt_pk);
                $res[$i]['flags'] = trim($res[$i]['flags']);

                if ($mode & DB_TABLEINFO_ORDER) {
                    $res['order'][$res[$i]['name']] = $i;
                }
                if ($mode & DB_TABLEINFO_ORDERTABLE) {
                    $res['ordertable'][$res[$i]['table']][$res[$i]['name']] = $i;
                }
                $i++;
            }

            if ($mode) {
                $res['num_fields'] = $i;
            }
            @OCIFreeStatement($stmt);

        } else {
            if (isset($result->result)) {
                /*
                 * Probably received a result object.
                 * Extract the result resource identifier.
                 */
                $result = $result->result;
            } else {
                /*
                 * ELSE, probably received a result resource identifier.
                 * Deprecated.  Here for compatibility only.
                 */
            }

            if ($result === $this->last_stmt) {
                $count = @OCINumCols($result);

                for ($i=0; $i<$count; $i++) {
                    $res[$i]['table'] = '';
                    $res[$i]['name']  = $case_func(@OCIColumnName($result, $i+1));
                    $res[$i]['type']  = @OCIColumnType($result, $i+1);
                    $res[$i]['len']   = @OCIColumnSize($result, $i+1);
                    $res[$i]['flags'] = '';

                    $res[$i]['type']  = preg_replace("/NUMBER/","DECIMAL",$res[$i]['type']);
                    $res[$i]['type']  = preg_replace("/LONG/","LONGTEXT",$res[$i]['type']);
                    $res[$i]['type']  = preg_replace("/CLOB/","LONGTEXT",$res[$i]['type']);

                    if ($mode & DB_TABLEINFO_ORDER) {
                        $res['order'][$res[$i]['name']] = $i;
                    }
                    if ($mode & DB_TABLEINFO_ORDERTABLE) {
                        $res['ordertable'][$res[$i]['table']][$res[$i]['name']] = $i;
                    }
                }

                if ($mode) {
                    $res['num_fields'] = $count;
                }

            } else {
                return $this->raiseError(DB_ERROR_NOT_CAPABLE);
            }
        }
        return $res;
    }

    // }}}
    // {{{ quoteIdentifier()

    /**
     * overrides DB_common::quoteIdentifier().
     *
     * Quote a string so it can be safely used as a table / column name
     *
     * Quoting style depends on which dbsyntax was passed in the DSN.
     *
     * Use 'mssql' as the dbsyntax in the DB DSN only if you've unchecked
     * "Use ANSI quoted identifiers" when setting up the ODBC data source.
     *
     * @param string $str  identifier name to be quoted
     *
     * @return string  quoted identifier string
     *
     * @since 1.6.0
     * @access public
     */
    function quoteIdentifier($str)
    {
        if (preg_match("/^\".*\"$/",$str)) return $str;
        return '"' . str_replace('"', '""', strtoupper($str)) . '"';
    }

    // }}}
    // {{{ updateBlobFile()
    
    /**
     * Usage:  store file pointed to by $val in a blob.
     *
     * @param string $table     tablename
     * @param string $column    columnname
     * @param string $val       filename
     * @param string $where     where clause
     * @param string $blobtype  
     *
     * @return string  quoted identifier string
     *
     * @access public
     */
     function updateBlobFile($table,$column,$val,$where,$blobtype='BLOB')
     {
        switch(strtoupper($blobtype)) {
            case 'BLOB': $type = OCI_B_BLOB; break;
            case 'CLOB': $type = OCI_B_CLOB; break;
            default: return $this->raiseError(DB_ERROR_SYNTAX);
        }
         
        $sql = "UPDATE $table SET $column=EMPTY_{$blobtype}() WHERE $where RETURNING $column INTO :blob";
        $stmt = $this->prepare($sql);
        $desc = OCINewDescriptor($this->connection, OCI_D_LOB);
         
        OCIBindByName($stmt, ":blob", &$desc, -1, $type);
        $success = @OCIExecute($stmt, OCI_DEFAULT);
        if (!$success) {
            return $this->oci8RaiseError($stmt);
        }
        
        if(!$desc->savefile($val)) {
            return $this->raiseError(DB_ERROR);
        }
        $desc->free();
        
        $tmp = DB_OK;
        if ($this->autocommit) {
            $tmp = $this->commit();
        }

        OCIFreeStatement($stmt);
        return $tmp;
     }

    // }}}
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */

?>
