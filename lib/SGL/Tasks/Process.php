<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Demian Turner                                         |
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
// | Process.php                                                                 |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: style.php,v 1.85 2005/06/22 00:40:44 demian Exp $

/**
 * Basic app process tasks: enables profiling, custom error handler, logging
 * and output buffering.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Process_Init extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        if (SGL_PROFILING_ENABLED && function_exists('apd_set_pprof_trace')) {
            apd_set_pprof_trace();
        }
        // load base utility lib
        require_once SGL_LIB_DIR . '/SGL.php';

        //  start output buffering
        if ($this->conf['site']['outputBuffering']) {
            ob_start();
        }

        $this->processRequest->process($input, $output);
    }
}

class SGL_Process_SetupORM extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $options = &PEAR::getStaticProperty('DB_DataObject', 'options');
        $options = array(
            'database'              => SGL_DB::getDsn(SGL_DSN_STRING),
            'schema_location'       => SGL_ENT_DIR,
            'class_location'        => SGL_ENT_DIR,
            'require_prefix'        => SGL_ENT_DIR . '/',
            'class_prefix'          => 'DataObjects_',
            'debug'                 => 0,
            'production'            => 0,
            'ignore_sequence_keys'  => 'ALL',
            'generator_strip_schema' => 1,
        );

        $this->processRequest->process($input, $output);
    }
}

/**
 * Block blacklisted users by IP.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Process_DetectBlackListing extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if ($this->conf['site']['banIpEnabled']) {
            $c = &SGL_Config::singleton();
            $conf = $c->getAll();

            if (!empty($conf['site']['allowList'])) {
                $allowList = explode( ' ', $conf['site']['allowList']);
                if ( !in_array( $_SERVER['REMOTE_ADDR'], $allowList)) {
                    $msg = SGL_String::translate('You have been banned');
                    SGL::raiseError($msg, SGL_ERROR_BANNEDUSER, PEAR_ERROR_DIE);
                }
            }

            if (!empty($conf['site']['denyList'])) {
                $denyList = explode( ' ', $conf['site']['denyList']);
                if ( in_array( $_SERVER['REMOTE_ADDR'], $denyList)) {
                    $msg = SGL_String::translate('You have been banned');
                    SGL::raiseError($msg, SGL_ERROR_BANNEDUSER, PEAR_ERROR_DIE);
                }
            }
        }

        $this->processRequest->process($input, $output);
    }
}

/**
 * Handle a debug session.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Process_DetectDebug extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  set debug session if allowed
        $req = $input->getRequest();
        $debug = $req->get('debug');
        if ($debug && SGL::debugAllowed()) {
            $debug = ($debug == 'on') ? 1 : 0;
            SGL_Session::set('debug', $debug);
        }

        $this->processRequest->process($input, $output);
    }
}

/**
 * Sets the current locale.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Process_SetupLocale extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $locale = $_SESSION['aPrefs']['locale'];
        $timezone = $_SESSION['aPrefs']['timezone'];
        $language = substr($locale, 0,2);

        if ($this->conf['site']['extendedLocale'] == false) {

            $cat = constant(str_replace("'", "", $this->conf['site']['localeCategory']));

            //  The default locale category is LC_ALL, but this will cause probs for
            //  european users who get their decimal points (.) changed to commas (,)
            //  and php numeric calculations will break.  The solution for these users
            //  is to select the LC_TIME category.  For a global effect change this in
            //  Config.
            if (setlocale($cat, $locale) == false) {
                setlocale(LC_TIME, $locale);
            }
            @putenv('TZ=' . $timezone);

            if (strtoupper(substr(PHP_OS, 0,3)) === 'WIN') {
                @putenv('LANG='     . $language);
                @putenv('LANGUAGE=' . $language);
            } else {
                @putenv('LANG='     . $locale);
                @putenv('LANGUAGE=' . $locale);
            }

        } else {
            require_once dirname(__FILE__) . '/../Locale.php';
            $setlocale = & SGL_Locale::singleton($locale);
        }

        $this->processRequest->process($input, $output);
    }
}

/**
 * Sets generic headers for page generation.
 *
 * Alternatively, headers can be suppressed if specified in module's config.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Process_BuildHeaders extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->processRequest->process($input, $output);

        //  don't send headers according to config
        $currentMgr = SGL_Inflector::caseFix(get_class($output->manager));
        if (!isset($this->conf[$currentMgr]['setHeaders'])
                || $this->conf[$currentMgr]['setHeaders'] == true) {

            //  set compression as specified in init, can only be done here :-)
            @ini_set('zlib.output_compression', (int)$this->conf['site']['compression']);

            //  build P3P headers
            if ($this->conf['p3p']['policies']) {
                $p3pHeader = '';
                if ($this->conf['p3p']['policyLocation'] != '') {
                    $p3pHeader .= " policyref=\"" . $this->conf['p3p']['policyLocation']."\"";
                }
                if ($this->conf['p3p']['compactPolicy'] != '') {
                    $p3pHeader .= " CP=\"" . $this->conf['p3p']['compactPolicy']."\"";
                }
                if ($p3pHeader != '') {
                    header("P3P: $p3pHeader");
                }
            }
            //  prepare headers during setup, can be overridden later
            header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
            header('Content-Type: text/html; charset=' . $GLOBALS['_SGL']['CHARSET']);
            header('X-Powered-By: Seagull http://seagull.phpkitchen.com');
        }
    }
}

class SGL_Process_SetSystemAlert  extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->processRequest->process($input, $output);

        if (isset($this->conf['site']['alert'])) {
            SGL_Session::set('message', $this->conf['site']['alert']);
            SGL_Session::set('messageType', SGL_MESSAGE_INFO);
        }
    }
}

/**
 * Initiates session check.
 *
 *      o global set of perm constants loaded from file cache
 *      o current class's config file is checked to see if authentication is required
 *      o if yes, session is checked for validity and expiration
 *      o if it's valid and not expired, the session is deemed valid.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Process_AuthenticateRequest extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // check for timeout
        $session = $input->get('session');
        $timeout = $session->isTimedOut();

        //  if page requires authentication and we're not debugging
        $mgr = $input->get('manager');
        $mgrName = SGL_Inflector::caseFix(get_class($mgr));
        if ($session->getRoleId() > SGL_GUEST
                && isset( $this->conf[$mgrName]['requiresAuth'])
                && $this->conf[$mgrName]['requiresAuth'] == true
                && $this->conf['debug']['authorisationEnabled'])
        {
            //  check that session is not invalid or timed out
            if (!$session->isValid() || $timeout) {

                //  prepare referer info for redirect after login
                $url = $input->getCurrentUrl();
                $redir = $url->toString();
                $loginPage = array( 'moduleName'    => 'user',
                                    'managerName'   => 'login',
                                    'redir'         => urlencode($redir));
                if (!$session->isValid()) {
                    SGL::raiseMsg('authorization required');
                    SGL_HTTP::redirect($loginPage);
                } else {
                    $session->destroy();
                    SGL::raiseMsg('session timeout');
                    SGL_HTTP::redirect($loginPage);
                }
            }
        } else {
            //  no authentication required
        }

        $this->processRequest->process($input, $output);
    }
}

/**
 * Loads global set of application perms from filesystem cache.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Process_SetupPerms extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $cache = & SGL_Cache::singleton();
        if ($serialized = $cache->get('all_users', 'perms')) {
            $aPerms = unserialize($serialized);
            SGL::logMessage('perms from cache', PEAR_LOG_DEBUG);
        } else {
            require_once SGL_MOD_DIR . '/user/classes/DA_User.php';
            $da = & DA_User::singleton();
            $aPerms = $da->getPermsByModuleId();
            $serialized = serialize($aPerms);
            $cache->save($serialized, 'all_users', 'perms');
            SGL::logMessage('perms from db', PEAR_LOG_DEBUG);
        }
        if (is_array($aPerms) && count($aPerms)) {
            foreach ($aPerms as $k => $v) {
                define('SGL_PERMS_' . strtoupper($v), $k);
            }
        } else {
            SGL::raiseError('there was a problem initialising perms', SGL_ERROR_NODATA);
        }

        $this->processRequest->process($input, $output);
    }
}

/**
 * Detects if language flag exists in $_GET, loads relevant
 * language translation file.
 *
 * @access  private
 * @author  Demian Turner <demian@phpkitchen.com>
 * @author  Alexander J. Tarachanowicz II <ajt@localhype.net>
 * @package SGL
 */
