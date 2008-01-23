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
// | Akismet.php                                                               |
// +---------------------------------------------------------------------------+
// | Author: Steven Stremciuc  <steve@freeslacker.net>                         |
// +---------------------------------------------------------------------------+

require_once 'HTTP/Request.php';

class Akismet
{
    var $restUrl = 'rest.akismet.com';
    var $akismetVersion = '1.1';
    var $pluginName = 'Akismet';
    var $pluginVersion = '0.1';

    function reportHam($oComment, $key)
    {
        $request = & new HTTP_Request('http://' . $key . '.' . $this->restUrl . '/' . $this->akismetVersion . '/submit-ham');
        $request->setHttpVer('1.0');
        $request->setMethod(HTTP_REQUEST_METHOD_POST);
        $request->addHeader('user-agent', 'Seagull Framework/' . SGL_SEAGULL_VERSION . ' | ' . $this->pluginName . '/' . $this->pluginVersion);
        $request->addPostData('blog', SGL_BASE_URL);
        $request->addPostData('user_ip', $_SERVER['REMOTE_ADDR']);
        $request->addPostData('user_agent', $_SERVER['HTTP_USER_AGENT'], true);
        $request->addPostData('referrer', $_SERVER['HTTP_REFERER']);
        $request->addPostData('comment_author', $oComment->full_name);
        $request->addPostData('comment_author_email', $oComment->email);
        $request->addPostData('comment_author_url', $oComment->url);
        $request->addPostData('comment_content', $oComment->body);

        if (!PEAR::isError($request->sendRequest())) {
            $response = $request->getResponseBody();
        } else {
            $response = '';
        }

        return ($response == '1') ? true : false;
    }

    function reportSpam($oComment, $key)
    {
        $request = & new HTTP_Request('http://' . $key . '.' . $this->restUrl . '/' . $this->akismetVersion . '/submit-spam');
        $request->setHttpVer('1.0');
        $request->setMethod(HTTP_REQUEST_METHOD_POST);
        $request->addHeader('user-agent', 'Seagull Framework/' . SGL_SEAGULL_VERSION . ' | ' . $this->pluginName . '/' . $this->pluginVersion);
        $request->addPostData('blog', SGL_BASE_URL);
        $request->addPostData('user_ip', $_SERVER['REMOTE_ADDR']);
        $request->addPostData('user_agent', $_SERVER['HTTP_USER_AGENT'], true);
        $request->addPostData('referrer', $_SERVER['HTTP_REFERER']);
        $request->addPostData('comment_author', $oComment->full_name);
        $request->addPostData('comment_author_email', $oComment->email);
        $request->addPostData('comment_author_url', $oComment->url);
        $request->addPostData('comment_content', $oComment->body);

        if (!PEAR::isError($request->sendRequest())) {
            $response = $request->getResponseBody();
        } else {
            $response = '';
        }

        return ($response == '1') ? true : false;
    }

    function isSpam($oComment, $key)
    {
        $request = & new HTTP_Request('http://' . $key . '.' . $this->restUrl . '/' . $this->akismetVersion . '/comment-check');
        $request->setHttpVer('1.0');
        $request->setMethod(HTTP_REQUEST_METHOD_POST);
        $request->addHeader('user-agent', 'Seagull Framework/' . SGL_SEAGULL_VERSION . ' | ' . $this->pluginName . '/' . $this->pluginVersion);
        $request->addPostData('blog', SGL_BASE_URL);
        $request->addPostData('user_ip', $_SERVER['REMOTE_ADDR']);
        $request->addPostData('user_agent', $_SERVER['HTTP_USER_AGENT'], true);
        $request->addPostData('referrer', $_SERVER['HTTP_REFERER']);
        $request->addPostData('comment_author', $oComment->full_name);
        $request->addPostData('comment_author_email', $oComment->email);
        $request->addPostData('comment_author_url', $oComment->url);
        $request->addPostData('comment_content', $oComment->body);

        if (!PEAR::isError($request->sendRequest())) {
            $response = $request->getResponseBody();
        } else {
            $response = '';
        }

        return ($response == 'true') ? true : false;
    }

    function verifyKey($key = '')
    {
        $request = & new HTTP_Request('http://' . $this->restUrl . '/' . $this->akismetVersion . '/verify-key');
        $request->setHttpVer('1.0');
        $request->setMethod(HTTP_REQUEST_METHOD_POST);
        $request->addHeader('user-agent', 'Seagull Framework/' . SGL_SEAGULL_VERSION . ' | ' . $this->pluginName . '/' . $this->pluginVersion);
        $request->addPostData('key', $key);
        $request->addPostData('blog', SGL_BASE_URL);

        if (!PEAR::isError($request->sendRequest())) {
            $response = $request->getResponseBody();
        } else {
            $response = '';
        }
        
        return ($response == 'valid') ? true : false;
    }
}
?>
