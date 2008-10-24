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
 * Abstract renderer strategy
 *
 * @abstract
 * @package SGL
 */
abstract class SGL2_OutputRendererStrategy
{
    /**
     * Prepare renderer options.
     *
     */
    abstract protected function _initEngine(SGL2_Response $data);

    /**
     * Abstract render method.
     *
     * @param SGL2_View $view
     */
    abstract public function render(SGL2_View $view);
}

class SGL2_HtmlRenderer_FlexyStrategy extends SGL2_OutputRendererStrategy
{
    const FORCE_COMPILE = 0;
    const DEBUG = 0;
    const FILTERS = 'SimpleTags';
    const ALLOW_PHP = true;
    const LOCALE = 'en';
    const COMPILER = 'Flexy';
    const VALID_FNS = 'include';
    const GLOBAL_FNS = true;
    const IGNORE =  0; //  don't parse forms when set to true

    /**
     * Director for html Flexy renderer.
     *
     * @param SGL2_View $view
     * @return string   rendered html output
     */
    public function render(SGL2_View $view)
    {
        //  suppress error notices in templates
        SGL2_ErrorHandler::setNoticeBehaviour(SGL2_NOTICES_DISABLED);

        //  prepare flexy object
        $flexy = $this->_initEngine($view->data);

#fixme:
        $layout = isset($view->data->layout)
            ? $view->data->layout
            : 'layout.html';
        $ok = $flexy->compile($layout);

        $data = $flexy->bufferedOutputObject($view->data, array());

        SGL2_ErrorHandler::setNoticeBehaviour(SGL2_NOTICES_ENABLED);
        return $data;
    }

    /**
     * Initialise Flexy options.
     *
     * @param SGL2_Output $data
     * @return boolean
     *
     * @todo move flexy constants to this class def
     */
    protected function _initEngine(SGL2_Response $response)
    {
        //  initialise template engine
        if (!isset($response->theme)) {
            $response->theme = SGL2_Config::get('site.defaultTheme');
        }
        $aTemplateDirs = array(
            // the current module's templates dir from the custom theme
            SGL2_THEME_DIR . '/' . $response->theme . '/' . $response->moduleName,
            // the default template dir from the custom theme
            SGL2_THEME_DIR . '/' . $response->theme . '/default',
            // the configured default module's templates dir
            SGL2_MOD_DIR . '/'. SGL2_Config::get('site.defaultModule') . '/templates',
            // the default template dir from the default theme
            SGL2_MOD_DIR . '/default/templates'
            );
        $options = array(
            'templateDir'       => implode(PATH_SEPARATOR, array_unique($aTemplateDirs)),
            'templateDirOrder'  => 'reverse',
            'multiSource'       => true,
            'compileDir'        => SGL2_CACHE_DIR . '/tmpl/' . $response->theme,
            'forceCompile'      => self::FORCE_COMPILE,
            'debug'             => self::DEBUG,
            'allowPHP'          => self::ALLOW_PHP,
            'filters'           => self::FILTERS,
            'locale'            => self::LOCALE,
            'compiler'          => self::COMPILER,
            'valid_functions'   => self::VALID_FNS,
            'flexyIgnore'       => self::IGNORE,
            'globals'           => true,
            'globalfunctions'   => self::GLOBAL_FNS,
        );

        $ok = $this->_setupPlugins($response, $options);
        $flexy = new HTML_Template_Flexy($options);
        return $flexy;
    }

    /**
     * Setup Flexy plugins if specified.
     *
     * @param SGL2_Output $data
     * @param array $options
     * @return boolean
     */
    protected function _setupPlugins(SGL2_Response $data, array $options)
    {
        //  Configure Flexy to use SGL ModuleOutput Plugin
        //   If an Output.php file exists in module's dir
        $customOutput = SGL2_MOD_DIR . '/' . $data->moduleName . '/classes/Output.php';
        if (SGL2_File::exists($customOutput)) {
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
?>