class SGL_Process_SetupLangSupport extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        require_once SGL_CORE_DIR .'/Translation.php';

        $req = $input->getRequest();
        $lang = $req->get('lang');

        require_once SGL_DAT_DIR . '/ary.languages.php';
        $aLanguages = $GLOBALS['_SGL']['LANGUAGE'];

        //  if lang var passed in request
        if (isset($lang) && array_key_exists($lang, $aLanguages)) {
            $_SESSION['aPrefs']['language'] = $lang;
        } else {
            $lang = @$_SESSION['aPrefs']['language'];
        }
        //  resolve current language from GET or session, assign to $language
        $language = @$aLanguages[$lang][1];
        if (empty($language)) {
            $language = 'english-iso-8859-15';
            $_SESSION['aPrefs']['language'] = 'en-iso-8859-15';
        }
        //  fetch default translation
        $langID = str_replace('-', '_', $lang);
        $defaultWords = SGL_Translation::getTranslations('default', $langID);

        //  fetch module translations
        $moduleName = ($req->get('moduleName'))
            ? $req->get('moduleName')
            : $this->conf['site']['defaultManager'];

        if ($moduleName != 'default') {
            $words = SGL_Translation::getTranslations($moduleName, $langID);
        }

        //  if current module is not the default module
        if (isset($words)) {
            $GLOBALS['_SGL']['TRANSLATION'] = array_merge($defaultWords, $words);

            //  else just assign default lang array to globals
        } else {
            $GLOBALS['_SGL']['TRANSLATION'] = &$defaultWords;
        }
        //  extract charset from current language string
        $aTmp = split('-', $language);
        array_shift($aTmp);
        $GLOBALS['_SGL']['CHARSET'] = join('-', $aTmp);

        $this->processRequest->process($input, $output);
    }
}

