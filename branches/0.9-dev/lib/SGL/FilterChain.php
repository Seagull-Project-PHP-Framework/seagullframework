<?php

/**
 * Manages an array of filters.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_FilterChain
{
    var $aFilters;

    function SGL_FilterChain($aFilters)
    {
        $this->aFilters = array_map('trim', $aFilters);
    }

    function doFilter(&$input, &$output)
    {
        $filters = '';
        $closeParens = '';

        $code = '$process = ';
        foreach ($this->aFilters as $filter) {
            if (class_exists($filter)) {
                $filters .= "new $filter(\n";
                $closeParens .= ')';
            }
        }
        $code = $filters . $closeParens;
        eval("\$process = $code;");

        $process->process($input, $output);
    }
}
?>