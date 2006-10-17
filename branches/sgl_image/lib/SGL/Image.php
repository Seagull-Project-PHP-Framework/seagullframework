<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Demian Turner                                         |
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
// | Image.php                                                                 |
// +---------------------------------------------------------------------------+
// | Author: Dmitri Lakachauskis <dmitri@telenet.lv>                           |
// +---------------------------------------------------------------------------+

/**
 * @todo improve config parsing
 *   - move image config to separate file, ???
 *   - specify path to config during SGL_Image initialization (in constructor),
 *   - improve mime type check.
 *
 * Base image class.
 *
 * @package    seagull
 * @subpackage image
 * @author     Dmitri Lakachauskis <dmitri@telenet.lv>
 */
class SGL_Image
{
    /**
     * Image file name (e.g. 201083.gif).
     *
     * @var string
     */
    var $fileName = null;

    /**
     * Name of module, which uses this class.
     *
     * @var string
     */
    var $module = '';

    /**
     * Name of image type (e.g. 'map', 'plan', 'gallery').
     *
     * @var string
     */
    var $imageName = '';

    /**
     * Used for image modification.
     *
     * @var array  params of image
     */
    var $_aParams = array();

    /**
     * Thumbnails params are stored here.
     *
     * @var array
     */
    var $_aThumbnails = array();

    /**
     * Constructor
     *
     * @access  public
     * @param   string  $fileName   image file name e.g. xxx.jpg
     * @param   array   $aParams    image transformation params
     * @param   string  $module     module name
     * @param   string  $imageName  image type name (useless for now)
     * @return  void
     */
    function SGL_Image($fileName = null, $aParams = null, $module = '', $imageName = '')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $c = &SGL_Config::singleton();
        $this->conf  = $c->getAll();

