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
// | MaintenanceMgr.php                                                        |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: MaintenanceMgr.php,v 1.56 2005/05/31 23:34:23 demian Exp $

require_once 'Config.php';

/**
 * Provides tools to manage translations and mtce tasks.
 *
 * @package default
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.56 $
 * @since   PHP 4.1
 */

class MaintenanceMgr extends SGL_Manager
{
    function MaintenanceMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Maintenance';
        $this->template     = 'maintenance.html';
        $this->redirect     = true;
        $this->da           = & DA_Default::singleton();

        $this->_aActionsMapping =  array(
            'verify'    => array('verify', 'redirectToDefault'),
            'edit'      => array('edit'),
            'update'    => array('update', 'redirectToDefault'),
            'append'    => array('append', 'redirectToDefault'),
            'checkAllModules' => array('checkAllModules'),
            'dbgen'     => array('dbgen'),
            'rebuildSequences' => array('rebuildSequences'),
            'clearCache' => array('clearCache'),
            'checkLatestVersion' => array('checkLatestVersion', 'redirectToDefault'),
            'list'      => array('list'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated    = true;
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template    = $this->template;
        $input->submit      = $req->get('submitted');
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->aTranslation = $req->get('translation');
        $input->cache       = ($req->get('frmCache')) ? $req->get('frmCache') : array();

        //  get current module, check session
        $input->currentModule = $req->get('frmCurrentModule');
        $sessLastModuleSelected = SGL_Session::get('lastModuleSelected');
        if (!$input->currentModule && !$sessLastModuleSelected) {
            $input->currentModule = 'default';
        } elseif (!$input->currentModule) {
            $input->currentModule = $sessLastModuleSelected;
        } elseif ($input->action == 'checkAllModules') {

            //  this one should be always ok. to avoid bad "file doesn't exist messages from process()
            $input->currentModule = 'default';
        }

        // get current lang, check session
        $input->currentLang = ($req->get('frmCurrentLang'))
            ? $req->get('frmCurrentLang')
            : SGL_Session::get('lastLanguageSelected');

        //  if both are empty get language from prefs
        $input->currentLang = ($input->currentLang)
            ? $input->currentLang
            : $_SESSION['aPrefs']['language'];

        //  add to session
        SGL_Session::set('lastModuleSelected', $input->currentModule);
        SGL_Session::set('lastLanguageSelected', $input->currentLang);

        //  catch any single quotes
        //  note: this is done by PEAR::Config automatically!
        if (($req->get('action') !='update') && ($req->get('action') !='append')){
            if (is_array($input->aTranslation)) {
                foreach ($input->aTranslation as $k => $v) {
                    if (is_array($v)) {
                        //array_map('addslashes', $v);
                        array_map('addslashes', $v);
                    } else {
                        $input->aTranslation[$k] = addslashes($v);
                    }
                }
            }
        }
        if ($input->submit) {
            if ($req->get('action') =='' || $req->get('action') =='list') {
                $aErrors['noSelection'] = SGL_Output::translate('please specify an option');
            }
            if ($input->action == 'clearCache' && !count($input->cache)) {
                $aErrors['nothingChecked'] = SGL_Output::translate('please check at least one box');
            }
        }
        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }

        //  setup lang arrays - get source array
        require SGL_MOD_DIR . '/' . $input->currentModule . '/lang/english-iso-8859-15.php';
        $aSourceLang = isset($defaultWords) ? $defaultWords : $words;

        //  hack to remove sub arrays from translations which cannot be handled by current system
        unset($defaultWords, $words);

        //  get target array
        $target = SGL_MOD_DIR . '/' . $input->currentModule . '/lang/' . $GLOBALS['_SGL']['LANGUAGE'][$input->currentLang][1] . '.php';

        if (is_file($target)){
            @include $target;
            $aTargetLang = isset($defaultWords) ? $defaultWords : @$words;
        } else {

        //  if the target lang file does not exist
            SGL::raiseMsg('the target lang file '.$target.'does not exist, please create it now',
                SGL_ERROR_NOFILE);
        }
        $aTargetLang = SGL_Array::removeBlanks($aTargetLang);

        if ($input->action != 'checkAllModules') {

            //  if target has more keys than source
            if (count($aTargetLang) > count($aSourceLang)) {
                $error = 'source trans has ' . count($aSourceLang) . ' keys<br />';
                $error .= 'target trans has ' . count($aTargetLang) . ' keys<br />';
                $error .= 'extra keys are:<br />';
                $aDiff = array_diff(array_keys($aTargetLang), array_keys($aSourceLang));
                foreach ($aDiff as $key => $value) {
                    $error .= '['.$key.'] => '.$value.'<br />';
                }
                //print '<pre>'; print_r($aDiff); print '</pre>'; //old code, delete later ;)
                $error .= 'The translation file is probably very out of date as it contains more keys than the source';
                SGL::raiseMsg($error);
            }
        }
        //  map to input for further processing
        $input->aSourceLang = &$aSourceLang;
        $input->aTargetLang = &$aTargetLang;
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get hash of all modules;
        $output->aModules = $this->da->retrieveAllModules(SGL_RET_NAME_VALUE);

        //  load available languages
        $output->aLangs = SGL_Util::getLangsDescriptionMap();

        $output->isValidate = ($output->action == 'validate')? 'checked' : '';
        $output->isEdit = ($output->action == 'edit')? 'checked' : '';
    }

