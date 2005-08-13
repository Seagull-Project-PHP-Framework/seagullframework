<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | Output.php                                                               |
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
// $Id: Output.php,v 1.4 2005/05/09 23:55:20 demian Exp $

class ShopOutput 
{
    
    function nl2br($inText) 
    {  
        return nl2br(htmlspecialchars($inText));
    }
    
    
    function formatPrice ($price) 
    {
    	return number_format($price, 2, ',', '.');
    }
    
    function formatLeuGreu ($price)
    {
        return number_format($price/10000, 2, ',', '.');
    }
    
    function currencyOptions($selectedCurrency = null) 
    {
        $conf = & $GLOBALS['_SGL']['CONF'];
        $aCurrency = array();
        foreach($conf['exchangeRate'] as $key=>$value) {
          $aCurrency[$key] = SGL_Output::translate($key);   
        }
        $currencyOptions = SGL_Output::generateSelect($aCurrency, @ $selectedCurrency);
        
        return $currencyOptions;
    }
    
    
    function currencyConverter($amount, $from, $to, $format = true) 
    {
        $conf = & $GLOBALS['_SGL']['CONF'];
        if (!(array_key_exists($from,$conf['exchangeRate']) and array_key_exists($to,$conf['exchangeRate']))) {
            return '';    
        } 
        
        $price = $amount * $conf['exchangeRate'][$from] / $conf['exchangeRate'][$to];
        
        if ($format) {
           $price = number_format($price, 0, ',', '.');
        } 
        
        return $price;
    }
    
    
    function formatChildrenCat($aCats) {
        $url = SGL_Url::makeLink();
        $url .= 'frmCatID/';
        $ret = '';
        if (is_array($aCats) and count($aCats) > 0) {
            // Create a table row with 3 columns
            for($i=0;$i<=count($aCats);$i++) {
                $ret .= "<tr>\n<td>";
                if (!empty($aCats[$i]['label'])) {
                       $ret .= '<a href="'.$url.$aCats[$i]['category_id'].'/">';
                       $ret .= $aCats[$i]['label'].'</a>';
                }
                $ret .= "</td>\n<td>";
                $i++;
                if (!empty($aCats[$i]['label'])) {
                       $ret .= '<a href="'.$url.$aCats[$i]['category_id'].'/">';
                       $ret .= $aCats[$i]['label'].'</a>';
                }
                $ret .= "</td>\n<td>";
                $i++;
                if (!empty($aCats[$i]['label'])) {
                       $ret .= '<a href="'.$url.$aCats[$i]['category_id'].'/">';
                       $ret .= $aCats[$i]['label'].'</a>';
                }
                $ret .= "</td>\n</tr>";
            }
        }
        return $ret;
    }
    
    function formatChildrenCat2($aCats) {
        $url = SGL_Url::makeLink();
        $url .= 'frmCatID/';
        $ret = '';
        if (is_array($aCats) and count($aCats) > 0) {
            // Create a table row with 3 columns
            for($i=0;$i<count($aCats);$i++) {
                $ret .= "<tr>\n<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                if (!empty($aCats[$i]['label'])) {
                       $ret .= '<a class="subC" href="'.$url.$aCats[$i]['category_id'].'/">';
                       $ret .= $aCats[$i]['label'].'</a>';
                }
                $ret .= "</td>\n</tr>\n";
            }
        }
        return $ret;
    }
    
    function descriptionToHTML ($inText) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aRet = '';
        $ret = '';
        $key = '';
        $aLines = explode("\n",$inText);
        foreach($aLines as $lineNo => $line) {
            $aSplit = explode(':',$line,2);
            if (is_array($aSplit) and count($aSplit) == 2) {
                if (strlen($aSplit[0]) > 0) {
                    $key = $aSplit[0];
                }
                if (strlen($aSplit[1]) > 0) {
                    $aRet[$key] .= "<br>\n"+$aSplit[1];
                } else {
                    $aRet[$key] = $aSplit[1];
                }
            }
        }
        foreach($aRet as $key => $value) {
            $ret .= '<span class="productDescKey">'.$key.'</span>'.$value."<BR>\n";
        }   
    	return ($ret);
    }
    
}
?>
