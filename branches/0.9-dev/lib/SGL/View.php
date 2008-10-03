<?php

/**
 * Container for output data and renderer strategy.
 *
 * @abstract
 * @package SGL
 */
class SGL_View
{
    /**
     * Output object.
     *
     * @var SGL_Output
     */
    var $data;

    /**
     * Reference to renderer strategy.
     *
     * @var SGL_OutputRendererStrategy
     */
    var $rendererStrategy;

    /**
     * Constructor.
     *
     * @param SGL_Output $data
     * @param SGL_OutputRendererStrategy $rendererStrategy
     * @return SGL_View
     */
    function SGL_View(&$data, $rendererStrategy)
    {
        $this->data = &$data;
        $this->rendererStrategy = $rendererStrategy;
    }

    /**
     * Post processing tasks specific to view type.
     *
     * @abstract
     * @return boolean
     */
    function postProcess() {}


    /**
     * Delegates rendering strategy based on view.
     *
     * @return string   Rendered output data
     */
    function render()
    {
        return $this->rendererStrategy->render($this);
    }
}

?>