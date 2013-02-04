<?php
require_once SGL_MOD_DIR  . '/block/classes/Block.php';

/**
 * Data access methods for the default module.
 *
 * @package Default
 * @author  Demian Turner <demian@phpkitchen.com>
 * @copyright Demian Turner 2005
 */
class BlockDAO extends SGL_Manager
{
    /**
     * Constructor - set default resources.
     *
     * @return BlockDAO
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * @access  public
     * @static
     * @return  BlockDAO reference to BlockDAO object
     */
    function singleton()
    {
        static $instance;

        // If the instance is not there, create one
        if (!isset($instance)) {
            $class = __CLASS__;
            $instance = new $class();
        }
        return $instance;
    }

    function addBlock($oBlock)
    {
        $block = new Block();
        //  insert block record
        $block->setFrom($oBlock);
        $ok = $block->insert();

        //  clear cache so a new cache file is built reflecting changes
        SGL_Cache::clear('blocks');
        return $ok;
    }
}
?>