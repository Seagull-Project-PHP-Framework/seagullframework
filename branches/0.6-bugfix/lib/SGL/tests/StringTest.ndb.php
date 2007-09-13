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

    function testClean()
    {
        // with a string
        $string = '<p>here is a string with tags<p>';
        $clean  = SGL_String::clean($string);
        $this->assertEqual($clean, 'here is a string with tags');
        // recursive on an array
        $array  = array(
            'foo1'  => '<p>here is a string with tags<p>',
            'foo2'  => '<span>bar2</span>'
        );
        $cleanArray = SGL_String::clean($array);
        $expectedArray = array(
            'foo1'  => 'here is a string with tags',
            'foo2'  => 'bar2'
        );
        $this->assertEqual($cleanArray, $expectedArray);
        // more recursive
        $array  = array(
            'foo1'  => array(
                'bar1' => '<p>here is a string with tags<p>',
                'bar2' => '<span>bar2</span>'
            ),
            'foo2'  => '<p>Another tagged string</p>'
        );
        $cleanArray = SGL_String::clean($array);
        $expectedArray = array(
            'foo1'  => array(
                'bar1' => 'here is a string with tags',
                'bar2' => 'bar2'
            ),
            'foo2'  => 'Another tagged string'
        );
        $this->assertEqual($cleanArray, $expectedArray);
    }

    function testTranslate()
    {
        $GLOBALS['_SGL']['TRANSLATION'] = array(
            '%foo% is my %bar%' => '%foo% is my %bar%',
            '%1% is my %2%'     => '%1% is my %2%',
        );
        $trans = new stdClass();
        $trans->foo = 'Dmitri';
        $trans->bar = 'friend';

        // params is an object
        $expected = 'Dmitri is my friend';
        $ret = SGL_String::translate('%foo% is my %bar%', 'vprintf', $trans);
        $this->assertEqual($ret, $expected);

        // params is an assoc array
        $trans = array();
        $trans['foo'] = 'Dmitri';
        $trans['bar'] = 'friend';

        $ret = SGL_String::translate('%foo% is my %bar%', 'vprintf', $trans);
        $this->assertEqual($ret, $expected);

        // params is array
        $trans = array();
        $trans[] = 'Dmitri';
        $trans[] = 'friend';

        $ret = SGL_String::translate('%1% is my %2%', 'vprintf', $trans);
        $this->assertEqual($ret, $expected);
    }

    function test_removeCharacters()
    {
        $str = "äöüßÀÂÇÈÉÊËÎÏÔÙÛÜàâçèéêëîïôùûüÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÖØÙÚÛÜÝàáâãäåæçèéêëìíîïðñòóôöøùúûüýĆćČčŁłŃńŘřŚśš";
        $this->assertEqual(mb_detect_encoding($str), 'UTF-8');
        $ret = SGL_String::replaceAccents($str);
        $pattern = '/[^A-Z^a-z^0-9()\s]/';
        $this->assertNoUnwantedPattern($pattern, $ret);
        $this->assertEqual(mb_detect_encoding($ret), 'ASCII');
    }

    function test_replaceAccentsFromAllChars()
    {
        $start  = 0x0;
        $end    = 0xFF;
        $str = '';
        for ($i = $start; $i < $end; $i++) {
            $str .= "&#$i;";
        }
        $ret = SGL_String::replaceAccents($str);
        $pattern = '/[^A-Z^a-z^0-9()\s]/';
        $this->assertNoUnwantedPattern($pattern, $ret);
    }
}

?>