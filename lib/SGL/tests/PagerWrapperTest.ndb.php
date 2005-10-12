<?php
// $Id: PagerWrapperTest.ndb.php,v 1.2 2005/06/23 15:25:06 demian Exp $

class PagerWrapperTest extends UnitTestCase
{
    function PagerWrapperTest($name='Test of Pager_Wrapper') 
    {
        $this->UnitTestCase($name);
    }
    
    function setUp() { }
    function tearDown() { }
    
    function testRewriteCountQuery() {
        //test LIMIT
        $query = 'SELECT a, b, c, d FROM mytable WHERE a=1 AND c="g" LIMIT 2';
        $expected = 'SELECT COUNT(*) FROM mytable WHERE a=1 AND c="g"';
        $this->assertEqual($expected, rewriteCountQuery($query));
        
        //test ORDER BY and quotes
        $query = 'SELECT a, b, c, d FROM mytable WHERE a=1 AND c="g" ORDER BY (a, b)';
        $expected = 'SELECT COUNT(*) FROM mytable WHERE a=1 AND c="g"';
        $this->assertEqual($expected, rewriteCountQuery($query));
        
        //test CR/LF
        $query = 'SELECT a, b, c, d FROM mytable
                   WHERE a=1
                     AND c="g"
                ORDER BY (a, b)';
        $expected = 'SELECT COUNT(*) FROM mytable
                   WHERE a=1
                     AND c="g"';
        $this->assertEqual($expected, rewriteCountQuery($query));
        
        //test GROUP BY
        $query = 'SELECT a, b, c, d FROM mytable WHERE a=1 GROUP  BY c';
        $this->assertFalse(rewriteCountQuery($query));
        
        //test DISTINCT
        $query = 'SELECT DISTINCT a, b, c, d FROM mytable WHERE a=1 GROUP BY c';
        $this->assertFalse(rewriteCountQuery($query));
        
        //test MiXeD Keyword CaSe
        $query = 'SELECT a, b, c, d from mytable WHERE a=1 AND c="g"';
        $expected = 'SELECT COUNT(*) FROM mytable WHERE a=1 AND c="g"';
        $this->assertEqual($expected, rewriteCountQuery($query));

        //test keywords embedded in other words
        $query = 'SELECT afieldFROM, b, c, d FROM mytable WHERE a=1 AND c="g"';
        $expected = 'SELECT COUNT(*) FROM mytable WHERE a=1 AND c="g"';
        $this->assertEqual($expected, rewriteCountQuery($query));

        $query = 'SELECT FROMafield, b, c, d FROM mytable WHERE a=1 AND c="g"';
        $expected = 'SELECT COUNT(*) FROM mytable WHERE a=1 AND c="g"';
        $this->assertEqual($expected, rewriteCountQuery($query));

        $query = 'SELECT afieldFROMaaa, b, c, d FROM mytable WHERE a=1 AND c="gLIMIT"';
        $expected = 'SELECT COUNT(*) FROM mytable WHERE a=1 AND c="gLIMIT"';
        $this->assertEqual($expected, rewriteCountQuery($query));
        
        $query = 'SELECT DISTINCTaaa, b, c, d FROM mytable WHERE a=1 AND c="g"';
        $expected = 'SELECT COUNT(*) FROM mytable WHERE a=1 AND c="g"';
        $this->assertEqual($expected, rewriteCountQuery($query));

        //this one fails... the regexp should NOT match keywords within quotes...
        //anyway, it's just a missed optimization chance, nothing wrong will happen.
        $query = 'SELECT afieldFROMaaa, b, c, d FROM mytable WHERE a=1 AND c="g LIMIT a"';
        $expected = 'SELECT COUNT(*) FROM mytable WHERE a=1 AND c="g LIMIT a"';
        $this->assertNotequal($expected, rewriteCountQuery($query));

        //test subqueries
        $query = 'SELECT a, b, c, d FROM (SELECT a, b, c, d FROM mytable WHERE a=1) AS tbl_alias WHERE a=1';
        $expected = 'SELECT COUNT(*) FROM (SELECT a, b, c, d FROM mytable WHERE a=1) AS tbl_alias WHERE a=1';
        $this->assertEqual($expected, rewriteCountQuery($query));
        
        //this one fails... subqueries with ORDER BY clauses are truncated
        $query = 'SELECT Version.VersionId, Version.Identifier,News.* FROM VersionBroker JOIN
ObjectType ON ObjectType.ObjectTypeId = VersionBroker.ObjectTypeId JOIN
Version ON VersionBroker.Identifier = Version.Identifier JOIN News ON
Version.ObjectId = News.NewsId WHERE Version.Status = \'Approved\' AND
ObjectType.Name = \'News\' AND Version.ApprovedTS = ( SELECT SubV.ApprovedTS
FROM Version SubV WHERE SubV.Identifier = VersionBroker.Identifier ORDER BY
ApprovedTS DESC LIMIT 1) ORDER BY ApprovedTS DESC';

        $expected = 'SELECT COUNT(*) FROM VersionBroker JOIN
ObjectType ON ObjectType.ObjectTypeId = VersionBroker.ObjectTypeId JOIN
Version ON VersionBroker.Identifier = Version.Identifier JOIN News ON
Version.ObjectId = News.NewsId WHERE Version.Status = \'Approved\' AND
ObjectType.Name = \'News\' AND Version.ApprovedTS = ( SELECT SubV.ApprovedTS
FROM Version SubV WHERE SubV.Identifier = VersionBroker.Identifier ORDER BY
ApprovedTS DESC LIMIT 1) ORDER BY ApprovedTS DESC';
        $this->assertNotequal($expected, rewriteCountQuery($query));
    }
}

/**
 * Helper method - Rewrite the query into a "SELECT COUNT(*)" query.
 * @param string $sql query
 * @return string rewritten query OR false if the query can't be rewritten
 * @access private
 */
function rewriteCountQuery($sql)
{
    if (preg_match('/^\s*SELECT\s+\bDISTINCT\b/is', $sql) || preg_match('/\s+GROUP\s+BY\s+/is', $sql)) {
        return false;
    }
    $queryCount = preg_replace('/(?:.*)\bFROM\b\s+/Uims', 'SELECT COUNT(*) FROM ', $sql, 1);
    list($queryCount, ) = preg_split('/\s+ORDER\s+BY\s+/is', $queryCount);
    list($queryCount, ) = preg_split('/\bLIMIT\b/is', $queryCount);
    return trim($queryCount);
}
?>