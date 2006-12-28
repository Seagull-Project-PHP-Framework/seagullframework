<?php
require_once dirname(__FILE__) . '/../File.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class FileTest extends UnitTestCase {

    function FileTest()
    {
        $this->UnitTestCase('File Test');
    }

    function testDirCopy()
    {
        $src = SGL_CORE_DIR . '/Install';

        $tmpDir = SGL_Util::getTmpDir();
        $target = $tmpDir . '/testDirCopy';
        $ok = SGL_File::copyDir($src, $target, $overwrite = true);
        $this->assertTrue($ok);

        require_once "File/Archive.php";
        require_once 'System.php';
        //  get size of orig folder
        File_Archive::extract(
            $src,
            File_Archive::toArchive("$target.tar",
                $dest = array(
                    File_Archive::toOutput(),
                    File_Archive::toFiles()
                )
            )
        );
        $srcSize = filesize("$target.tar");
        rename("$target.tar", "$target.1.tar");

        //  get size of target folder
        File_Archive::extract(
            $target,
            File_Archive::toArchive("$target.tar",
                $dest = array(
                    File_Archive::toOutput(),
                    File_Archive::toFiles()
                )
            )
        );
        $targetSize = filesize("$target.tar");

        $this->assertEqual($srcSize, $targetSize);
        unlink("$target.tar");
        unlink("$target.1.tar");
        System::rm("-rf $target");
    }
}

?>