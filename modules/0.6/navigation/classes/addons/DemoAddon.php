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
// | DemoAddon.php                                                             |
// +---------------------------------------------------------------------------+
// | Author:   Andrey Podshivalov <planetaz@gmail.com>                         |
// +---------------------------------------------------------------------------+
// $Id: DemoAddon.php,v 0.1 2006/01/29 23:28:37

/**
 * DemoAddon class
 *
 * Generates additional navigation tree (demo).
 *
 * @package navigation
 * @author  Andrey Podshivalov <planetaz@gmail.com>
 * @version 0.1
 * @access  public
 * @since   PHP 4.4.2
 */

class DemoAddon
{

    function init(&$output, &$section, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        if (!$section->is_enabled) {
            return false;
        }

        $sectionId = $section->section_id;

        //  first node
        $newSection = array(
            'title'        => 'Addon 1.0',
            'section_id'   => $sectionId,
            'resource_uri' => 'contactus/contactus',
            'is_enabled'   => true,
            'trans_id'     => 0, 
        );

        //  children node
        $newSection2 = array(
            'title'        => 'Addon 1.1',
            'section_id'   => $sectionId,
            'resource_uri' => 'uriExternal:http://www.google.com',
            'is_enabled'   => true,
            'children'     => false,
            'trans_id'     => 0,
        );
        
        $newSection['children'][] = (object)$newSection2;
        $aSections[] = (object)$newSection;

        //  second node
        $newSection = array(
            'title'        => 'Addon 2.0',
            'section_id'   => $sectionId,
            'resource_uri' => 'uriNode:1',
            'is_enabled'   => true,
            'children'     => false,
            'trans_id'     => 0,
        );

        $aSections[] = (object)$newSection;
        return $aSections;
    }
}
?>