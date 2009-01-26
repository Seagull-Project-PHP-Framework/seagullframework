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
// | Process.php                                                                 |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+

/**
 * Basic app process tasks: enables profiling and output buffering.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_Init extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        if (SGL_PROFILING_ENABLED && function_exists('apd_set_pprof_trace')) {
            apd_set_pprof_trace();
        }
        //  start output buffering
        if (SGL_Config::get('site.outputBuffering')) {
            ob_start();
        }

        $this->processRequest->process($input, $output);
    }
}

/**
 * @package Task
 */
class SGL_Task_SetupORM extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $oTask = new SGL_Task_InitialiseDbDataObject();
        $ok = $oTask->run();

        $this->processRequest->process($input, $output);
    }
}

/**
 * Block blacklisted users by IP.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_DetectBlackListing extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (SGL_Config::get('site.banIpEnabled')) {

            if (SGL_Config::get('site.allowList')) {
                $allowList = explode( ' ', SGL_Config::get('site.allowList'));
                if (!in_array($_SERVER['REMOTE_ADDR'], $allowList)) {
                    $msg = SGL_String::translate('You have been banned');
                    SGL::raiseError($msg, SGL_ERROR_BANNEDUSER, PEAR_ERROR_DIE);
                }
            }

            if (SGL_Config::get('site.denyList')) {
                $denyList = explode( ' ', SGL_Config::get('site.denyList'));
                if (in_array($_SERVER['REMOTE_ADDR'], $denyList)) {
                    $msg = SGL_String::translate('You have been banned');
                    SGL::raiseError($msg, SGL_ERROR_BANNEDUSER, PEAR_ERROR_DIE);
                }
            }
        }

        $this->processRequest->process($input, $output);
    }
}

class SGL_Task_MaintenanceModeIntercept extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        // check for maintenance mode "on"
        if (SGL_Config::get('site.maintenanceMode')) {
            // allow admin to access and to connect if provided a key
            $rid = SGL_Session::getRoleId();
            $adminMode = SGL_Session::get('adminMode');
            if ($rid != SGL_ADMIN && !$adminMode && !SGL::runningFromCLI()) {
                $req = $input->getRequest();
                // show mtnce page for browser requests
                if ($req->getType() == SGL_REQUEST_BROWSER) {
                    SGL::displayMaintenancePage($output);
                }
            }
        }

        $this->processRequest->process($input, $output);
    }
}

/**
 * Set an admin mode to give priviledged session.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_DetectAdminMode extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  set adminMode session if allowed
        $req = SGL_Request::singleton();
        $adminKey = $req->get('adminKey');
        if (SGL_Config::get('site.adminKey') && $adminKey == SGL_Config::get('site.adminKey')) {
            SGL_Session::set('adminMode', true);
        }

        $this->processRequest->process($input, $output);
    }
}

/**
 * Detects if session debug is allowed.
 *
 * @package Task
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 *
 * @todo think something better than checking for action to avoid
 *       saving config to file, when value was changed
 */
class SGL_Task_DetectSessionDebug extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $adminMode = SGL_Session::get('adminMode');
        $req       = $input->getRequest();
        //  if not in admin mode, but session debug was allowed
        if (!$adminMode && SGL_Config::get('debug.sessionDebugAllowed')
                && $req->get('action') != 'rebuildSeagull'
                && $req->getManagerName() != 'config') {
            //  flag it as not allowed
            SGL_Config::set('debug.sessionDebugAllowed', false);
        }

        $this->processRequest->process($input, $output);
    }
}

