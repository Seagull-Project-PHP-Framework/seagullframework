<?php
require_once dirname(__FILE__). '/../classes/SimpleDriver.php';

/**
 * Test suite.
 *
 * @package user
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: TestSimpleNav.php,v 1.1 2005/05/08 21:29:06 demian Exp $
 */  
class TestSimpleDriver extends UnitTestCase {

    function TestSimpleDriver()
    {
        $this->UnitTestCase('SimpleDriver Test');
    }

    function testReplacePlusesWithSpacesCallback()
    {
        $url = 'http://example.com/foo+bar/baz/fluux+';
        $baseUri = explode('/', $url);
        //  works only in php4
        //$baseUri = str_replace('+', ' ', $baseUri);
        
        $baseUri = array_map('foo', $baseUri);
        foreach ($baseUri as $elem) {
            $this->assertFalse(stristr($elem, '+'));
        }
    }
    
    function testReplacePlusesWithSpacesCreateFn()
    {
        $url = 'http://example.com/foo+bar/baz/fluux+';
        $baseUri = explode('/', $url);
        //  works only in php4
        //$baseUri = str_replace('+', ' ', $baseUri);
        
        $baseUri = array_map(create_function('$a', 'return str_replace("+", " ", $a);'), $baseUri);
        foreach ($baseUri as $elem) {
            $this->assertFalse(stristr($elem, '+'));
        }
    }    
}

function foo($a)
{
    return str_replace("+", " ", $a);
}
?>