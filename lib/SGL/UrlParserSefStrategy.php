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
// | Seagull 0.5                                                               |
// +---------------------------------------------------------------------------+
// | UrlParserSefStrategy.php                                                  |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Util.php,v 1.22 2005/05/11 00:19:40 demian Exp $

/**
 * Classic querystring url parser strategy.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.5 $
 */

/**
 * Concrete SEF url parser strategy
 *
 */
class SGL_UrlParserSefStrategy extends SGL_UrlParserStrategy
{
    /**
    * Returns full url
    *
    * @return string Full url
    * @access public
    */
    function toString(/*SGL_Url*/$url)
    {
        $retUrl = $url->protocol . '://'
                   . $url->user . (!empty($url->pass) ? ':' : '')
                   . $url->pass . (!empty($url->user) ? '@' : '')
                   . $url->host . ($url->port == $url->getStandardPort($url->protocol) ? '' : ':' . $url->port)
                   . $url->path
                   . $url->frontScriptName
                   . (!empty($url->querystring) ? $url->querystring : '')
                   . (!empty($url->anchor) ? '#' . $url->anchor : '');

        return $retUrl;
    }

    /**
     * Analyzes querystring content and parses it into module/manager/action and params.
     *
     * @param SGL_Url $url
     * @return array        An array to be assigned to SGL_Url::aQueryData
     * @todo frontScriptName is already dealt with in SGL_Url constructor, remove from here
     */
    function parseQueryString(/*SGL_Url*/$url, $conf)
    {
        $aUriParts = SGL_Url::toPartialArray($url->url, $conf['site']['frontScriptName']);

        //  remap
        if ($conf['site']['frontScriptName'] != false) {
            $aParsedUri['frontScriptName'] = array_shift($aUriParts);

            //  if frontScriptName empty, get from config
            if (empty($aParsedUri['frontScriptName'])
                    || $aParsedUri['frontScriptName'] != $conf['site']['frontScriptName']) {
                $aParsedUri['frontScriptName'] = $conf['site']['frontScriptName'];
            }
        }

        $aParsedUri['moduleName'] = strtolower(array_shift($aUriParts));
        $mgrCopy = array_shift($aUriParts);
        $aParsedUri['managerName'] = strtolower($mgrCopy);

        //  if no module name present, get from config
        //  catch case where debugging with Zend supplies querystring params
        $default = false;
        if (empty(  $aParsedUri['moduleName'])
                || (preg_match('/start_debug/', $aParsedUri['moduleName']))
                || (preg_match('/\?/i', $aParsedUri['moduleName']))) {
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

        //  we've got module name so load and merge local and global configs
        //  unless we're running the setup wizard
        if (!isset($conf['setup'])) {
            $c = &SGL_Config::singleton();
            $path = realpath(dirname(__FILE__)  . '/../../modules/' . $aParsedUri['moduleName'] . '/conf.ini');
            if (!$path) {
                return PEAR::raiseError('Could not read current module\'s conf.ini file',
                    SGL_ERROR_NOFILE);
            }
            $aModuleConfig = $c->load($path);

            if ($aModuleConfig) {
                $c->merge($aModuleConfig);
            } else {
                return PEAR::raiseError('Could not read current module\'s conf.ini file',
                    SGL_ERROR_NOFILE);
            }
        }

        //  determine is moduleName is simplified, in other words, the mgr
        //  and mod names should be the same
        if ($aParsedUri['moduleName'] != $aParsedUri['managerName']) {
            if (SGL_Inflector::isMgrNameOmitted($aParsedUri)) {
                array_unshift($aUriParts, $mgrCopy);
                $aParsedUri['managerName'] = $aParsedUri['moduleName'];
            }
        }

        //  catch case where when manger + mod names are the same, and cookies
        //  disabled, sglsessid gets bumped into wrong slot
        if (preg_match('/'.strtolower($conf['cookie']['name']).'/', $aParsedUri['managerName'])) {
            @list(,$cookieValue) = split('=', $aParsedUri['managerName']);
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
                $aRequestVars = array_merge($_REQUEST, $aParsedUri);
                if (    !array_key_exists($matches[1], $aRequestVars)
                    &&  !array_key_exists($matches[1], $aQsParams)) {
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

        //  remove frontScriptName
        unset($aParsedUri['frontScriptName']);

        //  and merge the default request fields with extracted param k/v pairs
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
    function makeLink($action, $mgr, $mod, $aList, $params, $idx, $output)
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        //  get a reference to the request object
        $req = & SGL_Request::singleton();

        //  determine module and manager names
        $mgr = (empty($mgr)) ? $req->get('managerName') : $mgr;
        $mod = (empty($mod)) ? $req->get('moduleName'): $mod;
        $url = ($conf['site']['frontScriptName'] != false) ? $conf['site']['frontScriptName'] . '/' : '';

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
}
?>