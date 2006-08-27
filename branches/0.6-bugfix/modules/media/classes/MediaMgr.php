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
// | MediaMgr.php                                                              |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@seaugllproject.org>                         |
// +---------------------------------------------------------------------------+

require_once SGL_MOD_DIR . '/media/classes/FileMgr.php';
require_once 'DB/DataObject.php';

/**
 * For managing different media files.
 *
 * @package seagull
 * @subpackage media
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class MediaMgr extends FileMgr
{
	// add more of these from http://filext.com/
	var $_aIdents = array(
    	'application/pdf' => '25 50 44 46 2D 31 2E',
    	'application/msword' => 'D0 CF 11 E0 A1 B1 1A E1',
    	'application/zip' => '50 4B 03 04',
    	'video/mpeg' => '00 00 01 BA 21 00 01'
	);

    function MediaMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::FileMgr();

        $this->pageTitle    = 'Media Manager';
        $this->template     = 'mediaList.html';

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
        $input->template        = $this->template;
        $input->masterTemplate = 'masterLeftCol.html';

        //  form vars
        $input->action          = ($req->get('action')) ? $req->get('action') : 'list';
        $input->submitted       = $req->get('submit');
        $input->from            = ($req->get('frmFrom'))? $req->get('frmFrom') : 0;
        $input->mediaId         = $req->get('frmMediaId');
        $input->aDelete         = $req->get('frmDelete');

        //  Pager's total items value (maintaining it saves a count(*) on each request)
        $input->totalItems = $req->get('totalItems');

        //  request values for upload
        $input->aMedia              = $req->get('mediaFile');
        $input->mediaFileName       = $input->aMedia['name'];
        $input->mediaFileType       = $input->aMedia['type'];
        $input->mediaFileTmpName    = $input->aMedia['tmp_name'];
        $input->mediaFileSize       = $input->aMedia['size'];

        //  request values for save upload
        $input->media = (object)$req->get('media');
        $input->media->orig_name = (isset($input->media->orig_name))
            ? $input->media->orig_name
            : '';
        $input->mediaName = !empty($input->media->name)
            ? $input->media->name
            : $input->media->orig_name;

        //  if media has been uploaded
        if (!empty($input->mediaFileName)) {
            if ($mimeType = $this->getMimeType($input->mediaFileTmpName)) {
                $input->mediaFileName = $this->toValidFileName($input->mediaFileName,
                    $mimeType);
            } else {
                $aErrors['unrecognised_type'] = 'Not a recognised file type';
            }
            //  ... and does not exist in uploads dir
            if (is_readable(SGL_UPLOAD_DIR . '/' . $input->mediaFileName)) {
                $aErrors['already_exists'] = 'A file with this name already exists';
            }
        }
        //  if form submitted and errors exist
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please correct the following errors:', false);
            $input->template = 'mediaAdd.html';
            $input->error = $aErrors;

            //  FIXME
            $input->save = '';
            $input->fileTypeID = '';
            $this->validated = false;
        }
    }

    function toValidFileName($string, $mimeType)
    {
    	// remove the current extenion
    	$chopTo = strlen(strrchr($string, '.'));
    	$string = substr($string, 0, (strlen($string) - $chopTo));

    	// remove non-alpha characters
    	$newString = ereg_replace("[^A-Za-z0-9]", '_', $string);
    	$finalString = ereg_replace("[_]+","_", $newString);

    	// get the correct extension type for the file
    	$extension = $this->getMimeExtension($mimeType);

    	return $finalString . $extension;
    }

    function getMimeExtension($mimeType)
    {
    	$mime = array(
        	'application/msword'=>'.doc',
        	'image/gif'=>'.gif',
        	'image/jpeg'=>'.jpg',
        	'application/pdf'=>'.pdf',
        	'image/png'=>'.png',
        	'application/zip'=>'.zip',
        	'text/plain'=>'.txt'
        );

        return $mime[$mimeType];
    }

    function condense($value)
    {
    	return pack('H*', str_replace(' ', '', $value));
    }

    function getIdent($filename, $aHexIdents)
    {
    	// open the file for reading (binary)
    	$fp = fopen($filename, 'rb');
    	if (!$fp) {
    		return false;
    	}
    	// get the (converted to bin) hex identifier length to extract that amount
    	// of bytes from our uploaded file
    	$aBinIdents = array_map(array($this, 'condense'), $aHexIdents);
    	$aSizes = array_map('strlen', $aBinIdents);
    	$read = max($aSizes);

    	// store the read data
    	$data = fread($fp, $read);
    	fclose($fp);

    	// check our data against the array of catalogued file types $this->_aIdents
    	foreach ($aBinIdents as $type => $signature) {
    		$found = (substr($data, 0, strlen($signature)) === $signature);
    		if ($found) {
    			break;
    		}
    	}
    	return ($found ? $type : false);
    }

    function isTextFile($filename)
    {
    	if (!is_readable($filename)) {
    	    return false;
    	}
    	$data = file_get_contents($filename);
    	$bad = false;
    	for ($x = 0 , $y = strlen($data); !$bad && $x < $y; $x++) {
    		$bad = (ord($data{$x}) > 127);
    	}
    	return !$bad;
    }

    function getMimeType($filename)
    {
    	// is the file an image file
    	if ($fileInfo = getimagesize($filename)){
    		$ret = $fileInfo['mime'];

    	// is the file type listed in our catalogued types $this->_aIdents
    	} elseif ($mimeType = $this->getIdent($filename, $this->_aIdents)){
    		$ret = $mimeType;

    	// is the uploaded file a text file
    	} elseif ($this->isTextFile($filename)){
    		$ret = 'text/plain';

    	// not a recognised file type by this class
    	} else {
    		$ret = false;
    	}
    	return $ret;
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    function ensureUploadDirWritable($targetDir)
    {
        //  check if uploads dir exists, create if not
        if (!is_writable($targetDir)) {
            require_once 'System.php';
            $success = System::mkDir(array($targetDir));
            if (!$success) {
                SGL::raiseError('The upload directory does not appear to be writable, please give the
                webserver permissions to write to it', SGL_ERROR_FILEUNWRITABLE);
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    function _cmd_add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'mediaAdd.html';
        if ($input->submitted) { // if file uploaded

            if (!$this->ensureUploadDirWritable(SGL_UPLOAD_DIR)) {
                return false;
            }

            $uniqueName = md5($input->mediaFileName . SGL_Session::getUid() . SGL_Date::getTime());
            $targetLocation = SGL_UPLOAD_DIR . '/' . $uniqueName;
            list($filename, $ext) = explode('.', $input->mediaFileName);
            if ($this->isImage($input->mediaFileType)) {
                //  ensure default image is not larger than max size allowed
                $newWidth = $this->conf['MediaMgr']['imageMaxWidth'];
                $newHeight = $this->conf['MediaMgr']['imageMaxHeight'];
                $srcImgLocation = $input->mediaFileTmpName;
                $targetLocation = SGL_UPLOAD_DIR . '/' . $uniqueName . '.jpg';

                $ok = $this->resizeImageAndSave($srcImgLocation, $targetLocation, $newWidth, $newHeight);

                //  hard-code to jpeg as all images are converted to jpegs
                $output->fileTypeID = 5;
                $output->mediaFileType = 'image/jpeg';
                $output->mediaFileName = $filename . '.jpg';
                $output->mediaUniqueName = $uniqueName . '.jpg';
                $output->fileTypeName = $this->_getType($output->fileTypeID);
            } else {
                copy($input->mediaFileTmpName, $targetLocation);
                $output->fileTypeID = $this->_mime2FileType($input->mediaFileType);
                $output->fileTypeName = $this->_getType($output->fileTypeID);
                $output->mediaUniqueName = $uniqueName;
            }
            $output->save = true;

            //  create small thumbnail
            if ($this->isImage($input->mediaFileType) && $this->conf['MediaMgr']['createSmallThumbnail']) {
                $thumbsDir = SGL_UPLOAD_DIR . '/' . $this->conf['MediaMgr']['thumbsDir'];
                if (!$this->ensureUploadDirWritable($thumbsDir)) {
                    return false;
                }
                $newWidth = $this->conf['MediaMgr']['thumbSmallWidth'];
                $newHeight = $this->conf['MediaMgr']['thumbSmallHeight'];
                $srcImgLocation = $input->mediaFileTmpName;
                $targetLocation = $thumbsDir . '/small_' . $uniqueName . '.jpg';

                $ok = $this->resizeImageAndSave($srcImgLocation, $targetLocation, $newWidth, $newHeight);
            }

            //  create large thumbnail
            if ($this->isImage($input->mediaFileType) && $this->conf['MediaMgr']['createLargeThumbnail']) {
                $thumbsDir = SGL_UPLOAD_DIR . '/' . $this->conf['MediaMgr']['thumbsDir'];
                if (!$this->ensureUploadDirWritable($thumbsDir)) {
                    return false;
                }
                $newWidth = $this->conf['MediaMgr']['thumbLargeWidth'];
                $newHeight = $this->conf['MediaMgr']['thumbLargeHeight'];
                $srcImgLocation = $input->mediaFileTmpName;
                $targetLocation = $thumbsDir . '/large_' . $uniqueName . '.jpg';

                $ok = $this->resizeImageAndSave($srcImgLocation, $targetLocation, $newWidth, $newHeight);
            }
        } else { // display upload screen
            $output->save = false;
            $output->fileTypeID = 0;
        }
    }

    function resizeImageAndSave($srcImgLocation, $targetLocation, $newWidth, $newHeight)
    {
        //  create img tranform obj
        require_once 'Image/Transform.php';
        $imageDriver = $this->conf['MediaMgr']['imageDriver'];
        $im = Image_Transform::factory($imageDriver);
        if (PEAR::isError($im)) {
        	return false;
        }
        // load image
        $ret = $im->load($srcImgLocation);
        if (PEAR::isError($ret)) {
        	return false;
        }
        //  get img size
        $size = $im->getImageSize();
        if (isset($size[0]) && isset($size[1])) {
        	$width = $size[0];
        	$height = $size[1];
        } else {
        	return SGL::raiseError('Unable to get image size');
        }
    	// make sure to keep the image aspect ratio
    	if ($width >= $height && $newHeight <= $height){
      		$newHeight = $height / ($width / $newWidth);
    	} elseif ($width < $height && $newWidth < $width) {
      		$newWidth = $width / ($height / $newHeight);
    	} else {
      		$newWidth = $width;
      		$newHeight = $height;
   		}

        $ret = $im->resize($newWidth, $newHeight);
        if (PEAR::isError($ret)) {
        	return false;
        }

        $ret = $im->save($targetLocation, 'jpeg');
        if (PEAR::isError($ret)) {
        	return false;
        }
        return true;
    }

    function _cmd_insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!SGL::objectHasState($input->media)) {
            SGL::raiseError('No data in input object', SGL_ERROR_NODATA);
            return false;
        }
        SGL_DB::setConnection();
        $media = DB_DataObject::factory($this->conf['table']['media']);
        $media->setFrom($input->media);
        $media->media_id = $this->dbh->nextId($this->conf['table']['media']);
        $media->date_created  = SGL_Date::getTime();
        $media->added_by = SGL_Session::getUid();
        $media->name = SGL_String::censor($media->name);
        $media->description = SGL_String::censor($media->description);
        if ($media->insert()) {
            SGL::raiseMsg('The media has successfully been added', false,
                SGL_MESSAGE_INFO);
        }
        //  if file has been renamed
        if ($input->media->orig_name != $media->name) {
            rename( SGL_UPLOAD_DIR . '/' . $media->media->orig_name,
                    SGL_UPLOAD_DIR . '/' . $media->name);
            $output->media = $media;
        }
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'mediaEdit.html';
        $media = DB_DataObject::factory($this->conf['table']['media']);

        $ok = $media->get($input->mediaId);
        $media->getLinks('link_%s');

        $output->media = $media;
    }

    function _cmd_setDownload(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if ($this->conf['MediaMgr']['zipDownloads']) {
            $this->_cmd_downloadZipped($input, $output);
        } else {
            $this->_cmd_download($input, $output);
        }
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $document = DB_DataObject::factory($this->conf['table']['media']);
        $document->get($input->mediaId);
        $document->setFrom($input->media);
        $document->name = SGL_String::censor($document->name);
        $document->description = SGL_String::censor($document->description);
        $document->update();

        //  if file has been renamed
        if ($input->media->orig_name != $document->name) {
            rename( SGL_UPLOAD_DIR . '/' . $input->media->orig_name,
                    SGL_UPLOAD_DIR . '/' . $document->name);
        }
        $output->asset = $document;
        SGL::raiseMsg('The media has successfully been updated', false,
            SGL_MESSAGE_INFO);
    }

    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $input->aDelete = array($input->mediaId); //FIXME
        foreach ($input->aDelete as $mediaId) {
            $media = DB_DataObject::factory($this->conf['table']['media']);
            $media->get($mediaId);
            //  delete physical file
            if (is_file(SGL_UPLOAD_DIR . '/' . $media->name)) {
                @unlink(SGL_UPLOAD_DIR . '/' . $media->name);
            }
            $media->delete();
            unset($media);
        }
        SGL::raiseMsg('The media has successfully been deleted', false,
            SGL_MESSAGE_INFO);
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

//        $query = "
//            SELECT    m.media_id,
//                      m.name, file_size, mime_type,
//                      m.date_created, description,
//                      mt.name AS file_type_name,
//                      u.username AS media_added_by
//            FROM      {$this->conf['table']['media']} m
//            JOIN      {$this->conf['table']['file_type']} mt ON mt.file_type_id = m.file_type_id
//            LEFT JOIN {$this->conf['table']['user']} u ON u.usr_id = m.added_by
//            ORDER BY  m.date_created DESC";
//        $output->results = $this->dbh->getAll($query);
        $output->addOnLoadEvent("new Effect.BlindUp($('view_type'),'blind')");
        $output->addOnLoadEvent('remoteHW.getMediaByFileType()');
    }

    function _mime2FileType($mimeType)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        switch ($mimeType) {

        case 'text/plain':                      $fileTypeId = 8; break;
        case 'application/msword':              $fileTypeId = 1; break;
        case 'application/rtf':                 $fileTypeId = 8; break;
        case 'application/vnd.ms-excel':        $fileTypeId = 2; break;
        case 'application/vnd.ms-powerpoint':   $fileTypeId = 3; break;
        case 'text/html':                       $fileTypeId = 4; break;
        //  jpgs on windows
        case 'image/pjpeg':                     $fileTypeId = 5; break;
        //  jpgs on linux
        case 'image/jpeg':                      $fileTypeId = 5; break;
        case 'image/x-png':                     $fileTypeId = 5; break;
        case 'image/png':                       $fileTypeId = 5; break;
        case 'image/gif':                       $fileTypeId = 5; break;
        case 'application/pdf':                 $fileTypeId = 6; break;
        case 'application/zip':                 $fileTypeId = 9; break;
        default:
            $fileTypeId = 7;   //  unknown
        }
        return $fileTypeId;
    }

    function isImage($mimeType)
    {
        return preg_match("/^image/", $mimeType);
    }

    function _getType($fileTypeId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "
            SELECT  name
            FROM    {$this->conf['table']['file_type']}
            WHERE   file_type_id = $fileTypeId";
        return $this->dbh->getOne($query);
    }
}
?>