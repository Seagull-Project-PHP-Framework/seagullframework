<?php

require_once SGL_MOD_DIR . '/user/classes/UserPreferenceMgr.php';
$theme = $_SESSION['aPrefs']['theme'];
$options = &PEAR::getStaticProperty('HTML_Template_Flexy','options');
$config = parse_ini_file('OnlineUsers.ini',TRUE);
$options = $config['HTML_Template_Flexy'];
$options['multiSource'] = true;
$options['templateDir'] = SGL_THEME_DIR . "/$theme/block/blocks";
$options['compileDir'] = SGL_CACHE_DIR . '/tmpl/' . $theme;
$options['forceCompile'] = SGL_FLEXY_FORCE_COMPILE;
$options['allowPHP'] = SGL_FLEXY_ALLOW_PHP;
$options['filters'] = SGL_FLEXY_FILTERS;
$options['locale'] = SGL_FLEXY_LOCALE;
$options['compiler'] = SGL_FLEXY_COMPILER;
$options['valid_functions'] = SGL_FLEXY_VALID_FNS;
$options['flexyIgnore'] = SGL_FLEXY_IGNORE;
$options['globals'] = true;
$options['globalfunctions'] = SGL_FLEXY_GLOBAL_FNS;

class OnlineUsers
{
    var $template = "OnlineUsers.html";

    function init($output)
    {
        return $this->getBlockContent($output);
    }

    function getBlockContent($output)
    {
        // Get the user id from the current session
        $uid = SGL_HTTP_Session::getUid();

        $theme = $_SESSION['aPrefs']['theme'];
        $output->webRoot = SGL_BASE_URL;
        $output->theme = $theme;
        $output->guests = SGL_HTTP_Session::getGuestSessionCount();
        $output->members = SGL_HTTP_Session::getMemberSessionCount();
        $output->total = $output->members + $output->guests;

        return $this->process($output);
    }

    function process($output) {
        $templ = new HTML_Template_Flexy();
        $templ->compile($this->template);
        return $templ->bufferedOutputObject($output);
    }
}
?>