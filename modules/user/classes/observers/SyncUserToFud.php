<?php
class SyncUserToFud extends SGL_Observer
{
    function update($observable)
    {
        if (!isset($observable->input->user->username)) {
            $observable->input->user->username = $observable->oUser->username;
        }
        $dsn1 = 'mysql://root@localhost/seagull_live';
        $dbh1 = & SGL_DB::singleton($dsn1);
        //  get max ID
        $userId = $dbh1->getOne("SELECT MAX(usr_id) FROM usr");

        //  insert into FUD
        $dsn2 = 'mysql://root@localhost/fud';
        $dbh2 = & SGL_DB::singleton($dsn2);
        $username = $dbh2->quoteSmart($observable->input->user->username);
        $query = "
            INSERT INTO `fud26_users` (`id`, `login`, `alias`, `theme`, `users_opt`)
            VALUES ($userId, $username, $username, 1, 4357110)";
        $ok = $dbh2->query($query);
        return $ok;
    }
}
?>