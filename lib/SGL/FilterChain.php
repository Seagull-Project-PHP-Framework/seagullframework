<?php
class SGL_FilterChain
{
    var $aFilters;

    function SGL_FilterChain($aFilters)
    {
        $this->aFilters = array_map('trim', $aFilters);
    }

    function doFilter(&$input, &$output)
    {
        $this->loadFilters();

        $filters = '';
        $closeParens = '';

        $code = '$process = ';
        foreach ($this->aFilters as $filter) {
            $filters .= "new $filter(\n";
            $closeParens .= ')';
        }
        $code = $filters . $closeParens;
        eval("\$process = $code;");

        $process->process($input, $output);
    }

    function loadFilters()
    {
        //  allow libs to come from custom path or seagull/lib/SGL/Task
        $ok = ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR
            . SGL_LIB_DIR);
        if (!$ok) {
            SGL::displayStaticPage('You need to be able to run ini_set(), sometimes '.
            'this is not available in safe_mode or in earlier versions of PHP');
        }

        foreach ($this->aFilters as $filter) {
            if (!class_exists($filter)) {
                $path = trim(preg_replace('/_/', '/', $filter)) . '.php';
                require_once $path;
            }
        }
    }
}
?>