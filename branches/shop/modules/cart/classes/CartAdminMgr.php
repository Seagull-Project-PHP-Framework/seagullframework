<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | CartAdminMgr.php                                                          |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2004-2005 Rares Benea & Tomas Bagdanavicius                 |
// |                                                                           |
// | Authors: Rares Benea <rbenea@bluestardesign.ro>                           |
// |          Tomas Bagdanavicius <info@lwis.net>							   |
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
// $Id: Produse.php,v 1.1 2004/11/24 01:07:11 cvsroot Exp $
require_once SGL_ENT_DIR . '/Usr.php';
require_once SGL_ENT_DIR . '/Cart.php';
require_once SGL_MOD_DIR . '/cart/classes/Order.php';
require_once SGL_MOD_DIR . '/cart/classes/Item.php';
require_once SGL_ENT_DIR . '/Cart_product.php';

/**
 * To allow users to contact site admins.
 *
 * @package produse
 * @author  Rares Benea <rbenea@bluestardesign.ro>
 * @version $Revision: 1.1 $
 * @since   PHP 4.1
 */
class CartAdminMgr extends SGL_Manager
{

    var $_order;

    function CartAdminMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();
        
        $this->module		= 'cart';
        $this->pageTitle    = 'Cart Admin';
        $this->template     = 'itemList.html';
        $this->_aActionsMapping =  array(
            'list'      => array('list'),
            'view'      => array('view'),
			'popular'	=> array('popular'),
            'delete'    => array('delete','list'),
#			'update'	=> array('update','list'),
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
        $input->orderId = (int) ($req->get('frmOrderID'));

#		$input->updateStatus = $req->get('frmUpdate');
#		$input->submit      = $req->get('submitted');

        $input->aDelUpd     = $req->get('frmDelUpd');
		$input->aUpdStatus     = $req->get('frmUpdStatus');

        $input->totalItems  = $req->get('totalItems');
        $input->sortBy      = SGL_Util::getSortBy($req->get('frmSortBy'), SGL_SORTBY_USER);
        $input->sortOrder   = SGL_Util::getSortOrder($req->get('frmSortOrder'));



        switch($input->action) {
            case 'delete':
                //if(!isset($input->itemId))
                //    $aErrors[] = 'No item specified';
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
    * List orders
    *
    * @access public
    * @modified Tomas Bagdanavicius
    */
/*    function _list(&$input, &$output)
    {

        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        $output->template = 'listOrders.html';
        $output->pageTitle = 'Cart Admin :: List orders';

        $orderBy_query = '';
        $allowedSortFields = array('cart_id','username','name','c.date_created','total','status','org_name');
        if(isset($input->sortBy)
		   and strlen($input->sortBy) > 0
           and isset($input->sortOrder)
		   and strlen($input->sortOrder) > 0
           and in_array($input->sortBy, $allowedSortFields)) {
                $orderBy_query = 'ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder ;
        } else {
            $orderBy_query = ' ORDER BY c.date_created DESC ';
        }

        $dbh = & SGL_DB :: singleton();

		$oUser = & new DataObjects_Usr();
		$oUser->get(SGL_HTTP_Session::getUid());

		// form a query
        $query = "
			SELECT
				*,c.date_created as date_created,
				c.status as status_id,
				u.username as username,
				CONCAT(u.first_name, ' ', u.last_name) as name " . ",
				o.name as org_name
			FROM
				{$conf['table']['cart']} as c,
				{$conf['table']['user']} as u,
				{$conf['table']['organisation']} as o
			WHERE c.usr_id = u.usr_id
			AND u.organisation_id = o.organisation_id ";

		// check whether it is not a super member
		if(SGL_HTTP_Session::get('rid') == 3) {
			$query .= "
				AND u.organisation_id = " . $oUser->organisation_id . "
				AND u.role_id != 1 ";
		}
		$query .= $orderBy_query;

        $limit = 5 * $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array ('mode' => 'Sliding', 'delta' => 3, 'perPage' => $limit, 'totalItems' => $input->totalItems);
        $aPagedData = SGL_DB::getPagedData($dbh, $query, $pagerOptions);

        if(!DB::isError($aPagedData)) {

			$statuses = SGL_String::translate('aStatuses');

			foreach($aPagedData['data'] as $no => $data) {
				$originalStatus = ($data['status']-1);

				$personalStatuses = $this->array_slice_key($statuses, $originalStatus, ((count($statuses))-$originalStatus));
				$aPagedData['data'][$no]['personalStatuses'] = $personalStatuses;
				unset($personalStatuses);
			}

            if(is_array($aPagedData['data']) && count($aPagedData['data'])) {
                $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
            }

            $output->totalItems = $aPagedData['totalItems'];
			if($aPagedData['totalItems'] == 0)
				SGL :: raiseMsg('There are no orders listed for this account');

            $output->aPagedData = $aPagedData;
           // dumpr($aPagedData['data']);

        }
		$output->addOnLoadEvent('document.frmCartMgrChooser.orders.disabled = true');
    }*/


    function _list(&$input, &$output)
    {

        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'listOrders.html';
        $output->pageTitle = 'Cart Admin::List orders';

        $orderBy_query = '';
        $allowedSortFields = array('cart_id','username','name','c.date_created','total','status','org_name');
        if(  !empty($input->sortBy)
           && !empty($input->sortOrder)
           && in_array($input->sortBy, $allowedSortFields)) {
                $orderBy_query = ' ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder ;
        } else {
            $orderBy_query = ' ORDER BY c.date_created DESC ';
        }

//        $dbh = & SGL_DB :: singleton();

		$oUser = & new DataObjects_Usr();
		$oUser->get(SGL_HTTP_Session::getUid());

		// form a query
        $query = "
			SELECT
				*,c.date_created as date_created,
				c.status as status_id,
				u.username as username,
				CONCAT(u.first_name, ' ', u.last_name) as name " . ",
				o.name as org_name
			FROM
				{$this->conf['table']['cart']} as c,
				{$this->conf['table']['user']} as u,
				{$this->conf['table']['organisation']} as o
			WHERE c.usr_id = u.usr_id
			AND u.organisation_id = o.organisation_id ";

		// check whether it is not a super member
		if(SGL_HTTP_Session::get('rid') == 3) {
			$query .= "
				AND u.organisation_id = " . $oUser->organisation_id . "
				AND u.role_id != 1 ";
		}
		$query .= $orderBy_query;

        $limit = 5 * $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array ('mode' => 'Sliding', 'delta' => 3, 'perPage' => $limit, 'totalItems' => $input->totalItems);
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);

        if(!DB::isError($aPagedData)) {

			$statuses = SGL_String::translate('aStatuses');

			foreach($aPagedData['data'] as $no => $data) {
				$originalStatus = ($data['status']);
                //dumpr($data['status']);
				//$personalStatuses = $this->array_slice_key($statuses, $originalStatus, ((count($statuses))-$originalStatus));
				//echo $originalStatus;
				//dumpr($this->GetStatusList(SGL_HTTP_Session::get('rid'),$originalStatus));
			$personalStatuses = $this->GetStatusList(SGL_HTTP_Session::get('rid'),$originalStatus);
				$aPagedData['data'][$no]['personalStatuses'] = $personalStatuses;
				unset($personalStatuses);
			}

            if(is_array($aPagedData['data']) && count($aPagedData['data'])) {
                $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
            }

            $output->totalItems = $aPagedData['totalItems'];
			if($aPagedData['totalItems'] == 0)
				SGL :: raiseMsg('There are no orders listed for this account');

            $output->aPagedData = $aPagedData;

        }
		$output->addOnLoadEvent('document.frmCartMgrChooser.orders.disabled = true');
    }

    function GetStatusList($rid, $status) {
        //if extended member
        $statuses = SGL_String::translate('aStatuses');
        if($rid == 3) {
            switch($status) {
               case "1" :
                  return array(
                    "1" => $statuses[1],
                    "10" => $statuses[10],
                    "11" => $statuses[11],
                  );
               break;
               case "2" :
               case "10" :
               case "11" :
              //should be able to view changes in read only mode ....
                  return array(
                    "2" => $statuses[2],
                    "10" => $statuses[10],
                    "11" => $statuses[11],
                  );
               break;
            }
  /*          if ($status == 1) {

            } elseif ($status == 2 || $status == 10 || $status == 11) {

            } */
        } elseif($rid == 1){
            switch($status){
                case "1" :
                  return array(
                    "1" => $statuses[1],
                    "4" => $statuses[4],
                    "10" => $statuses[10],
                    "11" => $statuses[11],
                  );
                break;
                case "2" :
                case "10" :
                  return array(
                    "2" => $statuses[2],
                    "4" => $statuses[4],
                    "5" => $statuses[5],
                    "6" => $statuses[6],
                    "11" => $statuses[11],
                  );
                break;
                case "3" :
                case "11" :
                  return array(
                    "3" => $statuses[3],
                  );
                break;
                case "4" :
                  return array(
                    "4" => $statuses[4],
                    "5" => $statuses[5],
                    "6" => $statuses[6],
                  );
                break;
                case "5" :
                  return array(
                    "5" => $statuses[5],
                    "6" => $statuses[6],
                  );
                break;
                case "6" :
                  return array(
                    "6" => $statuses[6],
                  );
                break;
            }
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
        $output->template = 'viewOrder.html';
        $output->pageTitle = 'Cart Admin::View order';

		$statuses = SGL_String::translate('aStatuses');

		// load cart information
        $oCart = & new DataObjects_Cart();
        $oCart->get($input->orderId);
		$statusId = $oCart->status;
        $output->cart = $oCart;
		$output->cart->status = $statuses[$statusId];


		// load product information
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

        $oUser = & new DataObjects_Usr();
        $oUser->get($oCart->usr_id);

        $output->user = $oUser;

        $output->itemCount = 0;
        foreach($output->items as $item) {
            $output->itemCount = $output->itemCount + $item->quantity;
        }

		$output->expand = true;
    }

    /**
    * Delete or update order(s) from DB
	*
    * @access admin
    * @author Tomas Bagdanavicius
    */
    function _delete (& $input, & $output)
    {
        SGL :: logMessage(null, PEAR_LOG_DEBUG);

		if(isset($input->aDelUpd) && count($input->aDelUpd) > 0 && is_array($input->aDelUpd)) {
			if(isset($input->aDelUpd['delete'])) {
			$deleted = array();
				foreach($input->aDelUpd['delete'] as $index => $cart_id) {
					$oCart = & new DataObjects_Cart();
					$oCart->whereAdd("cart_id = '".$cart_id."'");
					$oCart->delete(DB_DATAOBJECT_WHEREADD_ONLY);
					unset($oCart);
					$oCart_product = & new DataObjects_Cart_product();
					$oCart_product->whereAdd("cart_id = '".$cart_id."'");
					$oCart_product->delete(DB_DATAOBJECT_WHEREADD_ONLY);
					unset($oCart_product);
					$deleted[] = $cart_id;
				}
			}

			if(isset($input->aDelUpd['update'])) {
//				$dbh = & SGL_DB::singleton();
				foreach($input->aDelUpd['update'] as $cart_id => $status_id) {
					$oCart = & new DataObjects_Cart();
					$oCart->get($cart_id);
					$primeCartStatus = $oCart->status;

					// stop hack atacks, you can't return to a lower status :)
				/*	if($primeCartStatus > $status_id) {
						SGL::raiseError("Unable to update orders");
					} else*/if(isset($deleted) && in_array($cart_id, $deleted)) {
						continue;
					} else {
						if($primeCartStatus == 1 && $status_id == 2) {
//What we are checking here?  ^^^^
							$usr_id = $oCart->usr_id;
							// Get Payment data
							echo $query = "
									SELECT credit_limit, id, debt, (credit_limit-debt) as balance
									FROM payment
									WHERE user_id = " . $usr_id;

							 $aPayment = $this->dbh->getAll($query);
                        /*        dumpr($input);
                             dumpr($oCart);die();*/
							 
							 if (DB::isError($aPayment)) {
									SGL::raiseError('perhaps no item tables exist', SGL_ERROR_NODATA);
									$registerOrder = false;
							 }
							 $oPayment = $aPayment['0'];

							 $realbalance = $oPayment->balance;
							 if($oCart->total > $realbalance) {
								SGL::raiseMsg('Limit exceeded, could not update status',  SGL_ERROR_INVALIDPOST);
								continue;
							 }

							 $balance = $oPayment->credit_limit;
							 $debt = $oPayment->debt;
							 $PaymentId = $oPayment->id;
							 $total = $oCart->total;

							 unset($query,$aPayment,$oPayment);

							require_once SGL_ENT_DIR . '/Payment.php';
							$oPayment = & new DataObjects_Payment();
							$oPayment->get($PaymentId);
							$oPayment->debt = ($debt + $total);
							$creditLimit = $oPayment->credit_limit;
							if($debt == 0) {
								$oPayment->debt_start_date = SGL_Date::getTime();
							}
							$oPayment->payment_updated_by = SGL_HTTP_Session::getUid();
							$success = $oPayment->update();

						}

						$this->dbh->query('UPDATE ' . $this->conf['table']['cart'] . '
                               SET status = ' . $status_id . '
                               WHERE cart_id =' . $cart_id);
						//SGL :: raiseMsg('Order(s) modified successfully');

					}
				}
			}

		} else {
			SGL :: raiseError('Incorrect parameter passed to '.__CLASS__.'::'.__FUNCTION__, SGL_ERROR_INVALIDARGS);
		}

		unset($deleted);
	}

	function array_slice_key($array, $offset, $len=-1){

	   if (!is_array($array))
		   return FALSE;

	   $length = $len >= 0? $len: count($array);
	   $keys = array_slice(array_keys($array), $offset, $length);
	   foreach($keys as $key) {
		   $return[$key] = $array[$key];
	   }

	   return $return;
   }


} // end class CartAdminMgr
?>
