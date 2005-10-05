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
require_once SGL_CORE_DIR . '/Tasks.php';
require_once SGL_CORE_DIR . '/HTTP.php';
require_once 'HTML/Template/Flexy.php';

/**
 * Application controller.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.49 $
 * @since   PHP 4.1
 */
class SGL_AppController
{  
    /**
     * Main invocation, init tasks plus main process.
     *
     */
    function run()
    {
        $input = &SGL_RequestRegistry::singleton();
        $input->setRequest($req = SGL_Request::singleton());
        
        $process =  new SGL_Init(
                    new SGL_DiscoverClientOs(
                    new SGL_ManagerResolver(
                    new SGL_InitSession(
                    new SGL_InitLangSupport(
                    new SGL_InitPerms(
                    new SGL_AuthenticateRequest(
                    new SGL_BuildHeaders(
                    new SGL_SetLocale(
                    new SGL_DetectDebug(
                    new SGL_DetectBlackListing(
                    new SGL_MainProcess()
                   )))))))))));
                   
        $process->process($input);
    }


    /**
     * Adds pages to a Wizard queue.
     *
     * @access  public
     * @param   string  $pageName   the name of the calling script
     * @param   array   $param      params to be appended to URL
     * @return  void
     */
    function addPage($pageName, $param=null)
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
}

/**
 * Abstract renderer strategy
 *
 * @abstract
 */
class SGL_OutputRendererStrategy
{
    /**
     * Prepare renderer options.
     *
     */
    function initEngine() {}
    
    /**
     * Abstract render method.
     *
     * @param SGL_View $view
     */
    function render($view) {}
}


//  Flexy template settings - for php5, include with Flexy Renderer only
define('SGL_FLEXY_FORCE_COMPILE',       0);
define('SGL_FLEXY_DEBUG',               0);
define('SGL_FLEXY_FILTERS',             'SimpleTags');
define('SGL_FLEXY_ALLOW_PHP',           true);
define('SGL_FLEXY_LOCALE',              'en');
define('SGL_FLEXY_COMPILER',            'Standard');
define('SGL_FLEXY_VALID_FNS',           'include');
define('SGL_FLEXY_GLOBAL_FNS',          true);
define('SGL_FLEXY_IGNORE',              0); //  don't parse forms when set to true
        
class SGL_HtmlFlexyRendererStrategy extends SGL_OutputRendererStrategy
{
    
    /**
     * Director for html Flexy renderer.
     *
     * @param SGL_View $view
     * @return string   rendered html output
     */
    function render(/*SGL_View*/ &$view) 
    {
        //  invoke html view specific post-process tasks
        $view->postProcess($view);
        
        //  suppress error notices in templates
        SGL::setNoticeBehaviour(SGL_NOTICES_DISABLED);
        
        //  prepare flexy object
        $flexy = $this->initEngine($view->data);
        
        $ok = $flexy->compile($view->data->masterTemplate);

        //  if some Flexy 'elements' exist in the output object, send them as
        //  2nd arg to Flexy::bufferedOutputObject()
        $elements = (   isset($view->data->flexyElements) 
                  && is_array($view->data->flexyElements))
                ? $view->data->flexyElements 
                : array();

        $data = $flexy->bufferedOutputObject($view->data, $elements);
        
        SGL::setNoticeBehaviour(SGL_NOTICES_ENABLED);
        
//        if ($this->conf['site']['outputBuffering']) {
//            ob_end_flush();
//        }
        return $data;
    }
    
    /**
     * Initialise Flexy options.
     *
     * @param SGL_Output $data
     * @return boolean
     *
     * @todo move flexy constants to this class def
     */
    function initEngine(&$data)
    {
        //  initialise template engine
        $options = &PEAR::getStaticProperty('HTML_Template_Flexy','options');
        $options = array(
            'templateDir'       =>  SGL_THEME_DIR . '/' . $data->theme . '/' . $data->moduleName . PATH_SEPARATOR .
                                    SGL_THEME_DIR . '/default/' . $data->moduleName . PATH_SEPARATOR .
                                    SGL_THEME_DIR . '/' . $data->theme . '/default'. PATH_SEPARATOR .
                                    SGL_THEME_DIR . '/default/default',
            'templateDirOrder'  => 'reverse',
            'multiSource'       => true,
            'compileDir'        => SGL_CACHE_DIR . '/tmpl/' . $data->theme,
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
        
        $ok = $this->setupPlugins($data, $options);
        
        $flexy = & new HTML_Template_Flexy();
        return $flexy;
    }
    
    /**
     * Setup Flexy plugins if specified.
     *
     * @param SGL_Output $data
     * @param array $options
     * @return boolean
     */
    function setupPlugins(&$data, &$options)
    {
        //  Configure Flexy to use SGL ModuleOutput Plugin 
        //   If an Output.php file exists in module's dir
        $customOutput = SGL_MOD_DIR . '/' . $data->moduleName . '/classes/Output.php';
        if (is_readable($customOutput)) {
            $className = ucfirst($data->moduleName) . 'Output';
            if (isset($options['plugins'])) {
                $options['plugins'] = $options['plugins'] + array($className => $customOutput);
            } else {
                $options['plugins'] = array($className => $customOutput);
            }
        }
        return true;
    }
}

/**
 * Container for output data and renderer strategy.
 *
 * @abstract 
 *
 */
class SGL_View
{
	/**
	 * Output object.
	 *
	 * @var SGL_Output
	 */
	var $data;
	
    /**
     * Reference to renderer strategy.
     *
     * @var SGL_OutputRendererStrategy
     */
    var $rendererStrategy;
    
    /**
     * Constructor.
     *
     * @param SGL_Output $data
     * @param SGL_OutputRendererStrategy $rendererStrategy
     * @return SGL_View
     */
    function SGL_View($data, $rendererStrategy)
    {
    	$this->data = $data;
    	$this->rendererStrategy = $rendererStrategy;	
    }
    
    /**
     * Post processing tasks specific to view type.
     *
     * @abstract 
     * @return boolean
     */
    function postProcess() {}
    
    
    /**
     * Delegates rendering strategy based on view.
     *
     * @return string   Rendered output data
     */
    function render() 
    {
    	return $this->rendererStrategy->render($this);
    }  
}

class SGL_HtmlView extends SGL_View
{
    /**
     * Html specific implementation of view object.
     *
     * @param SGL_Output $data
     * @param SGL_OutputRendererStrategy $outputRendererStrategy
     * @return SGL_HtmlView
     */
    function SGL_HtmlView(&$data, $outputRendererStrategy)
    {
    	parent::SGL_View($data, $outputRendererStrategy);
    }
    
    function postProcess(/*SGL_View*/ &$view)
    {       
        $process =  new SGL_PrepareNavigation(
                    new SGL_PrepareBlocks(
                    new SGL_SetupWysiwyg(
                    new SGL_GetPerformanceInfo(
                    new SGL_PostProcess()
                   ))));
                   
        $process->process($view);
    }
}

/**
 * Abstract request processor.
 *
 * @abstract 
 *
 */
class SGL_ProcessRequest
{
    function process(/*SGL_Output*/ $data) {}
}

/**
 * Decorator.
 *
 * @abstract 
 */
class SGL_DecorateProcess extends SGL_ProcessRequest
{
    var $processRequest;
    
    function SGL_DecorateProcess(/* SGL_ProcessRequest */ $pr)
    {
        $this->processRequest = $pr;
        $this->conf = & $GLOBALS['_SGL']['CONF'];
    }
}
?>