<?php
/**
 * Auto Loader for the LazyPHP Framework
 *
 */
class Uber_Loader
{
    private static $_isOrdered = false;
    /**
     * @var array holding the different patterns for autoloading classes
     */
    private static $_autoloadPatterns = array('classes' => array() , 'dynamic' => array());
    private static $_namespaces = array();
    private static $_namespaceExceptionHandling = array();
    private static $_namespaceRegex = '';

    /**
     * Register a PEAR/Zend like namespace for class files.
     *
     * Uber_Loader::registerNamespace('Zend');
     *
     * will autoload all Zend_* classes from the includepath.
     *
     * Uber_Loader::registerNamespace('Zend','/var/src/');
     *
     * will autoload all Zend_* classes from the basedir /var/src/
     *
     *
     * @param string $namespace
     * @param string|boolean $baseDir
     * @param boolean $throwException create the class if missing and throw an exception if instantiated
     */
    public static function registerNamespace($namespace, $baseDir = true, $throwException = true)
    {
        self::$_namespaces[$namespace] = $baseDir;
        self::$_namespaceExceptionHandling[$namespace] = $throwException;
        self::$_namespaceRegex = '(' . implode('|', array_keys(self::$_namespaces)) . ')_.*';
    }

    /**
     * Register:
     *
     * array('Class_Name'=>'Class/Name.php');
     *
     * or
     *
     * array('preg_replace'=>array('_'=>'/'),'suffix'=>'.php','basedir'=>'lib/', 'throwException'=>true);
     *
     * or
     *
     * array('str_replace'=>array('_'=>'/'),'suffix'=>'.php','basedir'=>'lib/', 'throwException'=>true);
     *
     * or
     *
     * array('preg_match'=>'Module_(.*)','filename'=>'Module/$1.php','suffix'=>'.php','basedir'=>'lib/', 'throwException'=>true);
     *
     * or
     *
     * array('classes'=>array('Component1','Component2'),'basedir'=>'Module/','filename'=>'$class','suffix'=>'.php', 'throwException'=>true);
     *
     * @param array $pattern
     * @param integer $priority
     * @param boolean $throwException create the class if missing and throw an exception if instantiated
     * @throws Uber_Loader_Exception
     */
    public static function addAutoloadPattern(array $pattern, $priority = 1, $throwException = true)
    {
        self::$_isOrdered = false;
        if (count($pattern) == 1 && ($key = key($pattern)) && ! is_numeric($key) && is_string($pattern[$key])) {
            /**
             * array('Class_Name'=>'Class/Name.php');
             */
            $className = key($pattern);
            $fileName = $pattern[$className];
            self::$_autoloadPatterns['classes'][$className][$priority] = $fileName;
        } else {
            if (isset($pattern['classes']) && is_array($pattern['classes'])) {
                /**
                 * array('classes'=>array('Component1','Component2'),'basedir'=>'Module/','filename'=>'$class.php');
                 */
                $dirName = isset($pattern['basedir']) ? $pattern['basedir'] : '';
                foreach ($pattern['classes'] as $class) {
                    $fileName = $dirName . DS . $pattern['filename'] . (isset($pattern['suffix']) ? $pattern['suffix'] : '');
                    self::$_autoloadPatterns['classes'][$class][$priority] = $fileName;
                }
            } else {
                if (isset($pattern['preg_match']) && self::validateRegex($pattern['preg_match'])) {
                    /**
                     * array('preg_match'=>'Module_(.*)','filename'=>'Module/$1.php','basedir'=>'lib/'));
                     */
                    $pattern['type'] = 'preg_match';
                    $pattern['priority'] = isset($pattern['priority']) ? $pattern['priority'] : $priority;
                    $pattern['throwException'] = $throwException;
                    self::$_autoloadPatterns['dynamic'][] = $pattern;
                } else {
                    if (isset($pattern['preg_replace'])) {
                        /**
                         * array('preg_replace'=>array('_'=>'/'),'suffix'=>'.php','basedir'=>'lib/'));
                         */
                        $pattern['type'] = 'preg_replace';
                        $pattern['priority'] = isset($pattern['priority']) ? $pattern['priority'] : $priority;
                        $pattern['throwException'] = $throwException;
                        self::$_autoloadPatterns['dynamic'][] = $pattern;
                    } else {
                        if (isset($pattern['str_replace']) && isset($pattern['replacement'])) {
                            $pattern['type'] = 'str_replace';
                            $pattern['priority'] = isset($pattern['priority']) ? $pattern['priority'] : $priority;
                            $pattern['throwException'] = $throwException;
                            self::$_autoloadPatterns['dynamic'][] = $pattern;
                        } else {
                            if (isset($pattern['str_replace']) && is_array($pattern['str_replace']) && ! is_numeric(key($pattern['str_replace']))) {
                                /**
                                 * array('str_replace'=>array('_'=>'/'),'suffix'=>'.php','basedir'=>'lib/'));
                                 */
                                $pattern['type'] = 'str_replace';
                                $pattern['replacement'] = array_values($pattern['str_replace']);
                                $pattern['str_replace'] = array_keys($pattern['str_replace']);
                                $pattern['priority'] = isset($pattern['priority']) ? $pattern['priority'] : $priority;
                                $pattern['throwException'] = $throwException;
                                self::$_autoloadPatterns['dynamic'][] = $pattern;
                            } else {
                                throw new Uber_Loader_Exception('Invalid autoload pattern: ' . var_export($pattern, true));
                            }
                        }
                    }
                }
            }
        }
    }

