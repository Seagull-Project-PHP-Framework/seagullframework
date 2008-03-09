<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2008, Demian Turner                                         |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | o Redistributions of source code must retain the above copyright          |
// |   notice, this list of conditions and the following disclaimer.           |
// | o Redistributions in binary form must reproduce the above copyright       |
// |   notice, this list of conditions and the following disclaimer in the     |
// |   documentation and/or other materials provided with the distribution.    |
// | o The names of the authors may not be used to endorse or promote          |
// |   products derived from this software without specific prior written      |
// |   permission.                                                             |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT         |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR     |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT      |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,     |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT          |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,     |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE     |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.      |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | FileMgr.php                                                               |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: FileMgr.php,v 1.15 2005/03/14 02:21:46 demian Exp $

require_once 'DB/DataObject.php';
require_once SGL_CORE_DIR . '/Download.php';
require_once SGL_CORE_DIR . '/Category.php';

/**
 * For basic file operations.
 *
 * @package publisher
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class FileMgr extends SGL_Manager
{
    function FileMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle        = 'File Manager';
        $this->_aActionsMapping =  array(
            'download'       => array('download'),
            'downloadZipped' => array('downloadZipped'),
            'view'           => array('view'),
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
        $input->submitted       = $req->get('submitted');
        $input->assetId         = $req->get('frmAssetID');
    }

    function _cmd_download(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $document = $this->getDocument($input->assetId);
        if (PEAR::isError($document)) {
            return $document;
        }
        $fileName = SGL_UPLOAD_DIR . '/' . $document->name;
        $mimeType = $document->mime_type;
        $download = &new SGL_Download();
        $download->setFile($fileName);
        $download->setContentType($mimeType);
        $download->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT, $document->name);
        $download->setAcceptRanges('none');
        $error = $download->send();
        if (PEAR::isError($error)) {
            SGL::raiseError('There was an error attempting to download the file',
                SGL_ERROR_NOFILE);
        }
    }

    function _cmd_downloadZipped(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // for the case PHP is compiled without gzip support
        // redirect to standard download method
        if (!function_exists('gzcompress'))  {
            return SGL::raiseError('You need PHP compiled with zlib to use this feature',
                SGL_ERROR_INVALIDCALL);
        }
        require_once SGL_LIB_DIR . '/other/Zip.php';
        $document = $this->getDocument($input->assetId);
        if (PEAR::isError($document)) {
            return $document;
        }
        $fileName = SGL_UPLOAD_DIR . '/' . $document->name;
        $buffer = file_get_contents($fileName);
        $zip = & new Zip();
        $zip->addFile($buffer, basename($fileName));
        $fileData = $zip->file();
        $download = &new SGL_Download();
        $download->setData($fileData);
        $download->setContentType('application/zip');
        $download->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT, $document->name . '.zip');
        $download->setAcceptRanges('none');
        $download->setContentTransferEncoding('binary');
        $error = $download->send();
        if (PEAR::isError($error)) {
            SGL::raiseError('There was an error attempting to download the file',
                SGL_ERROR_NOFILE);
        }
        exit;
    }

    function _cmd_view(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $document = $this->getDocument($input->assetId);
        if (PEAR::isError($document)) {
            return $document;
        }
        $fileName = SGL_UPLOAD_DIR . '/' . $document->name;
        $mimeType = $document->mime_type;
        $download = &new SGL_Download();
        $download->setFile($fileName);
        $download->setContentType($mimeType);
        $download->setContentDisposition(HTTP_DOWNLOAD_INLINE, $document->name);
        $download->setAcceptRanges('none');
        $error = $download->send();
        if (PEAR::isError($error)) {
            SGL::raiseError('There was an error displaying the file',
                SGL_ERROR_NOFILE);
        }
        exit;
    }

   /*
    * Returns an DB_DataObject Object containing document data.
    *
    * Checks if the file exists and the user has read perms.
    *
    * @param assetId ID of Document
    */
    function getDocument($assetId)
    {
        $document = DB_DataObject::factory($this->conf['table']['document']);
        $document->get($assetId);

        //  check if user has rights for the category the file is in.
        $category = new SGL_Category();
        $category->load($document->category_id);
        if (!$category->hasPerms()) {
            return SGL::raiseError(
                'You do not have read permissions for this file',
                SGL_ERROR_INVALIDAUTH);
        }


        $fileName = SGL_UPLOAD_DIR . '/' . $document->name;
        if (!@is_file($fileName)) {
            return SGL::raiseError(
                'The specified file does not appear to exist',
                SGL_ERROR_NOFILE);
        }
        return $document;
    }
}
?>