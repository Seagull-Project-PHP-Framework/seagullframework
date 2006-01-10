<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Shane Caraveo <Shane@Caraveo.com>                           |
// +----------------------------------------------------------------------+
//
// $Id: getExchangeRates.php,v 1.1 2005/05/12 10:10:29 demian Exp $
//
require_once 'SOAP/Client.php';
require_once 'DB.php';

/*
CREATE TABLE rate (
  id int(11) NOT NULL default '0',
  country varchar(32) NOT NULL default '',
  rate float(5,2) NOT NULL default '0.00',
  query_time datetime NOT NULL default '0000-00-00 00:00:00'
);
*/

/**
 * this client runs against the example server in SOAP/example/server.php
 * it does not use WSDL to run these requests, but that can be changed easily by simply
 * adding '?wsdl' to the end of the url.
 */

//  a free web service for currency exchange rates
//  http://www.xmethods.com/ve2/ViewListing.po%3Bjsessionid%3DBgbEY7AOqfXKPATnAxqbS-T0%28QCcd0CRM%29?serviceid=5

$wsdl = new SOAP_WSDL("http://www.xmethods.net/sd/2001/CurrencyExchangeService.wsdl?wsdl");
$soapclient = $wsdl->getProxy();

$ret = $soapclient->getRate('england', 'usa');
if (PEAR::isError($ret)) {
    PEAR::raiseError('there was a retrieving the exchange rate');
}

$dbh = DB::connect('mysql://root@localhost/currency');
$query = "INSERT INTO rate VALUES ('".$dbh->nextId('rate')."', 'USA', '$ret', '".gmstrftime("%Y-%m-%d %H:%M:%S", time()) ."')";
$res = $dbh->query($query);

if (PEAR::isError($ret)) {
    print $ret->getMessage();
}

?>