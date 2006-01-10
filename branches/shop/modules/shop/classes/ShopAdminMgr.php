<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | ShopAdminMgr.php                                                          |
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
// $Id: ShopAdminMgr.php,v 1.4 2005/05/09 23:55:20 demian Exp $

require_once SGL_MOD_DIR.'/shop/classes/ShopMgr.php';
require_once SGL_MOD_DIR.'/navigation/classes/MenuBuilder.php';
//require_once SGL_CORE_DIR . '/Category.php';

if (isset($this->conf['ShopMgr']['multiCurrency']) &&
    $this->conf['ShopMgr']['multiCurrency'] == true) {
    require_once SGL_MOD_DIR . '/rate/classes/RateMgr.php';
}

/**
 * To allow users to contact site admins.
 *
 * @package shop
 * @author  Rares Benea <rbenea@bluestardesign.ro>  
 * @version $Revision: 1.4 $
 * @since   PHP 4.1
 */
class ShopAdminMgr extends SGL_Manager {

    function ShopAdminMgr() 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();
        
        $this->module = 'shop';
        $this->pageTitle = 'Shop Manager';
        $this->template = 'productListAdmin.html';
        $this->masterTemplate = 'masterLeftCol.html';
        $this->_aAllowedFileTypes = array ('gif', 'jpeg', 'jpg', 'png');
        $this->_aActionsMapping = array (
                'add' => array ('add'),
                'insert' => array ('insert'),
                'edit' => array ('edit'),
                'update' => array ('update'),
                'delete' => array ('delete'),
                'list' => array ('list'),
                'imageUpload' => array ('imageUpload'),
//                'config' => array ('config'),
//                'updateConfig' => array ('updateConfig'),
                'pCatDecr' => array ('pCatDecr'),
                'pCatDescrUpdate' => array ('pCatDescrUpdate'),
                'updateOrder' => array ('updateOrder'),
                'variantAdd' => array ('variantAdd'),
                'updateVariants' => array ('updateVariants'),
                'writeVariants' => array ('writeVariants'),

                );
                
