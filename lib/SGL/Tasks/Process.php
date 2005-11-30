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
// | Seagull 0.5                                                               |
// +---------------------------------------------------------------------------+
// | Tasks.php                                                                 |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: style.php,v 1.85 2005/06/22 00:40:44 demian Exp $

require_once dirname(__FILE__) . '/../../../modules/default/classes/ModuleMgr.php';
require_once dirname(__FILE__) . '/../AppController.php';

/**
 * Simple init task.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Process_Init extends SGL_DecorateProcess
{
    function process(&$input)
    {
        if (SGL_PROFILING_ENABLED && function_exists('apd_set_pprof_trace')) {
            apd_set_pprof_trace();
        }

        // load utility lib
        require_once SGL_LIB_DIR . '/SGL.php';

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

        //  start PHP error handler
        if ($this->conf['debug']['customErrorHandler']) {
	        require_once SGL_CORE_DIR . '/ErrorHandler.php';
	        $eh = & new SGL_ErrorHandler();
	        $eh->startHandler();
        }

        //  set PEAR error handler
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'pearErrorHandler');

        //  pre PHP 4.3.x workaround
        if (!defined('__CLASS__')) {
            define('__CLASS__', null);
        }
        //  clean start for logs
        error_log(' ');
        error_log('##########   New request: '.trim($_SERVER['PHP_SELF']).'   ##########');

        //  start output buffering
        if ($this->conf['site']['outputBuffering']) {
            ob_start();
        }
        //  starts session for page execution
        if (!is_writable(SGL_TMP_DIR)) {
            require_once 'System.php';

            //  pass path as array to avoid widows space parsing prob
            $success = System::mkDir(array(SGL_TMP_DIR));
            if (!$success) {
                SGL::raiseError('The tmp directory does not appear to be writable, please give the
                                webserver permissions to write to it', SGL_ERROR_FILEUNWRITABLE, PEAR_ERROR_DIE);
            }
        }
        $this->processRequest->process($input);
    }
}

/**
 * Block certain users.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Process_DetectBlackListing extends SGL_DecorateProcess
{
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if ($this->conf['site']['banIpEnabled']) {
            if (in_array($_SERVER['REMOTE_ADDR'], $GLOBALS['_SGL']['BANNED_IPS'])) {
                $msg = SGL_String::translate('You have been banned');
                SGL::raiseError($msg, SGL_ERROR_BANNEDUSER, PEAR_ERROR_DIE);
            }
        }

        $this->processRequest->process($input);
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
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  set debug session if allowed
        $req = $input->getRequest();
        $debug = $req->get('debug');
        if ($debug && SGL::debugAllowed()) {
            $debug = ($debug == 'on') ? 1 : 0;
            SGL_HTTP_Session::set('debug', $debug);
        }

        $this->processRequest->process($input);
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
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $locale = $_SESSION['aPrefs']['locale'];
        $timezone = $_SESSION['aPrefs']['timezone'];
        if (setlocale(LC_ALL, $locale) === false) {
            setlocale(LC_TIME, $locale);
        }
        if (@SGL_USR_OS != 'Win') {
            @putenv('TZ=' . $timezone);
        } else {
            @putenv('TZ=');
        }

        $this->processRequest->process($input);
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
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  don't send headers according to config
        $mgr = $input->get('manager');
        $currentMgr = SGL_Inflector::caseFix(get_class($mgr));
        if (!isset($this->conf[$currentMgr]['setHeaders'])
                || $this->conf[$currentMgr]['setHeaders'] == true) {

            //  set compression as specified in init, can only be done here :-)
            @ini_set('zlib.output_compression', (int)$this->conf['site']['compression']);

            //  build P3P headers
            if ($this->conf['p3p']['policies']) {
                $p3pHeader = '';
                if ($this->conf['p3p']['policy_location'] != '') {
                    $p3pHeader .= " policyref=\"" . $this->conf['p3p']['policy_location']."\"";
                }
                if ($this->conf['p3p']['compact_policy'] != '') {
                    $p3pHeader .= " CP=\"" . $this->conf['p3p']['compact_policy']."\"";
                }
                if ($p3pHeader != '') {
                    header("P3P: $p3pHeader");
                }
            }
            //  prepare headers during setup, can be overridden later
            header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
            header('Content-Type: text/html; charset=' . $GLOBALS['_SGL']['CHARSET']);
            header('X-Powered-By: Seagull ' . SGL_SEAGULL_VERSION);
        }

        $this->processRequest->process($input);
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
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  if page requires authentication and we're not debugging
        $mgr = $input->get('manager');

        $mgrName = SGL_Inflector::caseFix(get_class($mgr));
        if (isset( $this->conf[$mgrName]['requiresAuth'])
                && $this->conf[$mgrName]['requiresAuth'] == true
                && $this->conf['debug']['authenticationEnabled']) {

            //  prepare referer info for redirect after login
            $url = $input->getCurrentUrl();
            $redir = $url->toString();

            //  check that session is not invalid or timed out
            $loginPage = array( 'moduleName'    => 'user',
                                'managerName'   => 'login',
                                'redir'         => urlencode($redir));

            $session = $input->get('session');

            if (!$session->isValid()) {
                SGL::raiseMsg('authorization required');
                SGL_HTTP::redirect($loginPage);

            } elseif ($session->isTimedOut()) {
                $session->destroy();
                SGL::raiseMsg('session timeout');
                SGL_HTTP::redirect($loginPage);
            }
        } else {
            //  no authentication required
        }

        $this->processRequest->process($input);
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
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $cache = & SGL::cacheSingleton();
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

        $this->processRequest->process($input);
    }
}

/**
 * Detects if language flag exists in $_GET, loads relevant
 * language translation file.
 *
 * @access  private
 * @author  Demian Turner <demian@phpkitchen.com>
 * @author  Erlend Stromsvik <ehs@hvorfor.no>
 * @package SGL
 */
