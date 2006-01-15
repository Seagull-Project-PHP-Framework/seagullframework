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
require_once SGL_CORE_DIR . '/Category.php';




if (isset($this->conf['ShopMgr']['multiCurrency']) &&
    $this->conf['ShopMgr']['multiCurrency'] == true) {
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
        parent::SGL_Manager();
        
        $this->module = 'shop';
        $this->pageTitle = 'Shop';
        $this->template = 'productList.html';
        $this->_aActionsMapping = array (
            'list' => array ('list'),
            'fastList' => array ('fastList'),  
            'details' => array ('details'), 
            'order' => array ('order'),
            'mainList' => array ('mainList'),
            'lister' => array ('lister'),
            'search' => array ('search'),
            );

        $this->catMgr = & new SGL_Category();
//        require_once 'DB/DataObject.php';
//        $this->catMgr = DB_DataObject::factory('category');
        
        
        //TO DO: activate rate manager
        //$this->conf = & $GLOBALS['_SGL']['CONF'];
        if (isset($this->conf['ShopMgr']['multiCurrency'])) {
            if($this->conf['ShopMgr']['multiCurrency']) {
                $rateMgr = & new RateMgr();
            } else {
                $this->conf['exchangeRate'][$this->conf['ShopMgr']['defaultCurrency']] = $this->conf['ShopMgr']['defaultExchange'];
            }
        }
    }

    function validate($req, & $input) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated = true;
        $input->error = array ();
        $input->pageTitle = $this->pageTitle;
        $input->template = $this->template;
        $input->masterTemplate = $this->masterTemplate;
        $input->javascriptSrc   = array('TreeMenu.js');
        if ($req->get('action')) {
            $input->action = $req->get('action');
            if ($input->action == 'lister' || $input->action == 'fastList') {
               SGL_HTTP_SESSION::set('listMode', $input->action);
            }
        } else {
            $input->action = 'list';
        }

        $input->catId = (int) ($req->get('frmCatID'));
        $input->productId = $req->get('pid');
        $input->keywords = $req->get('keywords');
        $input->sortBy      = SGL_Util::getSortBy($req->get('frmSortBy'), SGL_SORTBY_USER);
        $input->sortOrder   = SGL_Util::getSortOrder($req->get('frmSortOrder'));
        $input->qty = $req->get('qty');
        $input->cod1 = $req->get('cod1');
        $input->manufacturer = $req->get('manufacturer');
        $input->promotion = $req->get('promotion');
        $input->new_id = $req->get('new_id');
        $input->bargain = $req->get('bargain');
        $input->img = $req->get('img');
        $input->adv = $req->get('adv');
        $input->prevAction = $req->get('prevAction');