        if (!is_null($fileName)) {
            $this->_init($fileName);
        }
        if (isset($aParams) && is_array($aParams)) {
            $this->setParams($aParams);
        }
        $this->module    = $module;
        $this->imageName = $imageName;
    }

    /**
     * Init image.
     *
     * @access private
     * @param  string  $fileName
     * @return void
     */
    function _init($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Set modification params.
     *
     * @access public
     * @param  array  $aParams
     * @return void
     */
    function setParams($aParams)
    {
        if (isset($aParams['thumbs'])) {
            if (is_array($aParams['thumbs']) && count($aParams['thumbs'])) {
                $this->_aThumbnails = $aParams['thumbs'];
            }
            unset($aParams['thumbs']);
        }
        $this->_aParams = $aParams;
    }

    /**
     * Check if method is called in static way.
     *
     * @access provate
     * @return boolean
     */
    function _isStaticCall()
    {
        return !SGL_Image::_isMethodCall();
    }

    /**
     * Check if method is called in dynamic way.
     *
     * @access provate
     * @return boolean
     */
    function _isMethodCall()
    {
        return isset($this) && is_a($this, 'SGL_Image');
    }

    /**
     * Return realpath to current image.
     *
     * @access public
     * If called statically first parameter is $module
     * @param  string  $module        module name for static calls
     * otherwise only 2 params exist
     * @param  boolean $includeFile   include filename, e.g. [path-to-upload-dir]/filename
     * @param  string  $thumb         include thumb, e.g. [path-to-upload-dir]/$thumb
     * @return string
     */
    function getPath()
    {
        $aArgs = func_get_args();
        $aArgs = array_merge(array('path'), $aArgs);
        return SGL_Image::_isStaticCall()
            ? call_user_func_array(array('SGL_Image', '_getImagePath'), $aArgs)
            : call_user_func_array(array($this, '_getImagePath'), $aArgs);
    }

    /**
     * Return URL to current image.
     *
     * @access public
     * If called statically first parameter is $module
     * @param  string  $module        module name for static calls
     * otherwise only 2 params exist
     * @param  boolean $includeFile   include filename, e.g. SGL_BASE_URL/$module/images/filename
     * @param  string  $thumb         include thumb, e.g. SGL_BASE_URL/$module/images/$thumb
     * @return string
     */
    function getUrl()
    {
        $aArgs = func_get_args();
        $aArgs = array_merge(array('url'), $aArgs);
        return SGL_Image::_isStaticCall()
            ? call_user_func_array(array('SGL_Image', '_getImagePath'), $aArgs)
            : call_user_func_array(array($this, '_getImagePath'), $aArgs);
    }

    /**
     * This method checks if we have a static call. If so it can handle 4 params,
     * otherwise only 3. The first param is always a path type (url or path).
     *
     * Possible results are:
     *  - SGL_UPLOAD_DIR
     *  - SGL_UPLOAD_DIR/filaname
     *  - SGL_UPLOAD_DIR/small
     *  - SGL_UPLOAD_DIR/small/filaname
     *  - SGL_APP_ROOT/$module/www/images
     *  - SGL_APP_ROOT/$module/www/images/filaname
     *  - SGL_BASE_URL/$module/images/filename
     *  - SGL_BASE_URL/$module/images/small
     *  - SGL_BASE_URL/$module/images/small/filename
     *
     * @access private
     * @return string
     */
    function _getImagePath()
    {
        $aArgs = func_get_args();
        if (empty($aArgs)) { // at least $pathType must be specified
            return false; // raiseError?
        }

        // get URL or PATH
        $pathType = array_shift($aArgs);

        $moduleName  = false; // default values
        $includeFile = false;
        $thumb       = false;

        if (SGL_Image::_isMethodCall()) {
            // we know module's name if class initialised
            $moduleName = $this->module;
            if (!empty($aArgs)) {
                // first param: include image file?
                $includeFile = array_shift($aArgs);
                if ($includeFile) {
                    $includeFile = $this->fileName;
                }
            }
            if (!empty($aArgs)) {
                // second param: thumb name
                $thumb = array_shift($aArgs);
            }
        } else {
            $numArgs = count($aArgs);
            for ($i = 0; $i < $numArgs; $i++) {
                $param = array_shift($aArgs);
                switch ($i) {
                    case 0: $moduleName  = $param; break;
                    case 1: $includeFile = $param; break;
                    case 2: $thumb       = $param; break;
                }
            }
        }

        if ('url' == $pathType) {
            // if we try to get a URL, module must be specified,
            // otherwise it is not possible to have direct access to file,
            // which exists in SGL_UPLOAD_DIR
            if (empty($moduleName)) {
                return false; // FIXME: raiseError or what?
            }
            $path = SGL_BASE_URL . '/' . $moduleName;
        } else {
            if (!empty($moduleName)) {
                $path = SGL_MOD_DIR . '/' . $moduleName . '/www/images';
            } else {
                $path = SGL_UPLOAD_DIR;
            }
        }
        if ($thumb) {
            //$path .= '/' . $thumb;
            $path .= '/thumbs';
        }
        return !empty($includeFile) ? $path . '/' . ($thumb ? $thumb . '_' . $includeFile : $includeFile) : $path;
    }

    /**
     * Upload image and create it's copies i.e. thumbnails.
     *
     * @access public
     * @param  string   $srcLocation  image temporary location
     * @param  boolean  $applyParams  apply or not transformation on images
     * @param  string   $function     which method to use to create new image from temporary one
     * @return mixed
     */
    function upload($srcLocation, $applyParams = true, $func = 'move_uploaded_file')
    {
        if (!function_exists($func)) {
            SGL::raiseError('Specified image creation method does not exist', SGL_ERROR_INVALIDARGS);
            return false;
        }
        if (is_null($this->fileName)) {
            $fileName = md5($srcLocation . SGL_Session::getUid() . SGL::getTime()); // FIXME
            $this->_init($fileName);
        }
        $newFile = $this->getPath($includeFile = true);
        if (!@$func($srcLocation, $newFile)) {
            SGL::raiseError('There is a problem while moving uploaded file', SGL_ERROR_INVALIDFILEPERMS);
            return false;
        }

        // create thumbnails
        $this->_toThumbnails();

        if ($applyParams) { // transform image according to params
            $this->applyParams($withThumbnails = true);
        }

        return $this->fileName;
    }

    /**
     * Update image and all it's copies i.e. thumbnails.
     *
     * @access public
     * @param  string   $srcLocation  image temporary location
     * @param  boolean  $applyParams  apply or not transformation on image
     * @param  string   $function     which method to use to create new image from temporary one
     * @return boolean
     */
    function replace($srcLocation, $applyParams = true, $function = 'move_uploaded_file')
    {
        if (!function_exists($function)) {
            SGL::raiseError('Specified image creation method does not exist', SGL_ERROR_INVALIDARGS);
            return false;
        }
        $mainFile = $this->getPath($includeFile = true);
        @unlink($mainFile);
        if (!@$function($srcLocation, $mainFile)) {
            SGL::raiseError('There is a problem while moving uploaded file', SGL_ERROR_INVALIDFILEPERMS);
            return false;
        }
        $this->_toThumbnails(); // replace thumbnails
        if ($applyParams) { // transform image according to params
            $this->applyParams($withThumbnails = true);
        }
        return true;
    }

    /**
     * Delete image and all it's copies i.e. thumbnails.
     *
     * @access public
     * @return boolean
     */
    function delete()
    {
        if (is_null($this->fileName)) {
            SGL::raiseError('Image is not initialized', SGL_ERROR_NODATA);
            return false;
        }
        $mainFile = $this->getPath($includeFile = true);
        if (!is_file($mainFile)) {
            SGL::raiseError('The specified file does not appear to exist', SGL_ERROR_NOFILE);
            return false;
        }
        unlink($mainFile);
        $this->_toThumbnails('unlink'); // drop thumbnails

        // success
        return true;
    }

    /**
     * Copy, move or delete thumbnails, using original image.
     *
     * @access private
     * @param  string   $function  operation to perform on original image
     * @return boolean
     */
    function _toThumbnails($function = 'copy')
    {
        if (empty($this->_aThumbnails)) {
            return true;
        }

        // FIXME
        $thumbDir = $this->getPath() . '/thumbs';
        if (!is_writable($thumbDir)) {
            require_once 'System.php';
            System::mkDir(array($thumbDir));
        }

        $aThumbs  = array_keys($this->_aThumbnails); // available thumbnails
        $origFile = $this->getPath($includeFile = true);
        foreach ($aThumbs as $thumbName) {
            //$thumbDir = $this->getPath(false, $thumbName);
            //if (!is_writable($thumbDir)) { // FIXME
            //    require_once 'System.php';
            //    System::mkDir(array($thumbDir));
            //}
            $thumbFile = $this->getPath($includeFile = true, $thumbName);
            if (file_exists($thumbFile)) {
                unlink($thumbFile);
            }
            if (function_exists($function) && 'unlink' != $function) {
                // copy or move
                $function($origFile, $thumbFile);
            }
        }
        return true;
    }

    /**
     * Delete image and all it's copies i.e. thumbnails.
     *
     * @static
     * @access public
     * @param  string   $fileName
     * @param  array    $aParams
     * @param  string   $module
     * @return boolean
     */
    function staticDelete($fileName, $aParams = null, $module = '')
    {
        $image = & new SGL_Image($fileName, $aParams, $module);
        return $image->delete();
    }

    /**
     * Applies transformation for image and it's thumbnails.
     *
     * @access public
     * @param  boolean  $withThumbnails
     * @return boolean
     */
    function applyParams($withThumbnails = false)
    {
        $aAllParams = array();
        $aAllParams[] = $this->_aParams;
        if ($withThumbnails) {
            $aAllParams = array_merge($aAllParams, $this->_aThumbnails);
        }

        require_once 'Image/Transform.php';
        foreach ($aAllParams as $paramBlock => $aParams) {
            foreach ($aParams as $paramKey => $paramValue) {

                $stratName = ucfirst($paramKey) . 'Strategy';
                $stratFile = SGL_CORE_DIR . '/ImageTransform/' . $stratName . '.php';
                if (empty($paramValue) || !file_exists($stratFile)) {
                    //SGL::raiseError('The specified image transformation strategy ' .
                    //    'file does not appear to exist', SGL_ERROR_NOFILE);
                    //return false;
                    continue;
                }

                // base filename to operate with
                $fileName = $this->getPath($includeFile = true, $paramBlock);

                // load driver
                $signature = md5($aParams['Driver']);
                if (!isset($aDrivers[$signature])) {
                    $aDrivers[$signature] = &Image_Transform::factory($aParams['Driver']);
                }

                // load and apply transformation
                include_once $stratFile;
                $stratClass = 'SGL_ImageTransform_' . $stratName;
                $oStrat = & new $stratClass(
                    $aDrivers[$signature],              // driver
                    $fileName,                          // filename to operate with
                    $this->_extractParams($paramValue), // transformation params
                    $aParams                            // config params for current block
                );
                $oStrat->transform();
                $oStrat->save();
            }
        }
        return true;
    }

    /**
     * Extracts params from config string.
     *
     * @access private
     * @param  string   $str
     * @return array
     */
    function _extractParams($str)
    {
        $aParams = array_map('trim', explode(',', $str));
        $aRes = array();
        foreach ($aParams as $param) {
            if (false !== strpos($param, ':')) {
                $arr = explode(':', $param);
                $aRes[$arr[0]] = $arr[1];
            } else {
                $aRes[] = $param;
            }
        }
        return $aRes;
    }

    /**
     * @todo remove it or improve it
     *
     * Checks mime type of image. Driven by "config inheritance" model.
     * In case an allowed types map is missing, built-in one will be used.
     *
     * @access public
     * @param  string   $manager  manager name, which config values will be evaluated
     * @param  string   $type     image mime type to check
     * @param  string   $name     image type name to check first
     * @return boolean
     */
    function isAllowedType($manager, $type, $name = '')
    {
        $aAllowedTypes = array('image/gif', 'image/png',
                               'image/jpg', 'image/jpeg',
                               'image/pjpeg');
        if (isset($this->conf)) {
            $conf = $this->conf;
        } else {
            $c = &SGL_Config::singleton();
            $conf = $c->getAll();
        }
        if (!empty($name)) {
            $name = ucfirst($name);
        }
        $aTypeString  = '';
        $paramKey     = 'image' . $name . 'AllowedTypes';
        $searchForKey = true;
        while ($searchForKey) {
            if (isset($conf[$manager][$paramKey])) {
                $aTypeString = $conf[$manager][$paramKey];
                break;
            }
            $inheritKey = 'image' . $name . 'Inherit';
            if (isset($conf[$manager][$inheritKey]) && $conf[$manager][$inheritKey]) {
                if (!empty($name)) {
                    $paramKey = 'imageAllowedTypes';
                    $name     = ''; // clear name
                } else {
                    $paramKey = 'defaultAllowedTypes';
                    $manager  = 'ImageMgr';
                }
                continue;
            }
            break;
        }
        if (!empty($aTypeString)) {
            $aAllowedTypes = array_map('trim', explode(',', $aTypeString));
        }
        return in_array($type, $aAllowedTypes);
    }

    /**
     * Builds image params map, using config "inheritance model".
     *
     * @access public
     * @param  string  $manager  manager name, which config values will be used to build a map
     * @param  string  $name     image type name (only config map for this type will be returned)
     */
    function extractParamsFromConfig($manager, $name = '')
    {
        // parsed params from manager
        $aManagerParams = SGL_Image::_extractParamsFromManager($manager, 'image');
        // default params for manager
        $aDefaultBlock = array_shift($aManagerParams);
        if (
             // check if we should inherit params from base manager for images
             isset($aDefaultBlock['Inherit']) && $aDefaultBlock['Inherit']) {

            // parsed params from default manager for images
            $aDefaultParams = SGL_Image::_extractParamsFromManager('ImageMgr', 'default');
            if (isset($aDefaultParams['defaultBlock'])) {
                // global default params
                $aDefaultParams = $aDefaultParams['defaultBlock'];
                // merge default manager with current manager
                $aDefaultBlock = SGL_Array::mergeReplace($aDefaultParams, $aDefaultBlock);
            }
        }
        // default thumbnails params
        $aDefaultBlockThumbs = array();
        if (isset($aDefaultBlock['thumbs'])
            && is_array($aDefaultBlock['thumbs'])
            && count($aDefaultBlock['thumbs'])) {
            $aDefaultBlockThumbs = $aDefaultBlock['thumbs'];
            unset($aDefaultBlock['thumbs']);
        }

        // list parsed blocks
        foreach ($aManagerParams as $blockName => $aBlockParams) {
            // thumbnails for current block
            $currThumbs = null;
            if (isset($aBlockParams['Inherit']) && $aBlockParams['Inherit']) {
                if (isset($aBlockParams['thumbs'])) {
                    //&& is_array($aBlockParams['thumbs'])
                    //&& count($aBlockParams['thumbs'])) {
                    // save current thumbnails
                    $currThumbs = $aBlockParams['thumbs'];
                    unset($aBlockParams['thumbs']);
                }
                if (!isset($currThumbs)) {
                    $currThumbs = $aDefaultBlockThumbs;
                }
                // inherit default params
                $aBlockParams = SGL_Array::mergeReplace($aDefaultBlock, $aBlockParams);
            }
            if (isset($aBlockParams['Inherit'])) {
                unset($aBlockParams['Inherit']);
            }
            if (is_array($currThumbs) && count($currThumbs)) {
                // list thumbnails
                foreach ($currThumbs as $thumbName => $aThumbParams) {
                    if (isset($aThumbParams['Inherit']) && $aThumbParams['Inherit']) {
                        unset($aThumbParams['Inherit']);
                        // inherit parent block's params
                        $currThumbs[$thumbName] = SGL_Array::mergeReplace($aBlockParams, $aThumbParams);
                    }
                }
                $aBlockParams['thumbs'] = $currThumbs;
            }
            $aManagerParams[$blockName] = $aBlockParams;
        }
        return empty($name)
            ? $aManagerParams // all blocks
            : (array_key_exists($name, $aManagerParams)
                   ? $aManagerParams[$name] // specified block
                   : array()); // empty set
    }

    /**
     * Extracts params from specified manager.
     *
     * @access private
     * @param  string   $managerName  manager name, which config values will be parsed
     * @param  string   $prefixName   only configs with $prefixName will be evaluated
     * @return array
     */
    function _extractParamsFromManager($managerName, $prefixName = '')
    {
        if (isset($this->conf)) {
            $conf = $this->conf;
        } else {
            $c = &SGL_Config::singleton();
            $conf = $c->getAll();
        }
        if (!isset($conf[$managerName])) {
            return array();
        }
        $aResultParams = array('defaultBlock' => array());
        foreach ($this->conf[$managerName] as $paramKey => $paramValue) {

            // skip non-image params
            if ($prefixName && ($prefixName != substr($paramKey, 0, strlen($prefixName)))) {
                continue;
            }
            $paramKey = substr($paramKey, strlen($prefixName)); // clean prefix

            // check for new name
            if ('name' == strtolower(substr($paramKey, 0, 4))) {
                // create new block
                $aResultParams[$paramValue] = array();
                // new name found, go to the next option
                continue;
            }

            // found image names so far
            $patternNames  = implode('|', array_keys($aResultParams));
            $currImageName = 'defaultBlock';

            // get image name
            if (!empty($patternNames) && preg_match("/^($patternNames)/i", $paramKey, $aMatches)) {
                $aMatches[1]{0} = strtolower($aMatches[1]{0});
                $currImageName = $aMatches[1];
                //$currImageName = strtolower($aMatches[1]); // current block
                $paramKey = substr($paramKey, strlen($currImageName)); // clean param name
            }

            // check for thumbnails
            if ('thumbs' == strtolower($paramKey)) {
                if (empty($paramValue)) {
                    $aResultParams[$currImageName]['thumbs'] = $paramValue;
                } else {
                    $aThumbs = array_map('trim', explode(',', $paramValue));
                    foreach ($aThumbs as $thumbName) {
                        // create new thumbs' blocks
                        $aResultParams[$currImageName]['thumbs'][$thumbName] = array();
                    }
                }
                // thumbs found, go to the next option
                continue;
            }

            // current block
            $placeToWrite = &$aResultParams[$currImageName];
            if (!empty($aResultParams[$currImageName]['thumbs'])) {
                $patternThumbs = implode('|', array_keys($aResultParams[$currImageName]['thumbs']));
                if (preg_match("/^($patternThumbs)/i", $paramKey, $aMatches)) {
                    $currThumbName = strtolower($aMatches[1]); // current thumb
                    $paramKey = substr($paramKey, strlen($currThumbName)); // trim thumb name
                    $placeToWrite = &$aResultParams[$currImageName]['thumbs'][$currThumbName];
                }
            }

            // assign new param
            //$paramKey{0} = strtolower($paramKey{0});
            $placeToWrite[$paramKey] = $paramValue;
        }
        return $aResultParams;
    }
}

