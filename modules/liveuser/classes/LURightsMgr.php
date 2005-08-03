<?php
require_once 'LUAdmin.php';
require_once 'DB/DataObject.php';

define('SGL_LIVEUSER_PERM_ADD', 1);
define('SGL_LIVEUSER_PERM_REMOVE', 2);

/**
 * To allow administrate the liveuser rights
 *
 * @package liveuser
 */
class LURightsMgr extends SGL_Manager
{
    function LURightsMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->module       = 'liveuser';
        $this->pageTitle    = 'Liveuser Rights Manager';
        $this->template     = 'luRightsList.html';

        $this->_aActionsMapping =  array(
            'add'       => array('add'), 
            'insert'    => array('insert', 'redirectToDefault'),
            'edit'      => array('edit'), 
            'update'    => array('update', 'redirectToDefault'), 
            'delete'    => array('delete', 'redirectToDefault'), 
            'list'      => array('list'),
            'editPerms' => array('editPerms'),
            'updatePerms' => array('updatePerms', 'redirectToDefault'),
        );
        
        $this->masterTemplate  = 'masterMinimal.html';
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template    = $this->template;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        
        $input->submit      = $req->get('submitted');
        
        $input->right    = $req->get('right');
        $input->right_id = $req->get('right_id');
        $input->aDelete  = $req->get('frmDelete');
        
        $input->permsToAdd      = $req->get('AddfrmRightPerms');
        $input->permsToRemove   = $req->get('RemovefrmRightPerms');
            
        if ($input->submit && ($input->action == 'insert' || $input->action == 'update')) {
            if(empty($input->right['name'])) {
                $aErrors['name'] = 'You must enter a name';
            }
            if(empty($input->right['right_define_name'])) {
                $aErrors['right_define_name'] = 'You must enter a "define name"';
            }
            if(empty($input->right['description'])) {
                $aErrors['description'] = 'You must enter a description';
            }
        }
        
