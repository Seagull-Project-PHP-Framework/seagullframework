/*
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2005 Frederico Caldeira Knabben
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *      http://www.opensource.org/licenses/lgpl-license.php
 *
 * For further information visit:
 *      http://www.fckeditor.net/
 *
 * "Support Open Source software. What about a donation today?"
 *
 * File Name: fckconfig.js
 *  Editor configuration settings.
 *  See the documentation for more info.
 *
 * File Authors:
 *      Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

FCKConfig.CustomConfigurationsPath = '' ;

FCKConfig.EditorAreaCSS = FCKConfig.BasePath + 'css/fck_editorarea.css' ;

FCKConfig.BaseHref = '' ;



FCKConfig.AutoDetectLanguage    = true ;
FCKConfig.DefaultLanguage   = 'en' ;
FCKConfig.ContentLangDirection  = 'ltr' ;


FCKConfig.TabSpaces     = 4 ;
/*
This is the original config. But we'd like to clean it up a little bit.

FCKConfig.ToolbarSets["Default"] = [
    ['Source','DocProps','-','Save','NewPage','Preview','-','Templates'],
    ['Cut','Copy','Paste','PasteText','PasteWord','-','Print','SpellCheck'],
    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
    ['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
    ['OrderedList','UnorderedList','-','Outdent','Indent'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
    ['Link','Unlink','Anchor'],
    ['Image','Flash','Table','Rule','Smiley','SpecialChar','PageBreak','UniversalKey'],
    ['Form','Checkbox','Radio','TextField','Textarea','Select','Button','ImageButton','HiddenField'],
    '/',
    ['Style','FontFormat','FontName','FontSize'],
    ['TextColor','BGColor'],
    ['About']
] ;
*/

