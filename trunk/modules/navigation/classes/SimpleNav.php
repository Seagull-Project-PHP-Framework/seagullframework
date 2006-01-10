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
// | SimpleNav.php                                                             |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: SimpleNav.php,v 1.43 2005/06/20 23:28:37 demian Exp $

require_once SGL_CORE_DIR . '/Translation.php';
require_once SGL_MOD_DIR . '/default/classes/DA_Default.php';

/**
 * Handles generation of nested unordered lists in HTML containing data from sections table.
 *
 * @package navigation
 * @author  Andy Crain <apcrain@fuse.net>
 * @author  Demian Turner <demian@phpkitchen.com>
 * @author  AJ Tarachanowicz <ajt@localhype.net>
 * @version $Revision: 1.43 $
 */

class SimpleNav
{
    /**
     * Id of the section (seagull.section.id) to which the static link links
     *
     * @access  private
     * @var     int
     */
    var $_staticId = 0;

    /**
     * Current user's group id (seagull.user_group.id)
     *
     * @access  private
     * @var     int
     */
    var $_rid = 0;

    /**
     * Id of the section (seagull.section.id) currently being viewed, according
     * to basename($_SERVER['PHP_SELF']) and seagull.section.resource_uri.
     *
     * @access  private
     * @var     int
     */
    var $_currentSectionId = 0;

    /**
     * Boolean flag typically set to true by NavStyleMgr. Used by _toHtml() to determine how
     * to write anchor tags in the list it outputs. If _disableLinks=false, links reflect values
     * in sections table, with current section highlighted, etc.; if =true, then links are all
     * to the current page and have &action=list and &staticId= the current page's id.
     *
     * @access  private
     * @var     boolean
     */
    var $_disableLinks = false;


    /**
     * A reference to the Data Access layer from the default module.
     *
     * @var DA_Default
     */
    var $da = null;

    /**
     * A copy of the Config map.
     *
     * @var array
     */
    var $conf = array();

    /**
     * A reference to the SGL_Translation object.
     *
     * @var SGL_Translation
     */
    var $trans = null;

    /**
     * Holds section id(s) of section(s) nested below which is the current page.
     *
     * @access  private
     * @var     array
     */
    var $_aParentsOfCurrentPage = array();
    /**
     * Holds section title translations
     *
     * @access  private
     * @var     array
     */
    var $_aTranslations = array();

    function SimpleNav($input)
    {
        $this->_rid = (int)SGL_HTTP_Session::get('rid');

        //  get a reference to the request object
        $req = & SGL_Request::singleton();

        $key = $req->get('staticId');
        $this->_staticId = (is_null($key)) ? 0 : $key;
        $this->input    = $input;
        $c              = &SGL_Config::singleton();
        $this->conf     = $c->getAll();
        $this->da       = & DA_Default::singleton();
        $this->trans    = &SGL_Translation::singleton();

        if (is_null($input->get('navLang'))) {
            $input->set('navLang', SGL_Translation::getLangID());
        }
        $this->_aTranslations =
            SGL_Translation::getTranslations('nav', $input->get('navLang'));
    }

    /**
     * Reads appropriate cache file if exists, else builds and returns nav bar HTML.
     *
     * Cache file name an md5 of this file name + user's group id, since nav access
     * is restricted by rid.
     *
     * @return string
     * @access  public
     */
    function render()
    {
        $cache = & SGL::cacheSingleton();

        //  get a unique token by considering url, group ID and if page
        //  is static or not
        $reg = &SGL_Registry::singleton();
        $url = $reg->getCurrentUrl();

        $cacheId = $url->getQueryString() . $this->_rid . $this->_staticId;
        if ($data = $cache->get($cacheId, 'nav')) {
            $aUnserialized = unserialize($data);
            $sectionId = $aUnserialized['sectionId'];
            $html = $aUnserialized['html'];
            SGL::logMessage('nav tabs from cache', PEAR_LOG_DEBUG);
        } else {
            $aSectionNodes = $this->getSectionsByRoleId();
            if (PEAR::isError($aSectionNodes)) {
                return $aSectionNodes;
            }
            //  fetch current lang
            $lang = SGL_Translation::getLangID();

            //  retreive nav translation
            $this->_aTranslations = SGL_Translation::getTranslations('nav', $lang);

            $sectionId = $this->_currentSectionId;
            $html = $this->_toHtml($aSectionNodes);
            $aNav = array('sectionId' => $sectionId, 'html' => $html);
            $cache->save(serialize($aNav), $cacheId, 'nav');
            SGL::logMessage('nav tabs from db', PEAR_LOG_DEBUG);
        }
        return array($sectionId, $html);
    }

