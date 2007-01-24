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
// | NavStyleMgr.php                                                           |
// +---------------------------------------------------------------------------+
// | Author: Andy Crain <crain@fuse.net>                                       |
// +---------------------------------------------------------------------------+
// $Id: NavStyleMgr.php,v 1.32 2005/06/23 19:15:26 demian Exp $

require_once SGL_MOD_DIR . '/user/classes/DA_User.php';

/**
 * To administer section nav bar stylesheets.
 *
 * @package navigation
 * @author  Andy Crain <crain@fuse.net>
 * @version $Revision: 1.32 $
 * @since   PHP 4.0
 */
class NavStyleMgr extends SGL_Manager
{
    function NavStyleMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->module       = 'navigation';
        $this->pageTitle    = 'Navigation Style Manager';
        $this->template     = 'navStyleList.html';
        $this->da           = & DA_User::singleton();

        $this->_aActionsMapping =  array(
            'list'   => array('list'), 
            'changeStyle' => array('changeStyle', 'redirectToDefault'), 
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        //  forward default values
        $input->pageTitle       = $this->pageTitle;
        $input->masterTemplate  = 'masterMinimal.html';
        $input->template        = $this->template;
        $input->error           = array();
        $input->action          = ($req->get('action')) ? $req->get('action') : 'list';
        //  misc.               
        $this->validated        = true;
        $this->submitted        = $req->get('submitted');
        $input->newStyle        = $req->get('newStyle');
        $input->staticId        = $req->get('staticId');
        $input->rid             = (int)$req->get('rid');
    }

    function _changeStyle(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (array_key_exists($input->newStyle, SGL_Util::getStyleFiles($this->getCurrentStyle()))) {

            //  change [navigation][stylesheet] to $newStyle in default.conf.ini
            require_once 'Config.php';
            $conf = & $GLOBALS['_SGL']['CONF'];
            $conf['navigation']['stylesheet'] = $input->newStyle;
            $c = new Config();

            //  read configuration data and get reference to root
            $root = & $c->parseConfig($conf, 'phparray');

            //  write configuration to file
            $result = $c->writeConfig(SGL_PATH . '/var/' . SGL_SERVER_NAME . '.default.conf.ini.php', 'inifile');
            SGL_Util::makeIniUnreadable(SGL_PATH . '/var/' . SGL_SERVER_NAME . '.default.conf.ini.php');
            if (!is_a($result, 'PEAR_Error')) {
                $this->_currentStyle = $input->newStyle;
                SGL::raiseMsg('Current style successfully changed');
            } else {
                SGL::raiseError('There was a problem saving your stylesheet name', 
                    SGL_ERROR_FILEUNWRITABLE);
            }
        } else {
            SGL::raiseError('Invalid stylesheet name supplied', SGL_ERROR_INVALIDARGS);
        }
    }

    /**
     * Gets the style .conf files and lists them.
     *
     * @access  private
     * @param   object $input
     * @param   object $output
     */
    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'navStyleList.html';
        $output->styleFiles = SGL_Util::getStyleFiles($this->getCurrentStyle());
        $output->currentStyle = $this->getCurrentStyle();
        $output->staticId = (is_numeric($input->staticId)) ? $input->staticId : $this->generateStaticId();

        //  build string of radio buttons html for selecting group       
        
        $aRoles = $this->da->getRoles();
        $aRoles[0]= 'guest';
        $output->groupsRadioButtons = '';
        foreach ($aRoles as $rid => $role) {
            $radioChecked = ($rid == $input->rid)?' checked':'';
  
            $output->groupsRadioButtons .="\n". '<input type="radio"' . $radioChecked . 
                ' onClick="location.href=\'' . 
                SGL_Url::makeLink('list', 'navstyle', 'navigation', array(), "staticId|{$output->staticId}||rid|$rid"). '\'">' . $role;
        }
        //  build html unordered list of sections
        require_once SGL_MOD_DIR . '/navigation/classes/SimpleNav.php';
        $nav = & new SimpleNav();
        $nav->setStaticId($output->staticId);
        $nav->setRid($input->rid);
        $nav->setDisableLinks(true);
        $nav->render($sectionId, $html);
        $output->navListPreview = $html;
        if (!$output->navListPreview) {
            $output->navListPreview = 'There are no sections accessible to members of the selected role: ' . 
                $aRoles[$input->rid] . '.';
        }
    }

    /**
     * Gets a staticId used to fool SimpleNav into IDing a section tab as current page, so
     * admin can see curPage style also. Kind of a singleton; uses the $_GET value if exists,
     * else fetches a valid, top-level section ID from section table.
     *
     * When fetching from db, only one section object is needed; would use a limit clause but
     * it's not universally supported, so we fetch all top-level sections and use id of the first.
     * @return  int staticId
     * @access  private
     */
    function generateStaticId()
    {
        require_once SGL_ENT_DIR . '/Section.php';
        $section = & new DataObjects_Section();
        
        //  get only top-level sections
        $section->level_id = 1;
        
        //  execute query and return the id of the first section found
        $section->find();
        $section->fetch();
        return $section->section_id;
    }

    /**
     * Accessor for current stylesheet name.
     *
     * @return  string name of current stylesheet from module's conf file
     * @access  private
     */
    function getCurrentStyle()
    {
        if (!isset($this->_currentStyle)) {
            $conf = & $GLOBALS['_SGL']['CONF'];
            $this->_currentStyle = $conf['navigation']['stylesheet'];
        }
        return $this->_currentStyle;
    }

    function _redirectToDefault(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);       

        //  if no errors have occured, redirect
        if (!(count($GLOBALS['_SGL']['ERRORS']))) {
            SGL_HTTP::redirect(array('rid' => $input->rid));

        //  else display error with blank template
        } else {
            $output->template = 'docBlank.html';
        }
    }
}
?>