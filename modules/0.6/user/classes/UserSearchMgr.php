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
// | UserSearchMgr.php                                                         |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: UserSearchMgr.php,v 1.10 2005/06/06 00:40:33 demian Exp $

require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';

/**
 * Manages searching for User objects.
 *
 * @package User
 * @author  Demian Turner <demian@phpkitchen.com>
 * @author  Jacob Hanson <jacdx@jacobhanson.com>
 * @version $Revision: 1.10 $
 */
class UserSearchMgr extends SGL_Manager
{
    function UserSearchMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle = 'User Manager';
        $this->template = 'userManagerSearch.html';
        $this->da = & UserDAO::singleton();

        $this->sortBy = 'usr_id';
        $this->validated = true;

        $this->_aActionsMapping = array(
            'add'       => array('add'),
            'search'    => array('search'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $input->action = ($req->get('action')) ? $req->get('action') : 'search';
        $input->template = $this->template;
        $input->masterTemplate  = 'masterMinimal.html';
        $input->pageTitle = $this->pageTitle . ' :: Search';
        $input->submitted = $req->get('submitted');
        $input->sortBy = SGL_Util::getSortBy($req->get('frmSortBy'), SGL_SORTBY_USER);
        $input->sortOrder = SGL_Util::getSortOrder($req->get('frmSortOrder'));

        //  Pager's total items value (maintaining it saves a count(*) on each request)
        $input->totalItems = $req->get('totalItems');

        //  search form data
        $input->search = $req->get('search');
        if (!empty($input->search['reg_date1'])) {
            $input->regDate1Str = SGL_Date::arrayToString($input->search['reg_date1']);
        }
        if (!empty($input->search['reg_date2'])) {
            $input->regDate2Str = SGL_Date::arrayToString($input->search['reg_date2']);
        }

        if (!isset($aErrors)) {
            $aErrors = array();
        }

        if ($input->submitted) {

            //  validate search form
            if (!empty($input->search['user_id']) && !(is_numeric($input->search['user_id']))) {
                $aErrors['user_id'] = 'You must specify a numeric id';
            }

            if ($input->search['reg_date_mod'] != 0) {

                //  only validate dates if a date modifier is selected, enabling date search
                if (!empty($input->search['reg_date1'])) {
                    $s = $input->search['reg_date1'];

                    //  make sure date is real
                    if (!checkdate($s['month'], $s['day'], $s['year'])) {
                        $aErrors['reg_date1'] = 'You must select a valid date';
                    }
                } else {
                    $aErrors['reg_date1'] = 'You must select a date';
                }

                if ($input->search['reg_date_mod'] == 4) {

                    //  only validate 2nd date if 'between' modifier is selected
                    if (!empty($input->search['reg_date2'])) {
                        $s = $input->search['reg_date2'];

                        //  make sure date is real
                        if (!checkdate($s['month'], $s['day'], $s['year'])) {
                            $aErrors['reg_date2'] = 'You must select a valid date';
                        }
                    } else {
                        $aErrors['reg_date2'] = 'You must select a date';
                    }
                }
            }
        }
        //  if errors have occured
        if (is_array($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
            $input->action = 'add';
        }
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->aRoles = $this->da->getRoles();
        if ($this->conf['OrgMgr']['enabled']) {
            $output->aOrgs = $this->da->getOrgs();
        }

        //  sync options
        $aSyncModes = array();
        $aSyncModes[SGL_ROLESYNC_ADDREMOVE] = SGL_String::translate('complete sync');
        $aSyncModes[SGL_ROLESYNC_REMOVE] = SGL_String::translate('remove extra perms');
        $aSyncModes[SGL_ROLESYNC_ADD] = SGL_String::translate('add missing perms');
        $output->aSyncModes = $aSyncModes;

        $output->regDate1 = SGL_Output::showDateSelector(@$output->regDate1Str,'search[reg_date1]', false);
        $output->regDate2 = SGL_Output::showDateSelector(@$output->regDate2Str,'search[reg_date2]', false);
        $output->regDateMod = array
            (
                '',
                SGL_String::translate('before'),
                SGL_String::translate('after'),
                SGL_String::translate('is'),
                SGL_String::translate('between')
            );

        $output->permSync = SGL_Output::generateCheckbox('perm_sync', '', @$output->search['perm_sync']);
        $output->status = array
            (
                '',
                SGL_String::translate('active'),
                SGL_String::translate('inactive')
            );
    }

    function _cmd_add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->addOnLoadEvent('updateFormInterface()');
    }

    function _cmd_search(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->pageTitle = $this->pageTitle . ' :: Browse';
        $output->template = 'userManager.html';
        $criteria = '';

        //  if search form data present, built search criteria SQL
        if (!(empty($input->search))) {

            $s = $input->search;
            if (array_key_exists('user_id', $s) && !empty($s['user_id'])) {
                $criteria .= " AND usr_id = {$s['user_id']} ";
            }
            if (array_key_exists('username', $s) && !empty($s['username'])) {
                $criteria .= " AND username LIKE '%{$s['username']}%' ";
            }
            if (array_key_exists('first_name', $s) && !empty($s['first_name'])) {
                $criteria .= " AND first_name LIKE '%{$s['first_name']}%' ";
            }
            if (array_key_exists('last_name', $s) && !empty($s['last_name'])) {
                $criteria .= " AND last_name LIKE '%{$s['last_name']}%' ";
            }
            if (array_key_exists('email', $s) && !empty($s['email'])) {
                $criteria .= " AND u.email LIKE '%{$s['email']}%' ";
            }
            if (array_key_exists('reg_date_mod', $s) && $s['reg_date_mod'] != 0) {
                if ($s['reg_date_mod'] == 4) {
                    //use both dates (between)
                    $criteria .= " AND u.date_created BETWEEN '{$input->regDate1Str}' AND '{$input->regDate2Str}' ";
                } else {
                    //use one date (before, after, is)
                    switch ($s['reg_date_mod']) {

                    case 1: $mod = '<'; break;
                    case 2: $mod = '>'; break;
                    case 3: $mod = '='; break;
                    }
                    $criteria .= " AND u.date_created $mod '{$input->regDate1Str}' ";
                }
            }
            if (array_key_exists('status', $s) && $s['status'] != 0) {
                $t = $s['status'] == 2 ? 0 : 1;
                $criteria .= " AND is_acct_active=$t ";
            }
            if (array_key_exists('roles', $s) && !empty($s['roles'])) {
                //search for all roles
                $criteria .= " AND (";
                $first = true;
                foreach ($s['roles'] as $k=>$v) {
                    if ($first) {
                        $first = false;
                    } else {
                        $criteria .= "OR ";
                    }
                    $criteria .= "u.role_id=$v ";
                }
                $criteria .= ") ";
            }
            if (array_key_exists('orgs', $s) && !empty($s['orgs'])) {
                if (!is_array($s['orgs'])) {
                    //search for one org
                    $criteria .= " AND u.organisation_id = {$s['orgs']} ";
                } else {
                    //search for multiple orgs
                    $criteria .= " AND (";
                    $first = true;
                    foreach ($s['orgs'] as $k=>$v) {
                        if ($first) {
                            $first = false;
                        } else {
                            $criteria .= "OR ";
                        }
                        $criteria .= "u.organisation_id=$v ";
                    }
                    $criteria .= ") ";
                }
            }
        }

        $allowedSortFields = array('usr_id','username','is_acct_active');
        if (  !empty($input->sortBy)
           && !empty($input->sortOrder)
           && in_array($input->sortBy, $allowedSortFields)) {
                $orderBy_query = 'ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder ;
        } else {
            $orderBy_query = ' ORDER BY u.usr_id ASC ';
        }

        if ($this->conf[SGL_Inflector::caseFix('OrgMgr')]['enabled']) {
            $query = "
                SELECT  u.*, o.name AS org_name, r.name AS role_name
                FROM    {$this->conf['table']['user']} u, {$this->conf['table']['organisation']} o, {$this->conf['table']['role']} r
                WHERE   o.organisation_id = u.organisation_id
                AND     r.role_id = u.role_id
                $criteria " . $orderBy_query;
        } else {
            $query = "
                SELECT  u.*, r.name AS role_name
                FROM    {$this->conf['table']['user']} u, {$this->conf['table']['role']} r
                WHERE   r.role_id = u.role_id
                $criteria " . $orderBy_query;
        }
        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'      => 'Sliding',
            'delta'     => 3,
            'perPage'   => $limit,
            'totalItems'=> $input->totalItems,
            'path'      => SGL_Output::makeUrl('search', 'usersearch', 'usersearch'),
            'append'    => true,
        );
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);
        if (PEAR::isError($aPagedData)) {
            SGL::raiseMsg('There was a database problem');
            $aPagedData = array();
        }

        $output->aPagedData = $aPagedData;
        if (isset($aPagedData['data']) && is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }
        $output->totalItems = @$aPagedData['totalItems'];

        $output->addOnLoadEvent("switchRowColorOnHover()");

    }
}
?>
