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
        $closeParens = '';
        $filters = '';
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

    function testORingActions()
    {
        $action = 'insert';
        $this->assertTrue($action == ('update' || 'insert'));

        $action = 'bar';
        //  fails
        //$this->assertFalse($action == ('update' || 'insert'));
    }

    function testObtainNextNumericKey()
    {
        $aFiles =
          array (
            'file1' => 'etc/sequence.my.sql',
            'file2' => 'modules/default/data/schema.my.sql',
            'file3' => 'modules/user/data/schema.my.sql',
            'file4' => 'modules/navigation/data/schema.my.sql',
            'file5' => 'modules/block/data/schema.my.sql',
          );
          $this->assertEqual(6, $this->getNextKey($aFiles));
    }

    function testVariousZeroBooleanCasts()
    {
        $var = 0;
        $ret = (!(bool) $var);
        $this->assertEqual($ret, true);
        $ret = !(bool) $var;
        $var = "0";
        $ret = !(bool) $var;
        $this->assertEqual($ret, true);
    }

    function test_hasAdminGui()
    {
        $rid = SGL_Session::getRoleId();
        if ($rid == SGL_ADMIN) {
            $this->assertTrue(SGL_Session::hasAdminGui());
        } else {
            $this->assertFalse(SGL_Session::hasAdminGui());
        }
    }

    function getNextKey($aKeys)
    {
        $keys = array_keys($aKeys);
        $out = array();
        foreach ($keys as $k) {
            preg_match("/[0-9].*/", $k, $matches);
            $out[] = $matches[0];
        }
        return (max($out)) +1;
    }
}

class Foo1{}
class Bar1{}
class Baz{}

?>
