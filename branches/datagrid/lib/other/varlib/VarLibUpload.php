<?php
/**
 * Varico Lib Upload Files Common
 * Common Upload Files Class for all varico modules
 * @since SL2.0
 * @author Varico
 */
class VarLibUpload {

    function varLibUpload() {

    }

    /**
     * This function upload files to defualt upload folder and to folder which call the same like module
     * from file was upload
     * @param array $data
     * @return string new file name
    **/
    function moveUploadFile($data) {
        global $form;
        $moduleName = $GLOBALS['_SGL']['REQUEST']['moduleName'];
        // create unique file name
        $uploadFile = strtolower($data['uploadedFile']['name']);
        $newName = md5($uploadFile . date('Ymis'));
        $file =& $form->getElement('uploadedFile');
        if(!is_dir(SGL_UPLOAD_DIR . '/' . $moduleName)) {
            $errorMsg = 'Folder ' . $moduleName . ' does not exist in upload folder!';
            SGL::raiseMsg($errorMsg);
            SGL::raiseError($errorMsg, SGL_ERROR_FILEUNWRITABLE);
        }
        if ($file->moveUploadedFile(SGL_UPLOAD_DIR . '/' . $moduleName, $newName)) {
            SGL::raiseMsg('File was upload successful');
            return $newName;
        } else {
            $errorMsg = 'There was a problem to upload file from module ' . $moduleName;
            SGL::raiseMsg($errorMsg);
            SGL::raiseError($errorMsg , SGL_ERROR_FILEUNWRITABLE);
            return false;
        }
    }

    /**
     * This function resize and save upload image to max default size
     * @param string $fileName
     * @return void
    **/
    function resizeUploadImageMax($fileName) {
        $moduleName = $GLOBALS['_SGL']['REQUEST']['moduleName'];
        $filePath = SGL_UPLOAD_DIR . '/' . $moduleName . '/' . $fileName;
        $imageDesc = VarLibUpload::resizeImage($filePath, MAX_WIDTH_FILE, MAX_HEIGHT_FILE);
        VarLibUpload::saveImage($imageDesc, $filePath);
        //VarLibUpload::displayImage($imageDesc);
    }

    /**
     * This function resize and save upload image to min default size
     * @param string $fileName
     * @return void
    **/
    function resizeUploadImageMin($fileName) {
        $moduleName = $GLOBALS['_SGL']['REQUEST']['moduleName'];
        $filePath = SGL_UPLOAD_DIR . '/' . $moduleName . '/' . $fileName;
        $imageDesc = VarLibUpload::resizeImage($filePath, MIN_WIDTH_FILE, MIN_HEIGHT_FILE);
        VarLibUpload::saveImage($imageDesc, SGL_UPLOAD_DIR . '/' . $moduleName . '/tmb_' . $fileName);
        //VarLibUpload::displayImage($imageDesc);
    }

    /**
     * This function upload image, resize to default max size and save file
     * @param array $data
     * @return string $newName
    **/
    function moveAndResizeMaxUploadImage($data) {
        global $form;
        $newName = VarLibUpload::moveUploadFile($data);
        VarLibUpload::resizeUploadImageMax($newName);
        return $newName;
    }

    /**
     * This function upload image, resize to default max and default min size and save files
     * @param array $data
     * @return string $newName
    **/
    function resizeMaxMinUploadImage($data) {
        global $form;
        $newName = VarLibUpload::moveUploadFile($data);
        VarLibUpload::resizeUploadImageMax($newName);
        VarLibUpload::resizeUploadImageMin($newName);
        return $newName;
    }

    /**
     * This function resize upload images
     * @param string $fileName
     * @param integer $width
     * @param integer $height
     * @return object $imageDesc convert image file descriptor
    **/
    function resizeImage($fileName, $width, $height) {
        // Get new dimensions
        list($widthOrig, $heightOrig) = getimagesize($fileName);

        if(($widthOrig > $width) || ($heightOrig > $height)) {
            if ($width && ($widthOrig < $heightOrig)) {
               $width = ($height / $heightOrig) * $widthOrig;
            } else {
               $height = ($width / $widthOrig) * $heightOrig;
            }
        }
        else {
            $width = $widthOrig;
            $height = $heightOrig;
        }
        // Resample
        $imageDesc = imagecreatetruecolor($width, $height);
        $image = imagecreatefromjpeg($fileName);

        $resampleFunction = 'ImageCopyResampled';
        if (!function_exists($resampleFunction)) {
            $resampleFunction = 'ImageCopyResized';
        }
        $resampleFunction($imageDesc, $image, 0, 0, 0, 0, $width, $height, $widthOrig, $heightOrig);
        return $imageDesc;
    }

    /**
     * This function save image file
     * @param object $imageDesc
     * @param string $fileName
     * @return
    **/
    function saveImage($imageDesc, $fileName) {
        imagejpeg($imageDesc, $fileName);
    }

    /**
     * This function display images directly in a browser
     * @param object $imageDesc
     * @return void
    **/
    function displayImage($imageDesc) {
        // Content type
        header('Content-type: image/jpeg');
        // Output
        imagejpeg($imageDesc, null, 100);
    }

    /**
     * This function delete file
     * @param string $fileName
     * @return
    **/
    function deleteFile($fileName) {
        @chmod($fileName, 0777);
        @unlink($fileName);
    }
}
?>
