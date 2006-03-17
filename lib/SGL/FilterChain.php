<?php
class SGL_FilterChain
{
    var $aFilters;

    function SGL_FilterChain($aFilters)
    {
        $this->aFilters = $aFilters;
    }

    function doFilter(&$input)
    {
        $this->loadFilters();
        $coreProcessor = $this->getCoreProcessor();

        $filters = '';
        $closeParens = '';

        $code = '$process = ';
        foreach ($aFilters as $filter) {
            $filters .= "new $filter(\n";
            $closeParens .= ')';
        }
        $code = $filters . $closeParens;
        eval("\$process = $code;");

        $process->process($input);
    }

    function loadFilters()
    {
        foreach ($this->aFilters as $filter) {
            if (!class_exists('$filter')) {
                $path = preg_replace('/_/', '/', $className) . '.php';
                require_once $path;
            }
        }
    }
}
?>