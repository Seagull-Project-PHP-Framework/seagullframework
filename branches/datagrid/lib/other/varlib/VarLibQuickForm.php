<?php
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/Object.php';
require_once 'HTML/QuickForm/Renderer/ObjectFlexy.php';
require_once 'HTML/Template/Flexy.php';

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | SYSTEM LACINSKI 2.0                                                       |
// +---------------------------------------------------------------------------+
// |CompanyMgr.php                                                         |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005 Varico.com                                             |
// |                                                                           |
// | Author: Varico <varico@varico.com>                                        |
// +---------------------------------------------------------------------------+
// |                                                                           |
// |Varico Lincence                                                            |
// |                                                                           |
// +---------------------------------------------------------------------------+

/* VarLibQuickForm
 * This class extends basic QuickForm functions about functions which are
 * necessary in S�2.0
 *
 */

function dateRule($elementName,$elementValue)
{
    $validatedData = SGL_Output::formatDate2DB($elementValue);
    if (isset($validatedData))
    {
        return true;
    }
    return false;
}

class VarLibQuickForm extends HTML_QuickForm {

    function varLibQuickForm($formName = '', $method = 'post', $action = '',
                                    $target = '_self', $attributes = null) {
        parent::HTML_QuickForm($formName, $method, $action, $target, $attributes);
        $this->registerRule('date','function','dateRule');
    }
    
    /**
     * Performs the server side validation
     * @access    public
     * @since     1.0
     * @param $buttonArray it is array of buttons name which are in the template, not in quickform object
     * @return    boolean   true if no error found
     */
    function validate($buttonArray = array())
    {
        foreach($this->_elements as $id => $obj) {
            if (isset($obj->_type) && $obj->_type === 'submit' && isset($this->_submitValues[$obj->_attributes['name']])) {
                //return normal validate values
                return parent::validate();
            } 
        }
        
        foreach($buttonArray as $name) {
            if(isset($this->_submitValues[$name])) {
                return parent::validate();
            }
        }
        return false;
    } 

    /**
     * createSelectListFromDB
     * create Select field baso on DB query and add to form object
     * @param object $form
     * @param string $elementName
     * @param string $elementLabel
     * @param string $query
     * @param integer $selectedId set selected option
     * @param array $option
     * @param boolean $nullOption set null option like string --select--
     * @param integer $multiselect if greater then 0 then select has multiple = true and size=$multiselect
     */
    function createSelectListFromDB(& $form, $elementName, $elementLabel, $query,
                                    $selectedId = 0, $nullOption = true, $option = null, $multiselect = 0) {
         $dbh = & SGL_DB::singleton();
         $element = $form->createElement('select', $elementName, $elementLabel);
         $element->updateAttributes($option);
         if($nullOption) {
            $element->addOption('--select --', 0);
         }
         $select = $dbh->getAssoc($query);
         $element->loadArray($select);
         if($selectedId != 0) {
            $element->setSelected($selectedId);
         }

         if ($multiselect > 0) {
             $element->setMultiple(true);
             $element->setSize($multiselect);
        }

         $form->addElementWithHotKey($element);
    }

    /**
     * addElementWithHotKey
     * create access key for dialog elements
     * @param object $element QuickForm dialog element
     * @return void
     */
    function addElementWithHotKey($element) {
        $label = $element->getLabel();
        if (ereg("(\^[A-Za-z0-9]{1})", $label, $regs)) {
            $accesskey = $regs[0];
            $hotKey = str_replace('^', '', $accesskey);
            $label = str_replace($accesskey, '<SPAN class="strong">' . $hotKey . '</SPAN>', $label);
            $element->updateAttributes(array('accesskey' => $hotKey));
            $element->updateAttributes(array('title' => _t('hotkey') . ' + ' . strtoupper($hotKey)));
            $element->setLabel($label);
            $this->addElement($element);
        }
        else {
            $this->addElement($element);
        }
    }
}
?>