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
// | PreferenceMgr.php                                                         |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: PreferenceMgr.php,v 1.39 2005/05/17 23:54:53 demian Exp $

require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
require_once 'DB/DataObject.php';

/**
 * Manages user permissions.
 *
 * @package User
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.39 $
 */
class PreferenceMgr extends SGL_Manager
{
    function PreferenceMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->template     = 'prefManager.html';
        $this->pageTitle    = 'Preference Manager';
        $this->da           = & UserDAO::singleton();

        $this->_aActionsMapping =  array(
            'add'       => array('add'),
            'insert'    => array('insert', 'redirectToDefault'),
            'edit'      => array('edit'),
            'update'    => array('update', 'redirectToDefault'),
            'updatePreferencesForAllMembers' => array('updatePreferencesForAllMembers', 'redirectToDefault'),
            'delete'    => array('delete', 'redirectToDefault'),
            'list'      => array('list'),
        );

        $this->aThemes = SGL_Util::getAllThemes();
        $this->aDateFormats = array(
            'UK' => 'UK',
            'US' => 'US',
            'FR' => 'FR',
            'BR' => 'BR',
            'DE' => 'DE',
            );
        $this->aTimeouts = array(
            '900' => '15 mins',
            '1800' => '30 mins',
            '3600' => '1 Hour',
            '7200' => '2 Hours',
            '10800' => '3 Hours',
            '28800' => '8 Hours',
            );
        $this->aResPerPage = array(
            '5' => '5',
            '10' => '10',
            '20' => '20',
            '50' => '50',
            );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated        = true;
        $input->pageTitle       = $this->pageTitle;
        $input->masterTemplate  = 'masterMinimal.html';
        $input->template        = $this->template;
        $input->submitted       = $req->get('submitted');
        $input->action          = ($req->get('action')) ? $req->get('action') : 'list';
        $input->from            = ($req->get('frmFrom'))?$req->get('frmFrom'):0;
        $input->prefId          = $req->get('frmPrefId');
        $input->prefRoleId      = $req->get('frmPrefRoleId');
        $input->currentModule   = $req->get('frmCurrentModule');
        $input->themeName       = $req->get('frmThemeName');
        $input->pref            = (object) $req->get('pref');
        $input->aDelete         = $req->get('frmDelete');
        $input->totalItems      = $req->get('totalItems');
        $input->sortBy          = SGL_Util::getSortBy($req->get('frmSortBy'), SGL_SORTBY_USER);
        $input->sortOrder       = SGL_Util::getSortOrder($req->get('frmSortOrder'));

        // This will tell HTML_Flexy which key is used to sort data
        $input->{ 'sort_' . $input->sortBy } = true;

        $aErrors = array();
        if ($input->submitted || in_array($input->action, array('insert', 'update'))) {
            if (empty($input->pref->name)) {
                $aErrors['name'] = 'You must enter a preference name';
            }
            if (!isset($input->pref->default_value)) {
                $aErrors['default_value'] = 'You must enter a default value';
            }
        }
        //  if errors have occured
        if (is_array($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;

            if ($input->action == 'insert') {
                $input->preferenceAdd = true;
            } else {
                $input->preferenceEdit = true;
            }
            $input->template = 'prefEdit.html';
            $this->validated = false;
        }
    }

    function display(&$output)
    {
        //  build map of available languages
        $output->aLangs = SGL_Util::getLangsDescriptionMap();

        //  FIXME: unix-only, create fallback for windows
        $locales = explode("\n", @shell_exec('locale -a'));

        //  remap to hash
        foreach ($locales as $locale) {
            $aLocales[$locale] = $locale;
        }
        $output->aLocales = $aLocales;
        $output->aThemes = $this->aThemes;
        require_once SGL_DAT_DIR . '/ary.timezones.en.php';
        $output->aTimezones = $tz;
        $aRoles = $this->da->getRoles();
        unset($aRoles[SGL_ADMIN]); // admin prefs cannot be set generically
        $output->aRoles = $aRoles;
    }

