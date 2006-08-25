<?php
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Demian Turner                                         |
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
// | DocumentorMgr.php                                                         |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: DocumentorMgr.php,v 1.12 2005/01/03 10:49:47 demian Exp $

require_once SGL_CORE_DIR . '/Item.php';
require_once SGL_MOD_DIR . '/navigation/classes/MenuBuilder.php';

/**
 * Creates simple list of anchors and relevant content paragraphs.
 *
 * @package documentor
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.12 $
 * @access  public
 * @since   PHP 4.1
 * @see     ArticleMgr()
 */
class DocumentorMgr extends SGL_Manager
{
    function DocumentorMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle        = 'Manual Generator';
        $this->template         = 'manual.html';
        $this->_aActionsMapping =  array(
            'list'   => array('list'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated       = true;
        $input->error          = array();
        $input->action         = ($req->get('action')) ? $req->get('action') : 'list';
        $input->pageTitle      = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template       = $this->template;
    }

    function _cmd_list(&$input, &$output)
    {
        $output->template = 'manual.html';
        //  build TOC
        $table = 'category';
        $menu = & new MenuBuilder('AnchorTOC', $table);
        $output->bulletList = $menu->toHtml();
        $output->lastUpdated = date("l dS of F Y h:i:s A");

        //  grab article with template type from session preselected
        $aResult = $this->retrieveAll($dataTypeID = 1, $queryRange = 'all');
        $this->add($aResult);
        $output->articles = $this->aElements;

        for ($x = 0; $x < count($output->articles); $x++) {
            $item = & new SGL_Item(array('itemID' => $output->articles[$x]->item_id));
            $preview = $item->manualPreview();
            $link = str_replace(' ', '_', $preview['title']);
            $title = $preview['title'];
            $preview['title'] = "<a name='$link'><h2>$title</h2></a>";
            $preview['bodyHtml'] = $preview['bodyHtml'] . '<hr>';
            $output->preview[] = (object)$preview;
            unset($item);
        }
    }
    
    /**
     * Add article objects to elements array for counting.
     *
     * @access  public
     * @param   mixed   $mElement
     * @return  void
     */
    function add($mElement)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->aElements = $mElement;
    }
   /**
     * Gets full list of articles, for Documentor.
     *
     * @access  public
     * @param   int     $dataTypeID template ID of article, ie, new article, weather article, etc.
     * @param   string  $queryRange flag to indicate if results limited to specific category
     * @return  array   $aResult        returns array of article objects, pager data, and show page flag
     * @see     retrievePaginated()
     */
    function retrieveAll($dataTypeID, $queryRange)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  if user only wants contents from current category, add where clause
        $rangeWhereClause   = ($queryRange == 'all') ?'' : "AND i.category_id = $catID";
        $typeWhereClause    = ($dataTypeID == 1) ?'' : "AND  it.item_type_id  = '$dataTypeID'";
        $query = "
            SELECT  i.item_id,
                    ia.addition,
                    u.username,
                    i.date_created,
                    i.start_date,
                    i.expiry_date
            FROM    {$this->conf['table']['item']} i, {$this->conf['table']['item_addition']} ia,
                    {$this->conf['table']['item_type']} it, {$this->conf['table']['item_type_mapping']} itm, {$this->conf['table']['user']} u

            WHERE   ia.item_type_mapping_id = itm.item_type_mapping_id
            AND     i.updated_by_id = u.usr_id
            AND     it.item_type_id  = itm.item_type_id
            AND     i.item_id = ia.item_id
            AND     i.item_type_id = it.item_type_id
            AND     itm.field_name = 'title'                /* match item addition type, 'title'    */ " .
            $typeWhereClause . "                            /* match datatype */
            AND     i.status  > " . SGL_STATUS_DELETED . "  /* don't list items marked as deleted */ " .
            $rangeWhereClause . "
            ORDER BY i.date_created DESC
                ";
        $aArticles = $this->dbh->getAll($query);
        return $aArticles;
    }
}
?>