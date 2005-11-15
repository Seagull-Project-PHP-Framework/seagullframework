<?php
/**
 * Generation script for PEAR package.xml file.
 * Generates a version 2 package.xml file using the package
 * PEAR_PackageFileManager.
 *
 * @link http://pear.php.net/package/PEAR_PackageFileManager
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Applications
 * @package    Serendipity
 * @author     Tobias Schlitt <toby@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: Php.php,v 1.5 2005/07/28 16:51:53 cellog Exp $
 * @link       http://www.s9y.org
 */

    /**
     * Package file manager for package.xml 2.
     */
    require_once 'PEAR/PackageFileManager2.php';

    /**
     * Some help functions.
     */
    #require_once 'generate_package_xml_functions.php';

	// Directory where the package files are located.
	$packagedir  = '/var/www/html/tmp/seagull';

    // Name of the channel, this package will be distributed through
    $channel     = 'pear.phpkitchen.com';

    // Category and name of the package
	$category    = 'Frameworks';
    $package     = 'Seagull';

    // Version (S9Y version is 0.9, I added .0 to reflect PEAR version scheme)
	$version     = '0.5.3';

    // Summary description
	$summary     = <<<EOT
Seagull is a PHP framework (BSD License).
EOT;

    // Longer description
	$description = <<<EOT
Seagull is a PHP framework (BSD License).

Blah.
EOT;

    // License information
    $license = 'BSD';

    // Notes, function to grab them directly from S9Y in
    // generate_package_xml_functions.php
	$notes = 'see CHANGELOG.txt';#retreiveSerendipityNotes($packagedir);

    // Instanciate package file manager
	$pkg = new PEAR_PackageFileManager2;

    // Setting options
	$e = $pkg->setOptions(
		array(
            // Where are our package files.
            'packagedirectory'  => $packagedir,
            // Where will package files be installed in
            // the local PEAR repository?
            'baseinstalldir'    => 'Seagull',
            // Where should the package file be generated
            'pathtopackagefile' => $packagedir,
            // Just simple output, no MD5 sums and <provides> tags
            'simpleoutput'      => true,
            // Use standard file list generator, choose CVS, if you
            // have your code in CVS
            'filelistgenerator' => 'file',

            // List of files to ignore and put not explicitly into the package
		    'ignore'            =>
            array(
                'package.xml',
                'package2.xml',
                'generate_package_xml.php',
                'lib/pear/',
                '*.svn',
		    ),

            // Global mapping of directories to file roles.
            // @see http://pear.php.net/manual/en/guide.migrating.customroles.defining.php
            'dir_roles'         =>
            array(
                'docs' => 'doc',
                'etc' => 'data',
                'lib' => 'data',
                'modules' => 'data',
                'var' => 'data',
                'www' => 'data',
            ),

            'roles'             =>
            array(
                'php' => 'php',
                'html' => 'php',
                '*' => 'php',
            ),

            // Define exceptions of previously defined role mappings,
            // this part uses real file names and no directories.
            'exceptions'        =>
            array(
                'CHANGELOG.txt' => 'doc',
                'COPYING.txt' => 'doc',
                'INSTALL.txt' => 'data',
                'README.txt' => 'data',
                'VERSION.txt' => 'data',
                'etc/mysql_SGL.php' => 'php',
                'etc/Tree.php' => 'php',
            ),

            'installexceptions' =>
            array(
                'mysql_SGL.php' => 'DB',
                'oci8_SGL.php' => 'DB',
                'maxdb_SGL.php' => 'DB',
                'Tree.php' => 'HTML',
            ),
	    )
    );

    // PEAR error checking
    if (PEAR::isError($e)) {
        die($e->getMessage());
    }

    // Set misc package information
    $pkg->setPackage($package);
    $pkg->setSummary($summary);
    $pkg->setDescription($description);
    $pkg->setChannel($channel);

    $pkg->setReleaseStability('beta');
    $pkg->setAPIStability('stable');
    $pkg->setReleaseVersion($version);
    $pkg->setAPIVersion($version);

    $pkg->setLicense($license);
    $pkg->setNotes($notes);

    // Our package contains PHP files (not C extension files)
    $pkg->setPackageType('php');

    // Must be available in new package.xml format
    $pkg->setPhpDep('4.3.0');
    $pkg->setPearinstallerDep('1.4.2');

    // Require custom file role for our web installation
    #$pkg->addPackageDepWithChannel('required', 'CustomInstallerFiles', 'pear.schlitt.info');

    // Require PEAR_DB package for initializing the database in the post install script
    $pkg->addPackageDepWithChannel('required', 'DB', 'pear.php.net', '1.2.0');

    // Insert path to our include files into S9Y global configuration
    #$pkg->addReplacement('serendipity_config.inc.php', 'pear-config', '@php_dir@', 'php_dir');

    $pkg->addReplacement('lib/SGL/Tasks/Setup.php', 'pear-config', '@PEAR-DIR@', 'php_dir');

    // Insert path to PEAR data dir into post install script
    #$pkg->addReplacement('Setup.php', 'pear-config', '@data_dir@', 'data_dir');

    // Define that we will use our custom file role in this script
//    $e = $pkg->addUsesRole('web', 'Webfiles');
//    if (PEAR::isError($e)) {
//        die($e->getMessage());
//    }

    // Mapping misc roles to file name extensions
    // Directly here, a dirty hack: Map all files without extension
    // to "doc" role
#    $e = $pkg->addRole('', 'doc');
#    if (PEAR::isError($e)) {
#        die($e->getMessage());
#    }
//    $e = $pkg->addRole('lib', 'doc');
//    if (PEAR::isError($e)) {
//        die($e->getMessage());
//    }
//
//    $e = $pkg->addRole('png', 'web');
//    if (PEAR::isError($e)) {
//        die($e->getMessage());
//    }
//    $e = $pkg->addRole('gif', 'web');
//    if (PEAR::isError($e)) {
//        die($e->getMessage());
//    }
//    $e = $pkg->addRole('jpeg', 'web');
//    if (PEAR::isError($e)) {
//        die($e->getMessage());
//    }

    // Create the current release and add it to the package definition
    $pkg->addRelease();

    // Package release needs a maintainer
	$pkg->addMaintainer('lead', 'demianturner', 'Demian Turner', 'demian@phpkitchen.com');

    // Internally generate the XML for our package.xml (does not perform output!)
    $test = $pkg->generateContents();
#    $packagexml = &$pkg->exportCompatiblePackageFile1();

    // If called without "make" parameter, we just want to debug the generated
    // package.xml file and want to receive additional information on error.
    if (isset($_GET['make']) || (isset($_SERVER['argv'][2]) &&
            $_SERVER['argv'][2] == 'make')) {
    	$e = $pkg->writePackageFile();
 #       $e = $packagexml->writePackageFile();
	} else {
    	$e = $pkg->debugPackageFile();
    	#$e = $packagexml->debugPackageFile();
	}

	if (PEAR::isError($e)) {
    	echo $e->getMessage();
	}

?>