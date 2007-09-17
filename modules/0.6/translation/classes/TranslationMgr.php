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

require_once SGL_LIB_PEAR_DIR . '/Config.php';
require_once SGL_MOD_DIR  . '/default/classes/DefaultDAO.php';
require_once SGL_MOD_DIR  . '/translation/classes/Translation2.php';

/**
 * Provides tools preform translation maintenance.
 *
 * @package    seagull
 * @subpackage default
 * @author     Demian Turner <demian@phpkitchen.com>
 */
class TranslationMgr extends SGL_Manager
{
    // file or db
    var $container;

    function TranslationMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle = 'Translation Maintenance';
        $this->template  = 'translationList.html';

        $this->_aActionsMapping = array(
            'list'              => array('list'),
            'edit'              => array('edit'),
            'update'            => array('update', 'redirectToDefault'),
            'summary'           => array('summary')
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
            } elseif ($input->action != 'summary') {
                if ($this->container == 'file') {
                    $curLang  = SGL_Translation2::transformLangID(
                        $input->currentLang, SGL_LANG_ID_SGL);
                    $filename = SGL_Translation2::getFileName($input->currentModule, $curLang);
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

        // get hash of all modules;
        $aModules = SGL_Util::getAllModuleDirs(true);
        // looking for email translation files
        foreach ($aModules as $module) {
            if (is_dir(SGL_MOD_DIR . '/' . $module . '/lang/email')) {
                $aModules[strtolower($module) . '_email'] = 'Mail: ' . $module;
            }
        }
        $output->aModules = $aModules;
        if ($this->container == 'file') {
            $aLangs      = SGL_Util::getLangsDescriptionMap();
            $currentLang = $output->currentLang;
        } else {
            $aLangs      = $this->trans->getLangs();
            $currentLang = SGL_Translation2::transformLangID($output->currentLang,
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

        SGL_Translation2::removeTranslationLocksByUser(SGL_Session::getUsername());
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $fallbackLang = SGL_Config::get('translation.fallbackLang');
        // do not allow to edit master translation
        if (SGL_Translation::transformLangID($fallbackLang) == $input->currentLang) {
            SGL::raiseMsg('You are not allowed to edit master translation', true,
                SGL_MESSAGE_WARNING);
            return false;
        }

        $aSourceLang = SGL_Translation2::getTranslations($input->currentModule,
            SGL_Translation2::transformLangID($fallbackLang));
        $aTargetLang = SGL_Translation2::getTranslations($input->currentModule,
            $input->currentLang);

        // access check
        $isLocked = SGL_Translation2::translationFileIsLocked(
            $input->currentModule, $input->currentLang);
        if ($isLocked) {
            $lockOwner = SGL_Translation2::getTranslationLockOwner(
                 $input->currentModule, $input->currentLang);
            SGL::raiseMsg('This translation is being editted by ' . $lockOwner
                . '. You can view translation data, but are not be able to '
                . 'save it.', false, SGL_MESSAGE_WARNING);

        // lock translation file
        } else {
            $ok = SGL_Translation2::lockTranslationFile($input->currentModule,
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
        $isLocked = SGL_Translation2::translationFileIsLocked(
            $input->currentModule, $input->currentLang);
        if ($isLocked) {
            $lockOwner = SGL_Translation2::getTranslationLockOwner(
                 $input->currentModule, $input->currentLang);
            SGL::raiseMsg('This translation is being editted by ' . $lockOwner
                . '. You can view translation data, but are not be able to '
                . 'save it.', false, SGL_MESSAGE_WARNING);
            return false;
        } else {
            SGL_Translation2::removeTranslationLock($input->currentModule,
                $input->currentLang);
        }

        $fallbackLang = SGL_Config::get('translation.fallbackLang');
        $fallbackLang = SGL_Translation2::transformLangID($fallbackLang,
            SGL_LANG_ID_SGL);

        // do not allow to edit master translation
        if ($fallbackLang == $input->currentLang) {
            SGL::raiseMsg('You are not allowed to edit master translation', true,
                SGL_MESSAGE_WARNING);
            return false;
        }

        // do not remove blanks for default translation
        if ($input->currentLang != $fallbackLang) {
            $input->aTranslation = SGL_Array::removeBlanks($input->aTranslation);
        }

        //  update translations
        $ok = SGL_Translation2::updateGuiTranslations(
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

    function _cmd_summary(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'translationSummary.html';
    }
}
?>