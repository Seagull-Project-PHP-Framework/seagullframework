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
// | WizardMgr.php                                                              |
// +---------------------------------------------------------------------------+
// | Author:   Tobias Kuckuck <kuckuck@rancon.de>                           |
// +---------------------------------------------------------------------------+
// $Id: WizardMgr.php,v 1.1 2005/04/04 10:41:09 demian Exp $

/**
 *
 * Initialisation of wizard
 *
 * @package wizardexample
 * @author  Tobias Kuckuck <kuckuck@rancon.de>
 * @copyright Tobias Kuckuck 2005
 * @version $Revision: 1.1 $
 * @since   PHP 4.1
 */
class WizardMgr extends SGL_Manager
{
    function WizardMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }
    
    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated    = true;

        // initialize wizard pages        
        SGL_HTTP_Session::set('wiz_sequence', array());
        SGL_Controller::addPage(array('managerName' => 'Page1Wiz'));
        SGL_Controller::addPage(array('managerName' => 'Page2Wiz'));
    }    

    function process(&$input, &$output)
    {
        SGL_Controller::startWizard();
    }
}
?>
