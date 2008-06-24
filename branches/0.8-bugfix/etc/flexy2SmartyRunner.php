#!/usr/bin/php
<?php
if (!@$_SERVER['argv'][1]) {
    die("Usage: php conv.php inputfile\n");
}
if(!$data = @file_get_contents($_SERVER['argv'][1])) {
    die("file doesn't exist\n");
}

require_once 'Flexy2Smarty.php';
$c = new Flexy2Smarty;
echo $c->convert(file_get_contents($_SERVER['argv'][1]));
?>
