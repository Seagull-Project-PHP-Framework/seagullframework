<?php
/**
 * File management utility methods.
 *
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
     */
    function copyDir($source, $dest, $overwrite = false)
    {
        if (!is_dir($dest)) {
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
    function rmDir($dir)
    {
        require_once 'System.php';
        System::rm("-rf $dir");
    }
}
?>