        //TO DO: activate rate manager
       // $this->conf = & $GLOBALS['_SGL']['CONF'];
       // $this->dbh = & SGL_DB::singleton();
        if (isset($this->conf['ShopMgr']['multiCurrency'])) {
            if($this->conf['ShopMgr']['multiCurrency']) {
                $rateMgr = & new RateMgr();
            } else {
                $this->conf['exchangeRate'][$this->conf['ShopMgr']['defaultCurrency']] =
                $this->conf['ShopMgr']['defaultExchange'];
            }
        }       
    }

    function validate($req, & $input) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //$conf = & $GLOBALS['_SGL']['CONF'];
        $aErrors = array ();

        $this->validated        = true;
        $input->error           = array ();
        $input->pageTitle       = $this->pageTitle;
        // restore previouse template
        $input->template        = $this->template;

        $input->masterTemplate  = $this->masterTemplate;
        $input->javascriptSrc   = array('TreeMenu.js');
        $input->action          = ($req->get('action')) ? $req->get('action') : 'list';
        $input->updateOrder     = $req->get('updateOrder');
        if (isset ($input->updateOrder)) {
            $input->action      = 'updateOrder';
            $input->productOrder    = $req->get('productOrder');
        }
        $input->productOrder    = $req->get('productOrder');
        $input->productId       = $req->get('frmProdId');
        $input->catID           = (int) $req->get('frmCatID') ? $req->get('frmCatID') : 0;
        $input->aDelete         = $req->get('frmDelete');
        $input->submit          = $req->get('submitted');
        $input->product         = (object) $req->get('product', true);
        $input->product->description = $req->get('frmBodyName', true);
        $input->pCatId          = $req->get('pCatId');
        $input->product->promotion = (isset ($input->product->promotion)) ? 1 : 0;
        $input->product->new_id = (isset ($input->product->new_id )) ? 1 : 0;
        $input->product->bargain = (isset ($input->product->bargain )) ? 1 : 0;
        $input->totalItems      = $req->get('totalItems');
        $input->sortBy          = SGL_Util::getSortBy($req->get('frmSortBy'), SGL_SORTBY_USER);
        $input->sortOrder       = SGL_Util::getSortOrder($req->get('frmSortOrder'));
        $input->config          = $req->get('Shop');
        $input->variantsToAdd   = $req->get('AddfrmProductVariants');
        $input->variantsToRemove = $req->get('RemovefrmProductVariants');

        if ($input->action == 'csvUpdate') {
            if ($input->product->action == 'add')
                $required = array ('product_id', 'name', 'cat_id', 'manufacturer', 'price', 'status', 'cod1');
            else
                $required = array ('cod1');
            if (!$this->_validateProductEdit($input->product, $aErrors, $required)) {
                $input->error = $aErrors;
                $this->validated = false;
                $input->template = 'productEdit.html';
                $input->formAction = 'csvUpdate';
                return $input;
            }
        }
        if ($input->action == 'update') {
            $required = array ('product_id', 'name', 'cat_id', 'manufacturer', 'price', 'status', 'cod1');
            if (!$this->_validateProductEdit($input->product, $aErrors, $required)) {
                $input->error = $aErrors;
                $this->validated = false;
                $input->template = 'productEdit.html';
                $input->formAction = 'update';
                return $input;
            }
        }

        if ($input->action == 'insert') {
            $required = array ('name', 'cat_id', 'manufacturer', 'price', 'status', 'cod1');
            if (!$this->_validateProductEdit($input->product, $aErrors, $required)) {
                $input->error = $aErrors;
                $this->validated = false;
                $input->template = 'productEdit.html';
                $input->formAction = 'insert';
                return $input;
            }
        }

        if ($input->action == 'updateConfig') {
            if (!$this->_validateConfig($input->config, $aErrors)) {
                $input->error = $aErrors;
                $this->validated = false;
                $input->template = 'configEdit.html';
                $input->formAction = 'config';
                return $input;
            }
        }

        // imageUpload
        $input->imageFileArray = $req->get('imageFile');
        if (is_array($input->imageFileArray) && !empty($input->imageFileArray['name'])) {
            $input->imageFileName = $input->imageFileArray['name'];
            $input->imageFileType = $input->imageFileArray['type'];
            $input->imageFileTmpName = $input->imageFileArray['tmp_name'];
            $input->imageFileSize = $input->imageFileArray['size'];
 // this does not work with PHP 5.0.5
 //         $input->imageFileExtension = end(explode('.', $input->imageFileArray['name']));
 //here goes workaround...
            $tmp = explode('.', $input->imageFileArray['name']);
            $input->imageFileExtension = end($tmp);
        }

        // csvUpload
        $input->csvFileArray = $req->get('csvFile');
        if (is_array($input->csvFileArray)) {
            $input->csvFileName = $input->csvFileArray['name'];
            $input->csvFileType = $input->csvFileArray['type'];
            $input->csvFileTmpName = $input->csvFileArray['tmp_name'];
            $input->csvFileSize = $input->csvFileArray['size'];
            $input->csvFileExtension = end(explode('.', $input->csvFileName));

            if (!(($input->csvFileExtension == 'csv') and $input->csvFileExtension == 'csv')) {
                $aErrors['csvUpload'] = SGL_Output::translate('Invalid file type');
            }
            if ($input->csvFileSize < 1) {
                $aErrors['csvUpload'] = SGL_Output::translate('Invalid file size');
            }
        }
        if ($input->submit) {
            if ($input->action == 'imageUpload') {
                //  if document has been uploaded 
                if ($input->imageFileTmpName == 'none' or $input->imageFileTmpName == '') {
                    $aErrors['imageUpload'] = SGL_Output::translate('Invalid file name');
                } else {
                    //  check uploaded file is of valid type
                    if (!in_array(strtolower($input->imageFileExtension), $this->_aAllowedFileTypes)) {
                        $aErrors['imageUpload'] = SGL_Output::translate('Not a recognised file type');
                    }
                    if ($input->imageFileSize > $this->conf['imageUpload']['maxSize']) {
                        $aErrors['imageUpload'] = SGL_Output::translate('File size to big');
                    }
                }
            }
        }

        //  if errors have occured
        if (isset ($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            if ($input->action == 'add' || $input->action == 'insert') {
                $input->template = 'productAdd.html';
            }

            if ($input->action == 'edit' || $input->action == 'update' || $input->action == 'imageUpload' || $input->action == 'csvEdit') {
                $input->template = 'productEdit.html';
            }
            if ($input->action == 'csvUpload') {
                $input->template = 'csvUpload.html';
            }

            if ($input->action == 'configEdit') {
                $input->template = 'configEdit.html';
            }
            $this->validated = false;
        }
    }

    function display(& $output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        //lets genereate this date only for appropriate form
        if ($output->action == "add" || $output->action == "edit" ||
            $output->action == "update" || $output->action == "insert") {
            // generate the category select box
            $menu1 = & new MenuBuilder('SelectBox');
            $menu1->setStartId($this->conf['ShopMgr']['rootCatID']);
            $aHtmlOptions = $menu1->toHtml();
            $output->catOptions = SGL_Output::generateSelect($aHtmlOptions, @ $output->product->cat_id);
            // generate the manufacturer select box
            $aManufacturer = $this->_getManufacturerList();
            $output->manufacturerOptions = SGL_Output::generateSelect($aManufacturer, @ $output->product->manufacturer);
        
            // generate status select box
            $aStatus = array ();
            if ($this->conf['statusOpts']) {
                $aStatus = $this->conf['statusOpts'];
            }
            foreach($aStatus as $k=>$v) {
                $aStatus[$k] = SGL_Output::translate($v);
            }

            //if we have status = 0
            if ((int)$output->product->status == 0 ){
                $output->product->status = 1;
            }
            
            $output->statusOptions = SGL_Output::generateSelect($aStatus, $output->product->status);
            $output->product->statusString = $this->conf['statusOpts'][ $output->product->status];

            $output->product->name = stripslashes($output->product->name);
            $output->product->frmBodyName = $output->product->description;

            // product image procces
            // product iamge path set
            $output->thumbPath = SGL_BASE_URL.'/'.$this->conf['imageUpload']['thumb'].'/';
            $output->imgPath = SGL_BASE_URL.'/'.$this->conf['imageUpload']['directory'].'/';

            if (!(strlen($output->product->img) > 1
                && file_exists(SGL_WEB_ROOT.'/'.$this->conf['imageUpload']['thumb'].'/'.$output->product->img))
                && file_exists(SGL_WEB_ROOT.'/'.$this->conf['imageUpload']['directory'].'/'.$output->product->img)) {

                $output->product->img = $this->conf['imageUpload']['noImageFile'];
            }

        // promotion check box
            $output->promoOptions = $output->product->promotion;
        // new poduct check box
            $output->newOptions = $output->product->new_id;
        // new poduct check box
            $output->bargainOptions = $output->product->bargain;

            $output->wysiwyg = true;
        }
        

        
        // Price computation
        if (isset ($output->product->price)) {
            $vat = isset ($_SESSION['aPrefs']['VAT'])
               ? (int) $_SESSION['aPrefs']['VAT']
               : $this->conf['ShopMgr']['defaultVAT'];
               //price without VAT. For product data display table
        	$output->product->priceWHvat = round($output->product->price / $vat, 2);
        }
        
        // Price currency
        //at the moment we support only default currency.
        //When RateMgr will be activated we will add support tu other currencty.
        if (isset ($output->product->currency)) {
            $aCurrency = array();
            $aCurrency[$this->conf['ShopMgr']['defaultCurrency']] = $this->conf['ShopMgr']['defaultCurrency'];
            $output->product->currency =
                 SGL_Output::generateSelect($aCurrency, $this->conf['ShopMgr']['defaultCurrency']);
        }
        
        //description
        if (isset ($output->product->description)) {
            $output->product->aDescription = $output->product->description;
            $output->product->description = nl2br($output->product->description);
        }
        
        // Set current category name and category path
        if (isset($output->catID) and isset($output->catID) > 0) {
            $catMgr = & new SGL_Category();
            $output->path = stripslashes($catMgr->getBreadCrumbs($output->catID, false, 'linkCrumbsAlt1', false));
            $output->currentCat = stripslashes($catMgr->getLabel($output->catID));
        }
    }
        
        
    /**
     * An extension of the validate() method that is called for every product
     * record to check the fields syntax
     *
     * @access private
     *
     * @param object  $product   The product object
     * @param array   $aError    The array that will contain the error msgs
     * @param array   $required  Array of required fields names
     *
     * @return boolean The validation result: true-OK, false-the rest
     *
     */
    function _validateProductEdit(& $product, & $aErrors, $required) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $validate = true;

        if (!is_object($product)) {
            SGL::raiseMsg('Internal error');
            return false;
        }

        // Product ID
        if (in_array('product_id', $required) && !(isset ($product->product_id) && $product->product_id > 0)) {
            SGL::raiseMsg('Invalid product ID');
            return false;
        }

        // Product name
        if (isset ($product->name) && strlen($product->name) > 1) {
            // name conains ' or " add slashes
            $product->name = addslashes($product->name);
        } else {
            if (in_array('name', $required)) {
                $aErrors['name'] = SGL_Output::translate('Please fill in name');
            }
        }
        // Product category
        if (in_array('cat_id', $required)) {
            if (isset ($product->cat_id) and $product->cat_id > 0) {
                $oCategory = & new SGL_Category();
                if (strlen($oCategory->getLabel($product->cat_id)) < 1)
                    $aErrors['cat_id'] = SGL_Output::translate('Invalid category data');
            } else
                $aErrors['cat_id'] = SGL_Output::translate('Please fill in category id');
        }
        
        // Product Manufacturer
       if (isset ($product->manufacturer) && strlen($product->manufacturer) > 1) {
            if ($product->manufacturer != addslashes($product->manufacturer)
                || preg_match("/[^A-z0-9_\-\s]/", $product->manufacturer) == 1) {
                $aErrors['manufacturer'] = SGL_Output::translate('Invalid manufacturer data');
            }
        } else {
            if (in_array('manufacturer', $required)) {
                $aErrors['manufacturer'] = SGL_Output::translate('Please fill in manufacturer').$product->manufacturer;
            }
        }
        
        // Price
        if (in_array('price', $required) && !(isset ($product->price))) {
            $aErrors['price'] = SGL_Output::translate('Please fill in price');
        } else {
            //if there are commas replase them with dots
            if (strstr($product->price, ',')) {
               $product->price = strtr($product->price, ",", ".");
            }
            if ((float)$product->price <= 0) {
               $aErrors['price'] = SGL_Output::translate('Price must be > 0');
            }

        }

        // status
        if (in_array('status', $required) and !(isset ($product->status) and $product->status > 0)) {
            $aErrors['status'] = SGL_Output::translate('Please fill in prod status');
        }

        // Product Short description
        if (isset ($product->short_description) && strlen($product->short_description) > 1) {
            $product->short_description = htmlspecialchars($product->short_description);
        } else
            if (in_array('short_description', $required)) {
                $aErrors['short_description'] = SGL_Output::translate('Please fill in short description');
            }


        // Product Code 1
        if (isset ($product->cod1) and strlen($product->cod1) > 1) {
            if ($product->cod1 != addslashes($product->cod1)) {
                $aErrors['cod1'] = SGL_Output::translate('Invalid cod1 data');
            }
        } else
            if (in_array('cod1', $required)) {
                $aErrors['cod1'] = SGL_Output::translate('Please fill in cod1');
            }

        // Product Code 2
        if (isset ($product->cod2) and strlen($product->cod2) > 1) {
            if ($product->cod2 != addslashes($product->cod2) ){
 //               || preg_match("/[^A-z0-9_\-]/", $product->cod2) == 1) { someth not ok here
                $aErrors['cod2'] = SGL_Output::translate('Invalid cod2 data'). " $product->cod2";
            }
        }

        // Product Warranty
        if (isset ($product->garantii) and strlen($product->garantii) > 1) {
            if ($product->garantii != addslashes($product->garantii)
                || preg_match("/[^A-z0-9_\-\s]/", $product->garantii) == 1) {
                $aErrors['garantii'] = SGL_Output::translate('Invalid warranty data');
            }
        }

        // Full product description
        if (isset ($product->description) and strlen($product->description) > 1) {
            //$aSyntaxErrors = $this->_checkDescriptionSyntax($product->description);
            //if (count($aSyntaxErrors) != 0) {
            //    $aErrors['description'] = SGL_Output::translate('Syntax error on line(s): ');
            //    $aErrors['description'] .= implode(', ', $aSyntaxErrors);
           // }
        } else {
            if (in_array('description', $required)) {
                $aErrors['description'] = SGL_Output::translate('Please fill in description');
            }
        }
        
        if (isset ($aErrors) && count($aErrors)) {
            $validate = false;
        }
        
        return $validate;
    }

    function _variantAdd(& $input, & $output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'productVersionAdd.html';
        $output->pageTitle = 'Products::Version Management';
        
        if (!empty($input->productId)) {
            $oProduct = & new DataObjects_Product();
            $oProduct->get($input->productId);

            $product =
               array('name'  => $oProduct->name,
                     'catID' => $oProduct->cat_id,
                     'cod1'  => $oProduct->cod1,
                     'product_id' => $oProduct->product_id,);
            $output->product = $product;
            $oProduct->free();

            $p = & new DataObjects_Product();
            //select all prods where variant id not set except current prod
            $p->whereAdd("variantID = 0 AND product_id != {$product['product_id']}"); 
            //TO THINK: might be god to alow users select possible variants from same category as the main product
            //and not from the whole product pool
            $nr_rows = $p->find();

            $prodList = array();
            while ($p->fetch()){
               $name = $p->name;
               $cod1 = $p->cod1;
               $id = $p->product_id;

               $prodList["$id"] = SGL_String::summarise("$cod1-$name", 15, '...');
            }
            $p->free();

            $p = & new DataObjects_Product();
            //select all prods where variant id not set except current prod
            $p->whereAdd("variantID = {$product['product_id']}");
            $nr_rows = $p->find();
           
            $prodVarList = array();
            while ($p->fetch()){
               $name = $p->name;
               $cod1 = $p->cod1;
               $id = $p->product_id;

               $prodVarList["$id"] = SGL_String::summarise("$cod1-$name", 15, '...');
            }
            $p->free();

            $output->prodList = $prodList;
            $output->prodVarList = $prodVarList;
        }
    }

    function _updateVariants(&$input, &$output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $variantsToAdd = $this->_parsePermsString($input->variantsToAdd);
        $variantsToRemove = $this->_parsePermsString($input->variantsToRemove);

        $success1 = true;
        $success2 = true;
        if (is_array($variantsToAdd) && count($variantsToAdd)) {
            $success1 = $this->_writeVariants($input->productId, $variantsToAdd, 'add');
        }
        
        if (is_array($variantsToRemove) && count($variantsToRemove)) {
            $success2 = $this->_writeVariants($input->productId, $variantsToRemove, 'remove');
        }

        if ($success1 == true && $success2 == true) {
            SGL::raiseMsg('Product variants updated successfully');
            SGL_HTTP::redirect(array ('action' => 'edit', 'frmProdId' => $input->productId));
        } else {
            SGL::raiseError('There was a problem updating the record', SGL_ERROR_NOAFFECTEDROWS);
            SGL_HTTP::redirect(array ('action' => 'variantAdd', 'frmProdId' => $input->productId));
        }

        SGL::raiseMsg('variant assignments updated successfully');
    }

