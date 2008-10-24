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
// | Seagull 2.0                                                               |
// +---------------------------------------------------------------------------+
// $Id$

/**
 * @package Task
 */
class SGL2_Task_SetupConstants extends SGL2_Task
{
    public function run($conf = array())
    {
        // framework file structure
        if (defined('SGL2_PEAR_INSTALLED')) {
            define('SGL2_VAR_DIR',              '@DATA-DIR@/Seagull/var');
            define('SGL2_ETC_DIR',              '@DATA-DIR@/Seagull/etc');
            define('SGL2_APP_ROOT',             '@PHP-DIR@/Seagull');
        } else {
            define('SGL2_VAR_DIR',               SGL2_PATH . '/var');
            define('SGL2_ETC_DIR',               SGL2_PATH . '/etc');
            define('SGL2_APP_ROOT',              SGL2_PATH);
        }
        define('SGL2_LOG_DIR',                   SGL2_VAR_DIR . '/log');
        define('SGL2_CACHE_DIR',                 SGL2_VAR_DIR . '/cache');
        define('SGL2_LIB_DIR',                   SGL2_APP_ROOT . '/lib');
        define('SGL2_ENT_DIR',                   SGL2_CACHE_DIR . '/entities');
        define('SGL2_DAT_DIR',                   SGL2_APP_ROOT . '/lib/data');
        define('SGL2_CORE_DIR',                  SGL2_APP_ROOT . '/lib/SGL');

        //  error codes
        //  start at -100 in order not to conflict with PEAR::DB error codes

        /**
         * Wrong args to function.
         */
        define('SGL2_ERROR_INVALIDARGS',         -101);
        /**
         * Something wrong with the config.
         */
        define('SGL2_ERROR_INVALIDCONFIG',       -102);
        /**
         * No data available.
         */
        define('SGL2_ERROR_NODATA',              -103);
        /**
         * No class exists.
         */
        define('SGL2_ERROR_NOCLASS',             -104);
        /**
         * No method exists.
         */
        define('SGL2_ERROR_NOMETHOD',            -105);
        /**
         * No rows were affected by query.
         */
        define('SGL2_ERROR_NOAFFECTEDROWS',      -106);
        /**
         * Limit queries on unsuppored databases.
         */
        define('SGL2_ERROR_NOTSUPPORTED'  ,      -107);
        /**
         * Invalid call.
         */
        define('SGL2_ERROR_INVALIDCALL',         -108);
        /**
         * Authentication failure.
         */
        define('SGL2_ERROR_INVALIDAUTH',         -109);
        /**
         * Failed to send email.
         */
        define('SGL2_ERROR_EMAILFAILURE',        -110);
        /**
         * Failed to connect to DB.
         */
        define('SGL2_ERROR_DBFAILURE',           -111);
        /**
         * A DB transaction failed.
         */
        define('SGL2_ERROR_DBTRANSACTIONFAILURE',-112);
        /**
         * User not allow to access site.
         */
        define('SGL2_ERROR_BANNEDUSER',          -113);
        /**
         * File not found.
         */
        define('SGL2_ERROR_NOFILE',              -114);
        /**
         * Perms were invalid.
         */
        define('SGL2_ERROR_INVALIDFILEPERMS',    -115);
        /**
         * Session was invalid.
         */
        define('SGL2_ERROR_INVALIDSESSION',      -116);
        /**
         * Posted data was invalid.
         */
        define('SGL2_ERROR_INVALIDPOST',         -117);
        /**
         * Translation invalid.
         */
        define('SGL2_ERROR_INVALIDTRANSLATION',  -118);
        /**
         * Could not write to the file.
         */
        define('SGL2_ERROR_FILEUNWRITABLE',      -119);
        /**
         * Method perms were invalid.
         */
        define('SGL2_ERROR_INVALIDMETHODPERMS',  -120);
        /**
         * Authorisation is invalid.
         */
        define('SGL2_ERROR_INVALIDAUTHORISATION',  -121);
        /**
         * Request was invalid.
         */
        define('SGL2_ERROR_INVALIDREQUEST',      -122);
        /**
         * Type invalid.
         */
        define('SGL2_ERROR_INVALIDTYPE',         -123);
        /**
         * Excessive recursion occured.
         */
        define('SGL2_ERROR_RECURSION',           -124);
        /**
         * Resource could not be found.
         */
        define('SGL2_ERROR_RESOURCENOTFOUND',    -404);

        //  message types to use with SGL:raiseMsg($msg, $translation, $msgType)
        define('SGL2_MESSAGE_ERROR',             0);  // by default
        define('SGL2_MESSAGE_INFO',              1);
        define('SGL2_MESSAGE_WARNING',           2);

        //  automate sorting
        define('SGL2_SORTBY_GRP',                1);
        define('SGL2_SORTBY_USER',               2);
        define('SGL2_SORTBY_ORG',                3);

        //  Seagull user roles
        define('SGL2_ANY_ROLE',                  -2);
        define('SGL2_UNASSIGNED',                -1);
        define('SGL2_GUEST',                     0);
        define('SGL2_ADMIN',                     1);
        define('SGL2_MEMBER',                    2);

        //  define return types, k/v pairs, arrays, strings, etc
        define('SGL2_RET_NAME_VALUE',            1);
        define('SGL2_RET_ID_VALUE',              2);
        define('SGL2_RET_ARRAY',                 3);
        define('SGL2_RET_STRING',                4);

        //  various
        define('SGL2_ANY_SECTION',               0);
        define('SGL2_NEXT_ID',                   0);
        define('SGL2_NOTICES_DISABLED',          0);
        define('SGL2_NOTICES_ENABLED',           1);

        // On install, $conf is empty let's load it
        if (empty($conf) && SGL2_File::exists(SGL2_ETC_DIR . '/customInstallDefaults.ini')) {
            $c = SGL2_Config::singleton();
            $conf1 = $c->load(SGL2_ETC_DIR . '/customInstallDefaults.ini');
            if (isset($conf1['path']['moduleDirOverride'])) {
                $conf['path']['moduleDirOverride'] = $conf1['path']['moduleDirOverride'];
            }
        // On re-install or INSTALL_COMPLETE
        } elseif (count($conf)) {
            define('SGL2_SEAGULL_VERSION', $conf['tuples']['version']);

            //  which degree of error severity before emailing admin
            define('SGL2_EMAIL_ADMIN_THRESHOLD',
                SGL2_String::pseudoConstantToInt($conf['debug']['emailAdminThreshold']));
            define('SGL2_BASE_URL', $conf['site']['baseUrl']);
        }

        if (isset($conf['path']['webRoot'])) {
            define('SGL2_WEB_ROOT', $conf['path']['webRoot']);
        } elseif (defined('SGL2_PEAR_INSTALLED')) {
            define('SGL2_WEB_ROOT', '@WEB-DIR@/Seagull/www');
        } else {
            define('SGL2_WEB_ROOT', SGL2_PATH . '/www');
        }

        define('SGL2_THEME_DIR', SGL2_WEB_ROOT . '/themes');
        if (!empty($conf['path']['moduleDirOverride'])) {
            define('SGL2_MOD_DIR', SGL2_PATH . '/' . $conf['path']['moduleDirOverride']);
        } else {
            define('SGL2_MOD_DIR', SGL2_PATH . '/modules');
        }
        if (!empty($conf['path']['uploadDirOverride'])) {
            define('SGL2_UPLOAD_DIR', SGL2_PATH . $conf['path']['uploadDirOverride']);
        } else {
            define('SGL2_UPLOAD_DIR', SGL2_VAR_DIR . '/uploads');
        }
        if (!empty($conf['path']['tmpDirOverride'])) {
            define('SGL2_TMP_DIR', $conf['path']['tmpDirOverride']);
        } else {
            define('SGL2_TMP_DIR', SGL2_VAR_DIR . '/tmp');
        }
    }
}
?>