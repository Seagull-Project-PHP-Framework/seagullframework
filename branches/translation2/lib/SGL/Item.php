<?php
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | Item.php                                                                  |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005 Demian Turner                                          |
// |                                                                           |
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This library is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU Library General Public               |
// | License as published by the Free Software Foundation; either              |
// | version 2 of the License, or (at your option) any later version.          |
// |                                                                           |
// | This library is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU         |
// | Library General Public License for more details.                          |
// |                                                                           |
// | You should have received a copy of the GNU Library General Public         |
// | License along with this library; if not, write to the Free                |
// | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
// |                                                                           |
// +---------------------------------------------------------------------------+
// $Id

/**
 * Item class
 *
 * Basic methods for manipulating Item objects.
 *
 * @access  public
 * @author  Demian Turner <demian@phpkitchen.com>
 * @package SGL
 * @version $Revision: 1.12 $
 * @since   PHP 4.1
 */
class SGL_Item
{
    /**
     * Item ID
     * 
     * @access	public
     * @var		int
     */
    var $id;
    
    /**
     * User ID of user to last update item
     * 
     * @access	public
     * @var		int
     */
    var $lastUpdatedById;
    
    /**
     * Timestamp an Item was created
     * 
     * @access 	public
     * @var		mixed
     */
    var $dateCreated;
    
    /**
     * Timestamp of last update for an Item
     * 
     * @access	public
     * @var		mixed
     */
    var $lastUpdated;
    
    /**
     * Timestamp when an Item becomes available
     * 
     * @access	public
     * @var		mixed
     */
    var $startDate;
    
    /**
     * Timestamp when an Item expires
     * 
     * @access 	public
     * @var		mixed
     */
    var $expiryDate;
    
    /**
     * Item Type Name
     * 
     * @access	public
     * @var		string
     */
    var $type;
    
    /**
     * Item Type ID
     * 
     * @access	public
     * @var		int
     */
    var $typeID;
    
    /**
     * Category ID
     * 
     * @access 	public
     * @var		int
     */
    var $catID;
    
    /**
     * Status ID
     * 
     * @access 	pubic
     * @var		int
     */
    var $statusID;

    /**
     * Constructor
     * 
     * @access	public
     * @return	void
     */
    function SGL_Item($itemID = -1, $languageID = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->module = 'item';
        if ($itemID >= 0) {
            $this->_init($itemID, $languageID);
        }
    }

    /**
     * Retrieves an Item's Meta Data. Sets the corrisponding class variables.
     * 
     * @access	private
     * @param	int		$itemID		ItemID
     * @return	void
     */
    function _init($itemID, $languageID = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        $dbh = & SGL_DB::singleton();

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
                    FROM    {$conf['table']['item']} i, {$conf['table']['item_type']} it, {$conf['table']['user']} u
                    WHERE   it.item_type_id = i.item_type_id
                    AND     i.created_by_id = u.usr_id
                    AND     i.item_id = $itemID
                ";
        $result = $dbh->query($query);
        if (!DB::isError($result)) {
            $itemObj = $result->fetchRow();
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
     * @access 	public 
     * @return 	int		$id	Item ID
     */
    function addMetaItems()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $dbh = & SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF']; 

        $catID = $this->catID ? $this->catID : 1;        
        $id = $dbh->nextId($conf['table']['item']);
        $query = "  
            INSERT INTO {$conf['table']['item']}(
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
                $dbh->quote($this->dateCreated) . ", " .
                $dbh->quote($this->lastUpdated) . ", " .
                $dbh->quote($this->startDate) . ", " .
                $dbh->quote($this->expiryDate) . ",
                $this->typeID," .
                SGL_STATUS_FOR_APPROVAL . ",
                $catID, ".
                $dbh->quote($this->languages) ."
            )";
        $result = $dbh->query($query);
        return $id;
    }

    /**
     * Inserts Data Items into item_addtion table.
     * 
     * @access 	public 
     * @param	int		$parentID	Parent ID
     * @param	int		$itemID		Item ID
     * @param	mixed	$itemValue	Item Value
     * @return	void
     */
    function addDataItems($parentID, $itemID, $itemValue, $language)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $dbh = & SGL_DB::singleton();
        $trans = &SGL_Translation::singleton('admin');        
        $conf = & $GLOBALS['_SGL']['CONF']; 
                
        for ($x=0; $x < count($itemID); $x++) {
            $id = $dbh->nextId($conf['table']['item_addition']);
            $transID = $dbh->nextID($conf['table']['translation']);
            
            if ($itemValue[$x] == '') {
                $itemValue[$x] = SGL_String::translate('No other text entered');
            }

            //  profanity check
            $editedTxt = SGL_String::censor($itemValue[$x]);

            //  build strings array
            $strings[$language] = $editedTxt;
    
            //  insert into item_addition
            $query = "
                    INSERT INTO {$conf['table']['item_addition']} VALUES (
                        $id,
                        $parentID,
                        $itemID[$x],
                        $transID
                    )";
            $result = $dbh->query($query);
            unset($query);

            //  insert translation
            $trans->add($transID, 'content', $strings);
            unset($strings);
        }
    }

