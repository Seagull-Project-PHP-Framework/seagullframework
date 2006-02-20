<?php
/**
 * RndMsgBlock : Returns a random message, or empty string on failure
 *
 * @package block
 * @author  Micha�l Willemot <michael@sotto.be>
 * @version 0.4
 */
class Randommsg_Block_Fortune
{
    function init()
    {
        return $this->getBlockContent();
    }

    function getBlockContent()
    {
        $dbh = & SGL_DB::singleton();
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

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