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
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | PageMgr.php                                                               |
// +---------------------------------------------------------------------------+
// | Authors:   Andy Crain <crain@fuse.net>                                    |
// |            Demian Turner <demian@phpkitchen.com>                          |
// +---------------------------------------------------------------------------+
// $Id: PageMgr.php,v 1.60 2005/05/29 21:32:17 demian Exp $

require_once SGL_CORE_DIR . '/NestedSet.php';
require_once SGL_MOD_DIR . '/user/classes/DA_User.php';
require_once SGL_MOD_DIR . '/default/classes/ModuleMgr.php';

/**
 * To administer sections.
 *
 * @package navigation
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.60 $
 * @since   PHP 4.1
 */
class PageMgr extends SGL_Manager
{
    var $_params = array();

    function PageMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle        = 'Page Manager';
        $this->masterTemplate   = 'masterMinimal.html';
        $this->template         = 'sectionList.html';
        $this->da               = & DA_User::singleton();

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
                'root_id'       => 'rootid',
                'left_id'       => 'l',
                'right_id'      => 'r',
                'order_id'      => 'norder',
                'level_id'      => 'level',
                'parent_id'     => 'parent',
                'resource_uri'  => 'resource_uri',
                'title'         => 'title',
                'perms'         => 'perms',
                'is_enabled'    => 'is_enabled',
                'is_static'     => 'is_static',
                'languages'     => 'languages',
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
        $input->section['is_enabled'] = (isset($input->section['is_enabled'])) ? 1 : 0;
        $input->navLang     = $req->get('frmNavLang');
        $input->availableLangs = $req->get('frmAvailableLangs');
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
        $this->validated    = true;
        $this->submit       = $req->get('submitted');

        //  fc hacks needed as a result of JS submit
        if ($input->action == 'insert' && is_null($this->submit)) {
            $input->action = 'add';
        }
        if ($input->action == 'update' && is_null($this->submit)) {
            $this->submit = true;
            $aErrors[] = 'Please supply full nav info';
        }
        //  validate form data
        if ($this->submit) {
            if (empty($input->section['title'])) {
                $aErrors[] = 'Please fill in a title';
            }
            //  zero is a valid property, refers to public group
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
        }

        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
            $input->template = 'sectionEdit.html';
        }
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
             FROM    {$this->conf['table']['item']} i, {$this->conf['table']['item_addition']} ia,
                     {$this->conf['table']['item_type']} it, {$this->conf['table']['item_type_mapping']} itm
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

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  pre-check enabled box
        $output->pageIsEnabled = (isset($output->section['is_enabled']) &&
            $output->section['is_enabled'] == 1) ? 'checked' : '';

            $output->staticSelected = '';
            $output->dynamicSelected = '';
            $output->wikiSelected = '';
            $output->uriAliasSelected = '';
            $output->uriExternalSelected = '';

        switch ($output->articleType) {
        case 'static':
            $output->staticSelected = 'selected';

            //  build static article list
            if (ModuleMgr::moduleIsRegistered('publisher')) {
                $output->aStaticArticles = $this->_getStaticArticles();
            } else {
                $output->aStaticArticles = array('' => 'invalid w/out Publisher module');
            }
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
            $currentMgr = isset($output->section['manager'])
                ? $output->section['manager']
                : key($output->aManagers);
            $output->aActions = ($currentMgr != 'none')
                ? SGL_Util::getAllActionMethodsPerMgr(SGL_MOD_DIR .'/'. $currentModule .'/classes/'. $currentMgr)
                : array();
            break;

        case'uriAlias':
            $output->uriAliasSelected = 'selected';
            break;

        case'uriExternal':
            $output->uriExternalSelected = 'selected';
            break;
        }
        //  build role widget
        $aRoles = $this->da->getRoles();
        $aRoles[0]= 'guest';
        $output->aRoles = $aRoles;
        $output->aSelectedRoles = explode(',', @$output->section['perms']);

        //  get array of section node objects and add images for folder-tree display
        $nestedSet = new SGL_NestedSet($this->_params);
        $output->sectionNodesOptions = $this->_generateSectionNodesOptions($nestedSet->getTree(),
            @$output->section['parent_id']);

        // build uriAliases select options
        include SGL_DAT_DIR . '/ary.uriAliases.php';
        foreach ($aUriAliases as $key => $value) {
            $output->aUriAliases[$key] = $key . ' >> ' . $value;
        }
