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
// | UserAjaxProvider.php                                                      |
// +---------------------------------------------------------------------------+
// | Author: Dmitri Lakachauskis <lakiboy83@gmail.com>                         |
// +---------------------------------------------------------------------------+

require_once dirname(__FILE__) . '/UserDAO.php';
require_once SGL_CORE_DIR . '/AjaxProvider.php';

/**
 * Simple wrapper to UserDAO to use with AJAX.
 *
 * @package    seagull
 * @subpackage user
 * @author     Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class UserAjaxProvider extends SGL_AjaxProvider
{
    function UserAjaxProvider()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_AjaxProvider();
        $this->responseFormat = SGL_RESPONSEFORMAT_JSON;
        $this->da = &UserDAO::singleton();
    }

    function &singleton()
    {
        static $instance;

        // If the instance is not there, create one
        if (!isset($instance)) {
            $class = __CLASS__;
            $instance = new $class();
        }
        return $instance;
    }

    /**
     * Check if username is unique.
     *
     * @param  string $username
     * @return array
     */
    function isUniqueUsername()
    {
        $req = &SGL_Request::singleton();
        $username = $req->get('username');
        $ok = $this->da->isUniqueUsername($username);
        if ($ok) {
            $msg = 'Selected username is available';
            $ret = array(
                'type'    => 'info',
                'message' => SGL_Output::translate($msg)
            );
        } else {
            $msg = 'This username already exist in the DB, please choose another';
            $ret = array(
                'type'    => 'error',
                'message' => SGL_Output::translate($msg)
            );
        }
        return $ret;
    }
}

?>