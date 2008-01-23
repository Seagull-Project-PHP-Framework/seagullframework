<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2008, Demian Turner                                         |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | o Redistributions of source code must retain the above copyright          |
// |   notice, this list of conditions and the following disclaimer.           |
// | o Redistributions in binary form must reproduce the above copyright       |
// |   notice, this list of conditions and the following disclaimer in the     |
// |   documentation and/or other materials provided with the distribution.    |
// | o The names of the authors may not be used to endorse or promote          |
// |   products derived from this software without specific prior written      |
// |   permission.                                                             |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT         |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR     |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT      |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,     |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT          |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,     |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE     |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.      |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | OrgMgr.php                                                                |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: OrgMgr.php,v 1.43 2005/06/05 23:14:43 demian Exp $

require_once SGL_CORE_DIR . '/NestedSet.php';
require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
require_once 'Validate.php';

/**
 * For managing Org objects.
 *
 * @package User
 * @author  Demian Turner <demian@phpkitchen.com>
 * @copyright Demian Turner 2004
 * @version $Revision: 1.43 $
 */
class OrgMgr extends SGL_Manager
{
    var $_params = array();

    /**
     * Constructor.
     *
     * @access  public
     * @return  void
     */
    function OrgMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Organisation Manager';
        $this->template     = 'orgManager.html';
        $this->da           = & UserDAO::singleton();

        $this->_aActionsMapping =  array(
            'add'       => array('add'),
            'insert'    => array('insert', 'redirectToDefault'),
            'edit'      => array('edit'),
            'update'    => array('update', 'redirectToDefault'),
            'delete'    => array('delete', 'redirectToDefault'),
            'list'      => array('list'),
        );

        $this->_params = array(
            'tableStructure' => array(
                'organisation_id' => 'id',
                'parent_id'       => 'parent',
                'root_id'         => 'rootid',
                'left_id'         => 'l',
                'right_id'        => 'r',
                'order_id'        => 'norder',
                'level_id'        => 'level',
                'role_id'         => 'role_id',
                'organisation_type_id' => 'organisation_type_id',
                'name'            => 'name',
                'description'     => 'description',
                'addr_1'          => 'addr_1',
                'addr_2'          => 'addr_2',
                'addr_3'          => 'addr_3',
                'city'            => 'city',
                'region'          => 'region',
                'country'         => 'country',
                'post_code'       => 'post_code',
                'telephone'       => 'telephone',
                'website'         => 'website',
                'email'           => 'email',
                'date_created'    => 'date_created',
                'created_by'      => 'created_by',
                'last_updated'    => 'last_updated',
                'updated_by'      => 'updated_by',
            ),
            'tableName'      => 'organisation',
            'lockTableName'  => 'table_lock',
            'sequenceName'   => 'organisation');
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated        = true;
        $input->error           = array();
        $input->submitted       = $req->get('submitted');
        $input->pageTitle       = $this->pageTitle;
        $input->masterTemplate  = 'masterMinimal.html';
        $input->template        = $this->template;
        $input->action          = ($req->get('action')) ? $req->get('action') : 'list';
        $input->from            = ($req->get('frmFrom')) ? $req->get('frmFrom'):0;
        $input->orgId           = $req->get('frmOrgId');
        $input->org             = (object) $req->get('org');
        $input->aDelete         = $req->get('frmDelete');

