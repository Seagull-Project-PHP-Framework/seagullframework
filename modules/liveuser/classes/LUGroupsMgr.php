<?php

require_once 'DB/DataObject.php';
require_once 'LUAdmin.php';

define('SGL_LIVEUSER_RIGHT_ADD', 1);
define('SGL_LIVEUSER_RIGHT_REMOVE', 2);

/**
 * To allow administrate the liveuser groups
 *
 * @package liveuser
 */
class LUGroupsMgr extends SGL_Manager
{
    function LUGroupsMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->module       = 'liveuser';
        $this->pageTitle    = 'Liveuser Groups Manager';
        $this->template     = 'luGroupsList.html';

        $this->_aActionsMapping =  array(
            'add'           => array('add'), 
            'insert'        => array('insert', 'redirectToDefault'),
            'edit'          => array('edit'), 
            'update'        => array('update', 'redirectToDefault'), 
            'delete'        => array('delete', 'redirectToDefault'), 
            'list'          => array('list'),
            'editRights'    => array('editRights'),
            'updateRights'  => array('updateRights', 'redirectToDefault'),
            'editMembers'   => array('editMembers'),
            'updateMembers' => array('updateMembers', 'redirectToDefault'),
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
        
        $input->group    = $req->get('group');
        $input->group_id = $req->get('group_id');
        $input->aDelete  = $req->get('frmDelete');
        
        $input->rightsToAdd      = $req->get('AddfrmGroupRights');
        $input->rightsToRemove   = $req->get('RemovefrmGroupRights');
        
        $input->membersToAdd      = $req->get('AddfrmGroupMembers');
        $input->membersToRemove   = $req->get('RemovefrmGroupMembers');
            
        if($input->submit && ($input->action == 'insert' || $input->action == 'update')) {
            if(empty($input->group['name'])) {
                $aErrors['name'] = 'You must enter a name';
            }
            /*
            if(empty($input->group['group_define_name'])) {
                $aErrors['group_define_name'] = 'You must enter a "define name"';
            }
            */
            if(empty($input->group['description'])) {
                $aErrors['description'] = 'You must enter a description';
            }
        }
        
        //  if errors have occured
        if (!empty($aErrors)) {
            $input->template = 'luGroupEdit.html';
            
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }
    }
    
