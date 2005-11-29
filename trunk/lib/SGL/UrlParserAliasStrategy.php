<?php
/**
 * Strategy for handling URL aliases.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.5 $
 */

require_once dirname(__FILE__) . '/UrlParserSimpleStrategy.php';

/**
 * Concrete alias url parser strategy
 *
 */
class SGL_UrlParserAliasStrategy extends SGL_UrlParserStrategy
{
    /**
     * Analyzes querystring content and parses it into module/manager/action and params.
     *
     * @param SGL_Url $url
     * @return array        An array to be assigned to SGL_Url::aQueryData
     * @todo frontScriptName is already dealt with in SGL_Url constructor, remove from here
     */
    function parseQueryString(/*SGL_Url*/$url, $conf)
    {
 		require_once SGL_DAT_DIR . '/ary.uriAliases.php';

 		$aUriParts = SGL_Url::toPartialArray($url->url, $conf['site']['frontScriptName']);

		//    The alias will always be the second uri part in the array
        $alias = $aUriParts[1];

        //  If alias exists, update the alias in the uri with the specified resource
        $ret = array();
        if (array_key_exists($alias, $aUriAliases)) {
        	$aliasUri = $aUriAliases[$alias];

            $obj = new SGL_Url($aliasUri, true, new SGL_UrlParserSimpleStrategy());
            $ret = $obj->getQueryData();
        }
        return $ret;
    }
}

?>