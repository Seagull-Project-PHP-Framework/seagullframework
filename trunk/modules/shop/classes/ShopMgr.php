<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | ShopMgr.php                                                               |
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
// $Id: ShopMgr.php,v 1.5 2005/05/09 23:55:20 demian Exp $

require_once SGL_ENT_DIR . '/Product.php';
require_once SGL_ENT_DIR . '/Price.php';
require_once SGL_MOD_DIR . '/navigation/classes/CategoryMgr.php';

if ($GLOBALS['_SGL']['CONF']['ShopMgr']['multiCurrency']) {
    require_once SGL_MOD_DIR . '/rate/classes/RateMgr.php';
}

/**
 * To allow users to contact site admins.
 *
 * @package shop
 * @author  Benea Rares <rbenea@bluestardesign.ro>  
 * @version $Revision: 1.5 $
 * @since   PHP 4.1
 */
class ShopMgr extends SGL_Manager 
{
    
    function ShopMgr() 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->module = 'shop';
        $this->pageTitle = 'Shop';
        $this->template = 'productList.html';
        $this->_aActionsMapping = array (
            'list' => array ('list'),
            'fastList' => array ('fastList'),  
            'details' => array ('details'), 
            'order' => array ('order'),);
            
       
        $this->catMgr = & new CategoryMgr();
        
