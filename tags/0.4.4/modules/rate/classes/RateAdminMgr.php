<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | RateAdminMgr.php                                                          |
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
// $Id: RateAdminMgr.php,v 1.6 2005/05/31 23:34:23 demian Exp $

require_once SGL_MOD_DIR . '/rate/classes/RateMgr.php';

/**
 * To allow users to contact site admins.
 *
 * @package shop
 * @author  Rares Benea <rbenea@bluestardesign.ro>  
 * @version $Revision: 1.6 $
 * @since   PHP 4.1
 */
class RateAdminMgr extends RateMgr
{
    function RateAdminMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
                
        $this->module		= 'rate';
        $this->pageTitle    = 'Rates';
        $this->template     = 'rateEdit.html';
        $this->_aActionsMapping =  array(
            'edit'           => array('edit'),
            'update'         => array('update','edit'),
            'retrieve'       => array('retrieve'),
        ); 
        
        $this->_init();   
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
            
        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->template    = $this->template;
        $input->masterTemplate = $this->masterTemplate;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'edit';
        
        $input->exchangeRates    = (array) $req->get('exchangeRates');
        
        
        if ($input->action == 'update') {
            $cErrors = '';
            $vErrors = '';
            //error_log(print_r(var_name,true));
            foreach($input->exchangeRates as $key=>$value) { 
        	   if (!array_key_exists($key,$conf['currency'])) {
                    $cErrors .= $key.' ';
               } elseif (!($value > 0)) { 
                    $vErrors .= $key.' ';
               }
               
            }
            
            if (!empty($cErrors)) {
                $aErrors['currency'] = SGL_Output::translate('Invalid currency').' : '.$cErrors;
            }
            
            if (!empty($vErrors)) {
                $aErrors['currency'] = SGL_Output::translate('Invalid data').' : '.$vErrors;
            }
            
            if (isset($aErrors) && count($aErrors)) {
                $input->template = 'rateEdit.html';
                $input->pageTitle = 'Exchange :: Edit';
                SGL::raiseMsg('Please fill in the indicated fields');
                $input->error = $aErrors;
                //$input->action = 'listExchange';
                $this->validated = false;
                return;
            }
        }
       
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }
    }
       
    
    /**
    * Retrieve exchange rates and display the edit form 
    *
    * @access public
    *
    */
    function _edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'rateEdit.html';
        $output->pageTitle = 'Exchange :: Edit';
        
        $conf = & $GLOBALS['_SGL']['CONF'];
        $exchangeRates = array();
        foreach($conf['currency'] as $currency=>$description) {
            $exchangeRates[$currency] = @$conf['exchangeRate'][$currency];
        }
        
        $output->exchangeRates = $exchangeRates;
        $output->action = 'updateExchange';
    }
    
    
    /**
    * Saves the exchange rate in DB. 
    * Used by update() and retrieve() 
    *
    * @access public
    *
    */
    function _set($currency, $rate, $date = null) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $conf = & $GLOBALS['_SGL']['CONF'];
        
        if (empty($date)) {
            $date = SGL::getTime();
        }

        $dbh = & SGL_DB::singleton();
        
        $query = "DELETE FROM rate WHERE currency='$currency' AND date='$date'";   
        $result = $dbh->query($query);
        if (DB::isError($result)) {
            SGL::raiseError('Incorrect parameter passed to '.__CLASS__.'::'.__FUNCTION__, SGL_ERROR_INVALIDARGS);
            return false;
        }  
      
        $nextId = $dbh->nextId('rate');
        $fields = array(
                    'rate_id' => $nextId,
                    'currency' => $currency,
                    'rate' => $rate,
                    'date' => $date,
                    'last_updated' => SGL::getTime(),
                    );        
        $result = $dbh->autoExecute('rate', $fields, DB_AUTOQUERY_INSERT);
        
        if (DB::isError($result)) {
            SGL::raiseError('Incorrect parameter passed to '.__CLASS__.'::'.__FUNCTION__, SGL_ERROR_INVALIDARGS);
            return false;
        }
        
        return true;
    }
    
    
    /**
    * Updates the exchange rates
    *
    * @access public
    *
    */
    function _update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'rateEdit.html';
        $output->pageTitle = 'Exchange :: Edit';
        
        $conf = & $GLOBALS['_SGL']['CONF'];
        $confFilePath = SGL_MOD_DIR. '/conf.ini';
        
        $result = true;
        foreach($input->exchangeRates as $key=>$value) {
            $result = $result && $this->_set($key,$value);        
        }
        
        if ($result) {
            SGL::raiseMsg('Data saved successfully');
        } else {
            SGL::raiseMsg('Data save error');
        }
        
        $this->_init();
    }
    
    
    /**
    * automatic update of exchange rate with data taken from
    * external web pages.
    *
    * @access public
    *
    */
    function _retrieve(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $conf = & $GLOBALS['_SGL']['CONF'];
        $EUR = null;
        $USD = null;     
      
        $buffer = '';
        $fd = @ fopen ("http://cursvalutar.kappa.ro/", "r");
        if (!$fd) {
            echo "Unable to connect to server";
            exit();
        }
        while(!feof($fd)) {
            $buffer .= fgets($fd,1024);
        } 
        fclose($fd);
    
        $trashHold = $conf['retrieve']['rateTrashHold'];
        // USD update
        if (preg_match('/1 USD =  (\d+) Lei<br />/',$buffer,$match)) {
                 $USD = $match[1];
        }
        if (isset($conf['exchangeRate']['USD'])){
            $oldUSD = $conf['exchangeRate']['USD'];
            if ($oldUSD/$trashHold < $USD and $oldUSD*$trashHold > $USD) { 
                if ($oldUSD != $USD) {
                    $this->_set('USD',$USD);                
                    $date = date("d-m-Y H:i");
                    echo "USD Rate updated at ".$date."\n ";
                    echo "USD Rate: $oldUSD - $USD";
                }
            } else {
                echo "Invalid USD rate: $USD";
            }
        } else {
            $this->_set('USD',$USD);                
            $date = date("d-m-Y H:i");
            echo "USD Rate updated at ".$date."\n ";
            echo "USD Rate: $USD";
        }
        
        // EUR update
        if (preg_match('/1 EUR =  (\d+) Lei<br />/',$buffer,$match)) {
                $EUR = $match[1];
        }
        if (isset($conf['exchangeRate']['EUR'])) {
             $oldEUR = $conf['exchangeRate']['EUR'];
            if ($oldEUR/$trashHold < $EUR and $oldEUR*$trashHold > $EUR) { 
                if ($oldEUR != $EUR) {
                    $this->_set('EUR',$EUR);                
                    $date = date("d-m-Y H:i");
                    echo "EUR Rate updated at ".$date."\n ";
                    echo "EUR Rate: $oldEUR - $EUR";
                }
            } else {
                echo "Invalid EUR rate: $EUR";
            }
        } else {
             $this->_set('EUR',$EUR);                
             $date = date("d-m-Y H:i");
             echo "EUR Rate updated at ".$date."\n ";
             echo "EUR Rate: $EUR";
        }
        exit();   
    }
}
?>