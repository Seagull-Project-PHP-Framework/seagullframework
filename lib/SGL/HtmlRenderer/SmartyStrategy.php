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
// | HtmlSmartyRendererStrategy.php                                            |
// +---------------------------------------------------------------------------+
// | Author: Malaney J. Hill  <malaney@gmail.com>                              |
// +---------------------------------------------------------------------------+

define('SGL_SMARTY_DIR' , SGL_LIB_DIR  . '/other/Smarty');
require_once SGL_SMARTY_DIR . '/libs/Smarty.class.php';

class SGL_Smarty extends Smarty
{
    /**
     *  Constructor.
     *  @access public
     *  @return void
     */
    function SGL_Smarty()
    {
        $this->Smarty();
        $this->debugging = true;
        $this->template_dir = SGL_WEB_ROOT . '/smarty';
        $this->compile_dir = SGL_CACHE_DIR . '/templates_c';
        $this->config_dir = SGL_SMARTY_DIR . '/unit_test/configs';
        $this->cache_dir = SGL_CACHE_DIR;
        $this->plugins_dir = SGL_SMARTY_DIR . '/libs/plugins';

        //  Define alternative mechanism for locating templates
        $this->default_template_handler_func = array($this, 'locateTemplate');

        if (!is_writable($this->compile_dir)) {
            require_once 'System.php';

            //  pass path as array to avoid windows space parsing prob
            $success = System::mkDir(array($this->compile_dir));
            if (!$success) {
                SGL::raiseError('The tmp directory does not '.
                'appear to be writable, please give the webserver permissions to write to it',
                SGL_ERROR_FILEUNWRITABLE, PEAR_ERROR_DIE);
            }
        }
    }

    /**
     *  Default template handler for Smarty
     *  Provides an alternative mechanism to Smarty
     *  for finding templates when one is not found at original
     *  file location.  Only called by Smarty
     *
     *  @access public
     *  @param  string $resourceType        (i.e. file, db, ldap)
     *  @param  string $resourceName        template file name
     *  @param  string $templateSource      source of template file
     *  @param  string $templateTimestamp   mtime of template file
     *  @param  object $oSmarty            Smarty object
     *  @return true if template found, false if not
     */
    function locateTemplate($resourceType, $resourceName, &$templateSource,
        &$templateTimestamp, &$oSmarty)
    {
        if (!is_readable($resourceName)) {
            //  parse module_name
            list($moduleName, $templateName) = split('/', $resourceName);
            $arr = array($moduleName, $templateName);
            $newResourceName = $oSmarty->template_dir . '/' . join('/', $arr);
            if (file_exists($newResourceName)) {
                $templateSource = file_get_contents($newResourceName);
                $templateTimestamp = filemtime($newResourceName);
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Returns a singleton Smarty instance.
     *
     * example usage:
     * $smarty = & SGL_Smarty::singleton();
     * warning: in order to work correctly, the cache
     * singleton must be instantiated statically and
     * by reference
     *
     * @access  public
     * @static
     * @return  mixed reference to SGL_Smarty object
     */
    function &singleton()
    {
        static $instance;
        if (!isset($instance)) {
            $class = __CLASS__;
            $instance = new $class();
        }
        return $instance;
    }
}

class SGL_HtmlRenderer_SmartyStrategy extends SGL_OutputRendererStrategy
{
    /**
     * Director for html Smarty renderer.
     *
     * @param SGL_View $view
     * @return string   rendered html output
     */
    function render( /*SGL_View*/ &$view)
    {
        //  invoke html view specific post-process tasks
        $view->postProcess($view);

        //  suppress error notices in templates
        SGL::setNoticeBehaviour(SGL_NOTICES_DISABLED);

        //  prepare smarty object
        $smarty = &SGL_Smarty::singleton();

        //    Initially I thought we needed to register our data as an object
        //    it turns out we do not need to this.  Assigning it like a
        //    traditional Smarty variable works just fine and even allows
        //    for the calling of methods
        $smarty->assign('result', $view->data);

        //    Need to build this string because Smarty doesn't look for templates
        //     in multiple dirs the way Flexy does
        $moduleName = $view->data->moduleName;
        $masterTemplateName = $moduleName.'/'.$view->data->masterTemplate;
        $data = $smarty->fetch($masterTemplateName);
        SGL::setNoticeBehaviour(SGL_NOTICES_ENABLED);

        return $data;
    }
}
?>
