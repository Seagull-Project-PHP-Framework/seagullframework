<?php
require_once dirname(__FILE__). '/../classes/BlockMgr.php';

class BlockMgrTest extends UnitTestCase
{
    function BlockMgrTest()
    {
        $this->UnitTestCase('BlockMgr Tests');
    }
    
    function setup()
    {
        $this->blockMgr = new BlockMgr();   
    }
    
    function testIsHtmlBlock()
    {
        // SiteNews block is not html
        $this->assertFalse($this->blockMgr->isHtmlBlock(1));
    }
}
?>
