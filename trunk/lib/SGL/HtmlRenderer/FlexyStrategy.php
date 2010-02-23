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

class SGL_HtmlRenderer_FlexyStrategy extends SGL_OutputRendererStrategy
{

    /**
     * Director for html Flexy renderer.
     *
     * @param SGL_View $view
     * @return string   rendered html output
     */
    function render(/*SGL_View*/ &$view)
    {
        //  suppress error notices in templates
        SGL::setNoticeBehaviour(SGL_NOTICES_DISABLED);

        //  prepare flexy object
        require_once 'HTML/Template/Flexy.php';
        $flexy = $this->initEngine($view->data);

        $masterTemplate = isset($view->data->masterTemplate)
            ? $view->data->masterTemplate
            : $view->data->manager->masterTemplate;

        $ok = $flexy->compile($masterTemplate);

        //  if some Flexy 'elements' exist in the output object, send them as
        //  2nd arg to Flexy::bufferedOutputObject()
        $elements = (   isset($view->data->flexyElements)
                  && is_array($view->data->flexyElements))
            ? $view->data->flexyElements
            : array();
        $data = $flexy->bufferedOutputObject($view->data, $elements);

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
    function initEngine(&$data)
    {
        //  initialise template engine
        $options = &PEAR::getStaticProperty('HTML_Template_Flexy','options');
        if (!isset($data->theme)) {
            $data->theme = 'default';
        }
        $options = array(
                                   // the current module's templates dir from the custom theme
            'templateDir'       => SGL_THEME_DIR . '/' . $data->theme . '/' . $data->moduleName . PATH_SEPARATOR .

                                   // the default template dir from the custom theme
                                   SGL_THEME_DIR . '/' . $data->theme . '/default'. PATH_SEPARATOR .

                                   // the current module's templates dir from the default theme
                                   SGL_MOD_DIR . '/'. $data->moduleName . '/templates' . PATH_SEPARATOR .

                                   // the configured default module's templates dir
                                   SGL_MOD_DIR . '/'. SGL_Config::get('site.defaultModule') . '/templates' . PATH_SEPARATOR .

                                   // the default template dir from the default theme
                                   SGL_MOD_DIR . '/default/templates',
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

        $flexy = new HTML_Template_Flexy();
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
?>