<?php

/**
 * Abstract renderer strategy
 *
 * @abstract
 * @package SGL
 */
abstract class SGL_OutputRendererStrategy
{
    /**
     * Prepare renderer options.
     *
     */
    abstract protected function _initEngine(SGL_Response $data);

    /**
     * Abstract render method.
     *
     * @param SGL_View $view
     */
    abstract public function render(SGL_View $view);
}

class SGL_HtmlRenderer_FlexyStrategy extends SGL_OutputRendererStrategy
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
     * @param SGL_View $view
     * @return string   rendered html output
     */
    public function render(SGL_View $view)
    {
        //  suppress error notices in templates
        SGL::setNoticeBehaviour(SGL_NOTICES_DISABLED);

        //  prepare flexy object
        $flexy = $this->_initEngine($view->data);

        $masterTemplate = isset($view->data->masterTemplate)
            ? $view->data->masterTemplate
            : 'master.html';
        $ok = $flexy->compile($masterTemplate);

        $data = $flexy->bufferedOutputObject($view->data, array());

        SGL::setNoticeBehaviour(SGL_NOTICES_ENABLED);
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
    protected function _initEngine(SGL_Response $response)
    {
        //  initialise template engine
        if (!isset($response->theme)) {
            $response->theme = 'default';
        }
        $aTemplateDirs = array(
            // the current module's templates dir from the custom theme
            SGL_THEME_DIR . '/' . $response->theme . '/' . $response->moduleName,
            // the default template dir from the custom theme
            SGL_THEME_DIR . '/' . $response->theme . '/default',
            // the configured default module's templates dir
            SGL_MOD_DIR . '/'. SGL_Config::get('site.defaultModule') . '/templates',
            // the default template dir from the default theme
            SGL_MOD_DIR . '/default/templates'
            );
        $options = array(
            'templateDir'       => implode(PATH_SEPARATOR, array_unique($aTemplateDirs)),
            'templateDirOrder'  => 'reverse',
            'multiSource'       => true,
            'compileDir'        => SGL_CACHE_DIR . '/tmpl/' . $response->theme,
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
     * @param SGL_Output $data
     * @param array $options
     * @return boolean
     */
    protected function _setupPlugins(SGL_Response $data, array $options)
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
?>