class SGL_Process_SetupLangSupport extends SGL_DecorateProcess
{
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $req = $input->getRequest();
        $lang = $req->get('lang');
        require_once SGL_DAT_DIR . '/ary.languages.php';
        $aLanguages = $GLOBALS['_SGL']['LANGUAGE'];

        //  if lang var passed in request
        if (isset($lang) && array_key_exists($lang, $aLanguages)) {
            include SGL_MOD_DIR . '/default/lang/' . $aLanguages[$lang][1] . '.php';
            $_SESSION['aPrefs']['language'] = $lang;
        } else {
            //  get it from session
            $currLang = $_SESSION['aPrefs']['language'];
            $globalLangFile = $aLanguages[$currLang][1] . '.php';

            //  if file exists, load global lang file
            if (is_readable(SGL_MOD_DIR . '/default/lang/' . $globalLangFile)) {
                include SGL_MOD_DIR . '/default/lang/' . $globalLangFile;
            } else {
                SGL::raiseError('could not locate the global language file', SGL_ERROR_NOFILE);
            }
        }
        //  resolve current language from GET or session, assign to $language
        $language = (isset($lang)) ? @$aLanguages[$lang][1] : $aLanguages[$currLang][1];
        if (empty($language)) {
            $language = 'english-iso-8859-15';
        }


        $path = SGL_MOD_DIR . '/' . $req->get('moduleName') . '/lang/';

        //  attempt to merge global language file with module's lang file
        if (is_readable($path . $language . '.php')) {
            include $path . $language . '.php';

            //  if current module is not the default module
            if (isset($words)) {
                $GLOBALS['_SGL']['TRANSLATION'] = array_merge($defaultWords, $words);

                //  else just assign default lang array to globals
            } else {
                $GLOBALS['_SGL']['TRANSLATION'] = &$defaultWords;
            }
        } else {
            SGL::raiseError('Could not open module\'s language file in ' .
                __CLASS__ . '::' . __FUNCTION__ .
                ', maybe it does not exist or the query parameter is incorrect',
                SGL_ERROR_NOFILE, PEAR_ERROR_DIE);
        }
        //  extract charset from current language string
        $aTmp = split('-', $language);
        array_shift($aTmp);
        $GLOBALS['_SGL']['CHARSET'] = join('-', $aTmp);

        $this->processRequest->process($input);
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
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $input->set('session', new SGL_HTTP_Session());

        $this->processRequest->process($input);
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
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get a reference to the request object
        $req = $input->getRequest();

        $moduleName = $req->get('moduleName');
        $managerName = $req->get('managerName');

