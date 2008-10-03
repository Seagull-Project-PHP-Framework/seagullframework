<?php

require_once 'HTML/QuickForm/Action/Display.php';


/**
 * Subclass the default 'display' handler to customize the output
 *
 * @package Install
 */
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
    <title>Seagull Framework :: Installation</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15" />
    <meta http-equiv="Content-Language" content="en" />
    <meta name="ROBOTS" content="ALL" />
    <meta name="Copyright" content="Copyright (c) 2006 Seagull Framework, Demian Turner, and the respective authors" />
    <meta name="Rating" content="General" />
    <meta name="Generator" content="Seagull Framework" />
    <link rel="help" href="http://trac.seagullproject.org/" title="Seagull Documentation." />
    <link rel="stylesheet" type="text/css" media="screen" href="$baseUrl/themes/default/css/installer.php" />

    <script type="text/javascript">

        function init()
        {
            //  disable 'use existing data' by default
            var useExistingData = document.getElementById('useExistingData');
            if (useExistingData != null) {
                useExistingData.disabled = true;
            }

            //  toggle lang list disabled by default
            if (useExistingData != null) {
                toggleLangList();
            }

            //  disable dbLoginName for mysql default
            var dbLoginName = document.getElementById('dbLoginNameElement');
            if (dbLoginName != null) {
                dbLoginName.disabled = true;
            }

            //  toggle translation setup
            var dbStorageSelected = document.getElementById('storeTranslationsInDB');
            if (dbStorageSelected != null && dbStorageSelected.checked) {
                document.getElementById('moreOptionsLink').innerHTML = 'Hide';
                document.getElementById('moreOptionsContainer').style.display = 'block';
            }
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
            var sampleData = document.getElementById('insertSampleData');
            var storeTransInDb = document.getElementById('storeTranslationsInDB')

            if (myCheckbox != null) {
                if (myCheckbox) {
                    sampleData.disabled = true;
                    storeTransInDb.disabled = true;
                } else {
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

        function copyValueToPortElement(elem)
        {
            var portElement = document.getElementById('targetPortElement');
            portElement.value = elem.value;
        }

        function toggleDbNameForLogin(enable)
        {
            var dbLoginName = document.getElementById('dbLoginNameElement');
            if (enable) {
                dbLoginName.value = '';
                dbLoginName.disabled = false;
            } else {
                dbLoginName.value = 'not required for MySQL login';
                dbLoginName.disabled = true;
            }
        }

        function toggleDefaultStorageEngine(enable)
        {
            var engine = document.getElementById('defaultStorageEngine');
            if (enable) {
                engine.disabled = false;
            } else {
                engine.selectedIndex = 0;
                engine.disabled = true;
            }
        }

        function toggleMoreOptions(containerName, oTrigger)
        {
            var elem = document.getElementById(containerName);
            if (elem.style.display == 'none') {
                elem.style.display = 'block';
                oTrigger.innerHTML = 'Hide';
            } else {
                elem.style.display = 'none';
                oTrigger.innerHTML = 'Show';
            }
        }
    </script>
</head>
<body onLoad="javascript:init();" id="content">

<div id="sgl">
<!-- Logo and header -->
<div id="header">
    <a id="logo" href="$baseUrl" title="Home">
        <img src="$baseUrl/themes/default/images/logo.png" align="absmiddle" alt="Seagull Framework Logo" />
    </a>
</div>
<p>&nbsp;</p>
<form{attributes}>
<table border="0" width="800px">
{content}
</table>
</form>
    <div id="footer">
    Powered by <a href="http://seagullproject.org/" title="Seagull framework homepage">Seagull Framework</a>
    </div>
</div>
</body>
</html>
_HTML
);
        $page->display();
    }
}
?>
