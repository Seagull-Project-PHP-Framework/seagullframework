<?php
/**
 * File management utility methods.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL2_File
{
    protected static $_file;

    /**
     *
     * Hack for [[php::file_exists() | ]] that checks the include_path.
     *
     * Use this to see if a file exists anywhere in the include_path.
     *
     * @param string $file Check for this file in the include_path.
     *
     * @return mixed If the file exists and is readble in the include_path,
     * returns the path and filename; if not, returns boolean false.
     *
     */
    public static function exists($file)
    {
        // no file requested?
        $file = trim($file);
        if (! $file) {
            return false;
        }

        // using an absolute path for the file?
        // dual check for Unix '/' and Windows '\',
        // or Windows drive letter and a ':'.
        $abs = ($file[0] == '/' || $file[0] == '\\' || $file[1] == ':');
        if ($abs && file_exists($file)) {
            return $file;
        }

        // using a relative path on the file
        $path = explode(PATH_SEPARATOR, ini_get('include_path'));
        foreach ($path as $base) {
            // strip Unix '/' and Windows '\'
            $target = rtrim($base, '\\/') . DIRECTORY_SEPARATOR . $file;
            if (file_exists($target)) {
                return $target;
            }
        }
        // never found it
        return false;
    }

    /**
     *
     * Uses [[php::include() | ]] to run a script in a limited scope.
     *
     * @param string $file The file to include.
     *
     * @return mixed The return value of the included file.
     *
     */
    public static function load($file)
    {
        self::$_file = self::exists($file);
        if (!self::$_file) {
            // could not open the file for reading
            throw new Exception('File does not exist or is not readable: '.$file);
        }

        // clean up the local scope, then include the file and
        // return its results.
        unset($file);
        return include self::$_file;
    }

    /**
     * Returns TRUE if the $filename is readable, or FALSE otherwise.
     * This function uses the PHP include_path, where PHP's is_readable()
     * does not.
     *
     * @param string   $filename
     * @return boolean
     */
//    public static function isReadable($filename)
//    {
//        if (! @fopen($filename, 'r', true)) {
//            return false;
//        }
//        return true;
//    }

    /**
     * Copies directories recursively.
     *
     * @param string $source
     * @param string $dest
     * @param boolean $overwrite
     * @return boolean
     * @todo chmod is needed
     */
    function copyDir($source, $dest, $overwrite = false)
    {
        if (!is_dir($dest)) {
            if (!is_writable(dirname($dest))) {
                return SGL::raiseError('filesystem not writable', SGL2_ERROR_INVALIDFILEPERMS);
            }
            mkdir($dest);
        }
        // if the folder exploration is successful, continue
        if ($handle = opendir($source)) {
            // as long as storing the next file to $file is successful, continue
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    $path = $source . '/' . $file;
                    if (is_file($path)) {
                        if (!is_file($dest . '/' . $file) || $overwrite) {
                            if (!@copy($path, $dest . '/' . $file)){
                                return SGL::raiseError('filesystem not writable',
                                    SGL2_ERROR_INVALIDFILEPERMS);
                            }
                        }
                    } elseif (is_dir($path)) {
                        if (!is_dir($dest . '/' . $file)) {
                            if (!is_writable(dirname($dest . '/' . $file))) {
                                return SGL::raiseError('filesystem not writable',
                                    SGL2_ERROR_INVALIDFILEPERMS);
                            }
                            mkdir($dest . '/' . $file); // make subdirectory before subdirectory is copied
                        }
                        SGL2_File::copyDir($path, $dest . '/' . $file, $overwrite); //recurse
                    }
                }
            }
            closedir($handle);
        }
        return true;
    }

    /**
     * Removes a directory and its contents recursively.
     *
     * @param string $dir  path to directory
     */
    function rmDir($dir, $args = '')
    {
        require_once 'System.php';
        if ($args && $args[0] == '-') {
            $args = substr($args, 1);
        }
        System::rm("-{$args}f $dir");
    }
}
?>