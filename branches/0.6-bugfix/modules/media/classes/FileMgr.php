<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Seagull Systems                                       |
// | All rights reserved.                                                      |
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software               |
// | Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301,|
// | USA                                                                       |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | FileMgr.php                                                               |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@seaugllproject.org>                         |
// +---------------------------------------------------------------------------+

require_once 'DB/DataObject.php';
require_once SGL_CORE_DIR . '/Download.php';

/**
 * For basic file operations.
 *
 * @package seagull
 * @subpackage media
 * @author  Demian Turner <demian@seaugllproject.org>
 */
class FileMgr extends SGL_Manager
{
    function FileMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle        = 'File Manager';
        $this->_aActionsMapping =  array(
            'download'          => array('download'),
            'downloadZipped'    => array('downloadZipped'),
            'view'              => array('view'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated        = true;
        $input->error           = array();
        $input->pageTitle       = $this->pageTitle;

        //  form vars
        $input->action          = $req->get('action');
        $input->submit          = $req->get('submit');
        $input->assetID         = $req->get('frmAssetID');
    }

    function _cmd_download(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $media = DB_DataObject::factory($this->conf['table']['media']);
        $media->get($input->mediaId);
        $fileName = SGL_UPLOAD_DIR . '/' . $media->file_name;
        $mimeType = $media->mime_type;
        $dl = &new SGL_Download();
        $dl->setFile($fileName);
        $dl->setContentType($mimeType);
        $dl->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT, $media->file_name);
        $dl->setAcceptRanges('none');
        $error = $dl->send();
        if (PEAR::isError($error)) {
            SGL::raiseError('There was an error attempting to download the file',
                SGL_ERROR_NOFILE);
        }
        exit;
    }

    function _cmd_downloadZipped(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        require_once SGL_LIB_DIR . '/other/Zip.php';
        $media = DB_DataObject::factory($this->conf['table']['media']);
        $media->get($input->mediaId);
        $fileName = SGL_UPLOAD_DIR . '/' . $media->file_name;
        $buffer = file_get_contents($fileName);
        $zip = & new Zip();
        $zip->addFile($buffer, basename($fileName));
        $fileData = $zip->file();
        $dl = &new SGL_Download();
        $dl->setData($fileData);
        $dl->setContentType('application/zip');
        $dl->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT, $media->file_name . '.zip');
        $dl->setAcceptRanges('none');
        $dl->setContentTransferEncoding('binary');
        $error = $dl->send();
        exit;
    }

    function _cmd_view(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'docBlank.html';
        $media = DB_DataObject::factory($this->conf['table']['media']);
        $media->get($input->mediaId);
        $fileName = SGL_UPLOAD_DIR . '/' . $media->file_name;
        $mimeType = $media->mime_type;
        return $this->_view($fileName, $mimeType);
    }

    function _view($fileName, $mimeType)
    {
        if (!@is_file($fileName)) {
            SGL::raiseError('The specified file does not appear to exist',
                SGL_ERROR_NOFILE);
            return false;
        }
        $dl = &new SGL_Download();
        $dl->setFile($fileName);
        $dl->setContentType($mimeType);
        $dl->setContentDisposition(HTTP_DOWNLOAD_INLINE, $fileName);
        $dl->setAcceptRanges('none');
        $error = $dl->send();
        if (PEAR::isError($error)) {
            SGL::raiseError('There was an error displaying the file',
                SGL_ERROR_NOFILE);
        }
        exit;
    }
}
?>