/**
 * Sets the current locale.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_SetupLocale extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $locale = $_SESSION['aPrefs']['locale'];
        $timezone = $_SESSION['aPrefs']['timezone'];
        $language = substr($locale, 0,2);

        if (!SGL_Config::get('site.extendedLocale')) {
            //  The default locale category is LC_ALL, but this will cause probs for
            //  european users who get their decimal points (.) changed to commas (,)
            //  and php numeric calculations will break.  The solution for these users
            //  is to select the LC_TIME category.  For a global effect change this in
            //  Config.
            if (setlocale(SGL_String::pseudoConstantToInt(SGL_Config::get('site.localeCategory')), $locale) == false) {
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
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_BuildHeaders extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $this->processRequest->process($input, $output);

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  don't send headers according to config
        $currentMgr = SGL_Inflector::caseFix(get_class($output->manager));
        $c = &SGL_Config::singleton(); $conf = $c->getAll();
        if (!isset($conf[$currentMgr]['setHeaders'])
                || $conf[$currentMgr]['setHeaders'] == true) {

            //  set compression as specified in init, can only be done here :-)
            @ini_set('zlib.output_compression', (int)SGL_Config::get('site.compression'));

            //  build P3P headers
            if (SGL_Config::get('p3p.policies')) {
                $p3pHeader = '';
                if (SGL_Config::get('p3p.policyLocation')) {
                    $p3pHeader .= " policyref=\"" . SGL_Config::get('p3p.policyLocation')."\"";
                }
                if (SGL_Config::get('p3p.compactPolicy')) {
                    $p3pHeader .= " CP=\"" . SGL_Config::get('p3p.compactPolicy')."\"";
                }
                if ($p3pHeader != '') {
                    $output->addHeader("P3P: $p3pHeader");
                }
            }
            //  prepare headers during setup, can be overridden later
            if (!headers_sent()) {
                header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
                header('Content-Type: text/html; charset=' . $GLOBALS['_SGL']['CHARSET']);
                header('X-Powered-By: Seagull http://seagullproject.org');
                foreach ($output->getHeaders() as $header) {
                    header($header);
                }
            }
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
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_AuthenticateRequest extends SGL_DecorateProcess
{
    /**
     * Returns 'remember me' cookie data.
     *
     * @return mixed
     */
    function getRememberMeCookieData()
    {
        //  no 'remember me' cookie found
        if (!isset($_COOKIE['SGL_REMEMBER_ME'])) {
            return false;
        }
        $cookie = $_COOKIE['SGL_REMEMBER_ME'];
        list($username, $cookieValue) = @unserialize($cookie);
        //  wrong cookie value was saved
        if (!$username || !$cookieValue) {
            return false;
        }
        //  get UID by cookie value
        require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
        $da  = &UserDAO::singleton();
        $uid = $da->getUserIdByCookie($username, $cookieValue);
        if ($uid) {
            $ret = array('uid' => $uid, 'cookieVal' => $cookieValue);
        } else {
            $ret = false;
        }
        return $ret;
    }

    /**
     * Authenticate user.
     *
     * @access protected
     *
     * @param integer $uid
     * @param SGL_Registry $input
     *
     * @return void
     */
    function doLogin($uid, &$input)
    {
        // if we do login here, then $uid was recovered by cookie,
        // thus activating 'remember me' functionality
        $input->set('session', new SGL_Session($uid, $rememberMe = true));

        // record login if allowed
        if (!SGL::moduleIsEnabled('user2')) {
            require_once SGL_MOD_DIR . '/user/classes/observers/RecordLogin.php';
            if (RecordLogin::loginRecordingAllowed()) {
                $dbh = &SGL_DB::singleton();
                RecordLogin::insert($dbh);
            }
        }
    }

    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // check for timeout
        $session = $input->get('session');
        $timeout = !$session->updateIdle();

        //  store request in session
        $aRequestHistory = SGL_Session::get('aRequestHistory');
        if (empty($aRequestHistory)) {
            $aRequestHistory = array();
        }
        $req = $input->getRequest();
        array_unshift($aRequestHistory, $req->getAll());
        $aTruncated = array_slice($aRequestHistory, 0, 2);
        SGL_Session::set('aRequestHistory', $aTruncated);

        $mgr = $input->get('manager');
        $mgrName = SGL_Inflector::caseFix(get_class($mgr));

        //  test for anonymous session and rememberMe cookie
        if (($session->isAnonymous() || $timeout)
                && SGL_Config::get('cookie.rememberMeEnabled')
                && !SGL_Config::get('site.maintenanceMode')) {
            $aCookieData = $this->getRememberMeCookieData();
            if (!empty($aCookieData['uid'])) {
                $this->doLogin($aCookieData['uid'], $input);

                //  session data updated
                $session = $input->get('session');
                $timeout = !$session->updateIdle();
            }
        }
        //  if page requires authentication and we're not debugging
        if (   SGL_Config::get("$mgrName.requiresAuth")
            && SGL_Config::get('debug.authorisationEnabled')
            && !SGL::runningFromCLI())
        {
            //  check that session is valid or timed out
            if (!$session->isValid() || $timeout) {

                //  prepare referer info for redirect after login
                $url = $input->getCurrentUrl();
                if (!SGL_Config::get('translation.langInUrl')) {
                    $redir = $url->toString();
                } else {
                    // creates proper current link,
                    // we need to specify lang param explicitly to avoid URLs
                    // like "http://sgl/index.php/ru-utf-8/default"
                    $redir = $url->makeCurrentLink(
                        array('lang' => SGL::getCurrentLang()));
                }

                $loginPage = SGL_Config::get('site.loginTarget')
                    ? SGL_Config::getCommandTarget(SGL_Config::get('site.loginTarget'))
                    : array('moduleName'    => 'user',
                            'managerName'   => 'login');
                $loginPage['redir'] = base64_encode($redir);
                // we need to create proper URL for redirect,
                // which SGL_HTTP::redirect() can't do
                if (SGL_Config::get('translation.langInUrl')) {
                    $loginPage = $url->makeLink($loginPage);
                }
                if (!$session->isValid()) {
                    SGL::raiseMsg('authentication required');
                    SGL_HTTP::redirect($loginPage);
                } else {
                    $session->destroy();
                    SGL::raiseMsg('session timeout');
                    SGL_HTTP::redirect($loginPage);
                }
            }
        }

        $this->processRequest->process($input, $output);
    }
}

