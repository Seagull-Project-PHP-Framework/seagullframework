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
// | SGL2.php                                                                   |
// +---------------------------------------------------------------------------+
// | Authors: Demian Turner <demian@phpkitchen.com>                            |
// +---------------------------------------------------------------------------+

/**
 * Register SGL2::autoload() with SPL.
 */
spl_autoload_register(array('SGL2', 'autoload'));

$sglPath = realpath(dirname(__FILE__).'/..');
$libPath = realpath(dirname(__FILE__).'/../lib');

define('SGL2_PATH', $sglPath);
set_include_path($libPath.PATH_SEPARATOR.get_include_path());
require_once 'SGL2/File.php';
new SGL2_Config();

/**
 * Provides a set of static utility methods used by most modules.
 *
 * @package SGL
 * @author Demian Turner <demian@phpkitchen.com>
 */
class SGL2
{
    /**
     *
     * Loads a class or interface file from the include_path.
     *
     * @param string $name A Seagull (or other) class or interface name.
     * @author Thanks to Solar
     * @return void
     *
     */
    public static function autoload($name)
    {
        // did we ask for a non-blank name?
        if (trim($name) == '') {
            new Exception('No class or interface named for loading');
        }

        // pre-empt further searching for the named class or interface.
        // do not use autoload, because this method is registered with
        // spl_autoload already.
        if (class_exists($name, false) || interface_exists($name, false)) {
            return;
        }

        // convert the class name to a file path.
        $file = str_replace('_', DIRECTORY_SEPARATOR, $name) . '.php';

        // include the file and check for failure. we use Solar_File::load()
        // instead of require() so we can see the exception backtrace.
        SGL2_File::load($file);

        // if the class or interface was not in the file, we have a problem.
        // do not use autoload, because this method is registered with
        // spl_autoload already.
        if (! class_exists($name, false) && ! interface_exists($name, false)) {
            throw new Exception('Class or interface does not exist in loaded file');
        }
    }
}
?>