    function _cmd_verify(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $filename = SGL_MOD_DIR . '/' . $input->currentModule . '/lang/' .
            $GLOBALS['_SGL']['LANGUAGE'][$input->currentLang][1] . '.php';

        if ($input->aTargetLang) {
            $aDiff = array_diff(array_keys($input->aSourceLang), array_keys($input->aTargetLang));
            if (count($aDiff)) {
                if (is_writeable($filename)) {
                    $output->sourceElements = count($input->aSourceLang);
                    $output->targetElements = count($input->aTargetLang);
                    $output->template = 'langDiff.html';
                    $output->aTargetLang = $aDiff;
                    $output->currentModuleName = ucfirst($output->currentModule);

                    //  bypass redirection
                    $this->redirect = false;
                } else {
                    SGL::raiseMsg('The target lang file '.$filename.' is not up to date and not writeable. Please change file permissions before editing.',
                        SGL_ERROR_NOFILE);
                }
            } else {
                SGL::raiseMsg('Congratulations, the target translation appears to be up to date');
            }
        } else {
            SGL::raiseMsg('The target lang file '.$filename.' does not exist. Please create it.',
                SGL_ERROR_NOFILE);
        }
    }

    /*
    *
    * Generates a list of all modules' lang files and checks if there are new
    * strings to translate
    *
    * @author Werner M. Krauss
    * @since 0.3.10 (2004-08-26)
    * @access private
    */

