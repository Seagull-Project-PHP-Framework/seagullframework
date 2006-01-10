<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | PriceMgr.php                                                              |
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
// $Id: PriceMgr.php,v 1.4 2005/05/09 23:55:20 demian Exp $

require_once SGL_CORE_DIR . '/Category.php';
require_once 'Spreadsheet/Excel/Writer.php';

require_once SGL_ENT_DIR . '/Product.php';
require_once SGL_ENT_DIR . '/Price.php';
require_once SGL_ENT_DIR . '/User_preference.php';

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
class PriceMgr extends SGL_Manager
{
	
    function PriceMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->module		= 'shop';
        $this->pageTitle    = 'Price';
        $this->template     = 'userList.html';
        $this->_aActionsMapping =  array(
            'list'       => array('list'),
            'export'       => array('export'),
        );
        
        $this->catMgr = & new SGL_Category();
        $this->dbh = & SGL_DB::singleton();
        //TO DO: activate rate manager
        $this->conf = & $GLOBALS['_SGL']['CONF'];
        
        if (isset($this->conf['ShopMgr']['multiCurrency'])) {
            if($this->conf['ShopMgr']['multiCurrency']) {
                $rateMgr = & new RateMgr();
            } else {
                $this->conf['exchangeRate'][$this->conf['ShopMgr']['defaultCurrency']] =
                $this->conf['ShopMgr']['defaultExchange'];
            }
        } 
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
            
        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->template    = $this->template;
        $input->masterTemplate = $this->masterTemplate;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'export';
        $input->catId       = (int) ($req->get('frmCatID')) ? $req->get('frmCatID') : 0; 
       
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $input->template = 'userList.html';
            $this->validated = false;
        }
              
    }
     
    // TO DO: find a way to make the code more reusable to product table change
    // TO DO: add translations to exported file
    /**
    * Generate a nice formatted A4 excel file with all the products sorted
    * recursively by category
    *
    * @access public
    *
    */
    function _export(& $input, & $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->conf = & $GLOBALS['_SGL']['CONF'];
        $usrId = $_SESSION['uid'];  
        
        // If not set in aPrefs take the defaults from conf.ini
        $discount = isset ($_SESSION['aPrefs']['productDiscount']) 
            ? (int) $_SESSION['aPrefs']['productDiscount'] 
            : $this->conf['ShopMgr']['defaultDiscount'];
        
        // get category tree
        $catTree = $this->_getCatTree(4);
        //echo "<pre>"; print_r($catTree);exit;    
        $fileName = SGL_SERVER_NAME.date('_Y_m_d').'.xls';    
        
		$workbook = new Spreadsheet_Excel_Writer();
		$workbook->send($fileName);
		$worksheet =& $workbook->addWorksheet(SGL_String::Translate('Price list'));
		$worksheet->setPaper(9);  // Set A4 paper size
		$worksheet->setMargins_LR(0);
		$worksheet->setMargins_TB(0);
		$workbook->setCustomColor(11, 41, 27, 107);
		
		//Set column widths
		$worksheet->setColumn(0,0,12);
		$worksheet->setColumn(1,1,24);
		$worksheet->setColumn(2,2,53);
		$worksheet->setColumn(3,3,8.43);
		
		//Path formating
		$format_path =& $workbook->addFormat();
		$format_path->setBold();
		$format_path->setColor('white');
		$format_path->setPattern(1);
		$format_path->setFgColor(11);
		$format_path->setAlign('left');
		$format_path->setBorder(1);
		$format_path->setBorderColor(11);
				 
		//table header formating
		$format_head =& $workbook->addFormat();
		$format_head->setBold();
		$format_head->setBorder(1);
		$format_head->setBorderColor(11);
  
  		//item formating
  		$format_item =& $workbook->addFormat();
  		$format_item->setSize(8);
  		$format_item->setBorder(1);
		$format_item->setBorderColor(11);
		
		// format S.C. ... S.R.L.
		$format_firm =& $workbook->addFormat();
		$format_firm->setBold();
		$format_firm->setSize(12);
		$format_firm->setAlign('right');
		
		//format Tel: ...  Fax: ....
		$format_address =& $workbook->addFormat();
		$format_address->setAlign('right');
		
		//Format Oferta de pret
		$format_title =& $workbook->addFormat();
		$format_title->setBold();
		$format_title->setSize(12);
		$format_title->setAlign('merge');
		
		// write page header
        $logo = SGL_THEME_DIR.'/'.$_SESSION['aPrefs']['theme'].'/images/excel_logo.bmp';
        if (file_exists($logo)) {
            $worksheet->insertBitmap(0,0,$logo);
        }
		$worksheet->write(0,3,'S.C. Blue Star Design S.R.L',$format_firm);
		$worksheet->write(1,3,'Sample Street 7, Someware Around 310031 - Arad, Romania',$format_address);
		$worksheet->write(2,3,'Visit: http://www.bluestardesign.ro',$format_address);
		
		$time = (string) time();
		$worksheet->write(6,0,SGL_String::Translate('The offer is valid until').' '.SGL_Output::formatDate($time),$format_title);
		$worksheet->write(6,1,'',$format_title);
		$worksheet->write(6,2,'',$format_title);
		$worksheet->write(6,3,'',$format_title);
            
        $xlsRow = 8;
        $query = 'SELECT product.*,product.price as default_price, IFNULL(price.price, product.price*(100-'.$discount.')/100) AS price 
                  FROM product LEFT JOIN price ON price.product_id=product.product_id AND price.usr_id = '.$usrId.' 
                  WHERE 1=1 AND product.cat_id = ? ORDER BY product.cat_id, manufacturer, price';
             
//        $dbh = & SGL_DB::singleton();
        $sth = $this->dbh->prepare($query);
        foreach($catTree as $catItem) {
        	
		    $result = $this->dbh->execute($query,array($catItem['id']));
		    //echo "<pre>"; print_r($catItem);
		    if ($result->numRows() < 1)
		    	continue;
		    
		    $firstRow = true; 
		    // export records
		    while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
		    	unset($row['product_id']);
		    	unset($row['catID']);
		    	if ($firstRow) {
		    		// export category path
		    		$worksheet->write($xlsRow,0,'');
		    		$xlsRow++;
		    		$worksheet->write($xlsRow,0,$this->catMgr->getBreadCrumbs($catItem['id'],false),$format_path);
		    		$worksheet->write($xlsRow,1,'',$format_path);
		    		$worksheet->write($xlsRow,2,'',$format_path);
		    		$worksheet->write($xlsRow,3,'',$format_path);
		    		$xlsRow++;
		    		// export column names
		    		$worksheet->write($xlsRow,0,SGL_String::Translate('Manufacturer'),$format_head);
		    		$worksheet->write($xlsRow,1,SGL_String::Translate('Product'),$format_head);
		    		$worksheet->write($xlsRow,2,SGL_String::Translate('Description'),$format_head);
		    		$worksheet->write($xlsRow,3,SGL_String::Translate('Price'),$format_head);
		    		$xlsRow++;
		    		$firstRow = false;
                }		        
		        
                //process short_description
                $row['short_description'] = str_replace("\n",' ',$row['short_description']);
                $row['short_description'] = str_replace("\r",' ',$row['short_description']);
                
		        
                //export item
                    if ($GLOBALS['_SGL']['CONF']['ShopMgr']['multiCurrency']) {
                        $row['price'] = $this->currencyConverter($row['price'],$row['currency'],
                            $GLOBALS['_SGL']['CONF']['ShopMgr']['defaultCurrency']);
                    }
		        	$worksheet->write($xlsRow,0,$row['manufacturer'],$format_item);
		    		$worksheet->write($xlsRow,1,$row['name'],$format_item);
		    		$worksheet->write($xlsRow,2,$row['short_description'],$format_item);
		    		$worksheet->writeNumber($xlsRow,3,number_format($row['price'], 0, ',', '.'),$format_item);
		    		$xlsRow++;
		    }
		    
		    
		   // echo "<pre>"; print_r($aProduse);
        unset($result);
        }
        // Let's send the file
        $worksheet->hideGridLines();
		$workbook->close();  
    }
    
    /**
    * navigate recursively in the category tree 
    *
    * @access public
    * 
    * @param int $id     Parent category ID
    * @param int $level  How deep we are in the category tree
    *
    * @return array      The child categories of the Parent CatID
    * 
    */
    function _getCatTree($id = 0, $level = 1) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $result = $this->catMgr->getChildren($id); 
        $categoryList = array();
        for ($x = 0; $x < count($result); $x++) {
            //  only generate link if node if leaf
            if ($this->catMgr->isBranch($result[$x]['category_id'])) {
                $categoryList[] =  array('level' => $level, 'label' => $result[$x]['label'], 'id'=> $result[$x]['category_id']);
            } else {
                $categoryList[] =  array('level' => $level, 'label' => $result[$x]['label'], 'id'=> $result[$x]['category_id']);
            }
            // if branch then recurse
            if ($this->catMgr->isBranch($result[$x]['category_id'])) {
                $branch = $this->_getCatTree($result[$x]['category_id'], $level+1);
                foreach($branch as $catItem)        
                    $categoryList[] = $catItem; 
            }
        }
        return $categoryList;    
    }
    
    
    // Testing only. The best is yet to come 
    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
            
        $this->conf = & $GLOBALS['_SGL']['CONF'];
                
        $aOptions = array();
        $menu2 = & new MenuBuilder('SelectBox', $aOptions);
        // category select box
        $menu2->setStartId(4);
        $aHtmlOptions = $menu2->toHtml();
        $output->pretCatOptions = SGL_Output::generateSelect($aHtmlOptions);
        
        //echo '<pre>'; print_r($output->catOptions); exit;  
    }
    
    // Testing only. The best is yet to come
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

			$line.= $val.$deliminator;

		} #end foreach

		# strip the last deliminator
		$line = substr($line, 0, (strlen($deliminator) * -1));
		# add the newline
		$line.= "\n";

		return $line;
	}
    
    
    /**
    * The currency converter function, the same like in Output.php 
    *
    * @access public
    * 
    * @param real 	 $amount    The price
    * @param string  $from  	The price's currency
    * @param string  $to  		The destination currency
    * @param boolean $format  	If we want number formating True = Yes 
    *
    * @return mixed	 The converted price. Real if not formated and string if
    * formated
    * 
    */
    function currencyConverter ($amount, $from, $to, $format = true) 
    {
        $this->conf = & $GLOBALS['_SGL']['CONF'];
        if (!(array_key_exists($from,$this->conf['exchangeRate']) and array_key_exists($to,$this->conf['exchangeRate']))) {
            return '';    
        } 
        
        $price = $amount * $this->conf['exchangeRate'][$from] / $this->conf['exchangeRate'][$to];
        
        if ($format) {
           $price = number_format($price, 0, ',', '.');
        } 
        
        return $price;
    }
}
?>