    /**
     * Returns an array of section objects that are enabled with perms based
     * on the user's role id.  Section objects are nested with children inside parents.
     *
     * Also determines _currentSectionId. NB Recursive.
     *
     * @access  public
     * @param   int $sectionId
     * @return  array
     * @author  Andy Crain <apcrain@fuse.net>
     * @author  Demian Turner <demian@phpkitchen.com>
     */
    function getSectionsByRoleId($sectionId = 0)
    {
        $result = $this->da->getSectionsByRoleId($sectionId);

        $reg = &SGL_Registry::singleton();
        $url = $reg->getCurrentUrl();

        //  query data never includes frontScriptName, ie, index.php
        $aQueryData = $url->getQueryData();

        if (PEAR::isError($aQueryData)) {
            return $aQueryData;
        }
        // replace +'s in array elements with spaces
        $aQueryData = array_map(create_function('$a', 'return str_replace("+", " ", $a);'),
            $aQueryData);

        //  temporarily remove session info
        SGL_Url::removeSessionInfo($aQueryData);

        //  return to string
        $querystring = implode('/', $aQueryData);

        //  find current section
        $aSectionNodes = array();
        $tmpQuerystring = $querystring;
        while ($result->fetchInto($section)) {
            if (!(in_array($this->_rid, explode(',', $section->perms)))) {
                continue;
            }
            //  get orig $querystring value for each iteration
            $querystring = $tmpQuerystring;

            //  set all defaults to false, then test
            $section->children = false;
            $section->isCurrent = false;
            $section->childIsCurrent = false;

            //  deal with different uri types
            if (preg_match("@^publisher/wikiscrape/url@", $section->resource_uri)) {
                $req = & SGL_Request::singleton();
                $req->set('articleTitle', $section->title);
            } elseif (preg_match('/^uriAlias:(.*)/', $section->resource_uri, $aUri)) {
                $ok = preg_match('/^[0-9]+/', $aUri[1], $aRet);
                $section->resource_uri = $this->da->getAliasById($aRet[0]);
            } elseif (preg_match('/^uriExternal:(.*)/', $section->resource_uri, $aUri)) {
                $section->resource_uri = $aUri[1];
                $section->uriExternal = true;
            }

            //  retreive translation
            if ($section->trans_id && array_key_exists($section->trans_id, $this->_aTranslations) ) {
                $section->title = $this->_aTranslations[$section->trans_id];
            }

            //  recurse if there are (potential) children--even if R - L > 1, the children might
            //  not be children for output if is_enabled != 1 or if user's _rid not in perms.
            if ($section->right_id - $section->left_id > 1) {
                $section->children = $this->getSectionsByRoleId($section->section_id);
                if (in_array($section->section_id, $this->_aParentsOfCurrentPage)) {
                    $section->childIsCurrent = true;
                }
            }
            // loop through all children of section and see if there's an active one.
            // if so set childIsCurrent
            if ($section->children) {
                 foreach ($section->children as $node) {
                      if ($node->isCurrent || $node->childIsCurrent) {
                           $section->childIsCurrent = true;

                           // collect all current sections
                           $this->_aParentsOfCurrentPage[] = $section->section_id;
                       }
                   }
            }
            // if we haven't  found current yet ...
            if(!$section->childIsCurrent) {
                //  first check if querystring is a simplified version of section name,
                //  ie, if we have example.com/index.php/faq instead of example.com/index.php/faq/faq
                if (SGL_Inflector::isUrlSimplified($querystring, $section->resource_uri)) {

                    //  module name and manager name are identical, temporarily unshorten
                    //  querystring name so match can be possible
                    $aParts = explode('/', $querystring);
                    $moduleName = $aParts[0];
                    array_unshift($aParts, $moduleName);

                    //  return to string
                    $querystring = implode('/', $aParts);
                }
                //  compare querystring and section name from db, is it:
    /*
                $conda1 = $section->resource_uri == $querystring;
                $conda2 = $this->_staticId == 0;
                $evala = $conda1 && $conda2;

                $condb1 = $section->section_id != 0;
                $condb2 = $section->section_id == $this->_staticId;
                $evalb = $condb1 && $condb2;

                $condc1 = strpos($querystring, 'articleview') !== false;
                $condc2 = strpos($querystring, 'frmCatID') !== false;
                $condc3 = $section->is_static == 0;
                $evalc = $condc1 && $condc2 && $condc3;
    */
                $realQueryS = $url->querystring;
                // remove possible staticId flag
                $realQueryS = preg_replace('/staticId[^$]*/','',$realQueryS);
                // remove trailing slash
                $realQueryS = preg_replace('/\/$/','',$realQueryS);
                // remove first slash
                $realQueryS = preg_replace('/^\//','',$realQueryS);
                if (
                    //  a. the strings are identical and it's not a static article
                    ($section->resource_uri == $querystring && $this->_staticId == 0 )
                    // b.
                    || (
                        $section->resource_uri !== '' &&
                        isset($url->aQueryData['moduleName']) &&
                        0 === strpos($url->aQueryData['moduleName'] .'/'. $realQueryS.'/', $section->resource_uri.'/')
                    )
                    // b.2 shortened form uri
                    || (
                        $section->resource_uri !== '' &&
                        isset($url->aQueryData['moduleName']) &&
                        0 === strpos($realQueryS.'/', $section->resource_uri.'/')
                    )
                    //  c. it is a static article, so staticId must be non-zero
                    || ($section->section_id != 0 && $section->section_id == $this->_staticId)

                    //  d. we're browsing articles by category ID
                    || (strpos($querystring, 'articleview') !== false)
                    && strpos($section->resource_uri, 'articleview') !== false
                    && strpos($querystring, 'frmCatID') !== false
                    && $section->is_static == 0)
                {
                    $section->isCurrent = true;
                    $this->_currentSectionId = $section->section_id;
                    $exactMatch = true;

                    //  add parent to parentsOfCurrentPage array
                    $this->_aParentsOfCurrentPage[] = $section->parent_id;
                } elseif (empty($section->resource_uri)) {

                    if (    $querystring == $this->conf['site']['defaultModule']
                        || ($querystring == $this->conf['site']['defaultModule'] . '/' .
                                            $this->conf['site']['defaultManager'])) {
                        $section->isCurrent = true;
                        $this->_currentSectionId = $section->section_id;
                        $exactMatch = true;

                        //  add parent to parentsOfCurrentPage array
                        $this->_aParentsOfCurrentPage[] = $section->parent_id;
                    }

                //  this case is for subtabs, ie Contact Us/Hosting Info
                } elseif (!isset($exactMatch)) {

                    // explode and rebuild baseUri. Compare current segment against $section->resource_uri
                    $aPieces = explode('/', $querystring);
                    $tmpUri = '';
                    $bFlag = false;
                    foreach ($aPieces as $k => $v) {
                        if (!$bFlag) {
                            $tmpUri .= $v;
                            $bFlag = true;
                        } else {
                            $tmpUri .= '/' . $v;
                        }
                        //  create array of potential matches
                        if ($tmpUri == $section->resource_uri) {

                            //  make sure we don't abort too early if we're matching a static id, ie, a static article
                            if ($this->_staticId != 0 && ($section->section_id != $this->_staticId)) {
                                break;
                            }

                            $section->isCurrent = true;
                            $this->_currentSectionId = $section->section_id;

                            //  add parent to parentsOfCurrentPage array
                            $this->_aParentsOfCurrentPage[] = $section->parent_id;
                            break;
                        }
                    }
                }
            } // end if ! childIsCurrent
            //  add section node to nodes array, only if it is enabled, ie:
            //  $this->_currentSectionId may have been set, even if tab is not to be shown
            if ($section->is_enabled) {
                $aSectionNodes[$section->section_id] = $section;
            }
        }
        return $aSectionNodes;
    }

