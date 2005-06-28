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
require_once SGL_MOD_DIR.'/navigation/classes/CategoryMgr.php';

if ($GLOBALS['_SGL']['CONF']['ShopMgr']['multiCurrency']) {
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
class ShopAdminMgr extends ShopMgr {

    function ShopAdminMgr() 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
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
                'csvUpload' => array ('csvUpload'), 
                'csvList' => array ('csvList'), 
                'csvProcess' => array ('csvProcess'), 
                'csvEdit' => array ('csvEdit'), 
                'csvUpdate' => array ('csvUpdate'), 
                'csvDelete' => array ('csvDelete'), 
                'csvExport' => array ('csvExport'),);
                
        //TO DO: activeaza rate manager
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
        $aErrors = array ();

        $this->validated = true;
        $input->error = array ();
        $input->pageTitle = $this->pageTitle;
        $input->template = $this->template;
        $input->masterTemplate = $this->masterTemplate;
        $input->javascriptSrc   = array('TreeMenu.js');
        $input->action = ($req->get('action')) ? $req->get('action') : 'list';
        $input->productId = $req->get('frmProdId');
        $input->catID = (int) $req->get('frmCatID') ? $req->get('frmCatID') : 0;
        $input->aDelete = $req->get('frmDelete');
        $input->submit = $req->get('submitted');
        $input->product = (object) $req->get('product');
        $input->product->promotion = (isset ($input->product->promotion)) ? 1 : 0;
        $input->totalItems = $req->get('totalItems');
        $input->sortBy      = SGL_Util::getSortBy($req->get('frmSortBy'), SGL_SORTBY_USER);
        $input->sortOrder   = SGL_Util::getSortOrder($req->get('frmSortOrder'));

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

        // imageUpload
        $input->imageFileArray = $req->get('imageFile');
        if (is_array($input->imageFileArray)) {
            $input->imageFileName = $input->imageFileArray['name'];
            $input->imageFileType = $input->imageFileArray['type'];
            $input->imageFileTmpName = $input->imageFileArray['tmp_name'];
            $input->imageFileSize = $input->imageFileArray['size'];
            $input->imageFileExtension = end(explode('.', $input->imageFileName));
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
                    if ($input->imageFileSize > $conf['imageUpload']['maxSize']) {
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

            $this->validated = false;
        }

    }

    function display(& $output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $conf = & $GLOBALS['_SGL']['CONF'];

        if (isset ($output->product->cat_id)) {
            $output->catID = $output->product->cat_id;
            
            // generate the category select box
            $aOptions = array ();
            $menu1 = & new MenuBuilder('SelectBox', $aOptions);
            $menu1->setStartId($conf['ShopMgr']['rootCatID']);
            $aHtmlOptions = $menu1->toHtml();
            $output->catOptions = SGL_Output::generateSelect($aHtmlOptions, @ $output->product->cat_id);
        }

        if (isset ($output->product->manufacturer)) {
            // generate the manufacturer select box
            $aManufacturer = $this->_getManufacturerList();
            $output->manufacturerOptions = SGL_Output::generateSelect($aManufacturer, @ $output->product->manufacturer);
        }
        

        if (isset ($output->product->status)) {
            // generate status select box
            $aStatus = array ();
            if ($conf['statusOpts']) {
                $aStatus = $conf['statusOpts'];
            }
            $output->statusOptions = SGL_Output::generateSelect($aStatus, @ $output->product->status);

            $output->product->statusString = $conf['statusOpts'][@ $output->product->status];
        }

        // promotion check box
        if (isset ($output->product->promotion)) {
            $output->promoOptions = $output->product->promotion;
        }
        
        // product image procces
        // product thumb path set                   
        if (isset ($output->product->img) AND $output->product->img != '' AND file_exists(SGL_WEB_ROOT.'/'.$conf['imageUpload']['thumb'].'/'.$output->product->img)) {
            $output->product->thumbUrl = SGL_BASE_URL.'/'.$conf['imageUpload']['thumb'].'/'.$output->product->img;
        } else {
            $output->product->thumbUrl = SGL_BASE_URL.'/'.$conf['imageUpload']['thumb'].'/'.$conf['imageUpload']['noImageFile'];
        }
        // product image path set
        if (isset ($output->product->img) AND $output->product->img != '' AND file_exists(SGL_WEB_ROOT.'/'.$conf['imageUpload']['directory'].'/'.$output->product->img)) {
            $output->product->imageUrl = SGL_BASE_URL.'/'.$conf['imageUpload']['directory'].'/'.$output->product->img;
        } else {
            $output->product->imageUrl = SGL_BASE_URL.'/'.$conf['imageUpload']['directory'].'/'.$conf['imageUpload']['noImageFile'];
        }
        
        // Price computation
        if (isset ($output->product->price)) {
            $vat = isset ($_SESSION['aPrefs']['VAT']) ? (int) $_SESSION['aPrefs']['VAT'] : $conf['ShopMgr']['defaultVAT'];
        	$output->product->priceVAT = $output->product->price * $vat;
        }
        
        // Price currency
        //$aCurrency = array_keys($conf['exchangeRate']);
        //$output->currencyOptions = SGL_Output::generateSelect($aCurrency, @ $output->product->currency);
        
    
        if (isset ($output->product->description)) {
            $unser = @ $this->_descriptionToAray($output->product->description);
            if (is_array($unser)) {
                $output->product->aDescription = $unser;
            } else {
                $output->product->aDescription = array ();
            }
        }
        
        // Set current category name and category path
        if (isset($output->catID) and isset($output->catID) > 0) {
            $catMgr = & new CategoryMgr();
            $output->path = $catMgr->getBreadCrumbs($output->catID, true, 'linkCrumbsAlt1', true);
            $output->currentCat = $catMgr->getLabel($output->catID);
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
        if (in_array('product_id', $required) and !(isset ($product->product_id) and $product->product_id > 0)) {
            SGL::raiseMsg('Invalid product ID');
            return false;
        }

        // Product name
        if (isset ($product->name) and strlen($product->name) > 1) {
            /*	 if ($product->name != addslashes($product->name))
            		$aErrors['name'] = SGL_Output::translate('Invalid data');
            */
        } else {
            if (in_array('name', $required)) {
                $aErrors['name'] = SGL_Output::translate('Please fill in this field');
            }
        }
        // Product category
        if (in_array('cat_id', $required)) {
            if (isset ($product->cat_id) and $product->cat_id > 0) {
                $oCategory = new CategoryMgr();
                if (strlen($oCategory->getLabel($product->cat_id)) < 1)
                    $aErrors['cat_id'] = SGL_Output::translate('Invalid data');
            } else
                $aErrors['cat_id'] = SGL_Output::translate('Please fill in this field');
        }
        
        // Product Manufacturer
       if (isset ($product->manufacturer) and strlen($product->manufacturer) > 1) {
            if ($product->manufacturer != addslashes($product->manufacturer) || preg_match("/[^A-z0-9_\-\s]/", $product->manufacturer) == 1) {
                $aErrors['manufacturer'] = SGL_Output::translate('Invalid data');
            }
        } else {
            if (in_array('manufacturer', $required)) {
                $aErrors['manufacturer'] = SGL_Output::translate('Please fill in this field');
            }
        }
        
        // Price
        if (in_array('price', $required) and !(isset ($product->price) and $product->price >= 0)) {
            $aErrors['price'] = SGL_Output::translate('Please fill in this field');
        }

        // status
        if (in_array('status', $required) and !(isset ($product->status) and $product->status > 0)) {
            $aErrors['status'] = SGL_Output::translate('Please fill in this field');
        }

        // Product Short description
        if (isset ($product->short_description) and strlen($product->short_description) > 1) {
            /*$aSyntaxErrors = $this->_checkDescriptionSyntax($product->short_description);
            if (count($aSyntaxErrors) != 0) {
                $aErrors['short_description'] = SGL_Output::translate('Syntax error on line(s): ');
                $aErrors['short_description'] .= implode(', ', $aSyntaxErrors);
            }
            */
        } else
            if (in_array('short_description', $required)) {
                $aErrors['short_description'] = SGL_Output::translate('Please fill in this field');
            }

        // Product Code 1
        if (isset ($product->cod1) and strlen($product->cod1) > 1) {
            if ($product->cod1 != addslashes($product->cod1) || preg_match("/[^A-z0-9_\-]/", $product->cod1) == 1) {
                $aErrors['cod1'] = SGL_Output::translate('Invalid data');
            }

        } else
            if (in_array('cod1', $required)) {
                $aErrors['cod1'] = SGL_Output::translate('Please fill in this field');
            }

        // Product Code 2
        if (isset ($product->cod2) and strlen($product->cod2) > 1) {
            if ($product->cod2 != addslashes($product->cod2) || preg_match("/[^A-z0-9_\-]/", $product->cod2) == 1) {
                $aErrors['cod2'] = SGL_Output::translate('Invalid data');
            }
        }

        // Product Warranty
        if (isset ($product->garantii) and strlen($product->garantii) > 1) {
            if ($product->garantii != addslashes($product->garantii) || preg_match("/[^A-z0-9_\-\s]/", $product->garantii) == 1) {
                $aErrors['garantii'] = SGL_Output::translate('Invalid data');
            }
        }

        // Full product description
        if (isset ($product->description) and strlen($product->description) > 1) {
            $aSyntaxErrors = $this->_checkDescriptionSyntax($product->description);
            if (count($aSyntaxErrors) != 0) {
                $aErrors['description'] = SGL_Output::translate('Syntax error on line(s): ');
                $aErrors['description'] .= implode(', ', $aSyntaxErrors);
            }
        } else
            if (in_array('description', $required)) {
                $aErrors['description'] = SGL_Output::translate('Please fill in this field');
            }

        if (isset ($aErrors) && count($aErrors)) {
            $validate = false;
        }
        
        return $validate;
    }


    /**
     * BATCH PROCESSING FUNCTIONS
     */
    
    
    // TO DO: create a separate class with CSV inport/export functions.
    // TO DO: create other methods Ex: xmlUpload ...
    /**
     * An extension of the validate() method that is called for every product
     * record to check the fields syntax
     *
     * @access public
     *
     */
    function _csvUpload(& $input, & $output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'csvUpload.html';
        $output->pageTitle = $this->pageTitle.'::CSV Upload';
        
        $conf = & $GLOBALS['_SGL']['CONF'];

        if (!is_array($input->csvFileArray)) {
            return;
        }

        // return if invalid CVS file or just print the upload form

        $row = 0;
        $handle = fopen($input->csvFileTmpName, "r");
        while ($data = fgetcsv($handle, 1000, ",")) {
            if ($row == 0) {
                // set the columns names
                $num = count($data);
                $csvNames = array ();
                $aCsv = array ();
                for ($c = 0; $c < $num; $c ++) {
                    if (strlen($data[$c]) > 0) {
                        $csvNames[$c] = $data[$c];
                        eval ("$".$data[$c]." = array();");
                    } else {
                        SGL_HTTP_Session::remove('csvUpload');
                        SGL::raiseMsg('Inconsisent CSV table header');
                        SGL_HTTP::redirect(array('action' => 'csvList'));
                        return;
                    }
                }
                $row = 1;
            } else {
                // insert data into table
                if ($row > $conf['CSV']['maxUploadRec']) {
                    break;
                }
                
                $num = count($data);
                $row ++;
                for ($c = 0; $c < $num; $c ++) {
                    $aRow[$csvNames[$c]] = $data[$c];
                }
                $aRow['product_id'] = $row -1;

                // resolve \n\r problems with windows CSVs
                if (isset ($aRow['short_description']) and strlen($aRow['short_description']) > 0) {
                    $aRow['short_description'] = trim(ereg_replace(chr(10), chr(13).chr(10), $aRow['short_description']));
                }
               
                if (isset ($aRow['description']) and strlen($aRow['description']) > 0) {
                    $aRow['description'] = trim(ereg_replace(chr(10), chr(13).chr(10), $aRow['description']));
                }

                $aCsv[($row -1)] = (object) $aRow;
            }
        }
        fclose($handle);

        SGL_HTTP_Session::set('csvUpload', $aCsv);

        SGL::raiseMsg('CSV file uploaded succefuly');
        SGL_HTTP::redirect(array('action' => 'csvList'));
    }


     /**
     * List the batch processing products that are stored in the session
     *
     * @access public
     *
     */
    function _csvList(& $input, & $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'csvUpload.html';
        $output->pageTitle = $this->pageTitle.'::CSV List';

        $aCsv = SGL_HTTP_Session::get('csvUpload');

        if (count($aCsv) > 0) {
            $output->csvRows = $aCsv;
            $output->csvUploadResult = 1;
        } else {
            SGL::raiseMsg('No products');
        }

    }

    
     /**
     * Process the products from the session one by one, validate the 
     * fields and execute the action recorded in the action column
     *
     * @access public
     *
     */
    function _csvProcess(& $input, & $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aCsv = SGL_HTTP_Session::get('csvUpload');

        foreach ($aCsv as $index => $product) {
            $err = array ();
            if (isset ($aCsv[$index]->error))
                unset ($aCsv[$index]->error);
            
            // execute acording to the action column    
            switch ($product->action) {
                case 'add' :
                    $required = array ('name', 'cat_id', 'manufacturer', 'price', 'status', 'cod1');
                    if (!$this->_validateProductEdit($product, $err, $required)) {
                        $aCsv[$index]->error = SGL_Output::translate('Error: ');
                        foreach ($err as $err_line) {
                            $aCsv[$index]->error .= $err_line.'; '; 
                        }
                        break;
                    }
                    
                    $oProduct = & new DataObjects_Product();
                    $oProduct->cod1 = $product->cod1;
                    if ($oProduct->find() != 0) {
                        $aCsv[$index]->error = SGL_Output::translate('Duplicate found').' : '.SGL_Output::translate('Cod1');
                        break;
                    }
                    unset ($oProduct);
                    
                    $oProduct = & new DataObjects_Product();
                    $oProduct->setFrom($product);
                    $dbh = $oProduct->getDatabaseConnection();
                    $oProduct->product_id = $dbh->nextId('product');
                    $oProduct->last_updated = SGL::getTime();
                    $oProduct->updated_by = $_SESSION['uid'];
                    $oProduct->date_created = SGL::getTime();
                    $oProduct->created_by = $_SESSION['uid'];
                    $success = $oProduct->insert();
                    if ($success) {
                        // On success remove the product from the session table
                        unset ($aCsv[$index]);
                    }
                    else {
                        $aCsv[$index]->error = SGL_Output::translate('data save error');
                    }
                    break;
                    
                case 'update' :
                    $required = array ('cod1');
                    if (!$this->_validateProductEdit($product, $err, $required)) {
                        $aCsv[$index]->error = SGL_Output::translate('Error detected. Please edit the record');
                        error_log(print_r($err,true));
                        error_log(print_r($product,true));
                        break;
                    }
                    
                    $oProduct = & new DataObjects_Product();
                    $oProduct->cod1 = $product->cod1;
                    if ($oProduct->find(true) == 0) {
                        $aCsv[$index]->error = SGL_Output::translate('Nu sunt produse').' : '.SGL_Output::translate('Cod1');
                        break;
                    }

                    $prodId = $oProduct->product_id;
                    unset ($oProduct);
                    $oProduct = & new DataObjects_Product();
                    $oProduct->get($prodId);
                    $oProduct->setFrom($product);
                    $oProduct->last_updated = SGL::getTime();
                    $oProduct->updated_by = $_SESSION['uid'];
                    $success = $oProduct->update();
                    if ($success != false) {
                        unset ($aCsv[$index]);
                    } else {
                        $aCsv[$index]->error = SGL_Output::translate('data save error');
                    }
                    break;
                    
                case 'delete' :
                    if (isset ($product->cod1) and $product->cod1 > 0) {
                        $oProduct = & new DataObjects_Product();
                        $oProduct->cod1 = $product->cod1;
                        if ($oProduct->find(true) == 0) {
                            $aCsv[$index]->error = SGL_Output::translate('Nu sunt produse').' : '.SGL_Output::translate('Cod1');
                            break;
                        }
                        
                        $success = $oProduct->delete();
                        if ($success != false) {
                            unset ($aCsv[$index]);
                        } else {
                            $aCsv[$index]->error = SGL_Output::translate('Data delete error');
                        }
                    } else
                        $aCsv[$index]->error = SGL_Output::translate('Invalid data').' : '.SGL_Output::translate('Cod1');
                    break;
                    
                default :
                    $aCsv[$index]->error = SGL_Output::translate('No action selected. Please delete the record.');
                    break;
            }

        }

        SGL_HTTP_Session::set('csvUpload', $aCsv);

        SGL::raiseMsg('CSV file processed');
        SGL_HTTP::redirect(array('action' => 'csvList'));
    }


     /**
     * Display for editing the edit form with product data from session
     *
     * @access public
     *
     */
    function _csvEdit(& $input, & $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'productEdit.html';
        $output->pageTitle = $this->pageTitle.'::CSV Edit';
        $output->formAction = 'csvUpdate';

        $aCsv = SGL_HTTP_Session::get('csvUpload');
        //  get product data
        if (isset ($aCsv[$input->productId])) {
            $aErrors = array ();
            $oProduct = $aCsv[$input->productId];
            $output->product =  clone($oProduct);
        } else {
            SGL::raiseMsg('Invalid product id');
            SGL_HTTP::redirect(array('action' => 'csvList'));
        }
    }


     /**
     * Display for editing the edit form with product data from session
     *
     * @access public
     *
     */
    function _csvUpdate(& $input, & $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aCsv = SGL_HTTP_Session::get('csvUpload');
        $output->formAction = 'csvUpdate';

        $oProduct = (object) $input->product;
                    

        if (isset ($oProduct->product_id) AND $oProduct->product_id > 0) {
            if ($oProduct->action != 'add') {
                $objVars = get_object_vars($aCsv[$oProduct->product_id]);
                foreach ($objVars as $name => $value)
                    if (isset($oProduct->$name)) {
                    $aCsv[$oProduct->product_id]-> $name = $oProduct->$name;
                    }
            } else {
                $aCsv[$oProduct->product_id] = $oProduct;
            }

            $success = true;
            SGL_HTTP_Session::set('csvUpload', $aCsv);
        } else {
            SGL::raiseError('Invalid product ID', SGL_ERROR_NOAFFECTEDROWS);
            $output->template = 'productEdit.html';
            return;
        }

        if ($success) {
            //  redirect on success
            SGL::raiseMsg('Product updated successfully');
            SGL_HTTP::redirect(array('action' => 'csvList'));
        } else {
            SGL::raiseError('There was a problem updating the record', SGL_ERROR_NOAFFECTEDROWS);
            $output->template = 'csvEdit.html';
        }
    }


     /**
     * Delete product from the session 
     *
     * @access public
     *
     */
    function _csvDelete(& $input, & $output) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'csvUpload.html';

        $aCsv = SGL_HTTP_Session::get('csvUpload');

        if (is_array($input->aDelete)) {
            foreach ($input->aDelete as $index => $productId) {
                if (isset ($aCsv[$productId]))
                    unset ($aCsv[$productId]);
            }
        } else {
            SGL::raiseError('Incorrect parameter passed to '.__CLASS__.'::'.__FUNCTION__, SGL_ERROR_INVALIDARGS);
        }

        SGL_HTTP_Session::set('csvUpload', $aCsv);

        //  redirect on success
        SGL::raiseMsg('Product deleted successfully');
        SGL_HTTP::redirect(array('action' => 'csvList'));
    }

    // TO DO: create other methods Ex: xmlExport ...
     /**
     * Export all the products from DB in CSV format
     *
     * @access public
     *
     */
    function _csvExport(& $input, & $output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aProduct = array ();
        $query = 'SELECT * FROM product';
        $dbh = & SGL_DB::singleton();
        $result = $dbh->query($query);
        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);

        // export headers
        header("Content-type: application/ofx");
        header("Content-Disposition: attachment; filename=product.csv");
        
        // export column names
        unset ($row['product_id']);
        echo $this->_genCsvLine(array_keys($row));
        
        // export records
        while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
            unset ($row['product_id']);
            echo $this->_genCsvLine($row);
        }
        exit();
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
        
        //  insert record
        $oProduct = & new DataObjects_Product();
        $oProduct->setFrom($input->product);
        $dbh = $oProduct->getDatabaseConnection();
        $oProduct->product_id = $dbh->nextId('product');
        $oProduct->last_updated = SGL::getTime();
        $oProduct->updated_by = $_SESSION['uid'];
        $oProduct->date_created = SGL::getTime();
        $oProduct->created_by = $_SESSION['uid'];
        $success = $oProduct->insert();
        
        // if you hit sumit again an update action will be performed
        $output->formAction = 'update';
        if ($success) {
            //  redirect on success
            SGL::raiseMsg('Product saved successfully');
            SGL_HTTP::redirect(array ('action' => 'list', 'frmCatID' => $input->product->cat_id));
        } else {
            SGL::raiseError('There was a problem inserting the record', SGL_ERROR_NOAFFECTEDROWS);
            $output->product = $oProduct;

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
        $output->catID = $oProduct->cat_id;
        
        
        // TO DO: the best is yet to come
        $dbh = & SGL_DB::singleton();
        $query = 'SELECT * FROM product';
        $result = $dbh->query($query);
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

        $output->formAction = 'update';
        $oProduct = & new DataObjects_Product();
        $oProduct->get($input->product->product_id);
        $oProduct->setFrom($input->product);
        $oProduct->last_updated = SGL::getTime();
        $oProduct->updated_by = $_SESSION['uid'];

        
        if (isset ($oProduct->product_id) AND $oProduct->product_id > 0) {
            $success = $oProduct->update();
        } else {
            $dbh = $oProduct->getDatabaseConnection();
            $oProduct->product_id = $dbh->nextId('product');
            $oProduct->date_created = SGL::getTime();
            $oProduct->created_by = $_SESSION['uid'];
            $success = $oProduct->insert();
        }        

        $output->product = $oProduct;
        
        $output->catID = $oProduct->cat_id;
        
        if ($success) {
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

        $conf = & $GLOBALS['_SGL']['CONF'];

        $output->template = 'productEdit.html';
        $output->formAction = 'update';
        $oProduct = & new DataObjects_Product();
        $oProduct->get($input->productId);
        $output->product = $oProduct;

        if (!is_writable(SGL_WEB_ROOT.'/'.$conf['imageUpload']['directory'])) {
            include_once 'System.php';
            $success = System::mkDir(array (SGL_UPLOAD_DIR));
            if (!$success) {
                SGL::raiseError('The upload directory does not appear to be writable, please give the
                                   webserver permissions to write to it', SGL_ERROR_FILEUNWRITABLE, PEAR_ERROR_DIE);
            }
        }

        if (!is_writable(SGL_WEB_ROOT.'/'.$conf['imageUpload']['directory'])) {
            include_once 'System.php';
            $success = System::mkDir(array (SGL_WEB_ROOT.'/'.$conf['imageUpload']['directory']));
            if (!$success) {
                SGL::raiseError('The upload directory does not appear to be writable, please give the
                                   webserver permissions to write to it', SGL_ERROR_FILEUNWRITABLE, PEAR_ERROR_DIE);
            }
        }

        // Store the image
       // $fileName = SGL_WEB_ROOT.'/'.$conf['imageUpload']['directory'].'/'.$input->productId.'.'.$input->imageFileExtension;
       /* if (!move_uploaded_file($input->imageFileTmpName, $fileName)) {
            SGL::raiseError('The upload directory does not appear to be writable, please give the 
                               webserver permissions to write to it', SGL_ERROR_FILEUNWRITABLE, PEAR_ERROR_DIE);
        } */
        
        // resizeing images
        require_once SGL_LIB_DIR.'/other/phpthumb/phpthumb.class.php';
        // thumb generator
        $phpThumb = new phpThumb();
        $phpThumb->setSourceFilename($input->imageFileTmpName);
        $phpThumb->aoe = 1;
        $phpThumb->far = 1;
        $phpThumb->bg = $conf['imageUpload']['background'];
        $phpThumb->h = $conf['imageUpload']['thumbHeight'];
        $phpThumb->w = $conf['imageUpload']['thumbWidth'];
        $phpThumb->config_output_format = 'jpeg';
        $phpThumb->config_error_die_on_error = false;
        if ($phpThumb->GenerateThumbnail()) {
            // generate & output thumbnail
            $phpThumb->RenderToFile(SGL_WEB_ROOT.'/'.$conf['imageUpload']['thumb'].'/'.$input->productId.'.jpg');
        } else {
            // do something with error message
            SGL::raiseMsg(implode(",", $phpThumb->debugmessages));
        }
        unset ($phpThumb);
        
        // image generator
        $phpThumb = new phpThumb();
        $phpThumb->setSourceFilename($input->imageFileTmpName);
        $phpThumb->aoe = 1;
        $phpThumb->far = 1;
        $phpThumb->bg = $conf['imageUpload']['background'];
        $phpThumb->h = $conf['imageUpload']['imageHeight'];
        $phpThumb->w = $conf['imageUpload']['imageWidth'];
        $phpThumb->config_output_format = 'jpeg';
        $phpThumb->config_error_die_on_error = false;
        if ($phpThumb->GenerateThumbnail()) {
            // generate & output thumbnail
            $phpThumb->RenderToFile(SGL_WEB_ROOT.'/'.$conf['imageUpload']['directory'].'/'.$input->productId.'.jpg');
        } else {
            // do something with error message
            SGL::raiseMsg(implode(",", $phpThumb->debugmessages));
        }
        unset ($phpThumb);

        //$oProduct->img = $input->productId.'.'.$input->imageFileExtension;
        $oProduct->img = $input->productId.'.jpg';
        $success = $oProduct->update();
        if ($success) {
            SGL::raiseMsg('Image uploaded successfully');
        } else {
            SGL::raiseError('There was a problem updating the record');
        }
        //SGL_HTTP::redirect( array ('action' => 'edit', 'frmProdId' => $input->productId));

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


    /**
    * List for editing, the products from DB sorted by category
    *
    * @access public
    *
    */ 
    function _list(& $input, & $output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'productListAdmin.html';
        $output->pageTitle = 'Products::Browse';

        
        // Generate the orderBy query
        $orderBy_query = '';
        $allowedSortFields = array('product_id','cod1','manufacturer','name','last_updated','promotion','price','currency');
        if (isset($input->sortBy) and strlen($input->sortBy) > 0 
           and isset($input->sortOrder) and strlen($input->sortOrder) > 0 
           and in_array($input->sortBy, $allowedSortFields)) {
                $orderBy_query = 'ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder ; 
        }

        $dbh = & SGL_DB::singleton();
        
        // If not valid cat ID, list all the products
        if ($input->catID < 1) {
            $query = 'SELECT * FROM product '.$orderBy_query;
        } else {
            $query = 'SELECT * FROM product WHERE cat_id = '.$input->catID.' '.$orderBy_query;
        }
        
        $limit = 5 * $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array ('mode' => 'Sliding', 'delta' => 3, 'perPage' => $limit, 'totalItems' => $input->totalItems);
        $aPagedData = SGL_DB::getPagedData($dbh, $query, $pagerOptions);
        
        if (is_array($aPagedData) && count($aPagedData['data'])) {
            foreach ($aPagedData['data'] as $key => $value) {
                $product = $aPagedData['data'][$key];
                
                // trim short_descriptin to 30 chars
                $oldShortLen = strlen($product['short_description']);
                $product['short_description'] = substr($product['short_description'],0,30);
                if (strlen($product['short_description']) < $oldShortLen) {
                    $product['short_description'] .= ' ...';
                }
                
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
        $output->catID = $input->catID;
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


    /**
     * It verifies if the supplied text matches the syntax for the 'Description'
     * field.
     *
     * @access public
     *
     * @param string $inText   Multi line string that needs verification
     * 
     * @return array           With lines that have syntax errors
     *
     * @abstract  The syntax for the description field is like this:
     * 				'varName:varValue'	OR
     * 				'varName:'	 		OR
     * 				':varValue'
     * 
     * 			  varValue can contain ':' separator so this:
     *              'varName:this: varValuet : is ok'   
     * 
     */
    function _checkDescriptionSyntax($inText) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aRet = array();
        $aLines = explode("\n",$inText);
        foreach($aLines as $lineNo => $line) {
            $aSplit = explode(':',$line,2);
            if (!(is_array($aSplit) and count($aSplit) == 2)) {
                $aRet[] = $lineNo+1;
            }
        }
        return $aRet;
    }



    /**
    * Generates the navigation menu
    *
    * @access public
    *
    */ 
/*
    Not used
    
    function getNavigation(& $input, & $output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $menu = & new MenuBuilder('Explorer');
        $menu->setStartId(1);
        $output->menu = $menu->toHtml();
    }
/*

    /**
    * Generates a CSV row filled with data from one product
    * Taken from PHP help file.
    *
    * @access public
    *
    */ 
    function _genCsvLine($inArray, $deliminator = ",") 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $line = "";
        foreach ($inArray as $val) {
            # remove any windows new lines,
            # as they interfere with the parsing at the other end
            $val = str_replace("\r\n", "\n", $val);

            # if a deliminator char, a double quote char or a newline
            # are in the field, add quotes
            if (ereg("[$deliminator\"\n\r]", $val)) {
                $val = '"'.str_replace('"', '""', $val).'"';
            } #end if

            $line .= $val.$deliminator;

        } #end foreach

        # strip the last deliminator
        $line = substr($line, 0, (strlen($deliminator) * -1));
        # add the newline
        $line .= "\n";
        return $line;
    }
}
?>