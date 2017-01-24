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
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | ContactUsAdminMgr.php                                                     |
// +---------------------------------------------------------------------------+
// | Author: Dmitri Lakachauskis <dmitri@telenet.lv>                           |
// +---------------------------------------------------------------------------+

/**
 * Administration controller for contactus module.
 *
 * @package contactus
 * @author  Dmitri Lakachauskis <dmitri@telenet.lv>
 */
class ContactUsAdminMgr extends SGL_Manager 
{
    function ContactUsAdminMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();
        
        $this->module    = 'contactus';
        $this->pageTitle = 'Contact us\' manager';
        $this->template  = 'contactusList.html';
        
        $this->_aActionsMapping = array(
            'list'   => array('list'),
            'view'   => array('view'),
            'delete' => array('delete', 'redirectToDefault'),
        );
    }
    
    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $this->validated       = true;
        $input->error          = array();
        $input->module         = $this->module;
        $input->pageTitle      = $this->pageTitle;
        $input->template       = $this->template;
        $input->masterTemplate = $this->masterTemplate;
        $input->action         = $req->get('action') ? $req->get('action') : 'list';
        
        // list
        list($input->sortBy, $input->sortOrder)
            = $this->getSortParams($req->get('sortBy'), $req->get('sortOrder'));
        $input->pageID = $req->get('pageID') ? $req->get('pageID') : 1;
        
        // view
        $input->id = $req->get('id');
        
        // delete
        $input->aDelete = $req->get('frmDelete');
    }
    
    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        if ('list' == $output->action) {
            $output->addOnLoadEvent("switchRowColorOnHover()");
            $output->sortOrderDisplay = ('asc' == $output->sortOrder) ? 'desc' : 'asc';
        }
    }
    
    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $query = "
            SELECT    contact_us_id, first_name, last_name, email,
                      enquiry_type, user_comment, logtime
            FROM      {$this->conf['table']['contact_us']}
            ORDER BY  {$input->sortBy} {$input->sortOrder}
        ";
        
        // pager params
        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'     => 'Sliding',
            'delta'    => 3,
            'perPage'  => $limit,
        );
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);
        if (!empty($aPagedData['data'])) {
            $aTypes = SGL_Output::translate('aContactType');
            foreach ($aPagedData['data'] as $key => $aValue) {
                $aValue['enquiry_type'] = $aTypes[$aValue['enquiry_type']];
                $aPagedData['data'][$key] = $aValue;
            }
            $output->aPagedData = $aPagedData;
            $output->pager      = $aPagedData['totalItems'] > $limit;
            $output->{'sort_' . $input->sortBy} = true;
        }
    }
    
    function _cmd_view(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $aTypes = SGL_Output::translate('aContactType');

        require_once 'DB/DataObject.php';
        $oItem = &DB_DataObject::factory($this->conf['table']['contact_us']);
        $oItem->get($input->id);
        $oItem->user_comment = nl2br($oItem->user_comment);
        $oItem->enquiry_type = $aTypes[$oItem->enquiry_type];
        
        $aItem = (array)$oItem;
        $aItem['logtime'] = SGL_Output::formatDatePretty($aItem['logtime']);

        $output->oItem    = $oItem;
        $output->aItem    = $aItem;
        $output->template = 'contactusView.html';
    }
    
    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        if (empty($input->aDelete) || !is_array($input->aDelete)) {
            SGL::raiseError('Incorrect parameter passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
            return;
        }
        foreach ($input->aDelete as $id) {
            $oItem = &DB_DataObject::factory($this->conf['table']['contact_us']);
            $oItem->get($id);
            $oItem->delete();
            unset($oItem);
        }
        SGL::raiseMsg('Selected comments successfully deleted', true, SGL_MESSAGE_INFO);
    }
    
    function getSortParams($sortBy, $sortOrder)
    {
        $aValuesSortBy = array(
            'contact_us_id', 'first_name', 'last_name', 'email',
            'enquiry_type', 'user_comment', 'logtime'
        );
        $aValuesSortOrder = array('asc', 'desc');
        
        $sortByValue    = $this->loadSort($aValuesSortBy, $sortBy, 'sortBy');
        $sortOrderValue = $this->loadSort($aValuesSortOrder, $sortOrder, 'sortOrder');

        return array($sortByValue, $sortOrderValue);
    }
    
    function loadSort($aValuesRange, $value, $type)
    {
        $sessValue = SGL_Session::get($type . $this->module);
        if (empty($value) || !in_array($value, $aValuesRange)) {
            if (empty($sessValue)) {
                $value = $this->conf['ContactUsAdminMgr']['default' . ucfirst($type)];
            } else {
                $value = $sessValue;
            }
        }
        SGL_Session::set($type . $this->module, $value);
        return $value;
    }
}

?>