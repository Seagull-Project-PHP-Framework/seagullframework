#!/bin/bash
#script for reloading a default seagull install

#variables
MYSQLADMIN=/usr/local/mysql/bin/mysqladmin
WGET=/usr/sbin/wget
SEAGULL_PATH=/var/www/html/seagulldemo
SEAGULL_REQUEST="http://seagulldemo.phpkitchen.com/index.php?name=seagull&type=0&host=localhost&protocol=0&user=root&pass=&setupType%5BcreateSchema%5D=1&setupType%5BsetConnectionDetails%5D=1&btnSubmit=Execute+%28pls+be+patient+if+schema+creation+selected%29"

#drop database
$MYSQLADMIN -f drop seagull

#create new db
$MYSQLADMIN create seagull

#remove contents of var dir
rm -rf $SEAGULL_PATH/var/*

#fire off wizard
$WGET --delete-after $SEAGULL_REQUEST
