<?php

/**
 * Resolves request params into Manager model object.
 *
 * The module is resolved from Request parameter, if resolution fails, default
 * module is loaded.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Filter_ResolveManager extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $req = $input->getRequest();
        $moduleName = $req->get('moduleName');
        $managerName = $req->get('managerName');
        $getDefaultMgr = false;

        if (empty($moduleName) || empty($managerName)) {

            SGL::logMessage('Module and manager names could not be determined from request');
            $getDefaultMgr = true;

        } else {
            if (!SGL::moduleIsEnabled($moduleName)) {
                SGL::raiseError('module "'.$moduleName.'" does not appear to be registered',
                    SGL_ERROR_RESOURCENOTFOUND);
                $getDefaultMgr = true;
            } else {
                $conf = $input->getConfig();

                //  get manager name, if $managerName not correct attempt to load default
                //  manager w/$moduleName
                $mgrPath = SGL_MOD_DIR . '/' . $moduleName . '/classes/';
                $retMgrName = $this->getManagerName($managerName, $mgrPath, $conf);
                if ($retMgrName === false) {
                    SGL::raiseError("Specified manager '$managerName' could not be found, ".
                        "defaults loaded, pls ensure full manager name is present in module's conf.ini",
                        SGL_ERROR_RESOURCENOTFOUND);
                }
                $managerName = ($retMgrName)
                    ? $retMgrName
                    : $this->getManagerName($moduleName, $mgrPath, $conf);
                if (!empty($managerName)) {

                    //  build path to manager class
                    $classPath = $mgrPath . $managerName . '.php';
                    if (@is_file($classPath)) {
                        require_once $classPath;

                        //  if class exists, instantiate it
                        if (@class_exists($managerName)) {
                            $input->moduleName = $moduleName;
                            $input->set('manager', new $managerName);
                        } else {
                            SGL::logMessage("Class $managerName does not exist");
                            $getDefaultMgr = true;
                        }
                    } else {
                        SGL::logMessage("Could not find file $classPath");
                        $getDefaultMgr = true;
                    }
                } else {
                    SGL::logMessage('Manager name could not be determined from '.
                                    'SGL_Process_ResolveManager::getManagerName');
                    $getDefaultMgr = true;
                }
            }
        }
        if ($getDefaultMgr) {
            $ok = $this->getConfiguredDefaultManager($input);
            if (!$ok) {
                SGL::raiseError("The default manager could not be found",
                    SGL_ERROR_RESOURCENOTFOUND);
                $this->getDefaultManager($input);
            }
        }
        $this->processRequest->process($input, $output);
    }

    /**
     * Loads the default manager per config settings or returns false on failure.
     *
     * @param SGL_Registry $input
     * @return boolean
     */
    function getConfiguredDefaultManager(&$input)
    {
        $defaultModule = SGL_Config::get('site.defaultModule');
        $defaultMgr = SGL_Config::get('site.defaultManager');

        //  load default module's config if not present
        $c = &SGL_Config::singleton();
        $conf = $c->ensureModuleConfigLoaded($defaultModule);

        if (PEAR::isError($conf)) {
            SGL::raiseError('could not locate module\'s config file',
                SGL_ERROR_NOFILE);
            return false;
        }

        $mgrName = SGL_Inflector::caseFix(
            SGL_Inflector::getManagerNameFromSimplifiedName($defaultMgr));
        $path = SGL_MOD_DIR .'/'.$defaultModule.'/classes/'.$mgrName.'.php';
        if (!is_file($path)) {
            SGL::raiseError('could not locate default manager, '.$path,
                SGL_ERROR_NOFILE);
            return false;
        }
        require_once $path;
        if (!class_exists($mgrName)) {
            SGL::raiseError('invalid class name for default manager',
                SGL_ERROR_NOCLASS);
            return false;
        }
        $mgr = new $mgrName();
        $input->moduleName = $defaultModule;
        $input->set('manager', $mgr);
        $req = $input->getRequest();
        $req->set('moduleName', $defaultModule);
        $req->set('managerName', $defaultMgr);

        if (SGL_Config::get('site.defaultParams')) {
            $aParams = SGL_Url::querystringArrayToHash(
                explode('/', SGL_Config::get('site.defaultParams')));
            $req->add($aParams);
        }
        $input->setRequest($req); // this ought to take care of itself
        return true;
    }

    function getDefaultManager(&$input)
    {
        $defaultModule = 'default';
        $defaultMgr = 'default';
        $mgrName = SGL_Inflector::caseFix(
            SGL_Inflector::getManagerNameFromSimplifiedName($defaultMgr));
        $path = SGL_MOD_DIR .'/'.$defaultModule.'/classes/'.$mgrName.'.php';
        require_once $path;
        $mgr = new $mgrName();
        $input->moduleName = $defaultModule;
        $input->set('manager', $mgr);
        $req = $input->getRequest();
        $req->set('moduleName', $defaultModule);
        $req->set('managerName', $defaultMgr);
        $input->setRequest($req);
        return true;
    }

    /**
     * Returns classname suggested by URL param.
     *
     * @access  private
     * @param   string  $managerName    name of manager class
     * @param   string  $path           path to manager class
     * @param   array  $conf            array of config values
     * @return  mixed   either found class name or PEAR error
     */
    function getManagerName($managerName, $path, $conf)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aMatches = array();
        $aConfValues = array_keys($conf);
        $aConfValuesLowerCase = array_map('strtolower', $aConfValues);

        //  if Mgr suffix has been left out, append it
        $managerName = SGL_Inflector::getManagerNameFromSimplifiedName($managerName);

        //  test for full case sensitive classname in config array
        $isFound = array_search($managerName, $aConfValues);
        if ($isFound !== false) {
            $aMatches['caseSensitiveMgrName'] = $aConfValues[$isFound];
        }
        unset($isFound);

        //  test for full case insensitive classname in config array
        $isFound = array_search(strtolower($managerName), $aConfValuesLowerCase);
        if ($isFound !== false) {
            $aMatches['caseInSensitiveMgrName'] = $aConfValues[$isFound];
        }

        foreach ($aMatches as $match) {
            if (!@is_file($path . $match . '.php')) {
                continue;
            } else {
                return $match;
            }
        }
        return false;
    }
}

?>