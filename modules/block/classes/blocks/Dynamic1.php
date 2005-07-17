<?php
/**
 * RndMsgBlock : Returns a random message, or empty string on failure
 *
 * @package block
 * @author  Michaël Willemot <michael@sotto.be>
 * @version 0.4
 */
class Dynamic1
{
    function Dynamic1()
    {
        $this->dynamic = true;
    }

    function init(&$input)
    {
        //return $this->getBlockContent(&$input);
         return $input->content;
    }

    function getBlockContent(&$input)
    {
       
    }
}
?>