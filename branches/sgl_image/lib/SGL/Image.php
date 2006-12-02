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

define('SGL_IMAGE_DEFAULT_SECTION', 'default');

class SGL_Image_Test
{
    function init()
    {
/**
 * @staticvar array
 */
$aProp = &PEAR::getStaticProperty('SGL_Image', '_aMainParams');
$aProp = array('driver', 'saveQuality', 'thumbDir');

/**
 * @staticvar array
 */
$aProp = &PEAR::getStaticProperty('SGL_Image', '_aAdditionalParams');
$aProp = array('inherit', 'thumbnails', 'inheritThumbnails');
    }
}

/**
 * Base image class.
 *
 * @package    seagull
 * @subpackage image
 * @author     Dmitri Lakachauskis <dmitri@telenet.lv>
 */
class SGL_Image
{
    /**
     * Image file name e.g. my-image-name.jpg.
     *
     * @var string
     */
    var $fileName;

    /**
     * Name of module, which uses this class.
     *
     * @var string
     */
    var $moduleName;

    /**
     * Used for image modification.
     *
     * @var array
     */
    var $_aParams = array();

    /**
     * Thumbnails params are stored here.
     *
     * @var array
     */
    var $_aThumbnails = array();

    /**
     * Loaded strategies.
     *
     * @var array
     */
    var $_aStrats = array();

    /**
     * Constructor
     *
     * @access public
     * @param  string $fileName
     * @param  string $moduleName
     */
    function SGL_Image($fileName = null, $moduleName = '')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();

