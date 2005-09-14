<?php

// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | PopularMgr.php                                                            |
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
 * Manages Popular products
 *
 * @package Cart
 * @author  Tomas Bagdanavicius <info@lwis.net>
 * @version 1.00
 */

class PopularMgr extends SGL_Manager
{
    function PopularMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->module       = 'cart';
        $this->template     = 'orderPopular.html';
        $this->pageTitle    = 'Popular';

        $this->_aActionsMapping =  array(
            'list'      => array('list')
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

	/**
	* List most popular products
	* 
	* @access public
	* TODO: optimize code, no time for this now :)
	*/

	function _list(& $input, & $output) {

		SGL :: logMessage(null, PEAR_LOG_DEBUG);
		$conf = & $GLOBALS['_SGL']['CONF'];
        $output->template = 'orderPopular.html';
        $output->pageTitle = 'Cart :: Popular';

		$allowedSortFields = array('orders','quantity', 'product_name', 'balance');
		$orderBy_query = (
		       isset($input->sortBy) 
		   and strlen($input->sortBy) > 0 
           and isset($input->sortOrder) 
		   and strlen($input->sortOrder) > 0 
           and in_array($input->sortBy, $allowedSortFields))
                 ? ' ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder 
				 : ' ORDER BY orders DESC';

		// retrieve this user data
		require_once SGL_ENT_DIR . '/Usr.php';
		$oUser = & new DataObjects_Usr();
		$oUser->get(SGL_HTTP_Session::getUid());

		$dbh = & SGL_DB::singleton();
		$prime_query = "
			SELECT 
				cp.product_name,
				cp.product_code,
				cp.product_id,
				COUNT(cp.product_id) as orders, 
				SUM(cp.quantity) as quantity,
				p.price,
				p.balance
			FROM 
				{$conf['table']['product']} p,
				{$conf['table']['cart_product']} cp ";

		if(SGL_HTTP_Session::get('rid') == 3) {
			$prime_query .= ", 
				{$conf['table']['user']} u,
				{$conf['table']['cart']} c ";
		}

		$prime_query .=	" WHERE cp.product_id = p.product_id ";

		// check whether it is not a super member
		if(SGL_HTTP_Session::get('rid') == 3) {
			$prime_query .= "
				AND cp.cart_id = c.cart_id
				AND c.usr_id = u.usr_id
				AND u.organisation_id = " . $oUser->organisation_id . " ";
		}

	    $prime_query .= "GROUP BY cp.product_id ";

		
		/* build two queries:
		 1) orders ordered by default parameters 
		 2) user required ordering parameters
			*/

		$query_default = $prime_query.' ORDER BY orders DESC';
		$query_required = $prime_query.$orderBy_query;

		// fetch both queries
		$res =& $dbh->query($query_default, array());
			if (DB::isError($res)) {
				SGL :: raiseError('Could not query');
			}
		$totalItems = (int)$res->numRows();
		while ($res->fetchInto($row, DB_FETCHMODE_ASSOC)) {
			$aDefaultData[] = $row;
		}
        $res->free();


		// fetch the second query
		$res2 =& $dbh->query($query_required, array());
			if (DB::isError($res2)) {
				SGL :: raiseError('Could not query');
			}
		$totalItems2 = (int)$res2->numRows();
		while ($res2->fetchInto($row2, DB_FETCHMODE_ASSOC)) {
			$aRequiredData[] = $row2;
		}
        $res2->free();

		/*
		 End of two queries build
			*/

		// check if there are any results returned
		if($totalItems > 0 || $totalItems2 > 0 && $totalItems == $totalItems2) {

		// Save original sort
		$originalDefaultData = $aDefaultData;

		// Sort by quantity
		foreach($aDefaultData as $key => $val) {
			$quantity[$key] = $val['quantity'];
		}
		array_multisort($quantity, SORT_DESC, SORT_NUMERIC, $aDefaultData);






			/*
			 * TODO: this should be a function
			 */

			// merge results of both queries
			$place = 1;
			$mem = array();
			foreach($originalDefaultData as $key => $data) {

					$found_id = $this->search_array($aRequiredData,'product_id',$data['product_id']);
					$orders = $aRequiredData[$found_id]['orders'];
					if(array_key_exists($orders, $mem)) {
						$aRequiredData[$found_id]['no'] = $mem[$orders];
					} else {	
						$aRequiredData[$found_id]['no'] = $place;
						$mem[$orders] = $place;
						$place++;
					}
					unset($found_id);
			}
			unset($mem,$key,$data,$place);

			$place = 1;
			$mem = array();
			foreach($aDefaultData as $key => $data) {

					$found_id = $this->search_array($aRequiredData,'product_id',$data['product_id']);
					$quantity = $aRequiredData[$found_id]['quantity'];
					if(array_key_exists($quantity, $mem)) {
						$aRequiredData[$found_id]['noq'] = $mem[$quantity];
					} else {	
						$aRequiredData[$found_id]['noq'] = $place;
						$mem[$quantity] = $place;
						$place++;
					}
					unset($found_id);
			}

			// yet another merge 

			unset($originalDefaultData,$place);

			$limit = 5 * $_SESSION['aPrefs']['resPerPage'];
			$pagerOptions = array ('mode' => 'Sliding', 'delta' => 3, 'perPage' => $limit, 'totalItems' => $totalItems);
			$output->totalItems = $totalItems;
			unset($totalItems2);

			require_once 'Pager/Pager.php';
			$pager = Pager::factory($pagerOptions);

			$page = array();
			$page['totalItems'] = $pagerOptions['totalItems'];
			$page['links'] = $pager->links;
			$page['page_numbers'] = array(
				'current' => $pager->getCurrentPageID(),
				'total'   => $pager->numPages()
			);
			list($page['from'], $page['to']) = $pager->getOffsetByPageId();

			/*
			 * TODO: this should be a function
			 */

			if($input->sortBy == "no") {
				foreach($aRequiredData as $key => $val) {
					$no[$key] = $val['no'];
				}
				$sort = ($input->sortOrder == "ASC") ? SORT_ASC : SORT_DESC; 
				array_multisort($no, $sort, SORT_NUMERIC, $aRequiredData);
			}

			if($input->sortBy == "noq") {
				foreach($aRequiredData as $key => $val) {
					$noq[$key] = $val['noq'];
				}
				$sort = ($input->sortOrder == "ASC") ? SORT_ASC : SORT_DESC; 
				array_multisort($noq, $sort, SORT_NUMERIC, $aRequiredData);
			}


			$aRequiredDataNew = array_slice($aRequiredData, $page['from']-1, $limit);
			unset($aRequiredData);

			$page['data'] = array();
			$page['data'] = $aRequiredDataNew;

			if(is_array($page['data']) && count($page['data'])) {
                $output->pager = ($page['totalItems'] <= $limit) ? false : true;
            }

			$output->aPagedData = $page;
			
		} else {
			$output->totalItems = '0';
			SGL :: raiseMsg('There are no popular products listed');
		}
		$output->addOnLoadEvent('document.frmCartMgrChooser.popular.disabled = true');

	}


	/**
    * Search two dimensional an array for an approprate value according to a given key
	*
	* @return first dimension key
	*/
	function search_array(&$array, $key_to_search, $query) {
		foreach($array as $key => $value) {
			if(isset($value[$key_to_search]) 
				&& $value[$key_to_search] == $query)
				return $key;	
			else continue;
		}
	}



} // end class PopularMgr

?>