        $aErrors = array();
        if ($input->submitted || in_array($input->action, array('insert', 'update'))) {
            $v = & new Validate();
            if (empty($input->org->name)) {
                $aErrors['name'] = 'You must enter an organisation name';
            }
            if (empty($input->org->addr_1)) {
                $aErrors['addr_1'] = 'You must enter at least address 1';
            }
            if (empty($input->org->city)) {
                $aErrors['city'] = 'You must enter your city';
            }
            if (empty($input->org->post_code)) {
                $aErrors['post_code'] = 'You must enter your ZIP/Postal Code';
            }
            if (empty($input->org->country)) {
                $aErrors['country'] = 'You must enter your country';
            }
            if (empty($input->org->email)) {
                $aErrors['email'] = 'You must enter your email';
            } else {
                if (!$v->email($input->org->email)) {
                    $aErrors['email'] = 'Your email is not correctly formatted';
                }
            }
        }
        //  if submitted and there are errors
        if (is_array($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $input->template = 'orgEdit.html';
            if ($input->action == 'insert') {
                $input->isAdd = true;
            }

            //  build org type combobox
            if ($this->conf['OrgMgr']['typeEnabled']) {
                $output->aOrgTypes = $this->da->getOrgTypes();
            }
            $this->validated = false;
        }
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  build country/state select boxes unless any of following methods
        $aDisallowedMethods = array('list');
        if (!in_array($output->action, $aDisallowedMethods)) {
            $output->states = SGL::loadRegionList('states');
            $output->countries = SGL::loadRegionList('countries');

            //  build org type combobox
            if ($this->conf['OrgMgr']['typeEnabled']) {
                $output->aOrgTypes = $this->da->getOrgTypes();
                @$output->currentOrgType = $output->org->organisation_type_id;
            }
            //  build role combobox
            $aRoles = $this->da->getRoles($bExcludeAdmin = true);
            $output->aRoles = $aRoles;

            //  get array of section node objects and add images for folder-tree display
            $nestedSet = new SGL_NestedSet($this->_params);
            $output->orgHierOptions = $this->_generateOrgNodesOptions($nestedSet->getTree(),
                @$output->org->parent_id);
        }
    }

