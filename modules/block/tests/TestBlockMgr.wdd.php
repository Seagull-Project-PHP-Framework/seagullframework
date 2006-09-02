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
}
?>
