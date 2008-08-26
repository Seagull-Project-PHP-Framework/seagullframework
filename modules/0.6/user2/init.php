<?php

class SGL_Task_LoadUser2Data extends SGL_Task
{
    public function run($data)
    {
        // prepare cmd
        $conf   = SGL_Config::singleton()->getAll();
        $passwd = !empty($conf['db']['pass'])
            ? "-p{$conf['db']['pass']}"
            : '';

        $file = SGL_MOD_DIR . '/user2/data/routines.my.sql';
        $cmd  = "mysql -u{$conf['db']['user']} $passwd {$conf['db']['name']} < $file";

        SGL::logMessage($cmd, PEAR_LOG_DEBUG);
        $ok = `$cmd`;

        $file = SGL_MOD_DIR . '/user2/data/other.my.sql';
        $cmd  = "mysql -u{$conf['db']['user']} $passwd {$conf['db']['name']} < $file";

        SGL::logMessage($cmd, PEAR_LOG_DEBUG);
        $ok = `$cmd`;
    }
}
?>