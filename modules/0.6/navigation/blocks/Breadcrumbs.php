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
// | Seagull 0.6.5                                                             |
// +---------------------------------------------------------------------------+
// | Breadcrumbs.php                                                           |
// +---------------------------------------------------------------------------+
// | Author: Andrey Podshivalov <planetaz@gmail.com>                           |
// +---------------------------------------------------------------------------+

/**
 * Breadcrumbs block.
 *
 * @package block
 */
class Navigation_Block_Breadcrumbs
{
    var $template     = 'Breadcrumbs.html';
    var $templatePath = 'navigation';

    function init(&$output, $block_id, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        return $this->getBlockContent($output, $aParams);
    }

    function getBlockContent(&$output, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $outputClassName      = SGL_Config::get('site.customOutputClassName')
            ? SGL_Config::get('site.customOutputClassName')
            : 'SGL_Output';
        $blockOutput          = &new $outputClassName();
        $blockOutput->theme   = $output->theme;
        $blockOutput->webRoot = $output->webRoot;

        //  prepare navigation driver
        $navDriver = $output->conf['navigation']['driver'];
        $nav       = & new $navDriver($output);

        //  set default params
        $aDefaultParams = array(
                'startParentNode' => 0,
                'startLevel'      => 0,
                'levelsToRender'  => 1,
                'collapsed'       => 1,
                'showAlways'      => 0,
                'breadcrumbs'     => 1,
        );

        //  set custom params
        if (array_key_exists('startParentNode', $aParams)) {
            $aDefaultParams['startParentNode'] = (int)$aParams['startParentNode'];
        }
        if (array_key_exists('template', $aParams)) {
            $this->template = $aParams['template'];
        }

        //  set new navigation driver params
        $nav->setParams($aDefaultParams);

        //  call navigation renderer
        $aNav = $nav->render(null);
        $blockOutput->breadcrumbs = $aNav[2];

        return $this->process($blockOutput);
    }

    function process(&$output)
    {
        // use moduleName for template path setting
        $output->moduleName     = $this->templatePath;
        $output->masterTemplate = $this->template;

        $view = new SGL_HtmlSimpleView($output);
        return $view->render();
    }
}
?>