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
 * Lists Payments for statistical purposes
 *
 * @package Cart
 * @author  Tomas Bagdanavicius <info@lwis.net>
 * @version 1.00
 */

class PaymentMgr extends SGL_Manager
{
    function PaymentMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->module       = 'cart';
        $this->template     = 'paymentList.html';
        $this->pageTitle    = 'Payment';

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

	/*
	 * Basic statistical payments list
	 *
	 * @access admin, extended member
	 */

	function _list(& $input, & $output) {

		SGL :: logMessage(null, PEAR_LOG_DEBUG);
		$conf = & $GLOBALS['_SGL']['CONF'];
        $output->template = 'paymentList.html';
        $output->pageTitle = 'Cart :: Payment';

		// retrieve this user data
		require_once SGL_ENT_DIR . '/Usr.php';
		$oUser = & new DataObjects_Usr();
		$oUser->get(SGL_HTTP_Session::getUid());

		$allowedSortFields = array('debt','last_payment', 'user');
		$orderBy_query = (isset($input->sortBy) 
		   and strlen($input->sortBy) > 0 
           and isset($input->sortOrder) 
		   and strlen($input->sortOrder) > 0 
           and in_array($input->sortBy, $allowedSortFields))
                 ? ' ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder 
				 : ' ORDER BY id DESC';

		$dbh = & SGL_DB::singleton();

		// check whether it is not an extended member
		if(SGL_HTTP_Session::get('rid') == 3) {
			$query = "
				SELECT 
					p.debt, 
					IFNULL(p.last_payment, '--') as last_payment,
					u.username as user
				FROM payment p, usr u
				WHERE p.user_id = u.usr_id
				AND u.organisation_id = " . $oUser->organisation_id . "
				" . $orderBy_query;
		} else {
			$query = "
				SELECT 
					p.debt, 
					IFNULL(p.last_payment, '--') as last_payment,
					u.username as user
				FROM payment p, usr u 
				WHERE p.user_id = u.usr_id
				" . $orderBy_query;
		}


		$limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'      => 'Sliding',
            'delta'     => 3,
            'perPage'   => $limit,
            'totalItems'=> $input->totalItems,
        );

		$aPagedData = SGL_DB::getPagedData($dbh, $query, $pagerOptions);

		if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }

		$output->totalItems = $aPagedData['totalItems'];
		$output->aPagedData = $aPagedData;
			

		$output->addOnLoadEvent('document.frmCartMgrChooser.payment.disabled = true');
	}


} // end of class PaymentMgr

?>
