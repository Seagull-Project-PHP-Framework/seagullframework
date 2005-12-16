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
// | Item.php                                                                  |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Permissions.php,v 1.5 2005/02/03 11:29:01 demian Exp $

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
     * @var     int
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
     * Constructor
     * 
     * @access  public
     * @param   int     $itemID     ItemID
     * @param   string  $language   Language
     * @return  void
     */
    function SGL_Item($itemID = -1, $language = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();
        $this->dbh = & SGL_DB::singleton();
        if ($itemID >= 0) {
            $this->_init($itemID, $language);
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
    function _init($itemID, $languageID = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get default fields
        $query = "
                    SELECT  u.username,
                            i.created_by_id,
                            i.updated_by_id,
                            i.date_created,
                            i.last_updated,
                            i.start_date,
                            i.expiry_date,
                            i.item_type_id,
                            it.item_type_name,
                            i.category_id,
                            i.status
                    FROM    {$this->conf['table']['item']} i,
                            {$this->conf['table']['item_type']} it,
                            {$this->conf['table']['user']} u
                    WHERE   it.item_type_id = i.item_type_id
                    AND     i.created_by_id = u.usr_id
                    AND     i.item_id = $itemID
                ";
        $result = $this->dbh->query($query);
        if (!DB::isError($result)) {
            $itemObj = $result->fetchRow();

            //  catch null results
            if (is_null($itemObj)) {
                return false;
            }
            //  set object properties
            $this->set('id', $itemID);
            $this->set('creatorName', $itemObj->username);
            $this->set('createdByID', $itemObj->created_by_id);
            $this->set('lastUpdatedById', $itemObj->updated_by_id);
            $this->set('dateCreated', $itemObj->date_created);
            $this->set('lastUpdated', $itemObj->last_updated);
            $this->set('startDate', $itemObj->start_date);
            $this->set('expiryDate', $itemObj->expiry_date);
            $this->set('typeID', $itemObj->item_type_id);
            $this->set('type', $itemObj->item_type_name);
            $this->set('catID', $itemObj->category_id);
            $this->set('statusID', $itemObj->status);
            
            //  language clause
            (!is_null($languageID)) ? $this->set('languageID', $languageID) : '';
                        
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
        
        $catID = $this->catID ? $this->catID : 1;        
        $id = $this->dbh->nextId($this->conf['table']['item']);
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
                status,
                category_id,
                languages
            ) VALUES (
                $id,
                $this->createdByID,
                $this->createdByID, " .
                $this->dbh->quote($this->dateCreated) . ", " .
                $this->dbh->quote($this->lastUpdated) . ", " .
                $this->dbh->quote($this->startDate) . ", " .
                $this->dbh->quote($this->expiryDate) . ",
                $this->typeID," .
                SGL_STATUS_FOR_APPROVAL . ",
                $catID, ".
                $dbh->quote($this->languages) ."
            )";
        $result = $this->dbh->query($query);
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
    function addDataItems($parentID, $itemID, $itemValue, $language)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
                
        for ($x=0; $x < count($itemID); $x++) {
            $id = $dbh->nextId($this->conf['table']['item_addition']);
            $transID = $dbh->nextID($this->conf['table']['translation']);
            
            if ($itemValue[$x] == '')
                $itemValue[$x] = SGL_String::translate('No other text entered');

            //  profanity check
            $editedTxt = SGL_String::censor($itemValue[$x]);

            //  build strings array
            $strings[$language] = $editedTxt;
    
            //  insert into item_addition
            $query = "
                    INSERT INTO {$this->conf['table']['item_addition']} VALUES (
                        $id,
                        $parentID,
                        $itemID[$x],
                        $transID
                    )";
            $result = $this->dbh->query($query);
            unset($query);

            $trans->add($transID, 'content', $strings);
            unset($strings);
        }
    }

    /**
     * Inserts Item Body into item_addition table.
     * 
     * @access  public
     * @param   int     $parentID   Parent ID
     * @param   int     $itemID     Item ID
     * @param   mixed   $itemValue  Item Value
     * @param   string  $language   Language
     * @return  void
     */
    function addBody($parentID, $itemID, $itemValue, $language)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $trans = &SGL_Translation::singleton('admin');

        $id = $this->dbh->nextId($conf['table']['item_addition']);
        $transID = $this->dbh->nextID($conf['table']['translation']);

        if ($itemValue == '')
            $itemValue = SGL_String::translate('No body text entered');

        //  profanity check
        $editedTxt = SGL_String::censor($itemValue);
        
        $strings[$language] = $editedTxt;

        $query = "
            INSERT INTO {$this->conf['table']['item_addition']} VALUES (
                $id,
                $parentID,
                $itemID,
                $transID
            )";
        $result = $this->dbh->query($query);

        $trans->add($transID, 'content', $strings);
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
                status = $this->statusID,
                category_id = $this->catID,
                languages = ". $this->dbh->quote($this->languages) . "
                WHERE item_id = $this->id
                ";
        $result = $this->dbh->query($query);
    }

    /**
     * Update Data Items in item_addtiion table.
     * 
     * @access  public
     * @param   int     $itemID     Item ID
     * @param   mixed   $itemValue  Item Value
     * @param   string  $language   Language
     * @return  void
     */
    function updateDataItems($itemID, $itemValue, $language)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $trans = &SGL_Translation::singleton('admin');

        for ($x=0; $x < count($itemID); $x++) {
            if ($itemValue[$x] == '') {
                $itemValue[$x] = SGL_String::translate('No text entered');
            }
            //  profanity check
            $editedTxt = SGL_String::censor($itemValue[$x]);

            //  fetch current translations
            $strings[$language] = $trans->get($itemID[$x], 'content', $language);
            
            //  merge translations
            if ($editedTxt != $strings[$language]) {
                $strings[$language] = $editedTxt;   
            }        
            unset($editedTxt);

            //  update translations
            $trans->add($itemID[$x], 'content', $strings);            
        }
    }

    /**
     * Update a Data Item's Body in item_addition
     * 
     * @access  public
     * @param   int     $itemID     Item ID
     * @param   mixed   $itemValue  Item Value
     * @param   string  $language   Language
     * @return  void
     */
    function updateBody($itemID, $itemValue, $language)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $trans = &SGL_Translation::singleton('admin');
        
        if ($itemValue == '') {
            $itemValue = SGL_String::translate('No text entered');
        }   
        
        //  profanity check
        $editedTxt = SGL_String::censor(SGL_String::tidy($itemValue));
        
        //  fetch current translation
        $strings[$language] = $trans->get($itemID, 'content', $language);
        
        //  merge translations
        if ($editedTxt !== $strings[$language]) {
            $strings[$language] = $editedTxt;
        }

        //  add translations
        $transAdmin->add($itemID, 'content', $strings);            
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
            $trans = &SGL_Translation::singleton('admin');
            foreach ($aItems as $row) {
                //  fetch item translation ids
                $query = "SELECT * FROM {$this->conf['table']['item_addition']} WHERE item_id=$row";
                $additionTrans = $this->dbh->getAssoc($query);
                
                foreach ($additionTrans as $key => $values) {
                    $trans->remove($values->addition, 'content');   
                }

                $sql = "DELETE FROM {$this->conf['table']['item_addition']} WHERE item_id=$row";
                $this->dbh->query($sql);
            }
        }
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
            SELECT  ia.item_addition_id, itm.field_name, ia.addition, 
                    itm.field_type, itm.item_type_mapping_id
            FROM    {$this->conf['table']['item_addition']} ia,
                    {$this->conf['table']['item_type']} it,
                    {$this->conf['table']['item_type_mapping']} itm
            WHERE   ia.item_type_mapping_id = itm.item_type_mapping_id 
            AND     it.item_type_id  = itm.item_type_id    /*  match item type */
            AND     ia.item_id = $itemID
            ORDER BY itm.item_type_mapping_id
                ";
        $result = $this->dbh->query($query);

        $trans = &SGL_Translation::singleton();

        switch ($type) {

        case SGL_RET_ARRAY:
            $aFields = array();
            while (list($fieldID, $fieldName, $fieldValue, $fieldType)
                = $result->fetchRow(DB_FETCHMODE_ORDERED)) 
                    // set fieldID to tranlsation ID
                    $fieldID = $fieldValue;
                    $fieldValue = $trans->get($fieldValue, 'content', $language);
                    $aFields[ucfirst($fieldName)] =
                        $this->generateFormFields(
                        $fieldID, $fieldName, $fieldValue, $fieldType);
            }
            $res = $aFields;
            break;
            
        case SGL_RET_STRING:
        default:
            //  get language name
            //  FIXME: getLangID and $this->conf()
            $langID = str_replace('_', '-', $language);
            $availableLanguages = $GLOBALS['_SGL']['LANGUAGE'];
            $lang_name = ucfirst(substr(strstr($availableLanguages[$langID][0], '|'), 1));
            $languageName =  '('. $lang_name . ' - ' . $langID . ')';
    
            //  display dynamic form fields (changed default object output to standard array
            $fieldsString = '';
            while (list($fieldID, $fieldName, $fieldValue, $fieldType)
                = $result->fetchRow(DB_FETCHMODE_ORDERED)) {
                // set fieldID to tranlsation ID
                $fieldID = $fieldValue;
                $fieldValue = $trans->get($fieldValue, 'content', $language);
                $fieldsString .= "<tr>\n";
                $fieldsString .= '<th>' . ucfirst($fieldName) ." ". $languageName ."</th>\n";
                $fieldsString .= '<td>' . $this->generateFormFields(
                                          $fieldID, $fieldName, $fieldValue, $fieldType, $language) 
                                    . "</td>\n";
                $fieldsString .= "</tr>\n";
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
        $result = $dbh->query($query);

        //  get language name
        //  FIXME: getLangID() and add to $this->conf()
        $langID = str_replace('_', '-', $language);
        $availableLanguages = $GLOBALS['_SGL']['LANGUAGE'];
        $lang_name = ucfirst(substr(strstr($availableLanguages[$langID][0], '|'), 1));
        $languageName =  '('. $lang_name . ' - ' . $langID . ')';

        switch ($type) {

            case SGL_RET_ARRAY:
                $aFields = array();
                while (list($itemMappingID, $fieldName, $fieldType)
                    = $result->fetchRow(DB_FETCHMODE_ORDERED)) {
                        $aFields[ucfirst($fieldName)] =
                            $this->generateFormFields(
                            $itemMappingID, $fieldName, null, $fieldType);
                }
                $res = $aFields;
                break;
    
            case SGL_RET_STRING:
            //  display dynamic form fields (changed default object output to standard array)
            $fieldsString = '';
            while (list($itemMappingID, $fieldName, $fieldType) 
                = $result->fetchRow(DB_FETCHMODE_ORDERED)) {
                $fieldsString .= "<tr>\n";
                $fieldsString .= '<th>' . ucfirst($fieldName) .' '. $languageName ."</th>\n";
                $fieldsString .= '<td>' . $this->generateFormFields(
                                          $itemMappingID, $fieldName, null, $fieldType, $language) 
                                    . "</td>\n";
                $fieldsString .= "</tr>\n";
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
    function generateFormFields($fieldID, $fieldName, $fieldValue='', $fieldType, $language)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        switch($fieldType) {
        case 0:     // field type = single line
            $formHTML = "<input type='text' name='frmFieldName[]' value='$fieldValue' size='75' />";
            $formHTML .= "<input type='hidden' name='frmDataItemID[]' value='$fieldID' />";
            break;

        case 1:     // field type = textarea paragraph
            $formHTML = "<textarea name='frmFieldName[]' rows='10' cols='60'>$fieldValue</textarea>";
            $formHTML .= "<input type='hidden' name='frmDataItemID[]' value='$fieldID' />";
            break;

        case 2:     // field type = html paragraph
            $formHTML = "<textarea id='frmBodyName' name='frmBodyName' cols='75' rows='20'>$fieldValue</textarea>";
            $formHTML .= "<input type='hidden' name='frmBodyItemID' value='$fieldID' />";
            break;
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
            $constraint = $bPublished ? ' AND i.status  = ' . SGL_STATUS_PUBLISHED : '';
            $query = "
                SELECT  ia.item_addition_id, itm.field_name, ia.addition
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
            $result = $dbh->query($query);

            $trans = &SGL_Translation::singleton();
            if (!DB::isError($result)) {
                $html = array();
                while (list($fieldID, $fieldName, $fieldValue)
                    = $result->fetchRow(DB_FETCHMODE_ORDERED)) {
                    $fieldValue = $trans->get($fieldValue, 'content', $language);
                    $html[$fieldName] = $this->generateItemOutput(
                        $fieldID, $fieldName, $fieldValue, $this->typeID);
                }
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
     * Retierves and returns an array containing an an Item's Meta and Data
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

        $trans = &SGL_Translation::singleton();
        while (list($fieldID, $fieldName, $fieldValue) = $result->fetchRow(DB_FETCHMODE_ORDERED)) {
            $fieldValue = $trans->get($fieldValue, 'content', $this->languageID);
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
        $aResult = SGL_Item::retrievePaginated($catID, $bPublished = true, $dataTypeID);
        $aArticleList = $aResult['data'];
        
        //  get most recent article, if array is non-empty,
        //  only if none has been passed from 'more articles' list
        if (count($aArticleList)) {
            if (!$mostRecentArticleID) {
                SGL_HTTP_Session::set('articleID', $aArticleList[0]['item_id']);
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
        if ((!$itemID) && (SGL_HTTP_Session::get('articleID'))) {
            $itemID = SGL_HTTP_Session::get('articleID');
        }
        if ($itemID) {
            $item = & new SGL_Item($itemID);
            if (!isset($language) || empty($language) ) {
                $language = SGL_Translation::getLangID();
            }            
            $ret = $item->preview($bPublished, $language);            
            if (!is_a($ret, 'PEAR_Error')) {
                $ret['creatorName'] = $item->creatorName;
                $ret['createdByID'] = $item->createdByID;
                $ret['startDate'] = $item->startDate;
                $ret['type'] = $item->type;
                return $ret;
            } else {               
                SGL::raiseError('No preview available at ' . __FILE__ . ', ' . __LINE__, 
                    SGL_ERROR_NODATA);
            }
        } else {
            return false;
        }
    }

    /**
     * Gets paginated list of articles.
     *
     * @access  public
     * @static
     * @param   int     $dataTypeID template ID of article, ie, news article, weather article, etc.
     * @param   string  $queryRange flag to indicate if results limited to specific category
     * @param   int     $catID      optional cat ID to limit results to
     * @param   int     $from       row ID offset for pagination
     * @param   string  $orderBy    column to sort on
     * @return  array   $aResult    returns array of article objects, pager data, and show page flag
     * @see     retrieveAll()
     */
    function retrievePaginated($catID, $bPublished = false, $dataTypeID = 1, 
        $queryRange = 'thisCategory', $from = '', $orderBy = 'last_updated')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        if (!is_numeric($catID) || !is_numeric($dataTypeID)) {
            SGL::raiseError('Wrong datatype passed to '  . __CLASS__ . '::' . 
                __FUNCTION__, SGL_ERROR_INVALIDARGS, PEAR_ERROR_DIE);
        }
        //  if published flag set, only return published articles
        $isPublishedClause = ($bPublished)? 
            ' AND i.status  = ' . SGL_STATUS_PUBLISHED :
            ' AND i.status  > ' . SGL_STATUS_DELETED ;

        //  if user only wants contents from current category, add where clause
        $rangeWhereClause   = ($queryRange == 'all')?'' : " AND i.category_id = $catID";
        $roleId = SGL_HTTP_Session::get('rid');
        
        //  dataTypeID 1 = all template types, otherwise only a specific one
        $typeWhereClause = ($dataTypeID > '1') ? " AND it.item_type_id = $dataTypeID" : '';     
        $query = "
            SELECT  i.item_id,
                    ia.addition,
                    u.username,
                    i.date_created,
                    i.start_date,
                    i.expiry_date
            FROM    {$conf['table']['item']} i, {$conf['table']['item_addition']} ia, 
                    {$conf['table']['item_type']} it, {$conf['table']['item_type_mapping']} itm, 
                    {$conf['table']['user']} u, {$conf['table']['category']} c
            WHERE   ia.item_type_mapping_id = itm.item_type_mapping_id
            AND     i.updated_by_id = u.usr_id
            AND     it.item_type_id  = itm.item_type_id
            AND     i.item_id = ia.item_id
            AND     i.item_type_id = it.item_type_id
            AND     itm.field_name = 'title'" .         /*  match item addition type, 'title'    */
            $typeWhereClause .                          //  match datatype
            $rangeWhereClause . 
            $isPublishedClause . "
            AND     i.category_id = c.category_id
            AND     $roleId NOT IN (COALESCE(c.perms, '-1'))
            ORDER BY i.$orderBy DESC
            ";
        $dbh = & SGL_DB::singleton();
        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'     => 'Sliding',
            'delta'    => 3,
            'perPage'  => $limit,
        );
        $aPagedData = SGL_DB::getPagedData($dbh, $query, $pagerOptions);

        $trans = &SGL_Translation::singleton();
        $languageID = SGL_Translation::getLangID();
        $trans->setLang($languageID);
        foreach ($aPagedData['data'] as $aKey => $aValues) {
            $aPagedData['data'][$aKey]['addition'] = $trans->get($aValues['addition'], 'content');   
        }
        return $aPagedData;
    }
}
?>
