<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | Cart.php                                                                  |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2004 Rares Benea                                            |
// |                                                                           |
// | Author: Rares Benea <rbenea@bluestardesign.ro>                            |
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
// $Id: Produse.php,v 1.1 2004/11/24 01:07:11 cvsroot Exp $
require_once SGL_ENT_DIR . '/Usr.php';
require_once SGL_ENT_DIR . '/Cart.php';
require_once SGL_ENT_DIR . '/Cart_product.php';
require_once SGL_MOD_DIR . '/cart/classes/Order.php';
require_once SGL_MOD_DIR . '/cart/classes/Item.php';
require_once 'Mail.php';
require_once 'Mail/mime.php';

/**
 * To allow users to contact site admins.
 *
 * @package produse
 * @author  Rares Benea <rbenea@bluestardesign.ro>
 * @version $Revision: 1.1 $
 * @since   PHP 4.1
 */
class CartMgr extends SGL_Manager
{
    
    var $_order;
    
    function CartMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();
        $this->module		= 'cart';
        $this->pageTitle    = 'Cart';
        $this->template     = 'itemList.html';
       // $this->conf = & $GLOBALS['_SGL']['CONF'];
       // $this->dbh = & SGL_DB::singleton();
        
        $this->_aActionsMapping =  array(
            'insert'      => array('insert'),
            'update'      => array('update'),
            'list'        => array('list'),
            'checkout'    => array('checkout'),
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
        $update = $req->get('update');
        $delete = $req->get('delete');
        if (!empty($update)) {
            $input->aQty        = $req->get('qth');
        } elseif (!empty($delete)) {
            $input->aDelete     = $req->get('frmDelete');
        }
/*
        //Why this does not work?
        if (!empty($req->get('update'))) {
            $input->aQty        = $req->get('qth');
        } elseif (isset($req->get('delete'))) {
            $input->aDelete     = $req->get('frmDelete');
        }*/
        
        
        $input->catId       = (int) ($req->get('frmCatID'));
        $input->itemId      = $req->get('id');
        $input->data        = $req->get('data');
        
        // work around if you select one of the cats from the ShopNav block
        SGL::logMessage(print_r($input->catId,true));
        if(isset($input->catId) and $input->catId > 0) {
            SGL_HTTP :: redirect(SGL_Output :: makeUrl('','shop','shop').'list/frmCatID/'.$input->catId);
        }
        switch($input->action) {
            case 'delete':
                //if(!isset($input->itemId))
                //    $aErrors[] = 'No item specified';
                break;
            case 'insert':
                if(!isset($input->data))
                    $aErrors[] = 'No item specified';
                break;
            case 'checkout':
                $uid = SGL_HTTP_Session::get('uid');
                if($uid == 0) {
                    //we don't see this message...
                    SGL::raiseMsg('You are currently not logged in.');
                    $options = array(
                      'moduleName' => 'user',
                      'managerName' => 'login',
                      'action' => 'login',
                      'redir' => urlencode(SGL_Output::makeUrl('checkout','cart','cart')),
                    );
                    SGL_HTTP::redirect($options);
                }
                break;    
            case 'update':
                //check if delete and if recalck

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
    * Retrieve the order object from session. If new order, it 
    * creates the order object.
    *
    * @access public
    *
    */
    function _initCart() {
        $this->_order = SGL_HTTP_Session::get('cartOrder');
        // if new order, create the object
        if(!isset($this->_order->items)) {
                $this->_order = new Order();
        } 
        $this->_order->uid = SGL_HTTP_Session::get('uid');
    }
    
    /**
    * Save the order object in session
    *
    * @access public
    *
    */
    function _saveCart() {
        $this->_order->date = time();
        SGL_HTTP_Session::set('cartOrder',$this->_order);
    }
    
    
    /**
    * Deserialize the object received via GET/POST and inset it in the cart
    *
    * @access public
    *
    */
    function _insert(&$input, &$output) {
         SGL::logMessage(null, PEAR_LOG_DEBUG);    
        
        $success = false;

        $this->_initCart();
        
        $oItem = unserialize(base64_decode(urldecode($input->data)));
        if(is_object($oItem)) {
            $success = $this->_order->addItem($oItem);
        }
        
        if ($success) {
            $this->_saveCart();
            SGL::raiseMsg('Product was added to your cart'); //users need nice messages lol
        } else {
            SGL::raiseError('There was a problem inserting the record');
        }
        SGL_HTTP :: redirect(SGL_Output :: makeUrl('list','cart','cart'));

//        $this->_list($input, $output);
    }

    /**
    * Delete objects, or update the quantity of object stored in the cart
    *
    * @access public
    *
    */
    function _update(&$input, &$output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);   
            
        //error_log(print_r($input,true));
        
        $this->_initCart();

        //if smth is marked - DELETE
        if(isset($input->aDelete)) {
            foreach($input->aDelete as $noItem) {
                unset($this->_order->items[$noItem]);
            }
        }

        //else recalculate
        foreach($input->aQty as $noItem => $qty){
            if(array_key_exists($noItem, $this->_order->items)){
                $this->_order->items[$noItem]->quantity = $qty;
            }
        }
        
        $this->_order->_recalc();
        $this->_saveCart();
        
//        $this->_list($input, $output);
         $redirect = array(  'moduleName'    => 'cart',
                             'managerName'   => 'cart');
         SGL_HTTP::redirect($redirect);
    }
    
    /**
    * List the objects from the cart
    *
    * @access public
    *
    */
    function _list(&$input, &$output) {
         SGL::logMessage(null, PEAR_LOG_DEBUG);   

         $output->pageTitle = 'Cart :: List cart';

         $this->_initCart();
         
         if (!empty($this->_order->items)) {
             $output->itemCount = $this->_order->itemCount;
             $output->total_price = $this->_order->total_price;
             $output->total_sum = $this->_order->total_sum;
             $output->VATsum = $this->_order->VATsum;
             $output->total_sumVAT = $this->_order->total_sumVAT;
             $output->currency = $this->_order->currency;
             $vat = explode(".", SGL_HTTP_Session::get('vat'));
             $output->vat = $vat[1];
         }

         $output->items = array();
         $block = array();
         $output->total = 0;

         $i = 1;
         foreach($this->_order->items as $key => $item) {
            $output->items[$i++] = clone($item);
            $block[] = array('name' => $item->name,
                             'cod1' => $item->cod1,
                             'price' => $item->price,
                             'quantity' => $item->quantity,
                             );
            $output->total += (int)$item->quantity;
         }

         $output->itemCount = $i-1;
         //there is a bug smowhere in order class
         //item count gets wrong number and $this->_order->items starts numbering from 2
         //this is a quick fix to this
         //PS which one we use _order->itemCount or _order->_itemCount
         $this->_order->itemCount = $output->itemCount;
         $this->_order->_itemCount = $output->itemCount;

         $this->_order->items = $output->items;

         //dumpr($this);
         //error_log(print_r($this->_order,true)); 
         //ROL: do we use this???
         SGL_HTTP_Session::set('cartBlockItems',$block);
    }
    
    
     /**
     * check if user is registered, redirect to login if not, 
     * store order in DB and send notification e-mail to client 
     * and SGL admin
     *
     * @access public
     * @modified Tomas Bagdanavicius
     */ // should check when admin is trying to buy anything and to tell him that he is not allowed to buy
    function _checkout(&$input, &$output) {
         SGL::logMessage(null, PEAR_LOG_DEBUG);
         $output->pageTitle = 'Cart :: Checkout';
         $input->template = 'checkOut.html';
         
         $this->_initCart();
         $order = $this->_order;

         if(count($order->items) < 1) {
             SGL :: raiseMsg('Your cart is empty');
             return;
         }
         //$order = $this->_order;
         if (SGL_HTTP_Session :: getUserType() != SGL_ADMIN) {
		 // Get Payment data
//     		 $dbh = & SGL_DB::singleton();
    		 $query = "
				SELECT credit_limit-debt as credit_balance
				FROM payment
				WHERE user_id = " . SGL_HTTP_Session::getUid();

             	$aPayment = $this->dbh->getAll($query);
                if (DB::isError($aPayment)) {
                    SGL::raiseError('perhaps no item tables exist', SGL_ERROR_NODATA);
				    $registerOrder = false;
		        }
		        $oPayment = $aPayment['0'];
        } else {
            $oPayment->credit_balance = 10000; //lol
            // reiktu neleist adminui pirkti isvis..
        }

		 if(isset($registerOrder) && !$registerOrder || $order->total > $oPayment->credit_balance) {
			SGL :: raiseMsg('Limit exceeded, please reduce the total price');
			SGL_HTTP :: redirect(SGL_Output :: makeUrl('','','cart'));
			return;
		 }
		 unset($dbh,$query,$aPayment,$oPayment);

         // Write order to Cart DB
         $oOrder = & new DataObjects_Cart();
         $dbh = $oOrder->getDatabaseConnection();
         $oOrder->cart_id = $dbh->nextId('cart');
         $oOrder->usr_id = $order->uid;
         //$oOrder->items = addslashes(serialize($order->items));
         //$oOrder->items_count = $order->itemCount;
         $oOrder->total_sum    = $order->total_sum;
         $oOrder->total_sumVAT = $order->total_sumVAT;
         $oOrder->total = $order->total_sumVAT;  //at the moment only
         $oOrder->stage = 0;
         $oOrder->date_created = SGL::getTime();

         $success = $oOrder->insert();

		 // Write order to Cart_product DB
		 $success_cart_product = true;
		 $oOrderProduct = & new DataObjects_Cart_product();
		 foreach($order->items as $key => $item) {
			$oOrderProduct->cart_product_id = '';
			$oOrderProduct->cart_id = $oOrder->cart_id;
			$oOrderProduct->product_id = $item->id;
			$oOrderProduct->product_name = $item->name;
			$oOrderProduct->product_code = $item->cod1;
			$oOrderProduct->quantity = $item->quantity;
			$oOrderProduct->price = $item->price;
			$oOrderProduct->priceVAT = $item->priceVAT;

			if(!$oOrderProduct->insert())
				$success_cart_product = false;
		 }

         // Get user data
         $oUser = & new DataObjects_Usr();
         $oUser->get($order->uid);
         
         // Generate item list
         $output->itemCount = $this->_order->itemCount;
         $output->total = $this->_order->total; 
         
         $output->total_price  = $this->_order->total_price;
         $output->total_sum    = $this->_order->total_sum;
         $output->itemCount    = $this->_order->itemCount;
         $output->total_sumVAT = $this->_order->total_sumVAT;
         $output->currency     = $this->_order->currency;  
         $output->VATsum       = $this->_order->VATsum;
         $vat = explode(".", SGL_HTTP_Session::get('vat'));
         $output->vat = $vat[1];
         
         $output->items = array();
         $block = array();
         foreach($this->_order->items as $key => $item) {
            $output->items[$key] = clone($item);
            $block[] = array('name' => $item->name, 'price' => $item->price, 'quantity' => $item->quantity);  
         }
         
         
        if($this->conf['Cart']['emailConfirmation']) {
            // Send email
            $name = $oUser->first_name.' '.$oUser->last_name;
            $output->emailSiteName = $this->conf['site']['name'];
            $output->emailSubject = SGL_String::translate('Order received').' '.$this->conf['site']['name'];
            $output->emailName = strlen($name) > 2 ? $name : 'User';
            $output->emailAddress = $oUser->email;
            $output->emailBcc = $this->conf['email']['admin'];
            $ret = $this->_send($input, $output);
            if(!$ret) {
                 SGL::logMessage('Unable to send order message to: '.$oUser->email);
            }
        }
         
        if ($success && $success_cart_product) {
            //  redirect on success
            SGL_HTTP_Session::set('cartBlockItems',null);
            SGL_HTTP_Session::set('cartOrder',null);
            SGL :: raiseMsg('Order saved successfully');  
//            $output->addOnLoadEvent('document.items.delete.disabled = true');
        } else {
            SGL :: raiseError('There was a problem inserting the record', SGL_ERROR_NOAFFECTEDROWS);
        }
    }
    
    
    /**
    * E-mail send function
    *
    * @access public
    *
    */
    function _send(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->conf = & $GLOBALS['_SGL']['CONF'];
        $theme = $_SESSION['aPrefs']['theme'];
        $template = 'email_send_order.html';
        $output->webRoot          = SGL_BASE_URL;
 
        //  initialise template engine
        $options = &PEAR::getStaticProperty('HTML_Template_Flexy','options');
        $options = array(
            'templateDir'       =>  SGL_THEME_DIR . '/' . $theme . '/' . $this->module . PATH_SEPARATOR .
                                    SGL_THEME_DIR . '/default/' . $this->module . PATH_SEPARATOR .
                                    SGL_THEME_DIR . '/' . $theme . '/default'. PATH_SEPARATOR .
                                    SGL_THEME_DIR . '/default/default',
            'templateDirOrder'  => 'reverse',
            'multiSource'       => true,
            'compileDir'        => SGL_CACHE_DIR . '/tmpl/' . $theme,
            'forceCompile'      => SGL_FLEXY_FORCE_COMPILE,
            'debug'             => SGL_FLEXY_DEBUG,
            'allowPHP'          => SGL_FLEXY_ALLOW_PHP,
            'filters'           => SGL_FLEXY_FILTERS,
            'locale'            => SGL_FLEXY_LOCALE,
            'compiler'          => SGL_FLEXY_COMPILER,
            'valid_functions'   => SGL_FLEXY_VALID_FNS,
            'flexyIgnore'       => SGL_FLEXY_IGNORE,
            'globals'           => true,
            'globalfunctions'   => SGL_FLEXY_GLOBAL_FNS,
        );

        // Configure Flexy to use SGL ModuleOutput Plugin 
        // If an Output.php file exists in module's dir
        $customOutput = SGL_MOD_DIR . '/' . $this->module . '/classes/Output.php';
        if (is_readable($customOutput)) {
            $className = ucfirst($this->module) . 'Output';
            if (isset($options['plugins'])) {
                $options['plugins'] = $options['plugins'] + array($className => $customOutput);
            } else {
                $options['plugins'] = array($className => $customOutput);
            }
        }

        //  suppress notices in templates
        $GLOBALS['_SGL']['ERROR_OVERRIDE'] = true;
        $templ = & new HTML_Template_Flexy();
        $templ->compile($template);

        //  if some Flexy 'elements' exist in the output object, send them as
        //  2nd arg to Flexy::bufferedOutputObject()
        $elements = (   isset($output->flexyElements) && 
                        is_array($output->flexyElements))
                ? $output->flexyElements 
                : array();

        $bodyHtml = $templ->bufferedOutputObject($output, $elements);
 
 
        $headers['From'] = $this->conf['email']['admin'];
        $headers['Subject'] = $output->emailSubject;
        $headers['BCC'] = $output->emailBcc;
        $crlf = SGL_String::getCrlf();
        $mime = & new Mail_mime($crlf);
        $mime->setHTMLBody($bodyHtml);
        $body = $mime->get();
        $hdrs = $mime->headers($headers);
        $mail = & Mail::factory('mail');
        $success = $mail->send($output->emailAddress, $hdrs, $body);
        
        return $success;
    }
    
}
?>
