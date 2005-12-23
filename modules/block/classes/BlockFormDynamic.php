<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2004, Demian Turner                                         |
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
// | Seagull 0.3                                                               |
// +---------------------------------------------------------------------------+
// | BlockForm.php                                                             |
// +---------------------------------------------------------------------------+
// | Author: Gilles Laborderie <gillesl@users.sourceforge.net>                 |
// +---------------------------------------------------------------------------+
require_once 'HTML/QuickForm.php';
class BlockFormDynamic
{
    var $action;
    var $data;
    var $form;

    function BlockFormDynamic($action = '')
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        $dbh = & SGL_DB::singleton();

        $this->action = $action;
        $this->form = & new HTML_QuickForm('frmBlock', 'POST');
        require_once 'DB/DataObject.php';
        $sectionList = DB_DataObject::factory('Section');
        $sectionList->whereAdd('parent_id = 0');
        $sectionList->orderBy('section_id ASC');
        $result = $sectionList->find();
        if ($result > 0) {
            while ( $sectionList->fetch() ) {
                if (is_numeric($sectionList->title)) {
                    $trans = & SGL_Translation::singleton();
                    $trans->setLang(SGL_Translation::getLangID());
                    $sections[ $sectionList->section_id ] = $trans->get($sectionList->title, 'nav', $languageID);   
                } else {
                    $sections[ $sectionList->section_id ] = $sectionList->title;
                }
            }
        }
        $sections[0] = 'All sections';
        $this->sections = $sections;
        $query = "SELECT role_id, name FROM {$conf['table']['role']}";
        $res = & $dbh->getAll($query);
        $roles = array();
        $roles[SGL_ANY_ROLE] = SGL_String::translate('All roles');
        foreach ($res as $key => $value) {
            $roles[$value->role_id] = $value->name;
        }
        $this->roles = $roles;
    }

    function init( $data = null )
    {
        include SGL_DAT_DIR . '/ary.blocksNames.php';
        $this->data = $data;

        //  init data obj if coming from edit
        if( $this->action == 'edit' ) {
            $this->form->setDefaults( $this->data );
        } else {
            //  Set default form values
            $defaultValues['block[name]']         = 'DbHtmlBlock';
            $defaultValues['block[title]']        = null;
            $defaultValues['block[content]']      = null;
            $defaultValues['block[title_class]']  = '';
            $defaultValues['block[body_class]']   = '';
            $defaultValues['block[is_enabled]']   = 1;
            $defaultValues['block[sections]']     = 0;
            $defaultValues['block[roles]']        = SGL_ANY_ROLE;
            $this->form->setDefaults( $defaultValues );
        }
        //  hidden fields
        if( $this->action ) {
            $this->form->addElement('hidden', 'action', $this->action);
        }
        //  Form Header
        $this->form->addElement('header', null, SGL_Output::translate('Block Details') );
        // Field ID
        if( $this->action == 'edit' ) {
            // Create a label for ID
            $this->form->addElement('hidden', 'block[block_id]', $this->data['block[block_id]']);
            $this->form->addElement('static', 'block[id_label]',
                SGL_Output::translate('ID'), $this->data['block[block_id]']);
        }

        // Field name
        $this->form->addElement('text', 'block[name]', SGL_Output::translate('Name') );
        // Field title
        $this->form->addElement('text', 'block[title]', SGL_Output::translate('Title') );
        // Field title_class
        $this->form->addElement('text', 'block[title_class]', SGL_Output::translate('Title class') );
        // Field bgnd_colour
        $this->form->addElement('text', 'block[body_class]', SGL_Output::translate('Body class')) ;
        // Field position
        $this->form->addElement('select', 'block[position]', SGL_String::translate('Position'), $aBlocksNames);

        if( $this->action == 'edit' ) {
            // Field blk_order
            $this->form->addElement('static', 'block[blk_order]', SGL_Output::translate('Order'), $this->data['block[blk_order]']) ;
        }
        // Field is_enabled
        $this->form->addElement('checkbox', 'block[is_enabled]', SGL_Output::translate('Status'), SGL_Output::translate('check to activate'));

        // Field sections
        $this->form->addElement('select', 'block[sections]', SGL_Output::translate('Sections'), $this->sections );
        $select = &$this->form->getElement('block[sections]');
        $select->setMultiple(true);
        $select->setSize(15);
        // Field roles

        $this->form->addElement('select', 'block[roles]', SGL_String::translate('Can view'), $this->roles);
        $roles = &$this->form->getElement('block[roles]');
        $roles->setMultiple(true);
        $roles->setSize(5);
        $select = &$this->form->getElement('block[sections]');
        $select->setMultiple(true);
        $select->setSize(15);

        // Field Content
        $this->form->addElement('textarea','block[content]', NULL,
                        array('id' => 'frmBodyName', 'rows' => '20','cols' => '90' )) ;
        // Rules
        if( $this->action == 'edit' ) {
            $this->form->registerRule( 'can_be_activated', 'function', 'classAvailable', $this );
            $this->form->addRule( 'block[is_enabled]', SGL_Output::translate('You need to define a class for this block before activating it'), 'can_be_activated', 'function');
        }

        $this->form->addRule('block[name]', SGL_Output::translate('You must enter a name for your block'), 'required');
        $this->form->addRule('block[title]', SGL_Output::translate('You must enter a title for your block'), 'required');

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
