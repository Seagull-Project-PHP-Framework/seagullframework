<?php

class SGL2_Request_Cli
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
            throw new Exception("CLI parameters invalid");
        }
        $aProps = array();

        /* Take all _valid_ parameters and add them into aProps. */
        while (list($parameter, $value) = each($options[0])) {
            $value[0] = str_replace('--', '', $value[0]);
            $aProps[$value[0]] = $value[1];
        }
        return $aProps;
    }
}
?>