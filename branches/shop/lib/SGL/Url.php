<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | o Redistributions of source code must retain the above copyright          |
// |   notice, this list of conditions and the following disclaimer.           |
// | o Redistributions in binary form must reproduce the above copyright       |
// |   notice, this list of conditions and the following disclaimer in the     |
// |   documentation and/or other materials provided with the distribution.    |
// | o The names of the authors may not be used to endorse or promote          |
// |   products derived from this software without specific prior written      |
// |   permission.                                                             |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT         |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR     |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT      |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,     |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT          |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,     |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE     |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.      |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | Url.php                                                                   |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Url.php,v 1.32 2005/05/29 21:32:17 demian Exp $

/**
 * Url related functionality.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.32 $
 * @since   PHP 4.1
 * @see seagull/lib/SGL/tests/UrlTest.ndb.php
 */
class SGL_Url
{
    /**
     * Converts querystring into/se/friendly/format.
     *
     * Returns an array of all elements after the front script name
     * 
     * @access  public
     * @param   $url    Url to be parsed
     * @return  array   $aUriParts  An array of all significant parts of the URL, ie
     *                              from the front controller script name onwards
     */
    function getSignificantSegments($url)
    {
        $conf = & $GLOBALS['_SGL']['CONF'];

        //  split elements (remove eventual leading/trailing slashes)
        $aUriParts = explode('/', trim($url, '/'));

        //  step through array and strip until fc element is reached
        foreach ($aUriParts as $elem) {
            if ($elem != $conf['site']['frontScriptName']) {
                array_shift($aUriParts);
            } else {
                break;
            }
        }
        return $aUriParts;
    }

    /**
     * Returns true if manager name is the same of module name, ie, index.php/faq/faq/.
     *
     * @param string $url
     * @return boolean
     */
    function containsDuplicates($url)
    {
        if (!empty($url)) {
            $aPieces = explode('/', $url);
            $initial = count($aPieces);
            $unique = count(array_unique($aPieces));
            $ret = $initial != $unique;
        } else {
            $ret = false;
        }
        return $ret;
    }

    /**
    * Returns true if URL has been abbreviated
    *
    * This happens when a manager name is the same as its module name, ie
    * UserManger in the 'user' module would become user/user which gets
    * reduced to user
    *
    * @param string $url            From the querystring
    * @param string $sectionName    From the database
    * @return boolean
    */
    function isSimplified($url, $sectionName)
    {
        if (!(empty($url))) {
            $aUrlPieces = explode('/', $url);
            $moduleNameUrl = $aUrlPieces[0];
            $aSections =  explode('/', $sectionName);
            $ret = in_array($moduleNameUrl, $aSections) && (SGL_Url::containsDuplicates($sectionName));
        } else {
            $ret = false;
        }
        return $ret;
    }
    
    /**
     * Returns the full Manager name given the short name, ie, faq becomes FaqMgr.
     *
     * @param string $name
     * @return string
     */
    function getManagerNameFromSimplifiedName($name)
    {
        //  if Mgr suffix has been left out, append it
        if (strtolower(substr($name, -3)) != 'mgr') {
            $name .= 'Mgr';
        }
        return ucfirst($name);
    }
    
    /**
     * Returns the short name given the full Manager name, ie FaqMgr becomes faq.
     *
     * @param unknown_type $name
     * @return unknown
     */
    function getSimplifiedNameFromManagerName($name)
    {
        //  strip file extension if exists
        if (substr($name, -4) == '.php') {
            $name = substr($name, 0, -4);
        }
        
        //  strip 'Mgr' if exists
        if (strtolower(substr($name, -3)) == 'mgr') {
            $name = substr($name, 0, -3);
        }
        return strtolower($name);      
    }    

    /**
     * Ensures URL is fully qualified.
     *
     * @access  public
     * @param   string  $url    The relative URL string
     * @return  void
     */
    function toAbsolute(&$url)
    {
        $aUrl = parse_url($url);
        if (!(isset($aUrl['scheme']))) {
            $url = SGL_BASE_URL . '/' . $url;
        }
    }
    
    /**
     * Parse string stored in resource_uri field in section table.
     *
     * This will always contain URL elements after the frontScriptName (index.php), never
     * a FQDN, and never simplified names, ie section table must specify module name and 
     * manager name explicitly, even if they are the same, ie user/user
     *
     * @param string $str
     * @return array  A hash containing URL info
     */
    function parseResourceUri($str)
    {
        $ret = array();
        $default = array(
            'module' => 'default', 
            'manager' => 'default');        
            
        //  catch case for default page, ie, home
        if (empty($str)) {
            return $default;
        }
        $parts = array_filter(explode('/', $str), 'strlen');
        $numElems = count($parts);
        
        //  we need at least 2 elements
        if ($numElems < 2) {
            return $default;
        }
        $ret['module'] = $parts[0];
        $ret['manager'] = $parts[1];
        $actionExists = (isset($parts[2]) && $parts[2] == 'action') ? true : false;
        $ret['actionMapping'] = ($actionExists) ? $parts[3] : null;

        //  parse params
        $idx = ($actionExists) ? 4 : 2;
        
        //  break out if no params detected
        if ($numElems <= $idx) {
            return $ret;
        }        
        
        $aTmp = array();
        for ($x = $idx; $x < $numElems; $x++) {
            if ($x % 2) { // if index is odd
                $aTmp['varValue'] = urldecode($parts[$x]);
            } else {
                // parsing the parameters
                $aTmp['varName'] = urldecode($parts[$x]);
            }
            //  if a name/value pair exists, add it to request
            if (count($aTmp) == 2) {
                $ret['parsed_params'][$aTmp['varName']] = $aTmp['varValue'];
                $aTmp = array();                
            }
        }
        return $ret;               
    }
    

