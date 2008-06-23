<?php

require_once SGL_CORE_DIR . '/Output.php';
require_once SGL_CORE_DIR . '/String.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class TestOutput extends UnitTestCase
{
    function TestOutput()
    {
        $this->UnitTestCase('Output Test');
    }

    function setUp()
    {
        $this->aTrans = $GLOBALS['_SGL']['TRANSLATION'];
        $GLOBALS['_SGL']['TRANSLATION'] = array(
            'my string with %param1% one param' => 'my string with %param1% one param',
            'my string with %param1% one param and second %param2% param' => 'my string with %param1% one param and second %param2% param',
            'my string with %1% one param and second %2% param' => 'my string with %1% one param and second %2% param'
        );
    }

    function tearDown()
    {
        $GLOBALS['_SGL']['TRANSLATION'] = $this->aTrans;
    }

    function testTranslate()
    {
        $output = new SGL_Output();
        // add var
        $output->value1 = 'my value 1';

        // translate string with one argument
        $ret = $output->translate('my string with %param1% one param', 'vprintf',
            'param1|value1');
        $expected = 'my string with ' . $output->value1 . ' one param';
        $this->assertEqual($ret, $expected);

        // add another var
        $output->value2 = 'my value 2';

        // translate string with two arguments
        $ret = $output->translate('my string with %param1% one param and second %param2% param',
            'vprintf', 'param1|value1||param2|value2');
        $expected = 'my string with ' . $output->value1
            . ' one param and second ' . $output->value2 . ' param';
        $this->assertEqual($ret, $expected);

        $ret = $output->translate('my string with %1% one param and second %2% param',
            'vprintf', 'value1||value2');
        $expected = 'my string with ' . $output->value1
            . ' one param and second ' . $output->value2 . ' param';
        $this->assertEqual($ret, $expected);
    }
}

?>