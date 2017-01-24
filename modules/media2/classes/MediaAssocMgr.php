<?php

require_once SGL_CORE_DIR . '/Delegator.php';
require_once dirname(__FILE__) . '/Media2DAO.php';

/**
 * Media association manager.
 *
 * @package media2
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class MediaAssocMgr extends SGL_Manager
{
    public function __construct()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle      = 'Media Association';
        $this->masterTemplate = 'master.html';
        $this->template       = 'mediaassocList.html';

        $this->_aActionsMapping = array(
            'list' => array('list'),
        );

        $this->da = new SGL_Delegator();
        $this->da->add(Media2DAO::singleton());
    }

    public function validate(SGL_Request $req, SGL_Registry $input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated       = true;
        $input->pageTitle      = $this->pageTitle;
        $input->template       = $this->template;
        $input->masterTemplate = $this->masterTemplate;
        $input->action         = $req->get('action')
            ? $req->get('action') : 'list';

        // redirect to caller
        $input->redir = $req->get('redir');

        // limit media by media type
        $input->typeId = $req->get('typeId');

        // 'category', 'event', etc
        $input->entity   = $req->get('entity');
        $input->entityId = $req->get('entityId');
    }

    public function display(SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    public function _cmd_list(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aMedias = $this->da->getMedias($input->typeId);
        $mediaId = $this->da->getAssocMediaByEntity($input->entity, $input->entityId);

        $output->aMedias = $aMedias;
        $output->mediaId = $mediaId;
        $output->addJavascriptFile(array(
            'media2/js/jquery/plugins/jquery.dimensions.js',
            'media2/js/jquery/plugins/jquery.shadow.js',
            'media2/js/jquery/plugins/jquery.ifixpng.js',
            'media2/js/jquery/plugins/jquery.fancyzoom.js',
            'js/jquery/plugins/jquery.form.js',
            'media2/js/Media2.js',
            'media2/js/Media2/List.js',
            'media2/js/Media2/Assoc.js',
        ));
        $output->addOnLoadEvent('Media2.Assoc.init()');
    }
}
?>