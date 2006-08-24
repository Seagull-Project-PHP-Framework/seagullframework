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
// | ContentTypeMgr.php                                                        |
// +---------------------------------------------------------------------------+
// | Author: Alexander J. Tarachanowicz II <ajt@localhype.net>                 |
// +---------------------------------------------------------------------------+
// $Id: ContentTypeMgr.php,v 1.2 2005/02/26 21:02:21 demian Exp $

require_once SGL_MOD_DIR  . '/publisher/classes/DA_Publisher.php';

/**
 * Content Type Manager
 *
 * @access public
 * @package publisher
 * @author  Alexander J. Tarachanowicz II <ajt@localhype.net>
 * @version $Revision: 1.2 $
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
        parent::SGL_Manager();

        $this->pageTitle    = 'Content Type Manager';
        $this->template     = 'contentTypeList.html';
        $this->da = &DA_Publisher::singleton();

        $this->fieldTypes   = array(
            '0' => 'single line', 
            '1' => 'textarea', 
            '2' => 'HTML textarea',
            '3' => 'Yes / No',
            '4' => 'DateTime');

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
        $input->submitted   = $req->get('submitted');
        $input->type        = $req->get('type');
        $input->contentTypeID = $req->get('frmContentTypeID');

        if (isset($input->submitted) &&
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

    function display(&$output)
    {
        //  build total fields combobox, check if num fields value passed from
        //  list screen
        $totalFields = (isset($output->type['fields'])
                &&  is_scalar($output->type['fields']))
            ? $output->type['fields']
            : $this->conf['ContentTypeMgr']['totalFields'];
        for ($x = 1; $x <= $totalFields; $x++) {
            $output->totalFields[$x] = $x;
        }
        if ($output->action == 'add') {
            $output->type['fields'] = $output->totalFields;
        }
        $output->fieldTypes = $this->fieldTypes;
    }

    /**
     * Creates array used to create field name/type form.
     *
     * @access  private
     * @param   object  $input
     * @param   object  $output
     * @return  void
     */
    function _cmd_add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'contentTypeEdit.html';
        $output->formAction = 'insert';
    }

    /**
     * Inserts Item Type into item_type table and Item Type fields into
     * item_type_mapping table.
     *
     * @access  private
     * @param   object  $input
     * @param   object  $output
     * @return  void
     */
    function _cmd_insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  insert item type into item_type table.
        $itemTypeId = $this->da->addItemType($input->type['item_type_name']);
        if (PEAR::isError($itemTypeId)) {
            SGL::raiseError('There was a problem inserting the content type',
                SGL_ERROR_NOAFFECTEDROWS);
            return false;
        }

        //  insert item type fields into item_type_mapping table.
        foreach ($input->type['fields'] as $attributeId => $aItemAttributes) {
      		$ok = $this->da->addItemAttributes($itemTypeId, $aItemAttributes['field_name'],
      		    $aItemAttributes['field_type']);
            if (PEAR::isError($ok)) {
	            SGL::raiseError('There was a problem updating the content attributes',
	                SGL_ERROR_NOAFFECTEDROWS);
	            return false;
            }
        }
        SGL::raiseMsg('content type has successfully been added', true, SGL_MESSAGE_INFO);
    }

    /**
     * Retrieves data for selected Item Type .
     *
     * @access  private
     * @param   object  $input
     * @param   object  $output
     * @return  void
     */
    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'contentTypeEdit.html';
        $output->formAction = 'update';
        $aRes = $this->da->getItemAttributesByItemId($input->contentTypeID);

        $data = array();
        foreach ($aRes as $aValues) {
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

                case 'field_type':
                    $data['fields'][$item_type_mapping_id]['field_name'] = $field_name;
                    $data['fields'][$item_type_mapping_id]['field_type'] = $value;
                    break;
                }
            }
        }
        $output->type = $data;
    }

    /**
     * Updates Item Type Name on item_type table and Item Type fields on item_type_mapping table.
     *
     * @access  private
     * @param   object  $input
     * @param   object  $output
     * @return  void
     */
    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  check for item type name change
        if ($input->type['item_type_name'] != $input->type['item_type_name_original']) {

            $ok = $this->da->updateItemTypeName($input->contentTypeID,
                $input->type['item_type_name']);
            if (PEAR::isError($ok)) {
	            SGL::raiseError('There was a problem updating the content type name',
	                SGL_ERROR_NOAFFECTEDROWS);
	            return false;
            }
        }
        //  update item type fields
        foreach ($input->type['fields'] as $attributeId => $aItemAttributes) {

      		$ok = $this->da->updateItemAttributes($attributeId, $aItemAttributes);
            if (PEAR::isError($ok)) {
	            SGL::raiseError('There was a problem updating the content attributes',
	                SGL_ERROR_NOAFFECTEDROWS);
	            return false;
            }
        }
        SGL::raiseMsg('content type has successfully been updated', true, SGL_MESSAGE_INFO);
    }

    /**
     * Retrieves all Item Types w/ field names and types.
     *
     * @access  private
     * @param   object  $input
     * @param   object  $output
     * @return  void
     */
    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aPagedData = $this->da->getItemAttributesByItemId(null, $paginated = true);

        $data = array();
        foreach ($aPagedData['data'] as $aValues) {
            foreach ($aValues as $key => $value) {
                switch ($key) {

                case 'item_type_id':
                    $item_type_id = $value;
                    $data[$item_type_id]['item_type_id'] = $value;
                    break;

                case 'item_type_name':
                    $data[$item_type_id][$key] = $value;
                    break;

                case 'item_type_mapping_id':
                    $item_type_mapping_id = $value;
                    break;

                case 'field_name':
                    $field_name = $value;
                    break;

                case 'field_type':
                    $field_type = $this->fieldTypes[$value];
                    break;
                case 'is_translateable':
                    $isTranslateable = ($value === 0) ? 'no' : 'yes';
                    $field = array(
                        'name'      => $field_name,
                        'type'      => $field_type,
                        'isTranslateable' => $isTranslateable);
//                        $field_name => $this->fieldTypes[$value],
                    $data[$item_type_id]['fields'][$item_type_mapping_id] = $field;

                }
            }
        }
        //  unset data array
        unset($aPagedData['data']);

        //  set data array
        $aPagedData['data'] = $data;
        $output->aPagedData = $aPagedData;
    }

    /**
     * Deletes selected Item Type from item_type table and Item Type fields from item_type_mapping table.
     *
     * @access  private
     * @param   object  $input
     * @param   object  $output
     * @return  void
     */
    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (is_array($input->aDelete)) {

            foreach ($input->aDelete as $itemTypeId) {

            	//  delete item type from item_type
				$ok = $this->da->deleteItemTypeById($itemTypeId);
                if (PEAR::isError($ok)) {
		            SGL::raiseError('There was a problem deleting the content type',
		                SGL_ERROR_NOAFFECTEDROWS);
		            return false;
                }

                //  delete item type fields from item_type_mapping
				$ok = $this->da->deleteItemAttributesByItemTypeId($itemTypeId);
                if (PEAR::isError($ok)) {
		            SGL::raiseError('There was a problem deleting the content attributes',
		                SGL_ERROR_NOAFFECTEDROWS);
		            return false;
                }
            }
			SGL::raiseMsg('content type(s) has successfully been deleted', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('Incorrect parameter passed to ' .
                __CLASS__ . '::' . __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
    }
}
?>
