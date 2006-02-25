<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
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
// | Seagull 0.5                                                               |
// +---------------------------------------------------------------------------+
// | SectionMgr.php                                                            |
// +---------------------------------------------------------------------------+
// | Authors:   Andy Crain <crain@fuse.net>                                    |
// |            Demian Turner <demian@phpkitchen.com>                          |
// +---------------------------------------------------------------------------+
// $Id: SectionMgr.php,v 1.60 2005/05/29 21:32:17 demian Exp $

require_once SGL_CORE_DIR . '/NestedSet.php';
require_once SGL_MOD_DIR  . '/user/classes/DA_User.php';
require_once SGL_MOD_DIR  . '/default/classes/DA_Default.php';
require_once SGL_MOD_DIR  . '/default/classes/ModuleMgr.php';

/**
 * To administer sections.
 *
 * @package navigation
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SectionMgr extends SGL_Manager
{
    var $_params = array();

    function SectionMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle        = 'Section Manager';
        $this->masterTemplate   = 'masterMinimal.html';
        $this->template         = 'sectionList.html';

        $dataAccess        = &DA_User::singleton();
        $dataAccessDefault = &DA_Default::singleton();
        $dataAccess->add($dataAccessDefault);
        $this->da          = &$dataAccess;

        //  detect if trans2 support required
        if ($this->conf['translation']['container'] == 'db') {
            $this->trans = &SGL_Translation::singleton('admin');
        }

        $this->_aActionsMapping =  array(
            'add'       => array('add'),
            'insert'    => array('insert', 'redirectToDefault'),
            'edit'      => array('edit'),
            'update'    => array('update', 'redirectToDefault'),
            'reorder'   => array('reorder', 'redirectToDefault'),
            'delete'    => array('delete', 'redirectToDefault'),
            'list'      => array('list'),
        );

        $this->_params = array(
            'tableStructure' => array(
                'section_id'    => 'id',
                'title'         => 'title',
                'resource_uri'  => 'resource_uri',
                'perms'         => 'perms',
                'trans_id'      => 'trans_id',
                'root_id'       => 'rootid',
                'left_id'       => 'l',
                'right_id'      => 'r',
                'order_id'      => 'norder',
                'level_id'      => 'level',
                'parent_id'     => 'parent',
                'is_enabled'    => 'is_enabled',
                'is_static'     => 'is_static',
                'access_key'    => 'access_key',
                'rel'           => 'rel'
            ),
            'tableName'      => 'section',
            'lockTableName'  => 'table_lock',
            'sequenceName'   => 'section');
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // Forward default values
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template    = $this->template;
        $input->error       = array();
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->aDelete     = $req->get('frmDelete');

        //  Retrieve form values
        $input->sectionId   = $req->get('frmSectionId');
        $input->targetId    = $req->get('targetId');
        $input->move        = $req->get('move');
        $input->section     = $req->get('page');
        $input->section['is_enabled']       = (isset($input->section['is_enabled'])) ? 1 : 0;
        $input->section['uri_alias_enable'] = (isset($input->section['uri_alias_enable'])) ? 1 : 0;
        $input->navLang     = $req->get('frmNavLang');
        $input->articleType = @$input->section['articleType'];

        if (is_null($input->articleType)) {
            $input->articleType = 'static';
        }
        //  flatten group IDs for easy DB storage
        $input->section['perms'] = (isset($input->section['perms'])
                && count($input->section['perms']))
            ? join(',', $input->section['perms'])
            : null;

        //  Misc.
        $this->validated = true;
        $this->submit    = $req->get('submitted');
        $input->aParams  = $req->get('aParams', $allowTags = true);
        $input->isAdd    = $req->get('isadd');
        $input->mode     = $req->get('mode');

        //  validate form data
        if ($this->submit) {
            if (empty($input->section['title'])) {
                $aErrors[] = 'Please fill in a title';
            }
            //  zero is a valid property, refers to public role
            if (is_null($input->section['perms'])) {
                $aErrors[] = 'Please assign viewing rights to least one role';
            }
            //  If a child, need to make sure its is_enabled status OK with parents
            //  Only warn if they attempt to make child active when a parent is inactive
            if (($input->action == 'update' || $input->action == 'insert') && $input->section['parent_id'] != 0) {
                $nestedSet = new SGL_NestedSet($this->_params);
                $parent = $nestedSet->getNode($input->section['parent_id']);
                if ($parent['is_enabled'] == 0 && $input->section['is_enabled'] == 1) {
                    $aErrors[] = 'You cannot activate '
                        . $input->section['title'] . ' unless you first activate '
                        . $parent['title'] . '.';
                }
                //  check child has same or subset of parents permissions
                if ($input->section['perms']) {
                    $aPerms = explode(',', $input->section['perms']);
                    foreach ($aPerms as $permID) {
                        if (strpos($parent['perms'], $permID) === false){
                            $aErrors[] = 'To access this page, a user must have access' .
                                         ' to the parent page. One or more of the roles ' .
                                         'you selected does not have access to ' . $parent['title'] . '.';
                        }
                    }
                }
            }
        } elseif (!empty($input->section['edit'])) {
            unset($input->aParams);
            $this->validated = false;
        }
        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields', true, SGL_MESSAGE_WARNING);
            $input->error = $aErrors;
            $this->validated = false;
        }

        if (!$this->validated) {
            $input->template = 'sectionEdit.html';
            $this->_editDisplay($input);
        }
    }

    function _cmd_add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'sectionEdit.html';
        $output->action   = 'insert';
        $output->mode     = 'New page';
        $output->isAdd    = true;
        $this->_editDisplay($output);
    }

    function _cmd_insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!count($input->section)) {
            SGL::raiseError('No data in input object', SGL_ERROR_NODATA);
            return false;
        }

        $separator = '/'; // can be configurable later
        $errorMsg  = '';

        //  if pageType = static, append articleId, else build section url
        $input->section['is_static'] = 0;
        switch ($input->section['articleType']) {
        case 'static':
            $input->section['is_static'] = 1;
            $input->section['resource_uri'] =  'publisher/articleview/frmArticleID/' .
                $input->section['staticArticleId'] . '/';
            break;

        case 'wiki':
            $string = 'publisher/wikiscrape/url/' . urlencode($input->section['resource_uri']);
            $input->section['resource_uri'] = $string;
            break;

        case 'uriExternal':
            $string = 'uriExternal:' . $input->section['resource_uri'];
            $input->section['resource_uri'] = $string;
            break;

        case 'uriNode':
            $string = 'uriNode:' . $input->section['uri_node'];
            $input->section['resource_uri'] = $string;
            break;

        case 'uriEmpty':
            $string = 'uriEmpty:';
            $input->section['resource_uri'] = $string;
            break;

        case 'uriAddon':
            $string = 'uriAddon:' . $input->section['addon'] . ':' . @serialize($input->aParams);
            $input->section['resource_uri'] = $string;
            break;

        case 'dynamic':

            //  strip extension and 'Mgr'
            $simplifiedMgrName = SGL_Inflector::getSimplifiedNameFromManagerName($input->section['manager']);
            $actionPair = (!(empty($input->section['actionMapping'])) && ($input->section['actionMapping'] != 'none'))
                ? 'action' . $separator . $input->section['actionMapping'] . $separator
                : '';

            $input->section['resource_uri'] =
                $input->section['module'] . $separator .
                $simplifiedMgrName . $separator .
                $actionPair;
            break;
        }
        //  deal with additional params
        if (!(empty($input->section['add_params']))) {

            //  handle params abstractly to later accomodate traditional urls
            //  also strip blank array elements caused by input like '/foo/bar/'
            $params = array_filter(explode('/', $input->section['add_params']), 'strlen');
            $input->section['resource_uri'] .= implode($separator, $params);
        }
        //  add anchor if necessary
        if (!(empty($input->section['anchor']))) {
            $input->section['resource_uri'] .= '#' . $input->section['anchor'];
        }
        //  prepare resource_uri string for alias format
        if (!empty($input->section['uri_alias'])) {
            $nextAliasId = $this->dbh->nextId($this->conf['table']['uri_alias']);
            $input->section['resource_uri'] = 'uriAlias:' . $nextAliasId .':' . $input->section['resource_uri'];
        }
        //  remove trailing slash/ampersand if one is present
        if (substr($input->section['resource_uri'], -1) == $separator) {
            $input->section['resource_uri'] = substr($input->section['resource_uri'], 0, -1);
        }
        //  fetch next id
        $sectionNextId = $this->dbh->nextID($this->conf['table']['section']) + 1;

        //  add translations
        if ($this->conf['translation']['container'] == 'db') {
            $ok = $this->trans->add($sectionNextId, 'nav', array($input->navLang => $input->section['title']));
        }

        //  set translation id for nav title
        $input->section['trans_id'] = $sectionNextId;

        //  create new set with first rootnode
        $nestedSet = new SGL_NestedSet($this->_params);

        if ($input->section['parent_id'] == 0) {    //  they want a root node
            $nodeId = $nestedSet->createRootNode($input->section);
        } elseif ((int)$input->section['parent_id'] > 0) { //    they want a sub node
            $nodeId = $nestedSet->createSubNode($input->section['parent_id'], $input->section);
        } else { //  error
            SGL::raiseError('Incorrect parent node id passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }

        //  deal with potential alias
        if (!empty($input->section['uri_alias'])) {
            $aliasName = SGL_String::dirify($input->section['uri_alias']);
            $target = $nodeId;
            $ok = $this->da->addUriAlias($nextAliasId, $aliasName, $target);
            if (PEAR::isError($ok)) {
                $errorMsg = ' but alias creation failed as there can be no duplicates';
            }
        }

        //  clear cache so a new cache file is built reflecting changes
        SGL_Cache::clear('nav');

        //  possible DO bug here as correct insert always returns int 0
        if ($nodeId) {
            SGL::raiseMsg('Section successfully added' . $errorMsg, true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('There was a problem inserting the record', SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->mode     = 'Edit page';
        $output->template = 'sectionEdit.html';
        $output->action   = 'update';

        //  get DB_NestedSet_Node object for this section
        $nestedSet = new SGL_NestedSet($this->_params);
        $section   = $nestedSet->getNode($input->sectionId);
        $section['title_original']      = $section['title'];
        $section['is_enabled_original'] = $section['is_enabled'];
        $section['parent_id_original']  = $section['parent_id'];

        //  passing a non-existent section id results in null or false $section
        if ($section) {

            //  setup article type, dropdowns built in display()
            if (preg_match('/(uriAlias:)([0-9]+:)(.*)/', $section['resource_uri'], $aMatches)) {
                $section['resource_uri']     = $aMatches[3];
                $section['uri_alias_enable'] = $uriAlias = true;
            }
            if (preg_match("@^publisher/wikiscrape/url@", $section['resource_uri'])) {
                $aElems = explode('/', $section['resource_uri']);
                $wikiUrl = array_pop($aElems);
                $section['resource_uri'] = urldecode($wikiUrl);
                $output->articleType = 'wiki';
            } elseif (preg_match('/^uriExternal:(.*)/', $section['resource_uri'], $aUri)) {
                $section['resource_uri'] = $aUri[1];
                $output->articleType     = 'uriExternal';
            } elseif (preg_match('/^uriAddon:([^:]*):(.*)/', $section['resource_uri'], $aUri)) {
                $section['addon'] = $aUri[1];
                $section['aParams'] = base64_encode($aUri[2]);
                $output->articleType = 'uriAddon';
             } elseif (preg_match('/^uriNode:(.*)/', $section['resource_uri'], $aUri)) {
                $section['uri_node'] = $aUri[1];
                $output->articleType = 'uriNode';
             } elseif ('uriEmpty:' == $section['resource_uri']) {
                $output->articleType = 'uriEmpty';
            } else {
                $output->articleType = ($section['is_static']) ? 'static' : 'dynamic';

                //  parse url details
                $parsed = SGL_Url::parseResourceUri($section['resource_uri']);
                $section = array_merge($section, $parsed);

                //  adjust friendly mgr name to class filename
                $c          = &SGL_Config::singleton();
                $moduleConf = $c->load(SGL_MOD_DIR . '/' . $parsed['module'] . '/conf.ini', true);
                $c->merge($moduleConf);
                $className  = SGL_Inflector::getManagerNameFromSimplifiedName($section['manager']);
                if ($className) {
                    $section['manager'] = $className . '.php';
                } else {
                    SGL::raiseMsg('Manager was not found', true, SGL_MESSAGE_WARNING);
                }

                //  represent additional params as string
                if (array_key_exists('parsed_params', $parsed) && count($parsed['parsed_params'])) {
                    foreach ($parsed['parsed_params'] as $k => $v) {
                        $ret[] = $k . '/' . $v;
                    }
                    $section['add_params'] = implode('/', $ret);
                } else {
                    $section['add_params'] = null;
                }
                //  deal with static articles
                if ($section['is_static'] && $this->da->moduleIsRegistered('publisher')) {
                    if (isset($parsed['parsed_params'])) {
                        $section['staticArticleId'] = $parsed['parsed_params']['frmArticleID'];
                    }
                    $section['add_params'] = '';
                }
                //  split off anchor if exists
                if (stristr($section['resource_uri'], '#')) {
                    list(,$anchor) = split("#", $section['resource_uri']);
                    $section['anchor'] = $anchor;
                }
            }
            $section['uri_alias'] = $this->da->getAliasBySectionId($section['section_id']);
        }
        $output->section = $section;
        $this->_editDisplay($output);
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $separator = '/';
        $errorMsg  = '';

        //  if pageType = static, append articleId, else build page url
        $input->section['is_static'] = 0;
        switch ($input->section['articleType']) {
        case 'static':
            $input->section['is_static'] = 1;
            $input->section['resource_uri'] =  'publisher/articleview/frmArticleID/' . $input->section['staticArticleId'] . '/';
            break;

        case 'wiki':
            $string = 'publisher/wikiscrape/url/' . urlencode($input->section['resource_uri']);
            $input->section['resource_uri'] = $string;
            break;

        case 'uriExternal':
            $string = 'uriExternal:' . $input->section['resource_uri'];
            $input->section['resource_uri'] = $string;
            break;

        case 'uriNode':
            $string = 'uriNode:' . $input->section['uri_node'];
            $input->section['resource_uri'] = $string;
            break;

        case 'uriEmpty':
            $string = 'uriEmpty:';
            $input->section['resource_uri'] = $string;
            break;

        case 'uriAddon':
            $string = 'uriAddon:' . $input->section['addon'] . ':' . @serialize($input->aParams);
            $input->section['resource_uri'] = $string;
            break;

        case 'dynamic':

            //  strip extension and 'Mgr'
            $simplifiedMgrName = SGL_Inflector::getSimplifiedNameFromManagerName($input->section['manager']);
            $actionPair = (!(empty($input->section['actionMapping'])) && ($input->section['actionMapping'] != 'none'))
                ? 'action' . $separator . $input->section['actionMapping'] . $separator
                : '';

            $input->section['resource_uri'] =
                $input->section['module'] . $separator .
                $simplifiedMgrName . $separator .
                $actionPair;
            //  must be a dynamic article, set flag
            $input->section['is_static'] = 0;
            break;
        }

        //  deal with additional params
        if (!(empty($input->section['add_params']))) {

            //  handle params abstractly to later accomodate traditional urls
            //  also strip blank array elements caused by input like '/foo/bar/'
            $params = array_filter(explode('/', $input->section['add_params']), 'strlen');

            $input->section['resource_uri'] .= implode($separator, $params);
        }
        //  add anchor if necessary
        if (!(empty($input->section['anchor']))) {
            $input->section['resource_uri'] .= '#' . $input->section['anchor'];
        }

        //  prepare resource_uri string for alias format
        if (!empty($input->section['uri_alias'])) {
            $aliasId = $this->da->getAliasIdBySectionId($input->section['section_id']);

            if (is_null($aliasId)) {
                $aliasId = $this->dbh->nextId($this->conf['table']['uri_alias']);
                $aliasName = SGL_String::dirify($input->section['uri_alias']);
                $this->da->addUriAlias($aliasId, $aliasName, $input->section['section_id']);
            }
            $input->section['resource_uri'] = 'uriAlias:' . $aliasId.':'.$input->section['resource_uri'];
        }

        //  remove trailing slash/ampersand if one is present
        if (substr($input->section['resource_uri'], -1) == $separator) {
            $input->section['resource_uri'] = substr($input->section['resource_uri'], 0, -1);
        }
        //  update translations
        if ($this->conf['translation']['container'] == 'db') {
            if (strcmp($input->section['title'], $input->section['title_original']) !== 0) {
                if ($input->section['trans_id']) {
                    $ok = $this->trans->add($input->section['trans_id'], 'nav', array($input->navLang => $input->section['title']));
                }
                if ($input->navLang != SGL_Translation::getFallbackLangID()) {
                    $input->section['title'] = $input->section['title_original'];
                }
            }
        }
        $nestedSet = new SGL_NestedSet($this->_params);

        //  attempt to update section values
        if (!$parentId = $nestedSet->updateNode($input->section['section_id'], $input->section)) {
            SGL::raiseError('There was a problem updating the record',
                SGL_ERROR_NOAFFECTEDROWS);
            SGL::raiseMsg('Section details updated, no data changed', true, SGL_MESSAGE_INFO);
            SGL_HTTP::redirect();
        }
        //  If changing activation status, we need to enable/disable this node's children too
        if (($input->section['is_enabled'] != $input->section['is_enabled_original'])){
            $children = $nestedSet->getSubBranch($input->section['section_id']);
            if ($children) {
                foreach ($children as $child){
                    //  change the child's is_enabled status to that of its parent
                    if (!$nestedSet->updateNode($child['section_id'], array('is_enabled' => $input->section['is_enabled']))) {
                        SGL::raiseMsg('Section details updated, no data changed', true, SGL_MESSAGE_INFO);
                    }
                }
            }
        }

        //  move node if needed
        switch ($input->section['parent_id']) {
        case $input->section['parent_id_original']:
            //  usual case, no change => do nothing
            $message = 'Section details successfully updated';
            break;

        case $input->section['section_id']:
            //  cannot be parent to self => display user error
            $message = 'Section details updated, no data changed';
            break;

        case 0:
            //  move the section, make it into a root node, just above its own root
            $thisNode = $nestedSet->getNode($input->section['section_id']);
            $moveNode = $nestedSet->moveTree($input->section['section_id'], $thisNode['root_id'], 'BE');
            $message = 'Section details successfully updated';
            break;

        default:
            //  move the section under the new parent
            $moveNode = $nestedSet->moveTree($input->section['section_id'], $input->section['parent_id'], 'SUB');
            $message = 'Section details successfully updated';
            break;
        }
        //  deal with potential alias
        if (!empty($input->section['uri_alias_enable'])) {
            $aliasName = SGL_String::dirify($input->section['uri_alias']);
            $ok = $this->da->updateUriAlias($aliasName, $input->section['section_id']);
            if (PEAR::isError($ok)) {
                $errorMsg = ' but alias creation failed as there can be no duplicates';
            }
        }
        //  clear cache so a new cache file is built reflecting changes
        SGL_Cache::clear('nav');
        SGL::raiseMsg($message . $errorMsg, true, SGL_MESSAGE_INFO);
    }

    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (is_array($input->aDelete)) {
            $nestedSet = new SGL_NestedSet($this->_params);
            //  deleting parent nodes automatically deletes chilren nodes, but user
            //  might have checked child nodes for deletion, in which case deleteNode()
            //  would try to delete nodes that no longer exist, after parent deletion,
            //  and therefore error, so test first to make sure they're still around
            foreach ($input->aDelete as $index => $sectionId) {
                if ($section = $nestedSet->getNode($sectionId)){

                    //  remove translations
                    if ($this->conf['translation']['container'] == 'db') {
                        $this->trans->remove($section['trans_id'], 'nav');
                    }

                    //  remove page
                    $nestedSet->deleteNode($sectionId);

                    //  remove alias
                    $this->da->deleteAliasBySectionId($sectionId);
                }
            }
        } else {
            SGL::raiseError("Incorrect parameter passed to " . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        //  clear cache so a new cache file is built reflecting changes
        SGL_Cache::clear('nav');
        SGL::raiseMsg('The selected section(s) have successfully been deleted', true, SGL_MESSAGE_INFO);
    }

    function _cmd_reorder(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $nestedSet = new SGL_NestedSet($this->_params);
        $aMoveTo = array('BE' => 'up',
                         'AF' => 'down');
        if (isset($input->sectionId, $input->targetId) && ($pos = array_search($input->move, $aMoveTo))) {
            $nestedSet->moveTree($input->sectionId, $input->targetId, $pos);
        } else {
            SGL::raiseError("Incorrect parameter passed to " . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        //  clear cache so a new cache file is built reflecting changes
        SGL_Cache::clear('nav');
        SGL::raiseMsg('Sections reordered successfully', true, SGL_MESSAGE_INFO);
    }

    /**
     * Generate list of all nodes displayed in their hierarchy.
     *
     * Gets set of section nodes in tree-style order, and adds to each an array of
     * images to represent place in tree. $output->results is array of section nodes
     * and their values, including tree-builder images. $output->sectionArrayJS is
     * a string Javascript array representing the tree, for use by JS confirmDelete()
     * in mainAdmin.js.
     *
     * @access  private
     * @param   object $input    not used;might want to eliminate; here only for consistency with other process methods
     * @return  object $output
     */
    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'sectionList.html';
        $output->mode     = 'Browse';
        $nestedSet = new SGL_NestedSet($this->_params);
        $nestedSet->setImage('folder', 'images/imagesAlt2/file.png');
        $sectionNodes = $nestedSet->getTree();

        if ($this->conf['translation']['container'] == 'db') {
            //  fetch available languages
            $availableLanguages = $GLOBALS['_SGL']['LANGUAGE'];

            //  fetch current languag
            $lang = SGL::getCurrentLang() .'-'. $GLOBALS['_SGL']['CHARSET'];

            //  fetch fallback language
            $fallbackLang = $this->conf['translation']['fallbackLang'];

            //  fetch translations title
            $aTranslations = SGL_Translation::getTranslations('nav', str_replace('-', '_' , $lang),
                $fallbackLang);

            //  FIXME currently only set translation if numeric
            foreach ($sectionNodes as $k => $aValues) {
                if ($aValues['trans_id'] && array_key_exists($aValues['trans_id'], $aTranslations)) {
                    $sectionNodes[$k]['title'] = $aTranslations[$aValues['trans_id']];
                }
            }
            $output->fallbackLang = $fallbackLang;
        }

        //  remove first element of array which serves as a 'no section' fk
        //  for joins from the block_assignment table
        unset($sectionNodes[0]);
        $nestedSet->addImages($sectionNodes);
        $output->results = $sectionNodes;
        $output->sectionArrayJS = $this->_createNodesArrayJS($sectionNodes);

        if ($this->conf['site']['adminGuiEnabled']) {
            $output->addOnLoadEvent("switchRowColorOnHover()");
        }
    }

    function _editDisplay(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  pre-check enabled box
        $output->pageIsEnabled = (isset($output->section['is_enabled']) &&
            $output->section['is_enabled'] == 1) ? 'checked' : '';

        $output->uriAliasEnabled = (isset($output->section['uri_alias_enable']) &&
            $output->section['uri_alias_enable'] == 1) ? 'checked' : '';

        //  get array of section node objects
        $nestedSet = new SGL_NestedSet($this->_params);
        $output->sectionNodesOptions = $this->_generateSectionNodesOptions($nestedSet->getTree(),
            @$output->section['parent_id']);

        $output->staticSelected = '';
        $output->dynamicSelected = '';
        if ($this->conf['PageMgr']['wikiScreenTypeEnabled']) {
            $output->wikiSelected = '';
        }
        $output->uriExternalSelected = '';

        if ($this->conf['translation']['container'] == 'db') {
            $availableLangs = $this->trans->getLangs();

            if ($output->action == 'insert') {
                $output->navLang        = SGL_Translation::getFallbackLangID();
                $output->fullNavLang    = $availableLangs[$output->navLang];
            } elseif ($output->action == 'update' && !empty($output->section['trans_id'])) {
                $navLang = (isset($output->navLang) && !empty($output->navLang))
                    ? $output->navLang
                    : SGL_Translation::getLangID();
                $output->navLang            = $navLang;
                $output->fullNavLang        = $availableLangs[$navLang];
                $output->availableLangs     = $availableLangs;
                $output->section['title']   = $this->trans->get($output->section['trans_id'],
                    'nav', $output->navLang);
            }
        }

        switch ($output->articleType) {
        case 'static':
            $output->staticSelected = 'selected';

            //  build static article list
            if ($this->da->moduleIsRegistered('publisher')) {
                $articles = $this->_getStaticArticles();
                if ($articles && $this->conf['translation']['container'] == 'db') {
                    foreach ($articles as $key => $value) {
                        if (is_numeric($value)){
                            $articles[$key] = $this->trans->get($value, 'content', $output->navLang);
                        }
                    }
                }
                $output->aStaticArticles = $articles;
            } else {
                $output->aStaticArticles = array('' => 'invalid w/out Publisher module');
            }
            $output->uriAliasAllowed = true;
            break;

        case 'wiki':
            $output->wikiSelected = 'selected';
            break;

        case 'dynamic':
            $output->dynamicSelected = 'selected';

            //  build dynamic section choosers
            $output->aModules = SGL_Util::getAllModuleDirs();
            $currentModule = isset($output->section['module'])
                ? $output->section['module']
                : key($output->aModules);
            $output->aManagers = SGL_Util::getAllFilesPerModule(SGL_MOD_DIR .'/'. $currentModule);
            $currentMgr = (isset($output->section['manager'])
                        && isset($output->aManagers[$output->section['manager']]))
                ? $output->section['manager']
                : key($output->aManagers);
            $output->aActions = ($currentMgr != 'none')
                ? SGL_Util::getAllActionMethodsPerMgr(SGL_MOD_DIR .'/'. $currentModule .'/classes/'. $currentMgr)
                : array();
            $output->uriAliasAllowed = true;
            break;

        case'uriExternal':
            $output->uriExternalSelected = 'selected';
            $output->uriAliasAllowed = true;
            break;

        case'uriEmpty':
            $output->uriEmptySelected = 'selected';
            $output->uriAliasEnabled  = false;
            $output->uriAliasAllowed  = false;
            break;

        case'uriNode':
            $output->uriNodeSelected = 'selected';
            $output->uriAliasEnabled = false;
            $output->uriAliasAllowed = false;

            //  get array of section node objects for internal link
            $sectionTree = $nestedSet->getTree();
            unset($sectionTree[0]);
            $output->sectionNodesOptions2 = $this->_generateSectionNodesOptions($sectionTree, @$output->section['uri_node']);
            break;

        case'uriAddon':
            $output->uriAddonSelected = 'selected';
            $output->uriAliasEnabled  = false;
            $output->uriAliasAllowed  = false;

            //  get current params
            if (empty($output->aParams)) {
                $aCurrentParams = array();
            } else {
                $aCurrentParams = $output->aParams;
                unset($output->aParams);
            }

            //  get params from ini
            if (!empty($output->section['addon'])) {
                $ini_file = SGL_MOD_DIR . '/navigation/classes/addons/' . $output->section['addon'] . '.ini';
                if (!is_array($aSavedParams = @unserialize(base64_decode($output->section['aParams'])))) {
                    $aSavedParams = array();
                }
                $aParams = SGL_Util::loadParams($ini_file, $aSavedParams, $aCurrentParams);
                foreach ($aParams as $key => $value) {
                    $output->$key = $value;
                }
            }

            $output->aAllAddons = SGL_Util::getAllClassesFromFolder(SGL_MOD_DIR .
                '/navigation/classes/addons/');
            break;
    }

        //  build role widget
        $aRoles = $this->da->getRoles();
        $aRoles[0]= 'guest';
        $output->aRoles = $aRoles;
        $output->aSelectedRoles = explode(',', @$output->section['perms']);
    }

    /**
     * Returns a hash of articles.
     *
     * @return array
     *
     * @todo move to DA_Publisher
     */
    function _getStaticArticles()
    {
        $query = "
             SELECT  i.item_id,
                     ia.addition
             FROM    {$this->conf['table']['item']} i,
                     {$this->conf['table']['item_addition']} ia,
                     {$this->conf['table']['item_type']} it,
                     {$this->conf['table']['item_type_mapping']} itm
             WHERE   ia.item_type_mapping_id = itm.item_type_mapping_id
             AND     it.item_type_id  = itm.item_type_id
             AND     i.item_id = ia.item_id
             AND     i.item_type_id = it.item_type_id
             AND     itm.field_name = 'title'
             AND it.item_type_id  = '5'         /* Static Html Article */
             AND i.status  > " . SGL_STATUS_DELETED . "
             ORDER BY i.last_updated DESC
        ";
        $res = $this->dbh->getAssoc($query);
        return ($res)? $res : false;
    }

    function _generateSectionNodesOptions($sectionNodesArray, $selected = null)
    {
        $ret = '';
        //  add "selected" to the appropriate nodeObject for <select...selected> output
        //  and add level_id based spacers
        $ret .= "<option value=\"0\">Top level (No parent)</option>\n";
        if (is_array($sectionNodesArray) && count($sectionNodesArray)) {
            foreach ($sectionNodesArray as $k => $sectionNode) {
                $spacer = str_repeat('&nbsp;&nbsp;', $sectionNode['level_id']);
                $toSelect = ($selected == $sectionNode['section_id'])?'selected':'';
                if ($this->conf['translation']['container'] == 'db') {
                    if ($title =  $this->trans->get($sectionNode['trans_id'], 'nav',
                        SGL_Translation::getLangID())) {
                        $sectionNode['title'] = $title;
                    } elseif ($title = $this->trans->get($sectionNode['trans_id'], 'nav',
                        SGL_Translation::getFallbackLangID())) {
                        $sectionNode['title'] = $title;
                    }
                }
                $ret .= '<option value="' . $k . '" ' . $toSelect . '>' . $spacer .
                    $sectionNode['title'] . "</option>\n";
            }
        }
        return $ret;
    }

    /**
     * Creates and returns a string representing JavaScript array of node info arrays,
     * for use by JS confirmDelete() in mainAdmin.js.
     *
     * @access  private
     * @param   array   $nodesArray an array of nodes arrays
     * @return  string  representation of a JavaScript array, for use
     */
    function _createNodesArrayJS($nodesArray)
    {
        $nodesArrayJS = '';
        if (is_array($nodesArray) && count($nodesArray)) {
            foreach($nodesArray as $node) {
                //need to build string array for Javascript confirmDelete()
                //now since Flexy won't compile stuff inside JS tags
                $nodesArrayJS .= 'nodeArray[' . $node['section_id'] . "] = new Array();\n";
                $nodesArrayJS .= 'nodeArray[' . $node['section_id'] . '][0] = ' . $node['left_id'] . ";\n";
                $nodesArrayJS .= 'nodeArray[' . $node['section_id'] . '][1] = ' . $node['right_id'] . ";\n";
                $nodesArrayJS .= 'nodeArray[' . $node['section_id'] . '][2] = "' . $node['title'] . "\";\n";
                $nodesArrayJS .= 'nodeArray[' . $node['section_id'] . '][3] = ' . $node['level_id'] . ";\n";
                $nodesArrayJS .= 'nodeArray[' . $node['section_id'] . '][4] = ' . $node['root_id'] . ";\n";
            }
        }
        return "<script type='text/javascript'>\nvar nodeArray = new Array()\n" . $nodesArrayJS . "</script>\n";
    }
}
?>