<?php
/**
 * Strategy for handling URL aliases.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.5 $
 */


/**
 * Concrete alias url parser strategy
 *
 */
class SGL_UrlParserAliasStrategy extends SGL_UrlParserSimpleStrategy
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
        static $aUriAliases;
        if (!isset($aUriAliases)) {
            $aUriAliases = $this->getAllAliases($conf);
        }

 		$aUriParts = SGL_Url::toPartialArray($url->url, $conf['site']['frontScriptName']);

		//	The alias will always be the second uri part in the array
		//	FIXME: needs to be more flexible
        $ret = array();
		if (count($aUriParts) > 1) {
            $alias = $aUriParts[1];

            //  If alias exists, update the alias in the uri with the specified resource
            if (array_key_exists($alias, $aUriAliases)) {
            	$aliasUri = $aUriAliases[$alias];
            	$tmp = new stdClass();
            	$tmp->url = $aliasUri;
            	$ret = parent::parseQueryString($tmp, $conf);
            }
		}
        return $ret;
    }

    function getAllAliases($conf)
    {
        $dbh = & SGL_DB::singleton();
        $query = "
        SELECT uri_alias, resource_uri
        FROM {$conf['table']['uri_alias']} u, {$conf['table']['section']} s
        WHERE u.section_id = s.section_id
        ";
        return $dbh->getAssoc($query);
    }
}

?>