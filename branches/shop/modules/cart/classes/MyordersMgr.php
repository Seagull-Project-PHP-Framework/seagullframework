<?php

// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | MyordersMgr.php                                                           |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005 Tomas Bagdanavicius                                    |
// |                                                                           |
// | Author: Tomas Bagdanavicius <info@lwis.net>                               |
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

/**
 * List my orders
 *
 * @package Cart
 * @author  Tomas Bagdanavicius <info@lwis.net>
 * @version 1.00
 */

class MyordersMgr extends SGL_Manager
{
    function MyordersMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->module       = 'cart';
        $this->template     = 'myordersList.html';
        $this->pageTitle    = 'My Orders';

        $this->conf = & $GLOBALS['_SGL']['CONF'];
        $this->dbh = & SGL_DB::singleton();
        
        $this->_aActionsMapping =  array(
            'list'      => array('list'),
			'view'		=> array('view')
        );
	}


	function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated        = true;
        $input->pageTitle       = $this->pageTitle;
        $input->masterTemplate  = $this->masterTemplate;
        $input->template        = $this->template;
		$input->action          = ($req->get('action')) ? $req->get('action') : 'list';

		$input->orderId = (int) ($req->get('frmOrderID'));
		$input->totalItems  = $req->get('totalItems');
        $input->sortBy      = SGL_Util::getSortBy($req->get('frmSortBy'), SGL_SORTBY_USER);
        $input->sortOrder   = SGL_Util::getSortOrder($req->get('frmSortOrder'));

        //  if errors have occured
        if (isset($aErrors) && is_array($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }
    }

	/*
	 * Basic statistical my orders list
	 *
	 * @access admin, extended member
	 */

	function _list(& $input, & $output) {

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $orderBy_query = '';
        $allowedSortFields = array('cart_id','c.date_created','total','status');
        if(isset($input->sortBy) 
		   and strlen($input->sortBy) > 0 
           and isset($input->sortOrder) 
		   and strlen($input->sortOrder) > 0 
           and in_array($input->sortBy, $allowedSortFields)) {
                $orderBy_query = ' ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder ;
        } else {
            $orderBy_query = ' ORDER BY date_created DESC ';
        }
        
        $dbh = & SGL_DB :: singleton();

		require_once SGL_ENT_DIR . '/Usr.php';
		$oUser = & new DataObjects_Usr();
		$oUser->get(SGL_HTTP_Session::getUid());

		// form a query
        $query = "
			SELECT 
				*,date_created as date_created, 
				status as status_id
			FROM 
				{$this->conf['table']['cart']} as c
			WHERE usr_id = ". SGL_HTTP_Session::getUid() . $orderBy_query;

        $limit = 5 * $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array ('mode' => 'Sliding', 'delta' => 3, 'perPage' => $limit, 'totalItems' => $input->totalItems);
        $aPagedData = SGL_DB::getPagedData($dbh, $query, $pagerOptions);

        if(!DB::isError($aPagedData)) {

		$statuses = SGL_String::translate('aStatuses');


			foreach($aPagedData['data'] as $no => $data) {
				$aPagedData['data'][$no]['status_id'] = $statuses[$aPagedData['data'][$no]['status_id']];
			}

            if(is_array($aPagedData['data']) && count($aPagedData['data'])) {
                $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
            }

            $output->totalItems = $aPagedData['totalItems'];
			if($aPagedData['totalItems'] == 0) 
				SGL :: raiseMsg('There are no orders listed for this account');

            $output->aPagedData = $aPagedData;

        } 
	}

    /**
    * View order and user details
    *
    * @access public
    * @modified Tomas Bagdanavicius
    */
    function _view (& $input, & $output) 
    {
        SGL :: logMessage(null, PEAR_LOG_DEBUG);
		$this->conf = & $GLOBALS['_SGL']['CONF'];
        $output->template = 'viewOrder.html';
        $input->pageTitle = 'My Orders :: View order';

		$statuses = SGL_String::translate('aStatuses');

		require_once SGL_ENT_DIR . '/Cart.php';
		// load cart information
        $oCart = & new DataObjects_Cart();
        $oCart->get($input->orderId);
		$statusId = $oCart->status;
        $output->cart = $oCart;
		$output->cart->status = $statuses[$statusId];


		// load product information
//		$dbh = & SGL_DB::singleton();

		$query = "
			SELECT 
				cp.product_name as name, 
				cp.product_code as cod1, 
				cp.product_id as id, 
				c.total,
				cp.quantity,
				cp.price,
				cp.price*cp.quantity as total
			FROM 
				{$this->conf['table']['cart']} as  c,
				{$this->conf['table']['cart_product']} as cp
			WHERE c.cart_id = cp.cart_id
			AND c.cart_id = " .$oCart->cart_id;

		$aProducts = $this->dbh->getAll($query);

		$output->items = $aProducts;
		
        if(!is_array($output->items) or count($output->items) < 0) {
            SGL :: raiseMsg('Invalid order ID');
            return;
        }
		
		require_once SGL_ENT_DIR . '/Usr.php';
        $oUser = & new DataObjects_Usr();
        $oUser->get($oCart->usr_id);
        
        $output->user = $oUser;
        
        $output->itemCount = 0;
        foreach($output->items as $item) {
            $output->itemCount = $output->itemCount + $item->quantity;
        }


    }



}

?>
