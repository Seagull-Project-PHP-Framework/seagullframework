<?php

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class RegistryTest extends PHPUnit_Framework_TestCase
{
    function testAccess()
    {
        $registry = SGL_Registry::singleton();
        $this->assertFalse($registry->exists('a'));
        $this->assertNull($registry->get('a'));
        $thing = 'thing';
        $registry->set('a', $thing);
        $this->assertTrue($registry->exists('a'));
        $this->assertSame($registry->get('a'), $thing);
    }

//    function testSingleton()
//    {
//        SGL_Registry::_unsetSingleton();
//
//        $this->assertSame(
//            SGL_Registry::singleton(),
//            SGL_Registry::singleton());
//        $this->assertEquals(get_class(SGL_Registry::singleton()), 'SGL_Registry');
//    }

    function testSettingRegistryObjectValues()
    {
        SGL_Registry::_unsetSingleton();
        $foo = new TestFoo();
        SGL_Registry::set('foo', $foo);
        $foo->bar = 'baz';
        $this->assertEquals(SGL_Registry::get('foo')->bar, $foo->bar);
    }
}

class TestFoo
{

}

?>