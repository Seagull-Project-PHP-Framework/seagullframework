<?php
//  init
require_once dirname(__FILE__)  . '/../FrontController.php';
SGL_FrontController::init();

require_once SGL_LIB_DIR . '/SGL/XML/RPC/Remote.php';

//  get cmd param from GET
if (isset($_GET['cmd'])) {

    $config = './xmlrpc_conf.ini';
    $remote = new SGL_XML_RPC_Remote($config);

    switch ($_GET['cmd']) {

    case 'determineLatestVersion':
        $res = $remote->call('framework.determineLatestVersion');
        break;

    case 'getAllModules':
        $res = $remote->call('framework.getAllModules');
        break;

    case 'listMethods':
        $res = $remote->call('system.listMethods');
        break;

    case 'methodHelp':
        $res = $remote->call('system.methodHelp', 'framework.determineLatestVersion');
        break;

    case 'methodSignature':
        $res = $remote->call('system.methodSignature', 'framework.determineLatestVersion');
        break;
    }

    if (PEAR::isError($res)) {
        echo '<pre>'; print_r($res).'\n';
    }
    print '<pre>'; print_r($res);
}


?>
<html>
<body>
<p><a href="<?php echo $_SERVER['PHP_SELF'] ?>">reset</a></p>
<p><a href="?cmd=determineLatestVersion">determineLatestVersion</a></p>
<p><a href="?cmd=getAllModules">getAllModules</a></p>
<p><a href="?cmd=listMethods">listMethods</a></p>
<p><a href="?cmd=methodHelp">methodHelp</a></p>
<p><a href="?cmd=methodSignature">methodSignature</a></p>
</body>
</html>
