<?php
require_once SGL_MOD_DIR . '/ecomm/classes/EcommDAO.php';

class RestoreCart extends SGL_Observer
{
    function update($observable)
    {
        $da = EcommDAO::singleton();
        $aResults = $da->getCartDatabyUserId(SGL_Session::getUid());
        if (empty($_SESSION['cartItems'])) {
            if (count($aResults)) {
                foreach ($aResults as $productId => $oResult) {
                    $_SESSION['cartItems'][$productId] = $oResult->quantity;
                }
            }
        }
    }
}
?>