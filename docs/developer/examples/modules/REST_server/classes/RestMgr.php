<?php
require_once SGL_MOD_DIR . '/REST_server/classes/REST_serverDAO.php';

/**
 * REST server.
 *
 * @package seagull
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class RestMgr extends SGL_Manager
{
    function RestMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->da = &REST_serverDAO::singleton();
        $this->pageTitle = 'REST Server';
        $this->_aActionsMapping =  array(
            'get_foo'   => array('get_foo'),
            'save_foo'  => array('save_foo'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated        = true;
        $input->masterTemplate  = $this->masterTemplate;
        $input->pageTitle       = $this->pageTitle;
        $input->template        = $this->template;

        //  request vars
        $input->action          = ($req->get('action')) ? $req->get('action') : 'list';
        $input->gid             = $req->get('gid');
        $input->entityId        = (int)$req->get('id');
        $input->contentType     = 'xml'; // the content type must be set to XML
    }

    function _cmd_get_foo(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $oData = new DataStructExample();
        $input->result = $oData; // the returned data must be set on the 'result' $input property
    }

    function _cmd_save_foo(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $oData = new DataStructExample();
        $input->result = $oData; // the returned data must be set on the 'result' $input property
    }
}
?>