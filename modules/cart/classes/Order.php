<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | Order.php                                                                 |
// +---------------------------------------------------------------------------+
// | Author: Rares Benea <rbenea@bluestardesign.ro>                            |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2004, Rares Benea                                           |
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
// $Id: Order.php,v 1.2 2005/05/07 23:59:45 demian Exp $

require_once SGL_MOD_DIR . '/cart/classes/Item.php';

/**
 * To allow users to contact site admins.
 *
 * @package orders
 * @author  Rares Benea <rbenea@bluestardesign.ro>
 * @version $Revision: 1.2 $
 * @since   PHP 4.1
 */
class Order extends Item
{
    var $date = 0;
    var $uid = 0;
    var $items = array();
    var $itemCount = 0;
    var $total = 0;
    var $sid = '';
    var $stage = 0;
    var $_itemCount = 0;
    
    function Order()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);   
        $this->module       = 'cart';
    }
    

    function &addItem($oItem = null) {
        SGL::logMessage(null, PEAR_LOG_DEBUG); 
            
        if(is_object($oItem))    
            $newItem = $oItem;
        else
            $newItem = new Item();
        
        $k = null;
        
        foreach($this->items as $key => $item) {
            if($item->id == $newItem->id and $item->type == $newItem->type)
                $k = $key;
        }
        
        if($k == null) {                 
            $this->_itemCount = $this->_itemCount + 1;
            $this->items[$this->_itemCount] = $newItem;
        } else {
            ++$this->items[$k]->quantity;
        }
        $this->_recalc();
        return true;
    }
    
    function delItem($itemNo) {
        SGL::logMessage(null, PEAR_LOG_DEBUG); 
        
            
        if(array_key_exists($itemNo,$this->items)) {
            unset($this->items[$itemNo]);
            $this->_recalc();
            return true;
        }
        else {
            return false;
        }
    }
    
    function _recalc() {
        SGL::logMessage(null, PEAR_LOG_DEBUG); 
         
        $total = 0;
        $itemCount = 0;
        
        foreach($this->items as $key => $item) {
            $total = $total + $item->quantity * $item->price;
            $itemCount = $itemCount + $item->quantity;
        }   
        
        $this->total = $total;
        $this->itemCount = $itemCount;
    }
}
?>