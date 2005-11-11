<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Rares Benea                                        |
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
// | ShoppingCart.php                                                          |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+

/**
 * Block: Display the list of Items in the Cart
 *
 * @package block
 * @author  Rares Benea <rbenea@bluestardesign.ro>
 * @version $Revision: 1.2 $
 * @since   PHP 4.1
 */
class ShoppingCart {
	function ShoppingCart() {
	}

	function init() {
		SGL :: logMessage(null, PEAR_LOG_DEBUG);
		return $this->getBlockContent();
	}

	function getBlockContent() {
		SGL :: logMessage(null, PEAR_LOG_DEBUG);

		$html = '';
		$items = SGL_HTTP_Session :: get('cartBlockItems');
		if (isset($items) and is_array($items) and count($items) > 0) {
            $total = 0;
			foreach ($items as $item) {
				$html.= '<strong>'.$item['name'].'</strong><br>';
                $html.= $item['quantity'].' x '.number_format($item['price'], 0, ',', '.');
                $html.= '<br>';
                $total = $total + $item['price']*$item['quantity'];
			}
			$html.= '<strong>Total: '.number_format($total, 0, ',', '.').' Lei</strong>';
        } else {
            $html = '<strong>The basket is empty</strong>';
        }
       
       $cart_url = SGL_output :: makeUrl ('','cart','cart');
       $html .= '<br><a href="'.$cart_url.'">Go to cart</a>';
		return $html;
	}
}
?>
