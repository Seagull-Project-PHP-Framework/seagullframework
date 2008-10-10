<?php

/**
 * @package Task
 */
class SGL_Task_SetupConstants extends SGL_Task
{
    public function run($conf = array())
    {
        // framework file structure
        if (defined('SGL_PEAR_INSTALLED')) {
            define('SGL_VAR_DIR',              '@DATA-DIR@/Seagull/var');
            define('SGL_ETC_DIR',              '@DATA-DIR@/Seagull/etc');
            define('SGL_APP_ROOT',             '@PHP-DIR@/Seagull');
        } else {
            define('SGL_VAR_DIR',               SGL_PATH . '/var');
            define('SGL_ETC_DIR',               SGL_PATH . '/etc');
            define('SGL_APP_ROOT',              SGL_PATH);
        }
        define('SGL_LOG_DIR',                   SGL_VAR_DIR . '/log');
        define('SGL_CACHE_DIR',                 SGL_VAR_DIR . '/cache');
        define('SGL_LIB_DIR',                   SGL_APP_ROOT . '/lib');
        define('SGL_ENT_DIR',                   SGL_CACHE_DIR . '/entities');
        define('SGL_DAT_DIR',                   SGL_APP_ROOT . '/lib/data');
        define('SGL_CORE_DIR',                  SGL_APP_ROOT . '/lib/SGL');

        //  error codes
        //  start at -100 in order not to conflict with PEAR::DB error codes

        /**
         * Wrong args to function.
         */
        define('SGL_ERROR_INVALIDARGS',         -101);
        /**
         * Something wrong with the config.
         */
        define('SGL_ERROR_INVALIDCONFIG',       -102);
        /**
         * No data available.
         */
        define('SGL_ERROR_NODATA',              -103);
        /**
         * No class exists.
         */
        define('SGL_ERROR_NOCLASS',             -104);
        /**
         * No method exists.
         */
        define('SGL_ERROR_NOMETHOD',            -105);
        /**
         * No rows were affected by query.
         */
        define('SGL_ERROR_NOAFFECTEDROWS',      -106);
        /**
         * Limit queries on unsuppored databases.
         */
        define('SGL_ERROR_NOTSUPPORTED'  ,      -107);
        /**
         * Invalid call.
         */
        define('SGL_ERROR_INVALIDCALL',         -108);
        /**
         * Authentication failure.
         */
        define('SGL_ERROR_INVALIDAUTH',         -109);
        /**
         * Failed to send email.
         */
        define('SGL_ERROR_EMAILFAILURE',        -110);
        /**
         * Failed to connect to DB.
         */
        define('SGL_ERROR_DBFAILURE',           -111);
        /**
         * A DB transaction failed.
         */
        define('SGL_ERROR_DBTRANSACTIONFAILURE',-112);
        /**
         * User not allow to access site.
         */
        define('SGL_ERROR_BANNEDUSER',          -113);
        /**
         * File not found.
         */
        define('SGL_ERROR_NOFILE',              -114);
        /**
         * Perms were invalid.
         */
        define('SGL_ERROR_INVALIDFILEPERMS',    -115);
        /**
         * Session was invalid.
         */
        define('SGL_ERROR_INVALIDSESSION',      -116);
        /**
         * Posted data was invalid.
         */
        define('SGL_ERROR_INVALIDPOST',         -117);
        /**
         * Translation invalid.
         */
        define('SGL_ERROR_INVALIDTRANSLATION',  -118);
        /**
         * Could not write to the file.
         */
        define('SGL_ERROR_FILEUNWRITABLE',      -119);
        /**
         * Method perms were invalid.
         */
        define('SGL_ERROR_INVALIDMETHODPERMS',  -120);
        /**
         * Authorisation is invalid.
         */
        define('SGL_ERROR_INVALIDAUTHORISATION',  -121);
        /**
         * Request was invalid.
         */
        define('SGL_ERROR_INVALIDREQUEST',      -122);
        /**
         * Type invalid.
         */
        define('SGL_ERROR_INVALIDTYPE',         -123);
        /**
         * Excessive recursion occured.
         */
        define('SGL_ERROR_RECURSION',           -124);
        /**
         * Resource could not be found.
         */
        define('SGL_ERROR_RESOURCENOTFOUND',    -404);

        //  message types to use with SGL:raiseMsg($msg, $translation, $msgType)
        define('SGL_MESSAGE_ERROR',             0);  // by default
        define('SGL_MESSAGE_INFO',              1);
        define('SGL_MESSAGE_WARNING',           2);

        //  automate sorting
        define('SGL_SORTBY_GRP',                1);
        define('SGL_SORTBY_USER',               2);
        define('SGL_SORTBY_ORG',                3);

        //  Seagull user roles
        define('SGL_ANY_ROLE',                  -2);
        define('SGL_UNASSIGNED',                -1);
        define('SGL_GUEST',                     0);
        define('SGL_ADMIN',                     1);
        define('SGL_MEMBER',                    2);

        //  define return types, k/v pairs, arrays, strings, etc
        define('SGL_RET_NAME_VALUE',            1);
        define('SGL_RET_ID_VALUE',              2);
        define('SGL_RET_ARRAY',                 3);
        define('SGL_RET_STRING',                4);

        //  various
        define('SGL_ANY_SECTION',               0);
        define('SGL_NEXT_ID',                   0);
        define('SGL_NOTICES_DISABLED',          0);
        define('SGL_NOTICES_ENABLED',           1);

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