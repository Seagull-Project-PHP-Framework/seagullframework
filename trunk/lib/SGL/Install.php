<?php
$GLOBALS['_SGL'] = array();
$_SESSION['ERRORS'] = array();

class SGL_Install
{   
    function errorPush($error)
    {
        array_push($_SESSION['ERRORS'], $error);
    }
    
    function errorCheck(&$page)
    {
        if (SGL_Install::errorsExist()) {
            foreach ($_SESSION['ERRORS'] as $oError) {
                $out =  $oError->getMessage() . '<br /> ';   
                $out .= $oError->getUserInfo();   
                $page->addElement('static',   'errors', 'Errors:', $out);                
            }
            $_SESSION['ERRORS'] = array();
        }
    }
    
    function getFrameworkVersion()
    {
        $version = file_get_contents(SGL_Install::getInstallRoot() . '/VERSION.txt');
        return $version;
    }
    
    function errorsExist()
    {
        return count($_SESSION['ERRORS']);
    }
    
    function getInstallRoot()
    {
        return dirname(dirname((dirname(__FILE__))));
    }
    
    function printHeader($title)
    {
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
        @import url("http://localhost/seagull/trunk/www/themes/default/css/style.php?navStylesheet=SglDefault_TwoLevel&moduleName=faq");
    </style>
    </head>
<body>

<div id="sgl">
<!-- Logo and header -->
<div id="header">
    <a id="logo" href="http://localhost/seagull/trunk/www" title="Home">
        <img src="http://localhost/seagull/trunk/www/themes/default/images/logo.gif" align="absmiddle" alt="Seagull Framework Logo" /> Seagull Framework :: Installation
    </a>
</div>
<h2>$title</h2>

HTML;
        print $html;
    }
    
    function printFooter()
    {
        $html = <<<HTML
    <div id="footer">
    Powered by <a href="http://seagull.phpkitchen.com" title="Seagull framework homepage">Seagull Framework</a>  
    </div>
</body>
</html>
HTML;
        print $html;
    }
}

if (!(function_exists('file_put_contents'))) {
    function file_put_contents($location, $data)
    {
        if (file_exists($location)) {
            unlink($location);
        }
        $fileHandler = fopen ($location, "w");
        fwrite ($fileHandler, $data);
        fclose ($fileHandler);
        return true;
    }
}
?>