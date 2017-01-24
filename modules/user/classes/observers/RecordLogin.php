<?php

require_once SGL_CORE_DIR . '/Observer.php';

/**
 * Records login in database.
 *
 * @package seagull
 * @subpackage user
 * @author Demian Turner <demian@phpkitchen.com>
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class RecordLogin extends SGL_Observer
{
    /**
     * Observer's routine.
     *
     * @access public
     *
     * @param SGL_Observable $observable
     */
    function update($observable)
    {
        // record login in db for security
        if (RecordLogin::loginRecordingAllowed()) {
            RecordLogin::insert($observable->dbh);
        }
    }

    /**
     * Checks if it is allowed to record login.
     *
     * @static
     *
     * @access public
     *
     * @return boolean
     */
    function loginRecordingAllowed()
    {
        $c = &SGL_Config::singleton();
        $conf = $c->load(SGL_MOD_DIR . '/user/conf.ini');
        return (boolean) $conf['LoginMgr']['recordLogin'];
    }

    /**
     * Insert record into database.
     *
     * @static
     *
     * @access public
     *
     * @param DB_Common $dbh
     * @param integer $userId
     *
     * @return boolean
     */
    function insert(&$dbh, $userId = null)
    {
        require_once 'DB/DataObject.php';
        $tableName        = SGL_Config::get('table.login');
        $login            = DB_DataObject::factory($tableName);
        $login->login_id  = $dbh->nextId($tableName);
        $login->usr_id    = $userId ? $userId : SGL_Session::getUid();
        $login->date_time = SGL_Date::getTime(true);
        $login->remote_ip = $_SERVER['REMOTE_ADDR'];
        return $login->insert();
    }
}
?>