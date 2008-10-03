<?php

/**
 * Container for output data and renderer strategy.
 *
 * @abstract
 * @package SGL
 */
abstract class SGL_View
{
    /**
     * Response object.
     *
     * @var SGL_Response
     */
    public $data;

    /**
     * Reference to renderer strategy.
     *
     * @var SGL_OutputRendererStrategy
     */
    public $rendererStrategy;

    /**
     * Constructor.
     *
     * @param SGL_Response $data
     * @param SGL_OutputRendererStrategy $rendererStrategy
     * @return SGL_View
     */
    function __construct($response, SGL_OutputRendererStrategy $rendererStrategy)
    {
        $this->data = $response;
        $this->rendererStrategy = $rendererStrategy;
    }

    /**
     * Post processing tasks specific to view type.
     *
     * @param SGL_View $view
     * @return boolean
     */
    abstract public function postProcess(SGL_View $view);


    /**
     * Delegates rendering strategy based on view.
     *
     * @param SGL_View $this
     * @return string   Rendered output data
     */
    public function render()
    {
        return $this->rendererStrategy->render($this);
    }
}

?>