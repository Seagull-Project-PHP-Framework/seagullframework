<?
$this->conf['ShopMgr']['rootCatID'] = 4;
$this->conf['ShopMgr']['requiresAuth'] = 0;
$this->conf['ShopMgr']['showCart'] = 1;
$this->conf['ShopMgr']['defaultVAT'] = 1.18;
$this->conf['ShopMgr']['defaultDiscount'] = 0;
$this->conf['ShopMgr']['multiCurrency'] = 0;
$this->conf['ShopMgr']['defaultExchange'] = 1;
$this->conf['ShopMgr']['defaultCurrency'] = "LIT";

$this->conf['ShopAdminMgr']['requiresAuth'] = 1;

$this->conf['statusOpts']['1'] = "Phone Order";
$this->conf['statusOpts']['2'] = "Short Supply";
$this->conf['statusOpts']['3'] = "In Stock";

$this->conf['imageUpload']['maxSize'] = 500000;
$this->conf['imageUpload']['imageWidth'] = 250;
$this->conf['imageUpload']['imageHeight'] = 250;
$this->conf['imageUpload']['thumbWidth'] = 75;
$this->conf['imageUpload']['thumbHeight'] = 75;
$this->conf['imageUpload']['imageDriver'] = "GD";
$this->conf['imageUpload']['magnify'] = 1;
$this->conf['imageUpload']['directory'] = "images/shop";
$this->conf['imageUpload']['thumb'] = "images/shop/thumb";
$this->conf['imageUpload']['noImageFile'] = "no_image.jpg";

$this->conf['PriceMgr']['requiresAuth'] = 0;

$this->conf['PriceAdminMgr']['requiresAuth'] = 1;

$this->conf['price']['discountPrefId'] = 10;
$this->conf['price']['roleId'] = 2;
$this->conf['price']['VAT'] = 1.18;
$this->conf['price']['requiresAuth'] = 1;

$this->conf['CSV']['requiresAuth'] = 1;
$this->conf['CSV']['maxUploadRec'] = 600;

$this->conf['UploadMgr']['requiresAuth'] = 0;

$this->conf['shopConfigMgr']['requiresAuth'] = 1;

$this->conf['UploadMgr']['requiresAuth'] = 1;

?>