    /**
     * Converts querystring into/se/friendly/format.
     *
     * @access  public
     * @return  void
     * @todo    this data structure should be more similar to the one parsed in 
     *              SGL_Url::parseResourceUri()
     * @todo    use same method for SGL_Url::parseResourceUri()
     * @todo    implement file-based caching or url combinations, simple hashmap
     */
    function makeSearchEngineFriendly($aUriParts)
    {
        $conf = & $GLOBALS['_SGL']['CONF'];

        //  remap
        $aParsedUri['frontScriptName'] = array_shift($aUriParts);
        $aParsedUri['moduleName'] = strtolower(array_shift($aUriParts));
        $aParsedUri['managerName'] = strtolower(array_shift($aUriParts));

        //  if frontScriptName empty, get from config
        $default = false;
        if (empty($aParsedUri['frontScriptName'])
                || $aParsedUri['frontScriptName'] != $conf['site']['frontScriptName']) {
            $aParsedUri['frontScriptName'] = $conf['site']['frontScriptName'];
        }

        //  if no module name present, get from config
        //  catch case where debugging with Zend supplies querystring params
        if (empty(  $aParsedUri['moduleName'])
                || (preg_match('/start_debug/', $aParsedUri['moduleName']))) {
            $aParsedUri['moduleName'] = $conf['site']['defaultModule'];
            $default = true;
        }

        //  if no manager name, must be default manager, ie, has same name as module
        //  the exception is when the moduleName comes from the conf
        if ((empty( $aParsedUri['managerName']) && !$default)
                || (preg_match('/start_debug/', $aParsedUri['managerName']))) {
            $aParsedUri['managerName'] = $aParsedUri['moduleName'];

        //  we are here because we're using defaults from config
        } elseif ($default) {
            $aParsedUri['managerName'] = $conf['site']['defaultManager'];
            if (!empty($conf['site']['defaultParams'])) {
                $aParsedUri['defaultParams'] = $conf['site']['defaultParams'];
            }
        }
        
        //  catch case where when manger + mod names are the same, and cookies
        //  disabled, sglsessid gets bumped into wrong slot
        if (preg_match('/'.strtolower($conf['cookie']['name']).'/', $aParsedUri['managerName'])) {
            list(,$cookieValue) = split('=', $aParsedUri['managerName']);
            $cookieValue = substr($cookieValue, 0, -1);
            $aParsedUri['managerName'] = $aParsedUri['moduleName'];
            array_unshift($aUriParts, $cookieValue);
            array_unshift($aUriParts, $conf['cookie']['name']);
        }

        //  if 'action' is in manager slot, move it to querystring array, and replace 
        //  manager name with default mgr name, ie, that of the module
        if ($aParsedUri['managerName'] == 'action') {
            $aParsedUri['managerName'] = $aParsedUri['moduleName'];
            array_unshift($aUriParts, 'action');
        }

        //  if default params exist, append them to the uri array
        if (!empty($aParsedUri['defaultParams'])) {
            $aUriParts = array_merge($aUriParts, explode('/', $aParsedUri['defaultParams']));
        }

        $numParts = count($aUriParts);

        //  if varName/varValue don't match, assign a null varValue to the last varName
        if ($numParts % 2) {
            array_push($aUriParts, null);
            ++ $numParts;
        }

        //  parse FC querystring params
        $aQsParams = array();
        
        for ($i = 0; $i < $numParts; $i += 2) {
            $varName  = urldecode($aUriParts[$i]);
            $varValue = urldecode($aUriParts[$i+1]);

            //  check if the variable is an array
            if ((strpos($varName, '[') !== false) &&
                (strpos($varName, ']') !== false))
            {
                //  retrieve the array name ($matches[1]) and its eventual key ($matches[2])
                preg_match('/([^\[]*)\[([^\]]*)\]/', $varName, $matches);
                if (!array_key_exists($matches[1], $GLOBALS['_SGL']['REQUEST'])) {
                    $aQsParams[$matches[1]] = array();
                }
                //  no key given => append to array                
                if (empty($matches[2])) {
                    array_push($aQsParams[$matches[1]], $varValue);
                } else {
                    $aQsParams[$matches[1]][$matches[2]] = $varValue;
                }
            } else {
                $aQsParams[$varName] = $varValue;
            }
        }
        //  merge the default request fields with extracted param k/v pairs
        return array_merge($aParsedUri, $aQsParams);
    }

