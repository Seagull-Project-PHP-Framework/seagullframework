<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2008, Demian Turner                                         |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | o Redistributions of source code must retain the above copyright          |
// |   notice, this list of conditions and the following disclaimer.           |
// | o Redistributions in binary form must reproduce the above copyright       |
// |   notice, this list of conditions and the following disclaimer in the     |
// |   documentation and/or other materials provided with the distribution.    |
// | o The names of the authors may not be used to endorse or promote          |
// |   products derived from this software without specific prior written      |
// |   permission.                                                             |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT         |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR     |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT      |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,     |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT          |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,     |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE     |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.      |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | TemplateRenderer.php                                                      |
// +---------------------------------------------------------------------------+
// | Author:   Andrey Podshivalov <planetaz@gmail.com>                         |
// +---------------------------------------------------------------------------+

/**
 * Generates based on template HTML containing data from sections table.
 *
 * @package navigation
 * @author  Andrey Podshivalov <planetaz@gmail.com>
 */

class TemplateRenderer
{
    var $driver;
    var $output;
    var $view;

    function TemplateRenderer(&$navDriver)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->driver           = &$navDriver;
        $this->output           = &new SGL_Output();
        $this->view             = &new SGL_HtmlSimpleView($this->output);

        $output                 = &$this->output;
        $output->theme          = (isset($navDriver->output->theme)) ? 
                                  $navDriver->output->theme :
                                  $_SESSION['aPrefs']['theme'];

        $output->moduleName     = 'navigation';
        $output->renderer       = &$this;
        $output->masterTemplate = $navDriver->_template
            ? $navDriver->_template : 'templateRenderer.html';
    }

    /**
     * Returns rendered navigation menu.
     *
     * @access  public
     * @param   array $sectionNodes   array of DataObjects_Section objects
     * @param   int $currentRenderedLevel
     * @param   int $parentSectionId
     * @return  string | false
     */
    function toHtml(&$aSectionNodes, $currentRenderedLevel = 0, $parentSectionId = 0)
    {
        $html = false;
        $currentRenderedLevel++;

        //  check levelsToRender stuff
        if ((empty($this->driver->_levelsToRender)
            || $this->driver->_levelsToRender > $currentRenderedLevel
            || $currentRenderedLevel == 1)

            //  check collapsed stuff
            && (empty($this->driver->_collapsed) || empty($parentSectionId)
                || array_key_exists($parentSectionId, $this->driver->_aAllCurrentPages)))
        {
            $output            = &$this->output;
            $output->level     = $currentRenderedLevel;
            $output->aSections = $aSectionNodes;
            $html              = $this->view->render();
            $output->level     = $currentRenderedLevel-1;
        }

        return $html;
    }

    /**
     * Returns URL.
     *
     * @access  public
     * @param   object $section   DataObjects_Section object
     * @return  string
     */
    function makeUrl(&$section)
    {
        if (isset($section->uriExternal) && empty($section->uriAlias)) {
            $url = $section->resource_uri;
        } else {
            $url = $this->driver->makeLinkFromSection($section);
        }
        return $url;
    }

    /**
     * Returns element attributes.
     *
     * @access  public
     * @param   object $section   DataObjects_Section object
     * @param   string $type      element type
     * @return  string | false
     */
    function getAttributes(&$section, $type = null)
    {
        switch ($type) {
        case 'a':
            $attributes = !empty($section->access_key)
                ? ' accesskey="' . $section->access_key . '"' : '';
            break;
        case 'li':
            $attributes = !empty($section->children)
                ? 'parent' : '';
            $attributes .= (!empty($section->isCurrent) || !empty($section->childIsCurrent))
                ? ' current' : '';
            $attributes = $attributes ? ' class="' . trim($attributes) . '"' : '';
            break;
        default:
            $attributes = false;
        }
        return $attributes;
    }
}
?>