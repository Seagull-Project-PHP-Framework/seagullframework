<?php

/**
 * Strategy for handling URL aliases.
 * Concrete alias url parser strategy
 *
 * @package UrlParser
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.5 $
 */

require_once SGL_CORE_DIR . '/Alias.php';
require_once SGL_CORE_DIR . '/UrlParser/AliasStrategy.php';

class SGL_UrlParser_EntityAliasStrategy extends SGL_UrlParserStrategy
{
    function SGL_UrlParser_EntityAliasStrategy()
    {
        $this->da =& new SGL_Alias();
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
        $ret = array();

        //  catch case for default page, ie, home
        if (empty($url->url)) {
            return $ret;
        }
        
        $aUriAliases = $this->da->getAllEntityAliases();
        $aUriParts = SGL_Url::toPartialArray($url->url, $conf['site']['frontScriptName']);

        if (!empty($conf['site']['frontScriptName'])) {
            array_shift($aUriParts);
        }
        $alias = implode('/', $aUriParts);
        

        //  If alias exists, update the alias in the uri with the specified resource
        if (array_key_exists($alias, $aUriAliases)) {
            $key = $aUriAliases[$alias];
            $classFile = SGL_CORE_DIR . '/UrlParser/EntityAliasStrategies/' . ucfirst($key->entity_type) . ".php";
            if (is_file($classFile)) {
                require_once $classFile;
                $className = ucfirst($key->entity_type . "_Alias");
                $oAlias =& new $className();
                if ($oAlias) {
                    $tmp = new stdClass();
                    $ret = $oAlias->getURL($key->entity_id);
                    //$ret = parent::parseQueryString($tmp, $conf);
                }
            }
        }
        return $ret;
    }
}


?>