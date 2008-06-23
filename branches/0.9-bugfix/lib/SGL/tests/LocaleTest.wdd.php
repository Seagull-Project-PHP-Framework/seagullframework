<?php

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class LocaleTest extends UnitTestCase {

    function LocaleTest()
    {
        $this->UnitTestCase('Locale Test');
    }

    function xtestSingleton()
    {
        $locale = SGL_Locale::singleton();
        print '<pre>'; print_r($locale);
    }

    function xtestSingletonWithArg()
    {
        require_once dirname(__FILE__) . '/../Locale.php';
        $locale = SGL_Locale::singleton('en_GB');
    }
}

?>