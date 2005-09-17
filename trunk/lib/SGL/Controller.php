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
// | Controller.php                                                            |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Controller.php,v 1.49 2005/06/23 19:15:25 demian Exp $

if (SGL_PROFILING_ENABLED && function_exists('apd_set_pprof_trace')) {
    apd_set_pprof_trace();
}

require_once SGL_LIB_DIR . '/SGL.php';
require_once SGL_CORE_DIR . '/Manager.php';
require_once SGL_CORE_DIR . '/Output.php';
require_once SGL_CORE_DIR . '/String.php';
require_once 'HTML/Template/Flexy.php';

/**
 * Single generic controller class for all model objects.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.49 $
 * @since   PHP 4.1
 */
class SGL_Controller
{
    /**
     * Model object passed in from start file, corresponds
     * to one of the module's manager classes.
     *
     * @access  public
     * @var     object
     */
    var $page = null;

    var $conf = array();

    /**
     * Constructor.
     *
     * @access  public
     * @return  void
     */
    function SGL_Controller()
    {
        //  pre PHP 4.3.x workaround
        if (!defined('__CLASS__')) {
            define('__CLASS__', null);
        }
        //  clean start for logs
        error_log(' ');
        error_log('##########   New request: '.trim($_SERVER['PHP_SELF']).'   ##########');

        //  start session and output buffering
        $this->conf = & $GLOBALS['_SGL']['CONF'];
        if ($this->conf['site']['outputBuffering']) {
            ob_start();
        }
        //  starts session for page execution
        if (!is_writable(SGL_TMP_DIR)) {
            include_once 'System.php';

            //  pass path as array to avoid widows space parsing prob
            $success = System::mkDir(array(SGL_TMP_DIR));
            if (!$success) {
                SGL::raiseError('The tmp directory does not appear to be writable, please give the
                                webserver permissions to write to it', SGL_ERROR_FILEUNWRITABLE, PEAR_ERROR_DIE);
            }
        }
    }

    /**
     * Runs the application - main starting point for workflow.
     *
     *  Workflow as follows:
     *  o starts timer
     *  o session & language initialisation and access-control check
     *  o creates output object to which all output properties are added
     *  o initiates validation of request data
     *  o if request is valid, output is sent to process with inherited input
     *  o if not, output is sent to display
     *
     * @access  public
     * @return  void
     */
    function go()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get a reference to the request object
        $req = & SGL_HTTP_Request::singleton();

        //  determine enduser OS
        SGL_Util::getUserOs();
        
        //  load relevant manager class and instantiate
        $this->page = $this->_getModel();
        
        //  do general session & language initialisation and access-control check
        $this->init();

        //  setup input/output objects
        $input = & new SGL_Output();

        //  validate the information posted from the browser
        $this->page->validate($req, $input);

        //  make a copy of $input for process
        $output = $input;

        //  if input is valid, copy data members from input to output
        //  and send to be processed
        if ($this->page->validated) {
            $this->_copyObjectProperties($input, $output);

            //  process the input, do workflow and add properties to output object
            $this->page->process($input, $output);
            $this->_displayPage($output);
        } else {
            //  otherwise, input validation failed, so input object 
            //  contains page data for try-again
            $this->_displayPage($input);
        }
    }

    /**
     * Session & language initialisation and access-control check.
     *
     * @access  public
     * @param   object  $req    Request object
     * @return  void
     */
    function init()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        //  setup session
        $this->session = & new SGL_HTTP_Session();
        
        //  get a reference to the request object
        $req = & SGL_HTTP_Request::singleton();

        //  set gui language and locale
        $lang = $req->get('lang');
        $this->_setLanguage($lang);

        //  check if user is authenticated
        $this->_checkSession();

        //  set headers and locale
        $this->_setHeaders();
        $this->_setLocale();

