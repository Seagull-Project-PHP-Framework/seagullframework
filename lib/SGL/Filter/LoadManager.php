<?php

/**
 * Resolves request params into Manager model object.
 *
 * The module is resolved from Request parameter, if resolution fails, default
 * module is loaded.
 *
 * @package Filter
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Filter_LoadManager extends SGL_DecorateProcess
{
    public function process(SGL_Request $input, SGL_Response $output)
    {
        $req = SGL_Registry::get('request');
        $moduleName = $req->get('moduleName');
        $managerName = $req->get('managerName');

        $mgr = SGL_Inflector::getManagerNameFromSimplifiedName($managerName);
        $mgrPath = SGL_MOD_DIR . '/' . $moduleName . '/classes/';

        //  build path to manager class
        $classPath = $mgrPath . $mgr . '.php';
        if (SGL::isReadable($classPath)) {
            $input->set('manager', new $managerName);
        } else {
            throw new Exception("Manager '$mgr' could not be found at $classPath");
        }

        $this->processRequest->process($input, $output);
    }
}

?>