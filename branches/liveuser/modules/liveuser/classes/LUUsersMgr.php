<?php
require_once SGL_ENT_DIR . '/Usr.php';
require_once SGL_MOD_DIR . '/liveuser/classes/LUAdmin.php';

define('SGL_LIVEUSER_ADD', 1);
define('SGL_LIVEUSER_REMOVE', 2);

/**
 * To allow administrate the liveuser users and groups
 *
 * @package liveuser
 */
class LUUsersMgr extends SGL_Manager
{
    function LUUsersMgr()
    {        
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();        
        $this->module       = 'liveuser';
        $this->pageTitle    = 'Group Manager';

        $this->_aActionsMapping =  array(
            'editGroups'    => array('editGroups'),
            'updateGroups'  => array('updateGroups'),
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
        $input->action      = $req->get('action');
        $input->submit      = $req->get('submitted');
        $input->user_id     = $req->get('user_id');
        $input->groupsToAdd      = $req->get('AddfrmUserGroups');
        $input->groupsToRemove   = $req->get('RemovefrmUserGroups');
    }
    
    // groups
    function _cmd_editGroups(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'luUserEditGroups.html';
        $output->pageTitle = $this->pageTitle . ' :: Change assignments';
        
        $user = & new DataObjects_Usr;
        $ret = $user->get($input->user_id);
        if (!$ret || PEAR::isError($ret)) {
            LUAdmin::noRecordRedirect();
        }
        $output->user = $user;
        
        //  get set of groups associated with user
        $aUserGroups = $this->getGroupsDetailsByUserId($input->user_id);
        if (PEAR::isError($aUserGroups)) {
            SGL::raiseError("Error getting groups");
        }
        asort($aUserGroups);
        $output->userGroupsOptions = SGL_Output::generateSelect($aUserGroups);

        //  get remaining groups
        $aRemainingGroups = $this->retrieveAllOthersGroups($aUserGroups);
        if (PEAR::isError($aRemainingGroups)) {
            SGL::raiseError("Error getting groups");
        }        
        asort($aRemainingGroups);
        $output->remainingGroupsOptions = SGL_Output::generateSelect($aRemainingGroups);
    }
    
    function _cmd_updateGroups(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $aGroupsToAdd    = LUAdmin::parseWidgetString($input->groupsToAdd);
        $aGroupsToRemove = LUAdmin::parseWidgetString($input->groupsToRemove);
        
        $result = true;
        if (is_array($aGroupsToAdd) && count($aGroupsToAdd)) {
            $result = $this->_cmd_changeGroupsAssignments($aGroupsToAdd, $input->user_id, SGL_LIVEUSER_ADD);
        }
        if (is_array($aGroupsToRemove) && count($aGroupsToRemove)) {
            $result = $this->_cmd_changeGroupsAssignments($aGroupsToRemove, $input->user_id, SGL_LIVEUSER_REMOVE);
        }
        
        if ($result) {
            SGL::raiseMsg('user assignments successfully updated', false, SGL_MESSAGE_INFO);
            SGL_HTTP::redirect(array('moduleName'=>'user','managerName'=>'user','action' => 'list'));         
        } else {
            SGL::raiseError("Error updating user permissions");        
        }
    }

    /**
     * Updates Db with new assignments.
     *
     * @access  private
     * @param   array       $aGroups array of groups to add/remove
     * @param   string      $userId  user ID to associate groups with
     * @param   constant    action   whether to add/remove group
     * @return  void
     */
    function _cmd_changeGroupsAssignments($aGroups, $userId, $action)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $dbh = & SGL_DB::singleton();
        
        if ($action == SGL_LIVEUSER_REMOVE) {
            foreach ($aGroups as $groupId => $name) {
                $dbh->query("   DELETE FROM liveuser_groupusers
                                WHERE   perm_user_id = $userId
                                AND     group_id = $groupId");                                
                if (PEAR::isError($dbh)) {
                    return false;
                }                                
            }
            
        } else {
            //  add groups
            foreach ($aGroups as $groupId => $name) {
                $dbh->query("   INSERT INTO liveuser_groupusers
                                (perm_user_id, group_id)
                                VALUES ($userId, $groupId)");
                if (PEAR::isError($dbh)) {
                    return false;
                }                                                
            }
        }
        return true;
    }

    /**
     * Returns assoc array of all groups per given user id.
     *
     * @access  public
     * @param   int     $userId   id of target user
     * @return  array   $data     array of groups returned
     * @see     retrieveAllOthersGroups()
     */
    function getGroupsDetailsByUserId($userId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $query = "
            SELECT  gu.group_id, lt.name
            FROM    liveuser_groups g, liveuser_groupusers gu
            LEFT JOIN liveuser_translations lt ON lt.section_id = gu.group_id     
            WHERE   gu.group_id = g.group_id
            AND     section_type = ".LIVEUSER_SECTION_GROUP."                    
            AND     gu.perm_user_id = $userId
            ";
        
        $dbh = & SGL_DB::singleton();
        $data = $dbh->getAssoc($query);
        return $data;
    }

    /**
     * Like a 'difference' operation, returns the balance of getPermsDetailsByRightId
     *
     * returns an assoc array of all permissions which are not in getPermsDetailsByRightId()
     *
     * @access  public
     * @param   array   $aGroupRights     hash of perms to exclude
     * @return  array   $aOthersRights    array of perms returned
     * @see     getRightsDetailsByGroupId()
     */
    function retrieveAllOthersGroups($aGroups)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (count($aGroups) > 0) {
            $whereClause = '';
            foreach ($aGroups as $key => $value) {
                $whereClause .= " $key NOT IN (g.group_id) AND ";
            }
            $whereClause = substr($whereClause, 0, -4);
        }
        $query = '
            SELECT  g.group_id, lt.name
            FROM    liveuser_groups g
            LEFT JOIN liveuser_translations lt ON lt.section_id = g.group_id';
                                
        if (count($aGroups) > 0) {
            $query .= " WHERE $whereClause";
        }
        $dbh = & SGL_DB::singleton();
        $aOthers = $dbh->getAssoc($query);
        return $aOthers;
    }
    
}
?>