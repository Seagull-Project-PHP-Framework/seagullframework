<?php
require_once SGL_MOD_DIR . '/translation/classes/Translation2.php';
require_once SGL_LIB_PEAR_DIR . '/Config.php';

/**
 * Test suite.
 *
 * @package translation
 * @author  Julien Casanova <julien@soluo.fr>
 */
class TestTranslation2 extends UnitTestCase
{
    function TestTranslation2()
    {
        $this->UnitTestCase('Translation Test');
    }

    function setUp()
    {
        $this->config = array(
            'key'                               => 'value',
            'key with\' escaped quote'          => 'value with \' escaped quote',
            'subArray'                          => array(
                'key with \' quote'             => 'value with \' quote'
            ),
            'subArray key with \' escaped quote' => array(
                'key1 with \' quote'            => 'value1 with \' quote',
                'key2 with \' quote'            => 'value2 with \' quote'
            )
        );
        $this->aMetaKeys = array('__SGL_UPDATED_BY', '__SGL_LAST_UPDATED');

        $_SESSION['username'] = 'testuser';
    }

    function tearDown()
    {
        unset($_SESSION['username']);
    }

    function testGetFileName()
    {
        //require_once SGL_LIB_DIR . '/data/ary.languages.php';
        $module = 'default';
        $langId = 'en-iso-8859-15';
        $translation = new SGL_Translation2();
        $fileName  = $translation->getFileName($module, $langId);
        $expectedFileName = SGL_MOD_DIR . '/' . $module . '/lang/' .
                $GLOBALS['_SGL']['LANGUAGE'][$langId][1] . '.php';
        $this->assertEqual($fileName, $expectedFileName);
    }

    function testUpdateGuiTranslation()
    {
    }

    function testUpdateMetaData()
    {
        $aRet = SGL_Translation2::updateMetaData($this->config);
        foreach ($this->aMetaKeys as $metaKey) {
            $this->assertTrue(array_key_exists($metaKey, $aRet));
        }
        // test first key is meta one
        reset($aRet);
        $key1 = key($aRet);
        $this->assertIdentical(0, strpos($key1, '__SGL_'));

        // test second key is also meta
        next($aRet);
        $key2 = key($aRet);
        $this->assertIdentical(0, strpos($key2, '__SGL_'));
    }

    function testRemoveMetaData()
    {
        $aRet = SGL_Translation2::updateMetaData($this->config);
        $aRet = SGL_Translation2::removeMetaData($aRet);
        foreach ($this->aMetaKeys as $metaKey) {
            $this->assertFalse(array_key_exists($metaKey, $aRet));
        }
    }

    function testLockTranslationFile()
    {
        $moduleName = 'testmodule';
        $lang       = 'language';
        $fileName   = SGL_VAR_DIR . '/translation/' . $moduleName . '_' . $lang . '.lock.txt';

        $ok = SGL_Translation2::lockTranslationFile($moduleName, $lang);
        $this->assertTrue(file_exists($fileName));
        $this->assertEqual(file_get_contents($fileName), SGL_Session::getUsername());

        unlink($fileName);
    }

    function testTranslationFileIsLocked()
    {
        $moduleName = 'testmodule';
        $lang       = 'language';
        $fileName   = SGL_VAR_DIR . '/translation/' . $moduleName . '_' . $lang . '.lock.txt';

        // lock files as testuser
        SGL_Translation2::lockTranslationFile($moduleName, $lang);

        // change user
        $_SESSION['username'] = 'anotheruser';

        // it should be locked for anotheruser
        $ok = SGL_Translation2::translationFileIsLocked($moduleName, $lang);
        $this->assertTrue($ok);

        $_SESSION['username'] = 'testuser';
        $ok = SGL_Translation2::translationFileIsLocked($moduleName, $lang);
        $this->assertFalse($ok);

        // touch
        //$touchTime = time() - (SGL_Translation2::$lockPeriod * 60);
        //touch($fileName, $touchTime);
        //$ok = SGL_Translation2::translationFileIsLocked($moduleName, $lang);
        //$this->assertFalse($ok);

        unlink($fileName);
    }

    function testRemoveTranslationLock()
    {
        $moduleName = 'testmodule';
        $lang       = 'language';
        $fileName   = SGL_VAR_DIR . '/translation/' . $moduleName . '_' . $lang . '.lock.txt';

        SGL_Translation2::lockTranslationFile($moduleName, $lang);
        SGL_Translation2::removeTranslationLock($moduleName, $lang);
        $this->assertFalse(file_exists($fileName));
    }

    function testEscapeSingleQuote()
    {
        // my string with ' single quote and " other chars \
        $string = 'my string with \' single quote and " other chars \\';
        $expected = 'my string with \\\' single quote and " other chars \\\\';

        $ret = SGL_Translation2::escapeSingleQuote($string);
        $this->assertEqual($ret, $expected);

        // test \' string
        $string = 'test \\\' string';
        $expected = 'test \\\\\\\' string';

        $ret = SGL_Translation2::escapeSingleQuote($string);
        $this->assertEqual($ret, $expected);
    }

    function testEscapeSingleQuoteInArrayKeys()
    {
        $expected = array(
            'key'                                  => 'value',
            'key with\\\' escaped quote'           => 'value with \' escaped quote',
            'subArray'                             => array(
                'key with \\\' quote'              => 'value with \' quote'
            ),
            'subArray key with \\\' escaped quote' => array(
                'key1 with \\\' quote'             => 'value1 with \' quote',
                'key2 with \\\' quote'             => 'value2 with \' quote'
            )
        );
        $ret = SGL_Translation2::escapeSingleQuoteInArrayKeys($this->config);
        $this->assertEqual($ret, $expected);
    }

    function testRemoveTranslationLocksByUser()
    {
        // lock module 1 with lang 1
        $moduleName = 'testmodule';
        $lang       = 'language';
        $fileName   = SGL_VAR_DIR . '/translation/' . $moduleName . '_' . $lang . '.lock.txt';
        SGL_Translation2::lockTranslationFile($moduleName, $lang);

        // lock module 2 with lang 2
        $moduleName2 = 'testmodule2';
        $lang2       = 'language2';
        $fileName2   = SGL_VAR_DIR . '/translation/' . $moduleName2 . '_' . $lang2 . '.lock.txt';
        SGL_Translation2::lockTranslationFile($moduleName2, $lang2);

        // switch user
        $_SESSION['username'] = 'anotheruser';

        // lock module 3 with lang 3 by anotheruser
        $moduleName3 = 'testmodule3';
        $lang3       = 'language3';
        $fileName3   = SGL_VAR_DIR . '/translation/' . $moduleName3 . '_' . $lang3 . '.lock.txt';
        SGL_Translation2::lockTranslationFile($moduleName3, $lang3);

        $targetDir = SGL_VAR_DIR . '/translation';

        // test total number of locks
        $filesCount = $this->_getFilesCountInDir($targetDir);
        $this->assertEqual($filesCount, 3);

        // remove locks set by testuser
        SGL_Translation2::removeTranslationLocksByUser('testuser');
        $filesCount = $this->_getFilesCountInDir($targetDir);
        $this->assertEqual($filesCount, 1);

        // remove locks set by anotheruser
        SGL_Translation2::removeTranslationLocksByUser('anotheruser');
        $filesCount = $this->_getFilesCountInDir($targetDir);
        $this->assertEqual($filesCount, 0);
    }

    function _getFilesCountInDir($dir)
    {
        $ret = 0;
        if (is_dir($dir)) {
            $dh = opendir($dir);
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $ret++;
            }
            closedir($dh);
        }
        return $ret;
    }
}
?>