    /**
     * Returns HTML unordered list with subsections nested; can be used with CSS for navigation
     * tabs. Adds attribute class="current" to <li> tags.
     * Return false if passed an empty array (if getTabsByRid() found no sections.)
     *
     * @access  private
     * @param   array $sectionNodes   array of DataObjects_Section objects
     * @return  string | false
     */
    function _toHtml($sectionNodes)
    {
        $listItems = '';
        foreach ($sectionNodes as $section) {
            $liAtts = '';
            if ($section->isCurrent || $section->childIsCurrent) {
                $liAtts = ' class="current"';
            }
            //  add static flag if necessary
            $isStatic = ($section->is_static) ? 'staticId/' . $section->section_id . '/': '';

            $aTmp = explode('/', $section->resource_uri);

            //  extract module name
            if (isset($aTmp[0]) && !empty($aTmp[0])) {
                $moduleName = $aTmp[0];
            } else {
                $moduleName = 'default';
            }
            unset($aTmp[0]);

            //  extract manager name
            if (isset($aTmp[1])) {
                $managerName = $aTmp[1];
                unset($aTmp[1]);
            } else {
                $managerName = $moduleName;
            }
            //  get querysting values if any
            $qs = '';
            foreach ($aTmp as $val) {
                $qs .= urlencode($val) . '/';
            }

            $url = ($this->_disableLinks)
                ? $_SERVER['PHP_SELF'] . '/staticId/' . $section->section_id . '/rid/' . $this->_rid . '/'
                : SGL_Url::makeLink('', $managerName, $moduleName) . $qs . $isStatic;

            //  extract anchor and place at end if exists
            if (stristr($url, '#')) {
                $anchorStart = strpos($url, '#');
                list(,$anchorFragment) = split('#', $url);
                $anchorOffset = (strpos($anchorFragment, '&amp;')) + 1;
                $anchorEnd = $anchorStart + $anchorOffset;
                $namedAnchor = substr($url, $anchorStart, $anchorOffset);

                //  remove anchor
                $url = str_replace($namedAnchor, '', $url);

                //  place anchor at end
                $url .= $namedAnchor;
            }
            $url = (isset($section->uriExternal)) ? $section->resource_uri : $url;
            $anchor      = '<a' . ' href="' . $url . '">' . $section->title . '</a>';
            $listItems  .= "<li" . $liAtts . '>' . $anchor;
            if ($section->children) {
               $listItems .= $this->_toHtml($section->children);
            }
            $listItems  .= "</li>\n";
        }
        $output = (isset($listItems)) ? "\n<ul>" . $listItems . "</ul>\n":false;
        return $output;
    }