        if (!empty($moduleName) && !empty($managerName)) {

            if (!ModuleMgr::moduleIsRegistered($moduleName)) {
                SGL::raiseError('module "'.$moduleName.'"does not appear to be registered', SGL_ERROR_INVALIDREQUEST);
                return $this->getDefaultManager($input);
            }

            //  get manager name if $managerName not correct attempt to load default manager w/$moduleName
            $mgrPath = SGL_MOD_DIR . '/' . $moduleName . '/classes/';
            $retMgrName = $this->getManagerName($managerName, $mgrPath);
            $managerName = ($retMgrName) ? $retMgrName : $this->getManagerName($moduleName, $mgrPath);

            //  if no manager name return error
            if (empty($managerName)) {
                SGL::raiseError('could not get class name', SGL_ERROR_INVALIDREQUEST);
                return $this->getDefaultManager($input);
            }
            //  build path to manager class
            $classPath = $mgrPath . $managerName . '.php';
            if (!@file_exists($classPath)) {
                SGL::raiseError('no mgr class file with this name exists', SGL_ERROR_NOFILE);
                return $this->getDefaultManager($input);
            }
            require_once $classPath;

            //  if class exists, instantiate it
            if (!@class_exists($managerName)) {
                SGL::raiseError('no mgr class with this name exists', SGL_ERROR_NOCLASS);
                return $this->getDefaultManager($input);
            }
            $input->moduleName = $moduleName;
            $input->set('manager', new $managerName);
        } else {
            return $this->getDefaultManager($input);
        }
        $this->processRequest->process($input);
    }

    /**
     * Returns default manager per config settings.
     *
     * @param SGL_Registry $input
     * @return void
     */
    function getDefaultManager(&$input)
    {
        $defaultModule = $this->conf['site']['defaultModule'];
        $defaultMgr = $this->conf['site']['defaultManager'];
        $mgrName = SGL_Inflector::getManagerNameFromSimplifiedName($defaultMgr);
        $path = SGL_MOD_DIR .'/'.$defaultModule.'/classes/'.$mgrName.'.php';
        if (!file_exists($path)) {
            SGL::raiseError('could not locate default manager', SGL_ERROR_NOFILE);
            return false;
        }
        require_once $path;
        if (!class_exists($mgrName)) {
            SGL::raiseError('invalid class name for default manager', SGL_ERROR_NOCLASS);
            return false;
        }
        $mgr = new $mgrName();
        $mgr->module = $defaultModule;
        $input->set('manager', $mgr);
        $req = $input->getRequest();
        $req->set('moduleName', $defaultModule);
        $req->set('managerName', $defaultMgr);
        $input->setRequest($req); // this should take care of itself

        $this->processRequest->process($input);
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
            if (!@file_exists($path . $match . '.php')) {
                continue;
            } else {
                return $match;
            }
        }
        return false;
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
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $ua = $_SERVER['HTTP_USER_AGENT'];
        if (!empty($ua) and !defined('SGL_USR_OS')) {
            if (strstr($ua, 'Win')) {
                define('SGL_USR_OS', 'Win');
            } elseif (strstr($ua, 'Mac')) {
                define('SGL_USR_OS', 'Mac');
            } elseif (strstr($ua, 'Linux')) {
                define('SGL_USR_OS', 'Linux');
            } elseif (strstr($ua, 'Unix')) {
                define('SGL_USR_OS', 'Unix');
            } elseif (strstr($ua, 'OS/2')) {
                define('SGL_USR_OS', 'OS/2');
            } else {
                define('SGL_USR_OS', 'Other');
            }
        } else {
			if (!defined('SGL_USR_OS')) {
            	define('SGL_USR_OS', 'None');
			}
        }
        $this->processRequest->process($input);
    }
}

