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
// | ContactUsMgr.php                                                          |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: ContactUsMgr.php,v 1.30 2005/05/18 23:30:50 demian Exp $

require_once 'Validate.php';

/**
 * To allow users to contact site admins.
 *
 * @package contactus
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.30 $
 * @since   PHP 4.1
 */
class ContactUsMgr extends SGL_Manager
{
    function ContactUsMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->module      = 'contactus';
        $this->pageTitle   = 'Contact Us';
        $this->template    = 'contact.html';

        $this->_aActionsMapping =  array(
            'list'  => array('list'), 
            'send'  => array('send', 'redirectToDefault'), 
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated    = true;
        $input->error       = null;
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template    = $this->template;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->submit      = $req->get('submitted');
        $input->contact     = (object)$req->get('contact');

        //  if enquiry_type var is in $_GET
        if ($req->get('enquiry_type')) {
            if (!(isset($input->contact->enquiry_type))) {
                $input->contact->enquiry_type = $req->get('enquiry_type');
            }
        }
        $input->token = $req->get('token');

        $aErrors = array();
        if ($input->submit) {

            //  check form security token generated in display
            if ($input->token != SGL_HTTP_Session::get('token')) {
                SGL::logMessage('Invalid POST from ' . gethostbyaddr($_SERVER['REMOTE_ADDR']), PEAR_LOG_ALERT);
                SGL::raiseMsg('Invalid POST source');
                $aParams = array(
                    'moduleName'    => 'default',
                    'managerName'   => 'default',
                    );
                SGL_HTTP::redirect($aParams);
            }
            $v = & new Validate();
            if (empty($input->contact->first_name)) {
                $aErrors['first_name'] = 'You must enter your first name';
            }
            if (empty($input->contact->last_name)) {
                $aErrors['last_name'] = 'You must enter your last name';
            }
            if (empty($input->contact->email)) {
                $aErrors['email'] = 'You must enter your email';
            } else {
                if (!$v->email($input->contact->email)) {
                    $aErrors['email'] = 'Your email is not correctly formatted';
                }
            }
            if (empty($input->contact->user_comment)) {
                $aErrors['user_comment'] = 'You must fill in your comment';
            }
        }
        //  if errors have occured
        if (is_array($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  build contact type dropdown
        $output->aContactType = SGL_String::translate('aContactType');

        //  generate one-time token for additional form security
        $output->token = md5(time());
        SGL_HTTP_Session::set('token', $output->token);
    }

    function _send(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        //  require Contact entity
        require_once SGL_ENT_DIR . '/Contact_us.php';

        //  1. Take data from validated contact object and pass
        //  to sendEmail() method
        $bEmailSent = $this->sendEmail($input->contact);
        //  2. If email sending is successfull:
        if ($bEmailSent) {

            //  3. insert contact details in the contact table
            $contact = & new DataObjects_Contact_us();
            $contact->setFrom($input->contact);
            $dbh = $contact->getDatabaseConnection();
            $contact->contact_us_id = $dbh->nextId('contact_us');
            $contact->insert();

            //  4. redirect on success - inherited redirectToDefault method forwards user to default page
            SGL::raiseMsg('email submitted successfully');
        }

        //  5. else if sending fails, raise error, this will be hidden
        //  if 'production' is set to true in config
        else {
            SGL::raiseError('Problem sending email', SGL_ERROR_EMAILFAILURE);
        }
    }

    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  require Contact and Usr entities
        require_once SGL_ENT_DIR . '/Contact_us.php';
        require_once SGL_ENT_DIR . '/Usr.php';

        //  1. Set template
        //  The default template set in the class vars is copied to the 
        //  input object in validate which in turn gets passed to process.
        //  In some cases you'll want different templates for each case 
        //  statement, do this by setting $output->template = 'yourTemplates.html'

        //  2. Detect user status
        //  If user is logged on, prepopulate form with his/her 
        //  details.

        //  check user auth level
        $contact = & new DataObjects_Contact_us();
        if (SGL_HTTP_Session::getUserType() != SGL_GUEST) {

            //  instantiate new User entity
            $user = & new DataObjects_Usr();
            $user->get(SGL_HTTP_Session::getUid());

            //  instantiate Contact_us entity which will hold User data
            $contact->first_name = $user->first_name;
            $contact->last_name = $user->last_name;
            $contact->email = $user->email;

            //  and send populated contact object to output
        }

        if (!(empty($input->contact->enquiry_type))) {
            $contact->enquiry_type = $input->contact->enquiry_type;
        }
        $output->contact = $contact;
    }

    function sendEmail($oContact)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        require_once SGL_CORE_DIR . '/Emailer.php';
        $conf = & $GLOBALS['_SGL']['CONF'];
        $contacterName = $oContact->first_name . ' ' . $oContact->last_name;
        $options = array(
                'toEmail'       => $conf['email']['info'],
                'toRealName'    => 'Admin',
                'fromEmail'     => $oContact->email,
                'fromRealName'  => $contacterName,
                'replyTo'       => $oContact->email,
                'subject'       => SGL_String::translate('Contact Enquiry from') .' '. $conf['site']['name'],
                'type'          => $oContact->enquiry_type,
                'body'          => $oContact->user_comment,
                'template'      => SGL_THEME_DIR . '/' . $_SESSION['aPrefs']['theme'] . '/' . 
                    $this->module . '/email_contact_us.php',
        );
        $message = & new SGL_Emailer($options);
        $message->prepare();
        return $message->send();
    }
}
?>
