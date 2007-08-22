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

require_once 'Config.php';
require_once SGL_CORE_DIR  . '/Translation.php';
require_once SGL_MOD_DIR  . '/default/classes/DefaultDAO.php';

/**
 * Provides tools preform translation maintenance.
 *
 * @package    seagull
 * @subpackage default
 * @author     Demian Turner <demian@phpkitchen.com>
 */
class TranslationMgr extends SGL_Manager
{
    // by default we redirect
    var $redirect = true;
    // file or db
    var $container;

    function TranslationMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle = 'Translation Maintenance';
        $this->template  = 'translationList.html';

        $this->_aActionsMapping = array(
            'list'            => array('list'),
            'checkAllModules' => array('checkAllModules'),
            'edit'            => array('edit'),
            'verify'          => array('verify', 'redirectToDefault'),
            'update'          => array('update', 'redirectToDefault'),
            'append'          => array('append', 'redirectToDefault'),
        );

        $this->da        = &DefaultDAO::singleton();
        $this->container = SGL_Config::get('translation.container');
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated       = true;
        $input->pageTitle      = $this->pageTitle;
        $input->template       = $this->template;
        $input->masterTemplate = $this->masterTemplate;
        $input->action         = $req->get('action')
            ? $req->get('action') : 'list';

        //  get current module
        $input->currentModule = $req->get('frmCurrentModule')
            ? $req->get('frmCurrentModule')
            : SGL_Session::get('lastModuleSelected');
        $input->currentModule = !empty($input->currentModule)
            ? $input->currentModule
            : 'default';

        //  get current lang
        $input->currentLang = $req->get('frmCurrentLang')
            ? $req->get('frmCurrentLang')
            : SGL_Session::get('lastLanguageSelected');
        //  if both are empty get language from prefs
        $input->currentLang = !empty($input->currentLang)
            ? $input->currentLang
            : $_SESSION['aPrefs']['language'];

        //  add to session
        SGL_Session::set('lastModuleSelected', $input->currentModule);
        SGL_Session::set('lastLanguageSelected', $input->currentLang);

        //  submit action
        $input->submitted = $req->get('submitted');

        if ($input->submitted) {
            if ($input->action == 'list') {
                $aErrors['noSelection'] = 'please specify an option';
            } elseif ($input->action != 'checkAllModules') {
                if ($this->container == 'file') {
                    $curLang  = SGL_Translation::transformLangID(
                        $input->currentLang, SGL_LANG_ID_SGL);
                    $filename = SGL_MOD_DIR . '/' . $input->currentModule
                        . '/lang/' . $GLOBALS['_SGL']['LANGUAGE'][$curLang][1]
                        . '.php';
                    if (is_file($filename)) {
                        if (!is_writeable($filename)) {
                            $aErrors['file'] =
                                SGL_String::translate('the target lang file')
                                . ' ' . $filename
                                . ' ' . SGL_String::translate('is not writeable.')
                                . ' ' . SGL_String::translate('Please change file'
                                . ' permissions before editing.');
                        }
                    } else {
                        $aErrors['file'] =
                            SGL_String::translate('the target lang file')
                            . ' ' . $filename
                            . ' ' . SGL_String::translate('does not exist.')
                            . ' ' . SGL_String::translate('Please create it.');
                    }
                }
            }
        }

        // after append/update
        $input->aTranslation = $req->get('translation');

