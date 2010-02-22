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
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | BlockMgr.php                                                              |
// +---------------------------------------------------------------------------+
// | Author: Gilles Laborderie <gillesl@users.sourceforge.net>                 |
// +---------------------------------------------------------------------------+
// $Id: BlockMgr.php,v 1.36 2005/05/29 00:14:37 demian Exp $

require_once SGL_MOD_DIR  . '/block/classes/Block.php';
require_once SGL_MOD_DIR  . '/user/classes/UserDAO.php';
require_once SGL_CORE_DIR . '/Delegator.php';

if (SGL::moduleIsEnabled('cms')) {
    require_once SGL_MOD_DIR  . '/cms/classes/NavigationDAO.php';
} else {
    require_once SGL_MOD_DIR  . '/navigation/classes/NavigationDAO.php';
}

/**
 * To administer blocks.
 *
 * @package seagull
 * @subpackage  block
 * @author  Gilles Laborderie <gillesl@users.sourceforge.net>
 */
class BlockMgr extends SGL_Manager
{
    function BlockMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        //  if custom block layout specified (can be loaded in any module's init.php file)
        if (isset($GLOBALS['_SGL']['aBlocksNames'])) {
            $this->aBlocksNames = $GLOBALS['_SGL']['aBlocksNames'];
        } else {
            require_once SGL_DAT_DIR . '/ary.blocksNames.php';
            $this->aBlocksNames = $aBlocksNames;
        }
        $daUser             = &UserDAO::singleton();
        $daNav = (SGL::moduleIsEnabled('cms'))
            ? CmsNavigationDAO::singleton()
            : NavigationDAO::singleton();
        $this->da           = new SGL_Delegator();
        $this->da->add($daUser);
        $this->da->add($daNav);

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
        $input->submitted   = $req->get('submitted');
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
        if ($input->submitted && $input->action != 'reorder' ) {

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
        } elseif (!empty($input->block->edit) && !$input->submitted) {
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
        $output->block->roles    = SGL_ANY_ROLE;
        $output->block->sections = SGL_ANY_SECTION;

        $this->_editDisplay($output);
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
            SGL::raiseMsg('The selected block(s) have successfully been deleted', true, SGL_MESSAGE_INFO);

            //clear cache so a new cache file is built reflecting changes
            SGL_Cache::clear('blocks');
        } else {
            SGL::raiseMsg('There is no block to delete', true, SGL_MESSAGE_ERROR);
        }
    }

    function _cmd_reorder(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $blocks = & new Block();
        if ($input->submitted) {

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

        $query = "
            SELECT
                block_id, name, title, title_class,
                body_class, blk_order, position, is_enabled
            FROM {$this->conf['table']['block']}
            ORDER BY " .
            $input->sortBy . ' ' . $input->sortOrder . $secondarySortClause;

        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'     => 'Sliding',
            'delta'    => 3,
            'perPage'  => $limit,
            'spacesBeforeSeparator' => 0,
            'spacesAfterSeparator'  => 0,
            'curPageSpanPre'        => '<span class="currentPage">',
            'curPageSpanPost'       => '</span>',
        );

        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);

        $transId = ($this->conf['translation']['container'] == 'db')
            ? ', trans_id'
            : '';

        if (SGL::moduleIsEnabled('cms')) {
            $id = 'page_id';
            $table = $this->conf['table']['page'];
        } else {
            $table = $this->conf['table']['section'];
            $id = 'section_id';
        }
        $query = "
            SELECT
                ba.block_id, ba.section_id as sections,
                s.title as section_title $transId
            FROM
                {$this->conf['table']['block_assignment']} ba,
                {$table} s
            WHERE s.$id = ba.section_id";

        $aBlockSections = $this->dbh->getAssoc($query, false, array(), DB_FETCHMODE_ASSOC, true);

        if (PEAR::isError($aBlockSections)) {
            SGL::raiseError($aBlockSections->getMessage());
            return false;
        }

        $this->_rebuildPagedData($aPagedData, $aBlockSections);

        $output->aPagedData = $aPagedData;
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }

        $output->addOnLoadEvent("switchRowColorOnHover()");
    }

    function _editDisplay(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->blockIsEnabled = empty($output->block->is_enabled) ? '' : 'checked';
        $output->blockIsCached  = empty($output->block->is_cached) ? '' : 'checked';

        //  check class existing
        if (!empty($output->block->name)) {
            preg_match('/^(.*)_.*_(.*)$/', $output->block->name, $aMatches);
            @$blockPath = strtolower($aMatches[1]) . '/blocks/' . $aMatches[2];
            $fileName = SGL_MOD_DIR . '/' . $blockPath . '.php';
            if (is_file($fileName)) {
                @require_once $fileName;
                if (class_exists($output->block->name)) {
                    $output->checked = true;

                    //  load block params
                    $block = & new Block();
                    $block->loadBlockParams($output, $blockPath, $output->blockId);
                }
            }
        }

        //  get section list
        $output->aSections[SGL_ANY_SECTION] = SGL_String::translate('All sections');
        $output->aSections = $output->aSections + $this->da->getSectionsForSelect();

        //  build role widget
        $aRoles[SGL_ANY_ROLE] = SGL_String::translate('All roles');
        $aRoles[SGL_GUEST]    = SGL_String::translate('guest');
        $output->aRoles       = $aRoles + $this->da->getRoles();

        //  search blocks from modules
        $aAllBlocks  = array();
        $aModuleDirs = SGL_Util::getAllModuleDirs();
        foreach ($aModuleDirs as $value) {
            $aModuleBlocks = SGL_Util::getAllClassesFromFolder(SGL_MOD_DIR .
                '/' . $value . '/blocks');
            foreach ($aModuleBlocks as $block) {
                $blockClassName = ucfirst($value) . '_Block_' . $block;
                $aAllBlocks[$blockClassName] = $blockClassName;
            }
        }
        $output->aAllBlocks = $aAllBlocks;
    }

    function _rebuildPagedData(&$aPagedData, &$aBlockSections)
    {
        if (count($aPagedData['data'])) {

            $data = array();

            //  rebuild $aPagedData['data']
            foreach ($aPagedData['data'] as $aValue) {
                $title = '';
                if (!empty($aBlockSections[$aValue['block_id']])) {
                    foreach ($aBlockSections[$aValue['block_id']] as $aaValue) {
                        if ($aaValue['sections']) {
                            if (isset($aaValue['trans_id']) && $aaValue['trans_id']
                                    && $this->conf['translation']['container'] == 'db') {
                                if (!$title = $this->trans->get($aaValue['trans_id'],
                                        'nav', SGL_Translation::getLangID())) {
                                    $title = $this->trans->get($aaValue['trans_id'],
                                        'nav', SGL_Translation::getFallbackLangID());
                                }
                            }
                            if ($title) {
                                $aValue['sections'][$aaValue['sections']] = $title;
                            } else {
                                $aValue['sections'][$aaValue['sections']] = $aaValue['section_title'];
                            }
                        } elseif (!$aaValue['sections']) {
                            unset($aValue['sections']);
                            $aValue['sections'][] = SGL_String::translate('All sections');
                            break;
                        }
                    }
                    $data[$aValue['block_id']] = $aValue;
                } else {
                    $aValue['sections'][] = SGL_String::translate('Unassigned');
                    $data[$aValue['block_id']] = $aValue;
                }
                $aPagedData['data'] = $data;
            }
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