/**
 * Loads global set of application perms from filesystem cache.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_SetupPerms extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $cache = & SGL_Cache::singleton();
        if ($serialized = $cache->get('all_users', 'perms')) {
            $aPerms = unserialize($serialized);
            SGL::logMessage('perms from cache', PEAR_LOG_DEBUG);
        } else {
            require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
            $da = & UserDAO::singleton();
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
 * Resolve current language and put in current user preferences.
 * Load relevant language translation file.
 *
 * @package Task
 * @author Demian Turner <demian@phpkitchen.com>
 * @author Alexander J. Tarachanowicz II <ajt@localhype.net>
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Task_SetupLangSupport extends SGL_DecorateProcess
{
    /**
     * Main workflow.
     *
     * @access public
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // save language in settings
        $_SESSION['aPrefs']['language'] = $lang = $this->_resolveLanguage();

        $req = $input->getRequest();

        // modules to get translation for
        if (SGL_Config::get('translation.defaultLangBC')) {
            $moduleDefault = 'default';
        } else {
            $moduleDefault = SGL_Config::get('site.defaultModule');
        }

        $moduleCurrent = $req->get('moduleName')
            ? $req->get('moduleName')
            : $moduleDefault;

        // get translations
        $aWords = SGL_Translation::getTranslations($moduleDefault, $lang);
        if ($moduleCurrent != $moduleDefault) {
            $aWords = array_merge(
                $aWords,
                SGL_Translation::getTranslations($moduleCurrent, $lang)
            );
        }

        // populate SGL globals
        $GLOBALS['_SGL']['TRANSLATION'] = $aWords;
        // we can remove this one, left for BC
        $GLOBALS['_SGL']['CHARSET'] = SGL_Translation::getCharset();

        // continue chain execution
        $this->processRequest->process($input, $output);
    }

    /**
     * Resolve language from browser settings.
     *
     * @access public
     *
     * @return mixed  language or false on failure
     */
    function resolveLanguageFromBrowser()
    {
        $ret = false;
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $env = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $aLangs = preg_split(
                ';[\s,]+;',
                substr($env, 0, strpos($env . ';', ';')), -1,
                PREG_SPLIT_NO_EMPTY
            );
            foreach ($aLangs as $langCode) {
                $lang = $langCode . '-' . SGL_Translation::getFallbackCharset();
                if (SGL_Translation::isAllowedLanguage($lang)) {
                    $ret = $lang;
                    break;
                }
            }
        }
        return $ret;
    }

    /**
     * Resolve language from domain name.
     *
     * @access public
     *
     * @return mixed  language or false on failure
     */
    function resolveLanguageFromDomain()
    {
        $ret = false;
        if (isset($_SERVER['HTTP_HOST'])) {
            $langCode = array_pop(explode('.', $_SERVER['HTTP_HOST']));

            // if such language exists, then use it
            $lang = $langCode . '-' . SGL_Translation::getFallbackCharset();
            if (SGL_Translation::isAllowedLanguage($lang)) {
                $ret = $lang;
            }
        }
        return $ret;
    }

    /**
     * Resolve current language.
     *
     * @access private
     *
     * @return string
     */
    function _resolveLanguage()
    {
        $req = &SGL_Request::singleton();

        // resolve language from request
        $lang = $req->get('lang');

        $anonRequest = SGL_Session::isFirstAnonRequest();

        // 1. look for language in URL
        if (empty($lang) || !SGL_Translation::isAllowedLanguage($lang)) {
            // 2. look for language in settings
            if (!isset($_SESSION['aPrefs']['language'])
                    || !SGL_Translation::isAllowedLanguage($_SESSION['aPrefs']['language'])
                    || $anonRequest) {
                // 3. look for language in browser settings
                if (!SGL_Config::get('translation.languageAutoDiscover')
                        || !($lang = $this->resolveLanguageFromBrowser())) {
                    // 4. look for language in domain
                    if (!SGL_Config::get('translation.languageAutoDiscover')
                            || !($lang = $this->resolveLanguageFromDomain())) {
                        // 5. get default language
                        $lang = SGL_Translation::getFallbackLangID(SGL_LANG_ID_SGL);
                    }
                }
            // get language from settings
            } else {
                $lang = $_SESSION['aPrefs']['language'];
            }
        }
        return $lang;
    }
}