        $this->fileName   = $fileName;
        $this->moduleName = $moduleName;
    }

    /**
     * @access public
     */
    function getAvailableParams()
    {
        $aAddParams  = &PEAR::getStaticProperty('SGL_Image', '_aAdditionalParams');
        $aMainParams = &PEAR::getStaticProperty('SGL_Image', '_aMainParams');
        return array_merge($aAddParams, $aMainParams);
    }

    /**
     * @access public
     */
    function getParamsFromFile($fileName)
    {
        if (!is_readable($fileName)) {
            return SGL::raiseError("SGL_Image: '$filename' is not readable");
        }
        $aRet = parse_ini_file($fileName, true);
        if (!isset($aRet[SGL_IMAGE_DEFAULT_SECTION])) {
            return SGL::raiseError('SGL_Image: default container not found');
        }
        $aSections = SGL_Image::_getUniqueSectionNames($aRet);

        ksort($aRet);
        $aResult = array();
        $default = array();
        foreach ($aSections as $sectionName) {
            $ret = SGL_Image::_getSectionData($aRet, $sectionName, $default);
            if (PEAR::isError($ret)) {
                return $ret;
            }
            if ($sectionName == SGL_IMAGE_DEFAULT_SECTION) {
                $default = $ret;
            } else {
                $aResult[$sectionName] = $ret;
            }
        }

        $aDefault = array(SGL_IMAGE_DEFAULT_SECTION => $default);
        return array_merge($aDefault, $aResult);
    }

    /**
     * @access public
     */
    function init($params, $container = SGL_IMAGE_DEFAULT_SECTION)
    {
        // filename is specified
        if (is_string($params)) {
            if (file_exists($params)) {
                $params = SGL_Image::getParamsFromFile($params);
                if (PEAR::isError($params)) {
                    return $params;
                }
                $params = $params[SGL_IMAGE_DEFAULT_SECTION];
            } else {
                return SGL::raiseError("SGL_Image: file '$params' not found");
            }
        // wrong parameters' type
        } elseif (!is_array($params)) {
            return SGL::raiseError("SGL_Image: you should specify an array
                or path to configuration file");
        }
        $ok = $this->setParams($params);
        if (PEAR::isError($ok)) {
            return $ok;
        }
        $ok = $this->_loadStrategies();
        if (PEAR::isError($ok)) {
            return $ok;
        }
        return true;
    }

    /**
     * Set modification params.
     *
     * @access public
     * @param  array  $aParams
     */
    function setParams($aParams)
    {
        $ok = SGL_Image::_mainParamsCheckInArray($aParams);
        if (PEAR::isError($ok)) {
            return $ok;
        }
        SGL_Image::_configParamCleanup($aParams);
        if (isset($aParams['thumbnails'])) {
            foreach ($aParams['thumbnails'] as $thumbName => $thumbParams) {
                $ok = SGL_Image::_mainParamsCheckInArray($thumbParams, $thumbName);
                if (PEAR::isError($ok)) {
                    return $ok;
                }
            }
            SGL_Image::_configParamCleanup($aParams['thumbnails']);
            $this->_aThumbnails = $aParams['thumbnails'];
            unset($aParams['thumbnails']);
        }
        $this->_aParams = $aParams;
    }

    /**
     * Genereate filename.
     *
     * @access public
     * @param  string $salt
     * @return string
     */
    function generateUniqueFileName($salt = '')
    {
        return md5($salt . SGL_Session::getUid() . SGL::getTime());
    }












    /**
     * Return realpath to current image.
     * If called statically first parameter is $module, otherwise only 2 params
     * exist and the first one is $includeFile.
     *
     * @access public
     * @param  string  $module       module name
     * @param  boolean $includeFile  include filename in path
     * @param  string  $thumb        include specified thumbnail in path
     * @return string
     *
     * @see _getImagePath()
     */
    function getPath()
    {
        $aArgs = func_get_args();
        $aArgs = array_merge(array('path'), $aArgs);
        $callback = SGL_Image::_isClassMethod()
            ? array('SGL_Image', '_getImagePath')
            : array($this, '_getImagePath');
        return call_user_func_array($callback, $aArgs);
    }

    /**
     * Return URL to current image.
     * If called statically first parameter is $module, otherwise only 2 params
     * exist and the first one is $includeFile.
     *
     * @access public
     * @param  string  $module       module name
     * @param  boolean $includeFile  include filename in URL
     * @param  string  $thumb        include specified thumbnail in URL
     * @return string
     *
     * @see _getImagePath()
     */
    function getUrl()
    {
        $aArgs = func_get_args();
        $aArgs = array_merge(array('url'), $aArgs);
        $callback = SGL_Image::_isClassMethod()
            ? array('SGL_Image', '_getImagePath')
            : array($this, '_getImagePath');
        return call_user_func_array($callback, $aArgs);
    }

    /**
     * This method checks if we have a static call. If so it can handle
     * 4 params, otherwise only 3. The first param is always a path
     * type: 'url' or 'path'.
     *
     * fixme - write examples
     *
     * @access private
     * @return string
     */
    function _getImagePath()
    {
        $aArgs = func_get_args();
        if (empty($aArgs)) { // at least $pathType must be specified
            return SGL::raiseError('SGL_Image: path type is not specified');
        }

        // get URL or PATH
        $pathType = array_shift($aArgs);

        $moduleName  = false; // default values
        $includeFile = false;
        $thumb       = false;

        if (SGL_Image::_isInstanceMethod()) {
            // we know module's name if we have an instance
            $moduleName = $this->moduleName;
            if (!empty($aArgs)) {
                // first param: include image file or not
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
            // otherwise it is not possible to have direct access to file
            if (empty($moduleName)) {
                return SGL::raiseError('Module is not specified',
                    SGL_ERROR_INVALIDARGS);
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
            $path .= '/thumbs'; // fixme
        }
        return !empty($includeFile)
            ? $path . '/' . ($thumb
                                ? $thumb . '_' . $includeFile
                                : $includeFile)
            : $path;
    }

    /**
     * Upload image and create it's copies i.e. thumbnails.
     *
     * @access public
     * @param  string $srcLocation  image temporary location
     * @param  string $function     which method to use to create new image
     *                              from temporary one
     * @return string
     */
    function upload($srcLocation, $callback = 'move_uploaded_file')
    {
        if (!function_exists($function)) {
            return SGL_image::raiseError("function '$function' does not exist");
        }
        if (is_null($this->fileName)) {
            $this->fileName = $this->generateFileName($srcLocation);
        }
        $newFile = $this->getPath($includeFile = true);



        if (!$function($srcLocation, $newFile)) {
            return SGL::raiseError("Function '$function' failed");
        }
        $result = $this->_toThumbnails(); // create thumbnails
        if (PEAR::isError($result)) {
            return $result;
        }


        $result = $this->applyParams(); // apply transformations
        if (PEAR::isError($result)) {
            return $result;
        }
        return $this->fileName;
    }

    /**
     * Replace image and all it's copies i.e. thumbnails.
     *
     * @access public
     * @param  string  $srcLocation  image temporary location
     * @param  string  $function     which method to use to create new image
     *                               from temporary one
     * @return boolean
     */
    function replace($srcLocation, $function = 'move_uploaded_file')
    {
        if (!function_exists($function)) {
            $error = "Function '$function' does not exist";
            return SGL::raiseError($error, SGL_ERROR_INVALIDARGS);
        }
        $mainFile = $this->getPath($includeFile = true);
        if (!is_writable($mainFile)) {
            $error = "File $mainFile is not writable";
            return SGL::raiseError($error, SGL_ERROR_INVALIDFILEPERMS);
        }
        unlink($mainFile);
        if (!$function($srcLocation, $mainFile)) {
            $error = "Function '$function' failed";
            return SGL::raiseError($error, SGL_ERROR_INVALIDFILEPERMS);
        }
        $result = $this->_toThumbnails(); // replace thumbnails
        if (PEAR::isError($result)) {
            return $result;
        }
        $result = $this->applyParams(); // apply transformations
        if (PEAR::isError($result)) {
            return $result;
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
            return SGL::raiseError('File name is not specified',
                SGL_ERROR_NODATA);
        }
        $mainFile = $this->getPath($includeFile = true);
        if (!is_writable($mainFile)) {
            $error = "File $mainFile is not writable";
            return SGL::raiseError($error, SGL_ERROR_INVALIDFILEPERMS);
        }
        unlink($mainFile);
        $result = $this->_toThumbnails('unlink'); // unlink thumbnails
        if (PEAR::isError($result)) {
            return $result;
        }
        return true;
    }

    /**
     * Copy, move or delete thumbnails, using original image.
     *
     * @access private
     * @param  string  $function  operation to perform on original image
     * @return boolean
     */
    function _toThumbnails($function = 'copy')
    {
        if (empty($this->_aThumbnails)) {
            return true;
        }

        $thumbDir = $this->getPath() . '/' . $this->_aParams['thumbDir'];
        if (!is_writable($thumbDir)) {
            require_once 'System.php';
            System::mkDir(array('-p', $thumbDir));
            $old = umask(0);
            @chmod($thumbDir, 0777);
            umask($old);
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
                if (!is_writable($thumbFile)) {
                    $error = "File '$thumbFile' is not writable";
                    return SGL::raiseError($error, SGL_ERROR_INVALIDFILEPERMS);
                }
                unlink($thumbFile);
            }
            if ($function != 'unlink') {
                if (!function_exists($function)) {
                    $error = "Function '$function' does not exist";
                    return SGL::raiseError($error, SGL_ERROR_INVALIDARGS);
                }
                if (!$function($origFile, $thumbFile)) {
                    $error = "Function '$function' failed";
                    return SGL::raiseError($error, SGL_ERROR_INVALIDFILEPERMS);
                }
            }
        }

        return true;
    }



    function transform()
    {
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
        $image = & new SGL_Image($fileName, $module);
        $image->init($aParams);
        return $image->delete();
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
     * @access private
     */
    function _loadStrategies()
    {
        // transformation: main container + thumbnails
        $aConfiguration = array_merge(
            array($this->_aParams),
            $this->_aThumbnails
        );
        $aAvailParams = SGL_Image::getAvailableParams();
        $aDrivers     = array();
        foreach ($aConfiguration as $container => $aParams) {

            // available strategies for current container
            $aStrats = array_diff(array_keys($aParams), $aAvailParams);

            // load driver
            $driverSignature = md5($aParams['driver']);
            if (!isset($aDrivers[$driverSignature])) {
                require_once 'Image/Transform.php';
                $oDriver = &Image_Transform::factory($aParams['driver']);
                if (PEAR::isError($oDriver)) {
                    return $oDriver;
                }
                $aDrivers[$driverSignature] = &$oDriver;
            }

            // load strategies
            foreach ($aStrats as $strategyName) {
                // skip strategy without params or switched off strategies
                if (empty($aParams[$strategyName])) {
                    continue;
                }
                $stratName = ucfirst($strategyName) . 'Strategy';
                $stratFile = SGL_CORE_DIR . "/ImageTransform/$stratName.php";
                if (!file_exists($stratFile)) {
                    return SGL::raiseError("SGL_Image: file '$stratFile' does not exist");
                }
                include_once $stratFile;
                $stratClass = 'SGL_ImageTransform_' . $stratName;
                if (!class_exists($stratClass)) {
                    return SGL::raiseError("SGL_Image: class '$stratClass' does not exist");
                }
                $this->_aStrats[$container][$strategyName] = & new $stratClass(
                    $aDrivers[$driverSignature]);
            }
        }
        return true;
    }

    /**
     * Check if method is called statically.
     *
     * @access private
     * @return boolean
     *
     * @see _isInstanceMethod()
     */
    function _isClassMethod()
    {
        return !SGL_Image::_isInstanceMethod();
    }

    /**
     * Check if SGL_Image instance is initialized.
     *
     * @access private
     * @return boolean
     *
     * @see _isClassMethod()
     */
    function _isInstanceMethod()
    {
        return isset($this) && is_a($this, 'SGL_Image');
    }

    /**
     * @access private
     */
    function _configParamCleanup(&$aParams)
    {
        if (empty($aParams)) {
            return;
        }
        reset($aParams);
        $firstkey = key($aParams);
        if (is_array($aParams[$firstkey])) {
            foreach ($aParams as $key => $value) {
                SGL_Image::_configParamCleanup($aParams[$key]);
            }
            return;
        }
        reset($aParams);
        $aAddParams = &PEAR::getStaticProperty('SGL_Image', '_aAdditionalParams');
        foreach ($aAddParams as $param) {
            if (isset($aParams[$param]) && is_scalar($aParams[$param])) {
                unset($aParams[$param]);
            }
        }
    }

    /**
     * @access private
     */
    function _mainParamsCheckInArray($aParams, $sectionName = '')
    {
        $aMainParams = &PEAR::getStaticProperty('SGL_Image', '_aMainParams');
        $aRet = array_diff($aMainParams, array_keys($aParams));
        if (!empty($aRet)) {
            $error = "SGL_Image: missing parameters";
            if (!empty($sectionName)) {
                $error .= ' for [' . $sectionName . ']';
            }
            $error .= ': ' . implode(', ', $aRet);
            return SGL::raiseError($error);
        }
        return true;
    }

    /**
     * @access private
     */
    function _getUniqueSectionNames($aSections)
    {
        $aResult   = array();
        $aSections = array_keys($aSections);
        sort($aSections);
        foreach ($aSections as $sectionName) {
            $aNameParts = explode('_', $sectionName);
            if (in_array($aNameParts[0], $aResult)) {
                continue;
            }
            $aResult[] = $sectionName;
        }
        // place default section at beginning
        $index = array_search(SGL_IMAGE_DEFAULT_SECTION, $aResult);
        unset($aResult[$index]);
        array_unshift($aResult, SGL_IMAGE_DEFAULT_SECTION);
        return $aResult;
    }

    /**
     * @access private
     */
    function _getSectionData($aData, $sectionName, $override)
    {
        if (!empty($override['thumbnails'])) {
            $overrideThumbs = $override['thumbnails'];
            unset($override['thumbnails']);
        }

        // save parent section's data
        $aResult = !empty($aData[$sectionName]['inherit'])
            ? array_merge($override, $aData[$sectionName]) // inherit from default
            : $aData[$sectionName];

        // check for obligatory params
        $ok = SGL_Image::_mainParamsCheckInArray($aResult, $sectionName);
        if (PEAR::isError($ok)) {
            return $ok;
        }

        // process thumbnails
        if (!empty($aResult['thumbnails']) || !empty($aResult['inheritThumbnails'])) {
            $aTotalThumbs = array();
            if (!empty($aResult['inheritThumbnails'])
                    && isset($overrideThumbs)
                    && is_array($overrideThumbs)) {
                $aTotalThumbs = array_keys($overrideThumbs);
            }
            if (!empty($aResult['thumbnails'])) {
                $aThumbs = explode(',', $aResult['thumbnails']);
                $aTotalThumbs = array_merge($aTotalThumbs, $aThumbs);
            }

            $aThumbs = array();
            foreach ($aTotalThumbs as $thumbName) {
                // e.g. media_small
                $thumbSectionName = $sectionName . '_' . $thumbName;
                if (isset($aData[$thumbSectionName])) {
                    $aThumbs[$thumbName] = (isset($aData[$thumbSectionName]['inherit'])
                            && !$aData[$thumbSectionName]['inherit'])
                        // do inherit parent container
                        ? $aData[$thumbSectionName]
                        // do inherit by default
                        : array_merge($aResult, $aData[$thumbSectionName]);

                // default thumbnail exists
                } elseif (isset($overrideThumbs[$thumbName])
                        // and it can be inherited
                        && !empty($aResult['inheritThumbnails'])) {
                    $defaultThumbSectionName = SGL_IMAGE_DEFAULT_SECTION .
                        '_' . $thumbName;
                    if (isset($aData[$defaultThumbSectionName]['inherit'])
                            && !$aData[$defaultThumbSectionName]['inherit']) {
                        // do not inherit parent section's data
                        $aThumbs[$thumbName] = $overrideThumbs[$thumbName];
                    } else {
                        // inherit parent section's data
                        $aThumbs[$thumbName] = array_merge($aResult,
                            $overrideThumbs[$thumbName]);
                    }
                }

                $ok = SGL_Image::_mainParamsCheckInArray($aThumbs[$thumbName],
                    $thumbSectionName);
                if (PEAR::isError($ok)) {
                    return $ok;
                }
            }
            if (!empty($aThumbs)) {
                SGL_Image::_configParamCleanup($aThumbs); // cleanup thumbnails
                $aResult['thumbnails'] = $aThumbs;
            }
        }
        SGL_Image::_configParamCleanup($aResult); // cleanup parent container
        return $aResult;
    }
}

/**
 * Base image transformation strategy.
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
    var $driver;

    /**
     * Full image path.
     *
     * @var string
     */
    var $fileName;

    /**
     * Constructor.
     *
     * @access public
     * @param  PEAR Image_Transfrom  $driver
     */
    function SGL_ImageTransformStrategy(&$driver)
    {
        $this->driver = &$driver;
    }

    /**
     * Extract params from config string.
     *
     * @access public
     * @param  string $configString
     * @return array
     */
    function getParamsFromString($configString)
    {
        $aParams = array_map('trim', explode(',', $configString));
        $aRet = array();
        foreach ($aParams as $param) {
            if (false !== strpos($param, ':')) {
                $arr = explode(':', $param);
                $aRet[$arr[0]] = $arr[1];
            } else {
                // when only one parameter exists
                $aRet['config'] = $param;
            }
        }
        return $aRet;
    }

    /**
     * Init strategy i.e. load image file and parse params.
     *
     * @access public
     * @param  string $fileName
     * @param  string $configString
     */
    function init($fileName, $configString = '')
    {
        $ok = $this->load($fileName);
        if (PEAR::isError($ok)) {
            return $ok;
        }
        if (!empty($configString)) {
            $aParams = $this->getParamsFromString($configString);
            $this->setParams($aParams);
        }
    }

    /**
     * @access public
     * @param  array $aParams
     */
    function setParams($aParams)
    {
        foreach ($aParams as $k => $v) {
            $this->$k = $v;
        }
    }

    /**
     * Load file.
     *
     * @access public
     * @param  string $fileName
     */
    function load($fileName)
    {
        if (!is_readable($fileName)) {
            return SGL::raiseError("SGL_Image: file '$fileName' is not readable");
        }
        $this->fileName = $fileName;
        return $this->driver->load($fileName);
    }

    /**
     * Save file, free memory.
     *
     * @access public
     * @param  $saveQuality
     * @param  $saveFormat   jpg, gif or png (not support by SGL_Image yet)
     */
    function save($saveQuality, $saveFormat = '')
    {
        $ok = $this->driver->save($this->fileName, $saveFormat, $saveQuality);
        if (PEAR::isError($ok)) {
            return $ok;
        }
        return $this->driver->free();
    }

    /**
     * Tranform image.
     *
     * @access   public
     * @abstract
     */
    function transform() {}
}

?>