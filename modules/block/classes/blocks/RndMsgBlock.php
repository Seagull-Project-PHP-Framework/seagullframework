<?php
/**
 * RndMsgBlock : Returns a random message, or empty string on failure
 *
 * @package block
 * @author  Michaël Willemot <michael@sotto.be>
 * @version 0.4
 */
class RndMsgBlock
{
    function SampleBlock1()
    {
    }

    function init()
    {
        return $this->getBlockContent();
    }

    function getBlockContent()
    {
        $dbh = & SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF'];
                
        $sql = "SELECT msg FROM {$conf['table']['rndmsg_message']}";

        // get random number (max=number of messages)
        $tmp = & $dbh->query($sql);
        $from = rand(0,( $tmp->numRows() - 1));

        // get msg (using random number as limit)
        $r = $dbh->getOne($dbh->modifyLimitQuery($sql, $from, 1));
        return (DB::isError($r)) ? '' : $r;
    }
}
?>