//        $input->searchFrmCatID = $req->get('searchFrmCatID');
        $input->totalItems = $req->get('totalItems');
        $input->resPerPage = $req->get('resPerPage');
        $input->s = $req->get('s');
        $input->buyProdID = $req->get('buyProdID');

        // autopagination
        $input->from = ($req->get('frmFrom')) ? $req->get('frmFrom') : 0;
        if ($input->action == 'list' || $input->action == 'fastList'
           || $input->action == 'lister') {
            if ($input->catId > 0 && $input->catId != $this->conf['ShopMgr']['rootCatID']) {
                $input->childrenCat = $this->catMgr->getChildren($input->catId);
            } else {
                $input->childrenCat = $this->catMgr->getChildren($this->conf['ShopMgr']['rootCatID']);
                //$input->action = 'list'; //if root category we display list
            }
        }

        if ($input->action == 'order'){
          if (empty($input->qty)) {
             // maybe we need default quantity??
             $aErrors['qty'] = SGL_Output::translate('Please enter product quantity');
          } elseif (!is_numeric($input->qty) || $input->qty <= 0) {
             $aErrors['qty'] = SGL_Output::translate('Wrong quantity');
             //it would be nice to check if qty not <= BALANCE (amount in warehouses)
          }

          /*if ($input->prevAction == 'details') {
              $input->template = 'productDetails.html';
              $input->action = 'details';
          }*/
        }
        //  if errors have occured
        // ROL is this needed? Can we have any errors???
        // ROL: Yes, if user is buying products and enters wrong quantity.
       /* if (isset ($aErrors) && count($aErrors)) {
            SGL_Output::msgGet('Please fill in the indicated fields');
            $input->error      = $aErrors;
            $this->template    = $input->template;
            $this->validated   = false;
            $input->formAction = $input->action;
            //return $input;
        }
        */
        
        //  if errors have occured
        if (isset ($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            if ($input->prevAction == 'details') {
                $input->template = 'productDetails.html';
                $input->formAction = 'details';
            }
            $this->validated = false;
        }
    }

    function display(& $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // Show the 'Buy' button if you set showCart in conf.ini
        if (isset ($this->conf['ShopMgr']['showCart']) and $this->conf['ShopMgr']['showCart'] == 1) {
            $output->showCart = 1;
        }
        
        // generate the path and current category name from the top page.
        if (isset($output->catID) && !empty($output->catID)) {
            $output->path = $this->catMgr->getBreadCrumbs($output->catID, false, 'linkCrumbsAlt1', false);
            $output->currentCat = $this->catMgr->getLabel($output->catID);
        }
    }

    function _search(& $input, & $output)   {
        $search_query = '';
        if (isset ($input->keywords) && strlen($input->keywords) > 1) {
           $aWords = explode(' ', $input->keywords);
           $search_query = "AND CONCAT(manufacturer, ' ', name, ' ', description) LIKE '%";
           foreach ($aWords as $word) {
               $search_query .= addslashes($word).'%';
           }
           $search_query .= "' ";
           $output->keywords = $input->keywords;
        }

        if (isset($input->cod1) && strlen($input->cod1) > 1) {
            $search_query .= " AND cod1 LIKE '%{$input->cod1}%' ";
            $output->cod1 = $input->cod1;
        }
        
        if (isset($input->manufacturer) && $input->manufacturer != 'any') {
               $search_query .= " AND manufacturer = '{$input->manufacturer}' ";
               $output->manufacturer = $input->manufacturer;
        }
        
        if (isset($input->promotion) && $input->promotion == 'on') {
            $search_query .= " AND promotion = 1 ";
            $output->promotion = $input->promotion;
        }

        if (isset($input->new_id) && $input->new_id == 'on') {
            $search_query .= " AND new_id = 1 ";
            $output->new_id = $input->new_id;
        }

        if (isset($input->bargain) && $input->bargain == 'on') {
            $search_query .= " AND bargain = 1 ";
            $output->bargain = $input->bargain;
        }

        if (isset($input->img) && $input->img == 'on') {
            $search_query .= " AND img IS NOT NULL ";
            $output->img = $input->img;
        }
        if (isset($input->catId) && $input->catId != 0 && is_numeric($input->catId)
           && $input->catId != $this->conf['ShopMgr']['rootCatID']) {
               $branches = $this->GetBranch($input->catId);
               while ($row = $branches->fetchRow(DB_FETCHMODE_ASSOC)) {
                   $s_query .= "cat_id = {$row['category_id']} OR ";
               }
               $search_query .= "AND (".substr($s_query, 0, strlen($s_query)-4) .")";
               $output->catId = $input->catId;
        }

        $input->search_query = $search_query;
        SGL_HTTP_SESSION::set('search_query', $search_query);

        if (!empty($input->search_query)) {
           SGL_HTTP :: redirect(SGL_Output :: makeUrl('list','shop','shop').'s/qry/');
        } else {
            $this->_list($input, $output);
        }
    }

    function GetBranch($catID){

        $qry = "SELECT parent_id, left_id as l, right_id as r
                FROM  {$this->conf['table']['category']}
                WHERE category_id = $catID";
        $result = $this->dbh->query($qry);
        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);

        $qry = "SELECT category_id, root_id, left_id, right_id, parent_id, label
             FROM  {$this->conf['table']['category']}
             WHERE right_id > {$row['l']} AND left_id < {$row['r']} AND root_id = {$this->conf['ShopMgr']['rootCatID']}
             ORDER BY left_id";
        $result2 = $this->dbh->query($qry);

        return $result2;
    }

    function IsLeaf($catID){
        $qry = "SELECT *
                FROM  {$this->conf['table']['category']}
                WHERE category_id = $catID
                    AND category_id NOT IN (
                      SELECT DISTINCT parent_id
                      FROM category)";
        $result = $this->dbh->query($qry);
        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);

        if (empty($row))
           return false;
        else
           return true;
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
        
        if (isset($input->resPerPage) && $input->resPerPage > 0) {
            $limit = $input->resPerPage;    
        } else {
            $limit = (int) $_SESSION['aPrefs']['resPerPage'];
        }
        
        // display 5 times more results then normal list()
        $input->resPerPage = 5*$limit; 
        
        $this->_lister($input, $output);
    }
    
    /**
     * Generate promo shop list
     *
     * @access public
     *
     */
    function _mainList(& $input, & $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'productMain2.html';
        $output->catID = $input->catId;

        $usrId = $_SESSION['uid'];

        $discount = isset ($_SESSION['aPrefs']['productDiscount'])
            ? (int) $_SESSION['aPrefs']['productDiscount']
            : $this->conf['ShopMgr']['defaultDiscount'];
        $vat = isset ($_SESSION['aPrefs']['VAT'])
            ? $_SESSION['aPrefs']['VAT']
            : $this->conf['ShopMgr']['defaultVAT'];
        /* category descriptions
        $qry = "SELECT label, description
                FROM category LEFT JOIN category_description ON cat_id = category_id
                WHERE category_id = {$input->catId}";
        $result = $dbh->query($qry);

        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        $output->cat_name = $row['label'];
        $output->description = $row['description'];
        */
        
        //lets get promotion products from this category only
        $this->conf['ShopMgr']['selectPromoFromAllCategories'] = 0;  //move that to config

        if (!empty ($input->catId) && $input->catId != $this->conf['ShopMgr']['rootCatID']
            && !empty ($input->childrenCat) && $this->conf['ShopMgr']['selectPromoFromAllCategories']) {

            $in = " AND cat_id in (";
            foreach ($input->childrenCat AS $v) {
               $in .= " {$v['category_id']},";
            }
            // lets delete the last comma
            $in = substr($in, 0, strlen($in)-1);
            $in .= ")";

            $cat_id_qry = $in;
        } else {
            $cat_id_qry = "";
        }

        $qry = 'SELECT A.product_id, A.cod1, A.cod2, A.name, A.short_description,
                         A.description, A.order_id, A.link_datasheet,
                         A.img, A.manufacturer,
                         A.link_manufacturer, A.cod2, A.currency,
                         A.warranty,
                         A.cat_id, A.promotion, A.new_id, A.bargain, A.status,
                         A.date_created, A.created_by, A.last_updated, A.updated_by,
                         A.BALANCE,
                         A.price as default_price,
                         IFNULL(price.price, A.price*(100-'.$discount.')/100) AS price,
                         IFNULL(price.currency, A.currency) AS currency
                   FROM product A LEFT JOIN price ON price.product_id = A.product_id
                                                   AND price.usr_id = '.$usrId.'
                   WHERE promotion = 1 '.$cat_id_qry;
/*        $qry = "SELECT img, short_description, cat_id, name
                FROM product
                WHERE promotion = 1".$cat_id_qry;*/
        $result = $this->dbh->query($qry);

        while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
           $arr[] = array(
//              'img' => SGL_BASE_URL.'/'.$this->conf['imageUpload']['thumb'].'/'.$row['img'],
              'img' => $row['img'],
              'short_description' => $row['short_description'],
              'cat_id' => SGL_BASE_URL.'/index.php/shop/action/fastList/frmCatID/'.$row['cat_id'],
              'name' => $row['name'],
              'price' => $row['price'],
              'default_price' => $row['default_price'],
              'currency' => $row['currency'],
              
           );
        }
        // no products in this branch
        if(!empty($arr)) {
           $p = (array)$this->GetPrices($usrId, $arr);

           if (count($arr) > 9) {
              $rand_keys = array_rand($arr, 9);
           } else {
              $rand_keys = array_keys($arr);
           }

           foreach ($rand_keys as $k => $v) {
              $out[] = array (
                'img' => $p[$v]->img,
                'short_description' => SGL_Output::summarise($p[$v]->short_description, 40),
                'cat_id' => $p[$v]->cat_id,
                'name' => $p[$v]->name,
                'priceVAT' => $p[$v]->priceVAT,
                'oldPriceVAT' => (!empty($p[$v]->oldPriceVAT))
                      ? $p[$v]->oldPriceVAT
                      : '',
                'currency' => $p[$v]->currency,

                'breakRow' => (($k+1) % 3 == 0) ? true : false,
              );
           }
           $output->out = $out;
       } 
    }


    function _list(& $input, & $output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (empty($input->s)) {
           unset ($_SESSION['search_query']);
        } else{
           $sq = SGL_HTTP_SESSION::get('search_query');
        }

        switch ($input->action) {
            case "search":
            case "fastList":
            case "lister":
            case "list":
            case "order":
              if (($input->catId == 0 || !$this->IsLeaf($input->catId)) && empty($sq)){
                     $this -> _mainList($input, $output);
              } else {
                  if (SGL_HTTP_SESSION::get('listMode') == 'lister') {
                       // echo "lister";
                     $this->_lister($input, $output);
                  } else {
                      //  echo "list";
                     $this->_fastList($input, $output);
                  }
              }
            break;
            case "mainList":
                  $this->_mainList($input, $output);
            break;
            default:
               die("ERROR".$input->action);
            break;
        }

    }

    /**
     * Generate a list of products from the category and 3 level deep
     * subcategories. Filter the result by keywords if set.
     * a
     * @access public
     * 
     */
    function _lister(& $input, & $output)     {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $usrId = $_SESSION['uid'];

        //if we are comming from breadcrumbs and we should se mainList???
        //lets drop user to list at first
//#######
        /*if ($subCats = $this->catMgr->getChildren($input->catId)) {
            $this -> _list($input, $output);
        }*/
        // If not set in aPrefs take the defaults from conf.ini
        $discount = isset ($_SESSION['aPrefs']['productDiscount'])
            ? (int) $_SESSION['aPrefs']['productDiscount'] 
            : $this->conf['ShopMgr']['defaultDiscount'];
        $vat = isset ($_SESSION['aPrefs']['VAT'])
            ? $_SESSION['aPrefs']['VAT']
            : $this->conf['ShopMgr']['defaultVAT'];
        
        // autopagination
        if (isset($input->resPerPage) && $input->resPerPage > 0) {
            $limit = $input->resPerPage;    
        } else {
            $limit = (int) $_SESSION['aPrefs']['resPerPage'];
        }
        
        // ORDER BY query generation and protection
        $allowedSortFields = array('manufacturer','name','price');
        if (  !empty($input->sortBy)
           && !empty($input->sortOrder)
           && in_array($input->sortBy, $allowedSortFields)) {
                $orderBy_query = 'ORDER BY cat_id, ' . $input->sortBy . ' ' . $input->sortOrder ;
                $imgDirVar = 'imgDir_'.$input->sortBy .'_'. $input->sortOrder ;
                $output-> $imgDirVar = '_on';
        } else {
            $orderBy_query = 'ORDER BY order_id DESC';
        }


        //search only in appropriate category.
        if (!isset($input->catId)) {
             $category_query = "";
        } else {
             $category_query = 'AND (A.cat_id='.$input->catId. ')';
        }

        // If we have search_query 
        $search_query = '';
        $tmp = SGL_HTTP_SESSION::get('search_query');
        if(!empty($tmp)) {
            $search_query = $tmp;
            $output->noPath = true;
            $category_query = '';
        }

            // IF valid catID ...
            // Lets get category descriptions

            //##########  #############

           /* $q = "SELECT B.description, A.label AS cat_name
                  FROM category A LEFT JOIN category_description B ON (A.category_id = B.cat_id)
                  WHERE A.root_id = 4 AND A.category_id = '{$input->catId}'";
            
            $result = $this->dbh->query($q);
            if ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
                $output->cat_description = $row['description'];
                $output->cat_name = $row['cat_name'];
            }*/


            // Include in to the list all the products from 3 catogory levels down 

            //we dont need this
            // there should be 2 options set in configuration -
            // inclusive query when we select all products from lower brnaches and
            // distinct qry when we select products only from current branch (where cat_id=frmCatId)
/*            foreach($input->childrenCat as $subCat) {
                $category_query .= ' OR A.cat_id='.$subCat['category_id'];
                $subSub = $this->catMgr->getChildren($subCat['category_id']);
                Dumpr($subSub);

                foreach($subSub as $subSubCat) {
                    $category_query .= ' OR A.cat_id='.$subSubCat['category_id'];
                }
            }*/
//            $category_query .= ')';
            
            

               // if we use select product.*, ''price'' will be included 2wice

          $query = 'SELECT A.product_id, A.cod1, A.cod2, A.name, A.short_description,
                                 A.description, A.order_id, A.link_datasheet,
                                 A.img, A.manufacturer,
                                 A.link_manufacturer, A.cod2, A.currency,
                                 A.warranty,
                                 A.cat_id, A.promotion, A.new_id, A.bargain, A.status,
                                 A.date_created, A.created_by, A.last_updated, A.updated_by,
                                 A.BALANCE,
                                 A.price as default_price,
                             IFNULL(price.price, A.price*(100-'.$discount.')/100) AS price,
                             IFNULL(price.currency, A.currency) AS currency
                      FROM product A LEFT JOIN price ON price.product_id = A.product_id
                                                   AND price.usr_id = '.$usrId.'
                      WHERE 1=1 '.$category_query.' '.$search_query.' '.$orderBy_query;



        $pagerOptions = array ('mode' => 'Sliding',
                               'delta' => 3,
                               'perPage' => $limit,
                               'totalItems' => $input->totalItems);
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);
        // return on DB:Error
        if (DB::isError($aPagedData)) {
            $output->catID = $input->catId;
            $output->totalItems = 0;
            unset($output->aPagedData); 
            SGL::logMessage(print_r($query,true));
            die("Error ".$query);
            return;
        }

        $aPagedData['data'] = $this->GetPrices($usrId, $aPagedData['data']);

        $output->catID = $input->catId;
        $output->totalItems = $aPagedData['totalItems'];

        $output->aPagedData = $aPagedData;
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }
    }

    function GetPrices($uid, $data_array) {

        $discount = isset ($_SESSION['aPrefs']['productDiscount'])
            ? (int) $_SESSION['aPrefs']['productDiscount']
            : $this->conf['ShopMgr']['defaultDiscount'];
        $vat = isset ($_SESSION['aPrefs']['VAT'])
            ? $_SESSION['aPrefs']['VAT']
            : $this->conf['ShopMgr']['defaultVAT'];

        if (is_array($data_array) && count($data_array)) {
            foreach ($data_array as $key => $value) {
                $product = (object) $data_array[$key];

                // adding path to img file
                if (isset ($product->img) AND $product->img != ''
                    && file_exists(SGL_WEB_ROOT.'/'.$this->conf['imageUpload']['thumb'].'/'.$product->img))
                    $product->img = SGL_BASE_URL.'/'.$this->conf['imageUpload']['thumb'].'/'.$product->img;
                else
                    $product->img = SGL_BASE_URL.'/'.$this->conf['imageUpload']['thumb'].'/no_image.jpg';

                // Price computation
                if (isset ($product->price)) {
                    $product->priceVAT = $product->price * $vat;

                    // create old price value if discount is applied
                    if (isset($product->default_price) && $product->price != $product->default_price) {
                        $product->oldPriceVAT = $product->default_price * $vat;
                        $product->oldPrice = (float)$product->default_price;
                     } else {
                        $product->oldPriceVAT = '';
                        $product->oldPrice = '';
                     }
                    // lets get rid of yet another price...
                    unset($product->default_price);
                }
                // fix this
                $product->statusString = '';
                if (isset ($product->status) AND @ $product->status > 0) {
                   @ $product->statusString = $this->conf['statusOpts'][$product->status];
                }

                $data_array[$key] = $product;
            }
        }
        return $data_array;
    }


    function GetPrice($uid, $product_id){

        $usrId = $_SESSION['uid'];
       // $default_currency = $this->conf['ShopMgr']['defaultCurrency'];
        
        // If not set in aPrefs take the defaults from conf.ini
        $discount = isset ($_SESSION['aPrefs']['productDiscount'])
            ? (int) $_SESSION['aPrefs']['productDiscount']
            : $this->conf['ShopMgr']['defaultDiscount'];
        $vat = isset ($_SESSION['aPrefs']['VAT'])
            ? $_SESSION['aPrefs']['VAT']
            : $this->conf['ShopMgr']['defaultVAT'];

        $query = 'SELECT product.price as default_price, product.name, product.cod1,
                         IF (price.price, price.price, product.price*(100-'.$discount.')/100 ) AS price,
                         IF (price.currency, price.currency, product.currency) AS currency
                  FROM product LEFT JOIN price ON price.product_id=product.product_id
                       AND price.usr_id = ?
                  WHERE  product.product_id = ?';


        $sth = $this->dbh->prepare($query);
        $aQueryData = array ($uid, $product_id);
        $result = $this->dbh->execute($sth, $aQueryData);

        if (DB::isError($result) || $result->numRows() == 0) {
            SGL::logMessage(print_r($query,true));
            // On error return to product list
            SGL::raiseMsg('Invalid product ID'. $query);  //remove this !!!
            SGL_HTTP::redirect(array ('action' => 'list'));
            return;
        }

        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        $default_price = $row['default_price'];
        $price = (float)$row['price'];
        $name = $row['name'];
        $cod1 = $row['cod1'];
        
        $currency = strlen($row['currency']) == 3
            ? $row['currency']
            : $this->conf['ShopMgr']['defaultCurrency'];

        if (isset ($price)) {
             $priceVAT = $price * $vat;

             // create old price value if discount is applied
            if (isset($default_price) && $price != $default_price) {
                $oldPriceVAT = $default_price * $vat;
                $oldPrice = (float)$default_price;
             } else {
                $oldPriceVAT = "";
                $oldPrice = "";
             }
        }
        
        return array("name"       => $name,
                    "product_id"  => $product_id,
                    "priceVAT"    => $priceVAT,
                    "price"       => $price,
                    "oldPrice"    => $oldPrice,
                    "oldPriceVAT" => $oldPriceVAT,
                    "currency"    => $currency);
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
       // $output->results = null;
        $uid = $_SESSION['uid'];

        $isProdVersion = $this->_isProductVariant($input->productId);
        if ($isProdVersion > 0) {
            SGL::raiseMsg('Invalid product ID '. $isProdVersion);  //remove this !!!
            SGL_HTTP::redirect(array ('action' => 'list'));
        } else {
           $output->product = $this->_getProductData($input->productId, $uid);
           $variants = $this->_getProductVariant($input->productId, $uid);
           if (is_array($variants)) {
               $output->variants = $variants;
           }
           $output->catID = $output->product->cat_id;
        };
    }
    
    function _isProductVariant($prodID) {
        $product = & new DataObjects_Product();

        $product->whereAdd("product_id = ".$prodID);
        $product->find();
        $product->fetch();

        if (!isset($product->variantID)) {
            die($prodID." is not a product at all");
        } elseif(is_numeric($product->variantID) && $product->variantID > 0)  {
//             dumpr($product->variantID );
//             echo " is product variant of {$prodID} product";
            return $prodID;
        } else { //if 0
//             echo $product->variantID . " not a product variant. Main product";
            return false;
        }

    }

    function _getProductData($prodID, $userID) {
        $product = & new DataObjects_Product();
        $product->get($prodID);
        //
        // to do error reporting when prod not found...
        // product thumb path set
        if (isset ($product->img) && $product->img != ''
           && file_exists(SGL_WEB_ROOT.'/'.$this->conf['imageUpload']['directory'].'/'.$product->img)) {
            $product->img = SGL_BASE_URL.'/'.$this->conf['imageUpload']['directory'].'/'.$product->img;
        } else {
            $product->img = SGL_BASE_URL.'/'.$this->conf['imageUpload']['directory'].'/no_image.jpg';
        }

        $product->price = $this->GetPrice($userID, $prodID);

        $product->statusString = '';
        if (isset ($product->status) AND @ $product->status > 0) {
            $product->statusString =
                SGL_Output::translate($this->conf['statusOpts'][$product->status]);
        }
        return $product;
    }


    function _getProductVariant($prodID, $userID){
        $product = & new DataObjects_Product();
        $product->whereAdd("variantID = ".$prodID);
        $product->find();

        while ($product->fetch()) {
            $prices = (object)$this->GetPrice($userID, $product->product_id);
              // product thumb path set
            if (isset ($product->img) && $product->img != ''
               && file_exists(SGL_WEB_ROOT.'/'.$this->conf['imageUpload']['thumb'].'/'.$product->img)) {
                $img = SGL_BASE_URL.'/'.$this->conf['imageUpload']['thumb'].'/'.$product->img;
            } else {
                $img = SGL_BASE_URL.'/'.$this->conf['imageUpload']['thumb'].'/no_image.jpg';
            }

            $variants[] = array('cod1' => $product->cod1,
                          'name' => $product->name,
                          'priceVAT' => $prices->priceVAT,
                          'price' => $prices->price,
                          'oldPriceVAT' => $prices->oldPriceVAT,
                          'oldPrice' => $prices->oldPrice,
                          'product_id' => $product->product_id,
                          'currency' => $prices->currency,
                          'thumb' => $img,
            );
        }

        if (isset($variants) && is_array($variants))
           return $variants;
        else false; // maybe to return error???
    }


    /**
    * accessed when the buy button clicked. It takes the product data (name,
    * price, etc..) and creates an array. The array is serialized, encoded
    * base64 and sent to the Cart module ( a fast �SOAP� :) )
    * 
    * @access public
    * 
    */
    function _order(& $input, & $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->conf['ShopMgr']['OnlyMemebersOrder'] = 1; //move this to config

        if ($this->conf['ShopMgr']['OnlyMemebersOrder']){
            if(SGL_HTTP_Session::getUid() == "0") {
                SGL::raiseMsg('You must be logged in order to buy products ');
                $this->_list($input, $output);
                //exit;
            } else {
                $usrId = $_SESSION['uid'];

                $vat = isset ($_SESSION['aPrefs']['VAT'])
                    ? $_SESSION['aPrefs']['VAT']
                    : $this->conf['ShopMgr']['defaultVAT'];

                $prices = $this->GetPrice($usrId, $input->productId);
                SGL_HTTP_Session::set('vat', $vat);

                require_once SGL_MOD_DIR.'/cart/classes/Item.php';
                $oOrderItem       = new Item();
                $oOrderItem->id   = $prices['product_id'];
                $oOrderItem->name = $prices['name'];
                $oOrderItem->type = 'product';

                // TO DO: check if currency exist and != 0
                // check this currency conversion thing
/*        $oOrderItem->price = $product->price * $vat * $this->conf['exchangeRate'][$product->currency] / $this->conf['exchangeRate'][$currency]; */
                $oOrderItem->price = $prices['price'];
                $oOrderItem->priceVAT = $prices['priceVAT'];
                $oOrderItem->price = $prices['price'];
                $oOrderItem->quantity = $input->qty; /// ?????
                $oOrderItem->currency = $prices['currency'];

                //$oOrderItem->sum = $oOrderItem->price * $oOrderItem->quantity;
                /// BUG sum should be calculated
                //in order recalculate...
                $oOrderItemSer = urlencode(base64_encode(serialize($oOrderItem)));
                SGL_HTTP::redirect(array (
                    'action' => 'insert',
                    'managerName' => 'cart',
                    'moduleName' => 'cart',
                    'data' => $oOrderItemSer));

            }
        }
        

    }

}
?>
