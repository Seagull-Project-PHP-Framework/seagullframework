<?php
class RecordLogin extends SGL_Observer
{
    function update($observable)
    {
        // Get the user id from the current session
        $uid = SGL_Session::getUid();
        //  record login in db for security
        if (@$observable->conf['LoginMgr']['recordLogin']) {

            require_once 'DB/DataObject.php';
            $login = DB_DataObject::factory($observable->conf['table']['login']);
            $login->login_id = $observable->dbh->nextId($observable->conf['table']['login']);
            $login->usr_id = $uid;
            $login->date_time = SGL_Date::getTime(true);
            $login->remote_ip = $_SERVER['REMOTE_ADDR'];
            $ok = $login->insert();
        }
    }
}
?>