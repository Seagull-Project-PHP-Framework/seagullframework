<?php

define('SGL_PKG_RELEASE_NAME', $_SERVER['argv'][2]); //    passed from etc/release.sh
define('SGL_PKG_TMP_BUILD_DIR', '/tmp/seagull-'.SGL_PKG_RELEASE_NAME);
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
 * @category   Frameworks
 * @package    Seagull
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
	$packagedir  = SGL_PKG_TMP_BUILD_DIR;

    // Name of the channel, this package will be distributed through
    $channel     = 'pear.phpkitchen.com';

    // Category and name of the package
	$category    = 'Frameworks';
    $package     = 'Seagull';

	$version     = SGL_PKG_RELEASE_NAME;

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
23-01-06    Fixed small bug where registry of PEAR modules listed in installer
            was referring to the system instead of local PEAR install
20-01-06    Problems with adding subscribers fixed in NewsletterMgr
            (AJ Tarachanowicz)
20-01-06    Problems with ContentTypeMgr fixed (AJ Tarachanowicz)
17-01-06    Fixed problems with naming conventions in module generator
30-12-05    Fixed bug where contact us template could not be found (Andrey Podshivalov)
21-12-05    Ancestors correctly highlighted in navigation trees (Gerald Fishcer)
20-12-05    Added missing path vars in config template (Tomasz Osmialowski)
20-12-05    Fixed corruption of german language file in default module
19-12-05    Fixed incorrect constant which prevented instant messages from working
            correctly
16-12-05    Changed erroneous ECT tz to CET (Sylvain PAPET)

IMPROVEMENTS
23-01-06    Implemented web-based PEAR package and channel management.  Basic ideas
            used from PEAR webinstaller and extended.
23-01-06    Seagull is now PHP 5.1.x compliant
23-01-06    Added new conf key: [RegisterMgr][enabled] set to true by default
23-01-06    Added auto-correct frontScriptName for CGI users in SGL_Task_CreateConfig()
18-01-06    Test runner now uses pearified SimpleTest and notices are suppressed
            for now, see http://trac.seagullproject.org/wiki/Standards/UnitTesting
17-01-06    Integrated Serendipity blog, see
            http://trac.seagullproject.org/wiki/Integration/Serendipity
17-01-06    Added new 'getAllModules' xml-rpc service
16-01-06    Instantiating a cache object has changed from
            \$cache =  SGL::cacheSingleton(); to \$cache =  SGL_Cache::singleton();
            Likewise, SGL::clearCache() has changed to SGL_Cache::clear()
10-01-06    Implemented a Savant2 template renderer (Andrey Podshivalov)
09-01-06    Url alias solution fully integrated and database-driven
06-01-06    Added generateRadioList method to SGL_Output (mawan)
06-01-06    Added a wrapper to create html views easily:
                \$view = new SGL_HtmlSimpleView(\$output);
                \$html = \$view->render();
            (Andrey Podshivalov)
06-01-06    Implemented post-connect query which is used to set the default
            character set for the current connection (MySQL 4.1 or higher). For
            example: SET NAMES utf8
06-01-06    All data object calls are now config driven (freeslacker)
05-01-06    Added a new admin GUI, admin tasks will be clearly defined and
            identified by this addition (Julien Casanova)
04-01-06    Blocks can now be enabled/disabled for sub-sections (mstahv)
04-01-06    Cleanup of Translation2 implementation, navigation section and items
            get their own translation id, no more sharing with titles.  section
            and item_addition both get a new trans_id field (AJ Tarachanowicz)
23-12-05    Added new navigation block (AJ Tarachanowicz)
22-12-05    PEAR's Translation2 lib integrated which means application interface,
            navigation and content can now be translated into multiple languages,
            many thanks to AJ's gargantuan effort here and the patronage of
            Multithink.com (AJ Tarachanowicz)
