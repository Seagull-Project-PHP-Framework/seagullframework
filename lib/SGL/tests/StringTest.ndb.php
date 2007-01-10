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

    function test_pseudoConstantToInt()
    {
        define('TMP_CONSTANT', 23);
        $this->assertTrue($this->_isValidPseudoConstantToIntRetVal(SGL_String::pseudoConstantToInt("'TMP_CONSTANT'")));
        $this->assertTrue($this->_isValidPseudoConstantToIntRetVal(SGL_String::pseudoConstantToInt('TMP_CONSTANT')));
        $this->assertTrue($this->_isValidPseudoConstantToIntRetVal(SGL_String::pseudoConstantToInt("23")));
        $this->assertTrue($this->_isValidPseudoConstantToIntRetVal(SGL_String::pseudoConstantToInt(23)));
        $this->assertFalse($this->_isValidPseudoConstantToIntRetVal(SGL_String::pseudoConstantToInt("'UNDEFINED_TEST_CONSTANT'")));
        $this->assertFalse($this->_isValidPseudoConstantToIntRetVal(SGL_String::pseudoConstantToInt('UNDEFINED_TEST_CONSTANT')));
    }

    function _isValidPseudoConstantToIntRetVal($val)
    {
        return is_int($val) && $val > 0;
    }

    function test_toValidVariableName()
    {
        $aControl[] = 'hsdfsd(*&*&^Y&  _+|"|:sdfdf  sSDDFD';
        $aControl[] = ' Dsdfsd(*&*&^Y&  _+|"|:sdfdf  sSDDFD';
        $aExpected[] = 'hsdfsdY_sdfdfsSDDFD';
        $aExpected[] = 'dsdfsdY_sdfdfsSDDFD';
        foreach ($aControl as $k => $control) {
            $ret = SGL_String::toValidVariableName($control);
            $this->assertEqual($aExpected[$k], $ret);
        }
    }
}

?>