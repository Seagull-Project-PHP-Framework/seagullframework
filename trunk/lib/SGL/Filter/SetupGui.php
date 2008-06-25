<?php
/**
 * Setup which admin Graphical User Interface to use.
 *
 * @package Task
 */
class SGL_Filter_SetupGui extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $this->processRequest->process($input, $output);

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!SGL::runningFromCLI()) {
            $mgrName = SGL_Inflector::caseFix(get_class($output->manager));
            $adminGuiAllowed = $adminGuiRequested = false;

            //  setup which GUI to load depending on user and manager
            $output->adminGuiAllowed = false;

            // first check if userRID allows to switch to adminGUI
            if (SGL_Session::hasAdminGui()) {
                $adminGuiAllowed = true;
            }

            $c = &SGL_Config::singleton();
            $conf = $c->getAll();
            if (!$c->get($mgrName)) {
                //  get current module
                $req = &SGL_Request::singleton();
                $moduleName = $req->getModuleName();

                //  load current module's config if not present
                $conf = $c->ensureModuleConfigLoaded($moduleName);

                if (PEAR::isError($conf)) {
                    SGL::raiseError('could not locate module\'s config file',
                        SGL_ERROR_NOFILE);
                }
            }
            // then check if manager requires to switch to adminGUI
            if (isset( $conf[$mgrName]['adminGuiAllowed'])
                    && $conf[$mgrName]['adminGuiAllowed']) {
                $adminGuiRequested = true;

                //  check for adminGUI override in action
                if (isset($output->overrideAdminGuiAllowed) && $output->overrideAdminGuiAllowed) {
                    $adminGuiRequested = false;
                }
            }
            if ($adminGuiAllowed && $adminGuiRequested) {

                // if adminGUI is allowed then change theme TODO : put the logical stuff in another class/method
                $output->adminGuiAllowed = true;
                $output->theme = $conf['site']['adminGuiTheme'];
                $output->masterTemplate = 'admin_master.html';
                $output->template = 'admin_' . $output->template;
                if (isset($output->submitted) && $output->submitted) {
                    $output->addOnLoadEvent("formErrorCheck()");
                }
            }
        }
    }
}
?>