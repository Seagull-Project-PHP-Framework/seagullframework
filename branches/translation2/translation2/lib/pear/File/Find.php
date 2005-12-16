<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Sterling Hughes <sterling@php.net>                           |
// +----------------------------------------------------------------------+
//
// $Id: Find.php,v 1.3 2005/06/23 15:56:30 demian Exp $
//

require_once 'PEAR.php';

/**
*  Commonly needed functions searching directory trees
*
* @access public
* @version $Id: Find.php,v 1.3 2005/06/23 15:56:30 demian Exp $
* @package File
* @author Sterling Hughes <sterling@php.net>
*/
class File_Find
{
    /**
     * internal dir-list
     * @var array
     */
    var $_dirs = array();

    /**
     * found files
     * @var array
     */
    var $files = array();

    /**
     * found dirs
     * @var array
     */
    var $directories = array();

    /**
     * Search the current directory to find matches for the
     * the specified pattern.
     *
     * @param string $pattern a string containing the pattern to search
     * the directory for.
     *
     * @param string $dirpath a string containing the directory path
     * to search.
     *
     * @param string $pattern_type a string containing the type of
     * pattern matching functions to use (can either be 'php' or
     * 'perl').
     *
     * @return array containing all of the files and directories
     * matching the pattern or null if no matches
     *
     * @author Sterling Hughes <sterling@php.net>
     * @access public
     */
    function &glob($pattern, $dirpath, $pattern_type = 'php')
    {
        $dh = @opendir($dirpath);

        if (!$dh) {
            $pe = PEAR::raiseError("Cannot open directory");
            return $pe;
        }

        $match_function = File_Find::_determineRegex($pattern, $pattern_type);
        $matches = array();
        while (false !== ($entry = @readdir($dh))) {
            if ($match_function($pattern, $entry) &&
                $entry != '.' && $entry != '..') {
                $matches[] = $entry;
            }
        }

        @closedir($dh);

        return (count($matches) > 0) ? $matches : null;
    }

    /**
     * Map the directory tree given by the directory_path parameter.
     *
     * @param string $directory contains the directory path that you
     * want to map.
     *
     * @return array a two element array, the first element containing a list
     * of all the directories, the second element containing a list of all the
     * files.
     *
     * @author Sterling Hughes <sterling@php.net>
     * @access public
     */
    function &maptree($directory)
    {
        /* TODO: make mapTree() statically callable */

        /* clear the results just in case */
        $this->files       = array();
        $this->directories = array();

        /* strip out tailing / to be consistent */
        $directory = ereg_replace(DIRECTORY_SEPARATOR.'$', '', $directory);

        $this->_dirs = array($directory);

        while (count($this->_dirs)) {
            $dir = array_pop($this->_dirs);
            File_Find::_build($dir);
            array_push($this->directories, $dir);
        }

        return array($this->directories, $this->files);
    }

    /**
     * Map the directory tree given by the directory parameter.
     *
     * @param string $directory contains the directory path that you
     * want to map.
     * @param integer $maxrecursion maximun number of folders to recursive 
     * map
     *
     * @return array a multidimensional array containing all subdirectories
     * and their files. For example:
     *
     * Array
     * (
     *    [0] => file_1.php
     *    [1] => file_2.php
     *    [subdirname] => Array
     *       (
     *          [0] => file_1.php
     *       )
     * )
     *
     * @author Mika Tuupola <tuupola@appelsiini.net>
     * @access public
     */
    function &mapTreeMultiple($directory, $maxrecursion = 0, $count = 0)
    {   
        $retval = array();

        $count++;

        $directory .= DIRECTORY_SEPARATOR;
        $dh = opendir($directory);
        while (false !== ($entry = @readdir($dh))) {
            if ($entry != '.' && $entry != '..') {
                 array_push($retval, $entry);
            }
        }

        closedir($dh);
     
        while (list($key, $val) = each($retval)) {
            $path = $directory . $val;
            $path = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,
                                DIRECTORY_SEPARATOR, $path);
      
            if (!is_array($val) && is_dir($path)) {
                unset($retval[$key]);
                if ($maxrecursion == 0 || $count < $maxrecursion) {
                    $retval[$val] = File_Find::mapTreeMultiple($path, 
                                    $maxrecursion, $count);
                }
            }
        }

        return $retval;
    }

    /**
     * Search the specified directory tree with the specified pattern.  Return
     * an array containing all matching files (no directories included).
     *
     * @param string $pattern the pattern to match every file with.
     *
     * @param string $directory the directory tree to search in.
     *
     * @param string $type the type of regular expression support to use, either
     * 'php' or 'perl'.
     *
     * @param bool $fullpath whether the regex should be matched against the
     * full path or only against the filename
     *
     * @return array a list of files matching the pattern parameter in the the
     * directory path specified by the directory parameter
     *
     * @author Sterling Hughes <sterling@php.net>
     * @access public
     */
    function &search($pattern, $directory, $type = 'php', $fullpath = true)
    {

        /* if called statically */
        if (!isset($this)  || !is_a($this, "File_Find")) {
            $obj = &new File_Find();
            return $obj->search($pattern, $directory, $type, $fullpath);
        } else {

            $matches = array();
            list (,$files)  = File_Find::maptree($directory);
            $match_function = File_Find::_determineRegex($pattern, $type);

            reset($files);
            while (list(,$entry) = each($files)) {
                if ($match_function($pattern, 
                                    $fullpath ? $entry : basename($entry))) {
                    $matches[] = $entry;
                }
            }
        }

        return ($matches);
    }

    /**
     * Determine whether or not a variable is a PEAR error
     *
     * @param object PEAR_Error $var the variable to test.
     *
     * @return boolean returns true if the variable is a PEAR error, otherwise
     * it returns false.
     * @access public
     */
    function isError(&$var)
    {
        return PEAR::isError($var);
    }

    /**
     * internal function to build singular directory trees, used by
     * File_Find::maptree()
     *
     * @param string $directory name of the directory to read
     * @return void
     */
    function _build($directory)
    {

        $dh = @opendir($directory);

        if (!$dh) {
            $pe = PEAR::raiseError("Cannot open directory");
            return $pe;
        }

        while (false !== ($entry = @readdir($dh))) {
            if ($entry != '.' && $entry != '..') {

                $entry = $directory.DIRECTORY_SEPARATOR.$entry;
                $entry = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,
                                     DIRECTORY_SEPARATOR, $entry);

                if (is_dir($entry)) {
                    array_push($this->_dirs, $entry);
                } else {
                    array_push($this->files, $entry);
                }
            }
        }

        @closedir($dh);
    }

    /**
     * internal function to determine the type of regular expression to
     * use, implemented by File_Find::glob() and File_Find::search()
     *
     * @param string $type given RegExp type
     * @return string kind of function ( "eregi", "ereg" or "preg_match") ;
     *
     */
    function _determineRegex($pattern, $type)
    {
        if (!strcasecmp($type, 'perl')) {
            $match_function = 'preg_match';
        } else if (!strcasecmp(substr($pattern, -2), '/i')) {
            $match_function = 'eregi';
        } else {
            $match_function = 'ereg';
        }

        return $match_function;
    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */

?>