        //  if errors have occured
        if (!empty($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get hash of all modules;
        $output->aModules = $this->da->getModuleHash(SGL_RET_NAME_VALUE);

        if ($this->container == 'file') {
            $aLangs      = SGL_Util::getLangsDescriptionMap();
            $currentLang = $output->currentLang;
        } else {
            $aLangs      = $this->trans->getLangs();
            $currentLang = SGL_Translation::transformLangID($output->currentLang,
                SGL_LANG_ID_TRANS2);
        }
        $output->aLangs            = $aLangs;
        $output->currentLang       = $currentLang;
        $output->currentLangName   = $aLangs[$currentLang];
        $output->currentModuleName = ucfirst($output->currentModule);
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        SGL_Translation::removeTranslationLocksByUser(SGL_Session::getUsername());
    }

    function _cmd_verify(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $fallbackLang = SGL_Config::get('translation.fallbackLang');
        //  retrieve source translations
        $aSourceLang = SGL_Translation::getTranslations($input->currentModule,
            // default language to compare with is always English
            SGL_Translation::transformLangID($fallbackLang));
        //  retrieve target translations
        $aTargetLang = SGL_Translation::getTranslations($input->currentModule,
            $input->currentLang);
        $aTargetLang = SGL_Array::removeBlanks($aTargetLang);

        if (empty($aTargetLang) && empty($aSourceLang)) {
            // no words - nothing to compare
            return true;
        }
        $aDiff = array_diff(
            array_keys($aSourceLang),
            array_keys($aTargetLang)
        );
        if (count($aDiff)) {
            $this->redirect = false;

            foreach ($aDiff as $key) {
                // provide original string
                $aLangDiff[$key] = $aSourceLang[$key];
            }

            // access check
            $isLocked = SGL_Translation::translationFileIsLocked(
                $input->currentModule, $input->currentLang);
            if ($isLocked) {
                SGL::raiseMsg('This translation is being editted by somebody else. '
                    . 'You can view translation data, but are not be able to '
                    . 'save it.', true, SGL_MESSAGE_WARNING);
            } else {
                $ok = SGL_Translation::lockTranslationFile(
                    $input->currentModule, $input->currentLang);
            }

            $output->translationIsLocked = $isLocked;
            $output->aSourceLang         = $aSourceLang;
            $output->aTargetLang         = $aLangDiff;
            $output->template            = 'translationEdit.html';
            $output->action              = 'append';

        } else {
            SGL::raiseMsg('Congratulations, the target translation' .
                ' appears to be up to date', true, SGL_MESSAGE_INFO);
        }

        // check for old entries
        $output->sourceElements = count($aSourceLang);
        $output->targetElements = count($aTargetLang);
        if ($output->targetElements > $output->sourceElements) {
            $msg = $this->_getExtraKeysMessage($aSourceLang, $aTargetLang);
            SGL::raiseMsg($msg, false);
        }
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $fallbackLang = SGL_Config::get('translation.fallbackLang');
        $aSourceLang = SGL_Translation::getTranslations($input->currentModule,
            SGL_Translation::transformLangID($fallbackLang));
        $aTargetLang = SGL_Translation::getTranslations($input->currentModule,
            $input->currentLang);
        $aTargetLang = SGL_Array::removeBlanks($aTargetLang);

        // access check
        $isLocked = SGL_Translation::translationFileIsLocked(
            $input->currentModule, $input->currentLang);
        if ($isLocked) {
            SGL::raiseMsg('This translation is being editted by somebody else. '
                . 'You can view translation data, but are not be able to '
                . 'save it.', true, SGL_MESSAGE_WARNING);

        // lock translation file
        } else {
            $ok = SGL_Translation::lockTranslationFile($input->currentModule,
                $input->currentLang);
        }

        $output->translationIsLocked = $isLocked;
        $output->aSourceLang         = $aSourceLang;
        $output->aTargetLang         = $aTargetLang;
        $output->template            = 'translationEdit.html';
        $output->action              = 'update';
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // access check
        $isLocked = SGL_Translation::translationFileIsLocked(
            $input->currentModule, $input->currentLang);
        if ($isLocked) {
            SGL::raiseMsg('This translation is being editted by somebody else. '
                . 'You can view translation data, but are not be able to '
                . 'save it.', true, SGL_MESSAGE_WARNING);
            return false;
        } else {
            SGL_Translation::removeTranslationLock($input->currentModule,
                $input->currentLang);
        }

        $input->aTranslation = SGL_Array::removeBlanks($input->aTranslation);

        //  update translations
        $ok = SGL_Translation::updateGuiTranslations(
            $input->currentModule,
            $input->currentLang,
            $input->aTranslation
        );
        if (!PEAR::isError($ok)) {
            SGL::raiseMsg('translation successfully updated', true,
                SGL_MESSAGE_INFO);
        } else {
            SGL::raiseMsg('There was a problem updating the translation',
                SGL_ERROR_FILEUNWRITABLE);
        }
    }

    function _cmd_append(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // access check
        $isLocked = SGL_Translation::translationFileIsLocked(
            $input->currentModule, $input->currentLang);
        if ($isLocked) {
            SGL::raiseMsg('This translation is being editted by somebody else. '
                . 'You can view translation data, but are not be able to '
                . 'save it.', true, SGL_MESSAGE_WARNING);
            return false;
        } else {
            SGL_Translation::removeTranslationLock($input->currentModule,
                $input->currentLang);
        }

        $aTargetLang = SGL_Translation::getTranslations($input->currentModule,
            $input->currentLang);

        //  remove blanks and merge
        $input->aTranslation = SGL_Array::removeBlanks($input->aTranslation);
        $aTrans = array_merge($input->aTranslation, $aTargetLang);

        //  update translations
        $ok = SGL_Translation::updateGuiTranslations($input->currentModule,
                $input->currentLang, $aTrans);

        if (!PEAR::isError($ok)) {
            SGL::raiseMsg('translation successfully updated', true,
                SGL_MESSAGE_INFO);
        } else {
            SGL::raiseMsg('There was a problem updating the translation',
                SGL_ERROR_FILEUNWRITABLE);
        }
    }

    function _cmd_checkAllModules(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get hash of all modules
        $modules = $this->da->getModuleHash(SGL_RET_NAME_VALUE);

        //  ok, now check each module
        $status['1'] = 'ok';
        $status['2'] = 'no file';
        $status['3'] = 'new strings';
        $status['4'] = 'old strings';

        //  the default language to compare with is always English
        $fallbackLang = SGL_Config::get('translation.fallbackLang');
        foreach ($modules as $name => $title) {
            $aModules[$name]['title'] = $title;

            //  get source array
            $aModules[$name]['orig'] = SGL_MOD_DIR . '/' . $name . '/lang/' .
                $GLOBALS['_SGL']['LANGUAGE'][$fallbackLang][1] . '.php';
            $aSourceLang =
                ($words = SGL_Translation::getTranslations($name, $fallbackLang))
                    ? $words
                    : array();

            //  get target array
            $curLang = SGL_Translation::transformLangID($input->currentLang,
                SGL_LANG_ID_SGL);
            $aModules[$name]['src'] = SGL_MOD_DIR . '/' . $name. '/lang/' .
                $GLOBALS['_SGL']['LANGUAGE'][$curLang][1] . '.php';
            $aTargetLang =
                ($words = SGL_Translation::getTranslations($name, $curLang))
                    ? $words
                    : array();

            //  check status of target file
            //    1: ok, all fields ok
            //    2: targetfile doesn't exist
            //    3: target has less entries than source
            //    4: target has more entries than source

            //  if the target lang file does not exist
            if (!is_file($aModules[$name]['src'])){
                $aModules[$name]['status'] = $status['2'];

            //  if target has less keys than source
            } elseif (array_diff(array_keys($aSourceLang), array_keys($aTargetLang))) {
                $aModules[$name]['status'] = $status['3'];
                if ($this->container == 'file'
                        && !is_writeable($aModules[$name]['src'])) {
                    $aModules[$name]['msg'] = "File not writeable";
                } else {
                    $aModules[$name]['diff'] = true;
                }

            //  if target has more keys than source
            } elseif (array_diff(array_keys($aTargetLang), array_keys($aSourceLang))) {
                $aModules[$name]['status'] = $status['4'];
                if ($this->container == 'file'
                        && !is_writeable($aModules[$name]['src'])) {
                    $aModules[$name]['msg'] = "File not writeable";
                } else {
                    $aModules[$name]['edit'] = true;
                }

            //  so if there are no differences, everything should be ok
            } else {
                $aModules[$name]['status'] = $status['1'];
                if ($this->container == 'file'
                        && !is_writeable($aModules[$name]['src'])) {
                    $aModules[$name]['msg'] = "File not writeable";
                } else {
                    $aModules[$name]['edit'] = true;
                }
            }
        }
        $output->modules  = $aModules;
        $output->template = 'translationCheckAll.html';
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

    function _getExtraKeysMessage($aSourceLang, $aTargetLang)
    {
        $message = 'source trans has ' . count($aSourceLang) . ' keys<br />'
            . 'target trans has ' . count($aTargetLang) . ' keys<br />'
            . 'extra keys are:<br />';
        $aExtra = array_diff(
            array_keys($aTargetLang),
            array_keys($aSourceLang)
        );
        foreach ($aExtra as $key => $value) {
            $message .= '[' . $key . '] => ' . $value . '<br />';
        }
        $message .= 'The translation file is probably contains more '
            . 'keys than the source';
        return $message;
    }
}
?>