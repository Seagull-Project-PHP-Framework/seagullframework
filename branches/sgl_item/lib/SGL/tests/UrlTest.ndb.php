<?php
require_once dirname(__FILE__) . '/../Url.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */  
class UrlTest extends UnitTestCase {

    function UrlTest()
    {
        $this->UnitTestCase('Url Test');
    }

    function testParseResourceUriFullString()
    {
        $url = 'contactus/contactus/action/list/enquiry_type/Hosting info';
        $obj = new SGL_Url();
        $ret = $obj->parseResourceUri($url);
        $this->assertTrue(is_array($ret));
        $this->assertTrue(array_key_exists('module', $ret));
        $this->assertTrue(array_key_exists('manager', $ret));
        $this->assertTrue(array_key_exists('actionMapping', $ret));        
        $this->assertTrue(is_array($ret['parsed_params']));
        $this->assertTrue(array_key_exists('enquiry_type', $ret['parsed_params']));        
    }
    
    function testParseResourceUriSlash()
    {
        $url = '/';
        $obj = new SGL_Url();
        $ret = $obj->parseResourceUri($url);
        $this->assertTrue(is_array($ret));
        $this->assertTrue(array_key_exists('module', $ret));
        $this->assertTrue(array_key_exists('manager', $ret));
    }

    function testParseResourceUriEmpty()
    {
        $url = '';
        $obj = new SGL_Url();
        $ret = $obj->parseResourceUri($url);
        $this->assertTrue(is_array($ret));
        $this->assertTrue(array_key_exists('module', $ret));
        $this->assertTrue(array_key_exists('manager', $ret));
    }
    
    function testParseAndExecute()
    {
        $sql = <<< EOF
INSERT INTO item_type VALUES (5,'Static Html Article');

#
# Dumping data for table `item_type_mapping`
#


INSERT INTO item_type_mapping VALUES (3,2,'title',0);
INSERT INTO item_type_mapping VALUES (4,2,'bodyHtml',2);
EOF;
        $aLines = explode("\n", $sql);
        $ret = $this->parse1($aLines);
//        print '<pre>'; print_r($ret);
        
        $ret = $this->parse2($aLines);
//        print '<pre>'; print_r($ret);
    }
    
    function parse1($aLines)
    {
        $sql = '';
        foreach ($aLines as $line) {
            $line = trim($line);
            $cmt  = substr($line, 0, 2);
            if ($cmt == '--' || trim($cmt) == '#') {
                continue;
            }
            $sql .= $line;
            if (!preg_match("/;\s*$/", $sql)) {
                continue;
            }
        }
        return $sql;
    }
    
    function parse2($aLines)
    {
        $sql = '';
        foreach ($aLines as $line) {
            if (preg_match("/^\s*(--)|^\s*#/", $line)) {
                continue;
            }
            $sql .= $line;
            if (!preg_match("/;\s*$/", $sql)) {
                continue;
            }
        }
        return $sql;
    }
    
    function testRemoveNonAlphaChars()
    {
        $foo = 'this is (foo - )';
        $pattern = "/[^\sa-z]/i";
        $replace = "";
        $ret = preg_replace($pattern, $replace, $foo);
        $this->assertEqual($ret, 'this is foo  ');     
    }
}
?>