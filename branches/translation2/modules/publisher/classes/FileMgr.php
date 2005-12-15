<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
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
// | Seagull 0.5                                                               |
// +---------------------------------------------------------------------------+
// | FileMgr.php                                                               |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: FileMgr.php,v 1.15 2005/03/14 02:21:46 demian Exp $

require_once SGL_ENT_DIR . '/Document.php';
require_once SGL_CORE_DIR . '/Download.php';

/**
 * For basic file operations.
 *
 * @package publisher
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.15 $
 * @since   PHP 4.1
 */
class FileMgr extends SGL_Manager
{
    function FileMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $this->module           = 'publisher';
        $this->pageTitle        = 'File Manager';

        $this->_aActionsMapping =  array(
            'download'   => array('download'), 
            'downloadZipped'   => array('downloadZipped'), 
            'view'   => array('view'), 
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

    function _download(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $document = & new DataObjects_Document();
        $document->get($input->assetID);
        $fileName = SGL_UPLOAD_DIR . '/' . $document->name;
        $mimeType = $document->mime_type;
        $dl = &new SGL_Download();
        $dl->setFile($fileName);
        $dl->setContentType($mimeType);
        $dl->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT, $document->name);
        $dl->setAcceptRanges('none');
        $error = $dl->send();
        if (PEAR::isError($error)) {
            SGL::raiseError('There was an error attempting to download the file', 
                SGL_ERROR_NOFILE);
        }
    }

    function _downloadZipped(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        require_once SGL_LIB_DIR . '/other/Zip.php';
        $document = & new DataObjects_Document();
        $document->get($input->assetID);
        $fileName = SGL_UPLOAD_DIR . '/' . $document->name;
        $buffer = file_get_contents($fileName);
        $zip = & new Zip();
        $zip->addFile($buffer, basename($fileName));
        $fileData = $zip->file();
        $dl = &new SGL_Download();
        $dl->setData($fileData);
        $dl->setContentType('application/zip');
        $dl->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT, $document->name . '.zip');
        $dl->setAcceptRanges('none');
        $dl->setContentTransferEncoding('binary');
        $error = $dl->send();
        exit;
    }

    function _view(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'docBlank.html';
        $document = & new DataObjects_Document();
        $document->get($input->assetID);
        $fileName = SGL_UPLOAD_DIR . '/' . $document->name;
        if (!@file_exists($fileName)) {
            SGL::raiseError('The specified file does not appear to exist', 
                SGL_ERROR_NOFILE);
            return false;
        }
        $mimeType = $document->mime_type;
        $dl = &new SGL_Download();
        $dl->setFile($fileName);
        $dl->setContentType($mimeType);
        $dl->setContentDisposition(HTTP_DOWNLOAD_INLINE, $document->name);
        $dl->setAcceptRanges('none');
        $error = $dl->send();
        if (PEAR::isError($error)) {
            SGL::raiseError('There was an error displaying the file', 
                SGL_ERROR_NOFILE);
        exit;
        }
    }
}
?>
