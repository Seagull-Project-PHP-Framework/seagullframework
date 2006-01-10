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
class ShopConfigMgr extends SGL_Manager {

    function ShopConfigMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();
        
        $this->module = 'shop';
        $this->pageTitle = 'Shop Manager';
        $this->template = 'productListAdmin.html';
        $this->masterTemplate = 'master.html';
        $this->_aAllowedFileTypes = array ('gif', 'jpeg', 'jpg', 'png');
        $this->_aActionsMapping = array (
                'list' => array ('list'),
                'updateConfig' => array ('updateConfig'),
                'validateConfig' => array ('validateConfig'),
                );
        //$this->conf = & $GLOBALS['_SGL']['CONF'];
        //$this->dbh = & SGL_DB::singleton();

    }

    function validate($req, & $input) 
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::validate($req, $input);

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
        $input->sortBy          = SGL_Util::getSortBy($req->get('frmSortBy'), SGL_SORTBY_USER);
        $input->sortOrder       = SGL_Util::getSortOrder($req->get('frmSortOrder'));
        $input->config          = $req->get('Shop');

        if ($input->action == 'updateConfig') {
            if (!$this->_validateConfig($input->config, $aErrors)) {
                $input->error = $aErrors;
                $this->validated = false;
                $input->template = 'configEdit.html';
                $input->formAction = 'list';
                return $input;
            }
        }

        //  if errors have occured
        if (isset ($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;

            if ($input->action == 'configEdit') {
                $input->template = 'configEdit.html';
            }
            $this->validated = false;
        }
    }


    function _list(&$input, &$output)
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

        if (empty($config['ShopMgr']['requiresAuth'])
            || !is_numeric((int)$config['ShopMgr']['requiresAuth'])) {
            //$aErrors['ShopMgr']['requiresAuth'] = SGL_Output::translate('Please fill in this field');
            $config['ShopMgr']['requiresAuth'] = 0;
        }

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
    }

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

function write_ini_file($path, $assoc_array) {

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

  }
?>
