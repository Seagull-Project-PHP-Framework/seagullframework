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
// | OrgPreferenceMgr.php                                                      |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: OrgPreferenceMgr.php,v 1.21 2005/05/23 22:51:12 demian Exp $

require_once SGL_MOD_DIR . '/user/classes/PreferenceMgr.php';

/**
 * Manages user preferences.
 *
 * @package User
 * @author  Demian Turner <demian@phpkitchen.com>
 * @copyright Demian Turner 2004
 * @version $Revision: 1.21 $
 * @since   PHP 4.1
 */
class OrgPreferenceMgr extends PreferenceMgr
{
    var $aThemes = array();
    var $aDateFormats = array();
    var $aTimeouts = array();
    var $aResPerPage = array();

    function OrgPreferenceMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::PreferenceMgr();
        $this->module       = 'user';
        $this->template     = 'prefOrgEdit.html';
        $this->pageTitle    = 'Organisation Preferences';

        $this->_aActionsMapping =  array(
            'editAll'   => array('editAll'), 
            'updateAll' => array('updateAll', 'redirectToDefault'), 
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated    = true;
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template    = $this->template;
        $input->submit      = $req->get('submitted');
        $input->orgId       = $req->get('frmOrgId');
        $input->action      = ($req->get('action')) ? $req->get('action') : 'editAll';
        $input->aPrefs      = $req->get('prefs');
    }

    function _editAll(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aPrefs = $this->da->getUserPrefsByOrgId($input->orgId);
        $output->aDateFormats = $this->aDateFormats;        
        $output->aThemes = $this->aThemes;
        $output->aTimeouts = $this->aTimeouts;
        $output->aResPerPage = $this->aResPerPage;
        $output->prefs = $aPrefs;
    }

    function _updateAll(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $dbh = & SGL_DB::singleton();
        $query1 = ' DELETE FROM org_preference
                    WHERE organisation_id = ' . $input->orgId;
        $dbh->query($query1);

        //  get prefName/id mapping
        $aMapping = $this->da->getPrefsMapping();
        foreach ($input->aPrefs as $prefName => $prefValue) {
            $query2 ="
            INSERT INTO org_preference 
                (   org_preference_id, 
                    organisation_id, 
                    preference_id, 
                    value)
            VALUES(" . 
                    $dbh->nextId('org_preference') . ", 
                    $input->orgId,
                    $aMapping[$prefName],
                    '$prefValue'
            )";
            $res = $dbh->query($query2);
            if (DB::isError($res)) {
                SGL::raiseError('Error inserting prefs, exiting ...', 
                    SGL_ERROR_NODATA, PEAR_ERROR_DIE);
            }
        }
        //  redirect on success
        SGL::raiseMsg('org details successfully updated');
    }
}
?>