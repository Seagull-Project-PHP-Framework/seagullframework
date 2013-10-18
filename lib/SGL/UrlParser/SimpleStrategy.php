<?php
/**
 * Created by JetBrains PhpStorm.
 * User: demian
 * Date: 18/10/2013
 * Time: 23:20
 * To change this template use File | Settings | File Templates.
 */
/**
 * Concrete simple url parser strategy.
 *
 */
class SGL_UrlParser_SimpleStrategy extends SGL_UrlParserStrategy
{
    /**
     * Analyzes querystring content and parses it into module/manager/action
     * and params.
     *
     * @param SGL_Url $url
     * @return array        An array to be assigned to SGL_Url::aQueryData
     */
    function parseQueryString( /*SGL_Url*/
        $url)
    {
        $ret = array();

        //  catch case for default page, ie, home
        if (empty($url->url)) {
            return $ret;
        }
        $parts = array_filter(explode('/', $url->url), 'strlen');
        $numElems = count($parts);

        //  we need at least 1 element
        if ($numElems < 1) {
            return $ret;
        }
        $ret['moduleName'] = $parts[0];
        $ret['managerName'] = isset($parts[1]) ? $parts[1] : $parts[0];
        $actionExists = (isset($parts[2]) && $parts[2] == 'action') ? true : false;
        $ret['action'] = ($actionExists) ? $parts[3] : null;

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
                $ret[$aTmp['varName']] = $aTmp['varValue'];
                $aTmp = array();
            }
        }
        return $ret;
    }
}