<?php
set_time_limit(0);
require_once 'PEAR/PackageFileManager2.php';

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
 * @category   Testing
 * @package    SimpleTest
 */

    /**
     * Package file manager for package.xml 2.
     */
    require_once 'PEAR/PackageFileManager2.php';

	// Directory where the package files are located.
	$packagedir  = '/var/www/html/simpletest';

    // Name of the channel, this package will be distributed through
    $channel     = 'pear.phpkitchen.com';

    // Category and name of the package
	$category    = 'Testing';
    $package     = 'SimpleTest';

	$version     = '1.0.1alpha3';

    // Summary description
	$summary     = <<<EOT
SimpleTest is a testing framework.
EOT;

    // Longer description
	$description = <<<EOD
Unit testing, mock objects and web testing framework for PHP.
EOD;

    // License information
    $license = 'The Open Group Test Suite License';

    // Notes, function to grab them directly from S9Y in
    // generate_package_xml_functions.php
	$notes = <<<EOD
The heart of SimpleTest is a testing framework built around test case classes.
These are written as extensions of base test case classes, each extended with
methods that actually contain test code. Top level test scripts then invoke
the run()  methods on every one of these test cases in order. Each test
method is written to invoke various assertions that the developer expects to
be true such as assertEqual(). If the expectation is correct, then a
successful result is dispatched to the observing test reporter, but any
failure triggers an alert and a description of the mismatch.

These tools are designed for the developer. Tests are written in the PHP
language itself more or less as the application itself is built. The advantage
of using PHP itself as the testing language is that there are no new languages
to learn, testing can start straight away, and the developer can test any part
of the code. Basically, all parts that can be accessed by the application code
can also be accessed by the test code if they are in the same language.
EOD;

    // Instanciate package file manager
	$pkg = new PEAR_PackageFileManager2();

    // Setting options
	$e = $pkg->setOptions(
		array(
            // Where are our package files.
            'packagedirectory'  => $packagedir,
            // Where will package files be installed in
            // the local PEAR repository?
            'baseinstalldir'    => 'SimpleTest',
            // Where should the package file be generated
            'pathtopackagefile' => $packagedir,
            // Just simple output, no MD5 sums and <provides> tags
            #'simpleoutput'      => true,

            'packagefile'       => 'package2.xml',
            // Use standard file list generator, choose CVS, if you
            // have your code in CVS
            'filelistgenerator' => 'file',

            // List of files to ignore and put not explicitly into the package
		    'ignore'            =>
            array(
                'packages/',
                'tutorials/',
                'ui/',
                'docs/',
                '*CVS*',
                'TODO',
		    ),

            // Global mapping of directories to file roles.
            // @see http://pear.php.net/manual/en/guide.migrating.customroles.defining.php
            'dir_roles'         =>
            array(
                'extensions' => 'php',
                'test' => 'test',
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
                'VERSION' => 'doc',
                'HELP_MY_TESTS_DONT_WORK_ANYMORE' => 'doc',
                'LICENSE' => 'doc',
                'README' => 'doc',
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

    $pkg->setReleaseStability('alpha');
    $pkg->setAPIStability('stable');
    $pkg->setReleaseVersion($version);
    $pkg->setAPIVersion($version);

    $pkg->setLicense($license);
    $pkg->setNotes($notes);

    // Our package contains PHP files (not C extension files)
    $pkg->setPackageType('php');

    // Must be available in new package.xml format
    $pkg->setPhpDep('4.3.0');
    $pkg->setPearinstallerDep('1.4.6');

    // Create the current release and add it to the package definition
    $pkg->addRelease();

    // Package release needs a maintainer
    $pkg->addMaintainer('lead', 'lastcraft', 'Marcus Baker', 'marcus@lastcraft.com');
	$pkg->addMaintainer('developer', 'tswicegood', 'Travis Swicegood', 'tswicegood@users.sourceforge.net');
	$pkg->addMaintainer('helper', 'jsweat', 'Jason Sweat', 'jsweat_php@yahoo.com');
	$pkg->addMaintainer('helper', 'pp11', 'Perrick Penet', 'perrick@noparking.net');
	$pkg->addMaintainer('helper', 'shpikat', 'Constantine Shpikat', 'shpikat@users.sourceforge.net');
	$pkg->addMaintainer('helper', 'demianturner', 'Demian Turner', 'demian@phpkitchen.com');

    // Internally generate the XML for our package.xml (does not perform output!)
    $test = $pkg->generateContents();


    // If called without "make" parameter, we just want to debug the generated
    // package.xml file and want to receive additional information on error.
    if (isset($_GET['make']) || (isset($_SERVER['argv'][1]) &&
            $_SERVER['argv'][1] == 'make')) {
    	$e = $pkg->writePackageFile();
        #$e = $packagexml->writePackageFile();

	} else {
    	$e = $pkg->debugPackageFile();
        #$e = $packagexml->writePackageFile();
	}

	if (PEAR::isError($e)) {
    	echo $e->getMessage();
	}
?>