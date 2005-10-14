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
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | DocumentMgr.php                                                           |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: DocumentMgr.php,v 1.47 2005/05/09 23:33:40 demian Exp $

require_once SGL_MOD_DIR . '/publisher/classes/PublisherBase.php';
require_once SGL_MOD_DIR . '/publisher/classes/FileMgr.php';
require_once SGL_MOD_DIR . '/navigation/classes/MenuBuilder.php';
require_once SGL_ENT_DIR . '/Category.php';
require_once SGL_ENT_DIR . '/Document.php';

/**
 * For performing operations on Document objects.
 *
 * @package publisher
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.47 $
 * @since   PHP 4.1
 * @todo    handle uploads with HTTP::Upload
 */
class DocumentMgr extends FileMgr
{
    var $_aAllowedFileTypes  = array();

    function DocumentMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->module       = 'publisher';
        $this->pageTitle    = 'Document Manager';
        $this->template     = 'documentManager.html';
        $this->_aAllowedFileTypes = array(
            'gif', 'jpeg', 'jpg', 'png',
            'doc', 'rtf', 'txt', 'pdf',
            'ppt', 'pps', 'xls', 'htm',
            'html', 'csv',
        );

        $this->_aActionsMapping =  array(
            'add'       => array('add'), 
            'insert'    => array('insert', 'redirectToDefault'),
            'edit'      => array('edit'), 
            'update'    => array('update', 'redirectToDefault'), 
            'delete'    => array('delete', 'redirectToDefault'), 
            'setDownload' => array('setDownload'), 
            'list'      => array('list'), 
            'view'      => array('view'), 
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated        = true;
        $input->error           = array();
        $input->pageTitle       = $this->pageTitle;
        $input->masterTemplate  = 'masterLeftCol.html';
        $input->template        = $this->template;
        $input->javascriptSrc   = array('TreeMenu.js');

        //  form vars
        $input->action          = ($req->get('action')) ? $req->get('action') : 'list';
        $input->submit          = $req->get('submit');
        $input->from            = ($req->get('frmFrom'))? $req->get('frmFrom'):0;
        $input->catID           = $req->get('frmCatID');
        $input->docCatID        = $req->get('frmDocumentCatID');
        $input->assetID         = $req->get('frmAssetID');
        $input->catChangeToID   = $req->get('frmCategoryChangeToID');
        $input->deleteArray     = $req->get('frmDelete');
        $input->queryRange      = $req->get('frmQueryRange');
        $input->totalItems      = $req->get('totalItems');

        //  request values for upload
        $input->assetFileArray        = $req->get('assetFile');
        $input->assetFileName         = $input->assetFileArray['name'];
        $input->assetFileType         = $input->assetFileArray['type'];
        $input->assetFileTmpName      = $input->assetFileArray['tmp_name'];
        $input->assetFileSize         = $input->assetFileArray['size'];

        //  determine user type
        $input->isAdmin = (SGL_HTTP_Session::getUserType() == SGL_ADMIN) ? 
            true : false;

        //  request values for save upload
        $input->document = (object)$req->get('document');
        $input->document->orig_name = (isset($input->document->orig_name))
            ? $input->document->orig_name
            : '';
        $input->assetName = (isset($input->document->name) && $input->document->name != '')
            ? $input->document->name
            : $input->document->orig_name;

        //  if document has been uploaded
        if ($input->assetFileName != '') {
            $ext = end(explode('.', $input->assetFileName));

            //  check uploaded file is of valid type
            if (!in_array(strtolower($ext), $this->_aAllowedFileTypes)) {
                $aErrors[] = SGL_String::translate('Error: Not a recognised file type');
            }
            //  ... and does not exist in uploads dir
            if (is_readable(SGL_UPLOAD_DIR . '/' . $input->assetFileName)) {
                $aErrors[] = SGL_String::translate('Error: A file with this name already exists');
            }
        }

        //  if form submitted and errors exist
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg($aErrors[0]);
            $input->template = 'documentMgrAdd.html';
            $input->error = $aErrors;

            //  prepare breadcrumbs and category changer for popup window
            $menu = & new MenuBuilder('SelectBox');
            $htmlOptions = $menu->toHtml();
            $input->aCategories = $htmlOptions;
            $input->currentCat = $input->docCatID;
            $input->breadCrumbs = $menu->getBreadCrumbs($input->docCatID, false);
//  FIXME
            $input->save = '';
            $input->assetTypeID = '';
            $this->validated = false;
        }
        //  session var persistence
        PublisherBase::maintainState($input);

