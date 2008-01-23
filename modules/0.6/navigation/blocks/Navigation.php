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
// | Seagull 0.6.0                                                             |
// +---------------------------------------------------------------------------+
// | Navigation.php                                                            |
// +---------------------------------------------------------------------------+
// | Author: Andrey Podshivalov <planetaz@gmail.com>                           |
// +---------------------------------------------------------------------------+

/**
 * Section Navigation block.
 *
 * @package block
 */
class Navigation_Block_Navigation
{
    function init(&$output, $block_id, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        return $this->getBlockContent($output, $aParams);
    }

    function getBlockContent(&$output, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  prepare navigation driver

        $navDriver = isset($output->conf['navigation']['driver'])
            ? $output->conf['navigation']['driver']
            : 'SimpleDriver';
        $navDrvFile   = SGL_MOD_DIR . '/navigation/classes/' . $navDriver . '.php';
        if (is_file($navDrvFile)) {
            require_once $navDrvFile;
        } else {
            SGL::raiseError('specified navigation driver does not exist',
                SGL_ERROR_NOFILE);
        }
        if (!class_exists($navDriver)) {
            SGL::raiseError('problem with navigation driver object',
                SGL_ERROR_NOCLASS);
        }
        $nav = & new $navDriver($output);

        //  set default params
        $aDefaultParams = array(
            'startParentNode' => 0,
            'startLevel'      => 0,
            'levelsToRender'  => 0,
            'collapsed'       => 0,
            'showAlways'      => 0,
            'cacheEnabled'    => 1,
            'breadcrumbs'     => 0,
        );

        //  set custom params
        foreach ($aParams as $key => $value) {
            $aDefaultParams[$key] = $value;
        }
        //  set new navigation driver params
        $nav->setParams($aDefaultParams);

        //  call navigation renderer
        $aNav = $nav->render($aParams['renderer']);

        //  return $aNav[1] - return rendered navigation menu
        //  return $aNav[2] - return breadcrumbs
        return $aNav[1];
    }
}
?>