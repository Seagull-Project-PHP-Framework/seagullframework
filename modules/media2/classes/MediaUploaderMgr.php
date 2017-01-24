<?php

require_once SGL_CORE_DIR . '/Image.php';
require_once dirname(__FILE__) . '/Media2DAO.php';
require_once SGL_MOD_DIR . '/media2/lib/Media/Util.php';

/**
 * Media uploader.
 *
 * @package media2
 * @author Thomas Goetz
 * @author Demian Turner <demian@phpkitchen.com>
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class MediaUploaderMgr extends SGL_Manager
{
    public function __construct()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->_aActionsMapping = array(
            'upload' => array('upload'),
        );

        $this->da = Media2DAO::singleton();
    }

    public function validate(SGL_Request $req, SGL_Registry $input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated = true;
        $input->action   = $req->get('action') ? $req->get('action') : 'upload';

        // default response
        $input->oResponse->isUploaded = false;

        $input->fkId   = $req->get('fkId');
        $input->typeId = $req->get('typeId');
        $input->oMedia = isset($_FILES) && isset($_FILES['filedata'])
            ? (object) $_FILES['filedata']
            : null;

        $valid = false;
        if ($input->action == 'upload') {
            // file must be uploaded without errors
            // and type should be specified
            if (!empty($input->oMedia) && empty($input->oMedia->error)) {
                // check for allowed mime type
                $aMimeTypes = !empty($input->typeId)
                    ? $this->da->getMimeTypesByMediaTypeId($input->typeId)
                    : $this->da->getMimeTypes();
                    
                $mimeType = SGL_Media_Util::getFileIdent(
                    $input->oMedia->tmp_name, $aMimeTypes);
                if (!$mimeType
                    && SGL_Media_Util::isTextFile($input->oMedia->tmp_name))
                {
                    $mimeType = 'text/plain';
                }
                if ($mimeType) {
                    $input->oMedia->mime_type = $mimeType;
                    $valid = true;
                } else {
                    SGL::raiseError('not allowed content type');
                }
            }
        } else {
            $valid = true;
        }
        $this->validated = $valid;
    }

    public function display(SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aError = array();
        while (SGL_Error::count()) {
            $aError = array(
                'message' => SGL_Error::pop()->getMessage(),
                'type'    => SGL_MESSAGE_ERROR
            );
        }
        if (!empty($aError)) {
            $output->oResponse->aMsg = $aError;
        }

        // response
        $output->data = json_encode($output->oResponse);
    }

    public function _cmd_upload(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // generate file name
        $userId    = SGL_Session::getUid();
        $fileName  = SGL_Media_Util::getUniqueString($userId . $input->oMedia->tmp_name);
        $ext       = $this->da->getExtensionByMimeType($input->oMedia->mime_type);
        $fileName .= '.' .$ext;

        // add media record
        $aFields['file_name']  = $fileName;
        $aFields['file_size']  = $input->oMedia->size;
        $aFields['mime_type']  = $input->oMedia->mime_type;
        $aFields['name']       = $input->oMedia->name;
        $aFields['created_by'] = $userId;
        $aFields['updated_by'] = $aFields['created_by'];
        if (!empty($input->typeId)) {
            $aFields['media_type_id'] = $input->typeId;
        }
        if (!empty($input->fkId)) {
            $aFields['fk_id'] = $input->fkId;
        }
        $mediaId = $this->da->addMedia($aFields);

        // update response
        if (!PEAR::isError($mediaId)) {
            $output->oResponse->isUploaded = true;
            $output->oResponse->mediaId    = $mediaId;
        }

        // upload image
        if (SGL_Media_Util::isImageMimeType($input->oMedia->mime_type)) {
            // get container name by media type
            $container = !empty($input->typeId)
                ? $this->da->getMediaTypeById($input->typeId)
                : 'default';
            $container = strtolower(str_replace(' ', '_', $container));

            $confFile = SGL_Config::locateCachedFile(
                SGL_MOD_DIR . '/media2/image.ini');

            // process image
            $oImage = new SGL_Image($fileName);
            $ok     = $oImage->init($confFile, $container);
            $ok     = $oImage->create($input->oMedia->tmp_name);

        // upload regular media
        } else {
            
            $c     = new SGL_Config();
            $aConf = $c->load(SGL_MOD_DIR . '/media2/image.ini');
            $uploadDir = SGL_VAR_DIR . '/../' . $aConf['product']['uploadDir'];
            $ok = SGL_Media_Util::ensureDirIsWritable($uploadDir);
            if (!PEAR::isError($ok)) {
                if (!($ok = @move_uploaded_file($input->oMedia->tmp_name,
                    $uploadDir . "/$fileName")))
                {
                    SGL::raiseError('can not move uploaded file');
                }
            }
        }
    }
}
?>
