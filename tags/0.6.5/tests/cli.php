<?php
/* EXAMPLE USAGE

php tests/cli.php --type=unit --level=all
php tests/cli.php --type=web --level=all
php tests/cli.php --type=unit --level=layer --layer=ndb
php tests/cli.php --type=unit --level=folder --layer=ndb --folder=lib/SGL
php tests/cli.php --type=unit --level=file --layer=wdd --folder=lib/SGL --file=UrlStrategyAliasTest.wdd.php
*/

/**
 * Put default arguments.
 *
 * @return void
 */
function testCliSetup()
{
    $aVars = array();
    for ($i = 0, $cnt = count($_SERVER['argv']); $i < $cnt; $i++) {
        $aVars[] = $_SERVER['argv'][$i];
        if (!$i) {
            $aVars[] = '--moduleName=default';
            $aVars[] = '--managerName=default';
            $aVars[] = '--action=list';
        }
    }
    $GLOBALS['argv'] = $_SERVER['argv'] = $aVars;
}

/**
 * Check if all needed args are set.
 *
 * @param string $type
 * @param string $level
 * @param string $layer
 * @param string $folder
 * @param string $file
 *
 * @return void
 */
function checkCliArguments($type, $level, $layer, $folder, $file)
{
    // check type
    if (!isset($type)) {
        echo "Error: argument `type` is not set\n";
        echo "Usage: php <sgl-root>/tests/cli.php --type=unit\n";
        exit();
    } elseif (!in_array($type, array('unit', 'web'))) {
        echo "Error: wrong value for `type`\n";
        echo "       following values are allowed: unit, web\n";
        echo "Usage: php <sgl-root>/tests/cli.php --type=unit\n";
        exit();
    }

    // check level
    if (!isset($level)) {
        echo "Error: argument `level` is not set\n";
        echo "Usage: php <sgl-root>/tests/cli.php --type=unit --level=file\n";
        exit();
    } elseif (!in_array($level, array('all', 'layer', 'folder', 'file'))) {
        echo "Error: wrong value for `level`\n";
        echo "       following values are allowed: all, layer, folder, file\n";
        echo "Usage: php <sgl-root>/tests/cli.php --type=unit --level=file\n";
        exit();
    }

    // check layer
    if ($level != 'all') {
        if (!isset($layer)) {
            echo "Error: argument `layer` is not set\n";
            echo "Usage: php <sgl-root>/tests/cli.php --type=unit --level=file --layer=wdd\n";
            exit();
        } elseif (!in_array($layer, array('wdb', 'wdd', 'ndb', 'web'))) {
            echo "Error: wrong value for `layer`\n";
            echo "       following values are allowed: wdb, wdd, ndb, web\n";
            echo "Usage: php <sgl-root>/tests/cli.php --type=unit --level=file --layer=wdd\n";
            exit();
        }
    }

    // check folder
    if ($level == 'folder' || $level == 'file') {
        if (!isset($folder)) {
            echo "Error: argument `folder` is not set\n";
            echo "Usage: php <sgl-root>/tests/cli.php --type=unit --level=file --layer=wdd --folder=lib/SGL\n";
            exit();
        }
    }

    // check file
    if ($level == 'file') {
        if (!isset($file)) {
            echo "Error: argument `file` is not set\n";
            echo "Usage: php <sgl-root>/tests/cli.php --type=unit --level=file --layer=wdd --folder=lib/SGL --file=UrlStrategyAliasTest.wdd.php\n";
            exit();
        } else {
            $filePath = STR_PATH . '/' . $folder . '/tests/' . $file;
            if (!file_exists($filePath)) {
                echo "Error: $layer test file $file was not found in $folder\n";
                exit();
            }
        }
    }
}

/**
 * Print intro.
 */
function printIntro()
{
    $v = SGL_Config::get('tuples.version');
    echo "\n";
    echo "Seagull Framework v$v\n";
    echo "Simple Test Command line Runner\n";
    echo "\n";
}

/**
 * Print footer.
 */
function printFooter($startTime)
{
    echo "\n";
    echo "Total execution time: " . getExecutionTime($startTime);
    echo "\n";
}

/**
 * Get exec time.
 *
 * @param int $start
 *
 * @return string
 */
function getExecutionTime($start)
{
    list ($endUsec, $endSec) = explode(' ', microtime());
    $endTime = ((float) $endUsec + (float) $endSec);
    list ($startUsec, $startSec) = explode(' ', $start);
    $startTime = ((float) $startUsec + (float) $startSec);
    return substr($endTime - $startTime, 0, 6);
}

/**
 * Simple test CLI runner.
 *
 * Examples:
 *   1. php tests/cli.php --type=unit --level=all
 *   2. php tests/cli.php --type=unit --level=layer --layer=ndb
 *   3. php tests/cli.php --type=unit --level=folder --layer=ndb --folder=lib/SGL
 *   4. php tests/cli.php --type=unit --level=file --layer=wdd --folder=lib/SGL --file=UrlStrategyAliasTest.wdd.php
 *
 * @author Dmitri Lakachauskis
 */

// start counter
$startTime = microtime();

// fake cli args
testCliSetup();

// init
require_once 'init.php';
$req = SGL_Request::singleton();

// vars
$type   = $req->get('type');
$level  = $req->get('level');
$layer  = $req->get('layer');
$folder = $req->get('folder');
$file   = $req->get('file');

// print copyright
printIntro();

// check if all needed argments were specified
checkCliArguments($type, $level, $layer, $folder, $file);

// make type global
$GLOBALS['_STR']['test_type'] = $type;

require_once STR_PATH . '/tests/classes/TestRunner.php';
switch ($level) {
    case 'all':
        STR_TestRunner::runAll();
        break;

    case 'layer':
        STR_TestRunner::runLayer($layer);
        break;

    case 'folder':
        STR_TestRunner::runFolder($layer, $folder);
        break;

    case 'file':
        STR_TestRunner::runFile($layer, $folder, $file);
        break;

    default:
        // nothing here
}

// print footer
printFooter($startTime);

?>