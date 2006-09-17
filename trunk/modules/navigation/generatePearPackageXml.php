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
 * @category   Frameworks
 * @package    Seagull
 * @subpackage Seagull_navigation
 * @author     Demian Turner <demian@phpkitchen.com>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link       http://www.seagullproject.org
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
    $path = (defined('SGL_PKG_TMP_BUILD_DIR'))
       ? SGL_PKG_TMP_BUILD_DIR.'/modules/navigation'
       : dirname(__FILE__);
    $navigation_packagedir  = $path;

    // Name of the channel, this package will be distributed through
    $navigation_channel     = 'pear.phpkitchen.com';

    // Category and name of the package
    $navigation_category    = 'Seagull Modules';
    $navigation_package     = 'Seagull_navigation';

    $navigation_version     = '1.0';

    // Summary description
    $navigation_summary     = <<<EOT
The navigation module provides functionality to create and administer a nagivation hierarchy.
EOT;

    // Longer description
    $navigation_description = <<<EOT
There are a wide range of features.
EOT;

    // License information
    $navigation_license = 'BSD';

    // Notes, function to grab them directly from S9Y in
    // generate_package_xml_functions.php
    $navigation_notes = <<<EOT
Publisher notes.
EOT;

    // Instantiate package file manager
    $navigation_pkg = new PEAR_PackageFileManager2();

    // Setting options
    $e = $navigation_pkg->setOptions(
        array(
            // Where are our package files.
            'packagedirectory'  => $navigation_packagedir,
            // Where will package files be installed in
            // the local PEAR repository?
            'baseinstalldir'    => 'Seagull/modules/navigation',
            // Where should the package file be generated
            'pathtopackagefile' => $navigation_packagedir,
            // Just simple output, no MD5 sums and <provides> tags
            #'simpleoutput'      => true,

            'packagefile'       => 'package2.xml',
            // Use standard file list generator, choose CVS, if you
            // have your code in CVS
            'filelistgenerator' => 'file',

            // List of files to ignore and put not explicitly into the package
            'ignore'            =>
            array(
                'package2.xml',
                '*tests*',
                '*.svn',
            ),

            // Global mapping of directories to file roles.
            // @see http://pear.php.net/manual/en/guide.migrating.customroles.defining.php
            'dir_roles'         =>
            array(
                'docs' => 'doc',
                'lib' => 'php',
                'modules' => 'php',
                'www' => 'web',
            ),

            'roles'             =>
            array(
                'php' => 'php',
                '*' => 'php',
            ),

            // Define exceptions of previously defined role mappings,
            // this part uses real file names and no directories.
            'exceptions'        =>
            array(
            ),
        )
    );

    // PEAR error checking
    if (PEAR::isError($e)) {
        die($e->getMessage());
    }

    // Set misc package information
    $navigation_pkg->setPackage($navigation_package);
    $navigation_pkg->setSummary($navigation_summary);
    $navigation_pkg->setDescription($navigation_description);
    $navigation_pkg->setChannel($navigation_channel);

    $navigation_pkg->setReleaseStability('beta');
    $navigation_pkg->setAPIStability('stable');
    $navigation_pkg->setReleaseVersion($navigation_version);
    $navigation_pkg->setAPIVersion($navigation_version);

    $navigation_pkg->setLicense($navigation_license);
    $navigation_pkg->setNotes($navigation_notes);

    // Our package contains PHP files (not C extension files)
    $navigation_pkg->setPackageType('php');

    // Must be available in new package.xml format
    $navigation_pkg->setPhpDep('4.3.0');
    $navigation_pkg->setPearinstallerDep('1.4.6');

    // Require PEAR_DB package for initializing the database in the post install script
    #$navigation_pkg->addPackageDepWithChannel('required', 'XML_Util', 'pear.php.net', '1.1.1');

    // Create the current release and add it to the package definition
    $navigation_pkg->addRelease();

    // Package release needs a maintainer
    $navigation_pkg->addMaintainer('lead', 'demianturner', 'Demian Turner', 'demian@phpkitchen.com');

    // Internally generate the XML for our package.xml (does not perform output!)
    $test = $navigation_pkg->generateContents();

if (!defined('SGL_PKG_TMP_BUILD_DIR'))    {
    if (isset($_GET['make']) || (isset($_SERVER['argv'][1]) &&
            $_SERVER['argv'][1] == 'make')) {
        #$e = $pkg->writePackageFile();
        $e = $navigation_pkg->writePackageFile();

        #$e = $packagexml->writePackageFile();
    } else {
        #$e = $pkg->debugPackageFile();
        $e = $navigation_pkg->debugPackageFile();
        #$e = $packagexml->debugPackageFile();
    }

    if (PEAR::isError($e)) {
        echo $e->getMessage();
    }
}

?>