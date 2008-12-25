<?php

$dbname = 'seagull';
$tablename = 'usr';

//  TEST 1: using mysql_num_fields()
$conn = mysql_connect('localhost', 'root', '');
$ret = mysql_list_fields($dbname, $tablename, $conn);
$count = @mysql_num_fields($ret);
for ($i = 0; $i < $count; $i++) {
    $res[$i] = array(
        'table' => mysql_field_table($ret, $i),
        'name'  => mysql_field_name($ret, $i),
        'type'  => mysql_field_type($ret, $i),
        'len'   => mysql_field_len($ret, $i),
        'flags' => mysql_field_flags($ret, $i),
    );
}
print '<pre>';print_r($res);exit;
//  output for username field, mysql 4.1
//    [3] => Array
//        (
//            [table] => usr
//            [name] => username
//            [type] => string
//            [len] => 64
//            [flags] => multiple_key
//        )

//  output for username field, mysql 5.0.18-nt
//    [3] => Array
//        (
//            [table] => usr
//            [name] => username
//            [type] => string
//            [len] => 64
//            [flags] => unique_key
//        )


//  TEST 2: using SHOW COLUMNS
$conn = mysql_connect('localhost', 'root', '');
mysql_select_db('seagull');
$result = mysql_query("SHOW COLUMNS FROM usr", $conn);
if (!$result) {
    echo 'Could not run query: ' . mysql_error();
    exit;
}
if (mysql_num_rows($result) > 0) {
    while ($row = mysql_fetch_assoc($result)) {
        print_r($row);
    }
}
//  output for username field, mysql 4.1
//Array
//(
//    [Field] => username
//    [Type] => varchar(64)
//    [Null] => YES
//    [Key] => MUL
//    [Default] =>
//    [Extra] =>
//)

//  output for username field, mysql 5.0.18-nt
//Array
//(
//    [Field] => username
//    [Type] => varchar(64)
//    [Null] => YES
//    [Key] => UNI
//    [Default] =>
//    [Extra] =>
//)