        //  set debug session if allowed
        $debug = $req->get('debug');
        if ($debug && SGL::debugAllowed()) {
            $debug = ($debug == 'on') ? 1 : 0;
            SGL_HTTP_Session::set('debug', $debug);
        }
        // ban IP if blacklisted
        if ($this->conf['site']['banIpEnabled']) {
            if (in_array($_SERVER['REMOTE_ADDR'], $GLOBALS['_SGL']['BANNED_IPS'])) {
                $msg = SGL_String::translate('You have been banned');
                SGL::raiseError($msg, SGL_ERROR_BANNEDUSER, PEAR_ERROR_DIE);
            }
        }
    }

    /**
     * Returns page 'model' object.
     *
     * @access  private
     * @param   object  $req    the Request object
     * @return  void
     */
    function _getModel()
    {
        //  get a reference to the request object
        $req = & SGL_HTTP_Request::singleton();

        $moduleName = $req->get('moduleName');
        $managerName = $req->get('managerName');

        if (!empty($moduleName) && !empty($managerName)) {

            //  get manager name if $managerName not correct attempt to load default manager w/$moduleName
            $mgrPath = SGL_MOD_DIR . '/' . $moduleName . '/classes/';
            $retMgrName = $this->_getClassName($managerName, $mgrPath);
            $managerName = ($retMgrName) ? $retMgrName : $this->_getClassName($moduleName, $mgrPath);

            //  if no manager name return error
            if (empty($managerName)) {
                SGL::raiseError('could not get class name', SGL_ERROR_INVALIDREQUEST, PEAR_ERROR_DIE);
            }
            //  build path to manager class
            $classPath = $mgrPath . $managerName . '.php';
            if (!@file_exists($classPath)) {
                SGL::raiseError('no mgr class file with this name exists', SGL_ERROR_NOFILE, PEAR_ERROR_DIE);
            }
            require_once $classPath;

            //  if class exists, instantiate it
            if (!@class_exists($managerName)) {
                SGL::raiseError('no mgr class with this name exists', SGL_ERROR_NOCLASS, PEAR_ERROR_DIE);
            }
            return new $managerName;
        } else {
            SGL::raiseError('malformed request', SGL_ERROR_INVALIDREQUEST, PEAR_ERROR_DIE);
        }
    }

    /**
     * Returns classname suggested by URL param.
     *
     * @access  private
     * @param   string  $managerName    name of manager class
     * @param   string  $path           path to manager class
     * @return  mixed   either found class name or PEAR error
     */
    function _getClassName($managerName, $path)
    {
        $aMatches = array();
        $aConfValues = array_keys($this->conf);
        $aConfValuesLowerCase = array_map('strtolower', $aConfValues);

        //  if Mgr suffix has been left out, append it
        $managerName = SGL_Url::getManagerNameFromSimplifiedName($managerName);
        
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

    /**
     * Initiates session check.
     *
     *      o global set of perm constants loaded from file cache
     *      o current class's config file is checked to see if authentication is required
     *      o if yes, session is checked for validity and expiration
     *      o if it's valid and not expired, the session is deemed valid.
     *
     * @access  private
     * @return  boolean  return true if session if valid or else redirects
     */
    function _checkSession()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get superset of perms
        $this->_initPerms();

        //  if page requires authentication and we're not debugging
        $pageName = SGL::caseFix(get_class($this->page));
        if (isset( $this->conf[$pageName]['requiresAuth'])
                && $this->conf[$pageName]['requiresAuth'] == true
                && $this->conf['debug']['authenticationEnabled']) {

            //  prepare referer info for redirect after login
            $aParts = SGL_Url::getSignificantSegments($_SERVER['PHP_SELF']);
            $redir = $this->conf['site']['baseUrl'] .'/'.  implode('/', $aParts);

            //  check that session is not invalid or timed out
            $loginPage = array( 'moduleName'    => 'user', 
                                'managerName'   => 'login',
                                'redir'         => urlencode($redir));

            if (!$this->session->isValid()) {
                SGL::raiseMsg('authorization required');
                SGL_HTTP::redirect($loginPage);
            } elseif ($this->session->isTimedOut()) {
                $this->session->destroy();
                SGL::raiseMsg('session timeout');
                SGL_HTTP::redirect($loginPage);
            }
        } else {
            return true;
        }
    }

    /**
     * Loads global set of application perms from filesystem cache.
     *
     * @access  private
     * @return  void
     */
    function _initPerms()
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
        return true;
    }

    /**
     * Sets generic headers for page generation.
     *
     * Alternatively, headers can be suppressed if specified in module's config.
     *
     * @access  private
     * @return  void
     */
    function _setHeaders()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  don't send headers according to config
        $currentMgr = SGL::caseFix(get_class($this->page));
        if (isset( $this->conf[$currentMgr]['setHeaders'])
                && $this->conf[$currentMgr]['setHeaders'] == false) {
            return false;
        }
        //  set compression as specified in init, can only be done here :-)
        @ini_set('zlib.output_compression', (int)$this->conf['site']['compression']);

        //  build P3P headers
        if ($this->conf['p3p']['policies']) {
            $p3pHeader = '';
            if ($this->conf['p3p']['policy_location'] != '') {
                $p3p_header .= " policyref=\"" . $this->conf['p3p']['policy_location']."\"";
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
        header('X-Powered-By: Seagull ' . $GLOBALS['_SGL']['VERSION']);
        return true;
    }

    /**
     * Detects if language flag exists in $_GET, loads relevant 
     * language translation file.
     *
     * @access  private
     * @param   string  $lang   passed in from Request
     * @return  void
     * @author  Demian Turner <demian@phpkitchen.com>
     * @author  Erlend Stromsvik <ehs@hvorfor.no>
     */
    function _setLanguage($lang)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

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
        
        //  if $process->init(new SGL_HTTP_Request()) is called directly, as in testing,
        //  there will be no page object, so create a dummy object
        if (is_null($this->page)) {
            $this->page = new stdClass();
            $this->page->module = 'default';
        }
        
        $path = SGL_MOD_DIR . '/' . $this->page->module . '/lang/';

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
        return true;
    }

    /**
     * Sets current locale.
     *
     * @access  private     
     * @return boolean
     */
    function _setLocale()
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
        return true;
    }

    /**
     * Adds pages to a Wizard queue.
     *
     * @access  public
     * @param   string  $pageName   the name of the calling script
     * @param   array   $param      params to be appended to URL
     * @return  void
     */
    function addPage($pageName,$param=null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aPages = SGL_HTTP_Session::get('wiz_sequence');
        if (isset($pageName)) {

            //  pagename, isCurrent, param
            $aPages[] = array(  'pageName'  => $pageName, 
                                'current'   => false,
                                'param'     => $param);
        }
        SGL_HTTP_Session::set('wiz_sequence', $aPages);
        return true;
    }

    /**
     * Loads sequence of pages from Wizard queue and starts execution.
     *
     * @access  public
     * @return  void
     */
    function startWizard()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aPages = SGL_HTTP_Session::get('wiz_sequence');

        //  set first page to enabled
        $aPages[0]['current'] = true;
        SGL_HTTP_Session::set('wiz_sequence', $aPages);
        SGL_HTTP::redirect($aPages[0]['pageName'],$aPages[0]['param']);
        return true;
    }

    /**
     * Sends processed input to template engine.
     *
     *      o sends input to specified page/model display method
     *      o loads navigation driver and renders page navigation
     *      o renders page left/right blocks
     *      o adds generic info to output, required by templates
     *      o stops execution timer
     *      o sends final output data to template engine
     *
     * @access  private
     * @param   object  $input  processed data from process()
     * @return  void
     */
    function _displayPage(& $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  process manager's display method
        $this->page->display($output);

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
            $nav = & new $navClass;

            //  the following arguments are passed by reference and assigned values in
            //  the method
            $nav->render($sectionId, $html);
            $output->navigation = $html;
            $output->currentSectionName = $nav->getCurrentSectionName();
        }

        //  set isAdmin flag
        $output->isAdmin = (SGL_HTTP_Session::getUserType() == SGL_ADMIN) 
            ? true : false;

        //  setup login stats
        if (SGL_HTTP_Session::getUserType() > SGL_GUEST) {
            $output->loggedOnUser = $_SESSION['username'];
            $output->loggedOnUserID = SGL_HTTP_Session::getUid();
            $output->loggedOnSince = strftime("%H:%M:%S", $_SESSION['startTime']);
            $output->loggedOnDate = strftime("%B %d", $_SESSION['startTime']);
            $output->remoteIp = $_SERVER['REMOTE_ADDR'];
            $output->isMember = true;
        }

        //  load blocks
        if ($this->conf['site']['blocksEnabled'] && $this->conf['navigation']['enabled']) {
            require_once SGL_CORE_DIR . '/BlockLoader.php';
            $blockLoader = & new SGL_BlockLoader($sectionId);
            $aBlocks = $blockLoader->render($output);
            $output->blocksLeft =  (isset($aBlocks['left'])) ? $aBlocks['left'] : '';
            $output->blocksRight = (isset($aBlocks['right'])) ? $aBlocks['right'] : '';
        }

        //  send sitewide variables to page output
        $output->siteName     = $this->conf['site']['name'];
        $output->newWinHeight = $this->conf['popup']['winHeight'];
        $output->newWinWidth  = $this->conf['popup']['winWidth'];
        $output->showLogo     = $this->conf['site']['showLogo'];
        if ($this->conf['navigation']['enabled']) {
            $output->navStylesheet = $this->conf['navigation']['stylesheet'];
        }
        $output->currUrl          = $_SERVER['PHP_SELF'];
        $output->currLang         = SGL::getCurrentLang();
        $output->theme            = $theme = $_SESSION['aPrefs']['theme'];
        $output->charset          = $GLOBALS['_SGL']['CHARSET'];
        $output->webRoot          = SGL_BASE_URL;
        $output->imagesDir        = SGL_BASE_URL . '/themes/' . $theme . '/images';
        $output->versionAPI       = $GLOBALS['_SGL']['VERSION'];
        $output->sessID           = SID;
        $output->scriptOpen       = "\n<script type=\"text/javascript\"> <!--\n";
        $output->scriptClose      = "\n//--> </script>\n";
        $output->frontScriptName  = $this->conf['site']['frontScriptName'];

		// set the default WYSIWYG editor
		if (isset($output->wysiwyg) && $output->wysiwyg == true) {
			
            // you can preset this var in your code
			if (!isset($output->wysiwyg_editor)) {	
				$output->wysiwyg_editor = isset($this->conf['site']['wysiwyg_editor']) 
				    ? $this->conf['site']['wysiwyg_editor'] 
                    : 'xinha';
			}
			
			switch($output->wysiwyg_editor) {
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
			}
		}
        //  get all html onLoad events
        $output->onLoad = $output->getAllOnLoadEvents();
        
        //  get performance info
        if (!empty($_SESSION['aPrefs']['showExecutionTimes']) 
                && $_SESSION['aPrefs']['showExecutionTimes'] == 1) {
                    
            //  prepare query count
            $output->queryCount = $GLOBALS['_SGL']['QUERY_COUNT'];
            
            //  and execution time
            $output->executionTime = getSystemTime() - $GLOBALS['_SGL']['START_TIME'];
        }
        
        //  initialise template engine
        $options = &PEAR::getStaticProperty('HTML_Template_Flexy','options');
        $options = array(
            'templateDir'       =>  SGL_THEME_DIR . '/' . $theme . '/' . $this->page->module . PATH_SEPARATOR .
                                    SGL_THEME_DIR . '/default/' . $this->page->module . PATH_SEPARATOR .
                                    SGL_THEME_DIR . '/' . $theme . '/default'. PATH_SEPARATOR .
                                    SGL_THEME_DIR . '/default/default',
            'templateDirOrder'  => 'reverse',
            'multiSource'       => true,
            'compileDir'        => SGL_CACHE_DIR . '/tmpl/' . $theme,
            'forceCompile'      => SGL_FLEXY_FORCE_COMPILE,
            'debug'             => SGL_FLEXY_DEBUG,
            'allowPHP'          => SGL_FLEXY_ALLOW_PHP,
            'filters'           => SGL_FLEXY_FILTERS,
            'locale'            => SGL_FLEXY_LOCALE,
            'compiler'          => SGL_FLEXY_COMPILER,
            'valid_functions'   => SGL_FLEXY_VALID_FNS,
            'flexyIgnore'       => SGL_FLEXY_IGNORE,
            'globals'           => true,
            'globalfunctions'   => SGL_FLEXY_GLOBAL_FNS,
        );

        // Configure Flexy to use SGL ModuleOutput Plugin 
        // If an Output.php file exists in module's dir
        $customOutput = SGL_MOD_DIR . '/' . $this->page->module . '/classes/Output.php';
        if (is_readable($customOutput)) {
            $className = ucfirst($this->page->module) . 'Output';
            if (isset($options['plugins'])) {
                $options['plugins'] = $options['plugins'] + array($className => $customOutput);
            } else {
                $options['plugins'] = array($className => $customOutput);
            }
        }

        //  suppress notices in templates
        $GLOBALS['_SGL']['ERROR_OVERRIDE'] = true;
        $templ = & new HTML_Template_Flexy();
        $templ->compile($output->masterTemplate);

        //  if some Flexy 'elements' exist in the output object, send them as
        //  2nd arg to Flexy::bufferedOutputObject()
        $elements = (   isset($output->flexyElements) && 
                        is_array($output->flexyElements))
                ? $output->flexyElements 
                : array();

        //  send memory consumption to output
        if (SGL_PROFILING_ENABLED && function_exists('memory_get_usage')) {
            $output->memoryUsage = number_format(memory_get_usage());
        }

        $data = $templ->bufferedOutputObject($output, $elements);

        $GLOBALS['_SGL']['ERROR_OVERRIDE'] = false;
        if ($this->conf['site']['outputBuffering']) {
            ob_end_flush();
        }
        echo $data;
#print '<pre>'; print_r($GLOBALS['_SGL']['REQUEST']);
    }

    /**
     * Copies properties from source object to destination object.
     *
     * @access  public
     * @static
     * @param   object  $src    typically the validated input from Request
     * @param   object  $dest   typically the ouput object
     * @return  void
     */
    function _copyObjectProperties(& $src, & $dest)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aObjAttrs = get_object_vars($src);
        if (is_array($aObjAttrs)) {
            foreach ($aObjAttrs as $objAttrName => $objAttrValue) {
                $dest->$objAttrName = $objAttrValue;
            }
        }
    }   
}
?>