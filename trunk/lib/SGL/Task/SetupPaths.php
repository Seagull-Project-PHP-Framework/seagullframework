<?php

/**
 * @package Task
 */
class SGL_Task_SetupPaths extends SGL_Task
{
    /**
     * Sets up the minimum paths required for framework execution.
     *
     * - SGL_SERVER_NAME must always be known in order to rewrite config file
     * - SGL_PATH is the filesystem root path
     * - pear include path is setup
     * - PEAR.php included for errors, etc
     *
     * @param array $data
     */
    function run($conf = array())
    {
        //define('SGL_SERVER_NAME', self::hostnameToFilename());
        if (defined('SGL_PEAR_INSTALLED')) {
            define('SGL_PATH', '@PHP-DIR@/Seagull');
            define('SGL_LIB_PEAR_DIR', '@PHP-DIR@');
        } else {
            $path = $GLOBALS['varDir']  . '/INSTALL_COMPLETE.php';
            if (is_file($path)) {
                $configFile = $GLOBALS['varDir']  . '/'
                    . self::hostnameToFilename() . '.conf.php';
                require_once $configFile;
                if (!empty($conf['path']['installRoot'])) {
                    define('SGL_PATH', $conf['path']['installRoot']);
                }
            } else {
                define('SGL_PATH', $GLOBALS['rootDir']);
            }
            //  put sgl lib dir in include path
            $sglLibDir =  SGL_PATH . '/lib';
        }

        $aPaths = array(
            $sglLibDir,
            get_include_path()
        );
        set_include_path(implode(PATH_SEPARATOR, $aPaths));
    }

    /**
     * Determines the name of the INI file, based on the host name.
     *
     * If PHP is being run interactively (CLI) where no $_SERVER vars
     * are available, a default 'localhost' is supplied.
     *
     * @return  string  the name of the host
     */
    function hostnameToFilename()
    {
        //  start with a default
        $hostName = 'localhost';
        if (!SGL::runningFromCLI()) {

            // Determine the host name
            if (!empty($_SERVER['SERVER_NAME'])) {
                $hostName = $_SERVER['SERVER_NAME'];

            } elseif (!empty($_SERVER['HTTP_HOST'])) {
                //  do some spoof checking here, like
                //  if (gethostbyname($_SERVER['HTTP_HOST']) != $_SERVER['SERVER_ADDR'])
                $hostName = $_SERVER['HTTP_HOST'];
            } else {
                //  if neither of these variables are set
                //  we're going to have a hard time setting up
                die('Could not determine your server name');
            }
            // Determine if the port number needs to be added onto the end
            if (!empty($_SERVER['SERVER_PORT'])
                    && $_SERVER['SERVER_PORT'] != 80
                    && $_SERVER['SERVER_PORT'] != 443) {
                $hostName .= '_' . $_SERVER['SERVER_PORT'];
            }
        }
        return $hostName;
    }
}

?>