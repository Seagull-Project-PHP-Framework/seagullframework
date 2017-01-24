<?php

require_once dirname(__FILE__) . '/Translation2.php';

/**
 * CLI manager.
 *
 * @package translation
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 *
 * USAGE:
 *   php www/index.php \
 *     --moduleName=translation \
 *     --managerName=jstranslation \
 *     --action=createFiles
 */
class JsTranslationMgr extends SGL_Manager
{
    public function __construct()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->_aActionsMapping = array(
            'list'        => array('list', 'cliResult'),
            'createFiles' => array('createFiles', 'cliResult')
        );
    }

    public function validate(SGL_Request $req, SGL_Registry $input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated = true;
        $input->tty      = "\n";
        $input->action   = $req->get('action') ? $req->get('action') : 'list';

        $input->targetModule  = $req->get('targetModule');
        $input->modulesToScan = $req->get('modulesToScan');

        $input->aModules = SGL_Util::getAllModuleDirs();

        // 'create files' action validation
        if ($input->action == 'createFiles' && !empty($input->targetModule)) {
            if (!in_array($input->targetModule, $input->aModules)) {
                $input->tty .= "Error: --targetModule module doesn't exists\n";
                $this->validated = false;
            }
        }
        if ($input->action == 'createFiles' && !empty($input->modulesToScan)) {
            $aModules = explode(',', $input->modulesToScan);
            foreach ($aModules as $moduleName) {
                if (!in_array($moduleName, $input->aModules)) {
                    $msg = "Error: --modulesToScan '%s' module doesn't exist\n";
                    $input->tty .= sprintf($msg, $moduleName);
                    $this->validated = false;
                }
            }
            if ($this->validated) {
                $input->aModules = $aModules;
            }
        }
    }

    public function display(SGL_Output $output)
    {
        $this->_cmd_cliResult($output, $output);
    }

    public function _cmd_list(SGL_Reqistry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $input->tty = <<< HELP

Available actions:
  1. createFiles                  create JavaScript localisation files
       --targetModule               module where JS translations will be stored
       --modulesToScan              comma-separated list of modules

HELP;
    }

    public function _cmd_createFiles(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aLangs        = SGL_Util::getLangsDescriptionMap();
        $defaultModule = !empty($input->targetModule)
            ? $input->targetModule
            : SGL_Config::get('site.defaultModule');
        $defaultLang   = SGL_Translation2::transformLangID(
            SGL_Translation2::getFallbackLangID(),
            SGL_LANG_ID_SGL
        );
        // make sure default lang goes first
        $aLangs       = array_merge(array($defaultLang => $aLangs[$defaultLang]), $aLangs);
        $aModules     = $input->aModules;
        $aDefaultDict = array();
        foreach ($aLangs as $key => $langName) {
            $aDict = array();
            foreach ($aModules as $moduleName) {
                $transFile = SGL_Translation2::getFileName($moduleName, $key);
                $aTrans = $words = $defaultWords = array();
                if (file_exists($transFile)) {
                    include $transFile;
                }
                if (!empty($words)) {
                    $aTrans = $words;
                } elseif (!empty($defaultWords)) {
                    $aTrans = $defaultWords;
                }
                if ($key == $defaultLang) {
                    $aModDict = self::_getStringsByCategory($aTrans, 'javascript');
                    $aModDict = SGL_Translation2::removeMetaData($aModDict, true);
                    $aDict    = array_merge($aDict, $aModDict);
                } else {
                    $aTrans   = SGL_Translation2::removeMetaData($aTrans, true);
                    $aDict    = array_merge($aDict, $aTrans);
                }
            }

            // remember default dictionary
            if ($key == $defaultLang) {
                $aDefaultDict = $aDict;
            // re-create js dictionary based on default one
            } else {
                $aLangDict = array();
                foreach ($aDefaultDict as $k => $v) {
                    if (isset($aDict[$k])) {
                        $aLangDict[$k] = $aDict[$k];
                    }
                }
                $aDict = $aLangDict;
            }

            $jsLocasation = '';
            foreach ($aDict as $k => $v) {
                $jsLocasation .= "\t'"
                    . SGL_Translation2::escapeSingleQuote($k). "' : '"
                    . SGL_Translation2::escapeSingleQuote($v) ."',\n";
            }

            if (!empty($jsLocasation)) {
                // create js string
                $jsLocasation = substr($jsLocasation, 0, -2);
                $jsLocasation = "SGL2.Localisation = {\n$jsLocasation\n};";

                $filePath = sprintf('%s/%s/www/js/Localisation/%s.js',
                    SGL_MOD_DIR, $defaultModule, $key);
                self::_ensureDirIsWriteable(dirname($filePath));

                // save and show results
                $ok  = file_put_contents($filePath, $jsLocasation);
                $msg = $ok
                    ? sprintf('JavaScript translation file created \'%s\'', $filePath)
                    : sprintf('Error creating file \'%s\'', $filePath);

                $input->tty .= $msg . "\n";
                $this->_flush($input->tty);
            }
        }
    }

    /**
     * Action, which outputs CLI result.
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    public function _cmd_cliResult(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $input->tty .= "\n";
        $this->_flush($input->tty, $stopScript = true);
    }

    /**
     * Send data to terminal.
     *
     * @param string $string
     * @param boolean $stopScript
     */
    protected function _flush(&$string, $stopScript = false)
    {
        echo $string;
        flush();
        $string = '';
        if ($stopScript) {
            exit;
        }
    }

    /**
     * @todo move to SGL_File.
     */
    protected static function _ensureDirIsWriteable($dir)
    {
        if (!is_writeable($dir)) {
            require_once 'System.php';
            $ok = System::mkDir(array('-p', $dir));
            $mask = umask(0);
            chmod($dir, 0777);
            umask($mask);
        }
    }

    /**
     * @todo move to some generic place
     */
    protected static function _getStringsByCategory($aStrings, $categoryName)
    {
        $aRet = array();
        $categoryName = '__SGL_CATEGORY_' . $categoryName;
        if (array_key_exists($categoryName, $aStrings)) {
            $aValues = array_values($aStrings);
            $aKeys   = array_keys($aStrings);

            $index   = array_search($categoryName, $aKeys);
            $aValues = array_slice($aValues, $index + 1);
            $aKeys   = array_slice($aKeys, $index + 1);

            foreach ($aKeys as $index => $key) {
            	if (false !== ($pos = strpos($key, '__SGL_CATEGORY_'))) {
                    $aValues = array_slice($aValues, 0, $index);
                    $aKeys   = array_slice($aKeys, 0, $index);
                    break;
            	}
            }

            $aRet = array_combine($aKeys, $aValues);
        }
        return $aRet;
    }
}
?>