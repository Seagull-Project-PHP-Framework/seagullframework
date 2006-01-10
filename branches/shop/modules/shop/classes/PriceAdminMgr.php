<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | ShopeMgr.php                                                              |
// +---------------------------------------------------------------------------+
// | Author: Rares Benea <rbenea@bluestardesign.ro>                            |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Rares Benea                                           |
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
// $Id: PriceAdminMgr.php,v 1.4 2005/05/10 00:25:14 demian Exp $

require_once SGL_ENT_DIR . '/Product.php';
require_once SGL_ENT_DIR . '/Price.php';
require_once SGL_ENT_DIR . '/User_preference.php';

//Actually $this at his moment is NULL...
if (isset($this->conf['ShopMgr']['multiCurrency']) &&
    $this->conf['ShopMgr']['multiCurrency'] == true) {
    require_once SGL_MOD_DIR . '/rate/classes/RateMgr.php';
}


/**
 * To allow users to contact site admins.
 *
 * @package shop
 * @author  Rares Benea <rbenea@bluestardesign.ro>  
 * @version $Revision: 1.4 $
 * @since   PHP 4.1
 */
class PriceAdminMgr extends SGL_Manager
{
    function PriceAdminMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();
        
        $this->module		= 'shop';
        $this->pageTitle    = 'Discounts';
        $this->template     = 'userList.html';
        $this->_aActionsMapping =  array(
            'delete'       => array('delete','list'),
            'insert'       => array('insert','list'),
            'list'       => array('list'),
            'deleteProd'       => array('deleteProd','listProd'),
            'insertProd'       => array('insertProd','listProd'),
            'listProd'       => array('listProd'),
        );   
        
        //$this->dbh = & SGL_DB::singleton();
        //TO DO: activate rate manager
        //$this->conf = & $GLOBALS['_SGL']['CONF'];
        if (isset($this->conf['ShopMgr']['multiCurrency'])) {
            if($this->conf['ShopMgr']['multiCurrency']) {
                $rateMgr = & new RateMgr();
            } else {
                $this->conf['exchangeRate'][$this->conf['ShopMgr']['defaultCurrency']] =
                $this->conf['ShopMgr']['defaultExchange'];
            }
        }
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
        $input->catId       = (int) ($req->get('frmCatID')) ? $req->get('frmCatID') : 0; 
        $input->productId   = $req->get('frmProdId');
        
        $input->usr_id      = (int) ($req->get('usr_id')) ? $req->get('usr_id') : 0;
        $input->discount    = (int) ($req->get('discount')) ? $req->get('discount') : 0;
        $input->price        = (int) $req->get('price');
        $input->currency    = $req->get('currency');
        $input->exchangeRates    = (array) $req->get('exchangeRates');
        $input->pref		= (object) $req->get('pref');
        $input->sortBy      = SGL_Util::getSortBy($req->get('frmSortBy'), SGL_SORTBY_USER);
        $input->sortOrder   = SGL_Util::getSortOrder($req->get('frmSortOrder'));
        
        $input->aDelete     = $req->get('frmDelete');
        
        // autopagination
        $input->from = ($req->get('frmFrom'))?$req->get('frmFrom'):0;          
        
        //  if errors have occured
        if ($input->action == 'insert') {
            if (isset($input->usr_id)) { 
                if ($input->usr_id < 0) {
                    $aErrors['usr_id'] = SGL_Output::translate('Please select a user');
                }                   
            } else {
                $aErrors['usr_id'] = SGL_Output::translate('Please select a user');
            }
        
            if (isset($input->discount) and ($input->discount < 0 || $input->discount > 100)) {
             $aErrors['discount'] = SGL_Output::translate('Value must be between 0-100%');
            } 
            
            if (isset($aErrors) && count($aErrors)) {
                SGL::raiseMsg('Please fill in the indicated fields');
                $input->error = $aErrors;
                $input->action = 'list';
                return;
            }
        }
        
