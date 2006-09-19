<?php
require_once SGL_ENT_DIR . '/Right_permission.php';
require_once SGL_MOD_DIR . '/liveuser/classes/LUAdmin.php';

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
        parent::SGL_Manager();
        $this->module       = 'liveuser';
        $this->pageTitle    = 'Rights Manager';
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
    
    function _cmd_add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $output->template = 'luRightEdit.html';
        $output->action = 'insert';
    }

    function _cmd_insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $admin = &LUAdmin::singleton();
        
        $data = $this->_cmd_buildRightData($input);
        $output->rightId = $admin->perm->addRight($data);
        if ($output->rightId === false) {
             LUAdmin::raiseError($admin);                     
        } else {
            $translation = $this->_cmd_buildRightTranslationData($input,$output->rightId);
            $result = $admin->perm->addTranslation($translation);
            if ($result === false) {
                LUAdmin::raiseError($admin);
            } else {            
            
//  TODO: check this out!
//                LUAdmin::rebuildRightsConstants();            
                SGL::raiseMsg('Right was successfully added', true, SGL_MESSAGE_INFO);
                
            }
        }
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
              
        $output->template = 'luRightEdit.html';
        $output->action = 'update';
        
        $params['fields'] = array('right_id', 'right_define_name', 'name', 'description');
        $params['filters'] = array('language_id' => SGL_Translation::getLangId(), 'right_id' => $input->right_id);        
              
        $admin = &LUAdmin::singleton();
        $rights = $admin->perm->getRights($params);
       
        if (empty($rights[0]['right_id'])) {
            LUAdmin::noRecordRedirect();
        }
        
        $output->right = &$rights[0];
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        if (empty($input->right_id)) {
            LUAdmin::noRecordRedirect();
        }
        
        $admin = &LUAdmin::singleton();
        
        $data = $this->_cmd_buildRightData($input);
        $filters = array('right_id' => $input->right_id);
        
        $upRight = $admin->perm->updateRight($data, $filters);

        if ($upRight === false) {
            LUAdmin::raiseError($admin);        
        } else {
            $translation = $this->_cmd_buildRightTranslationData($input,$upRight);
            $filters = array('section_id' => $input->right_id, 'section_type' => LIVEUSER_SECTION_RIGHT);
            $translation['section_id'] = $input->right_id;                  
            $result = $admin->perm->updateTranslation($translation, $filters);
            if ($result === false) {
                LUAdmin::raiseError($admin);
            } else {            
            
//  TODO: check this out
//                LUAdmin::rebuildRightsConstants();            
                SGL::raiseMsg('Right was successfully updated', true, SGL_MESSAGE_INFO);
                
            }
        }         
        
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $output->template = 'luRightsList.html';
        
        $admin = &LUAdmin::singleton();
        $params = array();
        $params['fields'] = array('right_id', 'right_define_name', 'name', 'description');
        $params['filters'] = array('language_id' => SGL_Translation::getLangId());        
        $output->rights = $admin->perm->getRights($params);
        $output->addOnLoadEvent("document.getElementById('frmUserMgrChooser').rights.disabled = true");        
    }

    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        if (is_array($input->aDelete)) {
            $admin = &LUAdmin::singleton();
            
            foreach ($input->aDelete as $rightId) {
                $filters = array('right_id' => $rightId);
                $rmRight = $admin->perm->removeRight($filters);
                if ($rmRight === false) {
                    LUAdmin::raiseError($admin);
                } else {
                    // also delete the associated records from right_permission table
                    $rightPermission = & new DataObjects_Right_permission();
                    $rightPermission->right_id = $rightId;
                    $ret = true;
                    if($rightPermission->find()) {
                        $ret = $rightPermission->delete();
                    }
                    if(!$ret || PEAR::isError($ret)) {
                        SGL::raiseError('There was a problem deleting the record '
                            . __FILE__ . ':' . __LINE__, 
                            SGL_ERROR_NOAFFECTEDROWS);
                    } else {
                        $filters = array('section_id' => $rightId, 'section_type' => LIVEUSER_SECTION_RIGHT);
                        $result = $admin->perm->removeTranslation($filters);
                        if ($result === false) {
                            LUAdmin::raiseError($admin);                        
                        } else {
                            SGL::raiseMsg('Right(s) was successfully deleted', true, SGL_MESSAGE_INFO);                    
                        }     
                    
                    }
                    
                }
            }

        } else {
            SGL::raiseError('Incorrect parameter passed to ' . 
                __CLASS__ . '::' . __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
    }
    
    function _cmd_editPerms(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'luRightEditPerms.html';
        $output->pageTitle = $this->pageTitle . ' :: Permissions';
        
        $params['filters'] = array('right_id' => $input->right_id);
        $admin = &LUAdmin::singleton();
        $rights = $admin->perm->getRights($params);
        if ($rights[0]['right_id']!=$input->right_id) {
            LUAdmin::noRecordRedirect();
        }
        $output->right = (object) $rights[0];
        
        //  get set of perms associated with role
        $aRightPerms = $this->getPermsDetailsByRightId($input->right_id);
        if (PEAR::isError($aRightPerms)) {
            SGL::raiseError("Error getting Permissions");
            return null; 
        }
        asort($aRightPerms);
        $output->rightPermOptions = SGL_Output::generateSelect($aRightPerms);

        //  get remaining perms
        $aRemainingPerms = $this->retrieveAllOthers($aRightPerms);
        if (PEAR::isError($aRemainingPerms)) {
            SGL::raiseError("Error getting Permissions");
            return null; 
        }        
        asort($aRemainingPerms);
        $output->remainingPermOptions = SGL_Output::generateSelect($aRemainingPerms);
    }
    
    function _cmd_updatePerms(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aPermsToAdd     = LUAdmin::parseWidgetString($input->permsToAdd);       
        $aPermsToRemove  = LUAdmin::parseWidgetString($input->permsToRemove);
        $result = true;
        if (is_array($aPermsToAdd) && count($aPermsToAdd)) {             
            $result = $this->_cmd_changeRightsAssignments($aPermsToAdd, $input->right_id, SGL_LIVEUSER_PERM_ADD);
        }
        if (is_array($aPermsToRemove) && count($aPermsToRemove)) {
            $result = $this->_cmd_changeRightsAssignments($aPermsToRemove, $input->right_id, SGL_LIVEUSER_PERM_REMOVE);
        }
        if ($result) {
            SGL::raiseMsg('right assignments successfully updated', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError("Error inserting Permissions to Rights link");            
        }
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
    function _cmd_changeRightsAssignments($aPerms, $rightId, $action)
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
            if (PEAR::isError($dbh)) {               
                return false;
            }               
        } else {
            //  add perms
            foreach ($aPerms as $permId => $permName) {             
                $dbh->query("   INSERT INTO right_permission
                                (right_permission_id, right_id, permission_id)
                                VALUES (" . $dbh->nextId('right_permission') . ", $rightId, $permId)");                               
                   
                if (PEAR::isError($dbh)) {               
                    return false;
                }   
          
            }
        }
        return true;
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
    function _cmd_buildRightData(&$input)
    {
        $data = array(
            'area_id' => SEAGULL_DEFAULT_AREA,
            'right_define_name' => LUAdmin::convertToConstant($input->right['right_define_name']),
        );                
        return $data;
    }
    
    
    /**
     * Build array with data for LiveUser transltion
     *
     * @access  private
     * @param   object $input  seagull input
     * @param   int    $right_id  id of translation
     * @return  array $data
     */        
    function _cmd_buildRightTranslationData(&$input,$right_id) {

        $data = array (
            'section_type' => LIVEUSER_SECTION_RIGHT,
            'section_id' => $right_id,
            'language_id' => SGL_Translation::getLangId(),
            'name' => $input->right['name'],
            'description' => $input->right['description'],        
        );
        return $data;
    }
    
}
?>