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
        //  get max ID and email
        $userId = $dbh1->getOne("SELECT MAX(usr_id) FROM usr");
        $email = $dbh1->getOne("SELECT email FROM usr WHERE usr_id = $userId");

        //  insert into FUD
        $dsn2 = 'mysql://root@localhost/fud';
        $dbh2 = & SGL_DB::singleton($dsn2);
        $username = $dbh2->quoteSmart($observable->input->user->username);
        $email = $dbh2->quoteSmart($email);
        $query = "
            INSERT INTO `fud26_users` (`id`, `login`, `alias`, `email`, `theme`, `users_opt`)
            VALUES ($userId, $username, $username, $email, 1, 4357110)";
        $ok = $dbh2->query($query);
        return $ok;
    }
}
?>