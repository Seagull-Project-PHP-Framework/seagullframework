<?php
//    initialise
    require_once '../init.php';
    require_once SGL_CORE_DIR . '/Controller.php';
    $process = & new SGL_Controller();
    $process->go();

//  don't not modify above, needed to init page
//  selectively uncomment various error types whilst in debug
//  mode to see output

//  test warning error
$fp = 'not_a_file_handle';
$row = fgets($fp, 1024);

//  test notice error
$var[ttest] = 'hello';

//  test user forced error
#trigger_error('trigger msg test', E_USER_NOTICE);

//  test normal raiseError
#SGL::raiseError('test PEAR erro msg', SGL_ERROR_INVALID_CALL);

//  test SGL fatal error
#SGL::raiseError('test PEAR erro msg', SGL_ERROR_INVALID_CALL, PEAR_ERROR_DIE);

//  test PEAR fatal error
#PEAR::raiseError('test PEAR fata error', SGL_ERROR_INVALID_CALL, PEAR_ERROR_DIE);

//  test db/PEAR error
#$dbh = & SGL_DB::singleton();
#$query = "SELECT  u.non_existent_field FROM users u";
#$result = $dbh->query($query);

//  test if program execution halted
#print "hello<BR>";
?>