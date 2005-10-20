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
// | BlockMgr.php                                                              |
// +---------------------------------------------------------------------------+
// | Author: Gilles Laborderie <gillesl@users.sourceforge.net>                 |
// +---------------------------------------------------------------------------+
// $Id: BlockMgr.php,v 1.36 2005/05/29 00:14:37 demian Exp $

require_once SGL_MOD_DIR . '/block/classes/BlockForm.php';
require_once SGL_MOD_DIR . '/block/classes/BlockFormDynamic.php';
require_once SGL_MOD_DIR . '/block/classes/Block.php';
require_once SGL_ENT_DIR . '/Block_assignment.php';

/**
 * To administer blocks.
 *
 * @package block
 * @author  Gilles Laborderie <gillesl@users.sourceforge.net>
 * @version $Revision: 1.36 $
 * @since   PHP 4.1
 */
class BlockMgr extends SGL_Manager
{
    function BlockMgr() 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();
        
        $this->module       = 'block';
        $this->pageTitle    = 'Blocks Manager';
        $this->template     = 'blockList.html';

        $this->_aActionsMapping =  array(
            'addDynamic' => array('addDynamic'),
            'add'       => array('add'), 
            'edit'      => array('edit'), 
            'reorder'   => array('reorder'), 
            'delete'    => array('delete', 'redirectToDefault'), 
            'list'      => array('list'), 
        );
    }

    function validate($req, &$input) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // Forward default values
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template    = $this->template;

        //  Retrieve form values
        $input->block_id    = $req->get('frmBlockId');
        $input->leftItems   = $req->get('_leftItems');
        $input->rightItems  = $req->get('_rightItems');
        $input->block       = $req->get('block');
        $input->form        = '';

        // Retrieve sorting keys
        $input->sortBy      = $this->getSortBy($req->get('frmSortBy') );
        $input->sortOrder   = strtolower($this->getSortOrder($req->get('frmSortOrder')));
        // This will tell HTML_Flexy which key is used to sort data
        $input->{ 'sort_' . $input->sortBy } = true;

        // Misc.
        $this->validated    = true;
        $this->submitted    = $req->get('submitted');
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->aDelete     = $req->get('frmDelete');
        $input->from        = ($req->get('frmFrom')) ? $req->get('frmFrom'):0;
        $input->totalItems  = $req->get('totalItems');
    }

    
    function _addDynamic(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $output->template = 'blockFormdynamic.html';
        $output->mode = 'New block';
        $output->wysiwyg = true;
        
        //  override autonaming for textarea element so 'block' hash can be preserved
        $output->wysiwygElementName = 'block[content]';
        
        // Build form
        $myForm = & new BlockFormDynamic('addDynamic');
        $output->form = $myForm->init();
               
        // If form has been submitted, validate it
        if ($this->submitted) {
            if ($output->form->validate()) {
                $oBlock = (object)$output->form->getSubmitValue('block');
                $block = & new Block();
                $block->setFrom($oBlock);

                // Find next available blk_order for targetted column
                $dbh = $block->getDatabaseConnection();

                $dbh->autocommit();
                $query = "SELECT MAX(blk_order) FROM {$this->conf['table']['block']} WHERE is_onleft = " . $oBlock->is_onleft;
                $next_order = (int)$dbh->getOne($query) + 1;
                $block->blk_order = $next_order;
                $block->insert(); // This takes into account block assignments as well
                $dbh->commit();

                //  clear cache so a new cache file is built reflecting changes
                SGL::clearCache('blocks');
                SGL::raiseMsg('Block successfully added');
                SGL_HTTP::redirect(array());
            }
        }
    }

    function _add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $output->template = 'blockForm.html';
        $output->mode = 'New block';

        // Build form
        $myForm = & new BlockForm('add');
        $output->form = $myForm->init();

        // If form has been submitted, validate it
        if ($this->submitted) {
            if ($output->form->validate()) {
                $oBlock = (object)$output->form->getSubmitValue('block');
                $block = & new Block();
                $block->setFrom($oBlock);

                // Find next available blk_order for targetted column
                $dbh = $block->getDatabaseConnection();

                $dbh->autocommit();
                $query = "SELECT MAX( blk_order ) FROM {$this->conf['table']['block']} WHERE is_onleft = " . $oBlock->is_onleft;
                $next_order = (int)$dbh->getOne($query) + 1;
                $block->blk_order = $next_order;
                // Insert record
                $block->insert(); // This takes into account block assignments as well
                $dbh->commit();

                //  clear cache so a new cache file is built reflecting changes
                SGL::clearCache('blocks');

                //  Redirect on success
                SGL::raiseMsg('Block successfully added');
                SGL_HTTP::redirect(array());
            }
        }
    }

    /**
     * Returns true if 'content' field has a string length greater than
     * zero or it is not NULL.
     *
     * @param integer $blockId
     * @return boolean
     */
    function isHtmlBlock($blockId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "
            SELECT content FROM {$this->conf['table']['block']}
            WHERE block_id = " . $blockId;
        $res = $this->dbh->getOne($query);
        if (!strlen($res) || $res == 'NULL') {
            return false;   
        } else {
            return true;   
        }
    }
    
    function _edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        //  determine block type
        if ($this->isHtmlBlock($input->block_id)) {
            $output->template = 'blockFormdynamic.html';
            $output->wysiwyg = true;
            
            //  override autonaming for textarea element so 'block' hash can be preserved
            $output->wysiwygElementName = 'block[content]';
            $blockForm = & new BlockFormDynamic('edit');            
        } else {
            $output->template = 'blockForm.html';
            $blockForm = & new BlockForm('edit');    
        }
        
        $output->mode = 'Edit block';

        //  get block data
        $block = & new Block();
        
        if ($this->submitted) {
            $block->get($input->block['block_id']);
        } else {
            $block->get($input->block_id);
        }

        $output->form = $blockForm->init( $block->toArray('block[%s]') );
        if ($output->form->validate()) {
            $oBlock = (object)$output->form->getSubmitValue('block');
            $oBlock->is_enabled = (isset($oBlock->is_enabled)) ? 1 : 0;
            $block->setFrom($oBlock);
            // Update record in DB
            $block->update(false, true); // This takes into account block assignments as well

            //clear cache so a new cache file is built reflecting changes
            SGL::clearCache('blocks');
            SGL::raiseMsg('Block details successfully updated');
            SGL_HTTP::redirect(array());
        }
    }

    function _delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (is_array($input->aDelete)) {
            foreach ($input->aDelete as $index => $blockId) {
                $block = & new Block();                
                $block->get($blockId);
                // This takes into account block assignments as well
                $block->delete();
                unset($block);
            }
        } else {
            SGL::raiseError( 'Incorrect parameter passed to ' .
                __CLASS__ . '::' . __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        //clear cache so a new cache file is built reflecting changes
        SGL::clearCache('blocks');

        SGL::raiseMsg('The selected block(s) have successfully been deleted');
    }

    function _reorder(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->mode = 'Reorder blocks';
        $output->template = 'blockReorder.html';
        if ($this->submitted) {
            $this->_reorderUpdate($input->leftItems);
            $this->_reorderUpdate($input->rightItems);
            //clear cache so a new cache file is built reflecting changes
            SGL::clearCache('blocks');

            //  Redirect on success
            SGL::raiseMsg('Block details successfully updated');
            SGL_HTTP::redirect(array());
        } else {
            $leftBlocks = & new Block();
            $leftBlocks->whereAdd('is_onleft = 1');
            $leftBlocks->orderBy('blk_order ASC');
            $result = $leftBlocks->find();
            if ($result > 0) {
                $aLeftBlocks = array();
                while ($leftBlocks->fetch()) {
                    $aLeftBlocks[ $leftBlocks->block_id ] = $leftBlocks->title;
                }
            }
            $output->aLeftBlocks = $aLeftBlocks;
            $rightBlocks = & new Block();
            $rightBlocks->whereAdd('is_onleft = 0');
            $rightBlocks->orderBy('blk_order ASC');
            $result = $rightBlocks->find();
            $aRightBlocks = array();
            if ($result > 0) {
                $aRightBlocks = array();
                while ($rightBlocks->fetch()) {
                    $aRightBlocks[$rightBlocks->block_id] = $rightBlocks->title;
                }
            }
            $output->aRightBlocks = $aRightBlocks;
        }
    }

    function _reorderUpdate($orderList)
    {
        $orderArray = explode(',', $orderList);
        //  Reorder blocks
        $pos = 1;
        $block = & new Block();
        $dbh = $block->getDatabaseConnection();
        $dbh->autocommit();
        foreach ($orderArray as $blockId) {
            $block->get($blockId);
            $block->blk_order =  $pos;
            $success = $block->update();
            unset($block);
            $block = & new Block();
            $pos++;
        }
        $dbh->commit();
    }

    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $output->mode = 'Browse';
        $output->template = 'blockList.html';
        $secondarySortClause = $this->conf['BlockMgr']['secondarySortClause'];
        $query = "  SELECT
                        b.block_id, b.name, b.title, b.title_class, 
                        b.body_class, b.blk_order, b.is_onleft, b.is_enabled, 
                        ba.section_id as sections, s.title as section_title
                    FROM {$this->conf['table']['block']} b, {$this->conf['table']['block_assignment']} ba, {$this->conf['table']['section']} s 
                    WHERE ba.block_id=b.block_id
                    AND s.section_id=ba.section_id 
                    ORDER BY " .
                    $input->sortBy . ' ' . $input->sortOrder . $secondarySortClause;

        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'      => 'Sliding',
            'delta'     => 3,
            'perPage'   => $limit,
            'totalItems'=> $input->totalItems,
        );

        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);
        $this->_rebuildPagedData($aPagedData);
               
        $output->aPagedData = $aPagedData;
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  format form output if any
        if ($output->form) {
            $output->form = $output->form->toHtml();
        }
    }
    
    function _rebuildPagedData(&$aPagedData)
    {
        //  rebuild $aPagedData['data']               
        foreach ($aPagedData['data'] as $k => $aValue) {
            if (isset($pKey) && isset($pBlock)) {
                if ($pBlock == $aValue['block_id']) {
                    if (is_numeric($aValue['section_title']) && $aValue['section_title'] != 0) {
                        $trans = & SGL_Translation::singleton();
                        $trans->setLang(SGL_Translation::getLangID());
                        $data[$pKey]['sections'][$aValue['sections']] = $trans->get($aValue['section_title'], 'nav');   
                    } else {
                        $data[$pKey]['sections'][$aValue['sections']] = $aValue['section_title'];                        
                    }                                                   
                } else {
                    $data[$k] = $aValue;
                    if ($aValue['sections']) {
                        unset ($data[$k]['sections']);
                        if (is_numeric($aValue['section_title']) && $aValue['section_title'] != 0) {
                            $trans = & SGL_Translation::singleton();
                            $trans->setLang(SGL_Translation::getLangID());
                            $data[$k]['sections'][$aValue['sections']] = $trans->get($aValue['section_title'], 'nav');   
                        } else {
                            $data[$k]['sections'][$aValue['sections']] = $aValue['section_title'];                        
                        }                                                   
                        $pKey = $k;               
                    } elseif ($aValue['sections'] == 0 ) {
                        unset($data[$k]['sections']);
                        $data[$k]['sections'][$aValue['sections']] = 'All Sections';
                        $pKey = $k;
                    }
                }    
                $pBlock = $aValue['block_id'];
            } else {
                $data[$k] = $aValue;
                if ($aValue['sections']) {
                    unset ($data[$k]['sections']);
                    if (is_numeric($aValue['section_title']) && $aValue['section_title'] != 0) {
                        $trans = & SGL_Translation::singleton();
                        $trans->setLang(SGL_Translation::getLangID());
                        $data[$k]['sections'][$aValue['sections']] = $trans->get($aValue['section_title'], 'nav',);
                    } else {
                        $data[$k]['sections'][$aValue['sections']] = $aValue['section_title'];                        
                    }                                                   
                    $pKey = $k;               
                } elseif ($aValue['sections'] == 0 ) {
                    unset($data[$k]['sections']);
                    $data[$k]['sections'][$aValue['sections']] = 'All Sections';
                    $pKey = $k;
                }
                $pBlock = $aValue['block_id'];
                $pKey = $k;                           
            }
        }
        unset($aPagedData['data']);

        //  reindex
        $aReindexedData = array();
        foreach ($data as $kk => $vv) {
            $aReindexedData[$vv['block_id']] = $vv;
        }
        $aPagedData['data'] = $aReindexedData;
    }
    
    /**
     * Determines which column results should be sorted by.
     *
     * If no value passed from Request, returns last value
     * from session
     *
     * @access  public
     * @param   string  $frmSortBy      column name passed from Request
     * @param   int     $callingPage    table relevant to sortby
     * @return  string  $sortBy         value to sort by
     */
    function getSortBy($frmSortBy)
    {
        // Look for non-empty value :
        // 1- using request
        // 2- using session
        // 3- using default
        if (empty($frmSortBy)) {
            $sessSortBy = SGL_HTTP_Session::get('sortByBlk');
            if (empty($sessSortBy)) {
                $sortBy = $this->conf['BlockMgr']['defaultSortBy'];
            } else {
                $sortBy = $sessSortBy;
            }
        } else {
            $sortBy = $frmSortBy;
        }
        //  update session
        SGL_HTTP_Session::set('sortByBlk', $sortBy);
        return $sortBy;
    }

    /**
     * Used by list pages to determine last sort order.
     *
     * If no value passed from Request, returns last value
     * from session
     *
     * @access  public
     * @param   string  $frmSortBy      column name passed from Request
     * @param   int     $callingPage    table relevant to sortby
     * @return  string  $sortBy         value to sort by
     */
    function getSortOrder($frmSortOrder)
    {
        // Look for non-empty value :
        // 1- using request
        // 2- using session
        // 3- using default
        if (empty($frmSortOrder)) {
            $sessSortOrder = SGL_HTTP_Session::get('sortOrderBlk');
            if (empty($sessSortOrder)) {
                $sortOrder = $this->conf['BlockMgr']['defaultSortOrder'];
            } else {
                $sortOrder = $sessSortOrder;
            }
        } else {
            if (strtoupper($frmSortOrder) == 'ASC' ) {
                $sortOrder = 'DESC';
            } else {
                $sortOrder = 'ASC';
            }
        }
        //  update session
        SGL_HTTP_Session::set('sortOrderBlk', $sortOrder);
        return $sortOrder;
    }
}
?>
