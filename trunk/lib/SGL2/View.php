<?php

/**
 * Container for output data and renderer strategy.
 *
 * @abstract
 * @package SGL
 */
abstract class SGL2_View
{
    /**
     * Response object.
     *
     * @var SGL2_Response
     */
    public $data;

    /**
     * Reference to renderer strategy.
     *
     * @var SGL2_OutputRendererStrategy
     */
    public $rendererStrategy;

    /**
     * Constructor.
     *
     * @param SGL2_Response $data
     * @param SGL2_OutputRendererStrategy $rendererStrategy
     * @return SGL2_View
     */
    public function __construct($response, SGL2_OutputRendererStrategy $rendererStrategy)
    {
        $this->data = $response;
        $this->rendererStrategy = $rendererStrategy;
    }

    /**
     * Post processing tasks specific to view type.
     *
     * @param SGL2_View $view
     * @return boolean
     */
    abstract public function postProcess(SGL2_View $view);


    /**
     * Delegates rendering strategy based on view.
     *
     * @param SGL2_View $this
     * @return string   Rendered output data
     */
    public function render()
    {
        return $this->rendererStrategy->render($this);
    }
}

?>