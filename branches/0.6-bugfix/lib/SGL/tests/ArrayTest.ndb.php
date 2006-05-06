<?php
require_once dirname(__FILE__) . '/../Array.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class ArrayTest extends UnitTestCase {

    function ArrayTest()
    {
        $this->UnitTestCase('Array Test');
    }

    function testConfigMerge()
    {
        $confOld['db']['type'] = 'mysql_SGL';
        $confOld['db']['host'] = 'localhost';
        $confOld['db']['protocol'] = 'tcp';
        $confOld['db']['socket'] = '';
        $confOld['db']['port'] = '3306';
        $confOld['db']['user'] = 'root';
        $confOld['db']['pass'] = 'root';
        $confOld['db']['name'] = 'seagulltrunk';
        $confOld['db']['postConnect'] = '';
        $confOld['db']['prefix'] = 'not implemented yet';
        $confOld['db']['tesssst'] = 'kr neki';
        $confOld['new']['section'] = 'test';

        $confNew['db']['type'] = 'a';
        $confNew['db']['host'] = 'b';
        $confNew['db']['protocol'] = 'c';
        $confNew['db']['socket'] = 'd';
        $confNew['db']['port'] = 'e';
        $confNew['db']['user'] = 'f';
        $confNew['db']['pass'] = 'g';
        $confNew['db']['name'] = 'h';
        $confNew['db']['postConnect'] = 'i';
        $confNew['db']['prefix'] = 'j';

        $ret = merge($confOld, $confNew); // former, defective Config::merge
        $this->assertFalse($ret['db']['type'] == $confNew['db']['type']);
        $this->assertTrue($ret['db']['type'] == $confOld['db']['type']);

        //  Miha's improve method gives expected results
        $ret = SGL_Array::mergeReplace($confOld, $confNew);
        $this->assertTrue($ret['db']['type'] == $confNew['db']['type']);
        $this->assertFalse($ret['db']['type'] == $confOld['db']['type']);
    }

    function testWithConfigMerge()
    {
        $confOld['db']['type'] = 'mysql_SGL';
        $confOld['db']['host'] = 'localhost';
        $confOld['db']['protocol'] = 'tcp';
        $confOld['db']['socket'] = '';
        $confOld['db']['port'] = '3306';
        $confOld['db']['user'] = 'root';
        $confOld['db']['pass'] = 'root';
        $confOld['db']['name'] = 'seagulltrunk';
        $confOld['db']['postConnect'] = '';
        $confOld['db']['prefix'] = 'not implemented yet';
        $confOld['db']['tesssst'] = 'kr neki';
        $confOld['new']['section'] = 'test';

        $confNew['db']['type'] = 'a';
        $confNew['db']['host'] = 'b';
        $confNew['db']['protocol'] = 'c';
        $confNew['db']['socket'] = 'd';
        $confNew['db']['port'] = 'e';
        $confNew['db']['user'] = 'f';
        $confNew['db']['pass'] = 'g';
        $confNew['db']['name'] = 'h';
        $confNew['db']['postConnect'] = 'i';
        $confNew['db']['prefix'] = 'j';

        $c = &SGL_Config::singleton();
        $c->replace($confOld);

        $c->merge($confNew);
        $ret = $c->getAll();
        $this->assertTrue($ret['db']['type'] == $confNew['db']['type']);
        $this->assertFalse($ret['db']['type'] == $confOld['db']['type']);
    }
}

/**
 * Old Config::merge method
 *
 * @param array $aProps
 * @param array $aConf
 * @return array
 */
function merge($aProps, $aConf)
{
  $firstKey = key($aConf);
  if (!array_key_exists($firstKey, $aProps)) {
      $aProps = array_merge_recursive($aProps, $aConf);
  }
  return $aProps;
}

?>