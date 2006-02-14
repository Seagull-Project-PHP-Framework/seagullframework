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

require_once SGL_MOD_DIR . '/block/classes/Block.php';

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

        include SGL_DAT_DIR . '/ary.blocksNames.php';
        $this->aBlocksNames = $aBlocksNames;

        $this->pageTitle    = 'Blocks Manager';
        $this->template     = 'blockList.html';
        $this->_aActionsMapping =  array(
            'add'       => array('add'),
            'insert'    => array('insert', 'redirectToDefault'),
            'edit'      => array('edit'),
            'update'    => array('update', 'redirectToDefault'),
            'reorder'   => array('reorder'),
            'delete'    => array('delete', 'redirectToDefault'),
            'list'      => array('list'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // Forward default values
        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->template    = $this->template;
        $input->masterTemplate = $this->masterTemplate;

        //  Retrieve form values
        $input->position    = $req->get('position');
        $input->blockId     = ($req->get('frmBlockId'));
        $input->items       = $req->get('_items');
        $input->block       = (object)$req->get('block');
        $input->aParams     = $req->get('aParams', $allowTags = true);

        // Misc.
        $this->submitted    = $req->get('submitted');
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->aDelete     = $req->get('frmDelete');
        $input->totalItems  = $req->get('totalItems');
        $input->isAdd       = $req->get('isadd');
        $input->mode        = $req->get('mode');

        // Retrieve sorting keys
        $input->sortBy      = $this->getSortBy($req->get('frmSortBy') );
        $input->sortOrder   = strtolower($this->getSortOrder($req->get('frmSortOrder')));
        // This will tell HTML_Flexy which key is used to sort data
        $input->{ 'sort_' . $input->sortBy } = true;

        // validate on submit
        if ($this->submitted && $input->action != 'reorder' ) {

            // validate input data
            if (empty($input->block->name)) {
                $aErrors['name'] = 'Please select a class name';
            }
            if (empty($input->block->title)) {
                $aErrors['title'] = 'Please fill in a title';
            }
            if (empty($input->block->sections)) {
                $aErrors['sections'] = 'Please select a section(s)';
            }
            if (empty($input->block->roles)) {
                $aErrors['roles'] = 'Please select a role(s)';
            }
            if (isset($aErrors) && count($aErrors)) {
                SGL::raiseMsg('Please fill in the indicated fields');
                $input->error    = $aErrors;
                $this->validated = false;
            }
        } elseif (!empty($input->block->edit) && !$this->submitted) {
            $this->validated = false;
            unset($input->aParams);
        }

        //  if not validated go to edit
        if (!$this->validated) {
            $input->template = 'blockEdit.html';
            $this->_editDisplay($input);
        }
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->aBlocksNames = $this->aBlocksNames;
    }

    function _cmd_add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->mode      = 'New block';
        $output->template  = 'blockEdit.html';
        $output->isAdd     = true;
        $this->_editDisplay($output);
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->mode      = 'Edit block';
        $output->template  = 'blockEdit.html';

        //  get block data
        $block         = & new Block();
        $block->get($input->blockId);
        $data          = $block->toArray('%s');
        $output->block = (object)$data;

        $this->_editDisplay($output);
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $oBlock             = $input->block;
        $oBlock->is_enabled = (isset($oBlock->is_enabled)) ? 1 : 0;
        $oBlock->is_cached  = (isset($oBlock->is_cached)) ? 1 : 0;
        $oBlock->params     = serialize($output->aParams);
        $block              = & new Block();

        // Update record in DB
        $block->get($oBlock->block_id);
        $block->setFrom($oBlock);
        $block->update(false, true);

        // clear cache so a new cache file is built reflecting changes
        SGL_Cache::clear('blocks');
        SGL::raiseMsg('Block details successfully updated', true, SGL_MESSAGE_INFO);
        SGL_HTTP::redirect();
    }

    function _cmd_insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $oBlock             = $input->block;
        $oBlock->is_enabled = (isset($oBlock->is_enabled)) ? 1 : 0;
        $oBlock->is_cached  = (isset($oBlock->is_cached)) ? 1 : 0;
        $oBlock->params     = serialize($output->aParams);
        $block              = & new Block();

        //  insert block record
        $block->setFrom($oBlock);
        $block->insert();

        //  clear cache so a new cache file is built reflecting changes
        SGL_Cache::clear('blocks');

        //  Redirect on success
        SGL::raiseMsg('Block successfully added', true, SGL_MESSAGE_INFO);
        SGL_HTTP::redirect();
    }

    function _cmd_delete(&$input, &$output)
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
        SGL_Cache::clear('blocks');
        SGL::raiseMsg('The selected block(s) have successfully been deleted', true, SGL_MESSAGE_INFO);
    }

    function _cmd_reorder(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $blocks = & new Block();
        if ($this->submitted) {

            $orderArray = explode(',', $input->items);
            $blocks->updateBlocksOrder($orderArray);

            //  clear cache so a new cache file is built reflecting changes
            SGL_Cache::clear('blocks');

            //  Redirect on success
            SGL::raiseMsg('Block details successfully updated', true, SGL_MESSAGE_INFO);
            SGL_HTTP::redirect();
        } else {
            $output->mode       = 'Reorder blocks';
            $output->template   = 'blockReorder.html';
            $output->aBlocks    = $blocks->loadBlocks($input->position);
            $output->blocksName = $input->position;
        }
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template    = 'blockList.html';
        $output->mode        = 'Browse';
        $secondarySortClause = $this->conf['BlockMgr']['secondarySortClause'];

        $query = "  SELECT
                        b.block_id, b.name, b.title, b.title_class,
                        b.body_class, b.blk_order, b.position, b.is_enabled,
                        ba.section_id as sections, s.title as section_title,
                        trans_id
                    FROM {$this->conf['table']['block']} b
                    LEFT JOIN {$this->conf['table']['block_assignment']} ba
                    ON ba.block_id=b.block_id
                    LEFT JOIN {$this->conf['table']['section']} s
                    ON s.section_id=ba.section_id
                    GROUP BY
                        b.block_id, b.name, b.title, b.title_class, b.body_class,
                        b.blk_order, b.position, b.is_enabled, sections, section_title,
                        trans_id
                    ORDER BY " .
                    $input->sortBy . ' ' . $input->sortOrder . $secondarySortClause;

        $limit = $_SESSION['aPrefs']['resPerPage'];
        if ($this->conf['site']['adminGuiEnabled']) {
            $pagerOptions = array(
                'mode'     => 'Sliding',
                'delta'    => 3,
                'perPage'  => $limit,
                'spacesBeforeSeparator' => 0,
                'spacesAfterSeparator'  => 0,
                'curPageSpanPre'        => '<span class="currentPage">',
                'curPageSpanPost'       => '</span>',
            );
        } else {
            $pagerOptions = array(
                'mode'     => 'Sliding',
                'delta'    => 3,
                'perPage'  => $limit,
            );
        }

        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);
        $this->_rebuildPagedData($aPagedData);

        $output->aPagedData = $aPagedData;
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }

        if ($this->conf['site']['adminGuiEnabled']) {
                $output->addOnLoadEvent("switchRowColorOnHover()");
        }
    }

    function _editDisplay(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->aAllBlocks     = SGL_Util::getAllBlocks();
        $output->blockIsEnabled = empty($output->block->is_enabled) ? '' : 'checked';
        $output->blockIsCached  = empty($output->block->is_cached) ? '' : 'checked';

        //  check class existing
        if (!empty($output->block->name)) {
            $blockClass = $output->block->name;
            require_once SGL_BLK_DIR . '/' . $blockClass . '.php';
            if (class_exists($blockClass)) {
                $output->checked = true;

                //  load block params
                $block = & new Block();
                $block->loadBlockParams($output, $blockClass, $output->blockId);
            }
        }
        //  get section list
        $sectionList = DB_DataObject::factory($this->conf['table']['section']);
        $sectionList->orderBy('left_id');
        $result = $sectionList->find();
        if ($result > 0) {
            while ( $sectionList->fetch() ) {
                $title = '';
                if (!empty($sectionList->trans_id) && $this->conf['translation']['container']=='db') {
                    if (!$title = $this->trans->get($sectionList->trans_id,'nav', SGL_Translation::getLangID())) {
                        $title = $this->trans->get($sectionList->trans_id,'nav', SGL_Translation::getFallbackLangID());
                    }
                }
                if ($title) {
                    $sections[$sectionList->section_id] = $title;
                } else {
                    $sections[$sectionList->section_id] = $sectionList->title;
                }
                $sections[$sectionList->section_id] = $this->_addSpaces($sectionList->level_id) .
                    $sections[ $sectionList->section_id ];
            }
        }
        $sections[0] = SGL_String::translate('All sections');
        $output->aSections = $sections;

        //  get roles list
        $query = "SELECT role_id, name FROM {$this->conf['table']['role']}";
        $res = & $this->dbh->getAll($query);
        $roles = array();
        $roles[SGL_ANY_ROLE] = SGL_String::translate('All roles');
        foreach ($res as $key => $value) {
            $roles[$value->role_id] = $value->name;
        }
        $output->aRoles = $roles;
    }

    function _addSpaces($order) {
        $s = '';
        for ($i = 1; $i < $order; $i++) {
            $s .= '&nbsp;&nbsp;';
        }
        return $s;
    }

    function _rebuildPagedData(&$aPagedData)
    {
        if (count($aPagedData['data'])) {

            //  rebuild $aPagedData['data']
            foreach ($aPagedData['data'] as $k => $aValue) {
                $title = '';
                if (isset($pKey) && isset($pBlock)) {
                    if ($pBlock == $aValue['block_id']) {
                        if ($aValue['trans_id'] != 0 && $this->conf['translation']['container'] == 'db') {
                            if (!$title = $this->trans->get($aValue['trans_id'], 'nav', SGL_Translation::getLangID())) {
                                $title = $this->trans->get($aValue['trans_id'], 'nav', SGL_Translation::getFallbackLangID());
                            }
                        }
                        if ($title) {
                            $data[$pKey]['sections'][$aValue['sections']] = $title;
                        } else {
                            $data[$pKey]['sections'][$aValue['sections']] = $aValue['section_title'];
                        }
                    } else {
                        $data[$k] = $aValue;
                        if ($aValue['sections']) {
                            unset ($data[$k]['sections']);
                            if (isset($aValue['trans_id']) && ($aValue['trans_id'] != 0)
                                && $this->conf['translation']['container'] == 'db') {
                                if (!$title = $this->trans->get($aValue['trans_id'], 'nav', SGL_Translation::getLangID())) {
                                    $title = $this->trans->get($aValue['trans_id'], 'nav', SGL_Translation::getFallbackLangID());
                                }
                            }
                           if ($title) {
                                $data[$k]['sections'][$aValue['sections']] = $title;
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
                        if (isset($aValue['trans_id']) && ($aValue['trans_id'] != 0)
                            && $this->conf['translation']['container'] == 'db') {
                            if (!$title = $this->trans->get($aValue['trans_id'], 'nav', SGL_Translation::getLangID())) {
                                $title = $this->trans->get($aValue['trans_id'], 'nav', SGL_Translation::getFallbackLangID());
                            }
                        }
                        if ($title) {
                            $data[$k]['sections'][$aValue['sections']] = $title;
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
            $sessSortBy = SGL_Session::get('sortByBlk');
            if (empty($sessSortBy)) {
                $sortBy = $this->conf['BlockMgr']['defaultSortBy'];
            } else {
                $sortBy = $sessSortBy;
            }
        } else {
            $sortBy = $frmSortBy;
        }
        //  update session
        SGL_Session::set('sortByBlk', $sortBy);
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
            $sessSortOrder = SGL_Session::get('sortOrderBlk');
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
        SGL_Session::set('sortOrderBlk', $sortOrder);
        return $sortOrder;
    }
}
?>
