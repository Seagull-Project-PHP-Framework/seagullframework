<?php
/* Reminder: always indent with 4 spaces (no tabs). */
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
// | Item.php                                                                  |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Item.php,v 1.5 2005/02/03 11:29:01 demian Exp $

/**
 * Acts as a wrapper for content objects.
 *
 * @access  public
 * @author  Demian Turner <demian@phpkitchen.com>
 * @package SGL
 * @version $Revision: 1.12 $
 */
class SGL_Item
{
    /**
     * Item ID
     *
     * @access  public
     * @var     int
     */
    var $id;

    /**
     * Name of user that created the item
     *
     * @access	public
     * @var		string
     */
    var $creatorName;

    /**
     * User ID of user that created the item
     *
     * @access	public
     * @var		int
     */
    var $createdByID;

    /**
     * User ID of user to last update item
     *
     * @access  public
     * @var     int
     */
    var $lastUpdatedById;

    /**
     * Timestamp an Item was created
     *
     * @access  public
     * @var     mixed
     */
    var $dateCreated;

    /**
     * Timestamp of last update for an Item
     *
     * @access  public
     * @var     mixed
     */
    var $lastUpdated;

    /**
     * Timestamp when an Item becomes available
     *
     * @access  public
     * @var     mixed
     */
    var $startDate;

    /**
     * Timestamp when an Item expires
     *
     * @access  public
     * @var     mixed
     */
    var $expiryDate;

    /**
     * Item Type Name
     *
     * @access  public
     * @var     string
     */
    var $type;

    /**
     * Item Type ID
     *
     * @access  public
     * @var     int
     */
    var $typeID;

    /**
     * Category ID
     *
     * @access  public
     * @var     array
     */
    var $catID;

    /**
     * Status ID
     *
     * @access  pubic
     * @var     int
     */
    var $statusID;

    /**
     * Language of Item
     * 
     * @access	public
     * @var		string
     */
    var $language;

    /**
     * Data Type Structure
     * 
     * @access  public
     * @var     array
     */
    var $aDataTypeStructure;


   /**
     * Item Data Type Structure
     * 
     * @access  public
     * @var     array
     */
    var $aItemDataTypeStructure;

