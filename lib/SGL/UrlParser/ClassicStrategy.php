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
// | UrlParserClassicStrategy.php                                              |
// +---------------------------------------------------------------------------+
// | Authors:   Richard Heyes <richard at php net>                             |
// |            Demian Turner <demian@phpkitchen.com>                          |
// +---------------------------------------------------------------------------+
// $Id: Util.php,v 1.22 2005/05/11 00:19:40 demian Exp $

/**
 * Classic querystring url parser strategy, from PEAR's Net_URL class.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.5 $
 */
class SGL_UrlParser_ClassicStrategy extends SGL_UrlParserStrategy
{
    /**
    * Parses raw querystring and returns an array of it
    *
    * @param  string  $querystring The querystring to parse
    * @return array                An array of the querystring data
    * @access private
    */
    function parseQuerystring(/*SGL_Url*/$url)
    {
        $parts  = preg_split('/[' . preg_quote(ini_get('arg_separator.input'),
            '/') . ']/',
            $url->querystring,
            -1,
            PREG_SPLIT_NO_EMPTY);

        $return = array();

        foreach ($parts as $part) {
            if ($part{0} == '/') {
                continue;
            }
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
            } elseif (!$url->useBrackets && !empty($return[$key])) {
                $return[$key]   = (array)$return[$key];
                $return[$key][] = $value;
            } else {
                $return[$key] = $value;
            }
        }
        return $return;
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
            ? $conf['site']['frontScriptName'] . '?'
            : '';

        //  allow for default managers, ie, in faqMgr, don't
        //  return http://localhost.localdomain/seagull/www/index.php/faq/faq/action/edit/
        $url .= 'moduleName='.$mod . '&';
        $url .= 'managerName='.$mgr;

        //  only add action param if an action was supplied/found
        if (!(empty($action))) {
            $url .= '&action=' . $action;
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
                    } elseif (isset($aList[$idx]) && is_null($listKey)) {
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
                        } elseif (is_a($output, 'SGL_Output') && isset($output->{$listKey})) {
                            $qsParamValue = $output->{$listKey}; // pass $output property
                        } else {
                            //  see blocks/SiteNews, not called from template
                            $qsParamValue = $listKey; // pass literal
                        }
                    }
                    $qs .= '&' . $qsParamName . '=' . $qsParamValue;
                } else {
                    $qs .= '&' . $qsParamName . '=' . $aList[$idx]->$listKey;
                }
            }
            //  append querystring
            $url .= $qs;
        }
        //  add url scheme and SGL prefix if necessary
        SGL_Url::toAbsolute($url);

        //  add session info if necessary
        SGL_Url::addSessionInfo($url);

        return $url;
    }
}
/*

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
