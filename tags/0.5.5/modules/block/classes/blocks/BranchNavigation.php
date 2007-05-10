<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
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
// | Seagull 0.5                                                               |
// +---------------------------------------------------------------------------+
// | BranchNavigation.php                                                      |
// +---------------------------------------------------------------------------+
// | Author: Alexander J. Tarachanowicz II <ajt@localhype.net>                 |
// +---------------------------------------------------------------------------+

/**
 * Branch Navigation block.
 *
 * @package block
 * @author  Alexander J. Tarachanowicz II <ajt@localhype.net>
 * @version $Revision: 1.6 $
 * @since   PHP 4.1
 */
class BranchNavigation
{

    // set start parent node
    var $startNode  = 1;

    // always show navigation from start node
    var $alwaysShow =  1;

    function init($output, $block_id)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        return $this->getBlockContent($output);
    }

    function getBlockContent(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        require_once SGL_MOD_DIR . '/navigation/classes/SimpleNav.php';

        $nav       = &new SimpleNav($output);
        $aSections = $nav->getSectionsByRoleId($this->startNode);

        if (!$this->alwaysShow && empty($nav->_currentSectionId)) {
            return false;
        }

        if ($aSections) {
            return $nav->_toHtml($aSections);
        }
        return false;
    }
}
?>