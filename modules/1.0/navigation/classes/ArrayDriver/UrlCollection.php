<?php

/**
 * Return array or URLs.
 *
 * @todo ArrayDriver_UrlCollection::toHtml() name is misleading.
 *
 * @package seagull
 * @subpackage navigation
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class ArrayDriver_UrlCollection
{
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

        $this->_aParams = $aParams;
    }

    /**
     * @access public
     *
     * @param array $aSections
     *
     * @return array
     */
    function toHtml($aSections)
    {
        $aRet = array();
        foreach ($aSections as $section) {
            $aRet[] = $section['link'];
            if (!empty($section['sub'])) {
                foreach ($section['sub'] as $subsection) {
                    $aRet[] = $subsection['link'];
                }
            }
        }
        return $aRet;
    }
}

?>