/**
 * Starts the session.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_CreateSession extends SGL_DecorateProcess
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
 * The module is resolved from Request parameter, if resolution fails, default
 * module is loaded.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_ResolveManager extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $req = $input->getRequest();
        $moduleName = $req->get('moduleName');
        $managerName = $req->get('managerName');
        $getDefaultMgr = false;

        if (empty($moduleName) || empty($managerName)) {

            SGL::logMessage('Module and manager names could not be determined from request');
            $getDefaultMgr = true;

        } else {
            if (!SGL::moduleIsEnabled($moduleName)) {
                SGL::raiseError('module "'.$moduleName.'" does not appear to be registered',
                    SGL_ERROR_RESOURCENOTFOUND);
                $getDefaultMgr = true;
            } else {
                $conf = $input->getConfig();

                //  get manager name, if $managerName not correct attempt to load default
                //  manager w/$moduleName
                $mgrPath = SGL_MOD_DIR . '/' . $moduleName . '/classes/';
                $retMgrName = $this->getManagerName($managerName, $mgrPath, $conf);
                if ($retMgrName === false) {
                    SGL::raiseError("Specified manager '$managerName' could not be found, ".
                        "defaults loaded, pls ensure full manager name is present in module's conf.ini",
                        SGL_ERROR_RESOURCENOTFOUND);
                }
                $managerName = ($retMgrName)
                    ? $retMgrName
                    : $this->getManagerName($moduleName, $mgrPath, $conf);
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
            $ok = $this->getConfiguredDefaultManager($input);
            if (!$ok) {
                SGL::raiseError("The default manager could not be found",
                    SGL_ERROR_RESOURCENOTFOUND);
                $this->getDefaultManager($input);
            }
        }
        $this->processRequest->process($input, $output);
    }

    /**
     * Loads the default manager per config settings or returns false on failure.
     *
     * @param SGL_Registry $input
     * @return boolean
     */
    function getConfiguredDefaultManager(&$input)
    {
        $defaultModule = SGL_Config::get('site.defaultModule');
        $defaultMgr = SGL_Config::get('site.defaultManager');

        //  load default module's config if not present
        $c = &SGL_Config::singleton();
        $conf = $c->ensureModuleConfigLoaded($defaultModule);

        if (PEAR::isError($conf)) {
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

        if (SGL_Config::get('site.defaultParams')) {
            $aParams = SGL_Url::querystringArrayToHash(
                explode('/', SGL_Config::get('site.defaultParams')));
            $req->add($aParams);
        }
        $input->setRequest($req); // this ought to take care of itself
        return true;
    }

    function getDefaultManager(&$input)
    {
        $defaultModule = 'default';
        $defaultMgr = 'default';
        $mgrName = SGL_Inflector::caseFix(
            SGL_Inflector::getManagerNameFromSimplifiedName($defaultMgr));
        $path = SGL_MOD_DIR .'/'.$defaultModule.'/classes/'.$mgrName.'.php';
        require_once $path;
        $mgr = new $mgrName();
        $input->moduleName = $defaultModule;
        $input->set('manager', $mgr);
        $req = $input->getRequest();
        $req->set('moduleName', $defaultModule);
        $req->set('managerName', $defaultMgr);
        $input->setRequest($req);
        return true;
    }

    /**
     * Returns classname suggested by URL param.
     *
     * @access  private
     * @param   string  $managerName    name of manager class
     * @param   string  $path           path to manager class
     * @param   array  $conf            array of config values
     * @return  mixed   either found class name or PEAR error
     */
    function getManagerName($managerName, $path, $conf)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aMatches = array();
        $aConfValues = array_keys($conf);
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

/**
 * @package Task
 */
class SGL_Task_StripMagicQuotes extends SGL_DecorateProcess
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
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_DiscoverClientOs extends SGL_DecorateProcess
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
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 * @author  Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Task_BuildOutputData extends SGL_DecorateProcess
{
    /**
     * Main routine.
     *
     * @access public
     *
     * @param SGL_Request $input
     * @param SGL_Output $output
     */
    function process(&$input, &$output)
    {
        $this->processRequest->process($input, $output);

        SGL::logMessage(null, PEAR_LOG_DEBUG);
        SGL_Task_BuildOutputData::addOutputData($output);
    }

    /**
     * Adds output vars to SGL_Output object.
     *
     * @access public
     *
     * @param SGL_Output $output
     */
    function addOutputData(&$output)
    {
        // setup login stats
        if (SGL_Session::getRoleId() > SGL_GUEST) {
            $output->loggedOnUser   = SGL_Session::getUsername();
            $output->loggedOnUserID = SGL_Session::getUid();
            $output->loggedOnSince  = strftime("%H:%M:%S", SGL_Session::get('startTime'));
            $output->loggedOnDate   = strftime("%B %d", SGL_Session::get('startTime'));
            $output->isMember       = true;
        }

        // request data
        if (!SGL::runningFromCLI()) {
            $output->remoteIp = $_SERVER['REMOTE_ADDR'];
            $output->currUrl  =
                    SGL_Config::get('site.inputUrlHandlers') == 'Horde_Routes'
                ? SGL_Task_BuildOutputData::getCurrentUrlFromRoutes()
                : $_SERVER['PHP_SELF'];
        }

        // lang data
        $output->currLang     = SGL::getCurrentLang();
        $output->charset      = SGL::getCurrentCharset();
        $output->currFullLang = $_SESSION['aPrefs']['language'];
        $output->langDir      = ($output->currLang == 'ar'
                || $output->currLang == 'he')
            ? 'rtl' : 'ltr';

        // setup theme
        $output->theme = isset($_SESSION['aPrefs']['theme'])
            ? $_SESSION['aPrefs']['theme']
            : 'default';
        // check if theme is affected by the current manager
        if (isset($output->manager)) {
            $output->managerName = SGL_Inflector::caseFix(get_class($output->manager));
            if (SGL_Config::get($output->managerName . '.theme')) {
                $output->theme = SGL_Config::get($output->managerName . '.theme');
            }
        }

        // Setup SGL data
        $c = &SGL_Config::singleton();
        $output->conf             = $c->getAll();
        $output->webRoot          = SGL_BASE_URL;
        $output->imagesDir        = SGL_BASE_URL . '/themes/' . $output->theme . '/images';
        $output->versionAPI       = SGL_SEAGULL_VERSION;
        $output->sessID           = SGL_Session::getId();
        $output->isMinimalInstall = SGL::isMinimalInstall();

        // Additional information
        $output->scriptOpen         = "\n<script type='text/javascript'>\n//<![CDATA[\n";
        $output->scriptClose        = "\n//]]>\n</script>\n";
        $output->showExecutionTimes = $_SESSION['aPrefs']['showExecutionTimes'];
    }

    /**
     * Get current URL in $_SERVER['PHP_SELF'] style.
     *
     * @return string
     */
    function getCurrentUrlFromRoutes()
    {
        $input   = &SGL_Registry::singleton();
        $url     = $input->getCurrentUrl();
        $currUrl = $url->toString();
        $baseUrl = $url->getBaseUrl($skipProto = false, $includeFc = false);
        return str_replace($baseUrl, '', $currUrl);
    }
}

/**
 * Sets up wysiwyg params.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_SetupWysiwyg extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $this->processRequest->process($input, $output);

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // set the default WYSIWYG editor
        if (isset($output->wysiwyg) && $output->wysiwyg == true && !SGL::runningFromCLI()) {

            // you can preset this var in your code
            if (!isset($output->wysiwygEditor)) {
                $output->wysiwygEditor = SGL_Config::get('site.wysiwygEditor')
                    ? SGL_Config::get('site.wysiwygEditor')
                    : 'fck';
            }
            switch ($output->wysiwygEditor) {

            case 'fck':
            case 'fckeditor':
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
    }
}

/**
 * Builds navigation menus.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_SetupNavigation extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $this->processRequest->process($input, $output);

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (SGL_Config::get('navigation.enabled')
            && !SGL::runningFromCli())
        {
            //  prepare navigation driver
            $navDriver = SGL_Config::get('navigation.driver');
            $navDrvFile = SGL_MOD_DIR . '/navigation/classes/' . $navDriver . '.php';
            if (is_file($navDrvFile)) {
                require_once $navDrvFile;
            } else {
                return SGL::raiseError("specified navigation driver, $navDrvFile, does not exist",
                    SGL_ERROR_NOFILE);
            }
            if (!class_exists($navDriver)) {
                return SGL::raiseError('problem with navigation driver object',
                    SGL_ERROR_NOCLASS);
            }
            $nav = & new $navDriver($output);

            //  render navigation menu
            $navRenderer = SGL_Config::get('navigation.renderer');
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
 * Setup which admin Graphical User Interface to use.
 *
 * @package Task
 */
class SGL_Task_SetupGui extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $this->processRequest->process($input, $output);

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!SGL::runningFromCLI()) {
            $mgrName = SGL_Inflector::caseFix(get_class($output->manager));
            $adminGuiAllowed = $adminGuiRequested = false;

            //  setup which GUI to load depending on user and manager
            $output->adminGuiAllowed = false;

            // first check if userRID allows to switch to adminGUI
            if (SGL_Session::hasAdminGui()) {
                $adminGuiAllowed = true;
            }

            $c = &SGL_Config::singleton();
            $conf = $c->getAll();
            if (!$c->get($mgrName)) {
                //  get current module
                $req = &SGL_Request::singleton();
                $moduleName = $req->getModuleName();

                //  load current module's config if not present
                $conf = $c->ensureModuleConfigLoaded($moduleName);

                if (PEAR::isError($conf)) {
                    SGL::raiseError('could not locate module\'s config file',
                        SGL_ERROR_NOFILE);
                }
            }
            // then check if manager requires to switch to adminGUI
            if (isset( $conf[$mgrName]['adminGuiAllowed'])
                    && $conf[$mgrName]['adminGuiAllowed']) {
                $adminGuiRequested = true;

                //  check for adminGUI override in action
                if (isset($output->overrideAdminGuiAllowed) && $output->overrideAdminGuiAllowed) {
                    $adminGuiRequested = false;
                }
            }
            if ($adminGuiAllowed && $adminGuiRequested) {

                // if adminGUI is allowed then change theme TODO : put the logical stuff in another class/method
                $output->adminGuiAllowed = true;
                $output->theme = $conf['site']['adminGuiTheme'];
                $output->masterTemplate = 'admin_master.html';
                $output->template = 'admin_' . $output->template;
                if (isset($output->submitted) && $output->submitted) {
                    $output->addOnLoadEvent("formErrorCheck()");
                }
            }
        }
    }
}

