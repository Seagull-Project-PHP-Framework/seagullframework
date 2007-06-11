<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Demian Turner                                         |
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
// | TranslationMgr.php                                                        |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// |         Werner M. Krauss <werner.krauss@hallstatt.net>                    |
// |         Alexander J. Tarachanowicz II <ajt@localhype.net>                 |
// +---------------------------------------------------------------------------+
// $Id: TranslationMgr. v 1.0 2005/04/17 02:15:02 demian Exp $

require_once 'Config.php';
require_once SGL_MOD_DIR  . '/default/classes/DefaultDAO.php';

/**
 * Provides tools preform translation maintenance.
 *
 * @package default
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.56 $
 * @since   PHP 4.1
 */

class TranslationMgr extends SGL_Manager
{
    function TranslationMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Translation Maintenance';
        $this->template     = 'translationMgr.html';
        $this->redirect     = true;
        $this->da           = & DefaultDAO::singleton();

        $this->_aActionsMapping =  array(
            'verify'    => array('verify', 'redirectToDefault'),
            'edit'      => array('edit'),
            'update'    => array('update', 'redirectToDefault'),
            'append'    => array('append', 'redirectToDefault'),
            'checkAllModules' => array('checkAllModules'),
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
        $input->action      = ($req->get('action'))
            ? $req->get('action')
            : 'list';
        $input->aTranslation = $req->get('translation');

        //  get current module, check session
        $input->currentModule = $req->get('frmCurrentModule');
        $sessLastModuleSelected = SGL_Session::get('lastModuleSelected');
        if (!$input->currentModule && !$sessLastModuleSelected) {
            $input->currentModule = 'default';
        } elseif (!$input->currentModule) {
            $input->currentModule = $sessLastModuleSelected;
        } elseif ($input->action == 'checkAllModules') {

            //  this one should be always ok. to avoid bad "file doesn't exist
            //  messages from process()
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
        if (($req->get('action') !='update')
            && ($req->get('action') !='append')) {
            if (is_array($input->aTranslation)) {
                foreach ($input->aTranslation as $k => $v) {
                    if (is_array($v)) {
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
        }

        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }
        //  retrieve source translations
        $aSourceLang = SGL_Translation::getTranslations($input->currentModule,
            // default language to compare with is always English
            SGL_Translation::transformLangID('en-iso-8859-15'));
        //  retrieve target translations
        $aTargetLang = SGL_Translation::getTranslations($input->currentModule,
            $input->currentLang);

        //  if the target lang file does not exist
        if ($this->conf['translation']['container'] == 'file') {
            $curLang = SGL_Translation::transformLangID($input->currentLang, SGL_LANG_ID_SGL);
            $target = SGL_MOD_DIR . '/' . $input->currentModule . '/lang/' .
                $GLOBALS['_SGL']['LANGUAGE'][$curLang][1] . '.php';

            if (!is_file($target)) {
                $errMsg = SGL_String::translate('the target lang file') .
                            ' '. $target .
                            ' '. SGL_String::translate('does not exist.') .
                            ' '. SGL_String::translate('Please create it.');
                SGL::raiseMsg($errMsg, false, SGL_ERROR_NOFILE);
            }
        }
        $aTargetLang = SGL_Array::removeBlanks($aTargetLang);

        if ($input->action != 'checkAllModules') {
            //  if target has more keys than source
            if (count($aSourceLang) && count($aTargetLang)
                    && count($aTargetLang) > count($aSourceLang)) {
                $error = 'source trans has ' . count($aSourceLang) . ' keys<br />';
                $error .= 'target trans has ' . count($aTargetLang) . ' keys<br />';
                $error .= 'extra keys are:<br />';
                $aDiff = array_diff(array_keys($aTargetLang), array_keys($aSourceLang));
                foreach ($aDiff as $key => $value) {
                    $error .= '['.$key.'] => '.$value.'<br />';
                }
                $error .= 'The translation file is probably contains more keys than the source';
                SGL::raiseMsg($error);
            }
            //  map to input for further processing
            $input->aSourceLang = &$aSourceLang;
            $input->aTargetLang = &$aTargetLang;
        }
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get hash of all modules;
        $output->aModules = $this->da->getModuleHash(SGL_RET_NAME_VALUE);

        $output->isValidate = ($output->action == 'validate')? 'checked' : '';
        $output->isEdit = ($output->action == 'edit')? 'checked' : '';

        if ($this->conf['translation']['container'] == 'file') {
            $aLangs      = SGL_Util::getLangsDescriptionMap();
            $currentLang = $output->currentLang;
        } else {
            $aLangs      = $this->trans->getLangs();
            $currentLang = SGL_Translation::transformLangID($output->currentLang,
                SGL_LANG_ID_TRANS2);
        }
        $output->aLangs          = $aLangs;
        $output->currentLang     = $currentLang;
        $output->currentLangName = $aLangs[$currentLang];
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    function _cmd_verify(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if ($this->conf['translation']['container'] == 'file') {
            $curLang = SGL_Translation::transformLangID($input->currentLang, SGL_LANG_ID_SGL);
            $filename = SGL_MOD_DIR . '/' . $input->currentModule . '/lang/' .
                $GLOBALS['_SGL']['LANGUAGE'][$curLang][1] . '.php';

            if (is_file($filename)) {
                if (!is_writeable($filename)) {
                    $errMsg = SGL_String::translate('the target lang file') .
                        ' '. $filename .
                        ' '. SGL_String::translate('is not writeable.') .
                        ' '. SGL_String::translate('Please change file permissions before editing.');
                    SGL::raiseMsg($errMsg, false, SGL_ERROR_NOFILE);
                    $this->redirect = true;
                }
            } else {
                $errMsg = SGL_String::translate('the target lang file') .
                            ' '. $filename .
                            ' '. SGL_String::translate('does not exist.') .
                            ' '. SGL_String::translate('Please create it.');
                SGL::raiseMsg($errMsg, false, SGL_ERROR_NOFILE);
                $this->redirect = false;
            }
        }

        if (count($input->aSourceLang) && count($input->aTargetLang)) {
            $aDiff = array_diff(array_keys($input->aSourceLang), array_keys($input->aTargetLang));

            if (count($aDiff)) {
                $output->sourceElements = count($input->aSourceLang);
                $output->targetElements = count($input->aTargetLang);
                $output->template = 'langDiff.html';

                foreach($aDiff as $key) {
                    //provide original string
                    $aLangDiff[$key] = $input->aSourceLang[$key];
                }

                $output->aTargetLang = $aLangDiff;
                $output->currentModuleName = ucfirst($output->currentModule);

                //  bypass redirection
                $this->redirect = false;
            } else {
                SGL::raiseMsg('Congratulations, the target translation' .
                    ' appears to be up to date', true, SGL_MESSAGE_INFO);
            }
        }
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if ($this->conf['translation']['container'] == 'file') {
            $curLang = SGL_Translation::transformLangID($input->currentLang, SGL_LANG_ID_SGL);
            $filename = SGL_MOD_DIR . '/' . $input->currentModule . '/lang/' .
                $GLOBALS['_SGL']['LANGUAGE'][$curLang][1] . '.php';

            if (is_file($filename)) {
                if (!is_writeable($filename)) {
                    $errMsg = SGL_String::translate('the target lang file') .
                        ' '. $filename .
                        ' '. SGL_String::translate('is not writeable.') .
                        ' '. SGL_String::translate('Please change file permissions before editing.');
                    SGL::raiseMsg($errMsg, false, SGL_ERROR_NOFILE);
                }
            } else {
                $errMsg = SGL_String::translate('the target lang file') .
                            ' '. $filename .
                            ' '. SGL_String::translate('does not exist.') .
                            ' '. SGL_String::translate('Please create it.');
                SGL::raiseMsg($errMsg, false, SGL_ERROR_NOFILE);
            }
        }

        $output->template = 'langEdit.html';
        $output->currentModuleName = ucfirst($output->currentModule);
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  update translations
        $curLang = $input->currentLang;
        $ok      = SGL_Translation::updateGuiTranslations($input->currentModule,
                        $curLang, $input->aTranslation);
        if (!is_a($ok, 'PEAR_Error')) {
            SGL::raiseMsg('translation successfully updated', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseMsg('There was a problem updating the translation',
                SGL_ERROR_FILEUNWRITABLE);
        }
    }

    function _cmd_append(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // remove blanks and merge
        $input->aTranslation = SGL_Array::removeBlanks($input->aTranslation);
        $aTrans = array_merge($input->aTranslation,$input->aTargetLang);

        //  update translations
        $ok = SGL_Translation::updateGuiTranslations($input->currentModule,
                $input->currentLang, $aTrans);

        if (!is_a($ok, 'PEAR_Error')) {
            SGL::raiseMsg('translation successfully updated', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseMsg('There was a problem updating the translation',
                SGL_ERROR_FILEUNWRITABLE);
        }
    }

    function _cmd_checkAllModules(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'langCheckAll.html';

        //  get hash of all modules
        $modules = $this->da->getModuleHash(SGL_RET_NAME_VALUE);

        //  ok, now check each module
        $status['1'] = 'ok';
        $status['2'] = 'no file';
        $status['3'] = 'new strings';
        $status['4'] = 'old strings';

        // the default language to compare with is always English
        $fallbackLang = 'en-iso-8859-15';
        foreach ($modules as $name => $title) {
            $aModules[$name]['title'] = $title;

            //reset arrays
            unset($aSourceLang);
            unset($aTargetLang);
            unset($words);
            unset($defaultWords);

            //get source array
            $fbackLang = SGL_Translation::transformLangID($fallbackLang, SGL_LANG_ID_SGL);
            $aModules[$name]['orig'] = SGL_MOD_DIR . '/' . $name . '/lang/' .
                    $GLOBALS['_SGL']['LANGUAGE'][$fbackLang][1] . '.php';

            $aSourceLang = ($words = SGL_Translation::getTranslations($name, $fallbackLang))
                            ? $words
                            : array();

            //  hack to remove sub arrays from translations which cannot be handled by current system
            unset($defaultWords, $words);

            //  get target array
            $curLang = SGL_Translation::transformLangID($input->currentLang, SGL_LANG_ID_SGL);
            $aModules[$name]['src'] = SGL_MOD_DIR . '/' . $name. '/lang/' .
                    $GLOBALS['_SGL']['LANGUAGE'][$curLang][1] . '.php';

            $aTargetLang = ($words = SGL_Translation::getTranslations($name, $curLang))
                            ? $words
                            : array();

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
            elseif (array_diff(array_keys($aSourceLang),array_keys($aTargetLang))) {
                $aModules[$name]['status'] = $status['3'];
                $aModules[$name]['action'] = 'verify';
                $aModules[$name]['actionTitle'] = 'Validate';
                if ($this->conf['translation']['container'] == 'file'
                    && !is_writeable($aModules[$name]['src'])) {
                    $aModules[$name]['msg'] = "File not writeable";
                } else {
                    $aModules[$name]['diff'] = true;
                }
            }

            //  if target has more keys than source
            elseif (array_diff(array_keys($aTargetLang),array_keys($aSourceLang) )) {
                $aModules[$name]['status'] = $status['4'];
                $aModules[$name]['action']= 'edit';
                if ($this->conf['translation']['container'] == 'file'
                    && !is_writeable($aModules[$name]['src'])) {
                    $aModules[$name]['msg'] = "File not writeable";
                } else {
                    $aModules[$name]['edit'] = true;
                }

             }
            //  so if there are no differences, everything should be ok
            else {
                $aModules[$name]['status'] = $status['1'];
                $aModules[$name]['action']= 'edit';
                if ($this->conf['translation']['container'] == 'file'
                    && !is_writeable($aModules[$name]['src'])) {
                    $aModules[$name]['msg'] = "File not writeable";
                } else {
                    $aModules[$name]['edit'] = true;
                }
            }

            //  remove empty array elements
            $aTargetLang = @array_filter($aTargetLang, 'strlen');
            $aSourceLang = @array_filter($aSourceLang, 'strlen');

            $aSourceLang = array_keys($aSourceLang);
            $aTargetLang = array_keys($aTargetLang);
        }
        $output->modules = $aModules;
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
