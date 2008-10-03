<?php

/**
 * Resolves request params into Manager model object.
 *
 * The module is resolved from Request parameter
 *
 * @package Filter
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Filter_LoadController extends SGL_DecorateProcess
{
    public function process(SGL_Request $input, SGL_Response $output)
    {
        $moduleName = $input->get('moduleName');
        $controllerName = $input->get('controller');

        $ctlr = SGL_Inflector::getControllerClassName($controllerName);
        $ctlrPath = SGL_MOD_DIR . '/' . $moduleName . '/classes/';

        //  build path to controller class
        $classPath = $ctlrPath . $ctlr . '.php';
        if (SGL::isReadable($classPath)) {
            require $classPath;
            SGL_Registry::set('controller', new $ctlr);
        } else {
            throw new Exception("Controller '$ctlr' could not be found at $classPath");
        }

        $this->processRequest->process($input, $output);
    }
}

?>