/**
 * Initialises block loading.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_SetupBlocks extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $this->processRequest->process($input, $output);

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  load blocks
        if (SGL_Config::get('site.blocksEnabled')
                && !SGL::runningFromCli()) {
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

/**
 * Builds data for debug block.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_BuildDebugBlock extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $this->processRequest->process($input, $output);

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (SGL_Config::get('debug.infoBlock')) {
            $output->debug_request = $output->request;
            $output->debug_session = $_SESSION;
            $output->debug_module = $output->moduleName;
            $output->debug_manager = isset($output->managerName)
                ? $output->managerName
                : '';
            $output->debug_action = $output->action;
            $output->debug_section = $output->sectionId;
            $output->debug_master_template = isset($output->masterTemplate)
                ? $output->masterTemplate
                : '';
            $output->debug_template = $output->template;
            $output->debug_theme = $output->theme;

        }
    }
}

/**
 * @package Task
 */
class SGL_Task_BuildView extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $this->processRequest->process($input, $output);

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get all html onLoad events and js files
        $output->onLoad = $output->getOnLoadEvents();
        $output->onUnload = $output->getOnUnloadEvents();
        $output->onReadyDom = $output->getOnReadyDomEvents();
        $output->javascriptSrc = $output->getJavascriptFiles();

        //  unset unnecessary objects
        unset($output->currentUrl);
        unset($output->manager->conf);
        unset($output->manager->dbh);

        //  build view
        $templateEngine = isset($output->templateEngine) ? $output->templateEngine : null;
        $view = new SGL_HtmlSimpleView($output, $templateEngine);
        $output->data = $view->render();
    }
}

/**
 * A void object.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Void extends SGL_ProcessRequest
{
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }
}
?>