    /**
     * Constructor
     *
     * @access  public
     * @param   int     $itemID     ItemID
     * @param   string  $language   Language
     * @return  void
     */
    function SGL_Item($options = array())
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!array_key_exists('itemID', $options)) {
            $options['itemID'] = -1;
        }
        if (!array_key_exists('language', $options)) {
            $options['language'] = null;
        }

        $c = &SGL_Config::singleton();
        $this->conf     = $c->getAll();
        $this->dbh      = & SGL_DB::singleton();

        //  detect if trans2 support required
        if ($this->conf['translation']['container'] == 'db') {
            $this->trans = & SGL_Translation::singleton('admin');
            if (is_null($options['language'])) {
                $options['language'] = SGL_Translation::getFallbackLangID();
            }
        }


        if ($options['itemID'] >= 0) {
            $this->_init($options);
        }
    }

    /**
     * Retrieves an Item's Meta Data. Sets the corrisponding class variables.
     *
     * @access  private
     * @param   int     $itemID     ItemID
     * @param   string  $language   Language
     * @return  void
     */
    function _init($options)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $roleId = SGL_Session::get('rid');

        //  get default fields
        $query = "
            SELECT      u.username,
                        i.created_by_id,
                        i.updated_by_id,
                        i.date_created,
                        i.last_updated,
                        i.start_date,
                        i.expiry_date,
                        i.item_type_id,
                        it.item_type_name,
                        i.category_id,
                        i.status,
                        icm.order_id
            FROM        {$this->conf['table']['item']} i
            LEFT JOIN   {$this->conf['table']['item_type']} it ON i.item_type_id = it.item_type_id
            LEFT JOIN   {$this->conf['table']['user']} u ON i.created_by_id = u.usr_id
            LEFT JOIN   {$this->conf['table']['category']} c ON i.category_id = c.category_id
            LEFT JOIN   {$this->conf['table']['item_category_mapping']} icm ON icm.category_id = c.category_id
            WHERE       i.item_id = {$options['itemID']}
            AND         $roleId NOT IN (COALESCE(c.perms, '-1'))
                ";
        $result = $this->dbh->query($query);
        if (!DB::isError($result)) {
            $itemObj = $result->fetchRow();

            //  catch null results
            if (is_null($itemObj)) {
                return false;
            }
            //  set object properties
            $this->set('id', $options['itemID']);
            $this->set('creatorName', $itemObj->username);
            $this->set('createdByID', $itemObj->created_by_id);
            $this->set('lastUpdatedById', $itemObj->updated_by_id);
            $this->set('dateCreated', $itemObj->date_created);
            $this->set('lastUpdated', $itemObj->last_updated);
            $this->set('startDate', $itemObj->start_date);
            $this->set('expiryDate', $itemObj->expiry_date);
            $this->set('typeID', $itemObj->item_type_id);
            $this->set('type', $itemObj->item_type_name);
            $this->set('statusID', $itemObj->status);

            // get categories
//            (!is_null($itemObj->category_id))
//                ? $this->set('catID', $itemObj->category_id)
//                : $this->set('catID', $this->getCategories());
            // as all articles are importet properly we can avoid this switch
            $this->set('catID', $this->getCategories());

            //  language clause
            (!is_null($options['language']))
                ? $this->set('languageID', $options['language'])
                : '';
        } else {
            SGL::raiseError('Problem with query in ' . __FILE__ . ', ' . __LINE__,
                SGL_ERROR_NODATA);
        }
    }

    /**
     * Inserts Meta Items into item table.
     *
     * @access  public
     * @return  int     $id Item ID
     */
    function addMetaItems()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aCategories = $this->catID ? $this->catID : array(1);

        /* build a container that will store the categories an item will appear in
         * array('category_id' => 'order_id')
         */
        foreach ($aCategories as $catID){
            $aCatID[$catID] = '';
        }
        $table = $this->conf['table']['item'];
        $id = $this->dbh->nextId($table);
        //TODO: make configurable. for now set it as published
        $this->statusID  = isset($this->statusID) ? $this->statusID : SGL_STATUS_FOR_APPROVAL ;
        $query = "
            INSERT INTO {$this->conf['table']['item']}(
                item_id,
                created_by_id,
                updated_by_id,
                date_created,
                last_updated,
                start_date,
                expiry_date,
                item_type_id,
                status
            ) VALUES (
                $id,
                $this->createdByID,
                $this->createdByID, " .
                $this->dbh->quote($this->dateCreated) . ", " .
                $this->dbh->quote($this->lastUpdated) . ", " .
                $this->dbh->quote($this->startDate) . ", " .
                $this->dbh->quote($this->expiryDate) . ", " .
                $this->typeID. ", " .
                $this->statusID . "
             )";
        $result = $this->dbh->query($query);
        
        //FIXME: check for DB Error

        //  add item to specified categories
        $this->addItemToCategories($id, $aCatID);
        return $id;
    }

    /**
     * Inserts Data Items into item_addtion table.
     *
     * @access  public
     * @param   int     $parentID   Parent ID
     * @param   int     $itemID     Item ID
     * @param   mixed   $itemValue  Item Value
     * @param   string  $language   Language
     * @return  void
     */
    function addDataItems($aItems)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        foreach ($aItems as $key => $aValue) {
            $language = $aValue['language'];
            $table = $this->conf['table']['item_addition'];
            foreach($aValue['data'] as $k => $v) {
                $id = $this->dbh->nextId($table);
                $transID = $this->isTranslatable($k) 
                    ? $this->dbh->nextID($this->conf['table']['translation'])
                    : 'NULL';
                    
//FIXME: make configurable
//                if ($v['value'] == '') {
//                    $v['value'] = SGL_String::translate('No other text entered');
//                }

                //  profanity check
                $editedTxt = SGL_String::censor($v['value']);

                if (isset($v['type'])) {
                    switch ($v['type']) {
                        case 'htmltextarea':
                            $editedTxt = SGL_String::tidy($editedTxt);
                            break;
                        case 'datetime':
                            $editedTxt = (is_array($editedTxt))
                                ? SGL_Date::arrayToString($editedTxt)
                                : $editedTxt;
                            break;
                    }
                }

                //  insert into item_addition
                $query = "INSERT INTO $table VALUES (
                        $id,
                        $key,
                        $k, ".
                        $this->dbh->quote($editedTxt) .",
                        $transID
                        )";
                $result = $this->dbh->query($query);
                unset($query);

                if ($this->conf['translation']['container'] == 'db'
                    && $this->isTranslatable($k)) {
                    //  build strings array
                    $strings[$language] = $editedTxt;

                    $this->trans->add($transID, 'content', $strings);
                }
                unset($strings);
            }
        }
    }

    /**
     * Add an item to item_category_mapping table
     *
     * @param int   $articleID
     * @param array $aCats      associative array: $catID => $orderID
     *
     * @access  public
     * @return  voide
     */

    function addItemToCategories($articleID, $aCats)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $table = $this->conf['table']['item_category_mapping'];
        if (!is_array($aCats)) {
            SGL::raiseError('Wrong Datatype: $aCatID should be an array.', SGL_ERROR_NODATA);
        }

        foreach ($aCats as $catID => $orderID) {
            // get order id of current category
            $orderID = (empty($orderID)) ? $this->nextOrderId($catID) : $orderID;

            //input to category mapping table
            $query = "INSERT INTO" .
                    " $table (item_id, category_id, order_id)" .
                    " VALUES ($articleID, $catID, $orderID)";
            $result = $this->dbh->query($query);
            unset ($orderID);
        }
    }

    /**
     * Delete item in item_category_mapping table
     *
     * @param int   $articleID
     *
     * @access  public
     * @return  voide
     */

    function deleteCategoryItems($articleID)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $table = $this->conf['table']['item_category_mapping'];

        $query = "DELETE FROM $table WHERE item_id = " . $articleID;
        $result = $this->dbh->query($query);
     }


    /**
     * Update Meta Items in item table.
     *
     * @access  public
     * @return  voide
     */
    function updateMetaItems()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "
            UPDATE {$this->conf['table']['item']} SET
                updated_by_id = $this->lastUpdatedById,
                last_updated = " . $this->dbh->quote($this->lastUpdated) . ",
                start_date = " . $this->dbh->quote($this->startDate) . ",
                expiry_date = " . $this->dbh->quote($this->expiryDate) . ",
                status = $this->statusID
            WHERE item_id = $this->id
                ";
        $result = $this->dbh->query($query);
        $this->updateItemCategories();
        return $result;
    }

    /**
     * Update Data Items in item_addition table.
     *
     * @todo doesn't work if is_translateable flag was changed
     *
     * @access  public
     * @param   int     $itemID     Item ID
     * @param   mixed   $itemValue  Item Value
     * @param   string  $language   Language
     * @return  void
     */
    function updateDataItems($aItems)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $updateTransId = false;

        foreach ($aItems as $key => $aValue) {
            $language = $aValue['language'];
            foreach ($aValue['data'] as $k => $v) {
//                if ($v['value'] == '') {
//                    $v['value'] = SGL_String::translate('No text entered');
//                }
                //  profanity check
                $editedTxt = SGL_String::censor($v['value']);

                if (isset($v['type'])) {
                    switch ($v['type']) {
                        case 'htmltextarea':
                            $editedTxt = SGL_String::tidy($editedTxt);
                            break;
                        case 'datetime':
                            $editedTxt = (is_array($editedTxt))
                                ? SGL_Date::arrayToString($editedTxt)
                                : $editedTxt;
                            break;
                    }
                }

                //  update translations
                $transId = $this->getTransId($k);
                $isTranslateable = $this->isTranslatable($this->aItemDataTypeStructure[$k]['fieldTypeId']);

                // if someone switched from not translateable to translateable in running system
                // this can cause errors when updating
                if ($transId == 0 && $isTranslateable) {
                    $transId =
                        $this->aItemDataTypeStructure[$k]['transId'] =
                        $this->dbh->nextID($this->conf['table']['translation']);
                    $updateTransId = true;
                }

                // same vice versa...

                if (!$isTranslateable) {
                    $updateTransId = true;
                    $transId = 'NULL';

                }


                $table = $this->conf['table']['item_addition'];
                if ($this->conf['translation']['container'] == 'db' && $isTranslateable) {
                        $strings[$language] = $this->trans->get($transId, 'content', $language);

                        if (strcmp($editedTxt, $strings[$language]) !== 0) {
                            $strings[$language] = $editedTxt;
                            $this->trans->add($transId, 'content', $strings);
                        }

                } elseif ($updateTransId == false) {
                    $editedTxt = $this->dbh->quote($editedTxt);
                    $table = $this->conf['table']['item_addition'];    
                    $query = "
                        UPDATE  $table
                        SET     addition = $editedTxt
                        WHERE   item_addition_id = $k
                             ";
                    $result = $this->dbh->query($query);
                    unset($query);
                }
                // this is for both cases, translateable or not
                if ($updateTransId == true) {
                    $updateTransId = false;
                    $editedTxt = $this->dbh->quote($editedTxt);
                    $query = "
                        UPDATE  $table
                        SET     trans_id = $transId, " .
                               "addition = $editedTxt
                        WHERE   item_addition_id = $k";
                    $result = $this->dbh->query($query);
                    unset($query);
                }
                unset($editedTxt);
            }
        }
    }

    /**
     * updates category - item - order table
     *
     * @return void
     */
    function updateItemCategories() {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aOldCats = $this->getCategories($orderID = true);
        $aOldCats = ($aOldCats) ? $aOldCats : array();
        $aNewCats = $this->catID;
        $table = $this->conf['table']['item_category_mapping'];

        //check if category has changed

        foreach ($aNewCats as $catID) {
            //a) item is already in category
            if (array_key_exists($catID, $aOldCats)) {
                // do nothing
                unset($aOldCats[$catID]);
            } else {
                //b) item is new in a category
                // add it...
                $aNewCatIDs[$catID] = '';
            }
        }
        if (isset($aNewCatIDs)) {
            $this->addItemToCategories($this->id, $aNewCatIDs);
        }

        //c) item is deleted from category

        if (count($aOldCats)) {
            foreach ($aOldCats as $catID => $orderID) {
                // delete entry from item - category - order table
                $query = "DELETE FROM $table " .
                        "WHERE item_id = " . $this->id . " AND category_id = " . $catID;
                $result = $this->dbh->query($query);

                // reorder items for this category
                $this->_updateOrderIDs($catID, $orderID);
            }
        }
    }

    /**
     * Deletes an Item from the item and item_addition table. If safe delete
     * is enabled only updates the items status to 0.
     *
     * @access  public
     * @param   array     $aItems   Hash of IDs to delete.
     * @return  void
     */
    function delete($aItems)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  if safeDelete is enabled, just set item status to 0, don't delete
        if ($this->conf['site']['safeDelete']) {
            $sth = $this->dbh->prepare("
                UPDATE {$this->conf['table']['item']}
                SET status = " . SGL_STATUS_DELETED . "
                WHERE item_id = ?");

            foreach ($aItems as $row) {
                $this->dbh->execute($sth, $row);
            }
        //  else delete the bugger
        } else {
            foreach ($aItems as $row) {
                $sql = "DELETE FROM {$this->conf['table']['item']} WHERE item_id = " . $row;
                $this->dbh->query($sql);
            }
            foreach ($aItems as $row) {

                //  fetch item translation ids
                if ($this->conf['translation']['container'] == 'db') {
                    $query = "SELECT * FROM {$this->conf['table']['item_addition']} WHERE item_id=$row";
                    $additionTrans = $this->dbh->getAssoc($query);

                    foreach ($additionTrans as $values) {
                        $this->trans->remove($values->trans_id, 'content');
                    }
                }

                $sql = "DELETE FROM {$this->conf['table']['item_addition']} WHERE item_id=$row";
                $this->dbh->query($sql);
                $this->deleteCategoryItems($row);
            }
        }
    }

    /**
     * Retrieves all categories for the recent item
     *
     * @access public
     * @return array category ids
     */
    function getCategories($orderID = false) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $id = $this->get('id');
        
        $table =  $this->conf['table']['item_category_mapping'];
        
        $query = "SELECT category_id, order_id " .
                 "FROM " . $table . " " .
                 "WHERE item_id = " . $id;

        $result = $this->dbh->query($query);
        $res = '';
        while ($row = $result->fetchRow(DB_FETCHMODE_ORDERED)) {
            if (true == $orderID) {
                $catID = $row[0];
                $res[$catID] = $row[1];
            } else {
               $res[] = $row[0];
            }
        }

        return $res;
    }

    /**
     * Builds a HTML form containing the data from the item_addition table. The
     * input types are built using the data in the item_type and
     * item_type_mapping tables.
     *
     * @access  public
     * @param   int     $itemID         Item ID
     * @param   int     $type           data type to return, can be SGL_RET_STRING
     *                                  or SGL_RET_ARRAY
     * @param   string  $language       Language
     * @return  mixed   $res    HTML Form or Array
     */
    function getDynamicContent($itemID, $type = SGL_RET_STRING, $language)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "
            SELECT  ia.item_addition_id, itm.field_name, ia.addition, ia.trans_id,
                    itm.field_type, itm.is_translateable
            FROM    {$this->conf['table']['item_addition']} ia,
                    {$this->conf['table']['item_type']} it,
                    {$this->conf['table']['item_type_mapping']} itm
            WHERE   ia.item_type_mapping_id = itm.item_type_mapping_id
            AND     it.item_type_id  = itm.item_type_id    /*  match item type */
            AND     ia.item_id = $itemID
            ORDER BY itm.item_type_mapping_id
                ";
        $result = $this->dbh->query($query);

        switch ($type) {

        case SGL_RET_ARRAY:
            $aFields = array();
            $x=0;
            while (list($fieldID, $fieldName, $fieldValue, $transID, $fieldType, $isTranslateable) =
                $result->fetchRow(DB_FETCHMODE_ORDERED)) {
                    // set fieldID to tranlsation ID
                    if ($this->conf['translation']['container'] == 'db' && $isTranslateable == 1 && $transID) {
                        $fieldValue = $this->trans->get($transID, 'content', $language);
                    }
                    //fallback if trans_id exists and it was switchted to "not translateable"
                    elseif ($this->conf['translation']['container'] == 'db' && $transID) {
                        $fieldValue = $this->trans->get($transID, 'content', $language);
                    }

                    $aFields[ucfirst($fieldName)] = $this->generateFormFields(
                        $fieldID, $fieldName, $fieldValue, $fieldType, $language, $x);
                    $x++;
            }
            $res = $aFields;
            break;

        case SGL_RET_STRING:
        default:
            //  get language name
            $langID = str_replace('_', '-', $language);
            $availableLanguages = $GLOBALS['_SGL']['LANGUAGE'];
            $lang_name = ucfirst(substr(strstr($availableLanguages[$langID][0], '|'), 1));
            $languageName =  '('. $lang_name . ' - ' . $langID . ')';

            //  display dynamic form fields (changed default object output to standard array
            $fieldsString = '';
            $x=0;
            while (list($fieldID, $fieldName, $fieldValue, $transID, $fieldType) =
                $result->fetchRow(DB_FETCHMODE_ORDERED)) {

                // set fieldID to tranlsation ID
                if ($this->conf['translation']['container'] == 'db' && $isTranslateable == 1 && $transID) {
                    $fieldValue = $this->trans->get($transID, 'content', $language);
                }
                $fieldsString .= "<tr>\n";
                $fieldsString .= '<th>' . ucfirst($fieldName) ." ". $languageName ."</th>\n";
                $fieldsString .= '<td>' . $this->generateFormFields(
                                          $fieldID, $fieldName, $fieldValue, $fieldType, $language,$x)
                                    . "</td>\n";
                $fieldsString .= "</tr>\n";
                $x++;
            }
            $res = $fieldsString;
        }
        return $res;
    }

    /**
     * Builds a HTML form with the input types built using the data in the
     * item_type and item_type_mapping tables.
     *
     * @access  public
     * @param   int     $typeID         Item Type ID
     * @param   int     $type           data type to return, can be SGL_RET_STRING
     *                                  or SGL_RET_ARRAY
     * @param   string  $language       Language
     * @return  mixed   $res            HTML Form or Array
     */
    function getDynamicFields($typeID, $type = SGL_RET_STRING, $language)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!is_numeric($typeID)) {
            SGL::raiseError('Wrong datatype passed to '  . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS, PEAR_ERROR_DIE);
        }
        //  get template specific form fields
        $query = "
            SELECT  itm.item_type_mapping_id, itm.field_name, itm.field_type
            FROM    {$this->conf['table']['item_type_mapping']} itm
            WHERE   itm.item_type_id = $typeID
            ORDER BY itm.item_type_mapping_id
                    ";

        $result = $this->dbh->query($query);

        //  get language name
        //  FIXME: getLangID() and add to $this->conf()
        $langID = str_replace('_', '-', $language);
        $availableLanguages = $GLOBALS['_SGL']['LANGUAGE'];
        $lang_name = ucfirst(substr(@strstr($availableLanguages[$langID][0], '|'), 1));
        $languageName =  '('. $lang_name . ' - ' . $langID . ')';

        switch ($type) {

        case SGL_RET_ARRAY:
            $aFields = array();
            $x = 0;
            while (list($itemMappingID, $fieldName, $fieldType)
                = $result->fetchRow(DB_FETCHMODE_ORDERED)) {
                    $aFields[ucfirst($fieldName)] =
                        $this->generateFormFields(
                        $itemMappingID, $fieldName, null, $fieldType, $language,$x);
                $x++;
            }
            $res = $aFields;
            break;

        case SGL_RET_STRING:
            //  display dynamic form fields (changed default object output to standard array)
            $fieldsString = '';
            $x = 0;
            while (list($itemMappingID, $fieldName, $fieldType) =
                    $result->fetchRow(DB_FETCHMODE_ORDERED)) {
                $fieldsString .= "<tr>\n";
                $fieldsString .= '<th>' . ucfirst($fieldName) .' '. $languageName ."</th>\n";
                $fieldsString .= '<td>' . $this->generateFormFields(
                                          $itemMappingID, $fieldName, null, $fieldType, $language,$x)
                                    . "</td>\n";
                $fieldsString .= "</tr>\n";
                $x++;
            }
            $res = $fieldsString;
        }
        return $res;
    }

    /**
     * Generates the form fields from the item_type_mapping table for the
     * methods getDynamicContent() and getDynamicFields.
     *
     * @access  public
     * @param   int     $fieldID    Field ID
     * @param   string  $fieldName  Field Name
     * @param   mixed   $fieldValue Field Value
     * @param   int     $fieldType  Field Type
     * @param   string  $language   Language
     * @return  mixed   $formHTML   HTML Form
     */
    function generateFormFields($fieldID, $fieldName, $fieldValue='', $fieldType, $language, $id)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        switch($fieldType) {
        case 0:     // field type = single line
            $formHTML = "<input type='text' id='frmFieldName_$fieldName' name='frmFieldName[$id]' value=\"$fieldValue\" />";
            $formHTML .= "<input type='hidden' name='frmDataItemID[$id]' value='$fieldID' />";
            break;

        case 1:     // field type = textarea paragraph
            $formHTML = "<textarea id='frmFieldName_$fieldName' name='frmFieldName[$id]'>$fieldValue</textarea>";
            $formHTML .= "<input type='hidden' name='frmDataItemID[$id]' value='$fieldID' />";
            break;

        case 2:     // field type = html paragraph
            $formHTML = "<textarea id='frmFieldName_$fieldName' name='frmFieldName[$id]' class='wysiwyg'>$fieldValue</textarea>";
            $formHTML .= "<input type='hidden' name='frmDataItemID[$id]' value='$fieldID' />";
            $formHTML .= "<input type='hidden' name='frmDataItemType[$fieldID]' value='htmltextarea' />";
            break;
        case 3:     // field type = yes no radio select
            $formFieldName = 'frmFieldName_' . $fieldName;
            $checked = ($fieldValue == 1) ? true : false;
            $options = array('id' => 'frmFieldName_'.$fieldName);
            $formHTML =  SGL_Output::generateRadioPair('frmFieldName['.$id.']',$checked,$options);
            $formHTML .= "<input type='hidden' name='frmDataItemID[$id]' value='$fieldID' />";
            break;
        case 4:     // field type = date time selector
            $formFieldName = 'frmFieldName_' . $fieldName;
            $webRoot = SGL_BASE_URL;
            // FIXME: make theme more flexible!
            $theme = 'default_admin';
            $aDate = ($fieldValue == '')
                ? SGL_Date::stringToArray(mktime())
                : SGL_Date::stringToArray($fieldValue);
            $fieldValue = (empty($fieldValue)) ? SGL_Date::getTime() : $fieldValue;
            /*$years = 5;
            $html = '';
            $month_html = "\n<select name='frmFieldName[$id][month]' id='frmFieldName_".$fieldName."[month]' >" . SGL_Date::getMonthFormOptions($aDate['month']) . '</select> / ';
            $day_html = "\n<select name='frmFieldName[$id][day]' id='frmFieldName_".$fieldName."[day]' >" . SGL_Date::getDayFormOptions($aDate['day']) . '</select> / ';
            if ($_SESSION['aPrefs']['dateFormat'] == 'US') {
                $html .= $month_html . $day_html;
            } else {
                $html .= $day_html . $month_html;
            }
            $html .= "\n<select name='frmFieldName[$id][year]' id='frmFieldName_".$fieldName."[year]' >" . SGL_Date::getYearFormOptions($aDate['year'], true, $years) . '</select>';
            $html .= '&nbsp;&nbsp; ';
            $html .= SGL_String::translate('at time');
            $html .= ' &nbsp;&nbsp;';
            $html .= "\n<select name='frmFieldName[$id][hour]'  id='frmFieldName_".$fieldName."[hour]'>" . SGL_Date::getHourFormOptions($aDate['hour']) . '</select> : ';
            $html .= "\n<select name='frmFieldName[$id][minute]' id='frmFieldName_".$fieldName."[minute]'>" . SGL_Date::getMinSecOptions($aDate['minute']) . '</select>';
            */
            $html = '<input type="hidden" name="frmFieldName['.$id.']" id="frmFieldName['.$id.']" value="'.$fieldValue.'" />
                <img class="calendar" id="'.$formFieldName.'Trigger" src="'.$webRoot.'/themes/'.$theme.'/images/16/clock.gif" />
                <span name="'.$formFieldName.'ToShow" id="'.$formFieldName.'ToShow">'.$fieldValue.'</span>';
            $formHTML = $html;
            $formHTML .= "<input type='hidden' name='frmDataItemID[$id]' value='$fieldID' />";
            $formHTML .= "<input type='hidden' name='frmDataItemType[$fieldID]' value='datetime' />";
            // setup the js calendar:
            $formHTML .= '<script type="text/javascript">
                Calendar.setup(
                    {
                        inputField  : "frmFieldName['.$id.']",         // ID of the input field
                        ifFormat    : "%Y-%m-%d %H:%M:%S",    // the date format
                        displayArea : "'.$formFieldName.'ToShow",
                        daFormat    : SGL_JS_DATETEMPLATE,
                        button      : "'.$formFieldName.'Trigger"      // ID of the button
                    }
                );
                </script>';


        }
        return $formHTML;
    }

    /**
     * Updates an Items Status (delete, approve, publish, archive) in the item
     * table.
     *
     * @access  public
     * @param   string  Item Status
     * @return  void
     */
    function changeStatus($status)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        switch($status) {
        case 'delete':
               //   mark item as deleted
            $query = "
                        UPDATE  {$this->conf['table']['item']}
                        SET     status = " . SGL_STATUS_DELETED . "
                        WHERE   item_id = $this->id
                        ";
            break;

        case 'approve':
               //   mark item as approved
            $query = "
                        UPDATE  {$this->conf['table']['item']}
                        SET     status = " . SGL_STATUS_APPROVED . "
                        WHERE   item_id = $this->id
                        ";
            break;

        case 'publish':
               //   mark item as published
            $query = "
                        UPDATE  {$this->conf['table']['item']}
                        SET     status = " . SGL_STATUS_PUBLISHED . "
                        WHERE   item_id = $this->id
                        ";
            break;

        case 'archive':
               //   mark item as published
            $query = "
                        UPDATE  {$this->conf['table']['item']}
                        SET     status = " . SGL_STATUS_ARCHIVED . "
                        WHERE   item_id = $this->id
                        ";
            break;
        }
        $result = $this->dbh->query($query);
    }

    /**
     * Retierves an Item's Meta and Data items and generates the output using
     * the method generateItemOutput().
     *
     * @access  public
     * @param   boolean $bPublished Item published
     * @param   string  $language   Language
     * @return  mixed   $html       HTML Output
     */
    function preview($bPublished = false, $language = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!is_null($language)) {
            //if article is not loaded user does not have permission to view
            if (!$this->id) {
                return false;
            }
            
            $constraint = $bPublished ? ' AND i.status  = ' . SGL_STATUS_PUBLISHED : '';
            $query = "
                SELECT  ia.item_addition_id, itm.field_name, ia.addition, ia.trans_id, i.category_id
                FROM    {$this->conf['table']['item']} i,
                        {$this->conf['table']['item_addition']} ia,
                        {$this->conf['table']['item_type']} it,
                        {$this->conf['table']['item_type_mapping']} itm
                WHERE   ia.item_type_mapping_id = itm.item_type_mapping_id
                AND     it.item_type_id  = itm.item_type_id    /*  match item type */
                AND     i.item_id = ia.item_id
                AND     ia.item_id = $this->id
                $constraint
                ORDER BY itm.item_type_mapping_id
                ";
            $result = $this->dbh->query($query);

            if (!DB::isError($result)) {
                $html = array();
                while (list($fieldID, $fieldName, $fieldValue, $transId, $catId) =
                        $result->fetchRow(DB_FETCHMODE_ORDERED)) {
                    if ($this->conf['translation']['container'] == 'db' && $transId) {
                        $fieldValue = $this->trans->get($transId, 'content', $language);
                    }
                    $html[$fieldName] = $this->generateItemOutput(
                        $fieldID, $fieldName, $fieldValue, $this->typeID);
                    $html['category_id'] = $catId;
                }
                //FIXME: fallback for old installations.
                //afaik it's better to convert everything to item-category-mapping table,
                //so we only have arrays of categories for an item
                //remove later
                $html['category_id'] =  (empty($html['category_id']))
                    ? $this->getCategories()
                    : $html['category_id'];
                return $html;
            } else {
                return SGL::raiseError('Problem with query in ' . __FILE__ . ', ' . __LINE__,
                    SGL_ERROR_NODATA);
           }
        } else {
            return SGL::raiseError('Invalid parameters', SGL_ERROR_INVALIDARGS);
        }
    }

    /**
     * Retrieves and returns an array that contain an Items' Meta and Data
     * items.
     *
     * @access  public
     * @return  array   $html
     */
    function manualPreview()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "
            SELECT  ia.item_addition_id, itm.field_name, ia.addition, itm.item_type_mapping_id
            FROM    {$this->conf['table']['item_addition']} ia,
                    {$this->conf['table']['item_type']} it,
                    {$this->conf['table']['item_type_mapping']} itm
            WHERE   ia.item_type_mapping_id = itm.item_type_mapping_id
            AND     it.item_type_id  = itm.item_type_id    /*  match item type */
            AND     ia.item_id = $this->id
            ORDER BY itm.item_type_mapping_id
                ";
        $result = $this->dbh->query($query);

        while (list($fieldID, $fieldName, $fieldValue, $trans_id) = $result->fetchRow(DB_FETCHMODE_ORDERED)) {
            if ($this->conf['translation']['container'] == 'db') {
                $fieldValue = $this->trans->get($trans_id, 'content', $this->languageID);
            }
            $html[$fieldName] = $fieldValue;
        }
        return $html;
    }

    /**
     * Generates the output for an Item using the template defined below.
     *
     * @access  public
     * @param   int     $fieldID        Field ID
     * @param   string  $fieldName      Field Name
     * @param   mixed   $fieldValue     Field Value
     * @param   int     $itemTypeID     Item Type ID
     * @return  array   $outputHTML
     */
    function generateItemOutput($fieldID, $fieldName, $fieldValue, $itemTypeID)
    {
        switch ($itemTypeID) {
        case 2:                //   template type = HTML article
            switch ($fieldName) {
            case 'title':
            case 'bodyHtml':
                $outputHTML = $fieldValue;
                break;
            }
            break;

        case 3:                //   template type = Text Article
            switch ($fieldName) {
            case 'title':
            case 'bodyText':
                $outputHTML = $fieldValue;
                break;
            }
            break;

        case 4:                //   template type = News article
            switch ($fieldName) {
            case 'title':
            case 'newsHtml':
                $outputHTML = $fieldValue;
                break;
            }
            break;

        case 5:                //   template type = static HTML article
            switch ($fieldName) {
            case 'title':
            case 'bodyHtml':
                $outputHTML = $fieldValue;
                break;
            }
            break;

        default:
        print "This means you haven't defined a template in Item::generateItemOutput";
            }
            return $outputHTML;
    }

    /**
     * Sets an Item's Meta Data value
     *
     * @access  public
     * @param   string  $attributeName
     * @param   mixed   $attributeValue
     * @return  void
     */
    function set($attributeName, $attributeValue)
    {
        $this->$attributeName = $attributeValue;
    }

    /**
     * Retrieves an Item's Meta Data value
     *
     * @access  public
     * @param   string  $attribute
     * @return  mixed   $this->attribute
     */
    function get($attribute)
    {
        return $this->$attribute;
    }

    /**
     * Retrieve a list of Items by CatID
     *
     * @access  public
     * @param   int     $catID
     * @param   int     $dataTypeID
     * @param   int     $mostRecentArticleID
     * @return  array   $aArticleList
     * @see     retrievePaginated()
     */
    function getItemListByCatID($catID, $dataTypeID, $mostRecentArticleID)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  grab article with template type from session preselected
        
        $aResult = SGL_Item::retrievePaginated(
            array(
                'catID'     => $catID,
                'bPublish'  => true,
                'dataTypeID'    => $dataTypeID,
            )
        );
        if (PEAR::isError($aResult)) {
            return $aResult;
        }
        $aArticleList = $aResult['data'];

        //  get most recent article, if array is non-empty,
        //  only if none has been passed from 'more articles' list
        if (count($aArticleList)) {
            if (!$mostRecentArticleID) {
                SGL_Session::set('articleID', $aArticleList[0]['item_id']);
            }
        }
        return (count($aArticleList) >= 1) ? $aArticleList : array();
    }

    /**
     * Retrieve an Items Meta and Data details.
     *
     * @access  public
     * @param   int     $itemID     Item ID
     * @param   boolean $bPublished Item Published
     * @param   string  $language   Language
     * @return  array   $ret        Array containg an Item's Details or false
     */
    function getItemDetail($itemID = null, $bPublished = null, $language = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if ((!$itemID) && (SGL_Session::get('articleID'))) {
            $itemID = SGL_Session::get('articleID');
        }
        if ($itemID) {
            $item = & new SGL_Item(array('itemID' => $itemID));
            if (!isset($language) || empty($language) ) {
                $language = SGL_Translation::getLangID();
            }
            $ret = $item->preview($bPublished, $language);

            //if article is not loaded user does not have permission to view
            if (!empty($ret)) {
                if (!is_a($ret, 'PEAR_Error')) {
                    $ret['creatorName'] = $item->creatorName;
                    $ret['createdByID'] = $item->createdByID;
                    $ret['startDate'] = $item->startDate;
                    $ret['type'] = $item->type;
                    return $ret;
                } else {
                    return $ret;
                }
            }
        }
        return false;
    }

    /**
     * Gets paginated list of articles.
     *
     * @access  public
     * @static
     * @param   array   $options
     * @param   int     $options['dataTypeID'] template ID of article, ie, news article, weather article, etc.
     * @param   string  $options['queryRange'] flag to indicate if results limited to specific category
     * @param   int     $options['catID']      optional cat ID to limit results to
     * @param   int     $options['from']       row ID offset for pagination
     * @param   string  $options['orderBy']    column to sort on
     * @param   array   $options['search']     id of field to search, text to search
     * @param   boolean $options['disablePager']  retrieve all items or paged data. Default to false (pageing enabled)  
     * @param   array   $options['perPage']    nr of results to serve. If emtpy default value from prevs are used
     * @return  array   $aResult    returns array of article objects, pager data, and show page flag
     */
    function retrievePaginated($options)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aAllowedSearchOperators = array ('=','>','<','<=','>=','=>','=<');
        if (is_array($options) && !array_key_exists('catID', $options)) {
            SGL::raiseError('Invalid parameterspassed to '  . __CLASS__ . '::' .
                    __FUNCTION__, SGL_ERROR_INVALIDARGS);
        } elseif (is_array($options)) {
            $catID = $options['catID'];
            $bPublished = (array_key_exists('bPublished', $options))
                ? $options['bPublished']
                : false;
            $dataTypeID = (array_key_exists('dataTypeID', $options))
                ? $options['dataTypeID']
                : 1;
            $queryRange = (array_key_exists('queryRange', $options))
                ? $options['queryRange']
                : 'thisCategory';
            $from = (array_key_exists('from', $options)) ? $options['from'] : '';
            $orderBy = (array_key_exists('orderBy', $options))
                ? 'i.'.$options['orderBy']
                : 'icm.order_id';
            // we don't need icm.order_id when all categories are shown.
            // Let's use last_updated in this case.
            $orderBy = ($queryRange != 'all') ? $orderBy : 'i.last_updated';
            $orderID = ($queryRange != 'all') ? ', icm.order_id' : '';
	    $orderDirection = (array_key_exists('orderDirection', $options))
	        ? ($options['orderDirection'] == 'ASC' || $options['orderDirection'] == 'DESC')
		    ? $options['orderDirection'] : 'ASC'
	        : 'ASC';

            if (array_key_exists('search', $options)){
	        if (array_key_exists('operator', $options['search'])) {
	            //remove possible withespace
    	            $options['search']['operator'] = trim ($options['search']['operator']);
		
                    $operator =  in_array($options['search']['operator'],$aAllowedSearchOperators)
		        ? ' ' . $options['search']['operator'] . ' '
		        : ' = ';
                } else {
                    $operator = ' = ';
                }
                $searchJoin =   "LEFT JOIN   {$this->conf['table']['item_addition']} ia2 ON i.item_id = ia2.item_id";
                $searchSelect = 'ia2.addition as search,';
                $searchWhereClause =  array_key_exists('text', $options['search'])
                        ?"AND ia2.addition " . $operator ."'" . $options['search']['text'] . "' "
                        : '';
                $searchFieldIdClause =  array_key_exists('id', $options['search'])
                    ? 'AND ia2.item_type_mapping_id = ' . $options['search']['id'] . ' '
                    : '';
             } else {
                $searchJoin =   '';
                $searchSelect = '';
                $searchWhereClause = '';
                $searchFieldIdClause = '';
            }

            if (!isset($this)) {
                new SGL_Item();
            }

            if (!is_numeric($catID) || !is_numeric($dataTypeID)) {
                return SGL::raiseError('Wrong datatype passed to '  . __CLASS__ . '::' .
                    __FUNCTION__, SGL_ERROR_INVALIDARGS);
            }
            //  if published flag set, only return published articles
            $isPublishedClause = ($bPublished)?
                ' AND i.status  = ' . SGL_STATUS_PUBLISHED :
                ' AND i.status  > ' . SGL_STATUS_DELETED ;

            //  if user only wants contents from current category, add where clause
            $rangeWhereClause   = ($queryRange == 'all')?'' : " AND icm.category_id = $catID";
            $roleId = SGL_Session::get('rid');

            //  dataTypeID 1 = all template types, otherwise only a specific one
            $typeWhereClause = ($dataTypeID > '1') ? " AND it.item_type_id = $dataTypeID" : '';

            $query = "
                SELECT  DISTINCT
                        i.item_id,
                        ia.addition,
                        ".$searchSelect."
                        ia.trans_id,
                        u.username,
                        i.date_created,
                        i.start_date,
                        i.expiry_date,
                        i.status" .
                        $orderID . "
            FROM        {$this->conf['table']['item']} i
            LEFT JOIN   {$this->conf['table']['item_addition']} ia ON i.item_id = ia.item_id
            ".$searchJoin."
            LEFT JOIN   {$this->conf['table']['item_type']} it ON i.item_type_id = it.item_type_id
            LEFT JOIN   {$this->conf['table']['item_type_mapping']} itm ON it.item_type_id = itm.item_type_id
            LEFT JOIN   {$this->conf['table']['item_category_mapping']} icm ON i.item_id = icm.item_id
            LEFT JOIN   {$this->conf['table']['user']} u ON i.updated_by_id = u.usr_id
            LEFT JOIN   {$this->conf['table']['category']} c ON i.category_id = c.category_id
            WHERE  " .
                    "ia.item_type_mapping_id = itm.item_type_mapping_id" .
           " AND " .
           "itm.field_name = 'title' " .
            $searchFieldIdClause .
            $searchWhereClause .
            $typeWhereClause .                          //  match datatype
            $rangeWhereClause .
            $isPublishedClause .
            "
            AND     $roleId NOT IN (COALESCE(c.perms, '-1'))
            ORDER BY $orderBy $orderDirection
            ";
            
//FIXME: remove debug code
//var_dump($query);
//die;

if (array_key_exists('debug',$options) && $options['debug'] == true) {
    echo $query;
}

            $limit =  (array_key_exists('perPage', $options)) 
                ? $options['perPage'] 
                :$_SESSION['aPrefs']['resPerPage'];
            $disablePager = (bool)(array_key_exists('disablePager', $options))
                ? $options['disablePager']
                : false;
            $pagerOptions = array(
                'mode'     => 'Sliding',
                'delta'    => 3,
                'perPage'  => $limit,
                'spacesBeforeSeparator' => 0,
                'spacesAfterSeparator'  => 0,
                'curPageSpanPre'        => '<span class="currentPage">',
                'curPageSpanPost'       => '</span>',
            );
            $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions, $disablePager);
            if ($this->conf['translation']['container'] == 'db') {
                foreach ($aPagedData['data'] as $k => $aValues) {
                    if (($title = $this->trans->get($aValues['trans_id'], 
                            'content', SGL_Translation::getLangID()))
                    ||  ($title = $this->trans->get($aValues['trans_id'], 
                            'content', SGL_Translation::getFallbackLangID())))
                    {
                        $aPagedData['data'][$k]['addition'] = $title;
                    }
                }
            }
            return $aPagedData;
        }
    }

    /**
     * Gets list of all articles in a category.
     * 
     * @deprecated use retrievePaginated() instead
     * @see     retrievePaginated()
     */
    function retrieveAll($options)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (is_array($options) && !array_key_exists('catID', $options)) {
            SGL::raiseError('Invalid parameters passed to '  . __CLASS__ . '::' .
                    __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        $options['disablePager'] = true;
        if (!isset($this)) {
            new SGL_Item();
        }
        
        return $this->retrievePaginated($options);
    }


    /**
     * returns the next orderID of a given category
     *
     * @param int $catID
     * @return int $id
     */
    function nextOrderId($catID)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $table = $this->conf['table']['item_category_mapping'];
        $query = "SELECT order_id " .
                 "FROM $table " .
                 "WHERE category_id = " . $catID;

        $result = $this->dbh->query($query);
        // note: For [..] oci8, this method only works if the
        // DB_PORTABILITY_NUMROWS portability option is enabled.

        // we start with ids with 0, so we can just return the number of ids
        return $result->numRows();
    }

    /**
     * reorders order_ids of a category after deleting an item
     *
     * @access private
     * @param int $catID
     * @param int $orderID where to start from
     * @return void
     */
    function _updateOrderIDs($catID, $orderID = 0)
    {
        $pr = $this->dbh->prepare('UPDATE ' . $this->conf['table']['item_category_mapping'] . 
            ' SET order_id = ? WHERE order_id = ? AND category_id = ' . $catID);
        do {
          $res = $this->dbh->execute($pr, array($orderID, ++$orderID));
          if (DB::isError($res)) {
            // handle error
            break;
          }
        } while ($this->dbh->affectedRows() > 0);
    }

    /**
     * for reordering items
     *
     * @access public
     * @param int $targetID where to move the current item to
     * @return void
     */
    function moveItem($catID, $targetID)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //check if item is in this category and get current order_id
        $query = "SELECT order_id " .
                 "FROM {$this->conf['table']['item_category_mapping']} " .
                 "WHERE category_id = " . $catID . " AND item_id = " . $this->id;

        $result = $this->dbh->query($query);

        if (!DB::isError($result)) {
            list($oldOrderID) = $result->fetchRow(DB_FETCHMODE_ORDERED);
        } else {
            return SGL::raiseError('Problem with query in ' . __FILE__ . ', ' . __LINE__,
                SGL_ERROR_NODATA);
        }

        //check if $targetID is inside order_id range
        $maxID = $this->nextOrderId($catID);
        if ($targetID >= $maxID){
            SGL::raiseError('Wrong $targetID in ' . __FILE__ . ', ' . __LINE__,
                SGL_ERROR_NODATA);
            break;
        }

        //decide if it's up or down
        $i = $targetID - $oldOrderID;
        if ($i < 0){
            $step = 1;
        } elseif ($i > 0) {
            $step = -1;
        } else {
            //something went wrong, we don't have to reorder anything
            die("nothing to do");
            return;
        }

        //reorder order_ids in between
        //move item at end of list
        $aOrderID[] = array ($maxID, $oldOrderID);
        $i = $oldOrderID;
        while($i - $targetID != 0){
            $aOrderID[] = array($i, $i - $step);
            $i = $i - $step;
        }
        //move item to final place
        $aOrderID[] = array ($targetID, $maxID);  
        
        $pr = $this->dbh->prepare('UPDATE ' . $this->conf['table']['item_category_mapping'] . 
            ' SET order_id = ? WHERE order_id = ? AND category_id = ' . $catID);
        //$res = $this->dbh->executeMultiple($pr, $aOrderID);
        foreach ($aOrderID as $row){
            $res = $this->dbh->execute($pr, $row);
            if (DB::isError($res)) {
              // handle error
              break;
            }
        }
    }
    /**
     * Returns an array representing the structure of a data type
     *
     * @param  int   $typeID
     * @return array $aDataTypeStructure
     */
    function getDataTypeStructure($typeID)
    {
        $aDataTypeStructure = array();
        $table = $this->conf['table']['item_type_mapping'];

        //  get template specific form fields
        $query = "
            SELECT  itm.item_type_mapping_id, itm.field_name, itm.field_type, " .
                    "itm.is_translateable
            FROM    $table itm
            WHERE   itm.item_type_id = $typeID
            ORDER BY itm.item_type_mapping_id
                    ";
        $result = $this->dbh->query($query);

        while (list($fieldID, $fieldName, $fieldType, $isTranslateable) = $result->fetchRow(DB_FETCHMODE_ORDERED)) {
            $aDataTypeStructure[$fieldID] = array(
                'id' => $fieldID,
                'name' => $fieldName,
                'type' => $fieldType,
                'is_translateable' => $isTranslateable
            );
        }
        return $aDataTypeStructure;
    }

    /**
     * Returns an array representing the structure of an existing item
     *
     * @param  int   $itemID
     * @return array $aDataTypeStructure
     */
    function getItemDataTypeStructure($itemId)
    {
        $aItemDataTypeStructure = array();
        //  get template specific form fields
        $table = $this->conf['table']['item_addition'];
        $query = "
            SELECT  ia.item_addition_id, ia.item_type_mapping_id, ia.trans_id
            FROM    $table ia
            WHERE   ia.item_id = $itemId
            ORDER BY ia.item_addition_id
                    ";
        $result = $this->dbh->query($query);

        while (list($fieldId, $fieldItemTypeId, $transId) = $result->fetchRow(DB_FETCHMODE_ORDERED)) {
            $aItemDataTypeStructure[$fieldId] = array(
                'id' => $fieldId,
                'itemId' => $itemId,
                'fieldTypeId' => $fieldItemTypeId,
                'transId' => $transId
            );
        }
        return $aItemDataTypeStructure;
    }

    /**
     * Returns true if an item field is translateable
     *
     * @param  int $field_id
     * @return bool
     */
    function isTranslatable($field_id)
    {
        if (!$this->aDataTypeStructure) {
           $this->aDataTypeStructure = $this->getDataTypeStructure($this->typeID); 
        }

        $aDataTypeStructure = $this->aDataTypeStructure;

        return ($this->aDataTypeStructure[$field_id]['is_translateable']== 0)? false : true;
    }

     /**
     * Returns the trans_id of an item_addition or false if it's not translateable
     *
     * @param  int $item_addition_id
     * @return mixed $transId or false
     */
    function getTransId($item_addition_id)
    {
        if (!$this->aItemDataTypeStructure) {
           $this->aItemDataTypeStructure = $this->getItemDataTypeStructure($this->id); 
        }
        $aItemDataTypeStructure = $this->aItemDataTypeStructure;
        
        return $this->aItemDataTypeStructure[$item_addition_id]['transId'];
        
    }
    
    /**
     * Returns true if the current item is using wysiwig editor
     * 
     * @return bool
     */
    function usesWysiwyg()
    {
        if (!$this->aDataTypeStructure) {
           $this->aDataTypeStructure = $this->getDataTypeStructure($this->typeID); 
        }
        
        foreach ($this->aDataTypeStructure as $field_id => $values) {
            if ($values['type'] == 2) {
                return true;
            } 
        }
        
        return false;
    } 
}
?>