    public static function resetAutoloadPatterns()
    {
        self::$_autoloadPatterns = array('classes' => array() , 'dynamic' => array());
        self::$_namespaces = array();
        Uber::initUberAutoload();
    }

    public static function setAutoloadPatterns($patterns)
    {
        foreach ($patterns as $pattern) {
            self::addAutoloadPattern($pattern);
        }
    }

    public static function registerAutoload()
    {
        spl_autoload_register(array('Uber_Loader' , 'autoload'));
    }

    protected static function _isValidClassName($className)
    {
        $result = preg_match('/^[A-Za-z_]+[A-Za-z0-9_]*$/', $className);
        return $result;
    }

    public static function sortDynamicPatterns($a, $b)
    {
        if ($a['priority'] === $b['priority']) {
            return 0;
        } else {
            return ($a['priority'] > $b['priority']) ? 1 : - 1;
        }
    }

    protected static function _handlePregMatchPattern($className, $pattern)
    {
        $found = false;
        if (preg_match('|' . $pattern['preg_match'] . '|', $className, $matches)) {
            $dirname = isset($pattern['basedir']) ? $pattern['basedir'] : '';
            $fileName = $pattern['filename'];
            $searches = array();
            $replacements = array();
            foreach ($matches as $i => $match) {
                $searches[] = '$' . $i;
                $replacements[] = $match;
            }
            $fileName = str_replace($searches, $replacements, $fileName);
            if (isset($pattern['eval']) && $pattern['eval'] == true) {
                $eval = '$fileName = ' . $fileName . ';';
                @eval($eval);
            }
            $fileName = $dirname . $fileName;
            if (is_readable($fileName)) {
                include ($fileName);
                $found = true;
            }
        }
        return $found;
    }

    protected static function _handlePregReplacePattern($className, $pattern)
    {
        $found = false;
        $name = $className;
        if (! is_array($pattern['preg_replace'])) {
            $pattern['preg_replace'] = array($pattern['preg_replace']);
        }
        if (isset($pattern['replacement']) && ! is_array($pattern['replacement'])) {
            $pattern['replacement'] = array($pattern['replacement']);
        }
        $modifier = '';
        if (isset($pattern['eval']) && $pattern['eval'] == true) {
            $modifier = 'e';
        }
        for ($pi = 0; $pi < count($pattern['preg_replace']); $pi ++) {
            if (isset($pattern['preg_replace'][0])) {
                $name = preg_replace('|' . $pattern['preg_replace'][$pi] . '|' . $modifier,
                    isset($pattern['replacement'][$pi])
                        ? $pattern['replacement'][$pi]
                        : $pattern['replacement'][0], $name);
            }
        }
        //  path might be multiple paths with separator
        $dir = isset($pattern['basedir']) ? $pattern['basedir'] : '';
        if (isset($dir) && preg_split("/\\".PATH_SEPARATOR."/", $dir)) {
            $paths = preg_split("/\\".PATH_SEPARATOR."/", $dir);
        } else {
            $paths = array($dir);
        }
        foreach ($paths as $dir) {
            $checkFileName = $dir . DS . $name;
            $checkFileName = $checkFileName . (isset($pattern['suffix']) ? $pattern['suffix'] : '');
            if (is_readable($checkFileName)) {
                $fileName = $checkFileName;
                include ($fileName);
                $found = true;
                break;
            }
        }
        return $found;
    }

