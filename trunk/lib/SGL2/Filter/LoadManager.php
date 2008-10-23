<?php

/**
 * Resolves request params into Manager model object.
 *
 * The module is resolved from Request parameter, if resolution fails, default
 * module is loaded.
 *
 * @package Filter
 * @author  Demian Turner <demian@phpkitchen.com>
 * @todo move to BC plugin
 */
class SGL2_Filter_LoadManager extends SGL2_DecorateProcess
{
    public function process(SGL2_Request $input, SGL2_Response $output)
    {
        $moduleName = $input->get('moduleName');
        $managerName = $input->get('managerName');

        $mgr = SGL_Inflector::getManagerNameFromSimplifiedName($managerName);
        $mgrPath = SGL2_MOD_DIR . '/' . $moduleName . '/classes/';

        //  build path to manager class
        $classPath = $mgrPath . $mgr . '.php';
        if (SGL2_File::exists($classPath)) {
            $input->set('manager', new $managerName);
        } else {
            throw new Exception("Manager '$mgr' could not be found at $classPath");
        }

        $this->_processRequest->process($input, $output);
    }
}

?>