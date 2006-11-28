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
Seagull is a PHP application framework with a number of
modules available that deliver CMS functionality.
EOT;

    // License information
    $license = 'BSD';

    // Notes, function to grab them directly from S9Y in
    // generate_package_xml_functions.php
	$notes = <<<EOT
BUGFIXES
11-11-05    Fix for &-related prob in thunderbird parsing of RSS
09-11-05    Fixed small bug where backdating date comboboxes wasn't working
            (Werner Krauss)
31-10-05	Fixed broken config calls in templates
26-10-05    Registration emails are no longer CCed to admin, separate notification
            is sent.

IMPROVEMENTS
11-11-05    Rewrote installer to handle tasks, easy to extend
11-11-05    Added conf[site][sessionInUrl] key and set to false to overcome
            t-bird RSS bug listed above
11-11-05    Reduced dependency on sections by modifying block listing query to use
            LEFT JOIN instead of WHERE clauses (Julien Casanova)
10-11-05    Moved MaintenanceMgr to 'default' module
10-11-05    Moved all SQL data to respective modules (modules, perms, role_perm
            assignments) by creating an auto-increment token in data and using
            dynamic inserts
09-11-05    Added ability for sql parser to auto-increment records when PK is marked
            with {SGL_NEXT_ID} token
09-11-05    Added ability to add all or minimum modules
09-11-05    Added ability to remove frontScriptName and have even clearner urls than
            previously, eg, now available: http://example.com/user/login/
            Option currently only available to apache users with mod_rewrite enabled,
            to activate set [site][frontScriptName] = false and copy
            seagull/etc/htaccess-cleanUrl.dist to seagull/www/.htaccess and make sure
            the file is readable by the webserver (Julien Casanova)
09-11-05    Added ability to backdate articles (Werner Krauss)
09-11-05    Removed unnecessary framebuster from login page
06-11-05    Split web tests into modules
06-11-05    Moved all core classes that doubled up with other classes to reduce
            file loading to seagull/lib/SGL/Other.php.  Currently this contains
            SGL_Array, SGL_Date and SGL_Inflector.  Should make libs easier to find:
            if you don't see a file named the same as the lib you want, it will most
            likely be in Other.php
04-11-05    New wizard can only be accessed if admin knows password stored in
            seagull/var/INSTALL_COMPLETE.php.  Installer is invoked automatically
            on first seagull install, and can be called manually by calling setup.php
03-11-05    Resolved dependencies between ProfileMgr, News articles, PageMgr,
            CategoryMgr so core modules could work
03-11-05    Added SGL_Process_ResolveManager::moduleIsRegistered() to gracefully supply
            default module if requested module is not registered.
03-11-05    all SQL-related files that used to live in seagull/etc have been moved to
            'default' module as that's what they are, default
03-11-05    item* tables and data moved to publisher
03-11-05    category table and data moved to publisher
03-11-05    Improved timezone list
02-11-05    Global config file renamed to <host_name>.conf.php
28-10-05    Blocks can now be displayed according to the role of the current user
            (Daniel Korsak)
23-10-05	Added support for observers with SGL_Oberserver and SGL_Observable
21-10-05    Grouped pre + post processing tasks together by name, renamed Tasks.php
            to Process.php to respect namespace
20-10-05    Added php4/5 compatible delegator class
EOT;

    // Instanciate package file manager
	$pkg = new PEAR_PackageFileManager2();

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
            #'simpleoutput'      => true,

            'packagefile'       => 'package2.xml',
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
                '*tests*',
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
                'CODING_STANDARDS.txt' => 'doc',
                'README.txt' => 'doc',
                'COPYING.txt' => 'data',
                'INSTALL.txt' => 'data',
                'VERSION.txt' => 'data',
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
    $pkg->addPackageDepWithChannel('required', 'Cache_Lite', 'pear.php.net', '1.5.2');
    $pkg->addPackageDepWithChannel('required', 'Config', 'pear.php.net', '1.10.4');
    $pkg->addPackageDepWithChannel('required', 'DB', 'pear.php.net', '1.7.6');
    $pkg->addPackageDepWithChannel('required', 'DB_DataObject', 'pear.php.net', '1.7.15');
    $pkg->addPackageDepWithChannel('required', 'DB_NestedSet', 'pear.php.net', '1.3.6');
    $pkg->addPackageDepWithChannel('required', 'Date', 'pear.php.net', '1.4.6');
    $pkg->addPackageDepWithChannel('required', 'File', 'pear.php.net', '1.2.2');
    $pkg->addPackageDepWithChannel('required', 'HTML_Common', 'pear.php.net', '1.2.2');
    #$pkg->addPackageDepWithChannel('required', 'HTML_TreeMenu', 'pear.php.net', '1.2.0');
    $pkg->addPackageDepWithChannel('required', 'HTML_QuickForm', 'pear.php.net', '3.2.5');
    $pkg->addPackageDepWithChannel('required', 'HTML_QuickForm_Controller', 'pear.php.net', '1.0.5');
    $pkg->addPackageDepWithChannel('required', 'HTML_Template_Flexy', 'pear.php.net', '1.2.3');
    #$pkg->addPackageDepWithChannel('required', 'HTTP_Download', 'pear.php.net', '1.2.0');
    $pkg->addPackageDepWithChannel('required', 'Log', 'pear.php.net', '1.9.2');
    $pkg->addPackageDepWithChannel('required', 'Mail_Mime', 'pear.php.net', '1.3.1');
    $pkg->addPackageDepWithChannel('required', 'Net_Socket', 'pear.php.net', '1.0.6');
    #$pkg->addPackageDepWithChannel('required', 'Net_Useragent_Detect', 'pear.php.net', '1.2.0');
    $pkg->addPackageDepWithChannel('required', 'Pager', 'pear.php.net', '2.3.4');
    $pkg->addPackageDepWithChannel('required', 'Text_Password', 'pear.php.net', '1.1.0');
    #$pkg->addPackageDepWithChannel('required', 'Text_Statistics', 'pear.php.net', '1.2.0');
    $pkg->addPackageDepWithChannel('required', 'Validate', 'pear.php.net', '0.6.2');
    $pkg->addPackageDepWithChannel('required', 'XML_Parser', 'pear.php.net', '1.2.7');
#    $pkg->addPackageDepWithChannel('required', 'XML_Tree', 'pear.php.net', '2.0.0RC2');
    $pkg->addPackageDepWithChannel('required', 'XML_Util', 'pear.php.net', '1.1.1');

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
    $packagexml = &$pkg->exportCompatiblePackageFile1();

    // If called without "make" parameter, we just want to debug the generated
    // package.xml file and want to receive additional information on error.
    if (isset($_GET['make']) || (isset($_SERVER['argv'][2]) &&
            $_SERVER['argv'][2] == 'make')) {
#    	$e = $pkg->writePackageFile();
        $e = $packagexml->writePackageFile();
	} else {
    	$e = $pkg->debugPackageFile();
    	#$e = $packagexml->debugPackageFile();
	}

	if (PEAR::isError($e)) {
    	echo $e->getMessage();
	}

?>