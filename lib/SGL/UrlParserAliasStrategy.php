<?php
/**
 * Strategy for handling URL aliases.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.5 $
 */

require_once SGL_MOD_DIR . '/default/classes/DA_Default.php';

/**
 * Concrete alias url parser strategy
 *
 */
class SGL_UrlParserAliasStrategy extends SGL_UrlParserSimpleStrategy
{
    function SGL_UrlParserAliasStrategy()
    {
        $this->da = & DA_Default::singleton();
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
        static $aUriAliases;
        if (!isset($aUriAliases)) {
            $aUriAliases = $this->da->getAllAliases();
        }

            $aUriParts = SGL_Url::toPartialArray($url->url, $conf['site']['frontScriptName']);

            //	The alias will always be the second uri part in the array
            //	FIXME: needs to be more flexible
            $countUriParts = (empty($conf['site']['frontScriptName'])) ? 0 : 1;
            $ret = array();
            if (count($aUriParts) > $countUriParts) {
                $alias = $aUriParts[$countUriParts];

                //  If alias exists, update the alias in the uri with the specified resource
                if (array_key_exists($alias, $aUriAliases)) {
                    $key = $aUriAliases[$alias];

                    // records stored in section table in following format:
                    // uriAlias:10:default/bug
                    // parse out SEF url from 2nd semi-colon onwards
                    $ok = preg_match('/(uriAlias:)([0-9]+:)(.*)/', $key, $aMatches);
                    $aliasUri = $aMatches[3];
                    $tmp = new stdClass();
                    $tmp->url = $aliasUri;
                    $ret = parent::parseQueryString($tmp, $conf);
                }
            }
        return $ret;
    }
}

?>