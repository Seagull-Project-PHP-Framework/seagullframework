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
	$version     = '0.5.4';

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
24-11-05    Fixed long-standing image preview header probs
24-11-05    Fixed config prob with FCK, images now upload correctly

IMPROVEMENTS
14-12-05    Module skeleton generator improved, it now: creates template files,
            lang files and aActionsMapping array (Werner Krauss)
14-12-05    Added SGL::loadRegionList for loading localised country/state arrays
            (Philippe Lhoste)
14-12-05    'localeCategory' configurable, set to LC_ALL by default although European
            users will want to change this where supplying a , for the decimal separator
            causes calculation probs (use LC_TIME)
14-12-05    Integrated advanced locale features available via SGL_Locale, disabled
            by default
14-12-05    SGL_USR_OS renamed more correctly to SGL_CLIENT_OS
12-12-05    Block management enhanced - you can now create any number of arbitrarily
            positioned blocks and assign content to them (Andrey Podshivalov)
12-12-05    Uri aliases integrated into navigation module, external Uris handled
            (Andrey Podshivalov)
06-12-05	Basic CLI request implemented (Eric Persson)
01-12-05    Three url parsers loaded by default: classic querystring, standard
            Seagull SEF, and now UrlAlias, see the 0.5.4 updates notes for details:
            http://trac.seagullproject.org/wiki/Howto/Misc/Upgrading/05
01-12-05    Improved performance for DB-based sessions (Eric Persson)
30-11-05    Polish translation updated (Tomasz Osmialowski)
29-11-05	RSS query more configurable (bluetoad)
29-11-05	Web root path now configurable from installer
24-11-05    DB2 support added (Tobias Kuckuck)
23-11-05    Implemented ability to load default data in installer
23-11-05	Implemented Smarty renderer (Malaney J. Hill)
22-11-05    Added ServiceLocator class and fixed db independence for testing
21-11-05    Factored out setup of table name aliases, these are not set on a per-module
            basis in the file 'tableAliases.ini' parsed at setup time.
16-11-05    Chinese translation updated (Finjon Kiang)
14-11-05    Added version-checker routine to Maintenance manager
14-11-05    Implemented xml-rpc gateway for easy creation of Seagull web services
14-11-05    Improved integration of page offset id in seagull SEF urls
            (Andreas Singer)
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
            'baseinstalldir'    => '/Seagull',
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
                'CHANGELOG-1.txt.gz',
                'generate_package_xml.php',
                'lib/pear/',
                'modules/wizardexample/',
                'www/themes/default/wizardexample/',
                '*tests*',
                '*.svn',
		    ),

            // Global mapping of directories to file roles.
            // @see http://pear.php.net/manual/en/guide.migrating.customroles.defining.php
            'dir_roles'         =>
            array(
                'docs' => 'doc',
                'etc' => 'data',
                'lib' => 'php',
                'modules' => 'php',
                'var' => 'data',
                #'www' => 'web',
            ),

            'roles'             =>
            array(
                'php' => 'php',
                'html' => 'web',
                'png' => 'web',
                'gif' => 'web',
                'jpg' => 'web',
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
                'db2_SGL.php' => 'DB',
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
    $pkg->addPackageDepWithChannel('required', 'Role_Web', 'pearified.com', '1.1.0');

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
//    $e = $pkg->addUsesRole('web', 'www');
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
//    $e = $pkg->addRole('html', 'web');
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
    	$e = $pkg->writePackageFile();
        $e = $packagexml->writePackageFile();
	} else {
    	$e = $pkg->debugPackageFile();
    	#$e = $packagexml->debugPackageFile();
	}

	if (PEAR::isError($e)) {
    	echo $e->getMessage();
	}

?>