/**
 * Starts the session.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Process_CreateSession extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $input->set('session', new SGL_Session());
        $this->processRequest->process($input, $output);
    }
}

/**
 * Resolves request params into Manager model object.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Process_ResolveManager extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        require_once SGL_MOD_DIR . '/default/classes/DA_Default.php';
        $da = & DA_Default::singleton();
        $req = $input->getRequest();
        $moduleName = $req->get('moduleName');
        $managerName = $req->get('managerName');
        $getDefaultMgr = false;
        $homePageRequest = false;

        if (empty($moduleName) || empty($managerName)) {

            SGL::logMessage('Module and manager names could not be determined from request');
            $getDefaultMgr = true;
            $homePageRequest = true;

        } else {
            if (!$da->moduleIsRegistered($moduleName)) {
                SGL::logMessage('module "'.$moduleName.'"does not appear to be registered');
                $getDefaultMgr = true;
            } else {
                //  load module's config if not present
                $ok = $this->ensureModuleConfigLoaded($moduleName);

                if (PEAR::isError($ok)) {
                    SGL::raiseError('could not locate module\'s config file',
                        SGL_ERROR_NOFILE);
                }

                //  get manager name if $managerName not correct attempt to load default
                //  manager w/$moduleName
                $mgrPath = SGL_MOD_DIR . '/' . $moduleName . '/classes/';
                $retMgrName = $this->getManagerName($managerName, $mgrPath);
                $managerName = ($retMgrName)
                    ? $retMgrName
                    : $this->getManagerName($moduleName, $mgrPath);
                if (!empty($managerName)) {

                    //  build path to manager class
                    $classPath = $mgrPath . $managerName . '.php';
                    if (@is_file($classPath)) {
                        require_once $classPath;

                        //  if class exists, instantiate it
                        if (@class_exists($managerName)) {
                            $input->moduleName = $moduleName;
                            $input->set('manager', new $managerName);
                        } else {
                            SGL::logMessage("Class $managerName does not exist");
                            $getDefaultMgr = true;
                        }
                    } else {
                        SGL::logMessage("Could not find file $classPath");
                        $getDefaultMgr = true;
                    }
                } else {
                    SGL::logMessage('Manager name could not be determined from '.
                                    'SGL_Process_ResolveManager::getManagerName');
                    $getDefaultMgr = true;
                }
            }
        }
        if ($getDefaultMgr) {
            $ok = $this->getDefaultManager($input);
            if (!$homePageRequest || !$ok) {
                SGL::raiseError("The specified manager could not be found, default loaded");
            }
        }
        $this->processRequest->process($input, $output);
    }

    /**
     * Ensures the module's config file was loaded.
     *
     * This is required when the homepage is set to custom mod/mgr/params,
     * and the module config file loaded while initialising the request is
     * not the file required for the custom invocation.
     *
     * @param string $moduleName
     * @return mixed    true on success, PEAR_Error on failure
     */
    function ensureModuleConfigLoaded($moduleName)
    {
        if (!defined('SGL_MODULE_CONFIG_LOADED')
                || $this->conf['localConfig']['moduleName'] != $moduleName) {
            $path = SGL_MOD_DIR . '/' . $moduleName . '/conf.ini';
            $modConfigPath = realpath($path);

            if ($modConfigPath) {
                $aModuleConfig = $this->c->load($modConfigPath);

                if (PEAR::isError($aModuleConfig)) {
                    $ret = $aModuleConfig;
                } else {
                    $this->c->merge($aModuleConfig);

                    //  remove first failed conf loading error in
                    //  SGL_UrlParser_SefStrategy::parseQueryString()
                    SGL_Error::shift();

                    //  reset conf keys
                    unset($this->conf);
                    $this->conf = $this->c->getAll();
                    $ret = true;
                }
            } else {
                $ret = SGL::raiseError("Config file could not be found at '$path'",
                    SGL_ERROR_NOFILE);
            }
        } else {
            $ret = true;
        }
        return $ret;
    }

    /**
     * Loads the default manager per config settings or returns false on failure.
     *
     * @param SGL_Registry $input
     * @return boolean
     */
    function getDefaultManager(&$input)
    {
        $defaultModule = $this->conf['site']['defaultModule'];
        $defaultMgr = $this->conf['site']['defaultManager'];

        //  load module's config if not present
        $ok = $this->ensureModuleConfigLoaded($defaultModule);
        if (PEAR::isError($ok)) {
            SGL::raiseError('could not locate module\'s config file',
                SGL_ERROR_NOFILE);
            return false;
        }

        $mgrName = SGL_Inflector::caseFix(
            SGL_Inflector::getManagerNameFromSimplifiedName($defaultMgr));
        $path = SGL_MOD_DIR .'/'.$defaultModule.'/classes/'.$mgrName.'.php';
        if (!is_file($path)) {
            SGL::raiseError('could not locate default manager, '.$path,
                SGL_ERROR_NOFILE);
            return false;
        }
        require_once $path;
        if (!class_exists($mgrName)) {
            SGL::raiseError('invalid class name for default manager',
                SGL_ERROR_NOCLASS);
            return false;
        }
        $mgr = new $mgrName();
        $input->moduleName = $defaultModule;
        $input->set('manager', $mgr);
        $req = $input->getRequest();
        $req->set('moduleName', $defaultModule);
        $req->set('managerName', $defaultMgr);

        if (!empty($this->conf['site']['defaultParams'])) {
            $aParams = SGL_Url::querystringArrayToHash(
                explode('/', $this->conf['site']['defaultParams']));
            $req->add($aParams);
        }
        $input->setRequest($req); // this ought to take care of itself
        return true;
    }

    /**
     * Returns classname suggested by URL param.
     *
     * @access  private
     * @param   string  $managerName    name of manager class
     * @param   string  $path           path to manager class
     * @return  mixed   either found class name or PEAR error
     */
    function getManagerName($managerName, $path)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aMatches = array();
        $aConfValues = array_keys($this->conf);
        $aConfValuesLowerCase = array_map('strtolower', $aConfValues);

        //  if Mgr suffix has been left out, append it
        $managerName = SGL_Inflector::getManagerNameFromSimplifiedName($managerName);

        //  test for full case sensitive classname in config array
        $isFound = array_search($managerName, $aConfValues);
        if ($isFound !== false) {
            $aMatches['caseSensitiveMgrName'] = $aConfValues[$isFound];
        }
        unset($isFound);

        //  test for full case insensitive classname in config array
        $isFound = array_search(strtolower($managerName), $aConfValuesLowerCase);
        if ($isFound !== false) {
            $aMatches['caseInSensitiveMgrName'] = $aConfValues[$isFound];
        }

        foreach ($aMatches as $match) {
            if (!@is_file($path . $match . '.php')) {
                continue;
            } else {
                return $match;
            }
        }
        return false;
    }
}