    function _cmd_add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->isAdd = true;
        $output->template = 'orgEdit.html';
        $output->pageTitle = $input->pageTitle . ' :: Add';
    }

    function _cmd_insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->pageTitle = $input->pageTitle . ' :: Add';
        //  handle org hierarchy
        $nestedSet = new SGL_NestedSet($this->_params);

        //  datatype must be an array for NestedSet
        $aOrg = (array) $input->org;
        $aOrg['date_created'] = $aOrg['last_updated'] = SGL_Date::getTime();
        $aOrg['created_by'] = $aOrg['updated_by'] = SGL_Session::getUid();

        //  create new set with first rootnode
        if ($aOrg['parent_id'] == 0) {    //  they want a root node
            $node = $nestedSet->createRootNode($aOrg);
        } elseif ((int)$aOrg['parent_id'] > 0) {//    they want a sub node
            $node = $nestedSet->createSubNode($aOrg['parent_id'], $aOrg);
        } else {
            SGL::raiseError('Incorrect parent node id passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        if (!($node)) {
            SGL::raiseError('There was a problem inserting the record',
                SGL_ERROR_NOAFFECTEDROWS);
        } else {
            SGL::raiseMsg('organisation successfully added', true, SGL_MESSAGE_INFO);
        }
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'orgEdit.html';

        //  get DB_NestedSet_Node array for this org
        $nestedSet = new SGL_NestedSet($this->_params);
        $aOrgNode = $nestedSet->getNode($input->orgId);

        //  build org type combobox
        if ($this->conf['OrgMgr']['typeEnabled']) {
            $output->currentOrgType = @$aOrgNode['organisation_type_id'];
        }
        $output->org = (object)$aOrgNode;
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->pageTitle = $input->pageTitle . ' :: Edit';
        $nestedSet = new SGL_NestedSet($this->_params);

        //  datatype must be an array for NestedSet
        $aOrg = (array) $input->org;
        $aOrg['last_updated'] = SGL_Date::getTime();
        $aOrg['updated_by'] = SGL_Session::getUid();

        //  attempt to update org values
        if (!$nestedSet->updateNode($aOrg['organisation_id'], $aOrg)) {
            SGL::raiseError('There was a problem updating the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
        //  move node if needed
        switch ($aOrg['parent_id']) {

        case $aOrg['original_parent_id']:
            //  usual case, no change => do nothing
            $message = 'The organisation has successfully been updated';
            break;

        case $aOrg['organisation_id']:
            //  cannot be parent to self => display user error
            $message = 'The organisation has successfully been updated, no data changed';
            break;

        case 0:
            //  move the org, make it into a root node, just above its own root
            $thisNode = $nestedSet->getNode($aOrg['organisation_id']);
            $moveNode = $nestedSet->moveTree($aOrg['organisation_id'], $thisNode['root_id'], 'BE');
            $message = 'The organisation has successfully been updated';
            break;

        default:
            //  move the section under the new parent
            $moveNode = $nestedSet->moveTree($aOrg['organisation_id'], $aOrg['parent_id'], 'SUB');
            $message = 'The organisation has successfully been updated';
        }
        SGL::raiseMsg($message, true, SGL_MESSAGE_INFO);
    }

    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (is_array($input->aDelete)) {
            $nestedSet = new SGL_NestedSet($this->_params);
            //  deleting parent nodes automatically deletes chilren nodes, but user
            //  might have checked child nodes for deletion, in which case deleteNode()
            //  would try to delete nodes that no longer exist, after parent deletion,
            //  and therefore error, so test first to make sure they're still around ...

            // ... and also check if the organisation is not needed by any users. If a
            // forein key to user exists, abort deletion to avoid bad integrity in the
            // database.

            $success = true;

            while ((list($index, $orgId) = each($input->aDelete)) && $success) {
                $org = $nestedSet->getNode($orgId);
                if ($org) {
                    $users = $this->da->getUsersByOrgId($orgId);
                    if (empty($users)) {
                        // ok, not dangerous to delete
                        $nestedSet->deleteNode($orgId);
                    } else {
                        $success = false;
                        $orgName = $org['name'];
                        SGL::raiseMsg(  "The selected organisation cannot be deleted because " .
                                        "there are users relating to it");
                    }
                }
            }

            if ($success) {
                //  redirect on success
                SGL::raiseMsg('The selected organisation(s) have successfully been deleted', true, SGL_MESSAGE_INFO);
            }
        } else {
            SGL::raiseError("Incorrect parameter passed to " . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $nestedSet = new SGL_NestedSet($this->_params);
        $nestedSet->setImage('folder', 'images/org.gif');
        $sectionNodes = $nestedSet->getTree();
        $nestedSet->addImages($sectionNodes);

        //  hack to convert role IDs to names
        $aRoles = $this->da->getRoles($bExcludeAdmin = true);
        $aOrgTypes = $this->da->getOrgTypes();

        if (is_array($sectionNodes) && count($sectionNodes) && count($aRoles)) {
            foreach ($sectionNodes as $k => $aVal) {
                $sectionNodes[$k]['role_id'] = $aRoles[$sectionNodes[$k]['role_id']];

                //  only join org types if there are any
                if (is_array($aOrgTypes) && count($aOrgTypes)) {
                    $sectionNodes[$k]['organisation_type_id'] = $aOrgTypes[$sectionNodes[$k]['organisation_type_id']];
                }
            }
        }
        $output->results = $sectionNodes;
        $output->addOnLoadEvent("switchRowColorOnHover()");
    }

    /**
     * Returns html for org combobox.
     *
     * @param array $aOrgNodes
     * @param boolean $selected
     * @return Html for combobox
     * @todo move to Output
     */
    function _generateOrgNodesOptions($aOrgNodes, $selected = null)
    {
        $ret = '';
        //  add "selected" to the appropriate nodeObject for <select...selected> output
        //  and add level_id based spacers
        $ret .= "<option value=\"0\">Top level (No parent)</option>\n";
        if (is_array($aOrgNodes) && count($aOrgNodes)) {
            foreach ($aOrgNodes as $k => $orgNode) {
                $spacer = str_repeat('&nbsp;&nbsp;', $orgNode['level_id']);
                $toSelect = ($selected == $orgNode['organisation_id']) ? 'selected' : '';
                $ret .= '<option value="' . $k . '" ' . $toSelect . '>' . $spacer . $orgNode['name'] . "</option>\n";
            }
        }
        return $ret;
    }
}
?>