    /**
     * Returns section name give the section id.
     *
     * @return string
     */
    function getCurrentSectionName()
    {
        if (!$this->_currentSectionId) {
            $sectionName = $this->input->get('pageTitle');
        } else {
            $sectionName = $this->da->getSectionNameById($this->_currentSectionId);

            if (is_numeric($sectionName)) {
                $sectionName = $this->trans->get($sectionName, 'nav', SGL_Translation::getLangID());
            }
        }
        return $sectionName;
    }

    /**
     * Sets private var _disableLinks to [true (default) | false]. If links are disabled, the
     * following is changed in the <a href=""> tags output by _toHtml:
     * - href attribute refers to PHP_SELF rather than the page in the sections table
     * - URL string added with "?action=list" and "&staticId=[$_GET[staticId]]"
     * This allows the generation of a self-referential, disabled list of links in NavStyleMgr.
     *
     * @access public
     * @param  bool $disable
     * @return void
     */
    function setDisableLinks($disable = true)
    {
        $this->_disableLinks = $disable;
    }

    /**
     * Modifier for _staticId, set from $_GET['staticId'] by the constructor. Sometimes we
     * need to set a fake staticId, though, in order to get SimpleNav
     * to flag as current a section that is different from the page we're on. For example,
     * NavStyleMgr displays a preview of the nav bar to the admin, but it does so from a
     * page for which there is no section in the section table, so we fake one.
     *
     * @access  public
     * @param   int $staticId
     * @return  true on success | false on failure
     */
    function setStaticId($staticId)
    {
        if (is_numeric($staticId)) {
            $this->_staticId = $staticId;
            return true;
        }
        return false;
    }

    /**
     * Modifier for _rid, set from group id in Session by the constructor. Sometimes we
     * need to set a fake _rid, though, in order to get SimpleNav
     * to return sections permitted to a group other than that of the current user. For example,
     * NavStyleMgr displays a preview of the nav bar to the admin, but since the admin _rid = 1,
     * and we want to display a nav bar as members (rid=2) would see it, we need to change it.
     *
     * @access  public
     * @param   int $rid    id representing group assignment
     * @return  true on success | false on failure
     */
    function setRid($rid)
    {
        if (is_numeric($rid)) {
            $this->_rid = $rid;
            return true;
        }
        return false;
    }
}
?>
