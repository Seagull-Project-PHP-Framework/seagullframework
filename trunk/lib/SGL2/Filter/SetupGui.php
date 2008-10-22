<?php
/**
 * Setup which admin Graphical User Interface to use.
 *
 * @package Task
 * @todo only  present if admin module installed
 */
class SGL2_Filter_SetupGui extends SGL2_DecorateProcess
{
    public function process(SGL2_Request $input, SGL2_Response $output)
    {
        $this->processRequest->process($input, $output);

        $ctrlName = $input->getControllerName();
        $adminGuiAllowed = false;

        //  setup which GUI to load depending on user and controller
        $output->adminGuiAllowed = false;

        // first check if role ID allows to switch to adminGUI
        if (SGL2_Session::hasAdminGui()) {
            $adminGuiAllowed = true;
        }
        // then check if controller allows to switch to adminGUI
        if (SGL2_Config::get("$ctrlName.adminGuiAllowed")) {
            $adminGuiRequested = true;

            // if adminGUI is allowed then change theme
            $output->adminGuiAllowed = true;
            $output->theme = SGL2_Config::get('site.adminGuiTheme');
            $output->masterTemplate = 'admin_master.html';
            $output->template = 'admin_' . $output->template;
            if (!empty($output->submitted)) {
                $output->addOnLoadEvent("formErrorCheck()");
            }
        }
    }
}
?>