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
// | Image.php                                                                 |
// +---------------------------------------------------------------------------+
// | Author: Dmitri Lakachauskis <dmitri@telenet.lv>                           |
// +---------------------------------------------------------------------------+

define('SGL_IMAGE_DEFAULT_SECTION', 'default');

/**
 * Base image class.
 *
 * @package    SGL
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
     * Constructor.
     *
     * @access public
     *
     * @param string $fileName
     * @param string $moduleName
     */
    function SGL_Image($fileName = null, $moduleName = '')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (is_null($fileName)) {
            $fileName = $this->generateUniqueFileName();
        }

        $this->fileName   = $fileName;
        $this->moduleName = $moduleName;
    }

    /**
     * Get filename.
     *
     * @return string
     */
    function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Initialize SGL_Image instance. Can parse the specified ini file passed
     * as $params or use the prepeared array directly.
     * Load strategies taking into account extracted params.
     *
     * @access public
     *
     * @param mixed  $params
     * @param string $container
     *
     * @return boolean
     */
    function init($params, $container = SGL_IMAGE_DEFAULT_SECTION)
    {
        // filename is specified
        if (is_string($params)) {
            if (file_exists($params)) {
                $params = SGL_ImageConfig::getParamsFromFile($params);
                if (PEAR::isError($params)) {
                    return $params;
                }
                if (!array_key_exists($container, $params)) {
                    $msg = "SGL_Image: container '$container' was not "
                        . "found in image.ini";
                    return SGL::raiseError($msg);
                }
                $params = $params[$container];
            } else {
                return SGL::raiseError("SGL_Image: file '$params' not found");
            }
        } elseif (!is_array($params)) { // wrong parameters' type
            return SGL::raiseError("SGL_Image: you should specify an array
                or path to configuration file");
        }
        $ok = $this->_setParams($params);
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
     * Generate filename.
     *
     * @access public
     *
     * @return string
     */
    function generateUniqueFileName()
    {
        return md5(uniqid(rand(), true));
    }

    /**
     * Get path to image folder. Can be called statically or as instance call.
     *
     * @access public
     *
     * @param string $moduleName
     *
     * @return string
     *
     * @see _getImagePath()
     */
    function getPath()
    {
        $args = func_get_args();
        $args = array_merge(array('path'), $args);
        $callback = SGL_Image::_isStaticMethod()
            ? array('SGL_Image', '_getImagePath')
            : array($this, '_getImagePath');
        return call_user_func_array($callback, $args);
    }

    /**
     * Get URL to image folder. Can be called statically or as instance call.
     * It is only possible to get an URL to a folder if we place files at
     * SGL_MOD_DIR/$moduleName/www/images. To achieve this we must specify a
     * module name during image instantiation or as an argument here.
     *
     * @access public
     *
     * @param string $moduleName
     *
     * @return string
     *
     * @see _getImagePath()
     */
    function getUrl()
    {
        $args = func_get_args();
        $args = array_merge(array('url'), $args);
        $callback = SGL_Image::_isStaticMethod()
            ? array('SGL_Image', '_getImagePath')
            : array($this, '_getImagePath');
        return call_user_func_array($callback, $args);
    }

    function isValidImage($fileName)
    {
        $ok = @getimagesize($fileName);
        return $ok !== false;
    }

    function isTransformable($fileName)
    {
        $ret = getimagesize($fileName);
        $mem = ini_get('memory_limit');

        // implement logic here, which detects if php has enough memory
        // to transform image
        return true;
    }

    /**
     * Upload image and create thumbnails.
     *
     * @access public
     *
     * @param string  $srcLocation  image temporary location
     * @param string  $callback     which method to use to create new image
     * @param boolean $replace      replace existing image
     *
     * @return boolean
     */
    function create($srcLocation, $callback = 'move_uploaded_file', $replace = false)
    {
        if (!function_exists($callback)) {
            return SGL::raiseError("SGL_Image: function '$callback'
                does not exist");
        }
        $destPath = $this->getPath();
        $ok = SGL_Image::_ensureDirIsWritable($destPath);
        if (PEAR::isError($ok)) {
            return $ok;
        }
        $destLocation = $destPath . '/' . $this->fileName;
        if ($replace && file_exists($destLocation)) {
            unlink($destLocation);
        } elseif (file_exists($destLocation)) { // purely for testing
            return SGL::raiseError("SGL_Image: file '$destLocation' exists");
        }
        if (!$callback($srcLocation, $destLocation)) {
            return SGL::raiseError("SGL_Image: function '$callback' failed");
        }
        $ok = $this->_toThumbnails();
        if (PEAR::isError($ok)) {
            return $ok;
        }
        $ok = $this->transform();
        if (PEAR::isError($ok)) {
            return $ok;
        }
        return true;
    }

    /**
     * Alias for SGL_Image#create($srcLocation, $callback, $replace = true).
     *
     * @access public
     *
     * @param string $srcLocation
     * @param string $callback
     *
     * @return boolean
     *
     * @see create()
     */
    function replace($srcLocation, $callback = 'move_uploaded_file')
    {
        return $this->create($srcLocation, $callback, true);
    }

    /**
     * Delete image and it's thumbnails.
     *
     * @access public
     *
     * @param mixed $fileName
     *
     * @return boolean
     */
    function delete($fileName = null)
    {
        if (is_null($fileName)) {
            if (is_null($this->fileName)) {
                return SGL::raiseError('SGL_Image: file name is not specified');
            }
            $fileName = $this->getPath() . '/' . $this->fileName;
        }
        $ok = SGL_Image::_ensureDirIsWritable(dirname($fileName));
        if (PEAR::isError($ok)) {
            return $ok;
        }

        if (!file_exists($fileName)) {
            return SGL::raiseError('SGL_Image: file not found ' . $fileName, SGL_ERROR_NOFILE);
        }
        unlink($fileName);
        $ok = $this->_toThumbnails('unlink'); // delete thumbnails
        if (PEAR::isError($ok)) {
            return $ok;
        }
        return true;
    }

    /**
     * Transform image.
     *
     * @access public
     *
     * @param mixed $section
     * @param string $pathOverride
     *
     * @return boolean
     */
    function transform($section = null, $pathOverride = null)
    {
        // do nothing if no strats were loaded or there is no strategy for
        // specified section (thumbnail)
        if (empty($this->_aStrats) || (!is_null($section)
                && !isset($this->_aStrats[$section]))) {
            return true;
        }
        reset($this->_aStrats);
        if (is_null($section)) {
            $section = key($this->_aStrats);
            // no strategy loaded for parent section
            if (!is_numeric($section)) {
                return true;
            }
            $fileName = $pathOverride
                ? $pathOverride . '/' . $this->fileName
                : $this->getPath() . '/' . $this->fileName;
            $params = &$this->_aParams;
        } else {
            $thumbDir = !empty($this->_aParams['thumbDir'])
                ? '/' . $this->_aParams['thumbDir']
                : '';
            $fileName = $pathOverride
                ? "{$pathOverride}$thumbDir/{$section}_{$this->fileName}"
                : $this->getPath() . "$thumbDir/{$section}_{$this->fileName}";
            $params = &$this->_aThumbnails[$section];
        }
        foreach ($this->_aStrats[$section] as $stratName => $stratObj) {
            $configString = $params[$stratName];
            if (is_object($configString)) {
                $configString = '';
            }
            $ok = $stratObj->init($fileName, $configString);
            if (PEAR::isError($ok)) {
                return $ok;
            }
            $ok = $stratObj->transform();
            if (PEAR::isError($ok)) {
                return $ok;
            }
            $ok = $stratObj->save($params['saveQuality']);
            if (PEAR::isError($ok)) {
                return $ok;
            }
        }
        return true;
    }

    /**
     * Transform main image and all it's thumbnails by loaded strategies.
     *
     * @access public
     *
     * @return boolean
     *
     * @see transform()
     */
    function transformAll()
    {
        $parentSection = array(null);
        $aSections = array_merge($parentSection, $this->getThumbnailNames());
        foreach ($aSections as $sectionName) {
            $ok = $this->transform($sectionName);
            if (PEAR::isError($ok)) {
                return $ok;
            }
        }
        return true;
    }

    /**
     * @access public
     *
     * @return array
     */
    function setImagePath($path = "")
    {
    	if (empty($path)) {
    		return;
    	}
        if (isset($this->_aParams['path'])) {
            $this->_aParams['path'] .= '/' . $path;
        } else {
            $this->_aParams['path'] = $path;
        }
    }

    /**
     * @access public
     *
     * @return array
     */
    function getThumbnailNames()
    {
        return array_keys($this->_aThumbnails);
    }

    /**
     * Set modification params.
     *
     * @access private
     *
     * @param array  $aParams
     *
     * @return boolean
     */
    function _setParams($aParams)
    {
        $ok = SGL_ImageConfig::paramsCheck($aParams);
        if (PEAR::isError($ok)) {
            return $ok;
        }
        SGL_ImageConfig::cleanup($aParams);
        if (isset($aParams['thumbnails'])) {
            foreach ($aParams['thumbnails'] as $thumbName => $thumbParams) {
                $ok = SGL_ImageConfig::paramsCheck($thumbParams, $thumbName);
                if (PEAR::isError($ok)) {
                    return $ok;
                }
            }
            SGL_ImageConfig::cleanup($aParams['thumbnails']);
            $this->_aThumbnails = $aParams['thumbnails'];
            unset($aParams['thumbnails']);
        }
        $this->_aParams = $aParams;
        return true;
    }

    /**
     * @access private
     *
     * @param string $callType
     * @param string $moduleName
     *
     * @return string
     */
    function _getImagePath()
    {
        $args = func_get_args();
        if (empty($args)) { // at least $callType must be specified
            return SGL::raiseError('SGL_Image: call type is not specified');
        }

        $callType   = array_shift($args); // 'url' or 'path'
        $moduleName = false;
        $path       = '';

        // get module
        if (SGL_Image::_isInstanceMethod()) {
            if (count($args)) {
                $moduleName = array_shift($args);
            } else { // we know module's name if we have an instance
                $moduleName = $this->moduleName;
            }
        } elseif (count($args)) {
            $moduleName = array_shift($args);
        }

        if ('url' == $callType) {
            // if we try to get an URL, module name must be specified,
            // otherwise it is not possible to have access to image via HTTP
            if (empty($moduleName)) {
                return SGL::raiseError('SGL_Image: module is not specified');
            }
            $path = SGL_BASE_URL . '/' . $moduleName . '/images';
        } else {
        	// if uploadDir is set in image.ini conf, overwrite
        	// starting from 'SGL_APP_ROOT'
        	if (SGL_Image::_isInstanceMethod() && isset($this->_aParams['uploadDir'])) {
        		$path = SGL_APP_ROOT . '/' . $this->_aParams['uploadDir'];
        	} elseif (!empty($moduleName)) {
                $path = SGL_MOD_DIR . '/' . $moduleName . '/www/images';
            } else {
                $path = SGL_UPLOAD_DIR;
            }
        }
        if (SGL_Image::_isInstanceMethod() && isset($this->_aParams['path'])) {
            $path .= '/' .$this->_aParams['path'];
        }

        return $path;
    }

    /**
     * Copy, move or delete thumbnails, using original image.
     *
     * @access private
     *
     * @param string $callback  operation to perform on original image
     *
     * @return boolean
     */
    function _toThumbnails($callback = 'copy')
    {
        if (empty($this->_aThumbnails)) {
            return true;
        }
        if (!function_exists($callback)) {
            return SGL::raiseError("SGL_Image: function '$callback'
                does not exist");
        }
        $aThumbs  = array_keys($this->_aThumbnails); // available thumbnails
        $origFile = $this->getPath() . '/' . $this->fileName;
        $thumbDir = empty($this->_aParams['thumbDir'])
            ? '' : '/' . $this->_aParams['thumbDir'];
        foreach ($aThumbs as $thumbName) {
            $destLocation = $this->getPath() . $thumbDir . '/' . $thumbName
                . '_' . $this->fileName;
            $ok = SGL_Image::_ensureDirIsWritable(dirname($destLocation));
            if (PEAR::isError($ok)) {
                return $ok;
            }
            if (file_exists($destLocation)) {
                unlink($destLocation);
            }
            if ($callback != 'unlink') {
                if (!$callback($origFile, $destLocation)) {
                    return SGL::raiseError("SGL_Image: function
                        '$callback' failed");
                }
                $ok = $this->transform($thumbName);
                if (PEAR::isError($ok)) {
                    return $ok;
                }
            }
        }
        return true;
    }

    /**
     * @access private
     * @static
     *
     * @param string $dirName
     *
     * @return boolean
     */
    function _ensureDirIsWritable($dirName)
    {
        if (!is_writable($dirName)) {
            require_once 'System.php';
            $ok = System::mkDir(array('-p', $dirName));
            if (PEAR::isError($ok)) {
                return $ok;
            }
            if (!$ok) {
                return SGL::raiseError("SGL_Image: can not make directory
                    '$dirName' writable");
            }
            $mask = umask(0);
            $ok   = @chmod($dirName, 0777);
            if (!$ok) {
                return SGL::raiseError("SGL_Image: can not perform chmod on
                    directory '$dirName'");
            }
            umask($mask);
        }
        return true;
    }

    /**
     * @access private
     *
     * @return boolean
     */
    function _loadStrategies()
    {
        // transformation: main container + thumbnails
        $aConfiguration = array_merge(array($this->_aParams), $this->_aThumbnails);
        $aAvailParams = SGL_ImageConfig::getAvailableParams();
        $aDrivers     = array();
        foreach ($aConfiguration as $container => $aParams) {

            // available strategies for current container
            $aStrats = array_diff(array_keys($aParams), $aAvailParams);

            // while testing mock object can be passed as a driver
            if (!is_object($aParams['driver'])) {
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
            }

            // load strategies
            foreach ($aStrats as $strategyName) {
                // skip strategy without params or switched off strategies
                if (empty($aParams[$strategyName])) {
                    continue;
                }

                // while testing a mock obejct can be passed
                if (is_object($aParams[$strategyName])) {
                    $this->_aStrats[$container][$strategyName] = &$aParams[$strategyName];
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
                $this->_aStrats[$container][$strategyName] = new $stratClass(
                    $aDrivers[$driverSignature]);
            }
        }
        return true;
    }

    /**
     * Check if method is called statically.
     *
     * @access private
     * @static
     *
     * @return boolean
     *
     * @see _isInstanceMethod()
     */
    function _isStaticMethod()
    {
        return !SGL_Image::_isInstanceMethod();
    }

    /**
     * Check if SGL_Image instance is initialized.
     *
     * @access private
     * @static
     *
     * @return boolean
     *
     * @see _isStaticMethod()
     */
    function _isInstanceMethod()
    {
        return isset($this) && is_a($this, 'SGL_Image');
    }
}

/**
 * @staticvar array
 *
 * @see SGL_ImageConfig::getProperty()
 */
$GLOBALS['_SGL']['ImageConfig']['aProps']['_aMainParams'] =
    array('driver', 'saveQuality', 'thumbDir', 'uploadDir');

/**
 * @staticvar array
 *
 * @see SGL_ImageConfig::getProperty()
 */
$GLOBALS['_SGL']['ImageConfig']['aProps']['_aAdditionalParams'] =
    array('inherit', 'thumbnails', 'inheritThumbnails');

/**
 * Manipulate with SGL_Image configuration.
 *
 * @package    SGL
 * @author     Dmitri Lakachauskis <dmitri@telenet.lv>
 */
class SGL_ImageConfig
{
    /**
     * Remove processed params from config array to keep it clean.
     *
     * @access public
     * @static
     *
     * @param array $aParams
     */
    function cleanup(&$aParams)
    {
        if (empty($aParams)) {
            return;
        }
        reset($aParams);
        $firstkey = key($aParams);
        if (is_array($aParams[$firstkey])) {
            foreach ($aParams as $key => $value) {
                SGL_ImageConfig::cleanup($aParams[$key]);
            }
            return;
        }
        reset($aParams);
        $aAddParams = &SGL_ImageConfig::getProperty('_aAdditionalParams');
        foreach ($aAddParams as $param) {
            if (isset($aParams[$param]) && is_scalar($aParams[$param])) {
                unset($aParams[$param]);
            }
        }
    }

    /**
     * Check if all params needed for SGL_Image initialization are specified.
     *
     * @access public
     * @static
     *
     * @param array  $aParams
     * @param string $sectionName
     *
     * @return boolean
     */
    function paramsCheck($aParams, $sectionName = '')
    {
        $aMainParams = &SGL_ImageConfig::getProperty('_aMainParams');
        //hack, remove uploadDir to keep BC.
        // need to find a better way
        if (!isset($aParams['uploadDir'])) {
            $aParams['uploadDir']='';
        }

        $aRet = array_diff($aMainParams, array_keys($aParams));
        if (!empty($aRet)) {
            $error = "SGL_ImageConfig: missing parameters";
            if (!empty($sectionName)) {
                $error .= ' for [' . $sectionName . ']';
            }
            $error .= ': ' . implode(', ', $aRet);
            return SGL::raiseError($error);
        }
        return true;
    }

    /**
     * Get all available config params. This does not include strategies.
     *
     * @access public
     * @static
     *
     * @return array
     */
    function getAvailableParams()
    {
        $aAddParams  = &SGL_ImageConfig::getProperty('_aAdditionalParams');
        $aMainParams = &SGL_ImageConfig::getProperty('_aMainParams');
        return array_merge($aAddParams, $aMainParams);
    }

    /**
     * Extract params from file. While extracting we take into account
     * config sections' inheritance.
     *
     * @access public
     * @static
     *
     * @param string $fileName
     *
     * @return array
     */
    function getParamsFromFile($fileName)
    {
        if (!is_readable($fileName)) {
            return SGL::raiseError("SGL_ImageConfig: '$fileName' is not readable");
        }
        $aRet = parse_ini_file($fileName, true);
        if (!isset($aRet[SGL_IMAGE_DEFAULT_SECTION])) {
            return SGL::raiseError('SGL_ImageConfig: default container not found');
        }
        $aSections = SGL_ImageConfig::getUniqueSectionNames($aRet);

        ksort($aRet);
        $aResult = array();
        $default = array();
        foreach ($aSections as $sectionName) {
            $ret = SGL_ImageConfig::_getSectionData($aRet, $sectionName, $default);
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
     * Extract params from config string.
     *
     * @access public
     * @static
     *
     * @param string $configString
     *
     * @return array
     */
    function getParamsFromString($configString)
    {
        $aRet = array();
        if (empty($configString)) {
            return $aRet;
        }
        $aParams = array_map('trim', explode(',', $configString));
        foreach ($aParams as $param) {
            if (false !== strpos($param, ':')) {
                $arr = explode(':', $param);
                $aRet[$arr[0]] = $arr[1];
            } else {
                $aRet[] = $param; // when only one parameter exists
            }
        }
        return $aRet;
    }

    /**
     * Helper method for SGL_ImageConfig::getParamsFromFile().
     *
     * @access public
     * @static
     *
     * @param array $aSections
     *
     * @return array
     *
     * @see getParamsFromFile()
     */
    function getUniqueSectionNames($aSections)
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
     * Helper method for SGL_ImageConfig::getParamsFromFile(), which does
     * all the dirty work i.e. merging sections' options.
     *
     * @access private
     * @static
     *
     * @param array  $aData
     * @param string $sectionName
     * @param array  $override
     *
     * @return array
     *
     * @see getParamsFromFile()
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
            }
            if (!empty($aThumbs)) {
                $aResult['thumbnails'] = $aThumbs;
            }
        }
        return $aResult;
    }

    /**
     * Replacement for PEAR::getStaticProperty(). Vars are initialized
     * by putting values directly in $GLOBALS array. In that case we can
     * ignore annoying error for PHP 5.1.6.
     *
     * @access public
     * @static
     *
     * @param string $name
     *
     * @return mixed
     */
    function &getProperty($name)
    {
        $aProps = &$GLOBALS['_SGL']['ImageConfig']['aProps'];
        if (!isset($aProps[$name])) {
            $aProps[$name] = null;
        }
        return $aProps[$name];
    }
}

/**
 * Base image transformation strategy.
 *
 * @package    SGL
 * @author     Dmitri Lakachauskis <dmitri@telenet.lv>
 *
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
     *
     * @param PEAR Image_Transfrom $driver
     */
    function SGL_ImageTransformStrategy(&$driver)
    {
        $this->driver = &$driver;
    }

    /**
     * Init strategy i.e. load image file and parse params.
     *
     * @access public
     *
     * @param string $fileName
     * @param string $configString
     *
     * @return boolean
     */
    function init($fileName, $configString = '')
    {
        $ok = $this->load($fileName);
        if (PEAR::isError($ok)) {
            return $ok;
        }
        if (!empty($configString)) {
            $aParsedParams = SGL_ImageConfig::getParamsFromString($configString);
            $this->setParams($aParsedParams);
        }
        return true;
    }

    /**
     * @access public
     *
     * @param array $aParams
     */
    function setParams($aParams)
    {
        $this->aParams = $aParams;
    }

    /**
     * Load file.
     *
     * @access public
     *
     * @param string $fileName
     *
     * @return boolean
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
     *
     * @param $saveQuality
     * @param $saveFormat   jpg, gif or png (not support by SGL_Image yet)
     *
     * @return boolean
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
    function transform()
    {
    }
}

?>