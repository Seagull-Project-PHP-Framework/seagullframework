<?php

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class InflectorTest extends PHPUnit_Framework_TestCase
{
    function testGetTitleFromCamelCase()
    {
        $camelWord = 'thisIsAnotherCamelWord';
        $ret = SGL2_Inflector::getTitleFromCamelCase($camelWord);
        $this->assertEquals($ret, 'This Is Another Camel Word');
    }

    function testCamelise()
    {
        $aControl[] = 'Here is a string to camelise';
        $aControl[] = ' here IS a StrIng tO CameLise';
        $aControl[] = ' Here  is a  STRING To  CameliSE';
        $aControl[] = "Here is\na string\n\nto camelise";
        $expected   = 'hereIsAStringToCamelise';

        foreach ($aControl as $k => $control) {
            $ret = SGL2_Inflector::camelise($control);
            $this->assertEquals($expected, $ret);
        }
    }

    function testIsCamelCase()
    {
        $str = 'thisIsCamel';
        $this->assertTrue(SGL2_Inflector::isCamelCase($str));

        $str = 'ThisIsCamel';
        $this->assertTrue(SGL2_Inflector::isCamelCase($str));

        $str = 'this_Is_not_Camel';
        $this->assertFalse(SGL2_Inflector::isCamelCase($str));

        $str = 'thisisnotcamel';
        $this->assertFalse(SGL2_Inflector::isCamelCase($str));

        $str = 'Thisisnotcamel';
        $this->assertFalse(SGL2_Inflector::isCamelCase($str));

        $str = 'thisisnotcameL';
        $this->assertFalse(SGL2_Inflector::isCamelCase($str));
    }

    function testIsConstant()
    {
        $this->assertTrue(SGL2_Inflector::isConstant('THIS_IS_A_CONSTANT'));
        $this->assertTrue(SGL2_Inflector::isConstant('CONSTANT'));
        $this->assertTrue(SGL2_Inflector::isConstant("'CONSTANT'"));
        $this->assertFalse(SGL2_Inflector::isConstant('CONSTANTa'));
        $this->assertFalse(SGL2_Inflector::isConstant('1'));
        $this->assertFalse(SGL2_Inflector::isConstant(''));
        $this->assertFalse(SGL2_Inflector::isConstant('127.0.0.1'));
        $this->assertFalse(SGL2_Inflector::isConstant('/'));
        $this->assertFalse(SGL2_Inflector::isConstant('SGLSESSID'));
        $this->assertFalse(SGL2_Inflector::isConstant('CUR ADM OUR NOR STA NID'));
    }
}

?>