<?php

require_once 'SGL/Emailer2.php';
require_once SGL_MOD_DIR . '/translation/classes/Translation2.php';
require_once SGL_MOD_DIR . '/user2/classes/User2DAO.php';

/**
 * Sends email with password recovery information.
 *
 * @package user2
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class EmailPasswordRecovery extends SGL_Observer
{
    public function __construct()
    {
        $this->da = User2DAO::singleton();
    }

    public function update($observable)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $userId = $this->da->getUserIdByUsername(
            $observable->input->user->username,
            $observable->input->user->email
        );
        $aPrefs    = $this->da->getPreferencesByUserId($userId);
        $defModule = SGL_Config::get('site.defaultModule');
        $siteName  = SGL_Config::get('site.name');

        $c     = new SGL_Config();
        $aConf = $c->load(SGL_MOD_DIR . '/' . $defModule . '/conf.ini');
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
        $langCode = reset(explode('-', $aPrefs['language']));
//        $lang = SGL::getCurrentLang() . '-' . SGL::getCurrentCharset();
        SGL_Translation2::loadDictionary($defModule . '_email', $aPrefs['language']);
        SGL_Translation2::loadDictionary('user2_email', $aPrefs['language']);

        // delivery options
        $aDelOpts['toEmail']      = $observable->input->user->email;
        $aDelOpts['toRealName']   = $observable->input->user->username;
        $aDelOpts['fromEmail']    = $aConf['fromEmail'];
        $aDelOpts['fromRealName'] = SGL_Output::tr('email signature password recovery %siteName%',
            'vprintf', array('siteName' => $siteName));
        $aDelOpts['subject']      = SGL_Output::tr('email subject password recovery %siteName%',
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
        $aTplOpts['lang']       = $aPrefs['language'];
        $aTplOpts['membername'] = $aDelOpts['toRealName'];
        $aTplOpts['resetUrl']   = $observable->output->makeUrl(
            'reset', 'passwordrecovery', 'user2', array(),
            'userId|' . $userId . '||k|' . $observable->input->hash
                . '||lang|' . $langCode
        );
        $aTplOpts['signature']  = $aConf['signature'];
        // obligatory template options
        $aTplOpts['textTemplate'] = SGL_MOD_DIR . '/user2/templates/email/emailPasswordRecovery.html';
        $aTplOpts['mode']         = SGL_Emailer_Builder::MODE_TEXT_ONLY;

        $ok = SGL_Emailer2::send($aDelOpts, $aTplOpts);
        return $ok;
    }
}
?>