22-12-05    Improvement to the who's online block (Andrey Podshivalov)

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
                'modules/',
                'www/themes/default_admin/',
                'www/themes/savant/',
                'www/themes/smarty/',
                '*tests*',
                '*.svn',
		    ),

            // Global mapping of directories to file roles.
            // @see http://pear.php.net/manual/en/guide.migrating.customroles.defining.php
            'dir_roles'         =>
            array(
                'docs' => 'doc',
                'lib' => 'php',
//                'modules' => 'php',
                'etc' => 'data',
                'var' => 'data',
                'www' => 'web',
            ),

            'roles'             =>
            array(
                'php' => 'php',
                #'html' => 'web',
                #'png' => 'web',
                #'gif' => 'web',
                #'jpg' => 'web',
                '*' => 'php',
            ),

            // Define exceptions of previously defined role mappings,
            // this part uses real file names and no directories.
            'exceptions'        =>
            array(
                'CHANGELOG.txt' => 'doc',
                'CODING_STANDARDS.txt' => 'doc',
                'README.txt' => 'doc',
                'COPYING.txt' => 'php',
                'INSTALL.txt' => 'doc',
                'VERSION.txt' => 'php',
            ),

            'installexceptions' =>
            array(
                'mysql_SGL.php' => 'DB',
                'oci8_SGL.php' => 'DB',
                'maxdb_SGL.php' => 'DB',
                'db2_SGL.php' => 'DB',
                'Tree.php' => 'HTML',
                'WebSGL.php' => 'PEAR/Frontend',
                'RemoteSGL.php' => 'PEAR/Command',
                'RemoteSGL.xml' => 'PEAR/Command',
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
    $pkg->setPearinstallerDep('1.4.6');

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
    $pkg->addPackageDepWithChannel('required', 'HTML_QuickForm', 'pear.php.net', '3.2.5');
    $pkg->addPackageDepWithChannel('required', 'HTML_QuickForm_Controller', 'pear.php.net', '1.0.5');
    $pkg->addPackageDepWithChannel('required', 'HTML_Template_Flexy', 'pear.php.net', '1.2.3');
    $pkg->addPackageDepWithChannel('required', 'Log', 'pear.php.net', '1.9.2');
    $pkg->addPackageDepWithChannel('required', 'Mail_Mime', 'pear.php.net', '1.3.1');
    $pkg->addPackageDepWithChannel('required', 'Net_Socket', 'pear.php.net', '1.0.6');
    $pkg->addPackageDepWithChannel('required', 'Net_Useragent_Detect', 'pear.php.net', '1.2.0');
    $pkg->addPackageDepWithChannel('required', 'Pager', 'pear.php.net', '2.3.4');
    $pkg->addPackageDepWithChannel('required', 'Text_Password', 'pear.php.net', '1.1.0');
    $pkg->addPackageDepWithChannel('required', 'Translation2', 'pear.php.net', '2.0.0beta8');
    $pkg->addPackageDepWithChannel('required', 'Validate', 'pear.php.net', '0.6.2');
    $pkg->addPackageDepWithChannel('required', 'XML_Parser', 'pear.php.net', '1.2.7');
    #$pkg->addPackageDepWithChannel('required', 'XML_Tree', 'pear.php.net', '2.0.0RC2');
    $pkg->addPackageDepWithChannel('required', 'XML_Util', 'pear.php.net', '1.1.1');

    //  package deps
    //  - default
    require_once SGL_PKG_TMP_BUILD_DIR . '/modules/default/generatePearPackageXml.php';
    $pkg->specifySubpackage($default_pkg, $dependency = false/* indicates subpackage */, $required = true);

    //  - navigation
    require_once SGL_PKG_TMP_BUILD_DIR . '/modules/navigation/generatePearPackageXml.php';
    $pkg->specifySubpackage($navigation_pkg, $dependency = false/* indicates subpackage */, $required = true);

    //  - user
    require_once SGL_PKG_TMP_BUILD_DIR . '/modules/user/generatePearPackageXml.php';
    $pkg->specifySubpackage($user_pkg, $dependency = false/* indicates subpackage */, $required = true);

    //  - publisher
#    require_once SGL_PKG_TMP_BUILD_DIR . '/modules/publisher/generatePearPackageXml.php';
#    $pkg->specifySubpackage($publisher_pkg, $dependency = false/* indicates subpackage */, $required = false);

    // Insert path to our include files into S9Y global configuration
    #$pkg->addReplacement('serendipity_config.inc.php', 'pear-config', '@php_dir@', 'php_dir');

    $pkg->addReplacement('lib/SGL/Task/Init.php', 'pear-config', '@PHP-DIR@',  'php_dir');
    $pkg->addReplacement('lib/SGL/Task/Init.php', 'pear-config', '@DATA-DIR@', 'data_dir');
    $pkg->addReplacement('lib/SGL/Task/Init.php', 'pear-config', '@WEB-DIR@',  'web_dir');
    $pkg->addReplacement('lib/SGL/Install/WizardCreateAdminUser.php', 'pear-config', '@WEB-DIR@',  'web_dir');
    $pkg->addReplacement('www/index.php', 'pear-config', '@DATA-DIR@', 'data_dir');
    $pkg->addReplacement('www/index.php', 'pear-config', '@PHP-DIR@',  'php_dir');
    $pkg->addReplacement('www/setup.php', 'pear-config', '@PHP-DIR@',  'php_dir');
    $pkg->addReplacement('www/setup.php', 'pear-config', '@DATA-DIR@', 'data_dir');

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

    //  get ver 1.0 compatible version
    #$packagexml = $pkg->exportCompatiblePackageFile1();
    #$packagexml->addMaintainer('lead', 'demianturner', 'Demian Turner', 'demian@phpkitchen.com');
    #$test1 = $packagexml->generateContents();


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