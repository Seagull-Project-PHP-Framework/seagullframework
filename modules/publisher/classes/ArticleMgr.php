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
// | ArticleMgr.php                                                            |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: ArticleMgr.php,v 1.52 2005/05/23 23:29:12 demian Exp $

require_once SGL_CORE_DIR . '/Item.php';
require_once SGL_MOD_DIR  . '/publisher/classes/PublisherBase.php';
require_once SGL_MOD_DIR  . '/navigation/classes/MenuBuilder.php';

/**
 * For performing operations on Article objects.
 *
 * @package publisher
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.52 $
 * @since   PHP 4.1
 */
class ArticleMgr extends SGL_Manager
{
    var $isAdmin = false;

    function ArticleMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle = 'Article Manager';
        $this->_aActionsMapping =  array(
            'add'       => array('add'),
            'insert'    => array('insert', 'redirectToDefault'),
            'edit'      => array('edit'),
            'update'    => array('update', 'redirectToDefault'),
            'delete'    => array('delete', 'redirectToDefault'),
            'changeStatus'    => array('changeStatus', 'redirectToDefault'),
            'list'      => array('list'),
            'view'      => array('view'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (SGL_Session::getUserType() == SGL_ADMIN) {
            $this->isAdmin = true;
            $this->template = 'articleManager.html';
        } else {
            $this->template = 'publisher.html';
        }
        $this->validated        = true;
        $input->masterTemplate  = ((SGL_Session::getUserType() == SGL_ADMIN) && !$this->conf['site']['adminGuiEnabled'])
            ? 'masterLeftCol.html'
            : $this->masterTemplate;
        $input->error           = array();
        $input->pageTitle       = $this->pageTitle;
        $input->template        = $this->template;

        //  select appropriate jscalendar lang file depending on prefs defined language
        $lang = SGL::getCurrentLang();
        $jscalendarLangFile = (file_exists(SGL_WEB_ROOT . '/js/jscalendar/lang/calendar-'. $lang . '.js'))
            ? 'jscalendar/lang/calendar-'. $lang . '.js'
            : 'jscalendar/lang/calendar-en.js';
        $input->javascriptSrc   = ($this->conf['site']['adminGuiEnabled'])
            ? array('TreeMenu.js','jscalendar/calendar.js',$jscalendarLangFile,'jscalendar/calendar-setup.js')
            : array('TreeMenu.js');

        //  form vars
        $input->action          = ($req->get('action')) ? $req->get('action') : 'list';
        $input->from            = ($req->get('frmFrom')) ? $req->get('frmFrom'):0;
        $input->catID           = (int)$req->get('frmCatID');
        $input->articleCatID    = (int)$req->get('frmArticleCatID');
        $input->catChangeToID   = (int)$req->get('frmCategoryChangeToID');
        $input->dataTypeID      = $req->get('frmArticleTypeID');
        $input->status          = $req->get('frmStatus');
        $input->articleID       = (int)$req->get('frmArticleID');
        $input->aDelete         = $req->get('frmDelete');
        $input->articleLang     = $req->get('frmArticleLang');
        $input->availableLangs  = $req->get('frmAvailableLangs');

        //  new article form vars
        $input->createdByID     = $req->get('frmCreatedByID');
        $input->aStartDate      = $req->get('frmStartDate');
        $input->aExpiryDate     = $req->get('frmExpiryDate');
        $input->noExpiry        = $req->get('frmExpiryDateNoExpire');
        $input->aDataItemID     = $req->get('frmDataItemID');
        $input->aDataItemType   = ($req->get('frmDataItemType')) ? $req->get('frmDataItemType') : array();
        $input->aDataItemValue  = $req->get('frmFieldName', $allowTags = true);
        $input->queryRange      = $req->get('frmQueryRange');

        //  session var persistence
        PublisherBase::maintainState($input);
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get cat name for reschooser title
        require_once 'DB/DataObject.php';
        $category = DB_DataObject::factory($this->conf['table']['category']);
        $category->get($output->catID);
        $output->catName = $category->label;
        $output->queryRange = PublisherBase::getQueryRange($output);

        //  generate template type options for article type chooser
        //  returns an assoc array: typeID => typeName
        $output->aArticleTypes = $this->getTemplateTypes();
    }

    function _add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template   = 'articleMgrAdd.html';
        $output->pageTitle .= ' :: New Article';

        //  don't show wysiwyg for 'news' articles
        if ($input->dataTypeID != 4) {
            $output->wysiwyg = true;
        }
        $output->todaysDate = SGL_Date::getTime();

        //  initialise input array with current date/time
        $aDate = SGL_Date::stringToArray(mktime());

        if ($this->conf['ArticleMgr']['backDateNumYears'] > 0) {
            $aDate['year'] = $aDate['year'] - $this->conf['ArticleMgr']['backDateNumYears'];
            $years = $this->conf['ArticleMgr']['backDateNumYears'] + 5;
        } else {
            $years = 5;
        }
        $output->dateSelectorStart =
            SGL_Output::showDateSelector($aDate, 'frmStartDate', true, true, $years);

        //  increment year for expiry and set time to midnight
        $aDate = SGL_Date::stringToArray(mktime(0,0,0,date('m'),date('d'),date('Y')+5));

        $expiryDate = mktime(0,0,0,date('m'),date('d'),date('Y')+5);
        $output->expiryDate = strftime("%Y-%m-%d %H:%M:%S", $expiryDate);
        $output->dateSelectorExpiry =
            SGL_Output::showDateSelector($aDate, 'frmExpiryDate');
        if ($this->conf['ArticleMgr']['noExpiry']) $aDate = '';
        $output->noExpiry = SGL_Output::getNoExpiryCheckbox($aDate, 'frmExpiryDate');
        $output->addOnLoadEvent("time_select_reset('frmExpiryDate','false')");

        //  use site language to create all articles
        $item = & new SGL_Item();
        $fieldReturnType = ($this->conf['site']['adminGuiEnabled'])
            ? SGL_RET_ARRAY
            : SGL_RET_STRING;
        $output->articleLang = SGL_Translation::getFallbackLangID();
        $output->dynaFields = $item->getDynamicFields($input->dataTypeID,
            $fieldReturnType, $output->articleLang);

        //  generate breadcrumbs and change category select
        $menu = & new MenuBuilder('SelectBox');
        $htmlOptions = $menu->toHtml();

        //  only display categories if 'html article' type is chosen
        if ($input->dataTypeID == 2) {
            $output->aCategories = $htmlOptions;
            $output->currentCat = $input->catID;
        }
        $output->breadCrumbs = $menu->getBreadCrumbs($input->catID, false);

        $output->addOnLoadEvent("showSelectedOptions('article','articleAddContent')");
    }

