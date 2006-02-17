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
// | Install.php                                                               |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+

require_once dirname(__FILE__)  . '/../Misc.php';

if (!isset($GLOBALS['_SGL'])) {
    $GLOBALS['_SGL'] = array();
    $_SESSION['ERRORS'] = array();
}

/**
 * Provides various static methods required for install routine.
 *
 */
class SGL_Install_Common
{
    function errorPush($error)
    {
        array_push($_SESSION['ERRORS'], $error);
    }

    function errorCheck(&$page)
    {
        if (SGL_Install_Common::errorsExist()) {
            foreach ($_SESSION['ERRORS'] as $oError) {
                $out =  $oError->getMessage() . '<br /> ';
                $out .= $oError->getUserInfo();
                $page->addElement('static',   'errors', 'Errors:', $out);
            }
            $_SESSION['ERRORS'] = array();
        }
    }

    function errorsExist()
    {
        return @count($_SESSION['ERRORS']);
    }

    function errorPrint()
    {
        foreach ($_SESSION['ERRORS'] as $oError) {
            $msg = SGL_Error::toString($oError);
            if (stristr($msg, "%e")) {
                $msg = str_replace("%e", SGL_VAR_DIR, $msg);
            }
            $html ='<div class="errorContainer">
                        <div class="errorHeader">Error</div>
                        <div class="errorContent">' . $msg . '</div>
                    </div>';
            print $html;
        }
    }

    /**
     * Returns a string indicating the framework version.
     *
     * @return string
     */
    function getFrameworkVersion()
    {
        $version = file_get_contents(SGL_PATH . '/VERSION.txt');
        return $version;
    }


    /**
     * Returns html head section of page, only used for 'enter passwd for
     * access setup' screen
     *
     * @param string $title
     *
     * @see QuickFormOverride.php for header html used in QuickForm install wizard
     */
    function printHeader($title = '')
    {
        if (SGL::runningFromCli()) {
            return false;
        }
        $baseUrl = SGL_BASE_URL;
        $html = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title>Seagull Framework :: Installation</title>        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15" />
    <meta http-equiv="Content-Language" content="en" />
    <meta name="ROBOTS" content="ALL" />
    <meta name="Copyright" content="Copyright (c) 2005 Seagull Framework, Demian Turner, and the respective authors" />
    <meta name="Rating" content="General" />
    <meta name="Generator" content="Seagull Framework" />

    <link rel="help" href="http://seagull.phpkitchen.com/docs/" title="Seagull Documentation." />

    <style type="text/css" media="screen">
        @import url("$baseUrl/themes/default/css/style.php?navStylesheet=SglDefault_TwoLevel");
    </style>
</head>
<body>

<div id="sgl">
<!-- Logo and header -->
<div id="header">
    <a id="logo" href="$baseUrl" title="Home">
        <img src="$baseUrl/themes/default/images/logo.gif" align="absmiddle" alt="Seagull Framework Logo" /> Seagull Framework :: Installation
    </a>
</div>
<h2>$title</h2>
HTML;
        print $html;
    }

    function printFooter()
    {
        if (SGL::runningFromCli()) {
            return false;
        }
        $html = <<<HTML
    <div id="footer">
    Powered by <a href="http://seagull.phpkitchen.com" title="Seagull framework homepage">Seagull Framework</a>
    </div>
</body>
</html>
HTML;
        print $html;
    }

    function printLoginForm()
    {
        $message = !empty($_SESSION['message']) ? $_SESSION['message'] : '';
        $_SESSION = array();

        $html = <<<HTML
<form name="frmLogin" method="post" action="" id="frmLogin">
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <div class="messageContainer">
        <div class="messageHeader">
            Authorisation Required
        </div>
        <div class="messageContent">
            <div>
                <div class="error">
                    $message
                </div>
                <span class="error">*&nbsp;</span>Password
                <input type="password" name="frmPassword" maxlength="24" />
            </div>
            <p><input type="submit" class="formsubmit" name="submitted" value="Enter" /></p>
        </div>
    </div>
</form>
HTML;
        print $html;
    }

    /**
     * Returns an array of modules scanned from filesystem.
     *
     * @return array
     */
    function getModuleList()
    {
        $dir =  SGL_MOD_DIR;
        $fileList = array();
        $stack[] = $dir;
        while ($stack) {
            $currentDir = array_pop($stack);
            if ($dh = opendir($currentDir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file !== '.' && $file !== '..' && $file !== '.svn') {
                        $currentFile = "{$currentDir}/{$file}";
                        if (is_dir($currentFile)) {
                            $fileList[] = "{$file}";
                        }
                    }
               }
           }
       }
       sort($fileList);
       return $fileList;
    }
}

if (!(function_exists('file_put_contents'))) {
    function file_put_contents($location, $data)
    {
        if (is_file($location)) {
            unlink($location);
        }
        $fileHandler = fopen ($location, "w");
        fwrite ($fileHandler, $data);
        fclose ($fileHandler);
        return true;
    }
}
?>