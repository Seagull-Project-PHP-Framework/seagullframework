<?php
/*
* Format: confMap['pattern'] = conf_file;
*
* Where: pattern   - regular expression
*        conf_file - site configuration file name
*
* Examples:
* $confMap['127.0.0.1']    = 'localhost.conf.php'; // alternative name of localhost
* $confMap['.*.localhost'] = 'localhost.conf.php'; // subsites conf
* $confMap['.*']           = 'default.conf.php';   // default conf for all sites
*/

$confMap['.*']           = 'default.conf.php';   // default conf for all sites
return $confMap;

?>
