<?php

/**
 * A script to call the STR_TestRunner class, based on the $_GET parameters
 * passed via the web client, as well as perform timing of the tests, 
 * etc.
 *
 * @author     Andrew Hill <andrew@m3.net>
 */

require_once 'init.php';
require_once STR_PATH . '/tests/classes/TestRunner.php';

echo "<style type=\"text/css\">\n";
echo file_get_contents(STR_PATH . '/tests/media/tests.css');
echo "</style>\n";


if (defined('TEST_ENVIRONMENT_NO_CONFIG')) {
    echo "<h1>First Run Detected</h1>
<p>The default Simple Test Runner configuration file <b>test.conf.ini</b> has been copied to the directory:
<br><br><b>" . stripslashes(STR_TMP_DIR) . "</b>
<br><br>
Please edit the new configuration file located at:<br>
<br>
<b>" . stripslashes(STR_TMP_DIR) . "/test.conf.ini</b><br>
<br>
 to reflect your database server details.<br><br>
If you would like to customise the default configuration settings when first running Simple Test Runner<br>
please edit the distribution configuration file located at <b>" . STR_PATH . "/test.conf.ini-dist</b>

</p>\n";
    exit();
}

$start = microtime();

// Store the type of test being run globally, to save passing 
// about as a parameter all the time
$GLOBALS['_STR']['test_type'] = @$_GET['type'];

$level = @$_GET['level'];
if ($level == 'all') {
    STR_TestRunner::runAll();
} elseif ($level == 'layer') {
    $layer = $_GET['layer'];
    STR_TestRunner::runLayer($layer);
} elseif ($level == 'folder') {
    $layer = $_GET['layer'];
    $folder = $_GET['folder'];
    STR_TestRunner::runFolder($layer, $folder);
} elseif ($level == 'file') {
    $layer = $_GET['layer'];
    $folder = $_GET['folder'];
    $file = $_GET['file'];
    STR_TestRunner::runFile($layer, $folder, $file);
}

// Display execution time
list ($endUsec, $endSec) = explode(" ", microtime());
$endTime = ((float) $endUsec + (float) $endSec);
list ($startUsec, $startSec) = explode(" ", $start);
$startTime = ((float) $startUsec + (float) $startSec);
echo '<div align="right"><br/>Test Suite Execution Time ~ <b>';
echo substr(($endTime - $startTime), 0, 6);
echo '</b> seconds.</div>';

?>