<?php

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 */  
class SGL_RegistryTest extends PHPUnit_Framework_TestCase {
    
    function testAccess()
    {
        $registry = SGL_Registry::singleton();        
        $this->assertFalse($registry->exists('a'));
        $this->assertNull($registry->get('a'));
        $thing = 'thing';
        $registry->set('a', $thing);
        $this->assertTrue($registry->exists('a'));
        #$this->assertReference($registry->get('a'), $thing);        
    }
    
    function testSingleton() 
    {
        $this->assertSame(
                SGL_Registry::singleton(),
                SGL_Registry::singleton());
        $this->assertInstanceOf('SGL_Registry', SGL_Registry::singleton());
    }
}

?>