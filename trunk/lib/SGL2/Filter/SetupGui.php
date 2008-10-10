<?php
/**
 * Setup which admin Graphical User Interface to use.
 *
 * @package Task
 */
class SGL2_Filter_SetupGui extends SGL2_DecorateProcess
{
    public function process(SGL2_Request $input, SGL2_Response $output)
    {
        $this->processRequest->process($input, $output);

        if ($input->getType() != SGL2_Request::CLI) {
            $mgrName = SGL2_Inflector::caseFix(get_class($output->manager));
            $adminGuiAllowed = $adminGuiRequested = false;

            //  setup which GUI to load depending on user and manager
            $output->adminGuiAllowed = false;

            // first check if userRID allows to switch to adminGUI
            if (SGL2_Session::hasAdminGui()) {
                $adminGuiAllowed = true;
            }

            $c = &SGL2_Config::singleton();
            $conf = $c->getAll();
            if (!$c->get($mgrName)) {
                //  get current module
                $req = SGL2_Request::singleton();
                $moduleName = $req->getModuleName();

                //  load current module's config if not present
                $conf = $c->ensureModuleConfigLoaded($moduleName);

                if (PEAR::isError($conf)) {
                    SGL::raiseError('could not locate module\'s config file',
                        SGL2_ERROR_NOFILE);
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