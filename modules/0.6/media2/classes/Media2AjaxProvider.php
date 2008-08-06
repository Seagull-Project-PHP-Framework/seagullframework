<?php

require_once SGL_CORE_DIR . '/AjaxProvider2.php';
require_once SGL_CORE_DIR . '/Image.php';
require_once dirname(__FILE__) . '/Media2DAO.php';
require_once SGL_MOD_DIR . '/media2/lib/Media/Util.php';

/**
 * Ajax provider.
 *
 * @package media2
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class Media2AjaxProvider extends SGL_AjaxProvider2
{
    public function __construct()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::__construct();

        $this->da->add(Media2DAO::singleton());
    }

    public function process(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // turn off autocommit
        $this->dbh->autoCommit(false);

        $ok = parent::process($input, $output);
        DB::isError($ok)
            ? $this->dbh->rollback()
            : $this->dbh->commit();

        // turn autocommit on
        $this->dbh->autoCommit(true);

        return $ok;
    }

    /**
     * Ensure the current user can perform requested action.
     *
     * @param integer $requestedUserId
     *
     * @return boolean
     */
    protected function _isOwner($requestedUserId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        /*
        $ok = SGL_Session::getRoleId() == SGL_ADMIN
            || SGL_Session::getRoleId() == SGL_MEMBER;
        if ($ok) {
            $ok = $this->_isOwnerResource($requestedUserId);
        }
        return $ok;
        */
        return true;
    }

    /*
    protected function _isOwnerResource($requestedUserId)
    {
        return false;
    }
    */

    public function getMediaEditScreen(SGL_Registry $input, SGL_Output $output)
    {
        $mediaId = $this->req->get('mediaId');

        $oMedia = $this->da->getMediaById($mediaId);

        $output->html = $this->_renderTemplate($output, array(
            'masterTemplate' => 'media2Edit.html',
            'oMedia'         => $oMedia
        ));
    }

    public function linkMediaAndView(SGL_Registry $input, SGL_Output $output)
    {
        $mediaId = $this->req->get('mediaId');
        $fkId    = $this->req->get('mediaId');
        $typeId  = $this->req->get('typeId');

        // link media
        $ok = $this->da->linkMediaToFk($mediaId, $fkId, $typeId);

        // get template
        $this->getMediaView($input, $output);
    }

    public function getMediaView(SGL_Registry $input, SGL_Output $output)
    {
        $mediaId = $this->req->get('mediaId');

        $oMedia = $this->da->getMediaById($mediaId);

        $output->html = $this->_renderTemplate($output, array(
            'masterTemplate' => 'media2_item.html',
            'oMedia'         => $oMedia,
            'aMedias'        => array($oMedia),
            'k'              => 0
        ));
    }

    public function updateMedia(SGL_Registry $input, SGL_Output $output)
    {
        $aMedia = $this->req->get('aMedia');

        $aMedia['updated_by'] = SGL_Session::getUid();
        $ok = $this->da->updateMediaById($aMedia['media_id'], $aMedia);
        if (!PEAR::isError($ok)) {
            $output->isUpdated = true;
        } else {
            $output->isUpdated = false;
        }
    }

    public function deleteMediaById(SGL_Registry $input, SGL_Output $output)
    {
        $mediaId = $this->req->get('mediaId');

        $oMedia = $this->da->getMediaById($mediaId);
        $ok     = $this->da->deleteMediaById($mediaId);

        // delete image
        if (SGL_Media_Util::isImageMimeType($oMedia->mime_type)) {
            $aContainers = parse_ini_file(SGL_MOD_DIR . '/media2/image.ini', true);

            // get container name by media type
            $container = !empty($oMedia->media_type_id)
                ? $this->da->getMediaTypeById($oMedia->media_type_id)
                : 'default';
            if (!isset($aContainers[$container])) {
                $container = 'default';
            }
            $container = strtolower(str_replace(' ', '_', $container));

            $oImage = new SGL_Image($oMedia->file_name);
            $ok     = $oImage->init(SGL_MOD_DIR . '/media2/image.ini', $container);
            $ok     = $oImage->delete();

        // delete regular media
        } else {
            @unlink(SGL_UPLOAD_DIR . '/' . $oMedia->file_name);
        }
    }
}
?>