    function _cmd_checkAllModules(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'langCheckAll.html';

        //  get hash of all modules
        $modules = $this->da->retrieveAllModules(SGL_RET_NAME_VALUE);

        //  load available languages
        $availableLanguages = $GLOBALS['_SGL']['LANGUAGE'];
        uasort($availableLanguages, 'SGL_cmp');
        foreach ($availableLanguages as $id => $tmplang) {
            $lang_name = ucfirst(substr(strstr($tmplang[0], '|'), 1));
            $aLangOptions[$id] =  $lang_name . ' (' . $id . ')';
        }

        //  ok, now check each module
        $status['1'] = 'ok';
        $status['2'] = 'no file';
        $status['3'] = 'new strings';
        $status['4'] = 'old strings';

        foreach ($modules as $name => $title) {
            $aModules[$name]['title'] = $title;

            //reset arrays
            unset($aSourceLang);
            unset($aTargetLang);
            unset($words);
            unset($defaultWords);

            //get source array
            $aModules[$name]['orig'] = SGL_MOD_DIR . '/' . $name . '/lang/english-iso-8859-15.php';
            require $aModules[$name]['orig'];

            $aSourceLang = isset($defaultWords) ? $defaultWords : $words;
            //  hack to remove sub arrays from translations which cannot be handled by current system
            unset($defaultWords, $words);

            //  get target array
            $aModules[$name]['src'] = SGL_MOD_DIR . '/' . $name. '/lang/' .
                $GLOBALS['_SGL']['LANGUAGE'][$input->currentLang][1] . '.php';

            if (is_file($aModules[$name]['src'])) {
                @include $aModules[$name]['src'];
                $aTargetLang = isset($defaultWords) ? $defaultWords : @$words;

                //  remove empty array elements
                $aTargetLang = @array_filter($aTargetLang, 'strlen');
                $aSourceLang = @array_filter($aSourceLang, 'strlen');

                $aSourceLang = array_keys($aSourceLang);
                $aTargetLang = array_keys($aTargetLang);
            }

            //  check status of target file
            // 1: ok, all fields ok
            // 2: targetfile doesn't exist
            // 3: target has less entries than source
            // 4: target has more entries than source

            //  if the target lang file does not exist
            if (!is_file($aModules[$name]['src'])){
                $aModules[$name]['status'] = $status['2'];
             }

            //  if target has less keys than source
            elseif (array_diff($aSourceLang,$aTargetLang)) {
                $aModules[$name]['status'] = $status['3'];
                $aModules[$name]['action'] = 'verify';
                $aModules[$name]['actionTitle'] = 'Validate';
                if (is_writeable($aModules[$name]['src']) ?
                    $aModules[$name]['diff'] = true : $aModules[$name]['msg'] = "File not writeable" );
            }

            //  if target has more keys than source
            elseif (array_diff($aTargetLang,$aSourceLang)) {
                $aModules[$name]['status'] = $status['4'];
                $aModules[$name]['action']= 'edit';
                if (is_writeable($aModules[$name]['src']) ?
                    $aModules[$name]['edit'] = true : $aModules[$name]['msg'] = "File not writeable" );
             }
            //  so if there are no differences, everything should be ok
            else {
                $aModules[$name]['status'] = $status['1'];
                $aModules[$name]['action']= 'edit';
                if (is_writeable($aModules[$name]['src']) ?
                    $aModules[$name]['edit'] = true : $aModules[$name]['msg'] = "File not writeable" );
            }
        }
        $output->modules = $aModules;
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $filename = SGL_MOD_DIR . '/' . $input->currentModule . '/lang/' .
            $GLOBALS['_SGL']['LANGUAGE'][$input->currentLang][1] . '.php';

        if (is_writeable($filename)) {
            $output->template = 'langEdit.html';
            $output->currentModuleName = ucfirst($output->currentModule);
            $output->aTargetLang = $input->aTargetLang;

        } else {
            SGL::raiseMsg('The target lang file '.$filename.' is not writeable. Please change file permissions before editing.',
                SGL_ERROR_NOFILE);
        }
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $c = new Config();

        //  read translation data and get reference to root
        $root = & $c->parseConfig($input->aTranslation, 'phparray');

        //  write translation to file
        $filename = SGL_MOD_DIR . '/' . $input->currentModule . '/lang/' .
            $GLOBALS['_SGL']['LANGUAGE'][$input->currentLang][1] . '.php';
        $arrayName = ($input->currentModule == 'default') ? 'defaultWords' : 'words';
        $result = $c->writeConfig($filename, 'phparray', array('name' => $arrayName));
        if (!is_a($result, 'PEAR_Error')) {
            SGL::raiseMsg('translation successfully updated');
        } else {
            SGL::raiseError('There was a problem updating the translation',
                SGL_ERROR_FILEUNWRITABLE);
        }
    }

