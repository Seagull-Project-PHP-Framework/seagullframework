<?php
require_once dirname(__FILE__) . '/../String.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */  
class StringTest extends UnitTestCase {

    function StringTest()
    {
        $this->UnitTestCase('String Test');
    }
    
    function testStripIniFileIllegalChars()
    {
        $target = 'these are legal chars';
        $targetLen = strlen($target);
        $this->assertEqual($targetLen, strlen(SGL_String::stripIniFileIllegalChars($target)));
        
        $target = 'contains illegal " character';
        $targetLen = strlen($target);
        $this->assertEqual($targetLen, strlen(SGL_String::stripIniFileIllegalChars($target)) +1);
        
        $target = 'contains illegal | character';
        $targetLen = strlen($target);
        $this->assertEqual($targetLen, strlen(SGL_String::stripIniFileIllegalChars($target)) +1);
        
        $target = 'contains illegal & character';
        $targetLen = strlen($target);
        $this->assertEqual($targetLen, strlen(SGL_String::stripIniFileIllegalChars($target)) +1);
        
        $target = 'contains illegal ~ character';
        $targetLen = strlen($target);
        $this->assertEqual($targetLen, strlen(SGL_String::stripIniFileIllegalChars($target)) +1);
        
        $target = 'contains illegal ! character';
        $targetLen = strlen($target);
        $this->assertEqual($targetLen, strlen(SGL_String::stripIniFileIllegalChars($target)) +1);
        
        $target = 'contains illegal ( character';
        $targetLen = strlen($target);
        $this->assertEqual($targetLen, strlen(SGL_String::stripIniFileIllegalChars($target)) +1);
        
        $target = 'contains illegal ) character';
        $targetLen = strlen($target);
        $this->assertEqual($targetLen, strlen(SGL_String::stripIniFileIllegalChars($target)) +1);
    }
}

?>