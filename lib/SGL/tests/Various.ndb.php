<?php
require_once dirname(__FILE__) . '/../Url.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */  
class VariousTest extends UnitTestCase {

    function VariousTest()
    {
        $this->UnitTestCase('Various Test');
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
    
    function testIsSetAndEmpty()
    {
        $this->assertFalse(@$foo);
        $this->assertNull(@$foo);
        $this->assertFalse(isset($foo));
        $this->assertTrue(empty($foo));
        
        //  test for null and non-null values
        $foo = null;
        $this->assertFalse(isset($foo));
        $this->assertFalse(!empty($foo));        
        $foo = 'up';
        $this->assertTrue(!empty($foo));
    }
}
?>