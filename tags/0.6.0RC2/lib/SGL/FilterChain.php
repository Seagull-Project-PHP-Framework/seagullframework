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
        foreach ($this->aFilters as $filter) {
            if (!class_exists($filter)) {
                $path = trim(preg_replace('/_/', '/', $filter)) . '.php';
                require_once $path;
            }
        }
    }
}
?>