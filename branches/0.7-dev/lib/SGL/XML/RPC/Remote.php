<?php
require_once 'PEAR.php';

/**
 * This is a class for doing remote operations against the central
 * PEAR database.
 *
 * @nodep XML_RPC_Value
 * @nodep XML_RPC_Message
 * @nodep XML_RPC_Client
 */
class SGL_XML_RPC_Remote
{
    var $conf = array();

    function SGL_XML_RPC_Remote($config)
    {
        $this->conf = parse_ini_file($config);
    }


    function call($method)
    {
        $args = func_get_args();
        $server_host = $this->conf['server_host'];

        if (!@require_once 'XML/RPC.php') {
            return PEAR::raiseError("For this remote PEAR operation you need to install the XML_RPC package");
        }
        //  remove method name
        array_shift($args);
        $username = $this->conf['username'];
        $password = $this->conf['password'];
        $eargs = array();
        foreach ($args as $arg) {
            $eargs[] = $this->_encode($arg);
        }
        $f = new XML_RPC_Message($method, $eargs);
        $shost = $server_host;
        if ($this->conf['secure']) {
            $shost = "https://$shost";
        }

        $server_port = $this->conf['server_port'];
        $proxy_host = $this->conf['server_host'];
        $proxy_port = $this->conf['server_port'];
        $proxy_user = @$this->conf['server_user'];
        $proxy_pass = @$this->conf['server_pass'];

        $file = $this->conf['xmlrpc_server_file'];
        $c = new XML_RPC_Client($file, $shost, $server_port, $proxy_host, $proxy_port,
            $proxy_user, $proxy_pass);
        if ($username && $password) {
            $c->setCredentials($username, $password);
        }
        if ($this->conf['verbose'] >= 3) {
            $c->setDebug(1);
        }
        $r = $c->send($f);
        if (!$r) {
            return PEAR::raiseError("XML_RPC send failed");
        }
        $v = $r->value();
        if ($e = $r->faultCode()) {
            return PEAR::raiseError($r->faultString(), $e);
        }

        $result = XML_RPC_decode($v);
        return $result;
    }

    // a slightly extended version of XML_RPC_encode
    function _encode($php_val)
    {
        global $XML_RPC_Boolean, $XML_RPC_Int, $XML_RPC_Double;
        global $XML_RPC_String, $XML_RPC_Array, $XML_RPC_Struct;

        $type = gettype($php_val);
        $xmlrpcval = new XML_RPC_Value();

        switch($type) {
            case "array":
                reset($php_val);
                $firstkey = key($php_val);
                end($php_val);
                $lastkey = key($php_val);
                reset($php_val);
                if ($firstkey === 0 && is_int($lastkey) &&
                    ($lastkey + 1) == count($php_val)) {
                    $is_continuous = true;
                    reset($php_val);
                    $size = count($php_val);
                    for ($expect = 0; $expect < $size; $expect++, next($php_val)) {
                        if (key($php_val) !== $expect) {
                            $is_continuous = false;
                            break;
                        }
                    }
                    if ($is_continuous) {
                        reset($php_val);
                        $arr = array();
                        while (list($k, $v) = each($php_val)) {
                            $arr[$k] = $this->_encode($v);
                        }
                        $xmlrpcval->addArray($arr);
                        break;
                    }
                }
                // fall though if not numerical and continuous
            case "object":
                $arr = array();
                while (list($k, $v) = each($php_val)) {
                    $arr[$k] = $this->_encode($v);
                }
                $xmlrpcval->addStruct($arr);
                break;
            case "integer":
                $xmlrpcval->addScalar($php_val, $XML_RPC_Int);
                break;
            case "double":
                $xmlrpcval->addScalar($php_val, $XML_RPC_Double);
                break;
            case "string":
            case "NULL":
                $xmlrpcval->addScalar($php_val, $XML_RPC_String);
                break;
            case "boolean":
                $xmlrpcval->addScalar($php_val, $XML_RPC_Boolean);
                break;
            case "unknown type":
            default:
                return null;
        }
        return $xmlrpcval;
    }
    // }}}
}

?>