<?php
class SGL_UrlParserClassicStrategy extends SGL_UrlParserStrategy
{
    /**
    * Resolves //, ../ and ./ from a path and returns
    * the result. Eg:
    *
    * /foo/bar/../boo.php    => /foo/boo.php
    * /foo/bar/../../boo.php => /boo.php
    * /foo/bar/.././/boo.php => /foo/boo.php
    *
    * This method can also be called statically.
    *
    * @param  string $url URL path to resolve
    * @return string      The result
    */
    function resolvePath($path)
    {
        $path = explode('/', str_replace('//', '/', $path));

        for ($i=0; $i<count($path); $i++) {
            if ($path[$i] == '.') {
                unset($path[$i]);
                $path = array_values($path);
                $i--;

            } elseif ($path[$i] == '..' AND ($i > 1 OR ($i == 1 AND $path[0] != '') ) ) {
                unset($path[$i]);
                unset($path[$i-1]);
                $path = array_values($path);
                $i -= 2;

            } elseif ($path[$i] == '..' AND $i == 1 AND $path[0] == '') {
                unset($path[$i]);
                $path = array_values($path);
                $i--;

            } else {
                continue;
            }
        }

        return implode('/', $path);
    }
    
    /**
    * Adds a querystring item
    *
    * @param  string $name       Name of item
    * @param  string $value      Value of item
    * @param  bool   $preencoded Whether value is urlencoded or not, default = not
    * @access public
    */
    function addQueryString($name, $value, $preencoded = false)
    {
        if ($preencoded) {
            $this->querystring[$name] = $value;
        } else {
            $this->querystring[$name] = is_array($value) ? array_map('rawurlencode', $value): rawurlencode($value);
        }
    }

    /**
    * Removes a querystring item
    *
    * @param  string $name Name of item
    * @access public
    */
    function removeQueryString($name)
    {
        if (isset($this->querystring[$name])) {
            unset($this->querystring[$name]);
        }
    }

    /**
    * Sets the querystring to literally what you supply
    *
    * @param  string $querystring The querystring data. Should be of the format foo=bar&x=y etc
    * @access public
    */
    function addRawQueryString($querystring)
    {
        $this->querystring = $this->_parseRawQueryString($querystring);
    }

    /**
    * Returns flat querystring
    *
    * @return string Querystring
    * @access public
    */
    function getQueryString()
    {
        if (!empty($this->querystring)) {
            foreach ($this->querystring as $name => $value) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $querystring[] = $this->useBrackets ? sprintf('%s[%s]=%s', $name, $k, $v) : ($name . '=' . $v);
                    }
                } elseif (!is_null($value)) {
                    $querystring[] = $name . '=' . $value;
                } else {
                    $querystring[] = $name;
                }
            }
            $querystring = implode(ini_get('arg_separator.output'), $querystring);
        } else {
            $querystring = '';
        }

        return $querystring;
    }

    /**
    * Parses raw querystring and returns an array of it
    *
    * @param  string  $querystring The querystring to parse
    * @return array                An array of the querystring data
    * @access private
    */
    function parseQuerystring($querystring)
    {
        $parts  = preg_split('/[' . preg_quote(ini_get('arg_separator.input'), '/') . ']/', $querystring, -1, PREG_SPLIT_NO_EMPTY);
        $return = array();

        foreach ($parts as $part) {
            if (strpos($part, '=') !== false) {
                $value = substr($part, strpos($part, '=') + 1);
                $key   = substr($part, 0, strpos($part, '='));
            } else {
                $value = null;
                $key   = $part;
            }
            if (substr($key, -2) == '[]') {
                $key = substr($key, 0, -2);
                if (@!is_array($return[$key])) {
                    $return[$key]   = array();
                    $return[$key][] = $value;
                } else {
                    $return[$key][] = $value;
                }
            } elseif (!$this->useBrackets && !empty($return[$key])) {
                $return[$key]   = (array)$return[$key];
                $return[$key][] = $value;
            } else {
                $return[$key] = $value;
            }
        }

        return $return;
    }
}
/*
improved URL class for 
- cleaner implementation in constants.php
- works with both tradition and FC querystrings

usage :

//  sort out compat issues
SGL_Url::resolveServerVars();

//  determine current request
$url = new SGL_Url($_SERVER['PHP_SELF']);  // set frontScriptName in const.

$host = $url->getHostName();

//  eg, handles situation where your URL is http://localhost/seagull/trunk/www/index.php
//  ie, hostName = localhost; path = /seagull/trunk/www/
$conf['site']['baseUrl'] = $url->getHostName() . $url->getPath();

- save $url to request registry for later use

- getSignificantSegments() becomes getQueryString()
$string = $url->getQueryString();
$dataStructure = $url->getQueryData();

SGL_Url
(
    [scheme] => https
    [host] => example.com
    [path] => /pls/portal30/PORTAL30.wwpob_page.changetabs/index.php
    [frontScriptName] => index.php
    [raw_query] => p_back_url=http%3A%2F%2Fexample.com%2Fservlet%2Fpage%3F_pageid%3D360%2C366%2C368%2C382%26_dad%3Dportal30%26_schema%3DPORTAL30&foo=bar
    [query] => Array
                (
                    [foo] => bar
                    [baz] => quux
                )
)


//  building SGL URLs
    $url = new SGL_Url();
    
    $url->setModule('publisher');
    $url->setManager('articleview');
    $url->setAction('list');
    $url->addQueryString('frmArticleId', 23);
    $output = $url->toString(SGL_URL_ABS);
    
//  for Flexy output:
makeLink(#self/publisher/articleview/action/view/frmArticleID/item_id#, aPagedData[data])

//  https
makeLink(#self/publisher/articleview/action/view/frmArticleID/item_id#,aPagedData[data],#https#)
-------------------------------------------------^^^^^^^^^^^^^ var name
--------------------------------------------------------------^^^^^^^^ obj prop/array key
-----------------------------------------------------------------------^^^^^^^^^^^^^^^^ collection
----------------------------------------------------------------------------------------^^^^^^^ is https or not

//  working with SGL_Url type, switching FC/traditional implementation at runtime
$url = new SGL_Url($url, $useBrackets = true, new SefUrlStrategy()); // as in Search Engine Friendly

*/
?>