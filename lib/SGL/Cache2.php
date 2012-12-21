<?php

/**
 * SGL wrapper to any third-party cache library.
 * Currently only PEAR::Cache_Lite and Zend_Cache are supported.
 *
 * @package SGL
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Cache2
{
    /**
     * Instances of Cache objects.
     *
     * @var array
     */
    private static $_aInstances = array();

    /**
     * This method is used to get Cache object instance.
     *
     * @param array $aOptions  cache options
     * @param string $lib      cache library to use
     *
     * @return object
     */
    public static function &singleton($aOptions = array(), $lib = 'Zend_Cache')
    {
        $frontend = !empty($aOptions['frontend'])
            ? $aOptions['frontend'] : 'Core';
        $backend  = !empty($aOptions['backend'])
            ? $aOptions['backend'] : 'File';
        $fOptions = !empty($aOptions['frontendOptions'])
            ? $aOptions['frontendOptions'] : array();
        $bOptions = !empty($aOptions['backendOptions'])
            ? $aOptions['backendOptions'] : array();

        $idOpts = serialize($fOptions) . '_' . serialize($bOptions);
        $id     = md5("{$lib}_{$frontend}_{$backend}_{$idOpts}");

        if (isset(self::$_aInstances[$id])) {
            $ret = self::$_aInstances[$id];
        } else {
            $ret = self::_factory($lib, $frontend, $backend, $fOptions, $bOptions);
            if (!PEAR::isError($ret)) {
                self::$_aInstances[$id] = $ret;
            }
        }
        return $ret;
    }

    /**
     * Factory method.
     *
     * @param string $lib       library name
     * @param string $frontend  frontend name
     * @param string $backend   backend name
     * @param array $fOpts      frontend options
     * @param array $bOpts      backend options
     *
     * @return object
     */
    private static function _factory($lib, $frontend, $backend, $fOpts, $bOpts)
    {
        // ensure minimum frontend options are set
        $fOpts['caching'] = isset($fOpts['caching'])
            ? $fOpts['caching'] : SGL_Config::get('cache.enabled');
        $fOpts['lifetime'] = isset($fOpts['lifetime'])
            ? $fOpts['lifetime'] : SGL_Config::get('cache.lifetime');
        $fOpts['writeControl'] = isset($fOpts['writeControl'])
            ? $fOpts['writeControl'] : SGL_Config::get('cache.writeControl');
        $fOpts['cleaningFactor'] = isset($fOpts['cleaningFactor'])
            ? $fOpts['cleaningFactor'] : SGL_Config::get('cache.cleaningFactor');
        $fOpts['automaticSerialization'] = isset($fOpts['automaticSerialization'])
            ? $fOpts['automaticSerialization'] : true;

        // ensure minimum backend options are set
        $bOpts['cacheDir'] = isset($bOpts['cacheDir'])
            ? $bOpts['cacheDir'] : SGL_TMP_DIR . '/';
        $bOpts['readControl'] = isset($bOpts['readControl'])
            ? $bOpts['readControl'] : SGL_Config::get('cache.readControl');

        switch ($lib) {
            case 'Zend_Cache':
                // load library
                require_once 'Zend/Cache.php';

                // fix frontend options
                $fOpts['write_control'] = $fOpts['writeControl'];
                $fOpts['automatic_cleaning_factor'] = $fOpts['cleaningFactor'];
                $fOpts['automatic_serialization'] = $fOpts['automaticSerialization'];
                unset($fOpts['writeControl'], $fOpts['cleaningFactor'],
                    $fOpts['automaticSerialization']);

                // fix backend options
                $bOpts['cache_dir'] = $bOpts['cacheDir'];
                $bOpts['read_control'] = $bOpts['readControl'];
                unset($bOpts['cacheDir'], $bOpts['readControl']);

                // default backend options are needed only for File backend
                if ($backend != 'File') {
                    unset($bOpts['cache_dir'], $bOpts['read_control']);
                }

                try {
                    $ret = Zend_Cache::factory($frontend, $backend, $fOpts, $bOpts);
                } catch (Zend_Cache_Exception $e) {
                    $msg = 'SGL_Cache2: ' . $e->getMessage();
                    $ret = SGL::raiseError($msg, SGL_ERROR_INVALIDARGS);
                }
                break;

            case 'Cache_Lite':
                // load library
                if ($frontend == 'Core') {
                    require_once SGL_CORE_DIR . '/Cache.php';
                    $className = 'SGL_Cache_Lite';
                } else {
                    require_once 'Cache/Lite/' . $frontend . '.php';
                    $className = $lib . '_' . $frontend;
                }

                // fix frontend options
                $fOpts['lifeTime'] = $fOpts['lifetime'];
                $fOpts['automaticCleaningFactor'] = $fOpts['cleaningFactor'];
                unset($fOpts['lifetime'], $fOpts['cleaningFactor']);

                $ret = new $className(array_merge($fOpts, $bOpts));
                break;

            default:
                $msg = 'SGL_Cache2: unknown caching library';
                $ret = SGL::raiseError($msg, SGL_ERROR_INVALIDARGS);
        }
        return $ret;
    }

    /**
     * Make cache ID from method name and it's arguments.
     *
     * @param string $methodName
     * @param array $aArgs
     *
     * @return string
     */
    function makeCacheIdFromMethodName($methodName, $aArgs = array())
    {
        return md5($methodName . serialize($aArgs));
    }
}

?>