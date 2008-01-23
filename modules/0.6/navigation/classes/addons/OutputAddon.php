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
// | OutputAddon.php                                                           |
// +---------------------------------------------------------------------------+
// | Author:   Andrey Podshivalov <planetaz@gmail.com>                         |
// +---------------------------------------------------------------------------+
// $Id: OutputAddon.php,v 0.1 2006/01/29 23:28:37

/**
 * OutputAddon class
 *
 * Gets additional navigation tree from app controller output.
 *
 * @package navigation
 * @author  Andrey Podshivalov <planetaz@gmail.com>
 * @version 0.2
 * @access  public
 * @since   PHP 4.4.2
 */

class OutputAddon
{
    var $sectionId;
    
    function init(&$output, &$section, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        if (!$section->is_enabled) {
            return false;
        }
        $navAddon = @$aParams['navAddon'];
        if (empty($navAddon)) {
            $navAddon = 'navAddon';
        }
        if (isset($output->$navAddon)) {
            $this->sectionId = $section->section_id;
            $aSections       = $output->$navAddon;
            $this->_setSectionId($aSections);
            return $aSections;
        } else {
            return false;
        }
    }

    
    /**
     * Sets section_id for all nodes.
     *
     * @access  private
     * @param   array   $aSections
     * @param   int     $sectionId;
     * @return  array
     */
    function _setSectionId(&$aSections)
    {
        foreach ($aSections as $key => $section) {
            $section->section_id = $this->sectionId;
            if ($section->children) {
                $this->_setSectionId($section->children);
            }
            $aSections[$key] = $section;
        }
    }
}
?>