        //TO DO: activate rate manager
        $conf = & $GLOBALS['_SGL']['CONF'];
        if ($conf['ShopMgr']['multiCurrency']) {
            $rateMgr = & new RateMgr();
        } else {
            $conf['exchangeRate'][$conf['ShopMgr']['defaultCurrency']] = $conf['ShopMgr']['defaultExchange'];
        }
    }

    function validate($req, & $input) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        
        $this->validated = true;
        $input->error = array ();
        $input->pageTitle = $this->pageTitle;
        $input->template = $this->template;
        $input->masterTemplate = $this->masterTemplate;
        $input->javascriptSrc   = array('TreeMenu.js');
        $input->action = ($req->get('action')) ? $req->get('action') : 'list';
        $input->catId = (int) ($req->get('frmCatID'));
        $input->productId = $req->get('pid');
        $input->keywords = $req->get('keywords');
        $input->sortBy      = SGL_Util::getSortBy($req->get('frmSortBy'), SGL_SORTBY_USER);
        $input->sortOrder   = SGL_Util::getSortOrder($req->get('frmSortOrder'));

        $input->totalItems = $req->get('totalItems');
        $input->resPerPage = $req->get('resPerPage');

        // autopagination
        $input->from = ($req->get('frmFrom')) ? $req->get('frmFrom') : 0;
        
        if ($input->action == 'list' or $input->action == 'fastList') {
            if (!empty ($input->catId) and $input->catId != $conf['ShopMgr']['rootCatID']) {
                $input->childrenCat = $this->catMgr->getChildren($input->catId);    
            } else {
                $input->childrenCat = $this->catMgr->getChildren($conf['ShopMgr']['rootCatID']);
            }
        }

        //  if errors have occured
        if (isset ($aErrors) && count($aErrors)) {
            SGL_Output::msgSet('Please fill in the indicated fields');
            $input->error = $aErrors;
            $input->template = 'productList.html';
            $this->validated = false;
        }

    }

    function display(& $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $conf = & $GLOBALS['_SGL']['CONF'];
        
        // Show the 'Buy' button if you set showCart in conf.ini
        if (isset ($conf['ShopMgr']['showCart']) and $conf['ShopMgr']['showCart'] == 1) {
            $output->showCart = 1;
        }
        
        // generate the path and current category name from the top page.
        if (isset($output->catID) and !empty($output->catID)) {
            $output->path = $this->catMgr->getBreadCrumbs($output->catID, true, 'linkCrumbsAlt1', true);
            $output->currentCat = $this->catMgr->getLabel($output->catID);
        }
    }
    
    
    /**
     * Generate a compressed list of products. Just set the new template and
     * results / page then call the _list() methos
     * 
     * @access public
     * 
     */
    function _fastList(& $input, & $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'productFastList.html';
        
        if (isset($input->resPerPage) and $input->resPerPage > 0) {
            $limit = $input->resPerPage;    
        } else {
            $limit = (int) $_SESSION['aPrefs']['resPerPage'];
        }
        
        // display 5 times more results then normal list()
        $input->resPerPage = 5*$limit; 
        
        $this->_list($input, $output); 
    }
    

    /**
     * Generate a list of products from the category and 3 level deep
     * subcategories. Filter the result by keywords if set.
     * 
     * @access public
     * 
     */
    function _list(& $input, & $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $conf = & $GLOBALS['_SGL']['CONF'];
        
        $usrId = $_SESSION['uid'];
        
        // If not set in aPrefs take the defaults from conf.ini
        $discount = isset ($_SESSION['aPrefs']['productDiscount']) 
            ? (int) $_SESSION['aPrefs']['productDiscount'] 
            : $conf['ShopMgr']['defaultDiscount'];         
        $vat = isset ($_SESSION['aPrefs']['VAT']) ? $_SESSION['aPrefs']['VAT'] : $conf['ShopMgr']['defaultVAT'];
        
        // autopagination
        if (isset($input->resPerPage) and $input->resPerPage > 0) {
            $limit = $input->resPerPage;    
        } else {
            $limit = (int) $_SESSION['aPrefs']['resPerPage'];
        }
        
        $dbh = & SGL_DB::singleton();
        
        // ORDER BY query generation and protection
        $orderBy_query = '';
        $allowedSortFields = array('manufacturer','name','price');
        if (isset($input->sortBy) and strlen($input->sortBy) > 0 
           and isset($input->sortOrder) and strlen($input->sortOrder) > 0 
           and in_array($input->sortBy, $allowedSortFields)) {
                $orderBy_query = 'ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder ;
                $imgDirVar = 'imgDir_'.$input->sortBy .'_'. $input->sortOrder ;
                $output-> $imgDirVar = '_on';
        } else {
            $orderBy_query = 'ORDER BY price DESC';
        }

        if (!empty ($input->catId) and $input->catId != $conf['ShopMgr']['rootCatID']) {
            
            // IF valid catID ... 
            
            // If $keywords are specified generate search query
            $search_query = '';
            if (isset ($input->keywords))
                if (strlen($input->keywords) > 1) {
                    $aWords = explode(' ', $input->keywords);
                    $search_query = "AND CONCAT(manufacturer, ' ', name, ' ', description) LIKE '%";
                    foreach ($aWords as $word) {
                        $search_query .= addslashes($word).'%';
                    }
                    $search_query .= "' ";

                    $output->keywords = $input->keywords;
                }
                
            // Include in to the list all the products from 3 catogory levels down 
            $category_query = '(product.cat_id='.$input->catId;
            foreach($input->childrenCat as $subCat) {
                $category_query .= ' OR product.cat_id='.$subCat['category_id'];
                $subSub = $this->catMgr->getChildren($subCat['category_id']);
                foreach($subSub as $subSubCat) {
                    $category_query .= ' OR product.cat_id='.$subSubCat['category_id'];
                }
            }
            $category_query .= ')';
                
            
            $query = 'SELECT product.*,product.price as default_price, IFNULL(price.price, product.price*(100-'.$discount.')/100) AS price, 
                      IFNULL(price.currency, product.currency) AS currency    
                      FROM product LEFT JOIN price ON price.product_id=product.product_id AND price.usr_id = '.$usrId.' 
                      WHERE 1=1 AND '.$category_query.' '.$search_query.' '.$orderBy_query;
        } else {
            
            // If not valid catID show only the promotion products from all categories
            $query = 'SELECT product.*,product.price as default_price, IFNULL(price.price, product.price*(100-'.$discount.')/100) AS price, 
                      IFNULL(price.currency, product.currency) AS currency  
                      FROM product LEFT JOIN price ON price.product_id=product.product_id AND price.usr_id = '.$usrId.'
                      WHERE promotion >= 1 '. $orderBy_query; 
        }
        
        
        $pagerOptions = array ('mode' => 'Sliding', 'delta' => 3, 'perPage' => $limit, 'totalItems' => $input->totalItems);
        $aPagedData = SGL_DB::getPagedData($dbh, $query, $pagerOptions);
        
        // return on DB:Error
        if (DB::isError($aPagedData)) {
            $output->catID = $input->catId;
            $output->totalItems = 0;
            unset($output->aPagedData); 
            error_log($query);
            return;
        }
        
        if (is_array($aPagedData) && count($aPagedData['data'])) {
            
            foreach ($aPagedData['data'] as $key => $value) {
                $product = (object) $aPagedData['data'][$key];
                
                // adding path to img file
                if (isset ($product->img) AND $product->img != '' AND file_exists(SGL_WEB_ROOT.'/'.$conf['imageUpload']['thumb'].'/'.$product->img))
                    $product->img = SGL_BASE_URL.'/'.$conf['imageUpload']['thumb'].'/'.$product->img;
                else
                    $product->img = SGL_BASE_URL.'/'.$conf['imageUpload']['thumb'].'/no_image.jpg';

                // Price computation
                if (isset ($product->price)) {
                    $product->priceVAT = $product->price * $vat;
                    
                    // create old price value if discount is applied
                    if (isset($product->default_price) and $product->price != $product->default_price) {
                        $product->oldPrice = $product->default_price;
                    }
                }

                $product->statusString = '';
                if (isset ($product->status) AND @ $product->status > 0) {
                    $product->statusString = $conf['statusOpts'][$product->status];
                }
                
                $aPagedData['data'][$key] = $product;
            }
        }
        
        $output->catID = $input->catId;
        $output->totalItems = $aPagedData['totalItems'];

        $output->aPagedData = $aPagedData;
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }
        
    }


    /**
    * Display product details
    * 
    * @access public
    * 
    */
    function _details(& $input, & $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->pageTitle = 'Product details';
        $output->template = 'productDetails.html';
        $output->results = null;
        
        $conf = & $GLOBALS['_SGL']['CONF'];
        $usrId = $_SESSION['uid'];
        
        // If not set in aPrefs take the defaults from conf.ini
        $discount = isset ($_SESSION['aPrefs']['productDiscount']) 
            ? (int) $_SESSION['aPrefs']['productDiscount'] 
            : $conf['ShopMgr']['defaultDiscount'];         
        $vat = isset ($_SESSION['aPrefs']['VAT']) ? $_SESSION['aPrefs']['VAT'] : $conf['ShopMgr']['defaultVAT'];

        $dbh = & SGL_DB::singleton();

        $query = 'SELECT product.*,product.price as default_price, IFNULL(price.price, product.price*(100-'.$discount.')/100) AS price, 
                  IFNULL(price.currency, product.currency) AS currency   
                  FROM product LEFT JOIN price ON price.product_id=product.product_id AND price.usr_id = ? 
                  WHERE  product.product_id = ?';

        $sth = $dbh->prepare($query);
        $aQueryData = array ($usrId, $input->productId);
        $result = $dbh->execute($sth, $aQueryData);

        // return on DB:Error
        if (DB::isError($result)) {
            SGL::logMessage(print_r($query,true));
            return;
        }

        // return if product not found
        if ($result->numRows() == 0) {
            return;
        } 
            
        
        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);          
        
        $product = (object) $row;
        // product thumb path set                   
        if (isset ($product->img) AND $product->img != '' AND file_exists(SGL_WEB_ROOT.'/'.$conf['imageUpload']['thumb'].'/'.$product->img)) {
            $product->img = SGL_BASE_URL.'/'.$conf['imageUpload']['directory'].'/'.$product->img;
        } else {
            $product->img = SGL_BASE_URL.'/'.$conf['imageUpload']['directory'].'/no_image.jpg';
        }

        // Description unserialize
        if (isset ($product->description)) {
            $unser = @ $this->_descriptionToAray($product->description);
            if (is_array($unser)) {
                $product->aDescription = $unser;
            } else {
                $product->aDescription = array ();
            }
        }
        
        // Price computation
        if (isset ($product->price)) {
            $product->priceVAT = $product->price * $vat;
            
            // create old price value if discount is applied
            if (isset($product->default_price) and $product->price != $product->default_price) {
                $product->oldPrice = $product->default_price;
            }
        }

        $product->statusString = '';
        if (isset ($product->status) AND @ $product->status > 0) {
            $product->statusString = $conf['statusOpts'][$product->status];
        }

        $output->product = $product;        
        
        $output->catID = $product->cat_id;
    }

    /**
    * accessed then the buy button clicked. It takes the product data (name,
    * price, etc..) and creates an array. The array is serialized, encoded
    * base64 and sent to the Cart module ( a fast ‘SOAP’ :) )
    * 
    * @access public
    * 
    */
    function _order(& $input, & $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $conf = & $GLOBALS['_SGL']['CONF'];
        $currency = $conf['ShopMgr']['defaultCurrency'];
        
        $usrId = $_SESSION['uid'];
        
        // If not set in aPrefs take the defaults from conf.ini
        $discount = isset ($_SESSION['aPrefs']['productDiscount']) 
            ? (int) $_SESSION['aPrefs']['productDiscount'] 
            : $conf['ShopMgr']['defaultDiscount'];         
        $vat = isset ($_SESSION['aPrefs']['VAT']) ? $_SESSION['aPrefs']['VAT'] : $conf['ShopMgr']['defaultVAT'];

        $dbh = & SGL_DB::singleton();

        $query = 'SELECT product.*, IFNULL(price.price, product.price*(100-'.$discount.')/100) AS price,  
                  IFNULL(price.currency, product.currency) AS currency 
                  FROM product LEFT JOIN price ON price.product_id=product.product_id AND price.usr_id = ? 
                  WHERE  product.product_id = ?';

        $sth = $dbh->prepare($query);
        $aQueryData = array ($usrId, $input->productId);
        $result = $dbh->execute($sth, $aQueryData);
        
        // return on DB:Error
        if (DB::isError($result)) {
            SGL::logMessage(print_r($query,true));
            // On error return to product list
            SGL::raiseMsg('Invalid product ID');
            SGL_HTTP::redirect(array ('action' => 'list'));
            return;
        }

        // return if product not found
        if ($result->numRows() == 0) {
             // On error return to product list
            SGL::raiseMsg('Invalid product ID');
            SGL_HTTP::redirect(array ('action' => 'list'));
            return;
        } 

  
        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        $product = (object) $row;

        require_once SGL_MOD_DIR.'/cart/classes/Item.php';

        $oOrderItem = new Item();
        $oOrderItem->id = $product->product_id;
        $oOrderItem->name = $product->name;
        $oOrderItem->type = 'product';

        // TO DO: check if currency exist and != 0
        $oOrderItem->price = $product->price * $vat * $conf['exchangeRate'][$product->currency] / $conf['exchangeRate'][$currency];
        $oOrderItem->quantity = 1;
        
        $oOrderItemSer = urlencode(base64_encode(serialize($oOrderItem)));
        
        SGL_HTTP::redirect(array (
            'action' => 'insert', 
            'managerName' => 'cart', 
            'moduleName' => 'cart', 
            'data' => $oOrderItemSer));
    }
    
    function _descriptionToAray($inText) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aRet = '';
        $i = 0;
        $aLines = explode("\n",$inText);
        foreach($aLines as $lineNo => $line) {
            $aSplit = explode(':',$line,2);
            if (is_array($aSplit) and count($aSplit) == 2) {
                if (strlen($aSplit[0]) == 0 and strlen($aSplit[1]) > 0) {
                   $aRet[$i]['value'] .= $aSplit[1];
                } else {
                   $key = $aSplit[0];
                   $i++; 
                   $aRet[$i]['key'] = $key;
                   $aRet[$i]['value'] = $aSplit[1];
                }
            }
        }
        return $aRet;
    }
}
?>