    function _cmd_add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'prefEdit.html';
        $output->preferenceAdd = true;
        $output->pageTitle = $this->pageTitle . ' :: Add';
        $output->pref = DB_DataObject::factory($this->conf['table']['preference']);
    }

    function _cmd_insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        SGL_DB::setConnection();
        $oPref = DB_DataObject::factory($this->conf['table']['preference']);
        $oPref->setFrom($input->pref);

        $oPref->preference_id = $this->dbh->nextId($this->conf['table']['preference']);
        $success = $oPref->insert();
        if ($success) {
            // add new preference to all users prefs
            $oUser = DB_DataObject::factory($this->conf['table']['user']);
            $oUser->find();
            while ($oUser->fetch()) {
                $ret = $this->da->addPrefsByUserId(
                    array($oPref->preference_id => $oPref->default_value), $oUser->usr_id);
            }
            SGL::raiseMsg('pref successfully added', true, SGL_MESSAGE_INFO);
        } else {
           SGL::raiseError('There was a problem inserting the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (is_null($input->prefId)) {
            SGL::raiseError('Incorrect parameter passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        } else {
            $output->preferenceEdit = true;
            $output->template = 'prefEdit.html';
            $output->pageTitle = $this->pageTitle . ' :: Edit';
            $oPref = DB_DataObject::factory($this->conf['table']['preference']);
            $oPref->get($input->prefId);
            $output->pref = $oPref;
        }
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $oPref = DB_DataObject::factory($this->conf['table']['preference']);
        $oPref->get($input->pref->preference_id);
        $oPref->setFrom($input->pref);
        unset($oPref->name);

        //  don't check for success because pref.name must remain the same
        $changed = $oPref->update();

        //  propagate changes to user_preference table
        if ($changed) {
            $ret = $this->da->syncDefaultPrefs();
        }
        SGL::raiseMsg('pref successfully updated', true, SGL_MESSAGE_INFO);
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $input
     * @param unknown_type $output
     * @todo should be renamed to _cmd_updatePreferencesForAllRoles
     */
    function _cmd_updatePreferencesForAllMembers(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (is_array($input->aDelete)) {
            $aRoles = $this->da->getRoles();
            unset($aRoles[SGL_ADMIN]);

            foreach ($input->aDelete as $index => $prefId) {
                if ($input->prefRoleId != -1) { // only update for a single role
                    $oPref = DB_DataObject::factory($this->conf['table']['preference']);
                    $oPref->get($prefId);
                    $ok = $this->da->updatePrefByRoleId($oPref->name, $oPref->default_value,
                        $input->prefRoleId);
                    unset($oPref);
                } else { // else for all roles
                    foreach ($aRoles as $roleId => $roleName) {
                        $oPref = DB_DataObject::factory($this->conf['table']['preference']);
                        $oPref->get($prefId);
                        $ok = $this->da->updatePrefByRoleId($oPref->name, $oPref->default_value,
                            $roleId);
                        unset($oPref);
                    }
                }
            }
            SGL::raiseMsg('Selected prefs have been set for all users', true,
                SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('Incorrect parameter passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
    }

    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aToDelete = array();
        if (is_array($input->aDelete)) {
            foreach ($input->aDelete as $index => $prefId) {
                $oPref = DB_DataObject::factory($this->conf['table']['preference']);
                $oPref->get($prefId);
                $oPref->delete();
                $aToDelete[] = $prefId;
                unset($oPref);
            }
            //  delete related user_prefs
            foreach ($aToDelete as $deleteId) {
                $oUserPref = DB_DataObject::factory($this->conf['table']['user_preference']);
                $oUserPref->get('preference_id', $deleteId);
                $oUserPref->delete();
                while ($oUserPref->fetch()) {
                    $oUserPref->delete();
                }
                unset($oUserPref);
            }
            SGL::raiseMsg('pref successfully deleted', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('Incorrect parameter passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->pageTitle = $this->pageTitle . ' :: Browse';

        $allowedSortFields = array('preference_id','name');
        if (  !empty($input->sortBy)
           && !empty($input->sortOrder)
           && in_array($input->sortBy, $allowedSortFields)) {
                $orderBy_query = 'ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder ;
        } else {
            $orderBy_query = 'ORDER BY preference_id ASC ';
        }

        $query = "
            SELECT  preference_id, name, default_value
            FROM    {$this->conf['table']['preference']}
            $orderBy_query";

        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'     => 'Sliding',
            'delta'    => 3,
            'perPage'  => $limit,
            'spacesBeforeSeparator' => 0,
            'spacesAfterSeparator'  => 0,
            'curPageSpanPre'        => '<span class="currentPage">',
            'curPageSpanPost'       => '</span>',
        );
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);
        $output->aPagedData = $aPagedData;
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }
        $output->addOnLoadEvent("switchRowColorOnHover()");
    }
}
?>
