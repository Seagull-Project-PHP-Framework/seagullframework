<?php

class AFK_Output extends SGL_Output
{
    /**
     * Output the current currency in the session
     *
     */
     function confirmCurrency($currency)
     {
     	$current = SGL_Session::get('currency');
    	if ( $currency == $current ) {
    		return true;
    	} else {
    		return false;
    	}
     }

    /**
     * Output the current currency in the session
     *
     */
     function confirmSaleOnly()
     {

    	if ( SGL_Session::get('saleOnly') ) {
    		return true;
    	} else {
    		return false;
    	}
     }

    /**
     * Convert the currency
     *
     */
     function convertCurrency($from, $amount)
     {
     	$aPrefs = SGL_Session::get('aPrefs');
    	$to = (isset($aPrefs['currency'])) ? $aPrefs['currency'] : 'GBP';

    	$newAmount = 0;

    	if ( empty( $to ) ) {
    		SGL_Session::set('currency', "GBP");
    		$to = "GBP";
    	}

     	$currnecySymbol = array(
    	"GBP" => "&pound;",
    	"USD" => "$",
    	"EUR" => "&euro;",
    	);

     	if ( $from != $to ) {
    		// only convert between different currencies
     		require_once(SGL_APP_ROOT . "/lib/SGL/Currency.php");

    		$exch = & SGL_Currency::singleton();
    		$exch->fromCode($from);
    		$exch->toCode($to);
    		$newAmount = $exch->convertAmount($amount);
    		$newAmount = number_format($newAmount, 2, '.', ',');
    		//$newAmount = $newAmount . ' <span class="highlight">('. $currnecySymbol[$from] . $amount . ')</span>';
    		// = $exch->convert();

    	} else {

    		$newAmount = $amount;

    	}

    	return $currnecySymbol[$to] . $newAmount;
     }

     function returnCurrencySymbol($to)
     {
     	$currnecySymbol = array(
    	"GBP" => "&pound;",
    	"USD" => "$",
    	"EUR" => "&euro;",
    	);

    	if(isset($currnecySymbol[$to])) {
     		return  $currnecySymbol[$to];
    	}
     }

     function isFree( $val )
     {
     	if ( $val > 0 ) {
    		return false;
    	} else {
    		return true;
    	}

     }


     function isBuyerRole()
     {
     	$rid = SGL_Session::get('rid');
    	if ( $rid == 8 ) {
    		return true;
    	} else {
    		return false;
    	}
     }

     function currentUsername()
     {
     	$username = SGL_Session::get('username');
    	if ( !empty($username) ) {
    		return $username;
    	} else {
    		return false;
    	}
     }

     function shoppingCartCount()
     {
    	if ( SGL_Session::get('cartItems') ) {
    		$aCartItems = SGL_Session::get('cartItems');
    		$numbItems = count($aCartItems);
    		return $numbItems;
    	} else {
     		return 0;
    	}
     }


     // COLLECTION FUNCTIONS

     function getCollectionId()
     {
     	$collectionId = SGL_Session::get('collectionId');

    	if ( $collectionId ) {
    		return $collectionId;
    	} else {
    		return false;
    	}
     }

     function getCollectionTitle()
     {
     	return SGL_Session::get('collectionTitle');
     }

     function getCollectionTheme()
     {
     	return SGL_Session::get('collectionTheme');
     }

     function getCollectionLinkTitle()
     {
     	return SGL_Session::get('collectionLink');
     }

     function urlencodeString( $str )
     {
     	return urlencode($str);
     }

     function getCollectionArt()
     {
     	$this->aBucketCollectionArt = SGL_Session::get('aCollectionArt');
    	return;
     }

     function userFileExists($user, $file)
     {
     	if ( !empty($file) && !empty($user) ) {
    		if (is_file(realpath(SGL_WEB_ROOT . "/uploads/" . $user . "/" . $file))) {
    			return true;
    		}else{
    			return false;
    		}
    	} else {
    		return false;
    	}
     }

     function changeCase( $str, $case )
     {
     	if ( $case == "lower" ) {
    		return strtolower($str);
    	} else {
    		return strtoupper($str);
    	}
     }
}
?>