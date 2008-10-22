<?php

/**
 * Instaniates a controller based on request params.
 *
 * The module is resolved from Request parameter
 *
 * @package Filter
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL2_Filter_LoadController extends SGL2_DecorateProcess
{
    public function process(SGL2_Request $input, SGL2_Response $output)
    {
        $moduleName = $input->get('moduleName');
        $controllerName = $input->get('controller');

        $ctlr = SGL2_Inflector::getControllerClassName($controllerName);
        $ctlrPath = SGL2_MOD_DIR . '/' . $moduleName . '/classes/';

        //  build path to controller class
        $classPath = $ctlrPath . $ctlr . '.php';
        try {
            SGL2_File::load($classPath);
            SGL2_Registry::set('controller', new $ctlr);
        } catch (Exception $e) {
            throw new Exception("Controller '$ctlr' could not be found at $classPath");
        }
        $this->processRequest->process($input, $output);
    }
}

?>