    protected static function _handleStrReplacePattern($className, $pattern)
    {
        $found = false;
        $name = str_replace($pattern['str_replace'], $pattern['replacement'], $className);
        $dir = isset($pattern['basedir']) ? $pattern['basedir'] : '';
        $checkFileName = $dir . DS . $name;
        $checkFileName = $checkFileName . (isset($pattern['suffix']) ? $pattern['suffix'] : '');
        if (is_readable($checkFileName)) {
            $fileName = $checkFileName;
            include ($fileName);
            $found = true;
        }
        return $found;
    }

    /**
     * Will include the class from the filename which is determined based on the registered
     * namespaces and autoload patterns.
     *
     * If the class does not exist, it will create an empty class which throws an exception
     * upon construct or static access. Like this you can recover from the error instead of
     * raising a fatal error.
     *
     * @param string $className
     * @throws Uber_Loader_Exception
     */
    public static function autoload($className)
    {
        if (! self::_isValidClassName($className)) {
            throw new Uber_Loader_Exception('Class Name "' . $className . '" is invalid', - 3);
        }
        $throwException = true;
        $found = false;
        $matched = false;
        $tryToInclude = false;
        if (! empty(self::$_namespaceRegex) && preg_match(':' . self::$_namespaceRegex . ':', $className, $matches)) {
            $matched = true;
            $baseDir = self::$_namespaces[$matches[1]];
            $throwException = self::$_namespaceExceptionHandling[$matches[1]];
            if ($baseDir !== true) {
                if (!is_array($baseDir)) {
                    $fileName = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
                    if (is_readable($fileName)) {
                        $tryToInclude = true;
                    }
                } else {
                    foreach($baseDir as $bDir) {
                        $fileName = rtrim($bDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
                        if (is_readable($fileName)) {
                            $tryToInclude = true;
                            break;
                        }
                    }
                }
            } else {
                $fileName = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
                /**
                 * we cannot check for file existance here, since no basedir is given,
                 * could be retrieved from the includepath
                 */
                $tryToInclude = true;
            }
            if ($tryToInclude && ($res = include ($fileName)) == true) {
                $found = true;
            }
        } else
            if (isset(self::$_autoloadPatterns['classes'][$className])) {
                $fileName = array_pop(self::$_autoloadPatterns['classes'][$className]);
                $fileName = str_replace('$class', $className, $fileName);
                if (is_readable($fileName)) {
                    include ($fileName);
                }
            } else {
                if (! self::$_isOrdered) {
                    usort(self::$_autoloadPatterns['dynamic'], array('Uber_Loader' , 'sortDynamicPatterns'));
                    self::$_isOrdered = true;
                }
                $found = false;
                foreach (self::$_autoloadPatterns['dynamic'] as $pattern) {
                    switch ($pattern['type']) {
                        case 'preg_match':
                            $found = self::_handlePregMatchPattern($className, $pattern);
                            break;
                        case 'preg_replace':
                            $found = self::_handlePregReplacePattern($className, $pattern);
                            break;
                        case 'str_replace':
                            $found = self::_handleStrReplacePattern($className, $pattern);
                            break;
                    }
                    if ($found === true) {
                        $throwException = isset($pattern['throwException']) ? $pattern['throwException'] : true;
                        break;
                    }
                }
            }
        if (($found || $matched) && $throwException === true && ! class_exists($className, false) && ! interface_exists($className, false)) {
            eval("class $className {
            function __construct() {
                throw new Uber_Loader_Exception('Class or interface $className not found',-1);
            }

            static function __callStatic(\$m, \$args) {
                throw new Uber_Loader_Exception('Class or interface $className not found',-2);
            }
        }");
        }
    }

    public static function validateRegex($regex)
    {
        $regex = trim($regex, '/');
        $regex = '/' . $regex . '/';
        @trigger_error('Uber_Loader_Dummy');
        @preg_match($regex, '1234');
        $errorAfter = error_get_last();
        $res = 'Uber_Loader_Dummy' == $errorAfter['message'];
        if (! $res) {
            throw new Uber_Loader_Exception('Invalid regular expression:' . $errorAfter['message']);
        }
        return $res;
    }
}
?>