<?php
require_once 'DB/odbc.php';

/**
 * SGL extension to maxdb driver
 *
 */
class DB_db2_SGL extends DB_odbc
{
    // {{{ constructor

    function DB_db2_SGL()
    {
        // call constructor of parent class
        $this->DB_odbc();
        
        // set type to db2
        $this->phptype = 'db2';
        
        // set special options
        $this->setOption('portability',DB_PORTABILITY_LOWERCASE);
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
            case 'sequences':
                return "SELECT lower(seqname) FROM syscat.sequences WHERE seqschema = '".strtoupper($this->dsn['username'])."' FOR READ ONLY";
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
            if (($row['TABLE_TYPE'] != $keep) || ($row['TABLE_SCHEM'] != strtoupper($this->dsn['username']))) {
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
            $id = @odbc_exec($this->connection, "SELECT tabname,colname,typename,length,nulls,keyseq FROM syscat.columns WHERE tabname = '".strtoupper($result)."' FOR READ ONLY");
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
                'table' => $got_string ? $case_func($result) : $aColumn[TABNNAME],
                'name'  => $case_func($aColumn['COLNAME']),
                'type'  => preg_replace("/CLOB/","LONGTEXT",$aColumn['TYPENAME']),
                'len'   => $aColumn['LENGTH'],
                'flags' => ($aColumn['NULLS'] == "Y") ? '' : 'not_null '
            );

            if ($aColumn['KEYSEQ'])
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
    
    // {{{ nextId()

    /**
     * Overrides DB_odbc::nextId
     *
     * @param string  $seq_name  name of the sequence
     * @param boolean $ondemand  when true, the seqence is automatically
     *                            created if it does not exist
     *
     * @return int  the next id number in the sequence.
     *               A DB_Error object on failure.
     *
     * @see DB_common::nextID(), DB_common::getSequenceName(),
     *      DB_db2_SGL::createSequence(), DB_db2_SGL::dropSequence()
     */
    function nextId($seq_name, $ondemand = true)
    {
        $seqname = $this->getSequenceName($seq_name);
        $repeat = 0;
        do {
            $this->expectError(DB_ERROR_NOSUCHTABLE);
            $result =& $this->query("SELECT NEXTVAL FOR ${seqname} FROM sysibm.sysdummy1");
            $this->popExpect();
            if ($ondemand && DB::isError($result) &&
                $result->getCode() == DB_ERROR_NOSUCHTABLE) {
                $repeat = 1;
                $result = $this->createSequence($seq_name);
                if (DB::isError($result)) {
                    return $this->raiseError($result);
                }
            } else {
                $repeat = 0;
            }
        } while ($repeat);
        if (DB::isError($result)) {
            return $this->raiseError($result);
        }
        $arr = $result->fetchRow(DB_FETCHMODE_ORDERED);

        return $arr[0];
    }

    /**
     * Overrides DB_odbc::createSequence
     *
     * @param string $seq_name  name of the new sequence
     *
     * @return int  DB_OK on success.  A DB_Error object on failure.
     *
     * @see DB_common::createSequence(), DB_common::getSequenceName(),
     *      DB_db2_SGL::nextID(), DB_db2_SGL::dropSequence()
     */
    function createSequence($seq_name)
    {
        return $this->query('CREATE SEQUENCE '
                            . $this->getSequenceName($seq_name));
    }

    // }}}
    // {{{ dropSequence()

    /**
     * Overrides DB_odbc::dropSequence
     *
     * @param string $seq_name  name of the sequence to be deleted
     *
     * @return int  DB_OK on success.  A DB_Error object on failure.
     *
     * @see DB_common::dropSequence(), DB_common::getSequenceName(),
     *      DB_odbc::nextID(), DB_odbc::createSequence()
     */
    function dropSequence($seq_name)
    {
        return $this->query('DROP SEQUENCE '
                            . $this->getSequenceName($seq_name));
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
    // {{{ prepare()

    /**
     * Prepares a query for multiple execution with execute()
     *
     * Creates a query that can be run multiple times.  Each time it is run,
     * the placeholders, if any, will be replaced by the contents of
     * execute()'s $data argument.
     *
     * Three types of placeholders can be used:
     *   + <kbd>?</kbd>  scalar value (i.e. strings, integers).  The system
     *                   will automatically quote and escape the data.
     *   + <kbd>!</kbd>  value is inserted 'as is'
     *   + <kbd>&</kbd>  requires a file name.  The file's contents get
     *                   inserted into the query (i.e. saving binary
     *                   data in a db)
     *
     * Example 1.
     * <code>
     * $sth = $db->prepare('INSERT INTO tbl (a, b, c) VALUES (?, !, &)');
     * $data = array(
     *     "John's text",
     *     "'it''s good'",
     *     'filename.txt'
     * );
     * $res = $db->execute($sth, $data);
     * </code>
     *
     * Use backslashes to escape placeholder characters if you don't want
     * them to be interpreted as placeholders:
     * <pre>
     *    "UPDATE foo SET col=? WHERE col='over \& under'"
     * </pre>
     *
     * With some database backends, this is emulated.
     *
     * {@internal ibase and oci8 have their own prepare() methods.}}
     *
     * @param string $query  the query to be prepared
     *
     * @return mixed  DB statement resource on success. A DB_Error object
     *                 on failure.
     *
     * @see DB_common::execute()
     */
    function prepare($query)
    {
        $tokens   = preg_split('/((?<!\\\)[&?!])/', $query, -1,
                               PREG_SPLIT_DELIM_CAPTURE);
        $token     = 0;
        $types     = array();
        $newtokens = array();

        foreach ($tokens as $val) {
            switch ($val) {
                case '?':
                    $types[$token++] = DB_PARAM_SCALAR;
                    break;
                case '&':
                    $types[$token++] = DB_PARAM_OPAQUE;
                    break;
                case '!':
                    $types[$token++] = DB_PARAM_MISC;
                    break;
                default:
                    $newtokens[] = preg_replace('/\\\([&?!])/', "\\1", $val);
            }
        }

        $this->last_query = $query;
        $query = $this->modifyQuery($query);
        if (!$stmt = @odbc_prepare($this->connection, $query)) {
            return $this->odbcRaiseError();
        }
        $this->prepare_types[(int)$stmt] = $types;
        $this->manip_query[(int)$stmt] = DB::isManip($query);
        return $stmt;
    }

    // }}}
    // {{{ execute()

    /**
     * Executes a DB statement prepared with prepare().
     *
     * To determine how many rows of a result set get buffered using
     * ocisetprefetch(), see the "result_buffering" option in setOptions().
     * This option was added in Release 1.7.0.
     *
     * @param resource  $stmt  a DB statement resource returned from prepare()
     * @param mixed  $data  array, string or numeric data to be used in
     *                      execution of the statement.  Quantity of items
     *                      passed must match quantity of placeholders in
     *                      query:  meaning 1 for non-array items or the
     *                      quantity of elements in the array.
     *
     * @return mixed  returns an oic8 result resource for successful SELECT
     *                queries, DB_OK for other successful queries.
     *                A DB error object is returned on failure.
     *
     * @see DB_oci8::prepare()
     */
    function &execute($stmt, $data = array())
    {
        $data = (array)$data;
        $this->last_parameters = $data;
        $this->_data = $data;

        $types =& $this->prepare_types[(int)$stmt];
        if (count($types) != count($data)) {
            $tmp =& $this->raiseError(DB_ERROR_MISMATCH);
            return $tmp;
        }

        $i = 0;
        foreach ($data as $key => $value) {
            if ($types[$i] == DB_PARAM_MISC) {
                /*
                 * Oracle doesn't seem to have the ability to pass a
                 * parameter along unchanged, so strip off quotes from start
                 * and end, plus turn two single quotes to one single quote,
                 * in order to avoid the quotes getting escaped by
                 * Oracle and ending up in the database.
                 */
                $data[$key] = preg_replace("/^'(.*)'$/", "\\1", $data[$key]);
                $data[$key] = str_replace("''", "'", $data[$key]);
            } elseif ($types[$i] == DB_PARAM_OPAQUE) {
                $fp = @fopen($data[$key], 'rb');
                if (!$fp) {
                    $tmp =& $this->raiseError(DB_ERROR_ACCESS_VIOLATION);
                    return $tmp;
                }
                $data[$key] = fread($fp, filesize($data[$key]));
                fclose($fp);
            }
            $i++;
        }
        $success = @odbc_execute($stmt, $data);
        if (!$success) {
            $tmp = $this->odbcRaiseError($stmt);
            return $tmp;
        }
        $this->last_stmt = $stmt;
        if ($this->manip_query[(int)$stmt]) {
            $tmp = DB_OK;
        } else {
            $tmp =& new DB_result($this, $stmt);
        }
        return $tmp;
    }

    // }}}
}

?>
