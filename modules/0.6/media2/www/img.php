<?php

/**
 * http://<host>/media2/img.php?path=<path>
 *
 * @todo add secret session key
 */

function create_session($varDir)
{
    require "$varDir/localhost.conf.php";

    // session params
    $sessionPath = !empty($conf['path']['tmpDirOverride'])
        ? $conf['path']['tmpDirOverride']
        : $varDir . '/tmp';
    $sessionName = $conf['cookie']['name'];

    // start session
    session_save_path($sessionPath);
    session_name($sessionName);
    session_start();
}

function get_uploaddir($varDir)
{
    require "$varDir/localhost.conf.php";
    return !empty($conf['site']['uploadDirOverride'])
        ? $conf['site']['uploadDirOverride']
        : $varDir . '/uploads';
}

function send_file($fileName)
{
    $fp = fopen($fileName, 'rb');

    // send the right headers
    header('Content-Type: image/png');
    header('Content-Length: ' . filesize($fileName));
    // dump the picture and stop the script
    fpassthru($fp);
}

// -------------------
// --- Definitions ---
// -------------------

$rootDir         = dirname(dirname(dirname(dirname(realpath(__FILE__)))));
$varDir          = "$rootDir/var";
$missingFileName = realpath($rootDir . '/www/media2/images/icons/image-missing.png');
$errorFileName   = realpath($rootDir . '/www/media2/images/icons/image-unreadable.png');

create_session($varDir);

// -----------------
// --- Find path ---
// -----------------

$allowed   = true;
$query     = isset($_REQUEST['path']) ? $_REQUEST['path'] : 'missing';
$fileName  = $rootDir . '/' . $query;
$uploadDir = get_uploaddir($varDir);

// check if file is within upload ir
if (strpos($fileName, $uploadDir) !== 0) {
    $allowed = false;
}

// ------------------
// --- Validation ---
// ------------------

if (!$allowed) {
    $fileName = $errorFileName;
} elseif (!file_exists($fileName)) {
    $fileName = $missingFileName;
}

if (file_exists($fileName)) {
    send_file($fileName);
}

?>