/****** *
should become some part DA . Someday :)
****** */
    function _writeVariants($prod_id, $variants, $action)     {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        switch($action){
          case "add" :
             foreach($variants as $k => $v) {
               $qry = "UPDATE product SET variantID = $prod_id WHERE product_id = $k";
               $res =& $this->dbh->query($qry);
             }
          break;
          case "remove":
             foreach($variants as $k => $v) {
               $qry = "UPDATE product SET variantID = 0 WHERE product_id = $k";
               $res =& $this->dbh->query($qry);
             }
          break;
            
          default:
          die ('NO ACTION');
        }
        
    	if (DB::isError($res)) {
			SGL :: raiseError('OOPS! '.$qry);
			return false;
		} else {
            return true;
        }
        
    }


    /**
     * Parse string from role changer widget.
     *
     * @access  private
     * @param   string  $sUsers colon-separated string of username_UIDs
     * @return  array   aUsers  hash of uid => username
     */
    function _parsePermsString($sPerms)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aTmpPerms = split(':', $sPerms);
        if (count($aTmpPerms) > 0) {
            array_pop($aTmpPerms);
            $aPerms = array();
            foreach ($aTmpPerms as $perm) {
                //  chop at caret
                list($permName, $permId) = split('\^', $perm);
                $aPerms[$permId] = $permName;
            }
        } else {
            return false;
        }
        return $aPerms;
    }


    /**
    * DB PRODUCT ADMIN FUNCTIONS
    */
    
    
    /**
    * Generate a new empty product object
    *
    * @access public
    *
    */
    function _add(& $input, & $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'productEdit.html';
        $output->pageTitle = 'Products::Add';

        $output->product = & new DataObjects_Product();
        // set default category 
        $output->product->cat_id = $input->catID;
        // set defautl manufacturer
        $output->product->manufacturer = '';
        // set default status
        $output->product->status = 3;


        $output->formAction = 'insert';
    }
    
    
    /**
    * Insert the new product into DB
    *
    * @access public
    *
    */
    function _insert(& $input, & $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
//        $conf = & $GLOBALS['_SGL']['CONF'];

        //  insert record
        $oProduct = & new DataObjects_Product();
        $oProduct->setFrom($input->product);
        $dbh = $oProduct->getDatabaseConnection();
        $oProduct->product_id = $dbh->nextId('product');
        $oProduct->last_updated = SGL_Date::getTime();
        $oProduct->updated_by = $_SESSION['uid'];
        $oProduct->date_created = SGL_Date::getTime();
        $oProduct->created_by = $_SESSION['uid'];
        $oProduct->price = round($input->product->price / $this->conf['ShopMgr']['defaultVAT'], 4);
        $success = $oProduct->insert();
        
        $input->productId = $oProduct->product_id;
        // if we have to upload a new image...
        $successImage = true;
        $input->productId = $oProduct->product_id;
        if (!empty($input->imageFileArray['name'])) {
            $successImage = $this->_imageUpload($input, $output);
        }

        // if you hit sumit again an update action will be performed
        $output->formAction = 'update';
//        if ($success === true && $successImage === true) {
// $success s INTEGER type not boolean
        if ($success == true && $successImage === true) {
            //  redirect on success
            SGL::raiseMsg('Product updated successfully');
            SGL_HTTP::redirect(array ('action' => 'list', 'frmCatID' => $input->product->cat_id));
        } else {
            SGL::raiseError('There was a problem updating the record', SGL_ERROR_NOAFFECTEDROWS);
            $output->template = 'productEdit.html';
        }
    }

    
    /**
    * Get the product object from DB and fill the edit form
    *
    * @access public
    *
    */
    function _edit(& $input, & $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'productEdit.html';
        $output->pageTitle = 'Products::Edit';
        $output->formAction = 'update';

        $oProduct = & new DataObjects_Product();
        //  get product data
        $oProduct->get($input->productId);
        $output->product = $oProduct;
        $output->product->price = round($output->product->price * $this->conf['ShopMgr']['defaultVAT'], 2);
        $output->catID = $oProduct->cat_id;

        // TO DO: the best is yet to come
        //ROL: What ?
   /*     $dbh = & SGL_DB::singleton();
        $query = 'SELECT * FROM product';
        $result = $dbh->query($query);*/
    }


    /**
    * Update the product stored in DB
    *
    * @access public
    *
    */
    function _update(& $input, & $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
       // $input->product->name = addslashes($input->product->name);
        $input->product->price = round($input->product->price / $this->conf['ShopMgr']['defaultVAT'], 4);
        $output->formAction = 'update';
        $oProduct = & new DataObjects_Product();
        $oProduct->get($input->product->product_id);
        $oProduct->setFrom($input->product);
        $oProduct->last_updated = SGL_Date::getTime();
        $oProduct->updated_by = $_SESSION['uid'];
        $oProduct->price = $input->product->price / $this->conf['ShopMgr']['defaultVAT'];

        if (isset ($oProduct->product_id) && $oProduct->product_id > 0) {
            $success = $oProduct->update();
        } else {
            $dbh = $oProduct->getDatabaseConnection();
            $oProduct->product_id = $dbh->nextId('product');
            $oProduct->date_created = SGL_Date::getTime();
            $oProduct->created_by = $_SESSION['uid'];
            $success = $oProduct->insert();
        }        

        $output->product = $oProduct;
        $output->catID = $oProduct->cat_id;
        // if we have to upload a new image...
        $successImage = true;
        $input->productId = $oProduct->product_id;
        if (!empty($input->imageFileArray['name'])) {
            $successImage = $this->_imageUpload($input, $output);
        }

// $success s INTEGER type not boolean
        if ($success == true && $successImage === true) {
            //  redirect on success
            SGL::raiseMsg('Product updated successfully');
            SGL_HTTP::redirect(array ('action' => 'list', 'frmCatID' => $input->product->cat_id));
        } else {
            SGL::raiseError('There was a problem updating the record', SGL_ERROR_NOAFFECTEDROWS);
            $output->template = 'productEdit.html';
        }
        
    }


    /**
    * Upload the product image. The product images is not resized,
    * but a resized thumb is generated from it.
    *
    * @access public
    *
    */
    function _imageUpload(& $input, & $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (empty($input->productId)) {
            SGL::raiseError('No product ID found', SGL_ERROR_NODATA, PEAR_ERROR_DIE);
        }

        $output->template = 'productEdit.html';
        $output->formAction = 'update';
        $oProduct = & new DataObjects_Product();
        $oProduct->get($input->productId);
        $output->product = $oProduct;

        $success = $this->uploadImg($input->imageFileArray, $input->productId.'.jpg'); // not nice

        if ($success === true) {
            $oProduct->img = $input->productId.'.jpg';
            $oProduct->update();
        } else {
            SGL::logMessage(print_r($success,true));
            SGL::raiseError('There was a problem resizeing the product image');
        }

        return $success;
    }


    function uploadImg($imageFileArray, $filename = '') {
    	require_once 'Image/Transform.php';

 //       $conf = & $GLOBALS['_SGL']['CONF'];
        if (empty ($filename)) {
            $filename = $imageFileArray['name'];
        }
       $thubDir = SGL_WEB_ROOT.'/'.$this->conf['imageUpload']['thumb'];
       $imageDir = SGL_WEB_ROOT.'/'.$this->conf['imageUpload']['directory'];

        //if we don't have images/shop directory, lets create it
        if (!file_exists($imageDir)){
            include_once 'System.php';
            $success = System::mkDir(array ($imageDir));
            if (!$success) {
                return SGL::raiseError('The image upload directory does not appear to be writable, please give the webserver permissions to write to it', SGL_ERROR_FILEUNWRITABLE, PEAR_ERROR_DIE);
            }
        }

        //if we don't have images/thumb directory, lets create it
        if (!file_exists($thubDir)){
            include_once 'System.php';
            $success = System::mkDir(array ($thubDir));
            if (!$success) {
                return SGL::raiseError('The thumb upload directory does not appear to be writable, please give the webserver permissions to write to it', SGL_ERROR_FILEUNWRITABLE, PEAR_ERROR_DIE);
            }
        }
        // now lets check if they are writable...
        if (!is_writable($imageDir) || !is_writable($thubDir)) {
            return SGL::raiseError('The imge or thumb upload directory  does not appear to be writable, please give the webserver permissions to write to it', SGL_ERROR_FILEUNWRITABLE, PEAR_ERROR_DIE);
        }
         
        $imageDriver = $this->conf['imageUpload']['imageDriver'];
        $magnify	 = $this->conf['imageUpload']['magnify'];
        $im = Image_Transform::factory($imageDriver);
        if(is_a($im, 'PEAR_Error')) {
        	return $im;
        }
        
        // Generate image
        $ret = $im->load($imageFileArray['tmp_name']);
        if(is_a($ret, 'PEAR_Error')) {
        	return $im;
        }

        $size = $im->getImageSize();
        if(isset($size['0']) && isset($size['1'])) {
        	$width = $size['0'];
        	$height = $size['1'];
        } else {
        	return SGL::raiseError('Unable to get image size');
        }
        
        $newWidth = $this->conf['imageUpload']['imageWidth'];
        $newHeight = $this->conf['imageUpload']['imageHeight'];

        // Mignify if necessary
        if($width < $newWidth && $height < $newHeight && $magnify) {
        	$height = $height * ($newWidth / $width);
        	$width = $newWidth;
        }
        
    	// We make sure to keep the image aspect ratio
    	if($width >= $height && $newHeight <= $height){
      		$newHeight = $height / ($width / $newWidth);
    	} else if ($width < $height && $newWidth < $width) {
      		$newWidth = $width / ($height / $newHeight);    
    	} else {
      		$newWidth = $width;
      		$newHeight = $height;
   		}
        
        $ret = $im->resize($newWidth, $newHeight);
        if(is_a($ret, 'PEAR_Error')) {
        	return $im;
        }

        $ret = $im->save($imageDir.'/'.$filename,'jpeg');
        if(is_a($ret, 'PEAR_Error')) {
        	return $im;
        }
        
        // Generate thumb
        $ret = $im->load($imageFileArray['tmp_name']);
        if(is_a($ret, 'PEAR_Error')) {
        	return $im;
        }
        
        $size = $im->getImageSize();
        if(isset($size['0']) && isset($size['1'])) {
        	$width = $size['0'];
        	$height = $size['1'];
        } else {
        	return SGL::raiseError('Unable to get image size');
        }
        
        $newWidth = $this->conf['imageUpload']['thumbWidth'];
        $newHeight = $this->conf['imageUpload']['thumbHeight'];
        
        // Mignify if necessary
        if($width < $newWidth && $height < $newHeight && $magnify) {
        	$height = $height * ($newWidth / $width);
        	$width = $newWidth;
        }
        
    	// We make sure to keep the image aspect ratio
    	if($width >= $height && $newHeight <= $height){
      		$newHeight = $height / ($width / $newWidth);
    	} else if ($width < $height && $newWidth < $width) {
      		$newWidth = $width / ($height / $newHeight);    
    	} else {
      		$newWidth = $width;
      		$newHeight = $height;
   		}
        
        $ret = $im->resize($newWidth, $newHeight);
        if(is_a($ret, 'PEAR_Error')) {
        	return $im;
        }
        
        $ret = $im->save($thubDir.'/'.$filename,'jpeg');
        if(is_a($ret, 'PEAR_Error')) {
        	return $im;
        }
        return true;
    }

    /**
    * Delete a product from DB
    *
    * @access public
    *
    */
    function _delete(& $input, & $output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (is_array($input->aDelete)) {
            foreach ($input->aDelete as $index => $product_id) {
                $oProduct = & new DataObjects_Product();
                $oProduct->get($product_id);
                $oProduct->delete();
                unset ($oProduct);
            }
        } else {
            SGL::raiseError('Incorrect parameter passed to '.__CLASS__.'::'.__FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        //  redirect on success
        SGL::raiseMsg('Product deleted successfully');
        SGL_HTTP::redirect(array ('action' => 'list', 'frmCatID' => $input->catID));
    }


function _getProductAdminList(){


}


    /**
    * List for editing, the products from DB sorted by category
    *
    * @access public
    * TODO: to make hierarchy type listing like user/org module.
    */ 
    function _list(& $input, & $output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'productListAdmin.html';
        $output->pageTitle = 'Products::Browse';
        
        // Generate the orderBy query
        $orderBy_query = '';
        $allowedSortFields = array('product_id','cod1','manufacturer',
                                   'name','last_updated','promotion','price','currency');
        if (  !empty($input->sortBy)
           && !empty($input->sortOrder)
           && in_array($input->sortBy, $allowedSortFields)) {
                $orderBy_query = 'ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder ;
        } 
        
        $this->conf['ShopAdminMgr']['displayVersionsAsProducts'] = false;  //move this to config
        if ($this->conf['ShopAdminMgr']['displayVersionsAsProducts']) {
            $where_qry = "";
        } else {
            $where_qry = " AND variantID = 0";
        }
        
        
        // If not valid cat ID, list all the products
        if ($input->catID < 1 || $input->catID == $this->conf['ShopMgr']['rootCatID']) {
            $query = "SELECT B.product_id, B.cod1, B.cod2, B.name, B.short_description,
                             B.description, B.link_datasheet, B.img, B.manufacturer,
                             B.link_manufacturer, B.price*{$this->conf['ShopMgr']['defaultVAT']} as priceVAT,
                             B.price as price,
                             B.currency, B.warranty,
                             B.cat_id, B.promotion, B.status, B.date_created,
                             B.created_by, B.last_updated, B.order_id, B.updated_by
                      FROM product B
                      WHERE 1=1 ".$where_qry.$orderBy_query;
            $output->catID = 0;
        } else {
           //here we get all sub branches
            $query = 'SELECT left_id, right_id
                      FROM category
                      WHERE category_id = '.$input->catID;
            $result = $this->dbh->query($query);
            
            $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
            $left_id = $row['left_id'];
            $right_id = $row['right_id'];

            $query = "SELECT B.product_id, B.cod1, B.cod2, B.name, B.short_description,
                             B.description, B.link_datasheet, B.img, B.manufacturer,
                             B.link_manufacturer, B.price*{$this->conf['ShopMgr']['defaultVAT']} as priceVAT,
                             B.price as price,
                             B.currency, B.warranty,
                             B.cat_id, B.promotion, B.status, B.date_created,
                             B.created_by, B.last_updated, B.order_id, B.updated_by, A.label
                      FROM product B, category A
                      WHERE B.cat_id = A.category_id
                            AND A.left_id >= {$left_id} AND A.right_id <={$right_id} "
                            .$where_qry.$orderBy_query;

            //kopijuojam tam kad meniu veiktu...
            $output->catID = $input->catID;
        }

        $limit = 5 * $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array ('mode' => 'Sliding',
                               'delta' => 3,
                               'perPage' => $limit,
                               'totalItems' => $input->totalItems);
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);
        
        if (is_array($aPagedData) && count($aPagedData['data'])) {
            foreach ($aPagedData['data'] as $key => $value) {
                $product = $aPagedData['data'][$key];
                
                // trim short_descriptin to 30 chars
                $product['short_description'] = SGL_Output::summarise($product['short_description'],30);
                $product['last_updated'] = SGL_Date::format( $product['last_updated']);
                $product['promotion'] = $product['promotion'] ? 'Yes': '';
                
                $aPagedData['data'][$key] = $product;
            }
        } else {
            SGL::raiseMsg('No products');
        }
        
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }

        $output->totalItems = $aPagedData['totalItems'];
        $output->aPagedData = $aPagedData;
    }

    /**
    * Returns an array of unique manufacturer names sorted from all the DB products 
    *
    * @access public
    * 
    * @return array  Manufacturer list
    *
    */ 
    function _getManufacturerList() {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $oProductList = & new DataObjects_Product();
        $oProductList->groupBy('manufacturer');
        $oProductList->orderBy('manufacturer');
        $oProductList->selectAdd();
        $oProductList->selectAdd('manufacturer');
        $oProductList->find();
        $aManufacturer = array ();
        while ($oProductList->fetch())
            $aManufacturer[$oProductList->manufacturer] = $oProductList->manufacturer;

        return $aManufacturer;
    }


    /**
    * Interpret the mime type. 
    * Taken from publisher.
    *
    * @access public
    *
    */ 
    function _mime2AssetType($mimeType) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        switch ($mimeType) {
            case 'application/octet-stream' :
                $assetTypeID = 1;
                break;
                //  jpgs on windows
            case 'image/pjpeg' :
                $assetTypeID = 2;
                break;
                //  jpgs on linux
            case 'image/jpeg' :
                $assetTypeID = 2;
                break;
            case 'image/x-png' :
                $assetTypeID = 3;
                break;
            case 'image/gif' :
                $assetTypeID = 4;
                break;
            default :
                $assetTypeID = 7; //  unknown
        }
        return $assetTypeID;
    }

