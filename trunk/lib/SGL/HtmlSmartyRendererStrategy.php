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
// | HtmlSmartyRendererStrategy.php                                            |
// +---------------------------------------------------------------------------+
// | Author: Malaney J. Hill  <malaney@gmail.com>                              |
// +---------------------------------------------------------------------------+

define('SGL_SMARTY_DIR', SGL_LIB_DIR .'/smarty/');
require_once SGL_SMARTY_DIR.'Smarty.class.php';

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
        $this->template_dir = SGL_WEB_ROOT.'/themes/smarty/';
        $this->compile_dir = SGL_SMARTY_DIR.'templates_c/';
        $this->config_dir = SGL_SMARTY_DIR.'configs/';
        $this->cache_dir = SGL_SMARTY_DIR.'cache/';
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

class SGL_HtmlSmartyRendererStrategy extends SGL_OutputRendererStrategy
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

        //	Initially I thought we needed to register our data as an object
        //	it turns out we do not need to this.  Assigning it like a
        //	traditional Smarty variable works just fine and even allows
        //	for the calling of methods
        $smarty->assign('result', $view->data);

        //	Need to build this string because Smarty doesn't look for templates
        // 	in multiple dirs the way Flexy does
        $moduleName = $view->data->moduleName;
        $masterTemplateName = $moduleName.'/'.$view->data->masterTemplate;
        $data = $smarty->fetch($masterTemplateName);
        SGL::setNoticeBehaviour(SGL_NOTICES_ENABLED);
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        if ($conf['site']['outputBuffering']) {
            ob_end_flush();
        }
        return $data;
    }
}
?>
