<?php

require_once 'DB/DataObject.php';

require_once 'LUAdmin.php';

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
        $this->module       = 'liveuser';
        $this->pageTitle    = 'Liveuser User Groups Manager';

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
        
        $input->user_id = $req->get('user_id');
        
        $input->groupsToAdd      = $req->get('AddfrmUserGroups');
        $input->groupsToRemove   = $req->get('RemovefrmUserGroups');
    }
    
    // groups
    function _editGroups(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'luUserEditGroups.html';
        $output->pageTitle = $this->pageTitle . ' :: Groups';
        
        $user = &DB_DataObject::factory('usr');
        $ret = $user->get($input->user_id);
        if(!$ret || PEAR::isError($ret)) {
            LUAdmin::noRecordRedirect();
        }
        $output->user = &$user;
        
        //  get set of groups associated with user
        $aUserGroups = $this->getGroupsDetailsByUserId($input->user_id);
        asort($aUserGroups);
        $output->userGroupsOptions = SGL_Output::generateSelect($aUserGroups);

        //  get remaining groups
        $aRemainingGroups = $this->retrieveAllOthersGroups($aUserGroups);
        asort($aRemainingGroups);
        $output->remainingGroupsOptions = SGL_Output::generateSelect($aRemainingGroups);
    }
    
    function _updateGroups(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $aGroupsToAdd    = LUAdmin::parseWidgetString($input->groupsToAdd);
        $aGroupsToRemove = LUAdmin::parseWidgetString($input->groupsToRemove);
        
        if (is_array($aGroupsToAdd) && count($aGroupsToAdd)) {
            $this->_changeGroupsAssignments($aGroupsToAdd, $input->user_id, SGL_LIVEUSER_ADD);
        }
        if (is_array($aGroupsToRemove) && count($aGroupsToRemove)) {
            $this->_changeGroupsAssignments($aGroupsToRemove, $input->user_id, SGL_LIVEUSER_REMOVE);
        }
        SGL::raiseMsg('user assignments successfully updated');
        SGL_HTTP::redirect('userMgr.php', array('action' => 'list')); 
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
    function _changeGroupsAssignments($aGroups, $userId, $action)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $dbh = & SGL_DB::singleton();
        
        if ($action == SGL_LIVEUSER_REMOVE) {
            foreach ($aGroups as $groupId => $name) {
                $dbh->query("   DELETE FROM liveuser_groupusers
                                WHERE   perm_user_id = $userId
                                AND     group_id = $groupId");
            }
        } else {
            //  add groups
            foreach ($aGroups as $groupId => $name) {
                $dbh->query("   INSERT INTO liveuser_groupusers
                                (perm_user_id, group_id)
                                VALUES ($userId, $groupId)");
            }
        }
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
            SELECT  gu.group_id, g.name
            FROM    liveuser_groupusers gu, liveuser_groups g
            WHERE   gu.group_id = g.group_id
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
            SELECT  g.group_id, g.name
            FROM    liveuser_groups g';
        if (count($aGroups) > 0) {
            $query .= " WHERE $whereClause";
        }
        $dbh = & SGL_DB::singleton();
        $aOthers = $dbh->getAssoc($query);
        return $aOthers;
    }
    
}
?>