<?php
/**
 * Strategy for handling URL aliases.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.5 $
 */

require_once SGL_MOD_DIR . '/navigation/classes/NavigationDAO.php';

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
    function parseQueryString(/*SGL_Url*/$url)
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

/**
 * Concrete alias url parser strategy
 *
 */
class SGL_UrlParser_AliasStrategy extends SGL_UrlParser_SimpleStrategy
{
    function SGL_UrlParser_AliasStrategy()
    {
        $this->da = & NavigationDAO::singleton();
    }
    /**
     * Analyzes querystring content and parses it into module/manager/action and params.
     *
     * @param SGL_Url $url
     * @return array        An array to be assigned to SGL_Url::aQueryData
     * @todo frontScriptName is already dealt with in SGL_Url constructor, remove from here
     */
    function parseQueryString(/*SGL_Url*/$url)
    {
        $aUriAliases = $this->da->getAllAliases();
        $aUriParts = SGL_Url::toPartialArray($url->url, SGL_Config::get('site.frontScriptName'));

        //    The alias will always be the second uri part in the array
        //    FIXME: needs to be more flexible
        $countUriParts = SGL_Config::get('site.frontScriptName') ? 1 : 0;
        $ret = array();
        if (count($aUriParts) > $countUriParts) {
            $alias = array_shift($aUriParts);
            if ($countUriParts) {
                $alias = array_shift($aUriParts);
            }

            //  If alias exists, update the alias in the uri with the specified resource
            if (array_key_exists($alias, $aUriAliases)) {
                $key = $aUriAliases[$alias]->resource_uri;

                // records stored in section table in following format:
                // uriAlias:10:default/bug
                // parse out SEF url from 2nd semi-colon onwards
                if (preg_match('/^(uriAlias:)([0-9]+:)(.*)$/', $key, $aMatches)) {
                    $aliasUri = $aMatches[3];

                    // check for uriExternal
                    if (preg_match('/^uriExternal:(.*)$/', $aliasUri, $aUri)) {
                        header('Location: ' . $aUri[1]);
                        exit;
                    }

                    $tmp = new stdClass();
                    $tmp->url = $aliasUri . '/' . implode('/', $aUriParts);
                    $ret = parent::parseQueryString($tmp);
                }
            }
        }
        return $ret;
    }
}
?>