        if ($input->action == 'insertProd') {
            if (isset($input->usr_id)) { 
                if ($input->usr_id < 0) {
                    $aErrors['usr_id'] = SGL_Output::translate('Please select a user');
                }                   
            } else {
                $aErrors['usr_id'] = SGL_Output::translate('Please select a user');
            }
            
            if (isset($input->productId)) {
                if ($input->productId < 1) { 
                    $aErrors['usr_id'] = SGL_Output::translate('Invalid product ID');                   
                } 
            } else {
                $aErrors['usr_id'] = SGL_Output::translate('Invalid product ID');
            }
            
            if (isset($input->currency)) {
                if (!array_key_exists($input->currency,$this->conf['exchangeRate'])) {
                    $aErrors['currency'] = SGL_Output::translate('Invalid currency');
                }
            } else {
                $aErrors['currency'] = SGL_Output::translate('Invalid currency');
            }
            
            if (isset($aErrors) && count($aErrors)) {
                SGL::raiseMsg('Please fill in the indicated fields');
                $input->error = $aErrors;
                $input->action = 'listProd';
                return;
            }
            
        }
 
       
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }
              
        
    }

    
    // TO DO: Check for DB::Error on SQL query
    /**
     * List the users that have a general discount set
     *
     * @access public
     *
     */
    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'userList.html';   
        $output->pageTitle = 'General discount :: List';
        
        //$this->conf = & $GLOBALS['_SGL']['CONF'];
        
        $roleId = $this->conf['price']['roleId'];
        // The discount is stored in user preferences with this ID
        $discPrefId = $this->conf['price']['discountPrefId'];
    
        // Generate Users select box for Add form
        $aUsers = array();
//        $dbh = &SGL_DB::singleton();
        $query = "SELECT usr_id, username, email FROM {$this->conf['table']['user']} WHERE role_id != 1";
        $result = $this->dbh->query($query);
        while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
            $aUsers[$row['usr_id']] = $row['username'].' : '.$row['email'].'  ';
        }
        $output->userList = SGL_Output::generateSelect($aUsers);
        
        
        // Generate discount list
        $orderBy_query = '';
        $allowedSortFields = array('usr_id','username','email','is_acct_active','discount');
        if (  !empty($input->sortBy)
           && !empty($input->sortOrder)
           && in_array($input->sortBy, $allowedSortFields)) {
                $orderBy_query = 'ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder ;
        }
        
        $query = "SELECT u.*, p.value as discount " .
            "FROM {$this->conf['table']['user']} u, user_preference p " .
            "WHERE u.usr_id = p.usr_id AND p.preference_id = '$discPrefId' ".$orderBy_query;
        
        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'     => 'Sliding',
            'delta'    => 3,
            'perPage'  => $limit,
        );
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);

        $output->aPagedData = $aPagedData;
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }        
    }
     
    
    /**
    * Add/update user general discount
    *
    * @access public
    *
    */
    function _insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
             
        $prefs = & new DataObjects_User_preference();
        $prefs->usr_id = $input->usr_id;
        $prefs->preference_id = $this->conf['price']['discountPrefId'];
        $noRows = $prefs->find();
        if ($noRows == 0) { 
            $dbh = & $prefs->getDatabaseConnection();
            $prefs->user_preference_id = $dbh->nextId('user_preference');
            $prefs->value = $input->discount;
            $ret = $prefs->insert();
        }
        else {
            $prefs->fetch();
            $prefs->value = $input->discount;
            $ret = $prefs->update();
        }
        
        if ($ret) {
            SGL::raiseMsg('Data saved successfully');
        } else {
            SGL::raiseMsg('Data save error');
        }  
    }
    
    
    /**
    * Delete user general discount
    *
    * @access public
    *
    */
    function _delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
            
        if (is_array($input->aDelete)) {
            foreach ($input->aDelete as $index => $userID) {
                //  don't allow admin to be deleted
                $prefs = & new DataObjects_User_preference();
                $prefs->whereAdd('usr_id = '.$userID);
                $prefs->whereAdd('preference_id = '.$this->conf['price']['discountPrefId']);
                $prefs->delete(DB_DATAOBJECT_WHEREADD_ONLY);
                unset($prefs);
            }
        } else {
            SGL::raiseError('Incorrect parameter passed to ' . 
                __CLASS__ . '::' . __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        
        SGL::raiseMsg('Data deleted successfully');
    }
    
    
    /**
    * List user product discounts
    *
    * @access public
    *
    */
    function _listProd(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'userListProd.html';   
        $output->pageTitle = 'Product discount :: List';
        
        $vat = isset ($_SESSION['aPrefs']['VAT'])
            ? $_SESSION['aPrefs']['VAT']
            : $this->conf['ShopMgr']['defaultVAT'];
        
        // If an productID is set then show the ADD discount tab
        if (isset($input->productId) and $input->productId > 0) {
            $query = "SELECT name, currency, price FROM {$this->conf['table']['product']} ".
                     "WHERE product_id='".$input->productId."' ";
            $result = $this->dbh->query($query);
            
            if ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
                $output->product = $row['name'];
                $output->priceVAT = $row['price'] * $vat;
//                $output->product_currency = $row['currency'];
                $output->frmProdId = $input->productId;
                $output->product_currency = array(
                    $row['currency'] => "{$row['currency']}",
                );

                $output->product_currency = SGL_Output::generateSelect($output->product_currency);
            } else {
                SGL::raiseMsg('Invalid product ID');
            }
            $roleId = $this->conf['price']['roleId'];
            
            $query = "SELECT usr_id, username, email
                      FROM {$this->conf['table']['user']}
                      WHERE role_id != 1";
            $result = $this->dbh->query($query);
            
            $aUsers = array(); 
            while($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
                $aUsers[$row['usr_id']] = $row['username'].' : '.$row['email'].'  ';
            }
            
            $output->userList = SGL_Output::generateSelect($aUsers, @ $input->usr_id);           
        } else {
            //redirect to simple listing?
//           echo "no prod id";
        }
        
        $orderBy_query = '';
        $allowedSortFields = array('usr_id','username','product_id','name','default_price','price');
        if (  !empty($input->sortBy)
           && !empty($input->sortOrder)
           && in_array($input->sortBy, $allowedSortFields)) {
                $orderBy_query = 'ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder ;
        }
        
        $where_query = ' ';
        // If an productID is set then show only that product discount
        // is this needed? if no productID we ge _list
        if (isset($input->productId) and $input->productId > 0) {
            $where_query .= " AND pr.product_id='".$input->productId."' ";
        }
        
        $query = "SELECT u.usr_id, u.username,".
                         "pr.price AS price, pr.currency AS currency, pr.product_id AS product_id, " .
                         "p.price AS default_price, p.currency AS default_currency, p.name AS name ".
                 "FROM {$this->conf['table']['user']} AS u,
                       {$this->conf['table']['price']} AS pr,
                       {$this->conf['table']['product']} AS p " .
                 'WHERE u.usr_id = pr.usr_id AND pr.product_id = p.product_id '.$where_query.$orderBy_query;

        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'     => 'Sliding',
            'delta'    => 3,
            'perPage'  => $limit,
        );
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);
        
        if (DB::isError($aPagedData)) {
            SGL::logMessage(print_r($query,true));
            die("Error ".$query);
            return;
        }
        
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            foreach ($aPagedData['data'] as $key => $value) {
                $v = $aPagedData['data'][$key];
                $v['priceVAT'] = round((float)$aPagedData['data'][$key]['price'] * $vat, 2);
                $v['default_priceVAT'] = round((float)$aPagedData['data'][$key]['default_price'] * $vat, 2);
                $aPagedData['data'][$key] = $v;
            }
