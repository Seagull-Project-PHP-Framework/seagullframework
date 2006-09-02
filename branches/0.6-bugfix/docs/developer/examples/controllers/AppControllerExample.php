<?php
//  override the SGL app controller to introduce custom functionality
//  or see etc/sglBridge.php for a lightweight eg used for testing

class MyFrontController extends SGL_FrontController
{
    /**
     * Main invocation, init tasks plus main process.
     *
     */
    function run()
    {
        if (!defined('SGL_INITIALISED')) {
            SGL_FrontController::init();
        }
        //  assign request to registry
        $input = &SGL_Registry::singleton();
        $req = SGL_Request::singleton();

        $err = $req->init();
        if (PEAR::isError($err)) {
            //  stop with error page
            SGL::displayStaticPage($err->getMessage());
        }
        $input->setRequest($req);

        $process =
            new SGL_Process_Init(
            new SGL_Process_SetupORM(
            new SGL_Process_StripMagicQuotes(
            new SGL_Process_DiscoverClientOs(
            new SGL_Process_ResolveManager(
            new SGL_Process_CreateSession(
            new SGL_Process_SetupLangSupport(
            new SGL_Process_SetupPerms(
            new SGL_Process_AuthenticateRequest(
            new SGL_Process_BuildHeaders(
            new SGL_Process_SetupLocale(
            new SGL_Process_DetectDebug(
            new SGL_Process_DetectBlackListing(
            /*
             ... your Process filters here
            */
            new SGL_MainProcess()
            )))))))))))));

        $process->process($input);
    }
}
?>