/**
 * Core data processing routine.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_MainProcess extends SGL_ProcessRequest
{
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        $req = $input->getRequest();

        $mgr = $input->get('manager');
        $mgr->validate($req, $input);

        $output = & new SGL_Output();
        $input->aggregate($output);

        //  process data if valid
        if ($mgr->isValid()) {
            $mgr->process($input, $output);
        }

        $mgr->display($output);

        //  build view
        $templateEngine = ucfirst($conf['site']['templateEngine']);
        $rendererClass = 'SGL_Html'.$templateEngine.'RendererStrategy';
        $rendererFile = 'Html'.$templateEngine.'RendererStrategy.php';
        if (file_exists(SGL_LIB_DIR .'/SGL/'. $rendererFile)) {
        	require_once SGL_LIB_DIR .'/SGL/'. $rendererFile;
        } else {
        	PEAR::raiseError('Could not find renderer',
        		SGL_ERROR_NOFILE, PEAR_ERROR_DIE);
        }
        $view = new SGL_HtmlView($output, new $rendererClass());
        echo $view->render();
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
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  set isAdmin flag
        $input->data->isAdmin = (SGL_HTTP_Session::getUserType() == SGL_ADMIN)
            ? true : false;

        //  setup login stats
        if (SGL_HTTP_Session::getUserType() > SGL_GUEST) {
            $input->data->loggedOnUser = $_SESSION['username'];
            $input->data->loggedOnUserID = SGL_HTTP_Session::getUid();
            $input->data->loggedOnSince = strftime("%H:%M:%S", $_SESSION['startTime']);
            $input->data->loggedOnDate = strftime("%B %d", $_SESSION['startTime']);
            $input->data->remoteIp = $_SERVER['REMOTE_ADDR'];
            $input->data->isMember = true;
        }
        $input->data->currUrl          = $_SERVER['PHP_SELF'];
        $input->data->currLang         = SGL::getCurrentLang();
        $input->data->theme            = $_SESSION['aPrefs']['theme'];
        $input->data->charset          = $GLOBALS['_SGL']['CHARSET'];
        $input->data->webRoot          = SGL_BASE_URL;
        $input->data->imagesDir        = SGL_BASE_URL . '/themes/' . $input->data->theme . '/images';
        $input->data->versionAPI       = SGL_SEAGULL_VERSION;
        $input->data->sessID           = SID;
        $input->data->scriptOpen       = "\n<script type=\"text/javascript\"> <!--\n";
        $input->data->scriptClose      = "\n//--> </script>\n";
		$input->data->conf = $this->conf;

        $this->processRequest->process($input);
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
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

		// set the default WYSIWYG editor
		if (isset($input->data->wysiwyg) && $input->data->wysiwyg == true) {

            // you can preset this var in your code
			if (!isset($input->data->wysiwyg_editor)) {
				$input->data->wysiwyg_editor = isset($this->conf['site']['wysiwyg_editor'])
				    ? $this->conf['site']['wysiwyg_editor']
                    : 'xinha';
			}

			switch ($input->data->wysiwyg_editor) {

            case 'fck':
            	$input->data->wysiwyg_fck = true;
            	$input->data->addOnLoadEvent('fck_init()');
            	break;
            case 'xinha':
            	$input->data->wysiwyg_xinha = true;
            	$input->data->addOnLoadEvent('xinha_init()');
            	break;
            case 'htmlarea':
            	$input->data->wysiwyg_htmlarea = true;
            	$input->data->addOnLoadEvent('HTMLArea.init()');
			}
		}
        //  get all html onLoad events
        $input->data->onLoad = $input->data->getAllOnLoadEvents();

        $this->processRequest->process($input);
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
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get performance info
        if (!empty($_SESSION['aPrefs']['showExecutionTimes'])
                && $_SESSION['aPrefs']['showExecutionTimes'] == 1) {

            //  prepare query count
            $input->data->queryCount = $GLOBALS['_SGL']['QUERY_COUNT'];

            //  and execution time
            $input->data->executionTime = getSystemTime() - @$GLOBALS['_SGL']['START_TIME'];
        }

        //  send memory consumption to output
        if (SGL_PROFILING_ENABLED && function_exists('memory_get_usage')) {
            $input->data->memoryUsage = number_format(memory_get_usage());
        }

        $this->processRequest->process($input);
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
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  generate navigation from appropriate driver
        if ($this->conf['navigation']['enabled']) {
            $navClass = $this->conf['navigation']['driver'];
            $navDriver = $navClass . '.php';
            if (file_exists(SGL_MOD_DIR . '/navigation/classes/' . $navDriver)) {
                require_once SGL_MOD_DIR . '/navigation/classes/' . $navDriver;
            } else {
                SGL::raiseError('specified navigation driver does not exist', SGL_ERROR_NOFILE);
            }
            if (!class_exists($navClass)) {
                SGL::raiseError('problem with navigation object', SGL_ERROR_NOCLASS);
            }
            $nav = & new $navClass($input);
            $aRes = $nav->render();
            if (!PEAR::isError($aRes)) {
                list($sectionId, $html) = $aRes;
                $input->data->sectionId = $sectionId;
                $input->data->navigation = $html;
                $input->data->currentSectionName = $nav->getCurrentSectionName();
            }
        }

        $this->processRequest->process($input);
    }
}


/**
 * Initialises block loading.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Process_SetupBlocks extends SGL_ProcessRequest
{
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        //  load blocks
        if ($conf['site']['blocksEnabled'] && $conf['navigation']['enabled']) {
            require_once SGL_CORE_DIR . '/BlockLoader.php';
            $input->data->sectionId = empty($input->data->sectionId) ? 0 : $input->data->sectionId;
            $blockLoader = & new SGL_BlockLoader($input->data->sectionId);
            $aBlocks = $blockLoader->render($input->data);
            $input->data->blocksLeft =  (isset($aBlocks['left'])) ? $aBlocks['left'] : '';
            $input->data->blocksRight = (isset($aBlocks['right'])) ? $aBlocks['right'] : '';
        }
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
