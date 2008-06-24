<?php
/**
 * File management utility methods.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_File
{
    /**
     * Copies directories recursively.
     *
     * @static
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
                return SGL::raiseError('filesystem not writable', SGL_ERROR_INVALIDFILEPERMS);
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
                                    SGL_ERROR_INVALIDFILEPERMS);
                            }
                        }
                    } elseif (is_dir($path)) {
                        if (!is_dir($dest . '/' . $file)) {
                            if (!is_writable(dirname($dest . '/' . $file))) {
                                return SGL::raiseError('filesystem not writable',
                                    SGL_ERROR_INVALIDFILEPERMS);
                            }
                            mkdir($dest . '/' . $file); // make subdirectory before subdirectory is copied
                        }
                        SGL_File::copyDir($path, $dest . '/' . $file, $overwrite); //recurse
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