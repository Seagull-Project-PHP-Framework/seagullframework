<?php

require_once 'HTML/QuickForm/Action/Display.php';

//  subclass the default 'display' handler to customize the output
class ActionDisplay extends HTML_QuickForm_Action_Display
{
    function perform(&$page, $actionName)
    {
        SGL_Install_Common::errorCheck($page);
        return parent::perform($page, $actionName);
    }

    function _renderForm(&$page)
    {
        $renderer =& $page->defaultRenderer();
        $baseUrl = SGL_BASE_URL;
        $renderer->setElementTemplate("\n\t<tr>\n\t\t<td align=\"right\" valign=\"top\" colspan=\"2\">{element}</td>\n\t</tr>", 'tabs');
        $renderer->setFormTemplate(<<<_HTML
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
    <script type="text/javascript">
        function disableLangList()
        {
            var foo = document.getElementById('installLangs');
            var bar = document.getElementById('addMissingTranslationsToDB');
            if (foo != null) {
                foo.disabled = true;
                bar.disabled = true;
            }

            //  temp measure
            var prefix = document.getElementById('prefix');
            prefix.disabled = true;

            //  disable 'use existing data' by default
            var useExistingData = document.getElementById('useExistingData');
            useExistingData.disabled = true;
        }

        function toggleLangList(myCheckbox)
        {
            var myCheckbox = document.getElementById('storeTranslationsInDB').checked;
            var langsList = document.getElementById('installLangs');
            var addLangsToDb = document.getElementById('addMissingTranslationsToDB');

            if (myCheckbox != null) {
                if (myCheckbox) {
                    langsList.disabled = false;
                    addLangsToDb.disabled = false;
                } else {
                    langsList.disabled = true;
                    addLangsToDb.disabled = true;
                }
            }
        }

        function toggleOptionsWhenUsingExistingDb(myCheckbox)
        {
            var myCheckbox = document.getElementById('useExistingData').checked;
            var allModules = document.getElementById('installAllModules');
            var sampleData = document.getElementById('insertSampleData');
            var storeTransInDb = document.getElementById('storeTranslationsInDB')

            if (myCheckbox != null) {
                if (myCheckbox) {
                    allModules.disabled = true;
                    sampleData.disabled = true;
                    storeTransInDb.disabled = true;
                } else {
                    allModules.disabled = false;
                    sampleData.disabled = false;
                    storeTransInDb.disabled = false;
                }
            }
        }

        function toggleExistingData(myCheckbox)
        {
            var myCheckbox = document.getElementById('skipDbCreation').checked;
            var useExistingData = document.getElementById('useExistingData');

            if (myCheckbox != null) {
                if (myCheckbox) {
                    useExistingData.disabled = false;
                } else {
                    useExistingData.disabled = true;
                }
            }
        }
    </script>
</head>
<body onLoad="javascript:disableLangList(true)">

<div id="sgl">
<!-- Logo and header -->
<div id="header">
    <a id="logo" href="$baseUrl" title="Home">
        <img src="$baseUrl/themes/default/images/logo.gif" align="absmiddle" alt="Seagull Framework Logo" /> Seagull Framework :: Installation
    </a>
</div>
<p>&nbsp;</p>
<form{attributes}>
<table border="0" width="800px">
{content}
</table>
</form>
    <div id="footer">
    Powered by <a href="http://seagull.phpkitchen.com" title="Seagull framework homepage">Seagull Framework</a>
    </div>
</body>
</html>
_HTML
);
        $page->display();
    }
}
?>