/*    function _config(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'configEdit.html';
        $output->pageTitle = $this->pageTitle . ' :: Configure';

        $path = SGL_MOD_DIR . "/{$this->module}/";
        if (is_readable($path . 'conf.ini')) {
            $config = parse_ini_file($path . 'conf.ini', true);
            foreach ($config['statusOpts'] as $k => $v) {
                @$statusOpts .= $v.";";
            }
            $config['statusOpts'] = $statusOpts;
            if (isset($config['imageUpload']['noImageFile'])) {
                $config['imageUpload']['oldNoImageFile'] = $config['imageUpload']['noImageFile'];
            }
        }

        $output->Shop = $config;
    }

    function _updateConfig (& $input, & $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if(!function_exists('write_ini_file')) {
            echo "Error function Write ini does not exist";
            die("aaaa!");
        }else{
            if (write_ini_file(SGL_MOD_DIR . "/{$this->module}/conf.ini", $input->config)) {
               if (!empty($input->imageFileArray)){
                   // should check for error
                   $this->uploadImg($input->imageFileArray,"no_image.jpg"); //extension will be added
               }
               SGL::raiseMsg('Configuration is saved');
            } else {
               SGL::raiseMsg('Configuration not saved. Check file conf.ini write permissions');
            }
            SGL_HTTP::redirect(array('action' => 'list'));
        }
     }

    function _validateConfig(& $config, & $aErrors){

        if (empty($config['ShopMgr']['rootCatID'])
            || !is_numeric($config['ShopMgr']['rootCatID'])) {
            $aErrors['ShopMgr']['rootCatID'] = SGL_Output::translate('Please fill in this field');
        }

        if (empty($config['ShopMgr']['defaultVAT'])
           || !is_numeric($config['ShopMgr']['defaultVAT'])) {
            $aErrors['ShopMgr']['defaultVAT'] = SGL_Output::translate('Please fill in this field');
        }

        if (!isset($config['ShopMgr']['defaultDiscount'])
           || !is_numeric($config['ShopMgr']['defaultDiscount'])) {
            $aErrors['ShopMgr']['defaultDiscount'] = SGL_Output::translate('Please fill in this field');
        }
        
        if (empty($config['ShopMgr']['defaultExchange'])
           || !is_numeric($config['ShopMgr']['defaultExchange'])) {
            $aErrors['ShopMgr']['defaultExchange'] = SGL_Output::translate('Please fill in this field');
        }

        if (empty($config['ShopMgr']['defaultCurrency'])
           || strlen($config['ShopMgr']['defaultCurrency']) != 3) {
            $aErrors['ShopMgr']['defaultCurrency'] = SGL_Output::translate('Please fill in this field');
        }
        
        if (empty($config['statusOpts'])) {
            $aErrors['statusOpts'] = SGL_Output::translate('Please fill in this field');
        } else {
           $config['statusOpts'] = explode(";", $config['statusOpts']);
           $i = 1;
           foreach ($config['statusOpts'] as $k => $v) {
              if (!empty($v)) {
                 $statusOpts[$i++] = $v;
              }
           }
           $config['statusOpts'] = $statusOpts;
        }

        if (empty($config['imageUpload']['maxSize'])
           || !is_numeric($config['imageUpload']['maxSize'])) {
            $aErrors['imageUpload']['maxSize'] = SGL_Output::translate('Please fill in this field');
        }

        if (empty($config['imageUpload']['imageWidth'])
           || !is_numeric($config['imageUpload']['imageWidth'])) {
            $aErrors['imageUpload']['imageWidth'] = SGL_Output::translate('Please fill in this field');
        }
        
        if (empty($config['imageUpload']['imageHeight'])
           || !is_numeric($config['imageUpload']['imageHeight'])) {
            $aErrors['imageUpload']['imageHeight'] = SGL_Output::translate('Please fill in this field');
        }
        
        if (empty($config['imageUpload']['thumbWidth'])
           || !is_numeric($config['imageUpload']['thumbWidth'])) {
            $aErrors['imageUpload']['thumbWidth'] = SGL_Output::translate('Please fill in this field');
        }
        
        if (empty($config['imageUpload']['thumbHeight'])
           || !is_numeric($config['imageUpload']['thumbHeight'])) {
            $aErrors['imageUpload']['thumbHeight'] = SGL_Output::translate('Please fill in this field');
        }
        
        if (empty($config['imageUpload']['background'])
           || strlen($config['imageUpload']['background']) != 6) {
            $aErrors['imageUpload']['background'] = SGL_Output::translate('Please fill in this field');
        }
        
        //we should check here can we write to that directory.
        if (empty($config['imageUpload']['directory'])) {
            $aErrors['imageUpload']['directory'] = SGL_Output::translate('Please fill in this field');
        }

        //we should check here can we write to that directory.
        if (empty($config['imageUpload']['thumb'])) {
            $aErrors['imageUpload']['thumb'] = SGL_Output::translate('Please fill in this field');
        }

        if (empty($config['price']['discountPrefId'])
           || !is_numeric($config['price']['discountPrefId'])) {
            $aErrors['price']['discountPrefId'] = SGL_Output::translate('Please fill in this field');
        }

         // lets remember the filename
        $config['imageUpload']['noImageFile'] = "no_image.jpg";

        if (empty($config['price']['roleId'])
           || !is_numeric($config['price']['roleId'])) {
            $aErrors['price']['roleId'] = SGL_Output::translate('Please fill in this field');
        }
        
        if (empty($config['price']['VAT'])
           || !is_numeric($config['price']['VAT'])) {
            $aErrors['price']['VAT'] = SGL_Output::translate('Please fill in this field');
        }
        
        if (empty($config['CSV']['maxUploadRec'])
           || !is_numeric($config['CSV']['maxUploadRec'])) {
            $aErrors['CSV']['maxUploadRec'] = SGL_Output::translate('Please fill in this field');
        }

        $config['UploadMgr'] = "";

        
        if (isset ($aErrors) && count($aErrors)) {
            Dumpr($aErrors);
            return false;
        } else {
            return true;
        }
        
//        return $aErrors;
    }*/

    function _pCatDecr(& $input, & $output) {

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'productCatDescription.html';
        $output->pageTitle = 'Products::Category Descriptions';
        
//        $dbh = & SGL_DB::singleton();
        $qry = "SELECT A.category_id, B.description, A.label AS cat_name
                FROM category A LEFT JOIN category_description B ON (A.category_id = B.cat_id)
                WHERE A.root_id = 4";  //fix this
        $limit = 10;
                
        $pagerOptions = array ('mode' => 'Sliding', 'delta' => 3, 'perPage' => $limit, 'totalItems' => 34);//fix this
        $aPagedData = SGL_DB::getPagedData($this->dbh, $qry, $pagerOptions);

        $output->aPagedData = $aPagedData;
        
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }
    }

    function _pCatDescrUpdate(& $input, & $output) {

        SGL::logMessage(null, PEAR_LOG_DEBUG);

//        $dbh = & SGL_DB::singleton();

        foreach ($input->pCatId as $k => $v) {
            if (!empty($v)) {
               $qry = "SELECT description
                       FROM category_description
                       WHERE cat_id = $k";
               $result = $this->dbh->query($qry);

               if ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
                   $qry = "UPDATE category_description
                           SET description = '$v'
                           WHERE cat_id = $k;";
               } else {
                   $qry = "INSERT INTO category_description (cat_id, description)
                           VALUES ('$k', '$v')";
               }
               $result = $this->dbh->query($qry);
            } else {
               $qry = "DELETE 
                       FROM category_description
                       WHERE cat_id = $k";
               $result = $this->dbh->query($qry);
            }
        }
        SGL::raiseMsg('Product categories descriptions updated successfully');
    }



    function _updateOrder(& $input, & $output) {
//        $input->productOrder = $input->productOrder

//        $dbh = & SGL_DB::singleton();
        foreach ($input->productOrder as $k => $v) {
            $qry = "UPDATE product
                    SET order_id = {$v}
                    WHERE product_id = $k";
            $result = $this->dbh->query($qry);
        }

        SGL::raiseMsg('Product ordering updated successfully');
        SGL_HTTP::redirect(array ('action' => 'list'));
    }
}

/*function write_ini_file($path, $assoc_array) {

   foreach($assoc_array as $key => $item) {
     if(is_array($item)) {
       @$content .= "\n[{$key}]\n";
       foreach ($item as $key2 => $item2) {
         if(is_numeric($item2) || is_bool($item2))
           $content .= "{$key2} = {$item2}\n";
         else
           $content .= "{$key2} = \"{$item2}\"\n";
       }
     } else {
       if(is_numeric($item) || is_bool($item))
         $content .= "{$key} = {$item}\n";
       else
         $content .= "{$key} = \"{$item}\"\n";
     }
   }

   if(!$handle = fopen($path, 'w')) {
     return false;
   }

   if(!fwrite($handle, $content)) {
     return false;
   }

   fclose($handle);
   return true;

  }*/
?>
