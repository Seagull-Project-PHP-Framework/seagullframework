<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | ContentTypeMgr.php                                                        |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005 Demian Turner                                          |
// |                                                                           |
// | Author: Alexander J. Tarachanowicz II <ajt@localhype.net>                 |
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
// $Id: ContentTypeMgr.php,v 1.2 2005/02/26 21:02:21 demian Exp $

/**
 * Content Type Manager
 *
 * @access public
 * @package publisher
 * @author  Alexander J. Tarachanowicz II <ajt@localhype.net>
 * @version $Revision: 1.2 $
 * @since   PHP 4.1
 */
class ContentTypeMgr extends SGL_Manager
{
/**
 * Field Types
 * 
 * @access  public
 * @var     array   
 */
var $fieldTypes;

    /**
     * Constructor
     * 
     * @access  public
     * @return  void
     */
    function ContentTypeMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->module       = 'publisher';
        $this->pageTitle    = 'Content Type Manager';
        $this->template     = 'contentTypeList.html';

        $this->fieldTypes   = array('0' => 'single line', '1' => 'textarea', '2' => 'HTML textarea');

        $this->_aActionsMapping =  array(
            'add'       => array('add'), 
            'insert'    => array('insert', 'redirectToDefault'),
            'edit'      => array('edit'), 
            'update'    => array('update', 'redirectToDefault'), 
            'delete'    => array('delete', 'redirectToDefault'), 
            'list'      => array('list'), 
        );
    }

    /**
     * Validate
     * 
     * @access  public
     * @param   object  $req    SGL_Request
     * @param   object  $input  SGL_Output
     * @return  void
     * @see     lib/SGL/SGL_Controller.php
     */
    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template    = $this->template;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->aDelete     = $req->get('frmDelete');
        $input->submit      = $req->get('submitted');
        $input->type        = $req->get('type');
        $input->contentTypeID = $req->get('frmContentTypeID');
        
        if (isset($input->submit) && 
			($input->action == 'add' || $input->action == 'insert' || $input->action == 'update')) 
		{            
            if (empty($input->type['item_type_name'])) {
                $aErrors['name'] = 'content type name';      
            }
        }

        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }
    }

    /**
     * Creates array used to create field name/type form. 
     * 
     * @access  private
     * @param   object  $input  
     * @param   object  $output 
     * @return  void     
     */
    function _add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'contentTypeAdd.html';
        for ($x = 1; $x <= $input->type['fields']; $x++) {
            $output->totalFields[$x] = $x;
        }           
        $output->fieldTypes = $this->fieldTypes;
    }

    /**
     * Inserts Item Type into item_type table and Item Type fields into item_type_mapping table. 
     * 
     * @access  private
     * @param   object  $input  
     * @param   object  $output 
     * @return  void     
     */
    function _insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        //  insert item type into item_type table.
        $dbh = &SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF'];        
        
        $item_type_id   = $dbh->nextId($conf['table']['item_type']);
        $item_type_name = $input->type['item_type_name']; 
        $query = "INSERT INTO {$conf['table']['item_type']} (item_type_id, item_type_name) VALUES ($item_type_id, '". $item_type_name . "')";
        $result = $dbh->query($query);         
        if (DB::isError($result)) {
        SGL::raiseError('Error inserting item type name exiting ...', 
            SGL_ERROR_NODATA, PEAR_ERROR_DIE);
        } else {
            $nameInserted = true;
        }
                
        //  insert item type fields into item_type_mapping table.       
        foreach ($input->type['field_name'] as $nKey => $nValue) {
            $field_type = $input->type['field_type'][$nKey];
            $item_type_mapping_id = $dbh->nextId($conf['table']['item_type_mapping']);
            $subquery = "INSERT INTO {$conf['table']['item_type_mapping']} (item_type_mapping_id, item_type_id, field_name, field_type) 
                         VALUES ($item_type_mapping_id, $item_type_id, '" . $nValue . "', $field_type)";
            $subresult = $dbh->query($subquery);
            print_r($subresult);
            if (DB::isError($subresult)) {
            SGL::raiseError('Error inserting item type fields exiting ...', 
                SGL_ERROR_NODATA, PEAR_ERROR_DIE);
            } else { 
                $fieldsInserted = true;
            }
        }
        SGL::raiseMsg('content type has successfully been added');
    }

    /**
     * Retrieves data for selected Item Type . 
     * 
     * @access  private
     * @param   object  $input  
     * @param   object  $output 
     * @return  void     
     */
    function _edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'contentTypeEdit.html';
        $dbh = &SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF'];       
                
        $query = "SELECT    itm.item_type_id,  it.item_type_name, itm.item_type_mapping_id, itm.field_name, itm.field_type
                  FROM      {$conf['table']['item_type_mapping']} itm, {$conf['table']['item_type']} it                   
                  WHERE     itm.item_type_id = $input->contentTypeID
                  AND       it.item_type_id = $input->contentTypeID";
        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'     => 'Sliding',
            'delta'    => 3,
            'perPage'  => $limit,
        );
        $aPagedData = SGL_DB::getPagedData($dbh, $query, $pagerOptions);

        foreach ($aPagedData['data'] as $aKey => $aValues) {
            foreach ($aValues as $key => $value) {                              
                switch ($key) {
                    
                case 'item_type_id':
                    $item_type_id = $value;
                    break;
                case 'item_type_name':
                    $data[$key] = $value; 
                    break;
                case 'item_type_mapping_id':
                    $item_type_mapping_id = $value;
                    break;
                case 'field_name':
                    $field_name = $value;                       
                    break;
                break;                    
                case 'field_type':
                    $data['fields'][$item_type_mapping_id]['field_name'] = $field_name;
                    $data['fields'][$item_type_mapping_id]['field_type'] = $value;
                    $data['fields'][$item_type_mapping_id]['item_type_mapping_id'] = $item_type_mapping_id;                        
                    break;
                }
                
            }
        }
        $output->type = $data;
        $output->fieldTypes = $this->fieldTypes;
    }

    /**
     * Updates Item Type Name on item_type table and Item Type fields on item_type_mapping table. 
     * 
     * @access  private
     * @param   object  $input  
     * @param   object  $output 
     * @return  void     
     */
    function _update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $dbh = &SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF'];               
        
        //  update item type name
        if ($input->type['item_type_name'] !== $input->type['item_type_name_original']) {
            $query = "UPDATE {$conf['table']['item_type']} SET item_type_name='" . $input->type['item_type_name'] . "'
                      WHERE item_type_id=" . $input->contentTypeID;
            $result = $dbh->query($query);
            if (DB::isError($result)) {
                SGL::raiseError('Error updating item type name exiting ...', 
                    SGL_ERROR_NODATA, PEAR_ERROR_DIE);
            }
            unset($query);                
        }
                       
        //  update item type fields

        foreach ($input->type['fields'] as $aKey => $aValue) {
            //  field name clause
            if ($aValue['field_name'] !== $aValue['field_name_original']) {
                $fieldNameClause = "field_name='" . $aValue['field_name'] . "'";   
            }
            
            //  field type clause
            if ($aValue['field_type'] !== $aValue['field_type_original']) {
                $fieldTypeClause = "field_type=". $aValue['field_type'];
            }

            //  build query
            if (!empty($fieldNameClause) && isset($fieldTypeClause)) {   //  update field_name & field_type
                $query = "UPDATE {$conf['table']['item_type_mapping']} SET $fieldNameClause, $fieldTypeClause WHERE item_type_mapping_id=" . $aKey;
                $result = $dbh->query($query);                
            } else if (isset($fieldNameClause)) {                       //  update only field_name
                $query = "UPDATE {$conf['table']['item_type_mapping']} SET $fieldNameClause WHERE item_type_mapping_id=" . $aKey;
                $result = $dbh->query($query);                                
            } else if (isset($fieldTypeClause)) {                       //  update only field_type
                $query = "UPDATE {$conf['table']['item_type_mapping']} SET $fieldTypeClause WHERE item_type_mapping_id=" . $aKey;
                $result = $dbh->query($query);                                
            }

            if (DB::isError($result)) { 
                SGL::raiseError('Error updating item type fields exiting ...', 
                    SGL_ERROR_NODATA, PEAR_ERROR_DIE);                
            }
            unset($query);                           
        }
        SGL::raiseMsg('content type has successfully been updated');
    }

    /**
     * Retrieves all Item Types w/ field names and types. 
     * 
     * @access  private
     * @param   object  $input  
     * @param   object  $output 
     * @return  void     
     */
    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $dbh = & SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF'];

        $query = "SELECT it.item_type_id, it.item_type_name, itm.item_type_mapping_id, itm.field_name, itm.field_type
                  FROM {$conf['table']['item_type']} it, {$conf['table']['item_type_mapping']} itm
                  WHERE itm.item_type_id = it.item_type_id";  
              
        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'     => 'Sliding',
            'delta'    => 3,
            'perPage'  => $limit,
        );
        $aPagedData = SGL_DB::getPagedData($dbh, $query, $pagerOptions);
        $output->aPagedData = $aPagedData;       
        foreach ($aPagedData['data'] as $aKey => $aValues) {
            foreach ($aValues as $key => $value) {                              
                switch ($key) {
                
                case 'item_type_mapping_id':
                    $item_type_mapping_id = $value;
                    break;
                    
                case 'item_type_id':
                    $item_type_id = $value;
                    $data[$item_type_id]['item_type_id'] = $value;
                    break;
                    
                case 'item_type_name':
                    $data[$item_type_id][$key] = $value; 
                    break;
                    
                case 'field_name':
                    $field_name = $value;                       
                    break;
                    
                case 'field_type':
                    $data[$item_type_id]['fields'][$item_type_mapping_id] = array($field_name => $this->fieldTypes[$value]);
                    break;
                }
                
            }

        }
        //  unset data array
        $output->aPagedData['data'] = array();

        //  set data array
        $output->aPagedData['data'] = $data;
                                     
        //  total number of fields allowed
        $conf = &$GLOBALS['_SGL']['CONF'];      
        $totalFields = $conf['ContentTypeMgr']['totalFields'];
        for($x = 1; $x <= $totalFields; $x++) {
            $output->totalFields[$x] = $x;
        }
    }

    /**
     * Deletes selected Item Type from item_type table and Item Type fields from item_type_mapping table. 
     * 
     * @access  private
     * @param   object  $input  
     * @param   object  $output 
     * @return  void
     */
    function _delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];        

        if (is_array($input->aDelete)) {
            $dbh = & SGL_DB::singleton();
            
            foreach ($input->aDelete as $index => $itemTypeId) {
                //  delete item type from item_type table
                $query = "DELETE FROM {$conf['table']['item_type']} WHERE item_type_id=$itemTypeId"; 
                if (DB::isError($dbh->query($query))) {
                    SGL::raiseError('Error updating item type name exiting ...', 
                        SGL_ERROR_NODATA, PEAR_ERROR_DIE);
                }
                unset($query);
                //  delete item type fields from item_type_mapping
                $query = "DELETE FROM {$conf['table']['item_type_mapping']} WHERE item_type_id=$itemTypeId"; 
                if (DB::isError($dbh->query($query))) {
                    SGL::raiseError('Error updating item type name exiting ...', 
                        SGL_ERROR_NODATA, PEAR_ERROR_DIE);
                }
                SGL::raiseMsg('content(s) type has successfully been deleted');                
            }
        } else {
            SGL::raiseError('Incorrect parameter passed to ' . 
                __CLASS__ . '::' . __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
    }
}
?>
