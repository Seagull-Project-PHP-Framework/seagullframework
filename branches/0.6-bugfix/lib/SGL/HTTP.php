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
// | HTTP.php                                                                  |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+

/**
 * Provides HTTP redirects.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_HTTP
{
    /**
     * Wrapper for PHP header() redirects.
     *
     * Simplified version of Wolfram's HTTP_Header class
     *
     * @access  public
     * @static
     * @param   mixed   $url    target URL
     * @return  void
     * @author  Wolfram Kriesing <wk@visionp.de>
     */
    function redirect($url = '')
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        //  if arg is not an array of params, pass straight to header function
        if (is_scalar($url) && strlen($url)) {

            //  add a trailing slash if one is not present for uris passed as strings
            if (substr($url, -1) != '/') {
                $url .= '/';
            }
            if (is_callable(array($conf['site']['outputUrlHandler'], 'makeLinkFromString'))) {
               $outputUrlHandler = new $conf['site']['outputUrlHandler'];
               $url = $outputUrlHandler->makeLinkFromString($url);
            }
        } else {
            //  get a reference to the request object
            $req = & SGL_Request::singleton();

            if (is_scalar($url)) {
                $url = array();
            }

            $moduleName  =  (array_key_exists('moduleName', $url))
                ? $url['moduleName']
                : $req->get('moduleName');
            $managerName =  (array_key_exists('managerName', $url))
                ? $url['managerName']
                : $req->get('managerName');

            //  parse out rest of querystring
            $aParams = array();
            foreach ($url as $k => $v) {
                if ($k == 'moduleName' || $k == 'managerName') {
                    continue;
                }
                if (is_string($k)) {
                    $aParams[] = urlencode($k).'/'.urlencode($v);
                }
            }
            $qs = (count($aParams)) ? implode('/', $aParams): '';
            $url = ($conf['site']['frontScriptName'])
                ? $conf['site']['frontScriptName'] . '/' . $moduleName
                : $moduleName;

            if (!empty($managerName)) {
                $url .=  '/' . $managerName;
            }
            $url .= '/' . $qs;

            if (is_callable(array($conf['site']['outputUrlHandler'], 'makeLinkFromString'))) {
               $outputUrlHandler = new $conf['site']['outputUrlHandler'];
               $url = $outputUrlHandler->makeLinkFromString($url);
            }
            //  check for absolute uri as specified in RFC 2616
            SGL_Url::toAbsolute($url);

            //  add a slash if one is not present
            if (substr($url, -1) != '/') {
                $url .= '/';
            }
            //  determine is session propagated in cookies or URL
            SGL_Url::addSessionInfo($url);
        }
        //  must be absolute URL, ie, string
        header('Location: ' . $url);
        exit;
    }
}
?>
