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
// | SectionMgr.php                                                            |
// +---------------------------------------------------------------------------+
// | Authors:   Andy Crain <crain@fuse.net>                                    |
// |            Demian Turner <demian@phpkitchen.com>                          |
// +---------------------------------------------------------------------------+
// $Id: SectionMgr.php,v 1.60 2005/05/29 21:32:17 demian Exp $

require_once SGL_MOD_DIR  . '/default/classes/DefaultDAO.php';
require_once SGL_MOD_DIR  . '/navigation/classes/NavigationDAO.php';
require_once SGL_MOD_DIR  . '/user/classes/UserDAO.php';
require_once SGL_MOD_DIR  . '/default/classes/ModuleMgr.php';
require_once SGL_CORE_DIR . '/Delegator.php';

/**
 * To administer sections.
 *
 * @package navigation
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SectionMgr extends SGL_Manager
{
    function SectionMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle      = 'Section Manager';
        $this->masterTemplate = 'masterMinimal.html';
        $this->template       = 'sectionList.html';

        $daDefault = &DefaultDAO::singleton();
        $daUser    = &UserDAO::singleton();
        $daNav     = &NavigationDAO::singleton();
        $this->da  = new SGL_Delegator();
        $this->da->add($daDefault);
        $this->da->add($daUser);
        $this->da->add($daNav);

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
        $input->section     = $req->get('section');
        $input->section['is_enabled']       = (isset($input->section['is_enabled'])) ? 1 : 0;
        $input->section['uri_alias_enable'] = (isset($input->section['uri_alias_enable'])) ? 1 : 0;
        $input->section['uri_alias_autoMode'] = (isset($input->section['uri_alias_autoMode'])) ? 1 : 0;
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
        $this->validated  = true;
        $input->submitted = $req->get('submitted');
        $input->aParams   = $req->get('aParams', $allowTags = true);
        $input->isAdd     = $req->get('isadd');
        $input->mode      = $req->get('mode');

        //  validate form data
        if ($input->submitted) {
            if (empty($input->section['title'])) {
                $aErrors['title'] = 'Please fill in a title';
            }
            //  zero is a valid property, refers to public role
            if (is_null($input->section['perms'])) {
                $aErrors['perms'] = 'Please assign viewing rights to least one role';
            }
            //  If a child, need to make sure its is_enabled status OK with parents
            //  Only warn if they attempt to make child active when a parent is inactive
            if (($input->action == 'update' || $input->action == 'insert') && $input->section['parent_id'] != 0) {
                $parent = $this->da->getSectionById($input->section['parent_id']);
                if ($parent['is_enabled'] == 0 && $input->section['is_enabled'] == 1) {
                    $aErrors['is_enabled']['string'] = 'You cannot activate unless you first activate.';
                    $aErrors['is_enabled']['args'][] = (!empty($input->section['title']))
                        ? $input->section['title']
                        : ($input->action == 'insert')
                            ? ''
                            : $input->section['title_original'];
                    $aErrors['is_enabled']['args'][] = $parent['title'];
                }
                //  check child has same or subset of parents permissions
                if ($input->section['perms']) {
                    $aPerms = explode(',', $input->section['perms']);
                    $aParentPerms = explode(',', $parent['perms']);
                    foreach ($aPerms as $permID) {
                        if (!in_array($permID, $aParentPerms) && !in_array(SGL_ANY_ROLE, $aParentPerms)) {
                            $aErrors['perms']['string'] = 'To access this section, a user must have access to the parent section.';
                            $aErrors['perms']['args'][] = $parent['title'];
                            break;
                        }
                    }
                }
            }
            if (isset($input->section['staticArticleId'])
                && $input->section['staticArticleId'] == 0) {
                $aErrors['staticArticleId'] = 'You must select a valid article';
            }
        } elseif (!empty($input->section['edit'])) {
            unset($input->aParams);
            $this->validated = false;
        }
        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields', true, SGL_MESSAGE_ERROR);
            $input->error    = $aErrors;
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
        $output->mode     = 'New section';
        $output->isAdd    = true;
        $this->_editDisplay($output);
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->mode     = 'Edit section';
        $output->template = 'sectionEdit.html';

        $section = $this->da->getSectionById($input->sectionId);
        $section['addonParams']         = base64_encode(@$section['addonParams']);
        $section['title_original']      = $section['title'];
        $section['is_enabled_original'] = $section['is_enabled'];
        $section['parent_id_original']  = $section['parent_id'];

        $output->section = $section;
        $this->_editDisplay($output);
    }

    function _cmd_insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!count($input->section)) {
            SGL::raiseError('No data in input object', SGL_ERROR_NODATA);
            return false;
        }
        if (!empty($input->aParams)) {
            $input->section['aParams'] = $input->aParams;
        }
        if ($this->da->addSection($input->section)) {
            SGL::raiseMsg('Section successfully added', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseMsg('There was a problem inserting the record', true, SGL_MESSAGE_ERROR);
        }
        //  clear cache so a new cache file is built reflecting changes
        SGL_Cache::clear('nav');
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $input->section['lang'] = $input->navLang;
        if (!empty($input->aParams)) {
            $input->section['aParams'] = $input->aParams;
        }

        if ($this->da->updateSection($input->section)) {
            SGL::raiseMsg('Section details successfully updated', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseMsg('There was a problem updating the record', true, SGL_MESSAGE_ERROR);
        }
        //  clear cache so a new cache file is built reflecting changes
        SGL_Cache::clear('nav');
    }

    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (is_array($input->aDelete)) {
            foreach ($input->aDelete as $index => $sectionId) {
                $this->da->deleteSectionById($sectionId);
            }
            SGL::raiseMsg('The selected section(s) have successfully been deleted',
                true, SGL_MESSAGE_INFO);

            //  clear cache so a new cache file is built reflecting changes
            SGL_Cache::clear('nav');
        } else {
            SGL::raiseMsg('There is no section to delete',
                true, SGL_MESSAGE_ERROR);
        }
    }

    function _cmd_reorder(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aMoveTo = array('BE' => 'up',
                         'AF' => 'down');
        if (isset($input->sectionId, $input->targetId)
                && ($pos = array_search($input->move, $aMoveTo))) {
            $this->da->moveSection($input->sectionId, $input->targetId, $pos);
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

        //  get all sections
        $aSections        = $this->da->getSectionTree();
        $output->results  = &$aSections;

        $output->sectionArrayJS = $this->_createNodesArrayJS($aSections);
        if ($this->conf['translation']['container'] == 'db') {
            $output->fallbackLang = SGL_Translation::getFallbackLangID();
        }
        $output->addOnLoadEvent("switchRowColorOnHover()");
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  load UriAliasHandler depending on action
        if ($output->action == 'add' || $output->action == 'insert') {
            $output->addOnLoadEvent("UriAliasHandlerInit('add','auto')");
        } elseif ($output->action == 'edit' || $output->action == 'update') {
            $output->addOnLoadEvent("UriAliasHandlerInit('edit','manual')");
        }
    }

    function _editDisplay(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  pre-check enabled box
        $output->sectionIsEnabled  = !empty($output->section['is_enabled']) ? 'checked="checked"' : '';
        $output->uriAliasIsEnabled = !empty($output->section['uri_alias_enable']) ? 'checked="checked"' : '';
        $output->uriAliasIsAutoMode = !empty($output->section['uri_alias_autoMode']) ? 'checked="checked"' : '';

        //  get array of section node objects
        $output->sectionNodesOptions[0] = SGL_String::translate('Top level (no parent)');
        $aNodesForSelect = $this->da->getSectionsForSelect();
        if (is_array($aNodesForSelect) && count($aNodesForSelect)) {
            $output->sectionNodesOptions  = $output->sectionNodesOptions
                                          + $aNodesForSelect;
        }
        if ($this->conf['translation']['container'] == 'db') {
            $availableLangs = $this->trans->getLangs();

            if (!empty($output->isAdd)) {
                $output->navLang     = SGL_Translation::getFallbackLangID();
                $output->fullNavLang = $availableLangs[$output->navLang];
            } elseif (!empty($output->section['trans_id'])) {
                $navLang = (isset($output->navLang) && !empty($output->navLang))
                    ? $output->navLang
                    : SGL_Translation::getLangID();
                $output->navLang          = $navLang;
                $output->fullNavLang      = $availableLangs[$navLang];
                $output->availableLangs   = $availableLangs;
                $output->section['title'] = $this->trans->get($output->section['trans_id'],
                    'nav', $output->navLang);
            }
        }

        switch (@$output->section['uriType']) {
        case 'wiki':
            $output->wikiSelected = 'selected';
            break;

        case 'dynamic':
            $output->dynamicSelected = 'selected';

            //  build dynamic section choosers
            $output->aModules = SGL_Util::getAllModuleDirs();
            reset($output->aModules);
            $currentModule = isset($output->section['module'])
                ? $output->section['module']
                : key($output->aModules);
            $output->aManagers = SGL_Util::getAllManagersPerModule(SGL_MOD_DIR .'/'. $currentModule);
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
            $output->sectionNodesOptions2 = $this->da->getSectionsForSelect();
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
                if (!is_array($aSavedParams = @unserialize(base64_decode($output->section['addonParams'])))) {
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

        default: //static
            $output->staticSelected = 'selected';

            //  build static article list
            if (SGL::moduleIsEnabled('publisher')) {
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
        }

        //  build role widget
        $aRoles[SGL_ANY_ROLE]   = SGL_String::translate('All roles');
        $aRoles[SGL_GUEST]      = SGL_String::translate('guest');
        $output->aRoles         = $aRoles + $this->da->getRoles();
        $output->aSelectedRoles = isset($output->section['perms'])
                ? explode(',', $output->section['perms']) : SGL_ANY_ROLE;
    }

    /**
     * Returns a hash of articles.
     *
     * @return array
     *
     * @todo move to PublisherDAO
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

    /**
     * Creates and returns a string representing JavaScript array of node info arrays,
     * for use by JS confirmDelete() in mainAdmin.js.
     *
     * @access  private
     * @param   array   $nodesArray an array of nodes arrays
     * @return  string  representation of a JavaScript array, for use
     */
    function _createNodesArrayJS(&$nodesArray)
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
