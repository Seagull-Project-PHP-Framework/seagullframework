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
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | BlockForm.php                                                             |
// +---------------------------------------------------------------------------+
// | Author: Gilles Laborderie <gillesl@users.sourceforge.net>                 |
// +---------------------------------------------------------------------------+
// $Id: BlockForm.php,v 1.11 2005/05/28 21:15:50 demian Exp $

require_once 'HTML/QuickForm.php';
require_once SGL_ENT_DIR . '/Section.php';

/**
 * Quickform Block wrapper class.
 *
 * @package block
 * @author  Gilles Laborderie <gillesl@users.sourceforge.net>
 * @version $Revision: 1.11 $
 * @since   PHP 4.1
 */


class BlockForm
{
    var $action;
    var $data;
    var $form;

    function BlockForm($action = '')
    {
        $this->action = $action;
        $this->form = & new HTML_QuickForm('frmBlock', 'POST');
        $sectionList = & new DataObjects_Section();
        $sectionList->whereAdd('parent_id = 0');        
        $sectionList->orderBy('section_id ASC');
        $result = $sectionList->find();
        if ($result > 0) {
            while ( $sectionList->fetch() ) {
                $sections[ $sectionList->section_id ] = $sectionList->title;
            }
        }
        $sections[0] = 'All sections';
        $this->sections = $sections;
    }

    function init( $data = null )
    {
        $this->data = $data;

        //  init data obj if coming from edit
        if ($this->action == 'edit' ) {
            $this->form->setDefaults( $this->data );
        } else {
            //  Set default form values
            $defaultValues['block[name]']         = null;
            $defaultValues['block[title]']        = null;
            $defaultValues['block[is_onleft]']    = 1;
            $defaultValues['block[is_enabled]']   = 0;
            $defaultValues['block[sections]']     = 0;
            $this->form->setDefaults( $defaultValues );
        }

        //  hidden fields
        if ($this->action ) {
            $this->form->addElement('hidden', 'action', $this->action);
        }
        //  Form Header
        $this->form->addElement('header', null, SGL_String::translate('Block Details') );
        // Field ID
        if ($this->action == 'edit' ) {
            // Create a label for ID
            $this->form->addElement('hidden', 'block[block_id]', $this->data['block[block_id]']);
            $this->form->addElement('static', 'block[id_label]', 
                SGL_String::translate('ID'), $this->data['block[block_id]']);
        }
        // Field name
        $this->form->addElement('text', 'block[name]', SGL_String::translate('Name') );
        // Field title
        $this->form->addElement('text', 'block[title]', SGL_String::translate('Title') );
        // Field title_class
        $this->form->addElement('text', 'block[title_class]', SGL_String::translate('Title class') );
        // Field bgnd_colour
        $this->form->addElement('text', 'block[body_class]', SGL_String::translate('Body class')) ;
        // Field sections
        $this->form->addElement('select', 'block[sections]', SGL_String::translate('Sections'), $this->sections );
        $select = &$this->form->getElement('block[sections]');
        $select->setMultiple(true);
        $select->setSize(15);
        // mark selected items
        if ($this->action == 'edit' ) {
          $select->setSelected($this->data['block[sections]']);
        }
        // Field position
        $this->form->addElement('select', 'block[is_onleft]', SGL_String::translate('Position'), array( '0' => SGL_String::translate('Right'), '1' => SGL_String::translate('Left')));
        if ($this->action == 'edit' ) {
            // Field blk_order
            $this->form->addElement('static', 'block[blk_order]', SGL_String::translate('Order'), $this->data['block[blk_order]']) ;
            // Field is_enabled
            $this->form->addElement('checkbox', 'block[is_enabled]', SGL_String::translate('Status'), SGL_String::translate('check to activate'));
        }
        // Rules
        if ($this->action == 'edit' ) {
            $this->form->registerRule( 'can_be_activated', 'function', 'classAvailable', $this );
            $this->form->addRule('block[is_enabled]', SGL_String::translate('You need to define a class for this block before activating it'), 'can_be_activated', 'function');
        }

        $this->form->addRule('block[name]', SGL_String::translate('You must enter a name for your block'), 'required');
        $this->form->addRule('block[title]', SGL_String::translate('You must enter a title for your block'), 'required');

        // Buttons
        $buttons[] = &HTML_QuickForm::createElement('submit', 'submitted', SGL_String::translate('Submit') );
        $buttons[] = &HTML_QuickForm::createElement('button', 'cancel', SGL_String::translate('Cancel'), 
            array( 'onClick' => "document.location.href='".SGL_URL::makeLink('list', 'block', 'block')."'" ) );
        $this->form->addGroup($buttons);

        //  apply filters
        $this->form->applyFilter('__ALL__', 'trim');

        return $this->form;
    }

    function classAvailable($element, $compareTo) 
    {
        if ($element) {
            $blockClass = $this->form->getElementValue('block[name]');
            @include_once SGL_BLK_DIR . '/' . $blockClass . '.php';
            return class_exists($blockClass);
        }
        return true;
    }

}
?>
