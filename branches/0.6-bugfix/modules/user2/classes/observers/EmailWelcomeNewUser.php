<?php

require_once 'SGL/Emailer2.php';
require_once SGL_MOD_DIR . '/translation/classes/Translation2.php';

/**
 * Sends email to new user with new username and password.
 *
 * @package user2
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class EmailWelcomeNewUser extends SGL_Observer
{
    private function _getFullName($oUser)
    {
        $ret = trim($oUser->first_name . ' ' . $oUser->last_name);
        if (empty($ret)) {
            $ret = $oUser->username;
        }
        return $ret;
    }

    public function update($observable)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $defModule = SGL_Config::get('site.defaultModule');
        $siteName  = SGL_Config::get('site.name');

        // load main module's config
        $c     = new SGL_Config();
        $aConf = $c->load(SGL_MOD_DIR . '/' . SGL_Config::get('site.defaultModule') . '/conf.ini');
        $aConf = isset($aConf['email_data'])
            ? $aConf['email_data']
            : array(
                  'testMode'  => false,
                  'testEmail' => false,
                  'bccEmail'  => false,
                  'fromEmail' => SGL_Config::get('email.admin'),
                  'signature' => $siteName
              );

        // load translations
        $lang = SGL::getCurrentLang() . '-' . SGL::getCurrentCharset();
        SGL_Translation2::loadDictionary($defModule . '_email', $lang);
        SGL_Translation2::loadDictionary('user2_email', $lang);

        // delivery options
        $aDelOpts['toEmail']      = $observable->input->user->email;
        $aDelOpts['toRealName']   = $this->_getFullName($observable->input->user);
        $aDelOpts['fromEmail']    = $aConf['fromEmail'];
        $aDelOpts['fromRealName'] = SGL_Output::tr('email signature welcome %siteName%',
            'vprintf', array('siteName' => $siteName));
        $aDelOpts['subject']      = SGL_Output::tr('email subject welcome %siteName%',
            'vprintf', array('siteName' => $siteName));

        // test opts
        if ($aConf['testMode']) {
            $aDelOpts['toEmail'] = $aConf['testEmail'];
            $aDelOpts['subject'] = 'TEST: ' . $aDelOpts['subject'];
        }
        if ($aConf['bccEmail']) {
            $aDelOpts['bcc'] = $aConf['bccEmail'];
        }

        // template vars
        $aTplOpts['lang']         = SGL::getCurrentLang();
        $aTplOpts['membername']   = $aDelOpts['toRealName'];
        $aTplOpts['user']         = $observable->input->user;
        $aTplOpts['signature']    = $aConf['signature'];
        // obligatory template options
        $aTplOpts['textTemplate'] = SGL_MOD_DIR . '/user2/templates/email/emailWelcomeNewUser.html';
        $aTplOpts['mode']         = SGL_Emailer_Builder::MODE_TEXT_ONLY;

        $ok = SGL_Emailer2::send($aDelOpts, $aTplOpts);
        return $ok;
    }
}
?>