<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | Cart.php                                                                  |
// +---------------------------------------------------------------------------+
// |                                                                           |
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
// $Id: CartMgr.php,v 1.3 2005/05/09 23:51:51 demian Exp $

require_once SGL_ENT_DIR . '/Usr.php';
require_once SGL_ENT_DIR . '/Cart.php';
require_once SGL_MOD_DIR . '/cart/classes/Order.php';
require_once SGL_MOD_DIR . '/cart/classes/Item.php';
require_once 'Mail.php';
require_once 'Mail/mime.php';

/**
 * To allow users to contact site admins.
 *
 * @package produse
 * @author  Rares Benea <rbenea@bluestardesign.ro>
 * @version $Revision: 1.3 $
 * @since   PHP 4.1
 */
class CartMgr extends SGL_Manager
{
    
    var $_order;
    
    function CartMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);    
        $this->module		= 'cart';
        $this->pageTitle    = 'Cart';
        $this->template     = 'itemList.html';
        $this->_aActionsMapping =  array(
            'insert'      => array('insert'),
            'update'      => array('update'),
            'list'      => array('list'),
            'checkout'   => array('checkout'),
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
        $input->catId       = (int) ($req->get('frmCatID'));
        $input->itemId      = $req->get('id');
        $input->data        = $req->get('data');
        $input->aDelete     = $req->get('frmDelete');
        $input->aQty        = $req->get('qth');       
        
        
        // work around if you select one of the cats from the ShopNav block
        SGL::logMessage(print_r($input->catId,true));
        if (isset($input->catId) and $input->catId > 0) {
            
            SGL_HTTP::redirect(SGL_Output::makeUrl('','shop','shop').'list/frmCatID/'.$input->catId);
        }
        
        switch ($input->action) {
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
                    SGL::raiseMsg('You are currently not logged in.');
                    SGL_HTTP::redirect(SGL_Output::makeUrl('','login','user').'?redir='.SGL_Output::makeUrl('checkout','cart','cart'));
                }
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
            SGL::raiseMsg('record insered successfully');
        } else {
            SGL::raiseError('There was a problem insering the record');
        }
        $this->_list($input, $output); 
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
        
        if(isset($input->aDelete))
            foreach($input->aDelete as $noItem)
                unset($this->_order->items[$noItem]);
            
        foreach($input->aQty as $noItem => $qty)
            if(array_key_exists($noItem, $this->_order->items))
                $this->_order->items[$noItem]->quantity = $qty;
        
        $this->_order->_recalc();
        $this->_saveCart();
        
        
        $this->_list($input, $output);     
    }
    
    /**
    * List the objects from the cart
    *
    * @access public
    *
    */
    function _list(&$input, &$output) {
         SGL::logMessage(null, PEAR_LOG_DEBUG);   
         
         $this->_initCart();
         //error_log(print_r($this->_order,true)); 
         $output->itemCount = $this->_order->itemCount;
         $output->total = $this->_order->total; 
         $output->items = array();
         $block = array();
         foreach($this->_order->items as $key => $item) {
            $output->items[$key] = clone($item);
            //$output->items[$key]->price = number_format($output->items[$key]->price , 0, ',', '.');
            $block[] = array('name' => $item->name, 'price' => $item->price, 'quantity' => $item->quantity);  
         }
         //error_log(print_r($this->_order,true)); 
         SGL_HTTP_Session::set('cartBlockItems',$block);
             
    }
    
    
     /**
     * check if user is registered, redirect to login if not, 
     * store order in DB and send notification e-mail to client 
     * and SGL admin
     *
     * @access public
     *
     */
    function _checkout(&$input, &$output) {
         SGL::logMessage(null, PEAR_LOG_DEBUG);   
         $input->template = 'checkOut.html';
         
         $conf = & $GLOBALS['_SGL']['CONF'];
         
         $this->_initCart();
         $order = $this->_order; 
         
         if(count($order->items) < 1) {
             SGL::raiseMsg('Your cart is empty');
             return;
         }
                 
         $order = $this->_order;
 
         // Write order to DB
         $oOrder = & new DataObjects_Cart();
         $dbh = $oOrder->getDatabaseConnection();
         $oOrder->cart_id = $dbh->nextId('cart');
         $oOrder->usr_id = $order->uid;
         $oOrder->items = addslashes(serialize($order->items));
         $oOrder->items_count = $order->itemCount;
         $oOrder->total = $order->total;
         $oOrder->stage = 0;
         $oOrder->date_created = SGL::getTime();
         $success = $oOrder->insert();
         
         // Get user data
         $oUser = & new DataObjects_Usr();
         $oUser->get($order->uid);
         
         
         // Generate item list
         $output->itemCount = $this->_order->itemCount;
         $output->total = $this->_order->total; 
         $output->items = array();
         $block = array();
         foreach($this->_order->items as $key => $item) {
            $output->items[$key] = clone($item);
            $block[] = array('name' => $item->name, 'price' => $item->price, 'quantity' => $item->quantity);  
         }
         
         
         if($conf['Cart']['emailConfirmation']) {
                    // Send email
                    $name = $oUser->first_name.' '.$oUser->last_name;
                    $output->emailSiteName = $conf['site']['name'];
                    $output->emailSubject = SGL_String::translate('Order received').' '.$conf['site']['name'];
                    $output->emailName = strlen($name) > 2 ? $name : 'User';
                    $output->emailAddress = $oUser->email;
                    $output->emailBcc = $conf['email']['admin'];
                    $ret = $this->_send($input, $output);
                    if(!$ret) {
                       SGL::logMessage('Unable to send order message to: '.$oUser->email);
                    }
                }
         
        if ($success) {
            //  redirect on success
            SGL_HTTP_Session::set('cartBlockItems',null);
            SGL_HTTP_Session::set('cartOrder',null);
            SGL::raiseMsg('Order saved successfully');  
        } else {
            SGL::raiseError('There was a problem inserting the record', SGL_ERROR_NOAFFECTEDROWS);
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
        $conf = & $GLOBALS['_SGL']['CONF'];
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
 
 
        $headers['From'] = $conf['email']['admin'];
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