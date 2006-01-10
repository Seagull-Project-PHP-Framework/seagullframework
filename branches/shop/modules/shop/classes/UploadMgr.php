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

require_once SGL_MOD_DIR.'/shop/classes/ShopAdminMgr.php';
require_once SGL_MOD_DIR.'/navigation/classes/MenuBuilder.php';
require_once SGL_CORE_DIR . '/Category.php';


if (isset($GLOBALS['_SGL']['CONF']['ShopMgr']['multiCurrency']) &&
    $GLOBALS['_SGL']['CONF']['ShopMgr']['multiCurrency'] == true) {
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
class UploadMgr extends SGL_Manager {  ///?

    function UploadMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->module = 'shop';
        $this->pageTitle = 'Upload Manager';
        $this->template = 'csvUpload.html';
        $this->masterTemplate = 'masterLeftCol.html';
        $this->_aAllowedFileTypes = array ('gif', 'jpeg', 'jpg', 'png');
        $this->_aActionsMapping = array (
                'csvUpload'   => array ('csvUpload'),
                'csvList'     => array ('csvList'),
                'csvProcess'  => array ('csvProcess'),
                'csvEdit'     => array ('csvEdit'),
                'csvUpdate'   => array ('csvUpdate'),
                'csvDelete'   => array ('csvDelete'),
                'csvExport'   => array ('csvExport'),
                'csvImage'    => array ('csvImage'),
                'updateOrder' => array ('updateOrder'),
                'genCsvLine'  => array ('genCsvLine'),
                'csvUploadProducts' => array('csvUploadProducts'),
                );
                
        //TO DO: activate rate manager
        //$this->conf = & $GLOBALS['_SGL']['CONF'];
        //$this->dbh = & SGL_DB::singleton();

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

        $aErrors = array ();

        $this->validated = true;
        $input->error = array ();
        $input->pageTitle = $this->pageTitle;
        // restore previouse template
        $input->template = $this->template;

        $input->masterTemplate  = $this->masterTemplate;
        $input->javascriptSrc   = array('TreeMenu.js');
        $input->action          = ($req->get('action')) ? $req->get('action') : 'csvUpload';
        $input->updateOrder     = $req->get('updateOrder');
        if (isset ($input->updateOrder)) {
            $input->action      = 'updateOrder';
            $input->productOrder    = $req->get('productOrder');
        }
        $input->productOrder    = $req->get('productOrder');
        $input->productId       = $req->get('frmProdId');
        $input->aDelete         = $req->get('frmDelete');
        $input->submit          = $req->get('submitted');
        $input->product         = (object) $req->get('product', true);
        $input->product->description = $req->get('frmBodyName', true);
        $input->product->promotion = (isset ($input->product->promotion)) ? 1 : 0;
        $input->product->new_id = (isset ($input->product->new_id )) ? 1 : 0;
        $input->product->bargain = (isset ($input->product->bargain )) ? 1 : 0;
        $input->totalItems      = $req->get('totalItems');
        $input->sortBy          = SGL_Util::getSortBy($req->get('frmSortBy'), SGL_SORTBY_USER);
        $input->sortOrder       = SGL_Util::getSortOrder($req->get('frmSortOrder'));
        $input->csvType         = $req->get('csvType');
        
        if ($input->action == 'csvUpdate') {
            if ($input->product->action == 'add')
                $required = array ('product_id', 'name', 'cat_id', 'manufacturer',
                                   'price', 'status', 'cod1');
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

        // csvUpload
        $input->csvFileArray = $req->get('csvFile');
        if (is_array($input->csvFileArray)) {
            $input->csvFileName = $input->csvFileArray['name'];
            $input->csvFileType = $input->csvFileArray['type'];
            $input->csvFileTmpName = $input->csvFileArray['tmp_name'];
            $input->csvFileSize = $input->csvFileArray['size'];
            $tmp = explode('.', $input->csvFileArray['name']);
            $input->csvFileExtension = end($tmp);
            if ($input->csvFileExtension !== 'csv') {
                $aErrors['csvUpload'] = SGL_Output::translate('Invalid file type');
            }
            if (empty($input->csvFileName)) {
                $aErrors['csvUpload'] = SGL_Output::translate('Invalid file');
            }
        }

        //  if errors have occured
        if (isset ($aErrors) && count($aErrors)) {
            if ($input->action == 'csvUpload') {

                $input->template = 'csvUpload.html';
            }
            $input->error = $aErrors;
            $this->validated = false;
        }

    }

    function display(& $output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
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
    function _csvUpload(& $input, & $output){
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->pageTitle = $this->pageTitle.'::CSV Upload';
        
        if ($input->csvType == "products") {
            $this->_csvUploadProducts($input,$output);
        }

    }

    function _csvUploadProducts(& $input, & $output){
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'csvUpload.html';
        
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
                        SGL::raiseMsg('Inconsisent CSV table header.'.'Column'."$c".'has length = 0 ...');
                        
                        SGL_HTTP::redirect(array('action' => 'csvList'));
                        return;
                    }
                }
                $row = 1;
            } else {
                // insert data into table
                if ($row > $this->conf['CSV']['maxUploadRec']) {
                    break;
                }
                
                $num = count($data);
                $row ++;
                for ($c = 0; $c < $num; $c ++) {
                    $aRow[$csvNames[$c]] = $data[$c];
                }
                $aRow['product_id'] = $row -1;

                // resolve \n\r problems with windows CSVs
                if (isset ($aRow['short_description'])
                    and strlen($aRow['short_description']) > 0) {
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
    * CSV Image upload function
    * upload images from \upload directory or directory specified in $input->product->img
    *
    * @author  Rolandas Burbulis, <rolandas@baltmedija.lt>
    * @access  public
    */

    function _csvImage(& $input, & $output)   {

        $aCsv = SGL_HTTP_Session::get('csvUpload');
        //if image path is set in $product->img copy image from $product->img
        // if no check /www/upload for cod1=filename maching.
        $upload_dir = SGL_WEB_ROOT.'/'."upload";
        $new_filename = $input->product->product_id.'.jpg';

         //if prod-img == imagefile
         if (!empty($input->product->img)) {
             $filename = $upload_dir.'/'.$input->product->img;
         //if cod1==imagefile
         } else {
             $filename = $upload_dir.'/'.$input->product->cod1.".jpg";
         }

         if (file_exists($filename)) {
             $imageFileArray['tmp_name'] = $filename;
             $imageFileArray['name'] = $new_filename;

             if (ShopAdminMgr::uploadImg($imageFileArray, $new_filename)) {
                 $input->product->img = $input->product->product_id.'.jpg';
                 //we are inserting product
              } else {
                 $aCsv[$input->index]->notice = SGL_Output::translate('image add error');
                 die("file $filename uploaded NOT success");
              };

          //if image not found
          } else {
              $aCsv[$input->index]->notice =
                 SGL_Output::translate("Product_id ".$input->product->product_id." cod1 ".$input->product->product_id." No image found");
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
//        $output->pageTitle = $this->pageTitle.'::CSV Process'; //no title  needed
        
        $aCsv = SGL_HTTP_Session::get('csvUpload');
        foreach ($aCsv as $index => $product) {
            $err = array ();
            if (isset ($aCsv[$index]->error))
                unset ($aCsv[$index]->error);
            $input->product = $product;
            // execute acording to the action column
            switch ($product->action) {
                case 'add' :
                    $required = array ('name', 'cat_id', 'manufacturer', 'price', 'cod1');
                    if (!ShopAdminMgr::_validateProductEdit($product, $err, $required)) {
                        $aCsv[$index]->error = SGL_Output::translate('Error:');
                        foreach ($err as $err_line) {
                            $aCsv[$index]->error .= $err_line.'; '; 
                        }
                        break;
                    }

                    $oProduct = & new DataObjects_Product();
                    $oProduct->cod1 = $product->cod1;
                    if ($oProduct->find() != 0) {
                        $aCsv[$index]->error =
                            SGL_Output::translate('Duplicate found: Product with the same cod1 exists');
                        break;
                    }
                    unset ($oProduct);

                    $oProduct = & new DataObjects_Product();
                    $oProduct->setFrom($product);
                    $dbh = $oProduct->getDatabaseConnection();
                    $oProduct->product_id = $dbh->nextId('product');

                    $input->product->product_id = $oProduct->product_id;
                    $input->index = $index;
                    $this->_csvImage($input, $output);
                    if (empty($aCsv[$index]->notice)) {
                        $oProduct->img = $input->product->img;
                    }
                    
                    $oProduct->last_updated = SGL_Date::getTime();
                    $oProduct->updated_by = $_SESSION['uid'];
                    $oProduct->date_created = SGL_Date::getTime();
                    $oProduct->created_by = $_SESSION['uid'];
                    $success = $oProduct->insert();
                    if ($success == 1 && empty($aCsv[$index]->notice)) {
                        // On success remove the product from the session table
                        unset ($aCsv[$index]);
                    } elseif($success != 1) {
                        $aCsv[$index]->error = SGL_Output::translate('data save error');
                    }
                    break;
                    
                case 'update' :
                    $required = array ('cod1');
                    if (!ShopAdminMgr::_validateProductEdit($product, $err, $required)) {
                        $aCsv[$index]->error = SGL_Output::translate('Error detected. Please edit the record');
                        error_log(print_r($err,true));
                        error_log(print_r($product,true));
                        break;
                    }
                    $oProduct = & new DataObjects_Product();
                    $oProduct->cod1 = $product->cod1;
                    if ($oProduct->find(true) == 0) {
                        $aCsv[$index]->error = SGL_Output::translate('No such product').' : '.SGL_Output::translate('Cod1');
                        break;
                    }
                    $prodId = $oProduct->product_id;
                    unset ($oProduct);
                    $oProduct = & new DataObjects_Product();
                    $oProduct->get($prodId);
                    
                    /* image upload from directory */
                    $input->product->product_id = $prodId;
                    $input->index = $index;
                    $this->_csvImage($input, $output);
                    if (empty($aCsv[$index]->notice)) {
                        $oProduct->img = $input->product->img;
                    }

                    $oProduct->setFrom($input->product);
                    $oProduct->last_updated = SGL_Date::getTime();
                    $oProduct->updated_by = $_SESSION['uid'];
                    $success = $oProduct->update();

//ToThink what we should report if few errors found?
                    if ($success == 1 && empty($aCsv[$index]->notice)) {
                        unset ($aCsv[$index]);
                    } elseif($success != 1) {
                        $aCsv[$index]->error = SGL_Output::translate('data save error'.$success);
                    }
                    break;
                    
                case 'delete' :
                    if (isset ($product->cod1) and $product->cod1 > 0) {
                        $oProduct = & new DataObjects_Product();
                        $oProduct->cod1 = $product->cod1;
                        if ($oProduct->find(true) == 0) {
                            $aCsv[$index]->error = SGL_Output::translate('Product not found');
                            break;
                        }

                        // TODO: Delete image;
                        if (isset($oProduct->img)){
                            $thubDir = SGL_WEB_ROOT.'/'.$this->conf['imageUpload']['thumb'];
                            $imageDir = SGL_WEB_ROOT.'/'.$this->conf['imageUpload']['directory'];
                            if (!unlink ($thubDir.'/'.$oProduct->img)){
                               $aCsv[$index]->error = SGL_Output::translate('Image delete error');
                            };
                            if (!unlink ($imageDir.'/'.$oProduct->img)){
                               $aCsv[$index]->error = SGL_Output::translate('Thumbnail delete error');
                            };
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
        $output->pageTitle = $this->pageTitle.'::CSV Update';
        
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
//        $dbh = & SGL_DB::singleton();
        $result = $this->dbh->query($query);
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
