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
		        'type'  => $aColumn['TYPENAME'],
		        'len'   => $aColumn['LENGTH'],
		        'flags'	=> ($aColumn['NULLS'] == "Y") ? '' : 'not_null '
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

}

?>
