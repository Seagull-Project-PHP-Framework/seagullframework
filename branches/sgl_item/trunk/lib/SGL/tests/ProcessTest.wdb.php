<?php
#require_once dirname(__FILE__) . '/../Registry.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class ProcessTest extends UnitTestCase {

    function ProcessTest()
    {
        $this->UnitTestCase('Registry Test');
    }

    function xtestResolver()
    {
        $reg = SGL_Registry::singleton();
        $resolver = new SGL_Process_ResolveManager();
        $resolver->process($reg);
    }
}

?>