    function _insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  if category has been changed, update input
        $input->catID = ($input->catID == $input->catChangeToID)
            ? $input->catID
            : $input->catChangeToID;

        //  check for missing article id
        if (empty($input->catID)) {
            SGL::logMessage('Category ID has been lost, FIXME', PEAR_LOG_NOTICE);
            $input->catID = 1;
        }
        $item = & new SGL_Item();
        $item->set('createdByID', $input->createdByID);
        $item->set('lastUpdatedById', $input->createdByID);
        $item->set('dateCreated', SGL_Date::getTime());
        $item->set('lastUpdated', SGL_Date::getTime());
        if ($this->conf['site']['adminGuiEnabled']) {
            $item->set('startDate', $input->aStartDate);
            $item->set('expiryDate', $input->noExpiry ? NULL : $input->aExpiryDate);
        } else {
            $item->set('startDate', SGL_Date::arrayToString($input->aStartDate));
            $item->set('expiryDate', $input->noExpiry
                ? NULL
                : SGL_Date::arrayToString($input->aExpiryDate));
        }
        $item->set('typeID', $input->dataTypeID);
        $item->set('catID', $input->catID);

        //  addMetaInfo
        $insertID = $item->addMetaItems();

        //  addDataItems
        $item->addDataItems($insertID, $input->aDataItemID, $input->aDataItemValue,
            $input->aDataItemType, $input->articleLang);