/**
 * Abstract image transform strategy.
 *
 * @package    seagull
 * @subpackage image
 * @author     Dmitri Lakachauskis <dmitri@telenet.lv>
 * @abstract
 */
class SGL_ImageTransformStrategy
{
    /**
     * PEAR Image_Transfrom.
     *
     * @var object
     */
    var $transform;

    /**
     * Full image path.
     *
     * @var string
     */
    var $fileName;

    /**
     * Transformation params.
     *
     * @var array
     */
    var $aParams;

    /**
     * Configuration params.
     *
     * @var array
     */
    var $aConfigParams;

    /**
     * Constructor.
     *
     * @access public
     * @param  PEAR Image_Transfrom  $transform
     * @param  string                $fileName
     * @param  array                 $aParams
     * @param  array                 $aConfigParams
     */
    function SGL_ImageTransformStrategy(&$transform, $fileName, $aParams = array(), $aConfigParams = array())
    {
        $this->transform     = &$transform;
        $this->fileName      = $fileName;
        $this->aParams       = $aParams;
        $this->aConfigParams = $aConfigParams;
        $this->load();
    }

    /**
     * Loads file.
     *
     * @access public
     * @return void
     */
    function load()
    {
        $this->transform->load($this->fileName);
    }

    /**
     * Saves file, frees memory.
     *
     * @access public
     * @return void
     */
    function save()
    {
        $this->transform->save($this->fileName, '', $this->aConfigParams['SaveQuality']);
        $this->transform->free();
    }

    /**
     * Applies transformation for loaded image.
     *
     * @access   public
     * @abstract
     */
    function transform()
    {
    }
}

?>