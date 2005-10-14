<?php
/**
 * Creates dynamic html blocks.
 *
 * @package block
 * @author  Michael Willemot <michael@sotto.be>
 * @version 0.4
 */
class DbHtmlBlock
{
    function DbHtmlBlock()
    {
        $this->dynamic = true;
    }

    /**
     * Essentially an empty placeholder.
     *
     */
    function init($output, $blockId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        return $this->getBlockContent((int)$blockId);
    }
    
    function getBlockContent($blockId)
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        $dbh = & SGL_DB::singleton();
        
        $query = "
            SELECT content FROM {$conf['table']['block']}
            WHERE block_id = " . $blockId;
        $res = $dbh->getOne($query);
        if (!PEAR::isError($res)) {
            return $res;
        } else {
            PEAR::raiseError('problem selected data for this block', SGL_ERROR_NODATA);
        }
    }
}
?>