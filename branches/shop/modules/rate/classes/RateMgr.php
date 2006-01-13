<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | RateMgr.php                                                               |
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
// $Id: RateMgr.php,v 1.5 2005/05/09 23:51:51 demian Exp $


/**
 * To allow users to contact site admins.
 *
 * @package shop
 * @author  Rares Benea <rbenea@bluestardesign.ro>  
 * @version $Revision: 1.5 $
 * @since   PHP 4.1
 */
class RateMgr extends SGL_Manager
{
    function RateMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();
                
        $this->module       = 'rate';
        $this->pageTitle    = 'Rates';
        $this->template     = 'rateEdit.html';
        $this->_aActionsMapping =  array(
            'edit'           => array('edit'),
            'update'         => array('update','edit'),
            'retrieve'       => array('retrieve'),
        );   
        
        $this->_init(); 
    }
    
    // TO DO: implement cache 
    /**
    * Take data from DB and store it in $conf 
    *
    * @access public
    *
    */
    function _init() {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
//        $conf = & $GLOBALS['_SGL']['CONF'];
        
        //  Check if rate already set
        if (isset($this->conf['exchangeRate']) and count($this->conf['exchangeRate']) > 0) {
            SGL::logMessage("Rate already set");
            return;
        }
        
//        $dbh = & SGL_DB::singleton();
        
        $query = "SELECT MAX(date) AS date FROM {$this->conf['table']['rate']}";
        $result = $this->dbh->query($query);
        if (DB::isError($result)) {
            SGL::raiseError('Incorrect parameter passed to '.__CLASS__.'::'.__FUNCTION__, SGL_ERROR_INVALIDARGS);
//            $this->_setDefault();
            return false;
        }
        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        if (!is_array($row)) {
            SGL::raiseError('Invalid data in rate table');
            return false;
        } 
       
        $query = "SELECT * FROM {$this->conf['table']['rate']} WHERE date='".$row['date']."'";
        $result = $this->dbh->query($query);
        if (DB::isError($result)) {
            SGL::raiseError('Incorrect parameter passed to '.__CLASS__.'::'.__FUNCTION__, SGL_ERROR_INVALIDARGS);
            $this->setDefault();
            return false;
        }
        if ($result->numRows() < 1) {
            SGL::raiseError('Invalid data in rate table');
            return false;
        }
        while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
            if (isset($row['currency'])) {
                $conf['exchangeRate'][$row['currency']] = $row['rate'];
            } 
        }
    }
}
