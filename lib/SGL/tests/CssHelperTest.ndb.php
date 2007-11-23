<?php

require_once SGL_THEME_DIR . '/csshelpers.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Dmitri Lakachauskis <dmitri at telenet dot lv>
 */
class StylesheetHelperTest extends UnitTestCase
{
    function StylesheetHelperTest()
    {
        $this->UnitTestCase('Stylesheet Helper Test');
    }

    function testIsBrowserFamily()
    {
        // MSIE tests
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/4.0 (compatible; MSIE 6.0; ' .
            'Windows NT 5.1)';

        $ret = isBrowserFamily('MSIE7', 'ge', true);
        $this->assertFalse($ret);

        $ret = isBrowserFamily('Gecko');
        $this->assertFalse($ret);

        $ret = !isBrowserFamily('Gecko');
        $this->assertTrue($ret);

        $ret = isBrowserFamily('MSIE');
        $this->assertTrue($ret);

        $ret = isBrowserFamily('MSIE6', '<');
        $this->assertFalse($ret);

        $ret = isBrowserFamily('MSIE6.0', 'eq');
        $this->assertTrue($ret);

        $ret = isBrowserFamily('Gecko', '<');
        $this->assertFalse($ret);

        $ret = isBrowserFamily('Gecko', '>');
        $this->assertFalse($ret);

        // Gecko tests
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows; U; Windows NT ' .
            '5.1; en-US; rv:1.8.0.8) Gecko/20061025 Firefox/1.5.0.8';

        $ret = isBrowserFamily('Gecko', null, true);
        $this->assertTrue($ret);

        $ret = isBrowserFamily('Gecko2.2');
        $this->assertTrue($ret);

        $ret = isBrowserFamily('Gecko2.2', '>');
        $this->assertTrue($ret);

        $ret = isBrowserFamily('Gecko2.2', '<');
        $this->assertTrue($ret);

        $ret = isBrowserFamily('MSIE');
        $this->assertFalse($ret);

        $ret = isBrowserFamily('MSIE', '<');
        $this->assertFalse($ret);

        $ret = isBrowserFamily('MSIE', '>');
        $this->assertFalse($ret);

        // Opera tests
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/4.0 (compatible; MSIE 6.0; ' .
            'Windows NT 5.1; en) Opera 8.50';

        $ret = isBrowserFamily('Opera', null, true);
        $this->assertTrue($ret);

        $ret = isBrowserFamily('Opera', '>');
        $this->assertTrue($ret);

        $ret = isBrowserFamily('Opera', '<');
        $this->assertTrue($ret);

        $ret = !isBrowserFamily('Gecko');
        $this->assertTrue($ret);

        $ret = !isBrowserFamily('MSIE');
        $this->assertTrue($ret);

        $ret = isBrowserFamily('MSIE', '<');
        $this->assertFalse($ret);

        $ret = isBrowserFamily('MSIE', '>');
        $this->assertFalse($ret);
    }
}

?>