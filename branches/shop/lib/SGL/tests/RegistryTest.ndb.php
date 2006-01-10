<?php
require_once dirname(__FILE__) . '/../Registry.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */  
class RegistryTest extends UnitTestCase {

    function RegistryTest()
    {
        $this->UnitTestCase('Registry Test');
    }
    
    function testAccess()
    {
        $registry = &SGL_Registry::singleton();        
        $this->assertFalse($registry->exists('a'));
        $this->assertNull($registry->get('a'));
        $thing = 'thing';
        $registry->set('a', $thing);
        $this->assertTrue($registry->exists('a'));
        #$this->assertReference($registry->get('a'), $thing);        
    }
    
    function testSingleton() 
    {
        $this->assertReference(
                SGL_Registry::singleton(),
                SGL_Registry::singleton());
        $this->assertIsA(SGL_Registry::singleton(), 'SGL_Registry');
    }
}

?>