<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2008, Demian Turner                                         |
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
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | UrlParserSefStrategy.php                                                  |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Util.php,v 1.22 2005/05/11 00:19:40 demian Exp $

/**
 * SEF querystring url parser strategy.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.5 $
 */

/**
 * Concrete SEF url parser strategy
 *
 */
class SGL_UrlParser_SefStrategy extends SGL_UrlParserStrategy
{
    /**
     * Analyzes querystring content and parses it into module/manager/action and params.
     *
     * @param SGL_Url $url
     * @return array        An array to be assigned to SGL_Url::aQueryData
     * @todo frontScriptName is already dealt with in SGL_Url constructor, remove from here
     */
    function parseQueryString(/*SGL_Url*/$url)
    {
        $aUriParts = SGL_Url::toPartialArray($url->url, SGL_Config::get('site.frontScriptName'));

        //  remap
        if (SGL_Config::get('site.frontScriptName')) {
            $aParsedUri['frontScriptName'] = array_shift($aUriParts);

            //  if frontScriptName empty, get from config
            if (empty($aParsedUri['frontScriptName'])
                    || $aParsedUri['frontScriptName'] != SGL_Config::get('site.frontScriptName')) {
                $aParsedUri['frontScriptName'] = SGL_Config::get('site.frontScriptName');
            }
        }

        if (count($aUriParts)) {
            $str = strtolower(array_shift($aUriParts));
            $aParsedUri['moduleName'] = (preg_match('/start_debug/', $str))
                ? ''
                :$str;
            $mgrCopy = array_shift($aUriParts);
            $aParsedUri['managerName'] = strtolower($mgrCopy);
        } else {
            $aParsedUri['moduleName'] = SGL_Config::get('site.defaultModule');
            $aParsedUri['managerName'] = $mgrCopy = SGL_Config::get('site.defaultManager');
            if (SGL_Config::get('site.defaultParams')) {
                $aParams = SGL_Url::querystringArrayToHash(
                    explode('/', SGL_Config::get('site.defaultParams')));
                foreach ($aParams as $k => $v) {
                    $aParsedUri[$k] = $v;
                }
            }
        }

        //  if no manager name, must be default manager, ie, has same name as module
        //  the exception is when the moduleName comes from the conf
        if (empty($aParsedUri['managerName'])
                || preg_match('/start_debug/', $aParsedUri['managerName'])) {
            $aParsedUri['managerName'] = $aParsedUri['moduleName'];
        }

        //  grab mod & mgr name from alias strategy if present
        //FIXME: use strat name as key, not index
        if (isset($url->aRes[1]) && array_key_exists('moduleName', $url->aRes[1])) {
            $aParsedUri['moduleName'] = $url->aRes[1]['moduleName'];
            $aParsedUri['managerName'] = $url->aRes[1]['managerName'];
        }
        //  we've got module name so load and merge local and global configs
        //  unless we're running the setup wizard
        if (!SGL_Config::get('site.setup')  && !empty($aParsedUri['moduleName'])) {
            $testPath = SGL_MOD_DIR  . '/' . $aParsedUri['moduleName'] . '/conf.ini';
            $path = realpath($testPath);
            if ($path && file_exists($path)) {
                $c = &SGL_Config::singleton();
                $aModuleConfig = $c->load($path);

                if (PEAR::isError($aModuleConfig)) {
                    return $aModuleConfig;
                } else {
                    @define('SGL_MODULE_CONFIG_LOADED', true);
                    $c->merge($aModuleConfig);
                    $c->set('localConfig', array('moduleName' => $aParsedUri['moduleName']));
                }
                //  determine is moduleName is simplified, in other words, the mgr
                //  and mod names should be the same
                if ($aParsedUri['moduleName'] != $aParsedUri['managerName']) {
                    if (SGL_Inflector::isMgrNameOmitted($aParsedUri)) {
                        array_unshift($aUriParts, $mgrCopy);
                        $aParsedUri['managerName'] = $aParsedUri['moduleName'];
                    }
                }
            }
        }
        //  catch case where when manager + mod names are the same, and cookies
        //  disabled, sglsessid gets bumped into wrong slot
        if (preg_match('/'.strtolower(SGL_Config::get('cookie.name')).'/', $aParsedUri['managerName'])) {
            $cookieValue = isset($aParsedUri['managerName'][1])
                ? $aParsedUri['managerName'][1]
                : '';
            $cookieValue = substr($cookieValue, 0, -1);
            $aParsedUri['managerName'] = $aParsedUri['moduleName'];
            array_unshift($aUriParts, $cookieValue);
            array_unshift($aUriParts, SGL_Config::get('cookie.name'));
        }
        //  if 'action' is in manager slot, move it to querystring array, and replace
        //  manager name with default mgr name, ie, that of the module
        if ($aParsedUri['managerName'] == 'action') {
            $aParsedUri['managerName'] = $aParsedUri['moduleName'];
            array_unshift($aUriParts, 'action');
        }

        $aQsParams = SGL_Url::querystringArrayToHash($aUriParts, $aParsedUri);

        //  remove frontScriptName
        unset($aParsedUri['frontScriptName']);
        if (empty($aParsedUri['moduleName'])) {
            unset($aParsedUri['moduleName']);
        }
        if (empty($aParsedUri['managerName'])) {
            unset($aParsedUri['managerName']);
        }

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
        $mgr = (empty($mgr)) ? 'default' : $mgr;
        $mod = (empty($mod)) ? 'default' : $mod;
        $url = ($conf['site']['frontScriptName'] != false)
            ? $conf['site']['frontScriptName'] . '/'
            : '';

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
                if ($aList && is_array(end($aList))
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
                    } elseif (is_array($aList) && isset($aList[$idx]) && is_null($listKey)) {
                        $qsParamValue = $idx;

                    } else {
                        if (stristr($listKey, '[')) { // it's a hash

                            //  split out images[fooBar] to array(images,fooBar)
                            $aElems = array_filter(preg_split('/[^a-z_0-9]/i', $listKey), 'strlen');
                            if (!($aList) && is_a($output, 'SGL_Output')) {

                                //  variable is of type $output->org['organisation_id'] = 'foo';
                                $qsParamValue = $output->{$aElems[0]}[$aElems[1]];
                            } else {
                                $qsParamValue = $aList[$idx][$aElems[0]][$aElems[1]];
                            }
                        } elseif (is_a($output, 'SGL_Output') && !empty($listKey) && isset($output->{$listKey})) {
                            $qsParamValue = $output->{$listKey}; // pass $output property
                        } else {
                            //  see blocks/SiteNews, not called from template
                            $qsParamValue = $listKey; // pass literal
                        }
                    }
                    if (strlen($qsParamValue) > 0) {
                        $qs .= '/' . $qsParamName . '/' . $qsParamValue;
                    }
                } else {
                    if (isset($aList[$idx]->$listKey) && strlen($aList[$idx]->$listKey) > 0) {
                        $qs .= '/' . $qsParamName . '/' . $aList[$idx]->$listKey;
                    }
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
