<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Demian Turner                                         |
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
// | NavStyleMgr.php                                                           |
// +---------------------------------------------------------------------------+
// | Author: Andy Crain <crain@fuse.net>                                       |
// +---------------------------------------------------------------------------+
// $Id: NavStyleMgr.php,v 1.32 2005/06/23 19:15:26 demian Exp $

require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';

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
        parent::SGL_Manager();

        $this->pageTitle    = 'Navigation Style Manager';
        $this->template     = 'navStyleList.html';
        $this->da           = & UserDAO::singleton();

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
        $input->submitted       = $req->get('submitted');
        $input->newStyle        = $req->get('newStyle');
        $input->staticId        = $req->get('staticId');
        $input->rid             = (int)$req->get('rid');
    }

    function _cmd_changeStyle(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (array_key_exists($input->newStyle, SGL_Util::getStyleFiles($this->getCurrentStyle()))) {

            //  change [navigation][stylesheet] to $newStyle in default.conf.ini
            $c = &SGL_Config::singleton();
            $c->set('navigation', array('stylesheet' => $input->newStyle));

            //  write configuration to file
            $ok = $c->save();

            if (!is_a($ok, 'PEAR_Error')) {
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
    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'navStyleList.html';
        $output->styleFiles = SGL_Util::getStyleFiles($this->getCurrentStyle());
        $output->currentStyle = $this->getCurrentStyle();
        $output->staticId = (is_numeric($input->staticId))
            ? $input->staticId
            : $this->generateStaticId();

        //  build string of radio buttons html for selecting group
        $aRoles = $this->da->getRoles();
        $aRoles[0]= 'guest';
        $output->groupsRadioButtons = '';
        foreach ($aRoles as $rid => $role) {
            $radioChecked = ($rid == $input->rid)?' checked':'';

            $output->groupsRadioButtons .="\n". '<input type="radio"' . $radioChecked .
                ' onClick="location.href=\'' .
                SGL_Url::makeLink('list', 'navstyle', 'navigation', array(),
                    "staticId|{$output->staticId}||rid|$rid"). '\'">' . $role;
        }
        //  build html unordered list of sections
        $navDriver  = $this->conf['navigation']['driver'];
        $navDrvFile = SGL_MOD_DIR . '/navigation/classes/' . $navDriver . '.php';
        if (is_file($navDrvFile)) {
            require_once $navDrvFile;
        } else {
            SGL::raiseError('specified navigation driver does not exist', SGL_ERROR_NOFILE);
        }
        if (!class_exists($navDriver)) {
            SGL::raiseError('problem with navigation driver object', SGL_ERROR_NOCLASS);
        }
        $nav = & new $navDriver($output);

        $nav->setStaticId($output->staticId);
        $nav->setRid($input->rid);
        $nav->setDisableLinks(true);
        $navRenderer = $this->conf['navigation']['renderer'];
        $aRes = $nav->render($navRenderer);
        list($sectionId, $html) = $aRes;
        $output->navListPreview = $html;
        if (!$output->navListPreview) {
            $output->navListPreview = 'There are no sections accessible to members of the selected role: ' .
                $aRoles[$input->rid] . '.';
        }
    }

    function _cmd_redirectToDefault(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  if no errors have occured, redirect
        if (!SGL_Error::count()) {
            SGL_HTTP::redirect(array('rid' => $input->rid));

        //  else display error with blank template
        } else {
            $output->template = 'docBlank.html';
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
        require_once 'DB/DataObject.php';
        $section = DB_DataObject::factory($this->conf['table']['section']);

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
            $this->_currentStyle = $this->conf['navigation']['stylesheet'];
        }
        return $this->_currentStyle;
    }
}
?>