//          fetch available languages
//        $aLangDescriptions = SGL_Util::getLangsDescriptionMap();
//
//          apply filter if current section is set
//        $filter = isset($output->sectionId)
//            ? ' WHERE section_id='.$output->sectionId : '';
//        $query = "
//            SELECT languages
//            FROM ". $this->conf['table']['section'] .
//            $filter;
//
//        $results = $this->dbh->getOne($query);
//        $aLangs = explode('|', $results);
//        foreach ($aLangs as $lang) {
//            $key = str_replace('_', '-', $lang);
//            $output->availableLangs[$lang] = $aLangDescriptions[$key];
//        }
        $trans = &SGL_Translation::singleton('admin');
        $output->availableLangs = $trans->getLangs();

        $navLang = (isset($output->navLang) && !empty($output->navLang))
            ? $output->navLang
            : SGL_Translation::getLangID();

        $output->navLang = $navLang;

        //  add language if adding new translation
//        if (!array_key_exists($navLang, $output->availableLangs)) {
//            $key = str_replace('_', '-', $navLang);
//            $output->availableLangs[$navLang] = $aLangDescriptions[$key];
//        }

        //  find unavailable languages
//        $installedLangs = explode(',', $this->conf['translation']['installedLanguages']);
//        foreach ($installedLangs as $uKey) {
//            if (!array_key_exists($uKey, $output->availableLangs)) {
//                $key = str_replace('_', '-', $uKey);
//                $output->availableAddLangs[$uKey] = $aLangDescriptions[$key];
//            }
//        }
    }

    function _add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'sectionEdit.html';
        $output->action = 'insert';
        $output->pageTitle = $this->pageTitle . ' :: Add';

        // fetch installed langs
        $trans = &SGL_Translation::singleton();
        $output->aLanguages = $trans->getLangs();
    }

    function _insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $separator = '/'; // can be configurable later

        //  if pageType = static, append articleId, else build page url
        switch ( $input->section['articleType'] ) {
        case 'static':
            $input->section['is_static'] = 1;
            $input->section['resource_uri'] =  'publisher/articleview/frmArticleID/' . $input->section['staticArticleId'] . '/';
            break;

        case 'wiki':
            $string = 'publisher/wikiscrape/url/' . urlencode($input->section['resource_uri']);
            $input->section['resource_uri'] = $string;
            break;

        case 'uriAlias':
            $string = 'uriAlias:' . $input->section['resource_uri'];
            $input->section['resource_uri'] = $string;
            break;

        case 'uriExternal':
            $string = 'uriExternal:' . $input->section['resource_uri'];
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
        //  remove trailing slash/ampersand if one is present
        if (substr($input->section['resource_uri'], -1) == $separator) {
            $input->section['resource_uri'] = substr($input->section['resource_uri'], 0, -1);
        }
        //  fetch next id
        $titleId = $this->dbh->nextID('translation');

        //  add translations
        $trans = &SGL_Translation::singleton('admin');
        $ok = $trans->add($titleId, 'nav', array($input->navLang => $input->section['title']));

        //  set translation id for nav title
        unset($input->section['title']);
        $input->section['title'] = $titleId;

        //  add lang field
        $input->section['languages'] = $input->navLang;

        //  create new set with first rootnode
        $nestedSet = new SGL_NestedSet($this->_params);

        if ($input->section['parent_id'] == 0) {    //  they want a root node
            $node = $nestedSet->createRootNode($input->section);
        } elseif ((int)$input->section['parent_id'] > 0) { //    they want a sub node
            $node = $nestedSet->createSubNode($input->section['parent_id'], $input->section);
        } else { //  error
            SGL::raiseError('Incorrect parent node id passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        //  clear cache so a new cache file is built reflecting changes
        SGL::clearCache('nav');

        //  possible DO bug here as correct insert always returns int 0
        if ($node) {
            SGL::raiseMsg('Section successfully added');
        } else {
            SGL::raiseError('There was a problem inserting the record', SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->mode = 'Edit section';
        $output->template = 'sectionEdit.html';
        $output->action = 'update';
        $output->pageTitle = $this->pageTitle . ' :: Edit';

        $trans = &SGL_Translation::singleton();

        //  get DB_NestedSet_Node object for this section
        $nestedSet = new SGL_NestedSet($this->_params);
        $section = $nestedSet->getNode($input->sectionId);

        //  if title is numeric retreive translation else populate with current title
        if (is_numeric($section['title'])) {
            $section['title_id'] = $section['title'];
            unset($section['title']);
            $section['title'] = $trans->get($section['title_id'], 'nav', $input->navLang);
            $section['language'] = $output->availableLangs[$input->navLang];
        } else {
            $section['language'] = $output->availableLangs[$input->navLang];
        }

        //  passing a non-existent section id results in null or false $section
        if ($section) {

            //  setup article type, dropdowns built in display()
            if (preg_match("@^publisher/wikiscrape/url@", $section['resource_uri'])) {
                $aElems = explode('/', $section['resource_uri']);
                $wikiUrl = array_pop($aElems);
                $section['resource_uri'] = urldecode($wikiUrl);
                $output->articleType = 'wiki';
            } elseif (preg_match('/^uriAlias:(.*)/', $section['resource_uri'], $aUri)) {
                $section['resource_uri'] = $aUri[1];
                $output->articleType = 'uriAlias';
            } elseif (preg_match('/^uriExternal:(.*)/', $section['resource_uri'], $aUri)) {
                $section['resource_uri'] = $aUri[1];
                $output->articleType = 'uriExternal';
            } else {
                $output->articleType = ($section['is_static']) ? 'static' : 'dynamic';

                //  parse url details
                    #$url = new SGL_Url($section['resource_uri'], false, new SGL_UrlParserSimpleStrategy());
                    #$parsed = $url->getQueryData($strict = true);

                $parsed = SGL_Url::parseResourceUri($section['resource_uri']);
                $section = array_merge($section, $parsed);

                //  adjust friendly mgr name to class filename
                $className = SGL_Inflector::getManagerNameFromSimplifiedName($section['manager']);
                $section['manager'] = $className . '.php';

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
                    if ($section['is_static'] && ModuleMgr::moduleIsRegistered('publisher')) {
                        if (isset($parsed['parsed_params'])) {
                            $section['resource_uri'] = $parsed['parsed_params']['frmArticleID'];
                        }
                    $section['add_params'] = '';
                }

                //  split off anchor if exists
                if (stristr($section['resource_uri'], '#')) {
                    list(,$anchor) = split("#", $section['resource_uri']);
                    $section['anchor'] = $anchor;
                }
            }
        }
        $output->section = $section;
    }

    function _update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $separator = '/';

        //  if pageType = static, append articleId, else build page url
        switch ( $input->section['articleType'] ) {
        case 'static':
            $input->section['is_static'] = 1;
            $input->section['resource_uri'] =  'publisher/articleview/frmArticleID/' . $input->section['staticArticleId'] . '/';
            break;

        case 'wiki':
            $string = 'publisher/wikiscrape/url/' . urlencode($input->section['resource_uri']);
            $input->section['resource_uri'] = $string;
            break;

        case 'uriAlias':
            $string = 'uriAlias:' . $input->section['resource_uri'];
            $input->section['resource_uri'] = $string;
            break;

        case 'uriExternal':
            $string = 'uriExternal:' . $input->section['resource_uri'];
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
        //  remove trailing slash/ampersand if one is present
        if (substr($input->section['resource_uri'], -1) == $separator) {
            $input->section['resource_uri'] = substr($input->section['resource_uri'], 0, -1);
        }

        //  update translations
        if ($input->section['title'] != $input->section['title_original']) {
            $strings[$input->navLang] = $input->section['title'];
            $trans = & SGL_Translation::singleton('admin');
            $result = $trans->add($input->section['section_id'], 'nav', $strings);

            //  assign title id and languages for update
            $input->section['title'] = $input->section['section_id'];
            $input->section['languages'] = implode('|', $input->availableLangs);
        }

        $nestedSet = new SGL_NestedSet($this->_params);

        //  attempt to update section values
        if (!$nestedSet->updateNode($input->section['section_id'], $input->section)) {
            SGL::raiseError('There was a problem updating the record',
                SGL_ERROR_NOAFFECTEDROWS);
            SGL::raiseMsg('Section details updated, no data changed');
            SGL_HTTP::redirect();
        }
        //  If changing activation status, we need to enable/disable this node's children too
        if (($input->section['is_enabled'] != $input->section['original_is_enabled'])){
            $children = $nestedSet->getSubBranch($input->section['section_id']);
            if ($children) {
                foreach ($children as $child){
                    //  change the child's is_enabled status to that of its parent
                    if (!$nestedSet->updateNode($child['section_id'], array('is_enabled' => $input->section['is_enabled']))) {
                        SGL::raiseMsg('Section details updated, no data changed');
                    }
                }
            }
        }

        //  move node if needed
        switch ($input->section['parent_id']) {
        case $input->section['original_parent_id']:
            //  usual case, no change => do nothing
            $message = 'Section details successfully updated';
            break;

        case $input->section['section_id']:
            //  cannot be parent to self => display user error
            $message = 'Section details updated, no data changed';
            break;

        case 0:
            //  move the section, make it into a root node, just above it's own root
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
        //  clear cache so a new cache file is built reflecting changes
        SGL::clearCache('nav');
        SGL::raiseMsg($message);
    }

    function _delete(&$input, &$output)
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
                    $trans = &SGL_Translation::singleton('admin');
                    $trans->remove($section['title'], 'nav');

                    //  remove page
                    $nestedSet->deleteNode($sectionId);
                }
            }
        } else {
            SGL::raiseError("Incorrect parameter passed to " . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        //  clear cache so a new cache file is built reflecting changes
        SGL::clearCache('nav');
        SGL::raiseMsg('The selected section(s) have successfully been deleted');
    }

    function _reorder(&$input, &$output)
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
        SGL::clearCache('nav');
        SGL::raiseMsg('Sections reordered successfully');
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
    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'sectionList.html';
        $nestedSet = new SGL_NestedSet($this->_params);
        $nestedSet->setImage('folder', 'images/imagesAlt2/file.png');
        $sectionNodes = $nestedSet->getTree();

        //  fetch available languages
        $availableLanguages = $GLOBALS['_SGL']['LANGUAGE'];

        //  fetch current languag
        $lang = SGL::getCurrentLang() .'-'. $GLOBALS['_SGL']['CHARSET'];

        //  fetch fallback language
        $fallbackLang = $this->conf['translation']['fallbackLang'];

        //  fetch translations title
        $aTranslations = SGL_Translation::getTranslations('nav', str_replace('-', '_' , $lang), $fallbackLang);

        //  FIXME currently only set translation if numeric
        foreach ($sectionNodes as $k => $aValues) {
            if (is_numeric($aValues['title'])) {
                $sectionNodes[$k]['title'] = $aTranslations[$aValues['title']];
            }
        }

        //  remove first element of array which serves as a 'no section' fk
        //  for joins from the block_assignment table
        unset($sectionNodes[0]);
        $nestedSet->addImages($sectionNodes);
        $output->results = $sectionNodes;
        $output->sectionArrayJS = $this->_createNodesArrayJS($sectionNodes);
        $output->fallbackLang = $fallbackLang;
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
                if (is_numeric($sectionNode['title'])) {
                    $trans = & SGL_Translation::singleton();
                    $trans->setLang(SGL_Translation::getLangID());
                    $sectionNode['title'] = $trans->get($sectionNode['title'], 'nav');
                }
                $ret .= '<option value="' . $k . '" ' . $toSelect . '>' . $spacer . $sectionNode['title'] . "</option>\n";
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