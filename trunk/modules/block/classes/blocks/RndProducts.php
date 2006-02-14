<?php
/**
 * RndMsgBlock : Returns a random message, or empty string on failure
 *
 * @package block
 * @author  Ori shiloh
 * @version 0.1
 */

 /*
     TODO:
     Find a way to use only one sql query.
     Promotion variant.
     Translation: SGL blocks problem.
     Thumbnails: ""
     Price:
 */

include_once SGL_MOD_DIR . '/rate/classes/RateMgr.php';
define('NUMBER_OF_PRODUCTS', 5);
define('THUMBS_DIR','images/shop/thumb/');

class RndProducts
{
    function init()
    {
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();
        $rateMgr = & new RateMgr();
        return $this->getBlockContent();
    }

    function currencyConverter ($amount, $from, $to, $format = true)
    {
        if (!(array_key_exists($from, $this->conf['exchangeRate'])
                && array_key_exists($to, $this->conf['exchangeRate']))) {
            return '';
        }

        $price = $amount * $this->conf['exchangeRate'][$from] / $this->conf['exchangeRate'][$to];

        if ($format) {
           $decimal = $to=='RON' ? 2 : 0;
           $price = number_format($price, $decimal, ',', '.');
        }

        return $price;
    }

    function getBlockContent()
    {

        $dbh = & SGL_DB::singleton();

        $sql = "SELECT * FROM {$this->conf['table']['product']} WHERE promotion >= '1' ";

        // get random number (max=number of messages)
        $res = & $dbh->query($sql);

        // if we have less products then NUMBER_OF_PRODUCTS
        $numRows = $res->numRows();

        if ($numRows == 0)
            return 'In aceasta perioada nu sunt promotii';

        $maxNum = ($numRows <= NUMBER_OF_PRODUCTS) ? $numRows : NUMBER_OF_PRODUCTS;
        $luckyNumbers = array(); // The extracted numbers array
        while(count($luckyNumbers) < $maxNum) {
            $rnd = rand(0,( $numRows - 1));
            if (!in_array($rnd, $luckyNumbers))
                $luckyNumbers[] = $rnd;
        }


        $aProduct = array();
        foreach($luckyNumbers as $value)
            $aProducts[] = $res->fetchRow(DB_FETCHMODE_OBJECT, $value);


        if (!isset($aProducts)) {
           //SGL::raiseError('perhaps no product tables exist', SGL_ERROR_NODATA);
           return 'In aceasta perioada nu sunt promotii';
        }

        if (DB::isError($aProducts)) {
            //SGL::raiseError('perhaps no product tables exist', SGL_ERROR_NODATA);
            return 'In aceasta perioada nu sunt promotii';
        }
        $HTMLoutput = "";
        if (is_array($aProducts) && count($aProducts)) {
            foreach ($aProducts as $key => $obj) {
              $obj->price = $this->currencyConverter($obj->price,$obj->currency,'EUR');
              $HTMLoutput .= $this->render_product($obj);
            }
            $HTMLoutput = '<link rel="stylesheet" type="text/css" href="'.SGL_BASE_URL.'/themes/default/shop/rndProducts.css" />'.$HTMLoutput;
        } else {
            $HMTLoutput = 'In aceasta perioada nu sunt promotii';
        }

       return $HTMLoutput;
    }

    function render_product($input)
    {


      $product_path = SGL_output :: makeUrl ('details','shop','shop');
      $product_path .= 'pid/'.$input->product_id.'/';

        if (isset($input->img) AND $input->img != '' && is_file(THUMBS_DIR.$input->img))
        {
            $thumb_path = SGL_BASE_URL.'/'.THUMBS_DIR.$input->img;
       $output = <<< HTML
       <TABLE>
        <TBODY>
            <TR>
            <TD valing="top" rowSpan="3" colspan="1"><a href="$product_path"><img src="$thumb_path" alt="$input->name" align="left" class="prod-image" /></a></TD>
            <TD></TD>
            </TR>
            <TR>
            <TD><a href="$product_path"><h6>$input->name</h6></a></TD>
            </TR>
            <TR>
            <TD><a href="$product_path"><h5>$input->price Euro</h5></a></TD>
            </TR>
        </TBODY>
        </TABLE>
HTML;
       } else {
              $output = '<br><a href="'.$product_path.'">'.$input->name.' - '.$input->price.'Euro</a><br>';
              }
       return $output;
    }
}
?>