    /**
     * Best way I've come up with so far for passing all params required by Flexy to build a URL.
     *
     * @param string $action
     * @param string $mgr
     * @param string $mod
     * @param array $aList
     * @param string $params
     * @param integer $idx
     * @param object $output
     * @return string
     */
    function makeLink($action = '', $mgr = '', $mod = '', $aList = array(), 
        $params = '', $idx = 0, $output = '')
    {
        $conf = & $GLOBALS['_SGL']['CONF'];

        //  get a reference to the request object
        $req = & SGL_HTTP_Request::singleton();

        //  determine module and manager names
        $mgr = (empty($mgr)) ? $req->get('managerName') : $mgr;
        $mod = (empty($mod)) ? $req->get('moduleName'): $mod;
        $url = $conf['site']['frontScriptName'] . '/';

        //  allow for default managers, ie, in faqMgr, don't
        //  return http://localhost.localdomain/seagull/www/index.php/faq/faq/action/edit/
        if ($mgr != $mod) {
            $url .= $mod . '/';
        }
        $url .= $mgr;

        //  only add action param if an action was supplied/found
        if (!(empty($action))) {
            $url .= '/action/' . $action;
        }

        //  if qs params are supplied
        if (!(empty($params))) {
            $aParams = explode('||', $params);
            $qs = '';
            foreach ($aParams as $param) {
                @list($qsParamName, $listKey) = explode('|', $param);

                //  regarding $aList:
                //  if we have an array of arrays (we're interating through a resultset)
                //  or no resulset was passed (qs params are literals)
                //  - empty array if invoked from manager (default arg)
                //  - string equal to 0 if ## passed from template
                if (is_array(end($aList)) 
                    || (is_array($aList) && !is_object(end($aList))) 
                    || !(count($aList)) 
                    || $aList == 0) {
                
                    //  determine type of param value
                    if (isset($aList[$idx][$listKey]) && !is_null($listKey)) { // pass referenced array element
                        $qsParamValue = $aList[$idx][$listKey];
                        
                    //  we're here because a simple array was passed for $aList, ie:
                    //  makeUrl(#edit#,#orgType#,#user#,orgTypes,#frmOrgTypeID#,id)
                    //  in this case, the key from the flexy foreach is what we want to assign as the value, ie
                    //  - frmOrgTypeId/0
                    //  - frmOrgTypeId/1 ... etc
                    } elseif (isset($aList[$idx]) && is_null($listKey)) {
                        $qsParamValue = $idx;
                        
                    } else {
                        if (stristr($listKey, '[')) { // it's a hash

                            //  split out images[fooBar] to array(images,fooBar)
                            $aElems = array_filter(preg_split('/[^a-z_]/i', $listKey), 'strlen');
                            if (!($aList) && is_a($output, 'SGL_Output')) {
                                
                                //  variable is of type $output->org['organisation_id'] = 'foo';
                                $qsParamValue = $output->{$aElems[0]}[$aElems[1]];
                            } else {
                                $qsParamValue = $aList[$idx][$aElems[0]][$aElems[1]];
                            }
                        } elseif (is_a($output, 'SGL_Output') && isset($output->{$listKey})) {
                            $qsParamValue = $output->{$listKey}; // pass $output property 
                        } else {
                            //  see blocks/SiteNews, not called from template                            
                            $qsParamValue = $listKey; // pass literal                        
                        }
                    }
                    $qs .= '/' . $qsParamName . '/' . $qsParamValue;
                } else {
                    $qs .= '/' . $qsParamName . '/' . $aList[$idx]->$listKey;
                }
            }
            //  append querystring
            $url .= $qs;
        }
        //  add url scheme and SGL prefix if necessary
        SGL_Url::toAbsolute($url);

        //  add a trailing slash if one is not present
        if (substr($url, -1) != '/') {
            $url .= '/';
        }
        //  add session info if necessary
        SGL_Url::addSessionInfo($url);
        
        return $url;
    }
    
    /**
     * Checks to see if cookies are enabled, if not, session id is added to URL.
     *
     * PHP's magic querystring functionality is negated in SimpleNav::getTabsByRid(),
     * in other words, the ?PHPSESSID=aeff023230323 is stripped out
     *
     * @param string $url
     * @return void
     */
    function addSessionInfo(&$url)
    {
        //  determine is session propagated in cookies or URL
        $sessionInfo = defined('SID') ? SID : '';
        if (!empty($sessionInfo)) {

            //  determine glue
            $glue = (preg_match("/\?pageID/i", $url)) ? '&' : '?';
            $url .= $glue . $sessionInfo . '&/1/';
        }
    }
    
    /**
     * Removes the session name and session value elements from an array.
     *
     * @param array $aUrl
     */
    function removeSessionInfo(&$aUrl)
    {
        $conf = & $GLOBALS['_SGL']['CONF'];
        $key = array_search($conf['cookie']['name'], $aUrl);
        if ($key !== false) {
            unset($aUrl[$key], $aUrl[$key + 1]);
        }
    }
}
?>