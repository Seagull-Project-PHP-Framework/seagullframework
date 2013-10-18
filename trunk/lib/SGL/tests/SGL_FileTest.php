<?php

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class SGL_FileTest extends PHPUnit_Framework_TestCase {

    function testDirCopy()
    {
        $src = SGL_CORE_DIR . '/Install';

        $tmpDir = SGL_Util::getTmpDir();
        $target = $tmpDir . '/testDirCopy';
        $ok = SGL_File::copyDir($src, $target, $overwrite = true);
        $this->assertTrue($ok);

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

        $this->assertEquals($srcSize, $targetSize);
        unlink("$target.tar");
        unlink("$target.1.tar");
        System::rm("-rf $target");
    }
}

?>