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
// | SimpleRenderer.php                                                        |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: SimpleRenderer.php,v 1.43 2005/06/20 23:28:37 demian Exp $

/**
 * Generates HTML containing data from sections table.
 *
 * @package navigation
 * @author  Andy Crain <apcrain@fuse.net>
 * @author  Demian Turner <demian@phpkitchen.com>
 * @author  AJ Tarachanowicz <ajt@localhype.net>
 * @author  Andrey Podshivalov <planetaz@gmail.com>
 */

class SimpleRenderer
{
    function SimpleRenderer(&$navDriver)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->driver = &$navDriver;
    }

    /**
     * Returns HTML unordered list with subsections nested; can be used with CSS for navigation
     * tabs. Adds attribute class="current" to <li> tags.
     *
     * @access  public
     * @param   array $sectionNodes   array of DataObjects_Section objects
     * @param   int $currentRenderedLevel
     * @return  string | false
     */
    function toHtml(&$aSectionNodes, $currentRenderedLevel = 0)
    {
        $listItems = '';
        $currentRenderedLevel++;

        foreach ($aSectionNodes as $section) {
            if ($section->is_enabled) {
                $liAtts = '';
                if ($section->isCurrent || $section->childIsCurrent) {
                    $liAtts = ' class="current"';
                }
                if (isset($section->uriExternal) && empty($section->uriAlias)) {
                    $url = $section->resource_uri;
                } elseif (isset($section->uriEmpty)) {
                    $url = 'javascript:void(0)';
                } else {
                    $url = $this->driver->makeLinkFromSection($section);
                }

                $accessKey  = !empty($section->access_key)
                    ? ' accesskey="' . $section->access_key . '"' : '';
                $anchor     = '<a' . ' href="' . $url . '"' . $accessKey . '>' . $section->title . '</a>';
                $listItems .= "<li" . $liAtts . '>' . $anchor;

                // show children nodes
                if ($section->children

                    // check levelsToRender stuff
                    && (!$this->driver->_levelsToRender
                        || $this->driver->_levelsToRender > $currentRenderedLevel)

                    // check collapsed stuff
                    && (!$this->driver->_collapsed
                        || array_key_exists($section->section_id, $this->driver->_aAllCurrentPages)))
                {
                   $listItems .= $this->toHtml($section->children, $currentRenderedLevel);
                }
                $listItems .= "</li>\n";
            }
        }
        $output = ($listItems) ? "\n<ul>" . $listItems . "</ul>\n" : false;
        return $output;
    }
}
?>