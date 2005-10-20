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
// | ProfileMgr.php                                                            |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: ProfileMgr.php,v 1.17 2005/06/08 10:07:28 demian Exp $

require_once SGL_MOD_DIR . '/user/classes/DA_User.php';

/**
 * Display user account account info.
 *
 * @package User
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.17 $
 */
class ProfileMgr extends SGL_Manager
{
    function ProfileMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();
                
        $this->module       = 'user';
        $this->pageTitle    = 'User Profile';
        $this->template     = 'profile.html';
        $this->da           = & DA_User::singleton();

        $this->_aActionsMapping =  array(
            'view'   => array('view'), 
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated        = true;
        $input->masterTemplate  = $this->masterTemplate;
        $input->template        = $this->template;
        $input->pageTitle       = $this->pageTitle;
        $input->action          = ($req->get('action')) ? $req->get('action') : 'view';
        $input->userId          = $req->get('frmUserID');
        $input->fromContacts    = $req->get('frmFromContacts');
    }

    function _view(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        require_once SGL_ENT_DIR . '/Usr.php';
        $user = & new DataObjects_Usr();
        $user->get($input->userId);
        $output->profile = $user;

        //  include countries array
        $lang = $_SESSION['aPrefs']['language'];
        if ($lang != 'en' && $lang != 'de' && $lang != 'it') {
            $lang = 'en';
        }
        require_once SGL_DAT_DIR . '/ary.countries.' . $lang . '.php';
        $output->profile->country = $countries[$user->country];

        //  get last login
        $output->login = $this->da->getLastLogin();

        //  total articles
        require_once SGL_ENT_DIR . '/Item.php';
        $items = & new DataObjects_Item();
        $items->created_by_id = $input->userId;
        $output->totalArticles = $items->count();

        //  set conditional 'back' button
        $output->backButton = (isset($input->fromContacts)) ? $input->fromContacts : false;

        //  if current user is viewing his/her own profile,
        //  disable 'add to contacts' & 'send message'
        $output->allowContact = ($input->userId == SGL_HTTP_Session::getUid()
                              || SGL_HTTP_Session::getUserType() == SGL_GUEST) 
            ? false 
            : true;
    }
}
?>