    //  for amended translations that were diffed
    function _cmd_append(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //escape apostrophes in KEYS of aTargetLang. To Values it's added magically by PEAR::Config
        //note: in edit() this is done automatically by Flexy...
        /*
        foreach ($input->aTargetLang as $k => $v) {
            if (is_array($input->aTargetLang[$k])) {
                foreach ($input->aTargetLang[$k] as $kk => $vv) {
                    $aTargetLang[addslashes($k)][addslashes($kk)] = $vv;
                }
            } else {
                $aTargetLang[addslashes($k)] = $v;
            }
        }
        */
        //this should be better but makes problems, too...
        //$aUpdatedTrans = array_merge($input->aTranslation,$aTargetLang);

        //old version:
        $aUpdatedTrans = array_merge($input->aTranslation,$input->aTargetLang);

        //debugging only
        /*
        echo "<pre>aTranslation<br />";
        print_r($input->aTranslation);
        echo "<hr>input->aTargetLang<br />";
        print_r($aTargetLang);
        echo "<hr>aUpdatedTrans<br />";
        print_r($aUpdatedTrans);
        echo "</pre>";
        */

        $c = new Config();
        //  read translation data and get reference to root

        //FIXME: config seems to have problems with sub-arrays!
        $root = & $c->parseConfig($aUpdatedTrans, 'phparray');

        //  write translation to file
        $filename = SGL_MOD_DIR . '/' . $input->currentModule . '/lang/' .
            $GLOBALS['_SGL']['LANGUAGE'][$input->currentLang][1] . '.php';
        $arrayName = ($input->currentModule == 'default') ? 'defaultWords' : 'words';
        $result = $c->writeConfig($filename, 'phparray', array('name' => $arrayName));
        if (!is_a($result, 'PEAR_Error')) {
            SGL::raiseMsg('translation successfully updated');
        } else {
            SGL::raiseError('There was a problem updating the translation',
                SGL_ERROR_FILEUNWRITABLE);
        }
    }

    //  regenerate dataobject entity files
    function _cmd_dbgen(&$input, &$output)
    {
        require_once SGL_CORE_DIR . '/Install/Tasks/Install.php';
        $res = SGL_Task_CreateDataObjectEntities::run();
        SGL::raiseMsg('Data Objects rebuilt successfully');
        SGL::logMessage($res, PEAR_LOG_DEBUG);
    }

    function _cmd_checkLatestVersion(&$input, &$output)
    {
        require_once SGL_CORE_DIR . '/Install/Common.php';
        $localVersion = SGL_Install_Common::getFrameworkVersion();

        require_once SGL_CORE_DIR . '/XML/RPC/Remote.php';
        $config = SGL_MOD_DIR . '/default/xmlrpc_conf.ini';
        $remote = new SGL_XML_RPC_Remote($config);
        $remoteVersion = $remote->call('framework.determineLatestVersion');

        if (PEAR::isError($remoteVersion)) {
            SGL::raiseError('remote interface problem');
        } else {
            $res = version_compare($localVersion, $remoteVersion);
            if ($res < 0) {
                $msg = 'There is a newer version available: ' . $remoteVersion . ', please upgrade '.
                '<a href="http://seagull.phpkitchen.com/index.php/publisher/articleview/frmArticleID/12/staticId/20/">here</a>';
            } else {
                $msg = "Your current version, $localVersion, is up to date";
            }
            SGL::raiseMsg($msg, false);
        }
    }

    function _cmd_rebuildSequences(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        require_once SGL_CORE_DIR . '/Install/Tasks/Install.php';
        $res = SGL_Task_SyncSequences::run();
        if (PEAR::isError($res)) {
            return $res;
        } else {
            SGL::raiseMsg('Sequences rebuilt successfully');
        }
    }

    function _cmd_clearCache(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $msg = '';
        if (array_key_exists('templates', $input->cache)) {
            require_once 'System.php';
            $theme = $_SESSION['aPrefs']['theme'];
            $dir = SGL_CACHE_DIR . "/tmpl/$theme";
            $aFiles = System::find(array($dir, "-name", "*"));

            //  remove last element found which is the theme folder
            array_pop($aFiles);
            if (!@System::rm($aFiles)) {
                SGL::raiseError('There was a problem deleting the files',
                    SGL_ERROR_FILEUNWRITABLE);
            } else {
                SGL::raiseMsg('Cache files successfully deleted');
            }
        }
        if (count($input->cache) > 0) {
            $success = true;
            foreach ($input->cache as $group => $v) {
                $result = SGL_Cache::clear($group);
                $success = $success && $result;
            }
            if ($success === false) { //  let's see what happens with Cache_Lite's return type
                SGL::raiseError('There was a problem deleting the files',
                    SGL_ERROR_FILEUNWRITABLE);
            } else {
                SGL::raiseMsg('Cache files successfully deleted');
            }
        }
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    function _cmd_redirectToDefault(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  if no errors have occured, redirect
        if (!SGL_Error::count()) {
            if (!($this->redirect)) {
                return;
            } else {
                SGL_HTTP::redirect();
            }

        //  else display error with blank template
        } else {
            $output->template = 'docBlank.html';
        }
    }
}
?>
