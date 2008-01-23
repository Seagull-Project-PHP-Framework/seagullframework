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
// | Gallery2Mgr.php                                                           |
// +---------------------------------------------------------------------------+
// | Author: Matti Tahvonen <mstahv@utu.fi>                                    |
// +---------------------------------------------------------------------------+
// $Id: ManagerTemplate.html,v 1.2 2005/04/17 02:15:02 demian Exp $

/**
 * Manager for embedding Gallery2 application.
 *
 * For detailed integration instructions please see
 * http://trac.seagullproject.org/wiki/Integration/Gallery
 *
 * @package seagull
 * @author  Matti Tahvonen <mstahv@utu.fi>
 */
class Gallery2Mgr extends SGL_Manager
{
    function Gallery2Mgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Image Gallery';
        $this->template     = 'gallery2List.html';

        $this->_aActionsMapping =  array(
            'list'      => array('list'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = "masterMinimal.html";
        $input->template    = $this->template;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';

        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }
    }

    function _cmd_list(&$input, &$output) {

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $g2dir           = $this->conf['Gallery2Mgr']['g2Dir'];
        $g2embedUri      = $this->conf['Gallery2Mgr']['embedUri'];
        $g2Uri           = $this->conf['Gallery2Mgr']['g2Uri'];
        $g2loginRedirect = $this->conf['Gallery2Mgr']['loginRedirect'];
        $uid = SGL_Session::getUid();
        if ($uid < 1) {
            $uid = '';
        }
        require_once $g2dir . '/embed.php';
        /** http://gallery.menalto.com/index.php?name=PNphpBB2&file=viewtopic&t=27321#124393  **/
        $ret = GalleryEmbed::init(array(
            'embedUri'      => $g2embedUri,
            'g2Uri'         => $g2Uri,
            'loginRedirect' => $g2loginRedirect,
            'activeUserId'  => $uid));

        if ($ret) {
            SGL::raiseMsg( "Creating Gallery 2 account" );
            require_once 'DB/DataObject.php';
            $user = DB_DataObject::factory($this->conf['table']['user']);
            $user->get($uid);
            $ret = GalleryEmbed::createUser($uid, array('username' => $user->username));
            if ($ret) {
                SGL::raiseMsg("Creation of Gallery2 user unsuccessful(". $uid . $user->username. ")" . $ret->getAsHtml());
                return;
            }
            $ret = GalleryEmbed::login($uid);
            if ($ret) {
                SGL::raiseMsg("Creating account failed");
                return;
            }
        }

        $g2data = GalleryEmbed::handleRequest();
        if ($g2data['isDone']) {
            exit; // G2 has already sent output (redirect or binary data)
        }
        // Use $g2data['headHtml'] and $g2data['bodyHtml']
        // to display G2 content inside embedding application
        // if you don't want to use $g2data['headHtml'] directly, you can get the css,
        // javascript and page title separately by calling...

        $output->g2body = $g2data['bodyHtml'];
        if (isset($g2data['headHtml'])) {
            list($title, $css, $javascript) = GalleryEmbed::parseHead($g2data['headHtml']);
            $output->g2title = $title;
            // next uncommented line needs modification to seagulls default header template:
            // {foreach:headerExtras,extra}
            // {extra:h}
            // {end:}
            $output->headerExtras = array_merge($css, $javascript);
        }
    }
}
?>
