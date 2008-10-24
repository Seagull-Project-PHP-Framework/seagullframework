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
// | Seagull 2.0                                                               |
// +---------------------------------------------------------------------------+
// $Id$

/**
 * @todo move to BC plugin
 *
 */
class SGL2_Controller_FrontBc extends SGL2_Controller_Abstract
{
    public function run()
    {
        if (!defined('SGL2_INITIALISED')) {
            $this->init();
        }
        $request  = SGL2_Registry::get('request');
        $response = SGL2_Registry::get('response');

        $router = $this->getRouter();
        $router->route($request);

        $aFilters = array(
            //  pre-process (order: top down)
           'SGL2_Filter_LoadManager',
           'SGL2_Filter_CreateSession',
           'SGL2_Filter_SetupLangSupport',
           'SGL2_Filter_SetupLocale',
           'SGL2_Filter_AuthenticateRequest',

            //  post-process (order: bottom up)
            'SGL2_Filter_BuildHeaders',
            'SGL2_Filter_BuildHtmlView',
//                'SGL2_Filter_SetupGui',
            'SGL2_Filter_BuildOutputData',
        );
        $target = 'SGL2_Controller_MainBc';
        $chain = new SGL2_FilterChain($aFilters);
        $chain->setTarget($target);
        $chain->doFilter($request, $response);
        echo $response;
    }
}
?>