FCKConfig.ToolbarSets["Default"] = [
        ['Source'],
        ['Cut','Copy','Paste','PasteText','PasteWord'],
        ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
        ['Bold','Italic','Underline','StrikeThrough'],
        '/',
        ['OrderedList','UnorderedList','-','Outdent','Indent'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
        ['Link','Unlink','Anchor'],
        ['Image','Smiley','SpecialChar','UniversalKey'],
        '/',
        ['Style','FontFormat','FontName','FontSize'],
        ['TextColor','BGColor'],
] ;

FCKConfig.ToolbarSets["Basic"] = [
    ['Bold','Italic','-','OrderedList','UnorderedList','-','Link','Unlink','-','About']
] ;

FCKConfig.ContextMenu = ['Generic','Link','Anchor','Image','Flash','Select','Textarea','Checkbox','Radio','TextField','HiddenField','ImageButton','Button','BulletedList','NumberedList','TableCell','Table','Form'] ;

FCKConfig.FontNames     = 'Arial;Comic Sans MS;Courier New;Tahoma;Times New Roman;Verdana' ;
FCKConfig.FontSizes     = '1/xx-small;2/x-small;3/small;4/medium;5/large;6/x-large;7/xx-large' ;
FCKConfig.FontFormats   = 'p;div;pre;address;h1;h2;h3;h4;h5;h6' ;

FCKConfig.StylesXmlPath     = FCKConfig.EditorPath + 'fckstyles.xml' ;
FCKConfig.TemplatesXmlPath  = FCKConfig.EditorPath + 'fcktemplates.xml' ;

FCKConfig.SpellChecker          = 'ieSpell' ;   // 'ieSpell' | 'SpellerPages'
FCKConfig.IeSpellDownloadUrl    = 'http://www.iespell.com/rel/ieSpellSetup211325.exe' ;

FCKConfig.MaxUndoLevels = 15 ;

FCKConfig.DisableImageHandles = false ;
FCKConfig.DisableTableHandles = false ;

FCKConfig.LinkDlgHideTarget     = false ;
FCKConfig.LinkDlgHideAdvanced   = false ;

FCKConfig.ImageDlgHideLink      = false ;
FCKConfig.ImageDlgHideAdvanced  = false ;

FCKConfig.FlashDlgHideAdvanced  = false ;

FCKConfig.LinkBrowser = true ;
FCKConfig.LinkBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Connector=connectors/' + 'php' + '/connector.' + 'php' ;
FCKConfig.LinkBrowserWindowWidth    = FCKConfig.ScreenWidth * 0.7 ;     // 70%
FCKConfig.LinkBrowserWindowHeight   = FCKConfig.ScreenHeight * 0.7 ;    // 70%

FCKConfig.ImageBrowser = true ;
FCKConfig.ImageBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Image&Connector=connectors/' + 'php' + '/connector.' + 'php' ;
FCKConfig.ImageBrowserWindowWidth  = FCKConfig.ScreenWidth * 0.7 ;  // 70% ;
FCKConfig.ImageBrowserWindowHeight = FCKConfig.ScreenHeight * 0.7 ; // 70% ;

FCKConfig.FlashBrowser = true ;
FCKConfig.FlashBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Flash&Connector=connectors/' + 'php' + '/connector.' + 'php' ;
FCKConfig.FlashBrowserWindowWidth  = FCKConfig.ScreenWidth * 0.7 ;  //70% ;
FCKConfig.FlashBrowserWindowHeight = FCKConfig.ScreenHeight * 0.7 ; //70% ;

FCKConfig.LinkUpload = true ;
FCKConfig.LinkUploadURL = FCKConfig.BasePath + 'filemanager/upload/' + 'php' + '/upload.' + 'php' ;
FCKConfig.LinkUploadAllowedExtensions   = "" ;          // empty for all
FCKConfig.LinkUploadDeniedExtensions    = ".(php|php3|php5|phtml|asp|aspx|ascx|jsp|cfm|cfc|pl|bat|exe|dll|reg|cgi)$" ;  // empty for no one

FCKConfig.ImageUpload = true ;
FCKConfig.ImageUploadURL = FCKConfig.BasePath + 'filemanager/upload/' + 'php' + '/upload.' + 'php' + '?Type=Image' ;
FCKConfig.ImageUploadAllowedExtensions  = ".(jpg|gif|jpeg|png)$" ;      // empty for all
FCKConfig.ImageUploadDeniedExtensions   = "" ;                          // empty for no one

FCKConfig.FlashUpload = true ;
FCKConfig.FlashUploadURL = FCKConfig.BasePath + 'filemanager/upload/' + 'php' + '/upload.' + 'php' + '?Type=Flash' ;
FCKConfig.FlashUploadAllowedExtensions  = ".(swf|fla)$" ;       // empty for all
FCKConfig.FlashUploadDeniedExtensions   = "" ;                  // empty for no one
/*
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2005 Frederico Caldeira Knabben
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *      http://www.opensource.org/licenses/lgpl-license.php
 *
 * For further information visit:
 *      http://www.fckeditor.net/
 *
 * "Support Open Source software. What about a donation today?"
 *
 * File Name: fckconfig.js
 *  Editor configuration settings.
 *  See the documentation for more info.
 *
 * File Authors:
 *      Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

FCKConfig.CustomConfigurationsPath = '' ;

FCKConfig.EditorAreaCSS = FCKConfig.BasePath + 'css/fck_editorarea.css' ;

FCKConfig.BaseHref = '' ;



FCKConfig.AutoDetectLanguage    = true ;
FCKConfig.DefaultLanguage   = 'en' ;
FCKConfig.ContentLangDirection  = 'ltr' ;


FCKConfig.TabSpaces     = 4 ;
/*
This is the original config. But we'd like to clean it up a little bit.

FCKConfig.ToolbarSets["Default"] = [
    ['Source','DocProps','-','Save','NewPage','Preview','-','Templates'],
    ['Cut','Copy','Paste','PasteText','PasteWord','-','Print','SpellCheck'],
    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
    ['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
    ['OrderedList','UnorderedList','-','Outdent','Indent'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
    ['Link','Unlink','Anchor'],
    ['Image','Flash','Table','Rule','Smiley','SpecialChar','PageBreak','UniversalKey'],
    ['Form','Checkbox','Radio','TextField','Textarea','Select','Button','ImageButton','HiddenField'],
    '/',
    ['Style','FontFormat','FontName','FontSize'],
    ['TextColor','BGColor'],
    ['About']
] ;
*/

FCKConfig.ToolbarSets["Default"] = [
        ['Source'],
        ['Cut','Copy','Paste','PasteText','PasteWord'],
        ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
        ['Bold','Italic','Underline','StrikeThrough'],
        '/',
        ['OrderedList','UnorderedList','-','Outdent','Indent'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
        ['Link','Unlink','Anchor'],
        ['Image','Smiley','SpecialChar','UniversalKey'],
        '/',
        ['Style','FontFormat','FontName','FontSize'],
        ['TextColor','BGColor'],
] ;

FCKConfig.ToolbarSets["Basic"] = [
    ['Bold','Italic','-','OrderedList','UnorderedList','-','Link','Unlink','-','About']
] ;

FCKConfig.ContextMenu = ['Generic','Link','Anchor','Image','Flash','Select','Textarea','Checkbox','Radio','TextField','HiddenField','ImageButton','Button','BulletedList','NumberedList','TableCell','Table','Form'] ;

FCKConfig.FontNames     = 'Arial;Comic Sans MS;Courier New;Tahoma;Times New Roman;Verdana' ;
FCKConfig.FontSizes     = '1/xx-small;2/x-small;3/small;4/medium;5/large;6/x-large;7/xx-large' ;
FCKConfig.FontFormats   = 'p;div;pre;address;h1;h2;h3;h4;h5;h6' ;

FCKConfig.StylesXmlPath     = FCKConfig.EditorPath + 'fckstyles.xml' ;
FCKConfig.TemplatesXmlPath  = FCKConfig.EditorPath + 'fcktemplates.xml' ;

FCKConfig.SpellChecker          = 'ieSpell' ;   // 'ieSpell' | 'SpellerPages'
FCKConfig.IeSpellDownloadUrl    = 'http://www.iespell.com/rel/ieSpellSetup211325.exe' ;

FCKConfig.MaxUndoLevels = 15 ;

FCKConfig.DisableImageHandles = false ;
FCKConfig.DisableTableHandles = false ;

FCKConfig.LinkDlgHideTarget     = false ;
FCKConfig.LinkDlgHideAdvanced   = false ;

FCKConfig.ImageDlgHideLink      = false ;
FCKConfig.ImageDlgHideAdvanced  = false ;

FCKConfig.FlashDlgHideAdvanced  = false ;

FCKConfig.LinkBrowser = true ;
FCKConfig.LinkBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Connector=connectors/' + 'php' + '/connector.' + 'php' ;
FCKConfig.LinkBrowserWindowWidth    = FCKConfig.ScreenWidth * 0.7 ;     // 70%
FCKConfig.LinkBrowserWindowHeight   = FCKConfig.ScreenHeight * 0.7 ;    // 70%

FCKConfig.ImageBrowser = true ;
FCKConfig.ImageBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Image&Connector=connectors/' + 'php' + '/connector.' + 'php' ;
FCKConfig.ImageBrowserWindowWidth  = FCKConfig.ScreenWidth * 0.7 ;  // 70% ;
FCKConfig.ImageBrowserWindowHeight = FCKConfig.ScreenHeight * 0.7 ; // 70% ;

FCKConfig.FlashBrowser = true ;
FCKConfig.FlashBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Flash&Connector=connectors/' + 'php' + '/connector.' + 'php' ;
FCKConfig.FlashBrowserWindowWidth  = FCKConfig.ScreenWidth * 0.7 ;  //70% ;
FCKConfig.FlashBrowserWindowHeight = FCKConfig.ScreenHeight * 0.7 ; //70% ;

FCKConfig.LinkUpload = true ;
FCKConfig.LinkUploadURL = FCKConfig.BasePath + 'filemanager/upload/' + 'php' + '/upload.' + 'php' ;
FCKConfig.LinkUploadAllowedExtensions   = "" ;          // empty for all
FCKConfig.LinkUploadDeniedExtensions    = ".(php|php3|php5|phtml|asp|aspx|ascx|jsp|cfm|cfc|pl|bat|exe|dll|reg|cgi)$" ;  // empty for no one

FCKConfig.ImageUpload = true ;
FCKConfig.ImageUploadURL = FCKConfig.BasePath + 'filemanager/upload/' + 'php' + '/upload.' + 'php' + '?Type=Image' ;
FCKConfig.ImageUploadAllowedExtensions  = ".(jpg|gif|jpeg|png)$" ;      // empty for all
FCKConfig.ImageUploadDeniedExtensions   = "" ;                          // empty for no one

FCKConfig.FlashUpload = true ;
FCKConfig.FlashUploadURL = FCKConfig.BasePath + 'filemanager/upload/' + 'php' + '/upload.' + 'php' + '?Type=Flash' ;
FCKConfig.FlashUploadAllowedExtensions  = ".(swf|fla)$" ;       // empty for all
FCKConfig.FlashUploadDeniedExtensions   = "" ;                  // empty for no one
