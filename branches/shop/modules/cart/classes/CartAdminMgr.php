<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | CartAdminMgr.php                                                                  |
// +---------------------------------------------------------------------------+
// | Author: Rares Benea <rbenea@bluestardesign.ro>                            |
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
// $Id: CartAdminMgr.php,v 1.4 2005/05/09 23:51:51 demian Exp $

require_once SGL_ENT_DIR . '/Usr.php';
require_once SGL_ENT_DIR . '/Cart.php';
require_once SGL_MOD_DIR . '/cart/classes/Order.php';
require_once SGL_MOD_DIR . '/cart/classes/Item.php';

/**
 * To allow users to contact site admins.
 *
 * @package produse
 * @author  Rares Benea <rbenea@bluestardesign.ro>
 * @version $Revision: 1.4 $
 * @since   PHP 4.1
 */
class CartAdminMgr extends SGL_Manager
{
    
    var $_order;
    
    function CartAdminMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);    
        $this->module		= 'cart';
        $this->pageTitle    = 'Cart Admin';
        $this->template     = 'itemList.html';
        $this->_aActionsMapping =  array(
            'list'      => array('list'),
            'view'      => array('view'),
            'delete'    => array('delete','list'),
        );
            
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);   
        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->template    = $this->template;
        $input->masterTemplate = $this->masterTemplate;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->orderId     = (int) ($req->get('frmOrderID'));
        $input->aDelete     = $req->get('frmDelete');
        
        $input->totalItems  = $req->get('totalItems');
        $input->sortBy      = SGL_Util::getSortBy($req->get('frmSortBy'), SGL_SORTBY_USER);
        $input->sortOrder   = SGL_Util::getSortOrder($req->get('frmSortOrder'));
        
        switch($input->action) {
            case 'delete':
                //if (!isset($input->itemId))
                //    $aErrors[] = 'No item specified';
                break;
        }
        
        //  if errors have occured
       
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseError('Please fill in the indicated fields');
            $input->error = $aErrors;
            $input->template = 'itemList.html';
            $this->validated = false;
        }
       
    }
    
    
    /**
    * List orders
    *
    * @access public
    *
    */
    function _list(&$input, &$output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        $output->template = 'listOrders.html';
        $input->pageTitle = 'Cart Admin :: List orders';
        
        $orderBy_query = '';
        $allowedSortFields = array('cart_id','username','name','c.date_created','total');
        if (isset($input->sortBy) and strlen($input->sortBy) > 0 
           and isset($input->sortOrder) and strlen($input->sortOrder) > 0 
           and in_array($input->sortBy, $allowedSortFields)) {
                $orderBy_query = 'ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder ; 
        } else {
            $orderBy_query = ' ORDER BY c.date_created DESC ';
        }
        
        $dbh = & SGL_DB::singleton();
        $query = "SELECT *,c.date_created as date_created, u.username as username, CONCAT(u.first_name, ' ', u.last_name) as name " .
                " FROM {$conf['table']['cart']} as c, {$conf['table']['user']} as u WHERE c.usr_id = u.usr_id ".$orderBy_query;;
          
        $limit = 5 * $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array ('mode' => 'Sliding', 'delta' => 3, 'perPage' => $limit, 'totalItems' => $input->totalItems);
        $aPagedData = SGL_DB::getPagedData($dbh, $query, $pagerOptions);
        
        if (!DB::isError($aPagedData)) {   

            if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
                $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
            }

            $output->totalItems = $aPagedData['totalItems'];
            $output->aPagedData = $aPagedData;
        } 
    }
    
    
    /**
    * View order and user details
    *
    * @access public
    *
    */
    function _view (& $input, & $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'viewOrder.html';
        $input->pageTitle = 'Cart Admin :: View order';
        
        $oCart = & new DataObjects_Cart();
        $oCart->get($input->orderId);
        $output->cart = $oCart;
        $output->items = @unserialize(stripslashes($oCart->items));
        if (!is_array($output->items) or count($output->items) < 0) {
            SGL::raiseMsg('Invalid order ID');
            return;
        }
        
        
        
        $oUser = & new DataObjects_Usr();
        $oUser->get($oCart->usr_id);
        
        $output->user = $oUser;
        
        $output->itemCount = 0;
        foreach($output->items as $item) {
            $output->itemCount = $output->itemCount + $item->quantity;
        }
    }
    
    
    /**
    * Delete the order from DB
    *
    * @access public
    *
    */
    function _delete (& $input, & $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
              
        if (is_array($input->aDelete)) {
            foreach ($input->aDelete as $index => $cart_id) {
                $oCart = & new DataObjects_Cart();
                $oCart->whereAdd("cart_id = '".$cart_id."'");
                $oCart->delete(DB_DATAOBJECT_WHEREADD_ONLY);
                unset ($oCart);
            }
        } else {
            SGL::raiseError('Incorrect parameter passed to '.__CLASS__.'::'.__FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        
        SGL::raiseMsg('Order deleted successfully');
    }
}
?>