    /**
     * Inserts Item Body into item_addition table.
     * 
     * @access	public
     * @param	int		$parentID	Parent ID
     * @param	int		$itemID		Item ID
     * @param	mixed	$itemValue	Item Value
     * @return	void
     */
    function addBody($parentID, $itemID, $itemValue, $language)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $dbh = & SGL_DB::singleton();
        $trans = &SGL_Translation::singleton('admin');

        $conf = & $GLOBALS['_SGL']['CONF'];

        $id = $dbh->nextId($conf['table']['item_addition']);
        $transID = $dbh->nextID($conf['table']['translation']);

        if ($itemValue == '') {
            $itemValue = SGL_String::translate('No body text entered');
        }

        //  profanity check
        $editedTxt = SGL_String::censor($itemValue);
        
        $strings[$language] = $editedTxt;

        $query = "
            INSERT INTO {$conf['table']['item_addition']} VALUES (
                $id,
                $parentID,
                $itemID,
                $transID
            )";
        $result = $dbh->query($query);

        $trans->add($transID, 'content', $strings);
    }

    /**
     * Update Meta Items in item table.
     * 
     * @access	public
     * @return	voide
     */
    function updateMetaItems()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $dbh = & SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF'];
        
        $query = "  
            UPDATE {$conf['table']['item']} SET
                updated_by_id = $this->lastUpdatedById,
                last_updated = " . $dbh->quote($this->lastUpdated) . ",
                start_date = " . $dbh->quote($this->startDate) . ",
                expiry_date = " . $dbh->quote($this->expiryDate) . ",
                status = $this->statusID,
                category_id = $this->catID,
                languages = ". $dbh->quote($this->languages) . "
            WHERE item_id = $this->id
                 ";
        $result = $dbh->query($query);
    }

    /**
     * Update Data Items in item_addtiion table.
     * 
     * @access	public
     * @param	int		$itemID		Item ID
     * @param	mixed	$itemValue	Item Value
     * @return	void
     */
    function updateDataItems($itemID, $itemValue, $language)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $trans = &SGL_Translation::singleton();
        $transAdmin = &SGL_Translation::singleton('admin');

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

            foreach ($GLOBALS['_SGL']['INSTALLED_LANGUAGES'] as $key) {
                if ($key != $language) {
                    if ($content = $trans->get($itemID[$x], 'content', $key)) {
                        $strings[$key] =  $content;
                    }
                }
            }

            //  remove all translations for current string
            $transAdmin->remove($itemID[$x], 'content');
            
            //  add translations
            $transAdmin->add($itemID[$x], 'content', $strings);            
        }
    }

    /**
     * Update a Data Item's Body in item_addition
     * 
     * @access	public
     * @param	int		$itemID		Item ID
     * @param	mixed	$itemValue	Item Value
     * @return	void
     */
    function updateBody($itemID, $itemValue, $language)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $trans = &SGL_Translation::singleton();
        $transAdmin = &SGL_Translation::singleton('admin');
        
        if ($itemValue == '') {
            $itemValue = SGL_String::translate('No text entered');
        }   
        
        $itemValue = SGL_String::tidy($itemValue);

        //  profanity check
        $editedTxt = SGL_String::censor($itemValue);
        
        //  fetch current translation
        $strings[$language] = $trans->get($itemID, 'content', $language);
        
        //  merge translations
        if ($editedTxt !== $strings[$language]) {
            $strings[$language] = $editedTxt;
        }


        foreach ($GLOBALS['_SGL']['INSTALLED_LANGUAGES'] as $key) {
            if ($key != $language) {
                if ($content = $trans->get($itemID, 'content', $key)) {
                    $strings[$key] = $content;
                }
            }
        }

        //  remove all translations for current string
        $transAdmin->remove($itemID, 'content');
        
        //  add translations
        $transAdmin->add($itemID, 'content', $strings);            
    }

    /**
     * Deletes an Item from the item and item_addition table. If safe delete
     * is enabled only updates the items status to 0.
     * 
     * @access 	public
     * @param	array     $aItems	Hash of IDs to delete.
     * @return	void
     */
    function delete($aItems)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        $dbh = & SGL_DB::singleton();

        //  if safeDelete is enabled, just set item status to 0, don't delete
        if ($conf['site']['safeDelete']) {
            $sth = $dbh->prepare("  UPDATE {$conf['table']['item']}
                                    SET status = " . SGL_STATUS_DELETED . "
                                    WHERE item_id = ?");
            foreach ($aItems as $row) {
                $dbh->execute($sth, $row);
            }
        //  else delete the bugger
        } else {
            foreach ($aItems as $row) {
                $sql = "DELETE FROM {$conf['table']['item']} WHERE item_id = " . $row;
                $dbh->query($sql);
            }
            $trans = &SGL_Translation::singleton('admin');
            foreach ($aItems as $row) {
                //  fetch item translation ids
                $query = "SELECT * FROM item_addition WHERE item_id=$row";
                $additionTrans = $dbh->getAssoc($query);
                
                foreach ($additionTrans as $key => $values) {
                    $trans->remove($values->addition, 'content');   
                }

                $sql = "DELETE FROM item_addition WHERE item_id = " . $row;
                $dbh->query($sql);
            }
        }
    }

    /**
     * Builds a HTML form containing the data from the item_addition table. The
     * input types are built using the data in the item_type and
     * item_type_mapping tables.
     * 
     * @todo	Make return array to build form in template.
     * 
     * @access	public
     * @param	int		$itemID			Item ID
     * @return	mixed	$fieldsString	HTML Form
     */
    function getDynamicContent($itemID, $language)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $dbh = & SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF'];
        
        $query = "
            SELECT  ia.item_addition_id, itm.field_name, ia.addition, 
                    itm.field_type, itm.item_type_mapping_id
            FROM    {$conf['table']['item_addition']} ia, {$conf['table']['item_type']} it, {$conf['table']['item_type_mapping']} itm
            WHERE   ia.item_type_mapping_id = itm.item_type_mapping_id 
            AND     it.item_type_id  = itm.item_type_id    /*  match item type */
            AND     ia.item_id = $itemID
            ORDER BY itm.item_type_mapping_id
                ";
        $result = $dbh->query($query);

        $trans = &SGL_Translation::singleton();

        //  get language name
        $langID = str_replace('_', '-', $language);
        $availableLanguages = $GLOBALS['_SGL']['LANGUAGE'];
        $lang_name = ucfirst(substr(strstr($availableLanguages[$langID][0], '|'), 1));
        $languageName =  '('. $lang_name . ' - ' . $langID . ')';

        //  display dynamic form fields (changed default object output to standard array
        $fieldsString = '';
        while (list($fieldID, $fieldName, $fieldValue, $fieldType)
            = $result->fetchRow(DB_FETCHMODE_ORDERED)) {
            $fieldID = $fieldValue;
            $fieldValue = $trans->get($fieldValue, 'content', $language);
            $fieldsString .= "<tr>\n";
            $fieldsString .= '<th>' . ucfirst($fieldName) ." ". $languageName ."</th>\n";
            $fieldsString .= '<td>' . $this->generateFormFields(
                                      $fieldID, $fieldName, $fieldValue, $fieldType, $language) 
                                . "</td>\n";
            $fieldsString .= "</tr>\n";
        }
        return $fieldsString;
    }

    /**
     * Builds a HTML form with the input types built using the data in the
     * item_type and item_type_mapping tables.
     * 
     * @todo	Make return array to build form in template.
     * 
     * @access  public
     * @param   int   	$typeID			Item Type ID
     * @param   array   $aLanguages      Languages to build fields from
     * @return  mixed   $fieldsString	HTML Form
     */
    function getDynamicFields($typeID, $language)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!is_numeric($typeID)) {
            SGL::raiseError('Wrong datatype passed to '  . __CLASS__ . '::' . 
                __FUNCTION__, SGL_ERROR_INVALIDARGS, PEAR_ERROR_DIE);
        }
        //  get template specific form fields
        $dbh = & SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF'];
        
        $query = "
            SELECT  itm.item_type_mapping_id, itm.field_name, itm.field_type
            FROM    {$conf['table']['item_type_mapping']} itm
            WHERE   itm.item_type_id = $typeID
            ORDER BY itm.item_type_mapping_id
                    ";
        $result = $dbh->query($query);

        //  get language name
        $langID = str_replace('_', '-', $language);
        $availableLanguages = $GLOBALS['_SGL']['LANGUAGE'];
        $lang_name = ucfirst(substr(strstr($availableLanguages[$langID][0], '|'), 1));
        $languageName =  '('. $lang_name . ' - ' . $langID . ')';

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
        return $fieldsString;
    }

    /**
     * Generates the form fields from the item_type_mapping table for the
     * methods getDynamicContent() and getDynamicFields.
     * 
     * @access	public
     * @param	int		$fieldID	Field ID
     * @param	string	$fieldName	Field Name
     * @param	mixed	$fieldValue	Field Value
     * @param	int		$fieldType	Field Type
     * @return	mixed	$formHTML	HTML Form
     */
    function generateFormFields($fieldID, $fieldName, $fieldValue='', $fieldType, $language)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        switch($fieldType) {
        case 0:     // field type = single line
            $formHTML = "<input type='text' name='frmFieldName[]' value='$fieldValue' size='75'>";
            $formHTML .= "<input type='hidden' name='frmDataItemID[]' value='$fieldID' />";
            break;

        case 1:     // field type = textarea paragraph
            $formHTML = "<textarea name='frmFieldName[]' rows='10' cols='60'>$fieldValue</textarea>";
            $formHTML .= "<input type='hidden' name='frmDataItemID[]' value='$fieldID' />";
            break;

        case 2:     // field type = html paragraph
            $formHTML = "<textarea id='frmBodyName' cols='75' rows='20' name='frmBodyName'>$fieldValue</textarea>";
            $formHTML .= "<input type='hidden' name='frmBodyItemID' value='$fieldID' />";
            break;
        }
        return $formHTML;
    }

    /**	
     * Updates an Items Status (delete, approve, publish, archive) in the item
     * table.
     * 
     * @access	public
     * @param	string	Item Status
     * @return	void
     */
    function changeStatus($status)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $dbh = & SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF'];        
        
        switch($status) {
        case 'delete':
               //   mark item as deleted
            $query = "
                        UPDATE  {$conf['table']['item']}
                        SET     status = " . SGL_STATUS_DELETED . "
                        WHERE   item_id = $this->id
                        ";
            break;

        case 'approve':
               //   mark item as approved
            $query = "
                        UPDATE  {$conf['table']['item']}
                        SET     status = " . SGL_STATUS_APPROVED . "
                        WHERE   item_id = $this->id
                        ";
            break;

        case 'publish':
               //   mark item as published
            $query = "
                        UPDATE  {$conf['table']['item']}
                        SET     status = " . SGL_STATUS_PUBLISHED . "
                        WHERE   item_id = $this->id
                        ";
            break;

        case 'archive':
               //   mark item as published
            $query = "
                        UPDATE  {$conf['table']['item']}
                        SET     status = " . SGL_STATUS_ARCHIVED . "
                        WHERE   item_id = $this->id
                        ";
            break;
        }
        $result = $dbh->query($query);
    }

    /**
     * Retierves an Item's Meta and Data items and generates the output using
     * the method generateItemOutput().
     * 
     * @access	public
     * @param	boolean	$bPublished	Item published
     * @return	mixed	$html		HTML Output
     */
    function preview($bPublished = false, $language = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (!is_null($language)) {
            $dbh = & SGL_DB::singleton();
            $conf = & $GLOBALS['_SGL']['CONF'];
        
            $constraint = $bPublished ? ' AND i.status  = ' . SGL_STATUS_PUBLISHED : '';
            $query = "
                SELECT  ia.item_addition_id, itm.field_name, ia.addition
                FROM    {$conf['table']['item']} i, {$conf['table']['item_addition']} ia, 
                        {$conf['table']['item_type']} it, {$conf['table']['item_type_mapping']} itm
                WHERE   ia.item_type_mapping_id = itm.item_type_mapping_id 
                AND     it.item_type_id  = itm.item_type_id    /*  match item type */
                AND     i.item_id = ia.item_id
                AND     ia.item_id = $this->id
                $constraint
                ORDER BY itm.item_type_mapping_id
                ";
            $result = $dbh->query($query);

            $trans = &SGL_Translation::singleton();
            $trans->setLang($language);
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
     * @return  array	$html          
     */
    function manualPreview()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $dbh = & SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF'];
        
        $query = "
            SELECT  ia.item_addition_id, itm.field_name, ia.addition, itm.item_type_mapping_id
            FROM    {$conf['table']['item_addition']} ia, {$conf['table']['item_type']} it, {$conf['table']['item_type_mapping']} itm
            WHERE   ia.item_type_mapping_id = itm.item_type_mapping_id 
            AND     it.item_type_id  = itm.item_type_id    /*  match item type */
            AND     ia.item_id = $this->id
            ORDER BY itm.item_type_mapping_id
                ";
        $result = $dbh->query($query);

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
     * @access	public
     * @param	int		$fieldID		Field ID
     * @param	string	$fieldName		Field Name
     * @param	mixed	$fieldValue 	Field Value
     * @param	int		$itemTypeID		Item Type ID
     * @return	array	$outputHTML		
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
     * @access	public
     * @param	string	$attributeName
     * @param	mixed	$attributeValue
     * @return	void
     */
    function set($attributeName, $attributeValue)
    {
        $this->$attributeName = $attributeValue;
    }

    /**
     * Retrieves an Item's Meta Data value
     * 
     * @access	public
     * @param	string	$attribute
     * @return	mixed	$this->attribute
     */
    function get($attribute)
    {
        return $this->$attribute;
    }

    /**
     * Retrieve a list of Items by CatID
     * 
     * @access	public
     * @param	int		$catID
     * @param	int		$dataTypeID
     * @param	int		$mostRecentArticleID
     * @return	array	$aArticleList			
     * @see		retrievePaginated()
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
     * @access	public
     * @param	int		$itemID		Item ID
     * @param	boolean	$bPublished	Item Published
     * @return	array	$ret		Array containg an Item's Details or false
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
     * @param   int     $dataTypeID template ID of article, ie, new article, weather article, etc.
     * @param   string  $queryRange flag to indicate if results limited to specific category
     * @param   int     $catID      optional cat ID to limit results to
     * @param   int     $from       row ID offset for pagination
     * @return  array   $aResult    returns array of article objects, pager data, and show page flag
     * @see     retrieveAll()
     */
    function retrievePaginated($catID, $bPublished = false, $dataTypeID = 1, 
        $queryRange = 'thisCategory', $from = '')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
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
            ORDER BY i.last_updated DESC
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