class SGL_Process_StripMagicQuotes extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $req = $input->getRequest();
        SGL_String::dispelMagicQuotes($req->aProps);
        $input->setRequest($req);

        $this->processRequest->process($input, $output);
    }
}

/**
 * Set client OS constant based on user agent.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Process_DiscoverClientOs extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $ua = '';
        }

        if (!empty($ua) and !defined('SGL_CLIENT_OS')) {
            if (strstr($ua, 'Win')) {
                define('SGL_CLIENT_OS', 'Win');
            } elseif (strstr($ua, 'Mac')) {
                define('SGL_CLIENT_OS', 'Mac');
            } elseif (strstr($ua, 'Linux')) {
                define('SGL_CLIENT_OS', 'Linux');
            } elseif (strstr($ua, 'Unix')) {
                define('SGL_CLIENT_OS', 'Unix');
            } elseif (strstr($ua, 'OS/2')) {
                define('SGL_CLIENT_OS', 'OS/2');
            } else {
                define('SGL_CLIENT_OS', 'Other');
            }
        } else {
            if (!defined('SGL_CLIENT_OS')) {
                define('SGL_CLIENT_OS', 'None');
            }
        }
        $this->processRequest->process($input, $output);
    }
}

/**
 * Assign output vars for template.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Process_BuildOutputData extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->processRequest->process($input, $output);

        //  setup login stats
        if (SGL_Session::getRoleId() > SGL_GUEST) {
            $output->loggedOnUser = $_SESSION['username'];
            $output->loggedOnUserID = SGL_Session::getUid();
            $output->loggedOnSince = strftime("%H:%M:%S", $_SESSION['startTime']);
            $output->loggedOnDate = strftime("%B %d", $_SESSION['startTime']);
            $output->remoteIp = $_SERVER['REMOTE_ADDR'];
            $output->isMember = true;
        }
        $output->currUrl          = $_SERVER['PHP_SELF'];
        $output->currLang         = SGL::getCurrentLang();
        $output->theme            = $_SESSION['aPrefs']['theme'];
        $output->charset          = $GLOBALS['_SGL']['CHARSET'];
        $output->webRoot          = SGL_BASE_URL;
        $output->imagesDir        = SGL_BASE_URL . '/themes/' . $output->theme . '/images';
        $output->versionAPI       = SGL_SEAGULL_VERSION;
        $output->sessID           = SID;
        $output->scriptOpen       = "\n<script type=\"text/javascript\"> <!--\n";
        $output->scriptClose      = "\n//--> </script>\n";
        $output->conf = $this->conf;

        if (isset($output->submitted) && $output->submitted) {
            $output->addOnLoadEvent("formErrorCheck()");
        }
    }
}

/**
 * Sets up wysiwyg params.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Process_SetupWysiwyg extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->processRequest->process($input, $output);

        // set the default WYSIWYG editor
        if (isset($output->wysiwyg) && $output->wysiwyg == true && !SGL::runningFromCLI()) {

            // you can preset this var in your code
            if (!isset($output->wysiwygEditor)) {
                $output->wysiwygEditor = isset($this->conf['site']['wysiwygEditor'])
                    ? $this->conf['site']['wysiwygEditor']
                    : 'fck';
            }

            switch ($output->wysiwygEditor) {

            case 'fck':
                $output->wysiwyg_fck = true;
                $output->addOnLoadEvent('fck_init()');
                break;
            case 'xinha':
                $output->wysiwyg_xinha = true;
                $output->addOnLoadEvent('xinha_init()');
                break;
            case 'htmlarea':
                $output->wysiwyg_htmlarea = true;
                $output->addOnLoadEvent('HTMLArea.init()');
                break;
            case 'tinyfck':
                $output->wysiwyg_tinyfck = true;
                // note: tinymce doesn't need an onLoad event to initialise
                break;
            }
        }
        //  get all html onLoad events
        $output->onLoad = $output->getAllOnLoadEvents();
    }
}

/**
 * Collects performance data.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Process_GetPerformanceInfo extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->processRequest->process($input, $output);

        //  get performance info
        if (!empty($_SESSION['aPrefs']['showExecutionTimes'])
                && $_SESSION['aPrefs']['showExecutionTimes'] == 1) {

            //  prepare query count
            $output->queryCount = $GLOBALS['_SGL']['QUERY_COUNT'];

            //  and execution time
            $output->executionTime = getSystemTime() - @SGL_START_TIME;
        }
        //  send memory consumption to output
        if (SGL_PROFILING_ENABLED && function_exists('memory_get_usage')) {
            $output->memoryUsage = number_format(memory_get_usage());
        }
    }
}

/**
 * Builds navigation menus.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Process_SetupNavigation extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->processRequest->process($input, $output);

        if ($this->conf['navigation']['enabled']) {

            //  prepare navigation driver
            $navDriver    = $this->conf['navigation']['driver'];
            $navDrvFile   = SGL_MOD_DIR . '/navigation/classes/' . $navDriver . '.php';
            if (is_file($navDrvFile)) {
                require_once $navDrvFile;
            } else {
                SGL::raiseError('specified navigation driver does not exist',
                    SGL_ERROR_NOFILE);
            }
            if (!class_exists($navDriver)) {
                SGL::raiseError('problem with navigation driver object', SGL_ERROR_NOCLASS);
            }
            $nav = & new $navDriver($output);

            //  render navigation menu
            $navRenderer = $this->conf['navigation']['renderer'];
            $aRes        = $nav->render($navRenderer);
            if (!PEAR::isError($aRes)) {
                list($sectionId, $html)  = $aRes;
                $output->sectionId  = $sectionId;
                $output->navigation = $html;
                $output->currentSectionName = $nav->getCurrentSectionName();
            }
        }
    }
}

/**
 * Setup which Graphical User Interface to use.
 *
 * @package SGL
 * @author
 */
