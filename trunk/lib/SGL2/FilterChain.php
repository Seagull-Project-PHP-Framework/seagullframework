<?php

/**
 * Manages an array of filters.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL2_FilterChain
{
    protected $_aFilters;
    protected $_target;

    public function __construct($aFilters)
    {
        $this->_aFilters = array_map('trim', $aFilters);
    }

    public function setTarget($target)
    {
        $this->_target = $target;
        return true;
    }

    public function getTarget()
    {
        return $this->_target;
    }

    public function doFilter(SGL2_Request $input, SGL2_Response $output)
    {
        array_push($this->_aFilters, $this->getTarget());
        $filters = '';
        $closeParens = '';
        $code = '$process = ';
        foreach ($this->_aFilters as $filter) {
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