//            dumpr($aPagedData['data']);
            $output->aPagedData = $aPagedData;

            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }
    }
    
    
    /**
    * Add/update user product discounts
    *
    * @access public
    *
    */
    function _insertProd(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
            
        $vat = isset ($_SESSION['aPrefs']['VAT'])
            ? $_SESSION['aPrefs']['VAT']
            : $this->conf['ShopMgr']['defaultVAT'];
            
        $price = & new DataObjects_Price();
        $price->usr_id = $input->usr_id;
        $price->product_id = $input->productId;
        $noRows = $price->find();
        if ($noRows == 0) { 
            $dbh = & $price->getDatabaseConnection();
            $price->price_id = $dbh->nextId('price');
            $price->price = round($input->price / $vat, 4);  // price comes with VAT and then here we drop it.
            $price->currency = $input->currency;
            $ret = $price->insert();
        }
        else {
            $price->fetch();
            $price->price = round($input->price / $vat, 4);
            $price->currency = $input->currency;
            $ret = $price->update();
        }
            
        if ($ret) {
            SGL::raiseMsg('Data saved successfully');
        } else {
            SGL::raiseMsg('Data save error');
        }
            
        SGL_HTTP::redirect('priceMgr.php', array('action'=> 'listProd', 'frmProdId' => $input->productId));
    }
    
   
     /**
    * Delete user product discounts
    *
    * @access public
    *
    */
    function _deleteProd(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        if (is_array($input->aDelete)) {
            foreach ($input->aDelete as $index => $userID) {
                //  don't allow admin to be deleted
                $prefs = & new DataObjects_Price();
                $prefs->whereAdd('usr_id = '.$userID);
                $prefs->whereAdd('product_id = '.$input->productId);
                $prefs->delete(DB_DATAOBJECT_WHEREADD_ONLY);
                unset($prefs);
            }
        } else {
            SGL::raiseError('Incorrect parameter passed to ' . 
                __CLASS__ . '::' . __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        
        SGL::raiseMsg('Data deleted successfully');
        SGL_HTTP::redirect('priceMgr.php', array('action'=> 'listProd', 'frmProdId' => $input->productId));        
    }
    
}
?>