        //  if errors have occured
        if (!empty($aErrors)) {
            $input->template = 'luRightEdit.html';
            
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }
    }
    
    function _add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $output->template = 'luRightEdit.html';
        $output->action = 'insert';
    }

    function _insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $admin = &LUAdmin::singleton();
        
        $data = $this->_buildRightData($input);
        $rightId = $admin->perm->addRight($data);
        if ($rightId === false) {
              SGL::raiseError('Error on line: '.__LINE__.' last query: '.$admin->perm->_storage->dbc->last_query, 
                  SGL_ERROR_DBFAILURE);
        } else {
            LUAdmin::rebuildRightsConstants();
            
            SGL::raiseMsg('Right was successfully added');
        }
    }

    function _edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        if (empty($input->right_id)) {
            LUAdmin::noRecordRedirect();
        }
        
        $output->template = 'luRightEdit.html';
        $output->action = 'update';
        
        $params['filters'] = array('right_id' => $input->right_id);
        
        $admin = &LUAdmin::singleton();
        $rights = &$admin->perm->getRights($params);
        
        if(empty($rights[$input->right_id])) {
            LUAdmin::noRecordRedirect();
        }
        
        $output->right = &$rights[$input->right_id];
    }

    function _update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        if(empty($input->right_id)) {
            LUAdmin::noRecordRedirect();
        }
        
        $admin = &LUAdmin::singleton();
        
        $data = $this->_buildRightData($input);
        $filters = array('right_id' => $input->right_id);
        
        $upRight = $admin->perm->updateRight($data, $filters); 
        
        if ($upRight === false) {
            SGL::raiseError('Error on line: '.__LINE__.', error: '.LUAdmin::errorToString($admin->getErrors()), 
                SGL_ERROR_DBFAILURE);
        } else {
            LUAdmin::rebuildRightsConstants();
            
            SGL::raiseMsg('Right was successfully updated');
        }
    }

    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $output->template = 'luRightsList.html';
        
        $admin = &LUAdmin::singleton();
        $output->rights = &$admin->perm->getRights();
    }

    function _delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        if (is_array($input->aDelete)) {
            $admin = &LUAdmin::singleton();
            
            foreach ($input->aDelete as $rightId) {
                $filters = array('right_id' => $rightId);
                $rmRight = $admin->perm->removeRight($filters);
                if ($rmRight === false) {
                    SGL::raiseError('Error on line: '.__LINE__.', error: '.LUAdmin::errorToString($admin->getErrors()), 
                        SGL_ERROR_DBFAILURE);
                } else {
                    // also delete the associated records from right_permission table
                    $rightPermission = &DB_DataObject::factory('right_permission');
                    $rightPermission->right_id = $rightId;
                    $ret = true;
                    if($rightPermission->find()) {
                        $ret = $rightPermission->delete();
                    }
                    if(!$ret || PEAR::isError($ret)) {
                        Base::raiseError('There was a problem deleting the record '
                            . __FILE__ . ':' . __LINE__, 
                            SGL_ERROR_NOAFFECTEDROWS);
                    }
                }
            }
            SGL::raiseMsg('Right(s) was successfully deleted');
        } else {
            SGL::raiseError('Incorrect parameter passed to ' . 
                __CLASS__ . '::' . __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
    }
    
    function _editPerms(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'luRightEditPerms.html';
        $output->pageTitle = $this->pageTitle . ' :: Permissions';
        
        $params['filters'] = array('right_id' => $input->right_id);
        $admin = &LUAdmin::singleton();
        $rights = &$admin->perm->getRights($params);
        if(empty($rights[$input->right_id])) {
            LUAdmin::noRecordRedirect();
        }
        $output->right = (object) $rights[$input->right_id];
        
        //  get set of perms associated with role
        $aRightPerms = $this->getPermsDetailsByRightId($output->right->right_id);
        asort($aRightPerms);
        $output->rightPermOptions = SGL_Output::generateSelect($aRightPerms);

        //  get remaining perms
        $aRemainingPerms = $this->retrieveAllOthers($aRightPerms);
        asort($aRemainingPerms);
        $output->remainingPermOptions = SGL_Output::generateSelect($aRemainingPerms);
    }
    
    function _updatePerms(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aPermsToAdd     = LUAdmin::parseWidgetString($input->permsToAdd);
        $aPermsToRemove  = LUAdmin::parseWidgetString($input->permsToRemove);
        if (is_array($aPermsToAdd) && count($aPermsToAdd)) {
            $this->_changeRightsAssignments($aPermsToAdd, $input->right_id, SGL_LIVEUSER_PERM_ADD);
        }
        if (is_array($aPermsToRemove) && count($aPermsToRemove)) {
            $this->_changeRightsAssignments($aPermsToRemove, $input->right_id, SGL_LIVEUSER_PERM_REMOVE);
        }
        SGL::raiseMsg('right assignments successfully updated');
    }

    /**
     * Updates Db with new assignments.
     *
     * @access  private
     * @param   array       $aPerms array of perms to add/remove
     * @param   string      $roleId role ID to associate permissions with
     * @param   constant    action  whether to add/remove perm
     * @return  void
     */
    function _changeRightsAssignments($aPerms, $rightId, $action)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        $dbh = & SGL_DB::singleton();
        if ($action == SGL_LIVEUSER_PERM_REMOVE) {
            foreach ($aPerms as $permId => $permName) {
                $dbh->query("   DELETE FROM right_permission
                                WHERE   permission_id = $permId
                                AND     right_id = $rightId");
            }
        } else {
            //  add perms
            foreach ($aPerms as $permId => $permName) {
                $dbh->query("   INSERT INTO right_permission
                                (right_permission_id, right_id, permission_id)
                                VALUES (" . $dbh->nextId('right_permission') . ", $rightId, $permId)");
            }
        }
    }

    /**
     * Returns assoc array of all perms per given right id.
     *
     * @access  public
     * @param   int     $rightId         id of target right
     * @return  array   $aRightPerms     array of perms returned
     * @see     retrieveAllOthers()
     */
    function getPermsDetailsByRightId($rightId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        $query = "
            SELECT  rp.permission_id, p.name
            FROM    right_permission rp, permission p
            WHERE   rp.permission_id = p.permission_id
            AND     right_id = $rightId
            ";
        $dbh = &SGL_DB::singleton();
        $aRightPerms = $dbh->getAssoc($query);
        return $aRightPerms;
    }

    /**
     * Like a 'difference' operation, returns the balance of getPermsDetailsByRightId
     *
     * returns an assoc array of all permissions which are not in getPermsDetailsByRightId()
     *
     * @access  public
     * @param   array   $aRightPerms     hash of perms to exclude
     * @return  array   $aOthersPerms      array of perms returned
     * @see     getPermsDetailsByRightId()
     */
    function retrieveAllOthers($aRightPerms)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (count($aRightPerms) > 0) {
            $whereClause = '';
            foreach ($aRightPerms as $key => $value) {
                $whereClause .= " $key NOT IN (p.permission_id) AND ";
            }
            $whereClause = substr($whereClause, 0, -4);
        }
        $query = '
            SELECT  p.permission_id, p.name
            FROM    permission p';
        if (count($aRightPerms) > 0)
            $query .= " WHERE $whereClause";
        $dbh = & SGL_DB::singleton();
        $aOthersPerms = $dbh->getAssoc($query);
        return $aOthersPerms;
    }
    
    /**
     * Build array with data for LiveUser_Admin package methods
     *
     * @access  private
     * @param   object $input  seagull input
     * @return  array $data
     */
    function _buildRightData(&$input)
    {
        $data = array(
            'area_id' => OPC_DEFAULT_AREA,
            'right_define_name' => LUAdmin::convertToConstant($input->right['right_define_name']),
            'name' => $input->right['name'],
            'description' => $input->right['description'],
        );
        
        return $data;
    }
     
}
?>