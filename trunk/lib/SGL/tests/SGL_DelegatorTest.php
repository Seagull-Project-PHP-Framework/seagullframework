<?php

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */  
class SGL_DelegatorTest extends PHPUnit_Framework_TestCase {

    function testAddingDelegates()
    {
        $d = new SGL_Delegator();
        $d->add(new Foo());
        $ret = $d->fooFirst();
        $this->assertEquals($ret, Foo::fooFirst());
    }
    
    function testExtendingFromDelegator()
    {
        $foo = new Foo();
        $foo->add(new Bar());
        $this->assertEquals($foo->barFirst(), Bar::barFirst());
    }
}

class Foo extends SGL_Delegator 
{
    function fooFirst()
    {
        return 'fooFirst';
    }
    
    function fooSecond()
    {
        return 'fooSecond';
    }   
}

class Bar
{
    function barFirst()
    {
        return 'barFirst';
    }
    
    function barSecond()
    {
        return 'barSecond';
    }   
}
?>