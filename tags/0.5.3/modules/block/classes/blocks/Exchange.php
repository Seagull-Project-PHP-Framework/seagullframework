<?php
/**
 * Block: It displays the exchange rate
 *
 * @package block
 * @author  Rares Benea <rbenea@bluestardesign.ro>
 * @version $Revision: 1.4 $
 * @since   PHP 4.1
 */
require_once SGL_MOD_DIR . '/rate/classes/RateMgr.php';

class Exchange
{
    function Exchange()
    {
    }

    function init()
    {   
        $rateMgr = & new RateMgr();
        return $this->getBlockContent();
    }

    function getBlockContent()
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        
        $date = date('d-m-Y');
        $text = "<strong>Today $date</strong><br />";
             
        if (isset($conf['exchangeRate']['EUR'])) {
            $euro = number_format($conf['exchangeRate']['EUR'], 0, ',', '.') . ' Lei';
            $text .= "1 EURO = $euro<br />\n";
        }
            
        if (isset($conf['exchangeRate']['USD'])) {    
            $usd  = number_format($conf['exchangeRate']['USD'], 0, ',', '.') . ' Lei';
            $text .= "1 USD = $usd<br />\n";
        }
            

        if (SGL_HTTP_Session::getUserType() == SGL_ADMIN) {
        $editUrl = SGL_Output :: makeUrl('edit','rateAdmin','rate');
        $retrieveUrl = SGL_Output :: makeUrl('retrieve','rateAdmin','rate');     
        $admin = <<< TAG
        <br>
        <a href="$editUrl">Edit</a>  - 
        <a href="$retrieveUrl" target="_blank">Update</a>
TAG;
		$text .= $admin;
        }
     
        return $text;
    }
}
?>