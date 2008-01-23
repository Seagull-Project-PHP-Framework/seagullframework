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
// | HtmlSavant2RendererStrategy.php                                           |
// +---------------------------------------------------------------------------+
// | Authors: Andrey Podshivalov <planetaz@gmail.com>                          |
// +---------------------------------------------------------------------------+

require_once 'Savant2.php';

class SGL_Savant2 extends Savant2
{
    /**
     * Constructor.
     * @access public
     * @param string $theme The theme name
     * @param string $moduleName The module name
     * @return void
     */
    function SGL_Savant2($theme = 'default', $moduleName = 'default')
    {
        $options = array(
            'template_path' => SGL_WEB_ROOT . '/savant/default/default' . PATH_SEPARATOR .
                               SGL_WEB_ROOT . '/savant/' . $theme . '/default' . PATH_SEPARATOR .
                               SGL_WEB_ROOT . '/savant/default/' . $moduleName. PATH_SEPARATOR .
                               SGL_WEB_ROOT . '/savant/' . $theme . '/' . $moduleName,
            'resource_path' => SGL_MOD_DIR . '/' . $moduleName . '/classes',
        );

        $this->Savant2($options);
    }

    /**
     * Returns a singleton Savant2 instance.
     *
     * example usage:
     * $savant2 = & SGL_Savant2::singleton($theme, $moduleName);
     * warning: in order to work correctly, the cache
     * singleton must be instantiated statically and
     * by reference
     *
     * @access  public
     * @static
     * @param string $theme The theme name
     * @param string $moduleName The module name
     * @return  mixed reference to SGL_Savant2 object
     */
    function &singleton($theme = 'default', $moduleName = 'default')
    {
        static $instance;
        if (!isset($instance)) {
            $class = __CLASS__;
            $instance = new $class($theme, $moduleName);
        }
        return $instance;
    }
}

class SGL_HtmlRenderer_Savant2Strategy extends SGL_OutputRendererStrategy
{
    /**
     * Director for html Savant2 renderer.
     *
     * @param SGL_View $view
     * @return string   rendered html output
     */
    function render( /*SGL_View*/ &$view)
    {
        //  invoke html view specific post-process tasks
        $view->postProcess($view);

        //  prepare Savant2 object
        $moduleName = isset($view->data->moduleName)
            ? $view->data->moduleName
            : 'default';
        $savant2 = &SGL_Savant2::singleton($view->data->theme, $moduleName);

        //  suppress error notices in templates
        SGL::setNoticeBehaviour(SGL_NOTICES_DISABLED);

        $savant2->assign('result', $view->data);
        $data = $savant2->fetch($view->data->masterTemplate);

        SGL::setNoticeBehaviour(SGL_NOTICES_ENABLED);

        return $data;
    }
}
?>