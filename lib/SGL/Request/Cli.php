<?php

class SGL_Request_Cli extends SGL_Request
{
    function init()
    {
        require_once 'Console/Getopt.php';
        $shortOptions = '';
        $longOptions = array('moduleName=', 'managerName=', 'action=');

        $console = new Console_Getopt();
        $arguments = $console->readPHPArgv();
        array_shift($arguments);

        // catch arbitrary arguments
        for ($i = 3; $i < count($arguments); $i++) {
            array_push($longOptions, substr($arguments[$i], 2, strpos($arguments[$i], "=") - 1));
        }
        $options = $console->getopt2($arguments, $shortOptions, $longOptions);

        if (!is_array($options) ) {
            die("CLI parameters invalid\n");
        }

        $this->aProps = array();

        /* Take all _valid_ parameters and add them into aProps. */
        while (list($parameter, $value) = each($options[0])) {
            $value[0] = str_replace('--', '', $value[0]);
            $this->aProps[$value[0]] = $value[1];
        }
        $this->type = SGL_REQUEST_CLI;
        return true;
    }
}
?>