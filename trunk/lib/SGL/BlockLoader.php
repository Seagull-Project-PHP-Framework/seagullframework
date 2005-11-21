<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | o Redistributions of source code must retain the above copyright          |
// |   notice, this list of conditions and the following disclaimer.           |
// | o Redistributions in binary form must reproduce the above copyright       |
// |   notice, this list of conditions and the following disclaimer in the     |
// |   documentation and/or other materials provided with the distribution.    |
// | o The names of the authors may not be used to endorse or promote          |
// |   products derived from this software without specific prior written      |
// |   permission.                                                             |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT         |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR     |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT      |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,     |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT          |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,     |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE     |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.      |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.5                                                               |
// +---------------------------------------------------------------------------+
// | BlockLoader.php                                                           |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: BlockLoader.php,v 1.7 2005/05/16 23:55:23 demian Exp $

/**
 * BlockLoader manages units of content that can be dynamically positioned in a 
 * page's left or right columns.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.7 $
 * @access  public
 */
class SGL_BlockLoader
{
    /**
     * Temporary container for processing blocks.
     *
     * @access  private
     * @var     array
     */
    var $_aData = array();

    /**
     * Array of processed blocks
     *
     * Left/right blocks stored as $this->aBlocks['left'],
     * $this->aBlocks['right'].
     *
     * @access  public
     * @var     array
     */
    var $aBlocks = array();

    /**
     * $_GET param used to determine uniqueness of pages.
     *
     * @access  private
     * @var     int
     */
    var $_staticId = 0;

    /**
     * The role id, used so blocks can be cached per role.
     *
     * @access  private
     * @var     int
     */
    var $_rid = 0;

    /**
     * SectionId is currently roughtly equivalent to page id.
     *
     * @access  private
     * @var     int
     * @todo    change to pageId, also rename section table
     */
    var $_currentSectionId = 0;

    /**
     * Constructor - sets the sectionId.
     *
     * @access  public
     * @return  void
     */
    function SGL_BlockLoader($sectionId)
    {
        $this->_rid = (int)SGL_HTTP_Session::get('rid');
        $this->_staticId = (isset($_GET['staticId'])) ? $_GET['staticId'] : 0;
        if (isset($sectionId)) {
            $this->_currentSectionId = $sectionId;
        }
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();
    }

    /**
     * Initialises Block Mgr, loads blocks into temporary array
     * $_aData for later processing.
     *
     * @author  Andy Crain <apcrain@fuse.net>>
     * @access  private
     * @return  array   array of block objects
     */
    function render($output)
    {
        //  put data generated so far into class scope
        $this->output = $output;
        $cache = & SGL::cacheSingleton();
        $cacheId = basename($_SERVER['PHP_SELF']) . $this->_rid . $this->_staticId;
        if ($data = $cache->get($cacheId, 'blocks')) {
            $this->aBlocks = unserialize($data);            
            SGL::logMessage('blocks from cache', PEAR_LOG_DEBUG);
        } else {
            $this->_loadBlocks();
            $data = serialize($this->aBlocks);
            $cache->save($data, $cacheId, 'blocks');
            SGL::logMessage('blocks from db', PEAR_LOG_DEBUG);
        }
        return $this->aBlocks;
    }

    /**
     * Loads blocks from DB.
     *
     * @access  private
     * @return  void
     */
    function _loadBlocks()
    {
        $dbh = & SGL_DB::singleton();
        $query = "
            SELECT
                b.block_id, b.name, b.title, b.title_class, 
                b.body_class, b.is_onleft
            FROM    {$this->conf['table']['block']} b, {$this->conf['table']['block_assignment']} ba,
                    {$this->conf['table']['block_role']} br
            WHERE   b.is_enabled = 1
            AND     (br.block_id = b.block_id AND 
                      (br.role_id = '" . SGL_HTTP_Session::getRoleId() . "' OR br.role_id = '" . SGL_ANY_ROLE . "')
                    )   
            AND     b.block_id = ba.block_id
            AND     ( ba.section_id = ".SGL_ANY_SECTION." OR ba.section_id = " . $this->_currentSectionId . ' )
            ORDER BY b.blk_order
        ';

        $aResult = $dbh->getAll($query);

        if (!DB::isError($aResult)) {
            $this->_aData = $aResult;

            //  render content from each class
            $this->_buildBlocks();
        } else {
            SGL::raiseError('section ID not found', SGL_ERROR_NODATA);
        }
    }

    /**
     * With block structures in place, block contents are built.
     *
     * Each block is a class in the modules/block/classes/blocks directory, 
     * containing static HTML or dynamic content
     *
     * @access  private
     * @return  void
     */
    function _buildBlocks()
    {
        //  render content
        if (count($this->_aData) > 0 ) {
            foreach ($this->_aData as $index => $oBlock) {
                $blockClass = $oBlock->name;
                $blockPath = SGL_BLK_DIR . '/' . $blockClass . '.php';
                @include_once $blockPath;
                if (!class_exists($blockClass)) {
                    unset($this->_aData[$index]);
                    SGL::raiseError($blockClass . ' is not a valid block classname', 
                        SGL_ERROR_NOCLASS);
                } else {
                    @$obj = & new $blockClass();
                    $this->_aData[$index]->content = $obj->init($this->output, $oBlock->block_id);
                }
            }
            $this->_sort();
        }
    }

    /**
     * Sorts tmp array $_aData into left/right blocks.
     *
     * easier to manage in Controller
     *
     * @access  private
     * @return  void
     */
    function _sort()
    {
        //  sort into left/right
        if (count($this->_aData) > 0) {
            foreach ($this->_aData as $oBlock) {
                if ($oBlock->is_onleft) {
                    $this->aBlocks['left'][] = $oBlock;
                } else {
                    $this->aBlocks['right'][] = $oBlock;
                }
            }
        }
        unset($this->_aData);
    }
}
?>
