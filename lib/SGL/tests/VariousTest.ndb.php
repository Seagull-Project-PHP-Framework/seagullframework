<?php

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

    function testObjectHasState()
    {
        $foo = new stdClass();
        $foo->bar = "0";
        $this->assertFalse(SGL::objectHasState($foo));
        $foo->bar = "";
        $this->assertFalse(SGL::objectHasState($foo));
        $foo->bar = 0;
        $this->assertFalse(SGL::objectHasState($foo));
        $foo->bar = array();
        $this->assertFalse(SGL::objectHasState($foo));
        $foo->bar = 1;
        $this->assertTrue(SGL::objectHasState($foo));
    }

    function testBuildFilterChain()
    {
        $aFilters = array('Foo1', 'Bar1', 'Baz');
        $code = '$process = ';
        foreach ($aFilters as $filter) {
            $filters .= "new $filter(\n";
            $closeParens .= ')';
        }
        $code = $filters . $closeParens;
        eval("\$process = $code;");
    }

    function testAutoLoad()
    {
        $className = 'Foo1_Bar1_Baz';
        $searchPath = preg_replace('/_/', '/', $className) . '.php';
        $expected = 'Foo1/Bar1/Baz.php';
        $this->assertEqual($expected, $searchPath);
    }

    function testDbVersionParsing()
    {
        $version = '4.1.16';
        $this->assertFalse(version_compare($version, '5', '>='));

        $version = '4.0.24_Debian-10sarge1-log';
        $this->assertFalse(version_compare($version, '5', '>='));

        $version = '5.0.1';
        $this->assertTrue(version_compare($version, '5', '>='));
    }

    function testIsImage()
    {
        $mimeType = 'image/x-png';
        $this->assertTrue(preg_match("/^image/", $mimeType));
    }

    function testApacheTypes()
    {
        $searchString = 'cgi';
        $this->assertTrue(preg_match("/cgi|apache2filter/i", $searchString));
        $searchString = 'apache2filter';
        $this->assertTrue(preg_match("/cgi|apache2filter/i", $searchString));
        $searchString = '';
        $this->assertFalse(preg_match("/cgi|apache2filter/i", $searchString));
    }

    function testArrayFilterForDisallowedMethods()
    {
        $test = array (
          'username' => '',
          'first_name' => 'Demian',
          'last_name' => 'Turner',
          'passwd' => '',
          'password_confirm' => '',
          'addr_1' => '39c Grange Park',
          'addr_2' => '',
          'addr_3' => '39c Grange Park',
          'city' => 'Ealing',
          'region' => '',
          'post_code' => 'W5 3PP',
          'country' => 'GB',
          'email' => 'demian@phpkitchen.com',
          'telephone' => '555555',
          'mobile' => '',
          'security_question' => '0',
          'security_answer' => '',
        );
        // returns no count, no disallowed keys
        $this->assertFalse(count(array_filter(array_flip($test), array($this, 'containsDisallowedKeys'))));

        $test = array (
          'username' => '',
          'first_name' => 'Demian',
          'last_name' => 'Turner',
          'passwd' => '',
          'password_confirm' => '',
          'addr_1' => '39c Grange Park',
          'addr_2' => '',
          'addr_3' => '39c Grange Park',
          'city' => 'Ealing',
          'region' => '',
          'post_code' => 'W5 3PP',
          'country' => 'GB',
          'email' => 'demian@phpkitchen.com',
          'telephone' => '555555',
          'mobile' => '',
          'security_question' => '0',
          'security_answer' => '',
          'role_id' => '', // forbidden key
        );
        //  returns count, disallowed key present
        $this->assertTrue(count(array_filter(array_flip($test), array($this, 'containsDisallowedKeys'))));

        $test = array('non-existant' => 'foo');
        $this->assertFalse(count(array_filter(array_flip($test), array($this, 'containsDisallowedKeys'))));
    }

    function containsDisallowedKeys($var)
    {
        $disAllowedKeys = array('role_id', 'organisation_id', 'is_acct_active');
        $ret = in_array($var, $disAllowedKeys);
        return $ret;
    }
}



class Foo1{}
class Bar1{}
class Baz{}

?>