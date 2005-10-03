<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2003-2005 m3 Media Services Limited                         |
// | For contact details, see: http://www.m3.net/                              |
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
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | init.php                                                                  |
// +---------------------------------------------------------------------------+
// | Authors:   Andrew Hill <andrew@m3.net>                                    |
// |            Demian Turner <demian@phpkitchen.com>                          |
// |            James Floyd <james@m3.net>                                     |
// +---------------------------------------------------------------------------+

/**
 * @author     Andrew Hill <andrew@m3.net>
 */

//    PEAR requirements
//     - PEAR
//     - SimpleTest
//     - HTML_TreeMenu
//     - DB
//     - File (optional)

function STR_init()
{   
    // Database connection constants
    define('STR_DSN_ARRAY',                 0);
    define('STR_DSN_STRING',                1);
    define('STR_TMP_DIR', (isset($_ENV["TEMP"])) ? $_ENV["TEMP"] : ini_get('session.save_path'));
    
    // Define the different environment configurations
    define('NO_DB',          0);
    define('DB_NO_TABLES',   1);
    define('DB_WITH_TABLES', 2);
    define('DB_WITH_DATA',   3);
    
    // Define the directory that tests should be stored in
    // (e.g. "tests", "tests/unit", etc.).
    define('unit_TEST_STORE', 'tests');
    
    // The different "layers" that can be tested, defined in terms of
    // layer test codes (ie. the test files for the layer will be
    // xxxxx.code.test.php), and the layer names and database
    // requirements for the test(s) in that layer

    $GLOBALS['_STR']['unit_layers'] = array(
            'wdb'   => array('DB related',   DB_WITH_TABLES),
            'wdt'   => array('DB related',   DB_WITH_DATA),
            'ndb'   => array('PHP only',     NO_DB),
        );
        
    // set error reporting as verbose as possible
    error_reporting(E_ALL);
    
    // Ensure that the initialisation has not been run before
    if (!(isset($GLOBALS['_STR']['CONF']))) {
        // Define the Max installation base path
        define('STR_PATH', dirname(dirname(__FILE__)));

        // Define the PEAR installation path
        #ini_set('include_path', STR_PATH . '/pear');

        // Parse the testing configuration file
        $GLOBALS['_STR']['CONF'] = $conf = parseIniFile();
        
        // The directories where tests can be found, to help
        // reduce filesystem parsing time   
        $GLOBALS['_STR']['directories'] =
            explode(',', $GLOBALS['_STR']['CONF']['general']['directoriesToScan']);
        
        //  load target app bridge
        if (isset($conf['general']['initBridge'])) {
            require_once STR_PATH . '/' .$conf['general']['initBridge'];
        }
    }
}

function parseIniFile()
{
    // Set up the configuration .ini file path location
    $configPath = STR_TMP_DIR;

    // Does the test environment config exist?
    if (file_exists($configPath . '/test.conf.ini')) {
        $ret = parse_ini_file($configPath . '/test.conf.ini', true);
    } else {
        // copy the default configuration file to the users tmp directory
        if (!copy(STR_PATH . '/tests/test.conf.ini-dist', STR_TMP_DIR . '/test.conf.ini')) {
            die('ERROR WHEN COPYING CONFIG FILE TO ' . STR_TMP_DIR . '/test.conf.ini');
        }
        define('TEST_ENVIRONMENT_NO_CONFIG', true);
        $ret = parse_ini_file($configPath . '/test.conf.ini', true);
    }
    return $ret;
}

//  main
STR_init();
require_once 'PEAR.php';

$conf = $GLOBALS['_STR']['CONF'];
?>