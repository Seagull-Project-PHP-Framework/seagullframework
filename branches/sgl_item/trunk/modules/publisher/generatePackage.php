<?php
/*  checklist
    - copy mysql_SGL.php and Tree.php to root
    - uncomment pear include constant in constants.php
    - pear generatePackage.php against clean checkout
*/


# pear install --onlyreqdeps seagull-0.3.10.tgz

/*
//  the following packages are presumed to be present in the base install:

PACKAGE        VERSION STATE
Archive_Tar    1.1     stable
Console_Getopt 1.2     stable
DB             1.6.1   stable
Mail           1.1.2   stable
Net_SMTP       1.2.5   stable
Net_Socket     1.0.1   stable
PEAR           1.3.1   stable
PHPUnit        0.6.2   stable
XML_Parser     1.0.1   stable
XML_RPC        1.1.0   stable
*/

set_time_limit(0);
require_once 'PEAR/PackageFileManager.php';

$packagexml = new PEAR_PackageFileManager;
$e = $packagexml->setOptions(array(
    'baseinstalldir' => 'publisher',
    'version' => '0.4.0',
    'license' => 'BSD License',
    'packagedirectory' => '/var/www/html/tmp/seagull/modules/publisher',
    'state' => 'beta',
    'package' => 'seagull_publisher',
    'simpleoutput' => true,
    'summary' => 'cms functionality',
    'description' => 'Seagull is a PHP application framework with a number of modules available that deliver CMS functionality',
    'filelistgenerator' => 'file', // generate from cvs, use file for directory
    'notes' => 'See the CHANGELOG for full list of changes',
    'dir_roles' => array(
        'modules' => 'data',
        ),
    'ignore' => array(
        'generatePackage.php', 
        '*CVS*',
        ),         
    'roles' => array(
        'php' => 'php',
        'html' => 'php',
        '*' => 'php',
         ),
    )
);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    die();
}



$e = $packagexml->addDependency('HTTP_Download', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('Text_Statistics', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addMaintainer('demianturner', 'lead', 'Demian Turner', 'demian@phpkitchen.com');
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

// note use of {@link debugPackageFile()} - this is VERY important
if (isset($_GET['make']) || (isset($_SERVER['argv'][2]) &&
      $_SERVER['argv'][2] == 'make')) {
    $e = $packagexml->writePackageFile();
} else {
    $e = $packagexml->debugPackageFile();
}
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    die();
}
?>