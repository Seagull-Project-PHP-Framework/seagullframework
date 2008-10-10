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
    public function testAccess()
    {
        $this->assertFalse(SGL2_Registry::exists('a'));

        try {
            SGL2_Registry::get('a');
            $this->fail('Expected exception when trying to fetch a non-existent key.');
        } catch (Exception $e) {
            $this->assertContains('No entry is registered for key', $e->getMessage());
        }
        $thing = 'thing';
        SGL2_Registry::set('a', $thing);
        $this->assertTrue(SGL2_Registry::exists('a'));
        $this->assertSame(SGL2_Registry::get('a'), $thing);
    }

    public function testCannotSetTwice()
    {
        $c = new TestFoo();
        SGL2_Registry::set('c', $c);
        try {
            SGL2_Registry::set('c', $c);
            $this->fail('Expected exception when trying to set an existing key.');
        } catch (Exception $e) {
            $this->assertContains('An entry is already registered for key', $e->getMessage());
        }
    }

    public function testSettingRegistryObjectValues()
    {
        $foo = new TestFoo();
        SGL2_Registry::set('foo', $foo);
        $foo->bar = 'baz';
        $this->assertEquals(SGL2_Registry::get('foo')->bar, $foo->bar);
    }
}

class TestFoo
{

}

?>