<?php
/**
 * Creates dynamic html blocks.
 *
 * @package block
 * @author  Michael Willemot <michael@sotto.be>
 * @version 0.4
 */
class Dynamic1
{
    function Dynamic1()
    {
        $this->dynamic = true;
    }

    /**
     * Essentially an empty placeholder.
     *
     */
    function init()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        return;
    }
}
?>