<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
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
// | Seagull 0.5                                                               |
// +---------------------------------------------------------------------------+
// | DA_Publisher.php                                                            |
// +---------------------------------------------------------------------------+
// | Authors:   Demian Turner <demian@phpkitchen.com>                          |
// +---------------------------------------------------------------------------+
// $Id: DA_Default.php,v 1.14 2005/06/21 23:26:24 demian Exp $

/**
 * Data access methods for the publisher module.
 *
 * @package Default
 * @author  Demian Turner <demian@phpkitchen.com>
 * @copyright Demian Turner 2005
 * @version $Revision: 1.14 $
 */
class DA_Publisher
{
    /**
     * Constructor - set default resources.
     *
     * @return DA_Default
     */
    function DA_Publisher()
    {
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();
        $this->dbh = $this->_getDb();
    }

    function &_getDb()
    {
        $locator = &SGL_ServiceLocator::singleton();
        $dbh = $locator->get('DB');
        if (!$dbh) {
            $dbh = & SGL_DB::singleton();
            $locator->register('DB', $dbh);
        }
        return $dbh;
    }

    /**
     * Returns a singleton DA_Default instance.
     *
     * example usage:
     * $da = & DA_Default::singleton();
     * warning: in order to work correctly, the DA
     * singleton must be instantiated statically and
     * by reference
     *
     * @access  public
     * @static
     * @return  DA_Default reference to DA_Default object
     */
    function &singleton()
    {
        static $instance;

        // If the instance is not there, create one
        if (!isset($instance)) {
            $class = __CLASS__;        	
            $instance = new $class();
        }
        return $instance;
    }

    function getItemsWithAttributes($paginated = false)
    {
        $query = "SELECT 
        			it.item_type_id, 
        			it.item_type_name, 
        			itm.item_type_mapping_id, 
        			itm.field_name, 
        			itm.field_type
                  FROM 
                  	{$this->conf['table']['item_type']} it, 
                  	{$this->conf['table']['item_type_mapping']} itm
                  WHERE itm.item_type_id = it.item_type_id";
        
        if ($paginated) {
	        $limit = $_SESSION['aPrefs']['resPerPage'];
	        $pagerOptions = array(
	            'mode'     => 'Sliding',
	            'delta'    => 3,
	            'perPage'  => $limit,
	        );        	
	        $ret = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);
	        
        } else {
        	$ret = $this->dbh->getAll($query, $paginated = false);
        }
        return $ret;
    }
}