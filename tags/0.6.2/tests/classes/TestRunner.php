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
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | TestRunner.php                                                            |
// +---------------------------------------------------------------------------+
// | Authors:   Andrew Hill <andrew@m3.net>                                    |
// |            Demian Turner <demian@phpkitchen.com>                          |
// |            James Floyd <james@m3.net>                                     |
// +---------------------------------------------------------------------------+

require_once STR_PATH . '/tests/classes/FileScanner.php';
require_once STR_PATH . '/tests/classes/TestEnv.php';
require_once 'SimpleTest/unit_tester.php';
require_once 'SimpleTest/mock_objects.php';
require_once 'SimpleTest/reporter.php';
require_once 'SimpleTest/web_tester.php';

error_reporting(E_ALL);

/**
 * A class for running tests.
 *
 * @author     Andrew Hill <andrew@m3.net>
 */
class STR_TestRunner
{
    /**
     * A method to run all the tests in the Max project.
     *
     */
    function runAll()
    {
        $type = $GLOBALS['_STR']['test_type'];
        foreach ($GLOBALS['_STR'][$type . '_layers'] as $layer => $data) {

            // Run each layer test in turn
            STR_TestRunner::runLayer($layer);
        }
    }

    /**
     * A method to run all tests in a layer.
     *
     * @param string $layer The layer group to run.
     */
    function runLayer($layer)
    {
        $type = $GLOBALS['_STR']['test_type'];

        // Set up the environment for the test
        STR_TestEnv::setup($layer);

        // Find all the tests in the layer
        $tests = STR_FileScanner::getLayerTestFiles($type, $layer);

        // Add the test files to a SimpleTest group
        $testName = strtoupper($type) . ': ' .
            $GLOBALS['_STR'][$type . '_layers'][$layer][0] .' Tests';
        $test = &new GroupTest($testName);
        foreach ($tests as $layerCode => $folders) {
            foreach ($folders as $folder => $files) {
                foreach ($files as $index => $file) {
                    $test->addTestFile(STR_PATH . '/' . $folder . '/' .
                                       constant($type . '_TEST_STORE') . '/' . $file);
                }
            }
        }
        $test->run(new HtmlReporter());

        // Tear down the environment for the test
        STR_TestEnv::teardown($layer);
    }

    /**
     * A method to all tests in a layer/folder.
     *
     * @param string $layer  The layer group to run.
     * @param string $folder The folder group to run.
     */
    function runFolder($layer, $folder)
    {
        $type = $GLOBALS['_STR']['test_type'];

        // Set up the environment for the test
        STR_TestEnv::setup($layer);

        // Find all the tests in the layer/folder
        $tests = STR_FileScanner::getTestFiles($type, $layer, STR_PATH . '/' . $folder);

        // Add the test files to a SimpleTest group
        $testName = strtoupper($type) . ': ' .
            $GLOBALS['_STR'][$type . '_layers'][$layer][0] . ': Tests in ' . $folder;

        $test = &new GroupTest($testName);
        foreach ($tests as $folder => $data) {
            foreach ($data as $index => $file) {
                $test->addTestFile(STR_PATH . '/' . $folder . '/' .
                                   constant($type . '_TEST_STORE') . '/' . $file);
            }
        }
        $test->run(new HtmlReporter());

        // Tear down the environment for the test
        STR_TestEnv::teardown($layer);
    }

    /** A method to run a single test file.
     *
     * @param string $layer  The layer group to run.
     * @param string $folder The folder group to run.
     * @param string $file   The file to run.
     */
    function runFile($layer, $folder, $file)
    {
        $type = $GLOBALS['_STR']['test_type'];

        // Set up the environment for the test
        STR_TestEnv::setup($layer);

        // Add the test file to a SimpleTest group
        $testName = strtoupper($type) . ': ' .
            $GLOBALS['_STR'][$type . '_layers'][$layer][0] . ': ' . $folder . '/' . $file;
        $test = &new GroupTest($testName);
        $test->addTestFile(STR_PATH . '/' . $folder . '/' .
                           constant($type . '_TEST_STORE') . '/' . $file);
        $test->run(new HtmlReporter());

        // Tear down the environment for the test
        STR_TestEnv::teardown($layer);
    }
}
?>