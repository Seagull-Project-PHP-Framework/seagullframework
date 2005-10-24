<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | SYSTEM LACINSKI 2.0                                                       |
// +---------------------------------------------------------------------------+
// |VarLibDB.php                                                               |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005 Varico.com                                             |
// |                                                                           |
// | Author: Varico <varico@varico.com>                                        |
// +---------------------------------------------------------------------------+
// |                                                                           |
// |Varico Lincence                                                            |
// |                                                                           |
// +---------------------------------------------------------------------------+

/* VarLibDB 
 * This class contains all DB methods necessary in S�2.0 
 *
 */
 
 class VarLibDB {
    
    function varLibDB() {
        
    }
    
    /** 
     * autoExecuteInsert
     * @param string $tableName 
     * @param string $primaryKey
     * @param string $tableData 
     * @param string $msg
     * @return integer $nextId id number added record
     */
    function autoExecuteInsert($tableName, $primaryKey, $tableData, $msg='') {
        $dbh = & SGL_DB::singleton();
        $nextId = $dbh->nextId($tableName);
        $tableData['date_add'] = SGL::getTime(true);
        $tableData['user_add'] = SGL_HTTP_Session::getUserType();
        $tableData[$primaryKey] = $nextId;
        $sth = $dbh->autoExecute($tableName, $tableData, DB_AUTOQUERY_INSERT);
        if($sth == 1) {
            SGL::raiseMsg($msg);
        }
        else {
            SGL::raiseMsg('There was a problem to insert record');
            SGL::raiseError('There was a problem to insert record', SGL_ERROR_NOAFFECTEDROWS);
        }   
        return $nextId;
    }   
    
     /** 
     * autoExecuteUpdate
     * @param string $tableName 
     * @param string $primaryKey
     * @param string $tableData 
     * @param integer $id
     * @param string $msg
     */
    function autoExecuteUpdate($tableName, $primaryKey, $tableData, $id, $msg='') {
        $dbh = & SGL_DB::singleton();
        $tableData['date_modify'] = SGL::getTime(true);
        $tableData['user_modify'] = SGL_HTTP_Session::getUserType();
        
        $tabDef = explode(',', REGISTER_HISTORY_TABLE);
        if (in_array($tableName, $tabDef)) {
            VarLibDB::historyRegister($tableName, $primaryKey, $tableData, $id);
        }
        $sth = $dbh->autoExecute($tableName, $tableData, DB_AUTOQUERY_UPDATE, 
                                $primaryKey . '=' . $id);
        if($sth == 1) {
            SGL::raiseMsg($msg);
        }
        else {
            SGL::raiseMsg('There was a problem to update record');
            SGL::raiseError('update record failed', SGL_ERROR_NOAFFECTEDROWS);
        }      
    }
    
    /**
     * This function registry history 
     * @param string $tableName 
     * @param string $primaryKey
     * @param string $tableData 
     * @param integer $id
     * @return
    **/
    function historyRegister($tableName, $primaryKey, $tableData, $id) {
        $dbh = & SGL_DB::singleton();
        $dbh->setFetchMode(DB_FETCHMODE_ASSOC);
        
        $dateModify = SGL::getTime(true);
        $userModify = SGL_HTTP_Session::getUserType();
        
        $oldValues = $dbh->getRow("SELECT * FROM $tableName WHERE $primaryKey = $id");
        unset($oldValues['date_modify']);
        
        $newValues = $tableData;
        $diffValues = array();
        foreach ($oldValues as $name => $value) {
            if (isset($oldValues[$name]) && isset($newValues[$name]) && $oldValues[$name] != $newValues[$name]) {
                $diffValues[] = $name;
            }
        }
        //insert history data
        if (count($diffValues) > 0) {
            foreach ($diffValues as $fieldName) {
                $nextId = $dbh->nextId('v_history');
                $oldId = 0;
                //set old value name for special field
                switch ($fieldName) {
                    case 'event_status_id':
                        $oldId = $oldValues[$fieldName];
                        $sql ="SELECT @name@ FROM v_event_status WHERE event_status_id = $oldId";
                        $oldValues[$fieldName] = $dbh->getOne($sql);
                        break;
                    case 'user_id':
                        $oldId = $oldValues[$fieldName];
                        $sql = "SELECT first_name || ' ' || last_name FROM usr WHERE usr_id = $oldId";
                        $oldValues[$fieldName] = $dbh->getOne($sql);
                        break;
                    case 'event_category_id':
                        $oldId = $oldValues[$fieldName];
                        $sql = "SELECT @name@ FROM v_event_category WHERE event_category_id = $oldId";
                        $oldValues[$fieldName] = $dbh->getOne($sql);
                        break;
                    case 'information_source_id':
                        $oldId = $oldValues[$fieldName];
                        $sql = "SELECT @name@ FROM v_information_source WHERE information_source_id = $oldId";
                        $oldValues[$fieldName] = $dbh->getOne($sql);
                        break;
                    case 'position_id':
                        $oldId = $oldValues[$fieldName];
                        $sql = "SELECT @name@ FROM v_position WHERE position_id = $oldId";
                        $oldValues[$fieldName] = $dbh->getOne($sql);
                        break;
                    case 'role':
                        $oldId = $oldValues[$fieldName];
                        $sql = "SELECT name FROM role WHERE role_id = $oldId";
                        $oldValues[$fieldName] = $dbh->getOne($sql);
                        break;
                }
                
                $sql = "INSERT INTO v_history 
                        VALUES ($nextId, $id, '$tableName', '" . _t($fieldName) . "', '$oldValues[$fieldName]', 
                                    '$oldId', '$dateModify', '$userModify')";
                $dbh->query($sql);
            }
        }
    }
    
    /** 
     * autoExecuteDelete - set hidden to true
     * @param string $tableName 
     * @param string $primaryKey
     * @param integer $id
     * @param string $where
     * @param string $msg
     */
    function autoExecuteDelete($tableName, $primaryKey, $id, $where = '', $msg='')
    {
        $dbh = & SGL_DB::singleton();
        $tableData['date_modify'] = SGL::getTime(true);
        $tableData['user_modify'] = SGL_HTTP_Session::getUserType();
        $tableData['hidden'] = 1;
        
        if ($where <> '') {
            $where = "AND $where";
        }
        $conf = & $GLOBALS['_SGL']['CONF'];
        // safe delete set only hidden variable true
        if ($conf['site']['safeDelete'] == 1) {
            $sth = $dbh->autoExecute($tableName, $tableData, DB_AUTOQUERY_UPDATE, 
                $primaryKey . '=' . $id . ' ' . $where);
        // delete record from db        
        } else {
            $query = "DELETE FROM $tableName WHERE $primaryKey = $id $where";
            $sth = $dbh->query($query);
        }
        if (($sth == 1) || ($sth == '')) {
            SGL::raiseMsg($msg);
        } else {
            SGL::raiseMsg('There was a problem to delete record');
            SGL::raiseError('Delete record failed', SGL_ERROR_NOAFFECTEDROWS);
        }
    }
}
?>