   /**
    *
    * Show the form which allow to create new group
    *
    */
    function _add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $output->template = 'luGroupEdit.html';
        $output->action = 'insert';
    }

   /**
    *
    * Insert new group to database
    *
    */
    function _insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $admin = &LUAdmin::singleton();
        
        $data = $this->_buildFilterData($input);
        $groupId = $admin->perm->addGroup($data);
        if ($groupId === false) {
              SGL::raiseError('Error on line: '.__LINE__.' last query: '.$admin->perm->_storage->dbc->last_query, 
                  SGL_ERROR_DBFAILURE);
        } else {
            SGL::raiseMsg('Group was successfully added');
        }
    }

   /**
    *
    * Show specific group data (in the form)
    *
    */
    function _edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        if(empty($input->group_id)) {
            LUAdmin::noRecordRedirect();
        }
        
        $output->template = 'luGroupEdit.html';
        $output->action = 'update';
        
        $group = $this->getGroup($input->group_id);
        if($group === false) {
            LUAdmin::noRecordRedirect();
        }
        
        $output->group = (array)$group;
    }

   /**
    *
    * Update group data
    *
    */
    function _update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        if(empty($input->group_id)) {
            LUAdmin::noRecordRedirect();
        }
        
        $admin = &LUAdmin::singleton();
        
        $data = $this->_buildFilterData($input);
        $filters = array('group_id' => $input->group_id);
        
        $upGroup = $admin->perm->updateGroup($data, $filters); 
        
        if ($upGroup === false) {
            SGL::raiseError('Error on line: '.__LINE__.', error: '.LUAdmin::errorToString($admin->getErrors()), 
                SGL_ERROR_DBFAILURE);
        } else {
            SGL::raiseMsg('Group was successfully updated');
        }
    }

   /**
    *
    * List all groups
    *
    */
    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $output->template = 'luGroupsList.html';
        
        $admin = &LUAdmin::singleton();
        $aparams['fields'] = array('name', 'description');
        $groups = &$admin->perm->getGroups();
        
        // get members quantity
        
        foreach($groups as $key => $group) {
            $liveuserGroupUsers = &DB_DataObject::factory('liveuser_groupusers');
            $liveuserGroupUsers->group_id = $group['group_id'];
            $groups[$key]['members_quantity'] = $liveuserGroupUsers->find();
        }
        
        $output->groups = &$groups;
    }

   /**
    *
    * Delete group(s)
    *
    */
    function _delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        if (is_array($input->aDelete)) {
            $admin = &LUAdmin::singleton();
            
            foreach ($input->aDelete as $groupId) {
                $filters = array('group_id' => $groupId, 'recursive' => true);
                $rmGroup = $admin->perm->removeGroup($filters);
                if ($rmGroup === false) {
                    SGL::raiseError('Error on line: '.__LINE__.', error: '.LUAdmin::errorToString($admin->getErrors()), 
                        SGL_ERROR_DBFAILURE);
                }
            }
            SGL::raiseMsg('Group(s) was successfully deleted');
        } else {
            SGL::raiseError('Incorrect parameter passed to ' . 
                __CLASS__ . '::' . __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
    }
    
   /**
    *
    * Edit group rights
    *
    */
    function _editRights(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'luGroupEditRights.html';
        $output->pageTitle = $this->pageTitle . ' :: Rights';
        
        $group = $this->getGroup($input->group_id);
        if($group === false) {
            LUAdmin::noRecordRedirect();
        }
        $output->group = &$group;
        
        //  get set of rights associated with group
        $aGroupRights = $this->getRightsDetailsByGroupId($input->group_id);
        asort($aGroupRights);
        $output->groupRightOptions = SGL_Output::generateSelect($aGroupRights);

        //  get remaining rights
        $aRemainingRights = $this->retrieveAllOthersRights($aGroupRights);
        asort($aRemainingRights);
        $output->remainingRightsOptions = SGL_Output::generateSelect($aRemainingRights);
    }
    
   /**
    *
    * Update group rights
    *
    */
    function _updateRights(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $aRightsToAdd    = LUAdmin::parseWidgetString($input->rightsToAdd);
        $aRightsToRemove = LUAdmin::parseWidgetString($input->rightsToRemove);
        
        if (is_array($aRightsToAdd) && count($aRightsToAdd)) {
            $this->_changeAssignments($aRightsToAdd, $input->group_id, SGL_LIVEUSER_RIGHT_ADD);
        }
        if (is_array($aRightsToRemove) && count($aRightsToRemove)) {
            $this->_changeAssignments($aRightsToRemove, $input->group_id, SGL_LIVEUSER_RIGHT_REMOVE);
        }
        SGL::raiseMsg('group assignments successfully updated');
    }

    /**
     * Updates Db with new assignments.
     *
     * @access  private
     * @param   array       $aRights array of rights to add/remove
     * @param   string      $groupId group ID to associate rights with
     * @param   constant    action  whether to add/remove right
     * @return  void
     */
    function _changeAssignments($aRights, $groupId, $action)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $dbh = & SGL_DB::singleton();
        
        if ($action == SGL_LIVEUSER_RIGHT_REMOVE) {
            foreach ($aRights as $rightId => $rightName) {
                $dbh->query("   DELETE FROM liveuser_grouprights
                                WHERE   right_id = $rightId
                                AND     group_id = $groupId");
            }
        } else {
            //  add rights
            foreach ($aRights as $rightId => $rightName) {
                $dbh->query("   INSERT INTO liveuser_grouprights
                                (group_id, right_id, right_level)
                                VALUES ($groupId, $rightId, 1)");
            }
        }
    }

    /**
     * Returns assoc array of all rights per given group id.
     *
     * @access  public
     * @param   int     $groupId         id of target group
     * @return  array   $aGroupRights    array of rights returned
     * @see     retrieveAllOthersRights()
     */
    function getRightsDetailsByGroupId($groupId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $query = "
            SELECT  gr.right_id, r.name
            FROM    liveuser_grouprights gr, liveuser_rights r
            WHERE   gr.right_id = r.right_id
            AND     gr.group_id = $groupId
            ";
        
        $dbh = & SGL_DB::singleton();
        $aGroupRights = $dbh->getAssoc($query);
        return $aGroupRights;
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
    function retrieveAllOthersRights($aGroupRights)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (count($aGroupRights) > 0) {
            $whereClause = '';
            foreach ($aGroupRights as $key => $value) {
                $whereClause .= " $key NOT IN (r.right_id) AND ";
            }
            $whereClause = substr($whereClause, 0, -4);
        }
        $query = '
            SELECT  r.right_id, r.name
            FROM    liveuser_rights r';
        if (count($aGroupRights) > 0) {
            $query .= " WHERE $whereClause";
        }
        $dbh = & SGL_DB::singleton();
        $aOthersRights = $dbh->getAssoc($query);
        return $aOthersRights;
    }
    
   /**
    *
    * Allow to edit group members
    *
    */
    function _editMembers(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'luGroupEditMembers.html';
        $output->pageTitle = $this->pageTitle . ' :: Members';
        
        $group = $this->getGroup($input->group_id);
        if($group === false) {
            LUAdmin::noRecordRedirect();
        }
        $output->group = &$group;
        
        //  get set of members associated with group
        $aGroupMembers = $this->getMembersDetailsByGroupId($input->group_id);
        asort($aGroupMembers);
        $output->groupMemberOptions = SGL_Output::generateSelect($aGroupMembers);

        //  get remaining members
        $aRemainingMembers = $this->retrieveAllOthersMembers($aGroupMembers);
        asort($aRemainingMembers);
        $output->remainingMembersOptions = SGL_Output::generateSelect($aRemainingMembers);
    }
    
    function _updateMembers(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $aMembersToAdd    = LUAdmin::parseWidgetString($input->membersToAdd);
        $aMembersToRemove = LUAdmin::parseWidgetString($input->membersToRemove);
        
        if (is_array($aMembersToAdd) && count($aMembersToAdd)) {
            $this->_changeMemberAssignments($aMembersToAdd, $input->group_id, SGL_LIVEUSER_RIGHT_ADD);
        }
        if (is_array($aMembersToRemove) && count($aMembersToRemove)) {
            $this->_changeMemberAssignments($aMembersToRemove, $input->group_id, SGL_LIVEUSER_RIGHT_REMOVE);
        }
        SGL::raiseMsg('group assignments successfully updated');
    }

    /**
     * Updates Db with new assignments.
     *
     * @access  private
     * @param   array       $aMembers array of members to add/remove
     * @param   string      $groupId group ID to associate rights with
     * @param   constant    action  whether to add/remove right
     * @return  void
     */
    function _changeMemberAssignments($aMembers, $groupId, $action)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $dbh = & SGL_DB::singleton();
        
        if ($action == SGL_LIVEUSER_RIGHT_REMOVE) {
            foreach ($aMembers as $memberId => $name) {
                $dbh->query("   DELETE FROM liveuser_groupusers
                                WHERE   perm_user_id = $memberId
                                AND     group_id = $groupId");
            }
        } else {
            //  add rights
            foreach ($aMembers as $memberId => $name) {
                $dbh->query("   INSERT INTO liveuser_groupusers
                                (group_id, perm_user_id)
                                VALUES ($groupId, $memberId)");
            }
        }
    }

    /**
     * Returns assoc array of all members per given group id.
     *
     * @access  public
     * @param   int     $groupId         id of target group
     * @return  array   $aGroupRights    array of rights returned
     * @see     retrieveAllOthersRights()
     */
    function getMembersDetailsByGroupId($groupId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $query = "
            SELECT  gu.perm_user_id, CONCAT(u.last_name, ' ', u.first_name, ' (', u.username, ')') as name
            FROM    liveuser_groupusers gu, usr u
            WHERE   gu.perm_user_id = u.usr_id
            AND     gu.group_id = $groupId
            ORDER BY u.last_name, u.first_name";
        
        $dbh = & SGL_DB::singleton();
        $aGroupMembers = $dbh->getAssoc($query);
        return $aGroupMembers;
    }

    /**
     * Like a 'difference' operation, returns the balance of getMembersDetailsByGroupId
     *
     * returns an assoc array of all members which are not in getMembersDetailsByGroupId()
     *
     * @access  public
     * @param   array   $aGroupMembers    hash of members to exclude
     * @return  array   $aOthersMembers    array of perms returned
     * @see     getMembersDetailsByGroupId()
     */
    function retrieveAllOthersMembers($aGroupMembers)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (count($aGroupMembers) > 0) {
            $whereClause = '';
            foreach ($aGroupMembers as $key => $value) {
                $whereClause .= " $key NOT IN (u.usr_id) AND ";
            }
            $whereClause = substr($whereClause, 0, -4);
        }
        $query = '
            SELECT  u.usr_id, CONCAT(u.last_name, " ", u.first_name, " (", u.username, ")") as name
            FROM    usr u';
        if (count($aGroupMembers) > 0) {
            $query .= " WHERE $whereClause";
        }
        $query .= ' ORDER BY u.last_name, u.first_name';
        
        $dbh = &SGL_DB::singleton();
        $aOthersMembers = $dbh->getAssoc($query);
        
        return $aOthersMembers;
    }
    
    /**
     * Find group via LiveUser_Admin api
     *
     * @access  public
     * @param   int   $groupId    group Id
     * @return  object $foundGroup  Found group (as an object) or false
     */
    function getGroup($groupId)
    {
        $params['filters'] = array('group_id' => $groupId);
        
        $admin = &LUAdmin::singleton();
        $groups = &$admin->perm->getGroups($params);
        $foundGroup = false;
        
        foreach($groups as $group) {
            if($group['group_id'] == $groupId) {
                $foundGroup = (object)$group;
                break;
            }
        }
        
        return $foundGroup;
    }
    
    /**
     * Find group via LiveUser_Admin api
     *
     * @access  private
     * @param   int   $input
     * @return  array $data  Formatted data options
     */
    function _buildFilterData(&$input)
    {
        $data = array(
            'group_define_name' => LUAdmin::convertToConstant($input->group['name']), //$input->group['group_define_name'],
            'name'              => $input->group['name'],
            'description'       => $input->group['description'],
            'is_active'         => 'Y',
        );
        
        return $data;
    }
    
}
?>