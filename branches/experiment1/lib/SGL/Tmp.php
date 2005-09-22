<?php

/**
 * Abstract registry class.
 *
 * @abstract 
 */
class SGL_Registry
{   
    var $dirty = false;
    var $aProps;
    
    function set($key, $value) 
    {
        $this->dirty = true;
        $this->aProps[$key] = $value;
    }
    
    function get($key) 
    {
        return $this->aProps[$key];
    }
    
    function getAll()
    {
        return $this->aProps;
    }
    
    function getError() {}
    
    function setError() {}
}



class SGL_RequestRegistry extends SGL_Registry 
{
    
    function &singleton()
    {
        static $instance;
        if (!isset($instance)) {
            $class = __CLASS__;
            $instance = new $class();
        }
        return $instance;
    }
    
    function getRequest()
    {
        return $this->request;
    }
    
    function setRequest($req)
    {
        $this->request = $req;
    }
}

//$input = SGL_RequestRegistry::singleton();
//print '<pre>'; print_r($input);



class SGL_SessionRegistry extends SGL_Registry {} //    same as SGL_HTTP_Session

class SGL_ApplicationRegistry extends SGL_Registry 
{
    var $configFile = '';
    
    function &singleton($configFile)
    {
        static $instance;
        if (!isset($instance)) {
            $class = __CLASS__;
            $instance = new $class($configFile);
        }
        return $instance;
    }
    
    function SGL_ApplicationRegistry($configFile)
    {
        $this->configFile = $configFile;
        $this->init();
    }
    
    function init()
    {
        $params = SGL_ParamHandler::singleton($this->configFile);
        $this->aProps = $params->getAll();
    }
    
    function isEmpty()
    {
        return empty($this->aProps);
    }
    
    function save()
    {
        $params = SGL_ParamHandler::singleton($this->configFile);
        $ok = $params->write();
    }

}

//$GLOBALS['_SGL']['configPath'] = 'test.ini';
//$reg = &SGL_ApplicationRegistry::singleton($GLOBALS['_SGL']['configPath']);
//
//$conf = $reg->getAll();
//print '<pre>'; print_r($conf);
//$reg->set('foo', array());
//print '<pre>'; print_r($reg);

//  emulated destructor
register_shutdown_function('registryCleanup');
function registryCleanup()
{
    $appReg = &SGL_ApplicationRegistry::singleton($GLOBALS['_SGL']['configPath']);
    if ($appReg->dirty) {
        $appReg->save();   
    }
}

class SGL_Request
{
    var $aProps;
    
    function SGL_Request()
    {
        $this->init();
        SGL_RequestRegistry::setRequest($this);
    }
    
    function init()
    {
        
    }
    
    function set($key, $value) 
    {
        $this->aProps[$key] = $value;
    }
    
    function get($key) 
    {
        return $this->aProps[$key];
    }
}

class SGL_foo{}

class SGL_AppController
{
    var $context;
    
    function SGL_AppController()
    {
        $this->context = new SGL_RequestRegistry();   
    }
    
    function getContext()
    {
        return $this->context;   
    }
    
    function getView($req) 
    {
        return $req->getTemplate();
    }
    
    function getForward() {}
    
    function process()
    {
        //p. 248
        while ($cmd = $this->getCommand($this->context->get('action'))) { // pass $req
            $cmd->execute($this->context);
        }
        $this->diplay($this->getView($req));
    }
    
    function getCommand($req)
    {
        $cmd = $req->get('action');
        if (! $cmd) {
            return new DefaultMgr();   
        }
        
        $oCmd = $this->resolveCommand($cmd);
        return $oCmd;
    }  
    
    function resolveCommand()
    {
        
    }
}


class SGL_Command /* abstract */
{
    var $status;
    
    function execute($req)
    {
        $this->status = $this->_doExecute($req);
        $req->set('cmd', $this);
    }
    
    function getStatus()
    {
        return $this->status;   
    }
}

/*
fake user request
$controller = new SGL_AppController();
$context = $controller->getContext();
$context->addParam('action', 'login');
$context->addParam('username', 'joe');
$context->addParam('password', 'tiddles');
$controller->process();
*/

class SGL_ParamHandler
{
    var $source;
    var $aParams;
    
    function SGL_ParamHandler($source)
    {
        $this->source = $source;  
    }

    function &singleton($source)
    { 
        static $instances;
        if (!isset($instances)) $instances = array();
        
        $signature = md5($source);
        if (!isset($instances[$signature])) {
            
            $ext = substr($source, -3);
            switch ($ext) {
             
            case 'xml':
                return new SGL_ParamHandler_Xml($source);
                break;
            case 'ini':
            case 'php':
                return new SGL_ParamHandler_Ini($source);
                break;
            }
            $class = __CLASS__;
            $instance = new $class($source);
        }
        return $instance;
    }
    
    function read() {}
    function write() {}
    
    function addParam($key, $value)
    {
        $this->aParams[$key] = $value;    
    }
    
    function getAll()
    {
        if (empty($this->aParams)) {
            $this->read();   
        }
        return $this->aParams;   
    }
}

class SGL_ParamHandler_Ini extends SGL_ParamHandler
{
    function read() 
    {
        $this->aParams = parse_ini_file($this->source, true);
    }
    
    function write() 
    {
        //  get PEAR::Config to do the job
        print 'writing file ...';
    }
}

class SGL_ParamHandler_Array extends SGL_ParamHandler
{

}

class SGL_ParamHandler_Xml extends SGL_ParamHandler
{

}
/*
$params = SGL_ParamHandler::singleton('conf.ini');
$params->read();
*/


//  messaging should use strategy, ie, email, instant msg, sms etc
// $msgSystem = ReceiverFactory::getMessageSystem();

//  $this->conf = SGL_ApplicationRegistry::singleton();

//  change http_request and http_session to request + session

//  function invokeView(new TemplateStrategy())