        SGL::raiseMsg('Article successfully added', true, SGL_MESSAGE_INFO);
    }

    function _edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'articleMgrEdit.html';

        //  don't show wysiwyg for 'news' articles
        if ($input->dataTypeID != 4) {
            $output->wysiwyg = true;
        }
        $item = & new SGL_Item($input->articleID);

        //  prepare date selectors
        $output->dateSelectorStart =
            SGL_Output::showDateSelector(SGL_Date::stringToArray($item->startDate),
                'frmStartDate');
        $output->itemStartDate = $item->startDate;
        $aExpiryDate = $item->expiryDate
            ? SGL_Date::stringToArray($item->expiryDate)
            : SGL_Date::stringToArray(mktime(0,0,0,date('m'),date('d'),date('Y')+5));
        $output->itemExpiryDate = $item->expiryDate
            ? $item->expiryDate
            : strftime("%Y-%m-%d %H:%M:%S", mktime(0,0,0,date('m'),date('d'),date('Y')+5));
        ;
        $output->dateSelectorExpiry =
            SGL_Output::showDateSelector($aExpiryDate,'frmExpiryDate');
        $output->noExpiry = SGL_Output::getNoExpiryCheckbox(
            SGL_Date::stringToArray($item->expiryDate), 'frmExpiryDate');
        $output->addOnLoadEvent("time_select_reset('frmExpiryDate','false')");

        //  translation support if enabled
        if ($this->conf['translation']['container'] == 'db') {
            $installedLanguages = $this->trans->getLangs();

            $output->availableLangs = $installedLanguages;
            $output->articleLang = (!empty($input->articleLang))
                ? $input->articleLang
                : $this->conf['translation']['fallbackLang'];
        }

        //  get dynamic content
        $fieldReturnType = ($this->conf['site']['adminGuiEnabled'])
            ? SGL_RET_ARRAY
            : SGL_RET_STRING;
        $output->dynaContent = (isset($input->articleLang))
            ? $item->getDynamicContent($input->articleID, $fieldReturnType, $input->articleLang)
            : $item->getDynamicContent($input->articleID, $fieldReturnType,
                @$this->conf['translation']['fallbackLang']);

        //  generate flesch html link
        $output->fleschLink = $this->conf['site']['baseUrl']
            . '/themes/'.$_SESSION['aPrefs']['theme'].'/publisher/flesch.'
            . $_SESSION['aPrefs']['language'] . '.html';

        //  calculate flesch score if enabled
        if ($this->conf['ArticleMgr']['fleschScore']) {

            //  strip tags, parse out raw text
            $search = array ("'<script[^>]*?>.*?</script>'si",  // Strip javascript
                             "'<[\/\!]*?[^<>]*?>'si",           // Strip html tags
                             "'([\r\n])[\s]+'",                 // Strip white space
                             "'\*'si");
            $replace = array (' ', ' ', '\1', '');
            $lines = ($this->conf['site']['adminGuiEnabled'])
                        ? preg_replace($search, $replace, $output->dynaContent)
                        : explode("\n", preg_replace($search, $replace, $output->dynaContent));

            //  body text occurs in 4th element
            if (!isset($lines[4])) {
                $lines[4] = '';
            }
            $rawTxt = strip_tags($lines[4]);

            //  detect if sufficient text to run stats
            //  minimum is one word and a full stop
            $bContainsPeriod = (boolean)preg_match("/\./", $rawTxt);
            $words = explode(' ', $rawTxt);
            if (count($words) && $bContainsPeriod) {
                require_once 'Text/Statistics.php';
                $block = & new Text_Statistics($rawTxt);
                $output->flesch = number_format($block->flesch, 2);
            } else {
                $output->flesch = 'n/a';
            }
        }
        $output->article = $item;

        //  generate breadcrumbs and change category select
        $menu = & new MenuBuilder('SelectBox');
        $htmlOptions = $menu->toHtml();

        //  only display categories if 'html article' type is chosen
        if ($item->typeID == 2) {
            $output->aCategories = $htmlOptions;
            $output->currentCat = $item->catID;
        }
        $output->breadCrumbs = $menu->getBreadCrumbs($item->catID, false);

        $output->addOnLoadEvent("showSelectedOptions('article','articleAddContent')");
    }

    function _update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'articleMgrEdit.html';

        //  if category has been changed, update input
        $input->articleCatID = ($input->articleCatID == $input->catChangeToID)
                            ? $input->articleCatID
                            : $input->catChangeToID;
        if (empty($input->articleCatID)) {
            SGL::logMessage('Category ID has been lost, FIXME', PEAR_LOG_NOTICE);
            $input->articleCatID = 1;
        }
        $item = & new SGL_Item($input->articleID);
        $item->set('lastUpdatedById', $input->createdByID);

        //  only update catID if it's  a dynamic html article
        if ($input->dataTypeID == 2) {
            $item->set('catID', $input->articleCatID);
        }
        $item->set('lastUpdated', SGL_Date::getTime());
        if ($this->conf['site']['adminGuiEnabled']) {
            $item->set('startDate', $input->aStartDate);
            $item->set('expiryDate', $input->noExpiry ? NULL : $input->aExpiryDate);
        } else {
            $item->set('startDate', SGL_Date::arrayToString($input->aStartDate));
            $item->set('expiryDate', $input->noExpiry ? NULL : SGL_Date::arrayToString($input->aExpiryDate));
        }
        $item->set('statusID', SGL_STATUS_FOR_APPROVAL);

        //  updateMetaItems
        $item->updateMetaItems();

        //  updateDataItems
        $item->updateDataItems($input->aDataItemID, $input->aDataItemValue, $input->aDataItemType, $input->articleLang);

        SGL::raiseMsg('Article successfully updated', true, SGL_MESSAGE_INFO);
    }

    function _changeStatus(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $item = & new SGL_Item($input->articleID);
        $item->changeStatus($input->status);
        $output->template = 'articleManager.html';
        SGL::raiseMsg('Article status has been successfully changed', true, SGL_MESSAGE_INFO);
    }

    function _delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $item = & new SGL_Item();
        $item->delete($input->aDelete);

        SGL::raiseMsg('The selected article(s) have successfully been deleted', true, SGL_MESSAGE_INFO);
    }

    function _view(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->masterTemplate = 'masterBlank.html';
        $output->template = 'preview.html';
        $output->leadArticle = SGL_Item::getItemDetail($input->articleID);
    }

    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if ($this->conf['site']['adminGuiEnabled']) {
            $output->masterTemplate = 'masterLeftCol.html';
        }
        //  fetch current language
        $langID = SGL_Translation::getLangID();

        //  grab article with template type from session preselected
        $aResult = $this->retrievePaginated(
            $input->catID,
            $bPublished = false,
            $input->dataTypeID,
            $input->queryRange,
            $input->from,
            $langID);

        //  generate action links for each article
        for ($n = 0; $n < count($aResult['data']); $n++) {
            $aResult['data'][$n]['actionLinks'] =
                $this->_generateActionLinks(
                    $aResult['data'][$n]['item_id'],
                    $aResult['data'][$n]['status']);
        }

        if (is_array($aResult['data']) && count($aResult['data'])) {
            $limit = $_SESSION['aPrefs']['resPerPage'];
            $output->pager = ($aResult['totalItems'] <= $limit) ? false : true;
        }
        $output->aPagedData = $aResult;

        //  prep publisher sub nav
        if ($this->isAdmin) {
            $theme = $_SESSION['aPrefs']['theme'];
            $output->addOnLoadEvent('checkNewButton()');
            if ($this->conf['site']['adminGuiEnabled']) {
                $output->addOnLoadEvent("switchRowColorOnHover()");
            } else {
                $output->addOnLoadEvent("document.getElementById('frmResourceChooser').articles.disabled = true");
            }
            $menu = & new MenuBuilder('SelectBox');
            $output->breadCrumbs = $menu->getBreadCrumbs($input->catID);
        }
    }

    /**
     * Gets paginated list of articles.
     *
     * @access  public
     * @param   int     $dataTypeID template ID of article, ie, new article, weather article, etc.
     * @param   string  $queryRange flag to indicate if results limited to specific category
     * @param   int     $catID      optional cat ID to limit results to
     * @param   int     $from       row ID offset for pagination
     * @return  array   $aResult    returns array of article objects, pager data, and show page flag
     * @see     retrieveAll()
     */
    function retrievePaginated($catID, $bPublished = false, $dataTypeID = 1,
                                $queryRange = 'thisCategory', $from = '', $lang)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!is_numeric($catID) || !is_numeric($dataTypeID)) {
            SGL::raiseError('Wrong datatype passed to '  . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS, PEAR_ERROR_DIE);
        }

        //  if published flag set, only return published articles
        $isPublishedClause = ($bPublished)?
            ' AND i.status  = ' . SGL_STATUS_PUBLISHED :
            ' AND i.status  > ' . SGL_STATUS_DELETED ;

        //  if user only wants contents from current category, add where clause
        $rangeWhereClause   = ($queryRange == 'all') ?
                                '' : " AND i.category_id = $catID";

        //  dataTypeID 1 = all template types, otherwise only a specific one
        $typeWhereClause    = ($dataTypeID == 1)?'' : " AND it.item_type_id  = '$dataTypeID'";
        $limitByAuthorClause = (SGL_Session::getUserType() == SGL_ADMIN)
            ? ''
            : ' AND i.updated_by_id = ' . SGL_Session::getUid();
        $query = "
            SELECT  i.item_id,
                    ia.addition,
                    ia.trans_id,
                    u.username,
                    i.date_created,
                    i.start_date,
                    i.expiry_date,
                    i.status
            FROM    {$this->conf['table']['item']} i, {$this->conf['table']['item_addition']} ia,
                    {$this->conf['table']['item_type']} it, {$this->conf['table']['item_type_mapping']} itm, {$this->conf['table']['user']} u
            WHERE   ia.item_type_mapping_id = itm.item_type_mapping_id
            AND     i.updated_by_id = u.usr_id
            AND     it.item_type_id  = itm.item_type_id
            AND     i.item_id = ia.item_id
            AND     i.item_type_id = it.item_type_id
            AND     itm.field_name = 'title'" . //  match item addition type, 'title'
            $typeWhereClause .                  //  match datatype
            $rangeWhereClause .
            $isPublishedClause .
            $limitByAuthorClause . "
            ORDER BY i.last_updated DESC
            ";
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

        //  fetch title translation
        if ($this->conf['translation']['container'] == 'db') {
            $fallbackLang = $this->conf['translation']['fallbackLang'];
            foreach ($aPagedData['data'] as $k => $aValues) {
                if ($aValues['trans_id']) {

                    //  get translation by language set in users preference
                    if ($title = $this->trans->get($aValues['trans_id'], 'content', $lang)) {
                        $aPagedData['data'][$k]['addition'] = $title . ' ('. str_replace('_', '-', $lang) .')';
                    } else {
                        //  get first available translation any installed language
                        if ($title = $this->trans->get($aValues['trans_id'], 'content', $fallbackLang)) {
                            $aPagedData['data'][$k]['addition'] = $title . ' ('. str_replace('_', '-', $fallbackLang) .')';
                        }
                    }
                }
            }
        }

        return $aPagedData;
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

    /**
     * Returns hash of template types.
     *
     * @access  public
     * @return  array   hash of template types
     */
    function getTemplateTypes()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "  SELECT  item_type_id, item_type_name
                    FROM    {$this->conf['table']['item_type']}
                ";
        return $this->dbh->getAssoc($query);
    }

    function _generateActionLinks($itemID, $itemStatusID)
    {
        //  prepare translations
        $approve = SGL_String::translate('approve');
        $publish = SGL_String::translate('publish');
        $archive = SGL_String::translate('archive');
        switch($itemStatusID) {
        case SGL_STATUS_FOR_APPROVAL:   //  item available for editing
            $url = SGL_Output::makeUrl('changeStatus', 'article', 'publisher');
            $linksHTML = <<< EOF
            <td><a href='${url}frmStatus/approve/frmArticleID/$itemID'>$approve</a></td>
EOF;
            break;

        case SGL_STATUS_BEING_EDITED:   //  item being edited, block access
            $linksHTML = <<< EOF
            <td>&nbsp;</td>
EOF;
            break;

        case SGL_STATUS_APPROVED:       //  item available for publishing
            $url = SGL_Output::makeUrl('changeStatus', 'article', 'publisher');
            $linksHTML = <<< EOF
            <td><a href='${url}frmStatus/publish/frmArticleID/$itemID'>$publish</a></td>
EOF;
            break;

        case SGL_STATUS_PUBLISHED:      //  item available for archiving
            $url = SGL_Output::makeUrl('changeStatus', 'article', 'publisher');
            $linksHTML = <<< EOF
            <td><a href='${url}frmStatus/archive/frmArticleID/$itemID'>$archive</a></td>
EOF;
            break;

        case SGL_STATUS_ARCHIVED:       //  item available for re-editing, being made live again
            $linksHTML = <<< EOF
            <td>&nbsp;use edit</td>
EOF;
            break;
        }
        return $linksHTML;
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

}
?>
