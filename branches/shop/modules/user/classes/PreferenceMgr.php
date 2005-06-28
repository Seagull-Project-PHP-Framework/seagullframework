<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2004, Demian Turner                                         |
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
// | PreferenceMgr.php                                                         |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: PreferenceMgr.php,v 1.39 2005/05/17 23:54:53 demian Exp $

require_once SGL_MOD_DIR . '/user/classes/DA_User.php';

/**
 * Manages user permissions.
 *
 * @package User
 * @author  Demian Turner <demian@phpkitchen.com>
 * @copyright Demian Turner 2004
 * @version $Revision: 1.39 $
 * @since   PHP 4.1
 */
class PreferenceMgr extends SGL_Manager
{
    function PreferenceMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();
        $this->module       = 'user';
        $this->template     = 'prefManager.html';
        $this->pageTitle    = 'Preference Manager';
        $this->da           = & DA_User::singleton();

        $this->_aActionsMapping =  array(
            'add'       => array('add'), 
            'insert'    => array('insert', 'redirectToDefault'),
            'edit'      => array('edit'), 
            'update'    => array('update', 'redirectToDefault'), 
            'delete'    => array('delete', 'redirectToDefault'), 
            'list'      => array('list'), 
        );

        $this->aThemes = SGL_Util::getAllThemes();
        $this->aDateFormats = array(
            'UK' => 'UK',
            'US' => 'US',
            'FR' => 'FR',
            'BR' => 'BR',
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
        $input->submit          = $req->get('submitted');
        $input->action          = ($req->get('action')) ? $req->get('action') : 'list';
        $input->from            = ($req->get('frmFrom'))?$req->get('frmFrom'):0;
        $input->prefId          = $req->get('frmPrefId');
        $input->currentModule   = $req->get('frmCurrentModule');
        $input->pref            = (object) $req->get('pref');
        $input->aDelete         = $req->get('frmDelete');
        $input->totalItems      = $req->get('totalItems');

        $aErrors = array();
        if ($input->submit) {
            if (empty($input->pref->name)) {
                $aErrors['name'] = 'You must enter a preference name';
            }
            if (is_null($input->pref->default_value) || !strlen($input->pref->default_value)) {
                $aErrors['default_value'] = 'You must enter a default value';
            }
        }
        //  if errors have occured
        if (is_array($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            if ($input->action == 'insert') {
                $input->pageTitle = $this->pageTitle . ' :: Add';
                $input->template = 'prefAdd.html';
            } else {
                $input->pageTitle = $this->pageTitle . ' :: Edit';
                $input->template = 'prefEdit.html';
            }
            $this->validated = false;
        }
    }
    
    function display(&$output)
    {
        //  load available languages
        $availableLanguages = $GLOBALS['_SGL']['LANGUAGE'];
        uasort($availableLanguages, 'SGL_cmp');
        foreach ($availableLanguages as $id => $tmplang) {
            $lang_name = ucfirst(substr(strstr($tmplang[0], '|'), 1));
            $aLangOptions[$id] =  $lang_name . ' (' . $id . ')';
        }
        $output->aLangs = $aLangOptions;
        
        //  FIXME: unix-only, create fallback for windows
        $locales = explode("\n", @shell_exec('locale -a'));

        //  remap to hash
        foreach ($locales as $locale) {
            $aLocales[$locale] = $locale;
        }        
        $output->aLocales = $aLocales;
        require_once SGL_DAT_DIR . '/ary.timezones.en.php';
        $output->aTimezones = $tz;        
    }

    function _add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        require_once SGL_ENT_DIR . '/Preference.php';
        $output->template = 'prefAdd.html';
        $output->pageTitle = $this->pageTitle . ' :: Add';
        $output->pref = & new DataObjects_Preference();
    }

    function _insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        require_once SGL_ENT_DIR . '/Preference.php';
        $oPref = & new DataObjects_Preference();
        $oPref->setFrom($input->pref);
        $dbh = & $oPref->getDatabaseConnection();
        $oPref->preference_id = $dbh->nextId('preference');
        $success = $oPref->insert();
        if ($success) {
            //  synchronise with user_preference table
            $ret = $this->da->syncDefaultPrefs();
            SGL::raiseMsg('pref successfully added');
        } else {
           SGL::raiseError('There was a problem inserting the record', 
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        require_once SGL_ENT_DIR . '/Preference.php';
        $output->template = 'prefEdit.html';
        $output->pageTitle = $this->pageTitle . ' :: Edit';
        $oPref = & new DataObjects_Preference();
        $oPref->get($input->prefId);
        $output->pref = $oPref;
    }

    function _update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        require_once SGL_ENT_DIR . '/Preference.php';
        $oPref = & new DataObjects_Preference();
        $oPref->get($input->pref->preference_id);
        $oPref->setFrom($input->pref);
        unset($oPref->name);
        
        //  don't check for success because pref.name must remain the same
        $changed = $oPref->update();

        //  propagate changes to user_preference table
        if ($changed) {
            $ret = $this->da->syncDefaultPrefs();
        }
        SGL::raiseMsg('pref successfully updated');
    }

    function _delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        require_once SGL_ENT_DIR . '/Preference.php';
        require_once SGL_ENT_DIR . '/User_preference.php';
        $aToDelete = array();
        foreach ($input->aDelete as $index => $prefId) {
            $oPref = & new DataObjects_Preference();
            $oPref->get($prefId);
            $oPref->delete();
            $aToDelete[] = $prefId;
            unset($oPref);
        }
        //  delete related user_prefs
        foreach ($aToDelete as $deleteId) {
            $oUserPref = & new DataObjects_User_preference();
            $oUserPref->get('preference_id', $deleteId);
            $oUserPref->delete();
            unset($oUserPref);
        }
        SGL::raiseMsg('pref successfully deleted');
    }

    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->pageTitle = $this->pageTitle . ' :: Browse';
        $dbh = & SGL_DB::singleton();
        $query = 'SELECT preference_id, name, default_value FROM preference';
        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'      => 'Sliding',
            'delta'     => 3,
            'perPage'   => $limit,
            'totalItems'=> $input->totalItems,
        );

        $aPagedData = SGL_DB::getPagedData($dbh, $query, $pagerOptions);
        $output->aPagedData = $aPagedData;
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }
        $output->addOnLoadEvent('document.frmUserMgrChooser.prefs.disabled = true');
    }
}
?>