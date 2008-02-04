<?php

/**
 * TemplateRenderer for ArrayDriver.
 *
 * @package seagull
 * @subpackage navigation
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class ArrayDriver_TemplateRenderer
{
    /**
     * Rendering params.
     *
     * @access private
     *
     * @var array
     */
    var $_aParams = array();

    /**
     * SGL_Output.
     *
     * @access private
     *
     * @var SGL_Output
     */
    var $_output;

    /**
     * SGL_HtmlSimpleView.
     *
     * @access private
     *
     * @var SGL_HtmlSimpleView
     */
    var $_view;

    /**
     * Constructor.
     *
     * @access public
     *
     * @param array $aParams
     *
     * @return ArrayDriver_TemplateRenderer
     */
    function ArrayDriver_TemplateRenderer($aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $outputClass            = SGL_FrontController::getOutputClass();
        $output                 = & new $outputClass();
        $output->theme          = $_SESSION['aPrefs']['theme'];
        $output->moduleName     = 'navigation';
        $output->masterTemplate = !empty($aParams['template'])
            ? $aParams['template']
            : 'arrayDriverTemplateRenderer.html';
        $output->renderer       = &$this;

        SGL_Task_BuildOutputData::addOutputData($output);
        $this->_output  = &$output;
        $this->_view    = & new SGL_HtmlSimpleView($this->_output);
        $this->_aParams = $aParams;
    }

    /**
     * Render sections.
     *
     * @access public
     *
     * @param array $aSections
     *
     * @return string
     */
    function toHtml($aSections)
    {
        $ret = '';
        if (!empty($aSections)) {
            $output            = &$this->_output;
            $output->aSections = $aSections;
            $ret               = $this->_view->render();
        }
        return $ret;
    }

    /**
     * Get attributes string.
     *
     * @access public
     *
     * @param array $aSection
     * @param string $initial
     *
     * @return string
     */
    function getAttributes($aSection, $initial = '')
    {
        $ret = $initial;
        if (!empty($aSection['is_current'])) {
            $ret .= ' current';
        }
        if (!empty($aSection['sub'])) {
            $ret .= ' parent';
        }
        return !empty($ret)
            ? ' class="' . trim($ret) .  '"'
            : $ret;
    }
}

?>