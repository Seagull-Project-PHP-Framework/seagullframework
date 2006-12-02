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

        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();

        $this->fileName   = $fileName;
        $this->moduleName = $moduleName;
    }

    /**
     * @access public
     * @static
     *
     * @return array
     */
    function getAvailableParams()
    {
        $aAddParams  = &PEAR::getStaticProperty('SGL_Image', '_aAdditionalParams');
        $aMainParams = &PEAR::getStaticProperty('SGL_Image', '_aMainParams');
        return array_merge($aAddParams, $aMainParams);
    }

    /**
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
            return SGL::raiseError("SGL_Image: '$fileName' is not readable");
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
                $params = SGL_Image::getParamsFromFile($params);
                if (PEAR::isError($params)) {
                    return $params;
                }
                $params = $params[$container];
            } else {
                return SGL::raiseError("SGL_Image: file '$params' not found");
            }
        } elseif (!is_array($params)) { // wrong parameters' type
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
     *
     * @param array  $aParams
     *
     * @return boolean
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
        return true;
    }

    /**
     * Generate filename.
     *
     * @access public
     *
     * @param string $salt
     *
     * @return string
     */
    function generateUniqueFileName($salt = '')
    {
        return md5($salt . SGL_Session::getUid() . SGL_Date::getTime());
    }

    /**
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
        $callback = SGL_Image::_isClassMethod()
            ? array('SGL_Image', '_getImagePath')
            : array($this, '_getImagePath');
        return call_user_func_array($callback, $args);
    }

    /**
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
        $callback = SGL_Image::_isClassMethod()
            ? array('SGL_Image', '_getImagePath')
            : array($this, '_getImagePath');
        return call_user_func_array($callback, $args);
    }

    /**
     * Upload image and create thumbnails.
     *
     * @access public
     *
     * @param string  $srcLocation  image temporary location
     * @param boolean $replace      replace existing image
     * @param string  $callback     which method to use to create new image
     *
     * @return mixed
     */
    function upload($srcLocation, $replace = false, $callback = 'move_uploaded_file')
    {
        if (!function_exists($callback)) {
            return SGL::raiseError("SGL_Image: function '$callback'
                does not exist");
        }
        // initialize filename if one is not set
        if (!$replace && is_null($this->fileName)) {
            $this->fileName = $this->generateUniqueFileName($srcLocation);
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
        $ok = $this->transform();
        if (PEAR::isError($ok)) {
            return $ok;
        }
        $ok = $this->_toThumbnails();
        if (PEAR::isError($ok)) {
            return $ok;
        }
        return $replace ? true : $this->fileName;
    }

    /**
     * @access public
     *
     * @param string $srcLocation
     * @param string $callback
     *
     * @return boolean
     *
     * @see upload()
     */
    function replace($srcLocation, $callback = 'move_uploaded_file')
    {
        return $this->upload($srcLocation, true, $callback);
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
        unlink($fileName);
        $ok = $this->_toThumbnails('unlink'); // delete thumbnails
        if (PEAR::isError($ok)) {
            return $ok;
        }
        return true;
    }

    /**
     * @access public
     *
     * @param mixed $section
     *
     * @return boolean
     */
    function transform($section = null)
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
            $fileName = $this->getPath() . '/' . $this->fileName;
            $params   = &$this->_aParams;
        } else {
            $thumbDir = !empty($this->_aParams['thumbDir'])
                ? '/' . $this->_aParams['thumbDir']
                : '';
            $fileName = $this->getPath() . $thumbDir . '/' .
                $section . '/' . $this->fileName;
            $params = &$this->_aThumbnails[$section];
        }
        foreach ($this->_aStrats[$section] as $stratName => $stratObj) {
            $ok = $stratObj->init($fileName, $params[$stratName]);
            if (PEAR::isError($ok)) {
                return $ok;
            }
            $ok = $stratObj->transform();
            if (PEAR::isError($ok)) {
                return $ok;
            }
            $ok = $stratObj->save($params[$stratName]['saveQuality']);
            if (PEAR::isError($ok)) {
                return $ok;
            }
        }
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
            if (!empty($moduleName)) {
                $path = SGL_MOD_DIR . '/' . $moduleName . '/www/images';
            } else {
                $path = SGL_UPLOAD_DIR;
            }
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
                . '/' . $this->fileName;
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
     * @static
     *
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
     * @static
     *
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
     * @static
     *
     * @param array $aParams
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
     * @static
     *
     * @param array  $aParams
     * @param string $sectionName
     *
     * @return boolean
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
     * @static
     *
     * @param array $aSections
     *
     * @return array
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
     * @static
     *
     * @param array  $aData
     * @param string $sectionName
     * @param array  $override
     *
     * @return array
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
     * Extract params from config string.
     *
     * @access public
     *
     * @param string $configString
     *
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
                $aRet[] = $param; // when only one parameter exists
            }
        }
        return $aRet;
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
            $this->setParams($this->getParamsFromString($configString));
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