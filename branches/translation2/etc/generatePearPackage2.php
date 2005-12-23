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
require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$packagexml = new PEAR_PackageFileManager2();

$e = $packagexml->setOptions(array(
    'baseinstalldir' => 'seagull',
    'packagedirectory' => '/var/www/html/tmp/seagull',
    'filelistgenerator' => 'file',
    'ignore' => array(
        'generatePackage.php', 
        'lib/pear/',
        'tests/',
        '*.svn*',
    ), 
    'installexceptions' => array(
        'mysql_SGL.php' => 'DB',
        'Tree.php' => 'HTML',
        ),
    'dir_roles' => array(
        'etc' => 'data',
        'lib' => 'data',
        'modules' => 'data',
        'var' => 'data',
        'www' => 'data',
        ),
        'exceptions' => array(
        'CHANGELOG.txt' => 'doc',
        'COPYING.txt' => 'doc',
        'INSTALL.txt' => 'data',
        'README.txt' => 'data',
        'VERSION.txt' => 'data',         
        'etc/mysql_SGL.php' => 'php',
        'etc/Tree.php' => 'php',       
        ),
    ));
$packagexml->setPackage('seagull');
$packagexml->setSummary('PHP Application Framework');
$packagexml->setDescription('Seagull is a PHP application framework with a number of modules available that deliver CMS functionality');
$packagexml->setChannel('pear.phpkitchen.com');
$packagexml->setAPIVersion('0.4.6');
$packagexml->setReleaseVersion('1.2.1');
$packagexml->setReleaseStability('stable');
$packagexml->setAPIStability('stable');
$packagexml->setNotes('');
$packagexml->setPackageType('php'); // this is a PEAR-style php script package
#$packagexml->addRelease(); // set up a release section
//$packagexml->setOSInstallCondition('windows');
//$packagexml->addInstallAs('pear-phpdoc.bat', 'phpdoc.bat');
//$packagexml->addIgnore('pear-phpdoc');
//$packagexml->addRelease(); // add another release section for all other OSes
//$packagexml->addInstallAs('pear-phpdoc', 'phpdoc');
//$packagexml->addIgnore('pear-phpdoc.bat');
//$packagexml->addRole('pkg', 'doc'); // add a new role mapping
$packagexml->setPhpDep('4.3.0');
$packagexml->setPearinstallerDep('1.4.0a12');
$packagexml->addMaintainer('lead', 'demianturner', 'Demian Turner', 'demian@phpkitchen.com');
$packagexml->setLicense('BSD License', 'http://www.php.net/license');
$packagexml->generateContents(); // create the <contents> tag
// replace @PHP-BIN@ in this file with the path to php executable!  pretty neat
#$packagexml->addReplacement('pear-phpdoc', 'pear-config', '@PHP-BIN@', 'php_bin');
#$packagexml->addReplacement('pear-phpdoc.bat', 'pear-config', '@PHP-BIN@', 'php_bin');
$pkg = &$packagexml->exportCompatiblePackageFile1(); // get a PEAR_PackageFile object
// note use of {@link debugPackageFile()} - this is VERY important
if (isset($_GET['make']) || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')) {
    $pkg->writePackageFile();
    $packagexml->writePackageFile();
} else {
    $pkg->debugPackageFile();
    $packagexml->debugPackageFile();
}

exit;
 
$e = $packagexml->setOptions(array(
    'baseinstalldir' => 'seagull',
    'version' => '0.4.3',
    'license' => 'BSD License',
    'packagedirectory' => '/var/www/html/tmp/seagull',
    'state' => 'beta',
    'package' => 'seagull',
    'simpleoutput' => true,
    'summary' => 'PHP Application Framework',
    'description' => 'Seagull is a PHP application framework with a number of modules available that deliver CMS functionality',
    'filelistgenerator' => 'file', // generate from cvs, use file for directory
    'notes' => 'See the CHANGELOG for full list of changes',
    'dir_roles' => array(
        'etc' => 'data',
        'lib' => 'data',
        'modules' => 'data',
        'var' => 'data',
        'www' => 'data',
        ),
    'ignore' => array(
        'generatePackage.php', 
        'lib/pear/',
        '*CVS*',
        ), 
    'roles' => array(
        'php' => 'php',
        'html' => 'php',
        '*' => 'php',
         ),
    'exceptions' => array(
        'CHANGELOG.txt' => 'doc',
        'COPYING.txt' => 'doc',
        'INSTALL.txt' => 'data',
        'README.txt' => 'data',
        'VERSION.txt' => 'data',         
        'etc/mysql_SGL.php' => 'php',
        'etc/Tree.php' => 'php',       
        ),
    'installexceptions' => array(
        'mysql_SGL.php' => 'DB',
        'Tree.php' => 'HTML',
        ),
    )
);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    die();
}


$e = $packagexml->addReplacement('constants.php', 'pear-config', '@PEAR-DIR@', 'php_dir');
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('Cache_Lite', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('Config', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('DB_DataObject', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('DB_NestedSet', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('Date', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('File', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('HTML_Common', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('HTML_TreeMenu', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('HTML_QuickForm', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('HTML_Template_Flexy', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('HTTP_Header', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('HTTP_Download', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('Log', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('Mail_Mime', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('Net_Socket', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('Net_Useragent_Detect', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('Pager', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('Text_Password', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('Text_Statistics', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('Validate', false, 'has', 'pkg', false);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addDependency('XML_Util', false, 'has', 'pkg', false);
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