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
// | Seagull 2.0                                                               |
// +---------------------------------------------------------------------------+
// $Id$


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
                throw new Exception('filesystem not writable', SGL2_ERROR_INVALIDFILEPERMS);
            }
            mkdir($dest);
        }
        // if the folder exploration is successful, continue
        if ($handle = opendir($source)) {
            // as long as storing the next file to $file is successful, continue
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    $path = $source . '/' . $file;
                    if (self::exists($path)) {
                        if (!self::exists($dest . '/' . $file) || $overwrite) {
                            if (!@copy($path, $dest . '/' . $file)){
                                throw new Exception('filesystem not writable',
                                    SGL2_ERROR_INVALIDFILEPERMS);
                            }
                        }
                    } elseif (is_dir($path)) {
                        if (!is_dir($dest . '/' . $file)) {
                            if (!is_writable(dirname($dest . '/' . $file))) {
                                throw new Exception('filesystem not writable', SGL2_ERROR_INVALIDFILEPERMS);
                            }
                            mkdir($dest . '/' . $file); // make subdirectory before subdirectory is copied
                        }
                        self::copyDir($path, $dest . '/' . $file, $overwrite); //recurse
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