        //  if document category has been changed, update input, 
        //  or if catChangeToID is 0 leave catID as is 
        //  (in the case of logged on user browsing documents)
        if (!$input->docCatID) {  //  true in case of adding doc
            $input->docCatID = 
                ($input->catID == $input->catChangeToID || !$input->catChangeToID)
                ? $input->catID : $input->catChangeToID;
        } else {                  //  true in case of editing
            $input->docCatID = 
                ($input->docCatID == $input->catChangeToID || !$input->catChangeToID)
                ? $input->docCatID : $input->catChangeToID;
        }
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get current navigation cat name for publisher subnav
        $category = & new DataObjects_Category();
        $category->get($output->catID);
        $output->catName = $category->label;
        $output->queryRange = PublisherBase::getQueryRange($output);
    }

    function _add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->docCatID = $input->docCatID;
        $output->template = 'documentMgrAdd.html';
        if ($input->submit) { // if file uploaded 
        
            //  check id dir exists, create if not
            if (!is_writable(SGL_UPLOAD_DIR)) {
                include_once 'System.php';
                $success = System::mkDir(array(SGL_UPLOAD_DIR));
                if (!$success) {
                    SGL::raiseError('The upload directory does not appear to be writable, please give the
                    webserver permissions to write to it', SGL_ERROR_FILEUNWRITABLE, PEAR_ERROR_DIE);
                }
            }
            copy($_FILES['assetFile']['tmp_name'], SGL_UPLOAD_DIR . '/' . $input->assetFileName);
            $output->save = "true";
            $output->assetTypeID = $this->_mime2AssetType($input->assetFileType);
            $output->assetTypeName = $this->_getType($output->assetTypeID);
            if ($input->isAdmin) {
                //  prepare breadcrumbs and category changer
                $menu = & new MenuBuilder('SelectBox');
                $htmlOptions = $menu->toHtml();
                $output->aCategories = $htmlOptions;
                $output->currentCat = $input->docCatID;

                $output->breadCrumbs = $menu->getBreadCrumbs($input->docCatID, false);
            }           
        } else { // display upload screen
            if ($input->isAdmin) {     
               
                //  prepare breadcrumbs and category changer
                $menu = & new MenuBuilder('SelectBox');
                $htmlOptions = $menu->toHtml();
                $output->aCategories = $htmlOptions;
                $output->currentCat = $input->catID;
    
                $output->breadCrumbs = $menu->getBreadCrumbs($input->catID, false);
            }
            $output->save = false;
            $output->assetTypeID = 0;
        }
    }

    function _insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $asset = & new DataObjects_Document();
        $asset->setFrom($input->document);
        $dbh = $asset->getDatabaseConnection();
        $asset->document_id = $dbh->nextId($this->conf['table']['document']);
        $asset->category_id = $input->docCatID;
        $asset->date_created  = SGL_Date::getTime();
        $asset->name = SGL_String::censor($asset->name);
        $asset->description = SGL_String::censor($asset->description);
        $asset->insert();
        
        //  if file has been renamed
        if ($input->document->orig_name != $asset->name) {
            rename(SGL_UPLOAD_DIR . '/' . $input->document->orig_name, 
                SGL_UPLOAD_DIR . '/' . $asset->name);
        $output->asset = $asset;
        }
    }

    function _edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'documentMgrEdit.html';
        $document = & new DataObjects_Document();
        $document->get($input->assetID);
        $document->getLinks('link_%s');

        //  prepare breadcrumbs and category changer
        if ($input->isAdmin) {
            $menu = & new MenuBuilder('SelectBox');
            $htmlOptions = $menu->toHtml();
            $output->aCategories = $htmlOptions;
            $output->currentCat = $document->category_id;

            $output->breadCrumbs = $menu->getBreadCrumbs($document->category_id, false);
        }
        $output->asset = $document;
    }

    function _setDownload(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if ($this->conf['DocumentMgr']['zipDownloads']) {
            $this->_downloadZipped($input, $output);
        } else {
            $this->_download($input, $output);
        }
    }

    function _update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $document = & new DataObjects_Document();
        $document->get($input->assetID);
        $document->setFrom($input->document);
        $document->category_id = $input->docCatID;
        $document->name = SGL_String::censor($document->name);
        $document->description = SGL_String::censor($document->description);
        $document->update();

        //  if file has been renamed
        if ($input->document->orig_name != $document->name) {
            rename( SGL_UPLOAD_DIR . '/' . $input->document->orig_name, 
                    SGL_UPLOAD_DIR . '/' . $document->name);
        }
        $output->asset = $document;
        SGL::raiseMsg('The asset has successfully been updated');
    }

    function _delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'documentMgrEdit.html';

        //  delete physical file
        foreach ($input->deleteArray as $index => $assetID) {
            $document = & new DataObjects_Document();
            $document->get($assetID);
            if (file_exists(SGL_UPLOAD_DIR . '/' . $document->name)) {
                @unlink(SGL_UPLOAD_DIR . '/' . $document->name);
            }
            $document->delete();
            unset($document);
        }
        SGL::raiseMsg('The asset has successfully been deleted');
    }

    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
                
        $rangeWhereClause = ($input->queryRange == 'all')
            ? ''
            : " AND c.category_id = $input->catID";
        $query = "
            SELECT document_id, c.category_id, d.document_type_id,
                d.name, file_size, mime_type,
                d.date_created, description,
                dt.name AS document_type_name,
                u.username AS document_added_by
            FROM
                {$this->conf['table']['document']} d, {$this->conf['table']['category']} c, 
                {$this->conf['table']['document_type']} dt, {$this->conf['table']['user']} u
            WHERE dt.document_type_id = d.document_type_id
            AND c.category_id = d.category_id
            AND u.usr_id = d.added_by
                    $rangeWhereClause
            ORDER BY d.date_created DESC";

        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'      => 'Sliding',
            'delta'     => 3,
            'perPage'   => $limit,
            'totalItems'=> $input->totalItems,
        );
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);
        $output->aPagedData = $aPagedData;
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }

        //  prepare data for publisher subnav
        $output->addOnLoadEvent("document.getElementById('frmResourceChooser').documents.disabled = true");

        //  prepare breadcrumbs
        $menu = & new MenuBuilder('SelectBox');
        $output->breadCrumbs = $menu->getBreadCrumbs($input->docCatID);
    }

    function _mime2AssetType($mimeType)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        switch ($mimeType) {

        case 'text/plain':
        case 'application/msword':              $assetTypeID = 1; break;
        case 'application/rtf':                 $assetTypeID = 1; break;
        case 'application/vnd.ms-excel':        $assetTypeID = 2; break;
        case 'application/vnd.ms-powerpoint':   $assetTypeID = 3; break;
        case 'text/html':                       $assetTypeID = 4; break;
        //  jpgs on windows
        case 'image/pjpeg':                     $assetTypeID = 5; break;
        //  jpgs on linux
        case 'image/jpeg':                      $assetTypeID = 5; break;
        case 'image/x-png':                     $assetTypeID = 5; break;
        case 'image/gif':                       $assetTypeID = 5; break;
        case 'application/pdf':                 $assetTypeID = 6; break;
        default:
            $assetTypeID = 7;   //  unknown
        }
        return $assetTypeID;
    }

    function _getType($documentTypeID)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "  SELECT  name
                    FROM    " . $this->conf['table']['document_type'] . "
                    WHERE   document_type_id = $documentTypeID";
        return $this->dbh->getOne($query);
    }
}
?>