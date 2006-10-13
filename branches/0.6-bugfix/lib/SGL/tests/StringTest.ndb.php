<?php
require_once dirname(__FILE__) . '/../String.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class StringTest extends UnitTestCase {

    function StringTest()
    {
        $this->UnitTestCase('String Test');
    }

    function testStripIniFileIllegalChars()
    {
        $target = 'these are legal chars';
        $targetLen = strlen($target);
        $this->assertEqual($targetLen, strlen(SGL_String::stripIniFileIllegalChars($target)));

        $target = 'contains illegal " character';
        $targetLen = strlen($target);
        $this->assertEqual($targetLen, strlen(SGL_String::stripIniFileIllegalChars($target)) +1);

        $target = 'contains illegal | character';
        $targetLen = strlen($target);
        $this->assertEqual($targetLen, strlen(SGL_String::stripIniFileIllegalChars($target)) +1);

        $target = 'contains illegal & character';
        $targetLen = strlen($target);
        $this->assertEqual($targetLen, strlen(SGL_String::stripIniFileIllegalChars($target)) +1);

        $target = 'contains illegal ~ character';
        $targetLen = strlen($target);
        $this->assertEqual($targetLen, strlen(SGL_String::stripIniFileIllegalChars($target)) +1);

        $target = 'contains illegal ! character';
        $targetLen = strlen($target);
        $this->assertEqual($targetLen, strlen(SGL_String::stripIniFileIllegalChars($target)) +1);

        $target = 'contains illegal ( character';
        $targetLen = strlen($target);
        $this->assertEqual($targetLen, strlen(SGL_String::stripIniFileIllegalChars($target)) +1);

        $target = 'contains illegal ) character';
        $targetLen = strlen($target);
        $this->assertEqual($targetLen, strlen(SGL_String::stripIniFileIllegalChars($target)) +1);
    }

    function testRemoveEmptyElements()
    {
        $arr = array(
                0 => 'foo',
                1 => false,
                2 => -1,
                3 => null,
                4 => '',
                5 => array(),
                  );

        $target = array(
                0 => 'foo',
                2 => -1,
                );
        $arr = SGL_Array::removeBlanks($arr);
        $this->assertEqual($arr, $target);
    }

    function testDirify()
    {
        $aControl[] = 'Here is a sentence-like string.';
        $aControl[] = ' Here is a sentence-like string.';
        $aControl[] = ' *Here is a sentence-like string.';
        $aExpected[] = 'here_is_a_sentence-like_string';
        $aExpected[] = '_here_is_a_sentence-like_string';
        $aExpected[] = '_here_is_a_sentence-like_string';
        foreach ($aControl as $k => $control) {
            $ret = SGL_String::dirify($control);
            $this->assertEqual($aExpected[$k], $ret);
        }
    }

    function testCamelise()
    {
        $aControl[] = 'Here is a string to camelise';
        $aControl[] = ' here IS a StrIng tO CameLise';
        $aControl[] = ' Here  is a  STRING To  CameliSE';
        $aControl[] = "Here is\na string\n\nto camelise";
        $expected   = 'hereIsAStringToCamelise';

        foreach ($aControl as $k => $control) {
            $ret = SGL_String::camelise($control);
            $this->assertEqual($expected, $ret);
        }
    }
}

?>