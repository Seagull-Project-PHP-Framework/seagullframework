<?php

//  Flexy template settings, include with Flexy Renderer only
define('SGL_FLEXY_FORCE_COMPILE',       0);
define('SGL_FLEXY_DEBUG',               0);
define('SGL_FLEXY_FILTERS',             'SimpleTags');
define('SGL_FLEXY_ALLOW_PHP',           true);
define('SGL_FLEXY_LOCALE',              'en');
define('SGL_FLEXY_COMPILER',            'Flexy');
define('SGL_FLEXY_VALID_FNS',           'include');
define('SGL_FLEXY_GLOBAL_FNS',          true);
define('SGL_FLEXY_IGNORE',              0); //  don't parse forms when set to true

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
    abstract function initEngine($data);

    /**
     * Abstract render method.
     *
     * @param SGL_View $view
     */
    abstract function render(SGL_View $view);
}

class SGL_HtmlRenderer_FlexyStrategy extends SGL_OutputRendererStrategy
{

    /**
     * Director for html Flexy renderer.
     *
     * @param SGL_View $view
     * @return string   rendered html output
     */
    function render(SGL_View $view)
    {
        //  suppress error notices in templates
        SGL::setNoticeBehaviour(SGL_NOTICES_DISABLED);

        //  prepare flexy object
        $flexy = $this->initEngine($view->data);

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
    function initEngine($response)
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

        $ok = $this->setupPlugins($response, $options);
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
    function setupPlugins($data, $options)
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