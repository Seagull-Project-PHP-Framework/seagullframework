<?php

/**
 * @package Task
 */
class SGL_Task_SetupConstantsFinish extends SGL_Task
{
    public function run($conf = array())
    {
        //  include Log.php if logging enabled
        if (isset($conf['log']['enabled']) && $conf['log']['enabled']) {
            require_once 'Log.php';

        } else {
            //  define log levels to avoid notices, since Log.php not included
            define('PEAR_LOG_EMERG',    0);     /** System is unusable */
            define('PEAR_LOG_ALERT',    1);     /** Immediately action */
            define('PEAR_LOG_CRIT',     2);     /** Critical conditions */
            define('PEAR_LOG_ERR',      3);     /** Error conditions */
            define('PEAR_LOG_WARNING',  4);     /** Warning conditions */
            define('PEAR_LOG_NOTICE',   5);     /** Normal but significant */
            define('PEAR_LOG_INFO',     6);     /** Informational */
            define('PEAR_LOG_DEBUG',    7);     /** Debug-level messages */
        }
        // On install, $conf is empty let's load it
        if (empty($conf) && file_exists(SGL_ETC_DIR . '/customInstallDefaults.ini')) {
            $c = SGL_Config::singleton();
            $conf1 = $c->load(SGL_ETC_DIR . '/customInstallDefaults.ini');
            if (isset($conf1['path']['moduleDirOverride'])) {
                $conf['path']['moduleDirOverride'] = $conf1['path']['moduleDirOverride'];
            }
        // On re-install or INSTALL_COMPLETE
        } elseif (count($conf)) {
            define('SGL_SEAGULL_VERSION', $conf['tuples']['version']);

            //  which degree of error severity before emailing admin
            define('SGL_EMAIL_ADMIN_THRESHOLD',
                SGL_String::pseudoConstantToInt($conf['debug']['emailAdminThreshold']));
            define('SGL_BASE_URL', $conf['site']['baseUrl']);
        }

        if (isset($conf['path']['webRoot'])) {
            define('SGL_WEB_ROOT', $conf['path']['webRoot']);
        } elseif (defined('SGL_PEAR_INSTALLED')) {
            define('SGL_WEB_ROOT', '@WEB-DIR@/Seagull/www');
        } else {
            define('SGL_WEB_ROOT', SGL_PATH . '/www');
        }

        define('SGL_THEME_DIR', SGL_WEB_ROOT . '/themes');
        if (!empty($conf['path']['moduleDirOverride'])) {
            define('SGL_MOD_DIR', SGL_PATH . '/' . $conf['path']['moduleDirOverride']);
        } else {
            define('SGL_MOD_DIR', SGL_PATH . '/modules');
        }
        if (!empty($conf['path']['uploadDirOverride'])) {
            define('SGL_UPLOAD_DIR', SGL_PATH . $conf['path']['uploadDirOverride']);
        } else {
            define('SGL_UPLOAD_DIR', SGL_VAR_DIR . '/uploads');
        }
        if (!empty($conf['path']['tmpDirOverride'])) {
            define('SGL_TMP_DIR', $conf['path']['tmpDirOverride']);
        } else {
            define('SGL_TMP_DIR', SGL_VAR_DIR . '/tmp');
        }
    }
}

?>