class SGL_Process_SetupGui extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->processRequest->process($input, $output);

        if (!SGL::runningFromCLI()) {
            $mgrName = SGL_Inflector::caseFix(get_class($output->manager));
            $userRid = SGL_Session::getRoleId();
            $adminGuiAllowed = $adminGuiRequested = false;

            //  setup which GUI to load depending on user and manager
            $output->adminGuiAllowed = false;

            // first check if userRID allows to switch to adminGUI
            if ($userRid == SGL_ADMIN) {
                $adminGuiAllowed = true;
            }

            // then check if manager requires to switch to adminGUI
            if (isset($this->conf[$mgrName]['adminGuiAllowed'])
                && $this->conf[$mgrName]['adminGuiAllowed']) {
                $adminGuiRequested = true;

                // exception
                // 1. allows to preview articles with default theme
                if ($mgrName == 'ArticleMgr' && $output->action == 'view') {
                    $adminGuiRequested = false;
                }
            }

            if ($adminGuiAllowed && $adminGuiRequested) {

                // if adminGUI is allowed then change theme TODO : put the logical stuff in another class/method
                $output->adminGuiAllowed = true;
                $output->theme = $this->conf['site']['adminGuiTheme'];
                $output->masterTemplate = 'admin_master.html';
                $output->template = 'admin_' . $output->template;
            }
        }
    }
}

/**
 * Initialises block loading.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Process_SetupBlocks extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->processRequest->process($input, $output);

        //  load blocks
        if ($this->conf['site']['blocksEnabled']
                && $this->conf['navigation']['enabled']
                && !SGL::runningFromCli()) {
            require_once SGL_CORE_DIR . '/BlockLoader.php';
            $output->sectionId = empty($output->sectionId)
                ? 0
                : $output->sectionId;
            $blockLoader = & new SGL_BlockLoader($output->sectionId);
            $aBlocks = $blockLoader->render($output);
            foreach ($aBlocks as $key => $value) {
                $blocksName = 'blocks'.$key;
                $output->$blocksName = $value;
            }
        }
    }
}

class SGL_Process_BuildView extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->processRequest->process($input, $output);

        //  build view
        $templateEngine = isset($output->templateEngine) ? $output->templateEngine : null;
        $view = new SGL_HtmlSimpleView($output, $templateEngine);
        $output->data = $view->render();
    }
}

/**
 * A void object.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Void extends SGL_ProcessRequest
{
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        //  do nothing
    }
}
?>
