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

require_once SGL_MOD_DIR . '/default/classes/DA_Default.php';

/**
 * Handles generation of nested unordered lists in HTML containing data from sections table.
 *
 * @package navigation
 * @author  Andy Crain <apcrain@fuse.net>
 * @author  Demian Turner <demian@phpkitchen.com>
 * @author  AJ Tarachanowicz <ajt@localhype.net>
 * @author  Andrey Podshivalov <planetaz@gmail.com>
 * @version $Revision: 1.43 $
 */

class TestNav
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
     * Title of current section.
     *
     * @access  private
     * @var     string
     */
    var $_currentTitle = '';

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
     * Holds all section id(s) from current.
     *
     * @access  private
     * @var     array
     */
    var $_aAllCurrentPages = array();

    /**
     * Holds section title translations.
     *
     * @access  private
     * @var     array
     */
    var $_aTranslations = array();

    /**
     * Holds home page node.
     *
     * @access  private
     * @var     array
     */
    var $_homePage = array();

    /**
     * The root node from which the branch starts.
     *
     * @access  private
     * @var     integer
     */
    var $_startParentNode = 0;

    /**
     * Start level to render. First level is 0.
     *
     * @access  private
     * @var     integer
     */
    var $_startLevel = 0;

    /**
     * How many levels to generate. To render all levels set to 0.
     *
     * @access  private
     * @var     integer
     */
    var $_levelsToRender = 0;

    /**
     * Set navigation menu as not collapsed by default.
     *
     * @access  private
     * @var     boolean
     */
    var $_collapsed = false;

    /**
     * Navigation menu can be forced to display always.
     *
     * E.g.: regardless if it is user or admin branches, ie for t&c, privacy
     * policy links at bottom on page.
     *
     * @access  private
     * @var     boolean
     */
    var $_showAlways = true;

    /**
     * Hold current rendered level (used by _toHtml function);
     *
     * @access  private
     * @var     integer
     */
    var $_currentRenderedLevel = 0;

    /**
     * generate site breadcrumb objects
     *
     * @access  private
     * @var     integer
     */
    var $_breadcrumbs = true;

    function TestNav(&$input)
    {
        $this->_rid        = (int)SGL_Session::get('rid');
        $this->input       = &$input;
        $this->req         = $input->get('request');
        $this->conf        = $input->conf;
        $this->querystring = $this->req->getUri();
        $this->_staticId   = $this->req->get('staticId');
        $this->da          = &DA_Default::singleton();

        if (is_null($input->get('navLang'))) {
            $input->set('navLang', SGL_Translation::getLangID());
        }
        //  detect if trans2 support required
        if ($this->conf['translation']['container'] == 'db') {
            require_once SGL_CORE_DIR . '/Translation.php';
            $this->trans = & SGL_Translation::singleton();
            $this->_aTranslations =
                SGL_Translation::getTranslations('nav', $input->get('navLang'));
        }
        // set default driver params
        $this->setParams();
    }

    /**
     * Set navigation driver parameters.
     *
     * @access  public
     * @param   array $aParams
     */
    function setParams($aParams = array())
    {

        //  set driver params from configuration
        foreach ($this->conf['navigation'] as $key => $value) {
            $this->{'_' . $key} = $value;
        }

        //  set driver params from aParams
        foreach ($aParams as $key => $value) {
            $this->{'_' . $key} = $value;
        }
    }

    /**
     * Reads appropriate cache file if exists, else builds and returns nav bar HTML.
     *
     * Cache file name an md5 of this file name + user's role id, since nav access
     * is restricted by rid.
     *
     * @return  string
     * @access  public
     */
    function render()
    {
        //  get a unique token by considering url, role ID and if page
        //  is static or not
        $reg     = &SGL_Registry::singleton();
        $url     = $reg->getCurrentUrl();
        $cache   = & SGL_Cache::singleton();
        $cacheId = $url->getQueryString() . $this->_rid . $this->_staticId
            . $this->_startParentNode . $this->_startLevel . $this->_levelsToRender
            . $this->_collapsed . $this->_showAlways;

        if ($data = $cache->get($cacheId, 'nav')) {
            $aUnserialized = unserialize($data);
            $sectionId     = $aUnserialized['sectionId'];
            $html          = $aUnserialized['html'];
            $breadcrumbs   = $aUnserialized['breadcrumbs'];

            SGL::logMessage('nav tabs from cache', PEAR_LOG_DEBUG);

        //  cache doesn't exist
        } else {
            $html = false;

            //  generate sections nodes
            $aSectionNodes = $this->getSectionsByRoleId($this->_startParentNode);
            if (PEAR::isError($aSectionNodes)) {
                return $aSectionNodes;
            }
            $sectionId = $this->_currentSectionId;

            //  get breadcrumbs
            $breadcrumbs = false;
            if ($sectionId && $this->_breadcrumbs) {
                $breadcrumbs = $this->getBreadcrumbs();
            }

            //  if showAlways is true or current section is defined should be rendered to HTML
            if (!empty($aSectionNodes) && ($this->_showAlways || $sectionId)) {

                //  if start level > 0 lookup new start parent node
                if ($this->_startLevel > 0) {
                    $position = count($this->_aAllCurrentPages) - $this->_startLevel;

                    //  look up current section id in array
                    $aPositions        = array_keys($this->_aAllCurrentPages);
                    $sectionIdPosition = array_search($sectionId, $aPositions);

                    if ($position >= $sectionIdPosition && $sectionId) {
                        $newParentNode = $this->_aAllCurrentPages[$aPositions[$position]];
                        $aSectionNodes = $newParentNode->children;
                    } else {
                        $aSectionNodes = false;
                    }
                }
                if ($aSectionNodes) {
                    $html = $this->_toHtml($aSectionNodes);
                }
            }

            //  cache stuff
            $aNav = array(  'sectionId' => $sectionId,
                            'html' => $html,
                            'breadcrumbs' => $breadcrumbs);
            $cache->save(serialize($aNav), $cacheId, 'nav');

            SGL::logMessage('nav tabs from db', PEAR_LOG_DEBUG);
        }
        return array($sectionId, $html, $breadcrumbs);
    }

    /**
     * Returns an array of section objects that are enabled with perms based
     * on the user's role id.  Section objects are nested with children inside parents.
     *
     * Also determines _currentSectionId.
     *
     * @access  public
     * @param   int $sectionId
     * @return  array of DataObjects_Section objects
     */
    function getSectionsByRoleId($sectionId = 0)
    {
        $this->_currentSectionId = 0;
        $this->_currentTitle     = '';
        $this->_aAllCurrentPages = array();

        // get navigation tree
        $aSectionNodes = $this->_getSections($sectionId);

        // search exact matching checking
        if ($aSectionNodes) {
            $this->_searchExactMatches($aSectionNodes);

            // if no exact matches search inexact matches
            if (!$this->_currentSectionId && !$this->_staticId) {
                $this->_searchInExactMatches($aSectionNodes);
            }
        }

        return $aSectionNodes;
    }

    /**
     * Returns an array of section objects that are enabled with perms based
     * on the user's role id.  Section objects are nested with children inside parents.
     *
     * @access  private
     * @param   int $sectionId
     * @return  array of DataObjects_Section objects
     */
    function _getSections($sectionId = 0)
    {
        $aSectionNodes = array();

        //  get nodes from parent node
        $result = $this->da->getSectionsByRoleId($sectionId);

        //  process with each node
        while ($result->fetchInto($sectionNode)) {

            //  check permissions
            if (!(in_array($this->_rid, explode(',', $sectionNode->perms)))) {
                continue;
            }

            //  recurse if there are (potential) children--even if R - L > 1, the children might
            $sectionNode->children = false;
            if ($sectionNode->right_id - $sectionNode->left_id > 1) {
                $sectionNode->children = $this->_getSections($sectionNode->section_id);
            }

            //  check add-on
            $aSections   = array();
            if (preg_match('/^uriAddon:([^:]*):(.*)/', $sectionNode->resource_uri, $aUri)) {
                $className = $aUri[1];
                if (!is_array($aClassParams = @unserialize($aUri[2]))) {
                    $aClassParams = array();
                }
                $classFile = dirname(__FILE__) . '/addons/' . $className . '.php';
                if (file_exists($classFile)) {
                    require_once $classFile;
                    if (class_exists($className)) {
                        $addonDriver = new $className;
                        $aSections   = $addonDriver->init($sectionNode, $aClassParams);
                        if ($aSections && is_array($aSections)) {
                            foreach ($aSections as $section) {
                                $aSectionNodes[] = $section;
                            }
                        }
                    }
                }
            } else {
                $aSectionNodes[] = $sectionNode;
            }
        }
        return $aSectionNodes;
    }

    /**
     * Recursively searching exact matches.
     *
     * @access  private
     * @param   array $aSectionNodes
     * @return  void
     */
    function _searchExactMatches(&$aSectionNodes)
    {
        foreach ($aSectionNodes as $key => $section) {
            $section->isCurrent      = false;
            $section->childIsCurrent = false;

            //  deal with different uri types
            //  internal link:
            if (preg_match('/^uriNode:([0-9]+)/', $section->resource_uri, $aUri)) {
                $linkedSection = $this->da->getSectionById($aUri[1]);
                if ($linkedSection &&
                    (in_array($this->_rid, explode(',', $linkedSection->perms)))) {
                    $section->dontMatch    = true;
                    $section->resource_uri = $linkedSection->resource_uri;
                } else {
                    continue;
                }
            }

            //  uri alias:
            if (preg_match('/^uriAlias:([0-9]+):(.*)/', $section->resource_uri, $aUri)) {
                $section->uriAlias     = $this->da->getAliasById($aUri[1]);
                $section->resource_uri = $aUri[2];
            }

            //  home page:
            if (!$section->resource_uri) {
                $section->resource_uri  = $this->conf['site']['defaultModule']
                    ?  $this->conf['site']['defaultModule'] : 'default';
                $section->resource_uri .= $this->conf['site']['defaultManager']
                    ?  '/' . $this->conf['site']['defaultManager'] : '';
                $section->resource_uri .= $this->conf['site']['defaultParams']
                    ?  '/' . $this->conf['site']['defaultParams'] : '';

            //  empty link:
            } elseif ('uriEmpty:' == $section->resource_uri) {
                $section->dontMatch = true;
                $section->uriEmpty  = true;

            //  wiki:
            } elseif (preg_match("@^publisher/wikiscrape/url@", $section->resource_uri)) {
                $req = & SGL_Request::singleton();
                $req->set('articleTitle', $section->title);

            //  external uri:
            } elseif (preg_match('/^uriExternal:(.*)/', $section->resource_uri, $aUri)) {
                $section->resource_uri = $aUri[1];
                $section->dontMatch    = true;
                $section->uriExternal  = true;
            }

            //  retreive translation
            if ($section->trans_id && $this->conf['translation']['container'] == 'db') {
                if (array_key_exists($section->trans_id, $this->_aTranslations)) {
                    $section->title = $this->_aTranslations[$section->trans_id];
                } elseif ($title = $this->trans->get($section->section_id, 'nav',
                    SGL_Translation::getFallbackLangID())) {
                    $section->title = $title;
                }
            }

            //  home page is a first node
            if (empty($this->_homePage) && $section->is_enabled) {
                $this->_homePage = $section;
            }

            if ($section->children) {
                $this->_searchExactMatches($section->children);

                //  if children node is current mark parent node
                foreach ($section->children as $section2) {
                    if ($section2->isCurrent || $section2->childIsCurrent) {
                        $section->childIsCurrent = true;
                        $this->_aAllCurrentPages[$section->section_id] = $section;
                        break;
                    }
                }
            }

            //  still no matches
            if(!$section->childIsCurrent && empty($section->dontMatch)) {
                if (
                    //  the strings are identical and it's not a static article
                    ($section->resource_uri == $this->querystring)

                    //  if disabled links staticId must be non-zero
                    || ($section->section_id && $section->section_id == $this->_staticId))
                {
                    //  exact match has been found
                    $section->isCurrent      = true;
                    $this->_currentSectionId = $section->section_id;
                    $this->_currentTitle     = $section->title;
                    $this->_aAllCurrentPages[$section->section_id] = $section;
                }
            }

            $aSectionNodes[$key] = $section;
        }
    }

    /**
     * Recursively searching inexact matches.
     *
     * @access  private
     * @param   array $aSectionNodes
     * @return  void
     */
    function _searchInExactMatches(&$aSectionNodes)
    {
        foreach ($aSectionNodes as $key => $section) {
            if ($section->children) {
                $this->_searchInExactMatches($section->children);

                //  if children node is current mark parent node
                foreach ($section->children as $section2) {
                    if ($section2->isCurrent || $section2->childIsCurrent) {
                        $section->childIsCurrent = true;
                        $this->_aAllCurrentPages[$section->section_id] = $section;
                        break;
                    }
                }
            }

            //  still no matches
            if (!$section->childIsCurrent && empty($section->dontMatch)) {
                if (strpos($this->querystring, $section->resource_uri . '/') === 0) {

                    //  inexact match has been found
                    $section->isCurrent      = true;
                    $this->_currentSectionId = $section->section_id;
                    $this->_currentTitle     = $section->title;
                    $this->_aAllCurrentPages[$section->section_id] = $section;
                }
            }

            // update if section has new params
            if ($section->isCurrent || $section->childIsCurrent) {
                $aSectionNodes[$key] = $section;
            }
        }
    }

    /**
     * Returns HTML unordered list with subsections nested; can be used with CSS for navigation
     * tabs. Adds attribute class="current" to <li> tags.
     * Returns false if passed an empty array (if getTabsByRid() found no sections.)
     *
     * @access  private
     * @param   array $sectionNodes   array of DataObjects_Section objects
     * @return  string | false
     */
    function _toHtml(&$sectionNodes)
    {
        $listItems = '';
        $this->_currentRenderedLevel++;

        foreach ($sectionNodes as $section) {
            if ($section->is_enabled) {
                $liAtts = '';
                if ($section->isCurrent || $section->childIsCurrent) {
                    $liAtts = ' class="current"';
                }
                if (isset($section->uriExternal) && empty($section->uriAlias)) {
                    if (isset($section->uriAlias)) {
                        $section->resource_uri = $section->uriAlias;
                    }
                    $url = $section->resource_uri;
                } elseif (isset($section->uriEmpty)) {
                    $url = 'javascript:void(0)';
                } else {
                    $url = $this->_makeLinkFromNode($section);
                }

                $anchor      = '<a' . ' href="' . $url . '">' . $section->title . '</a>';
                $listItems  .= "<li" . $liAtts . '>' . $anchor;

                // show children nodes
                if ($section->children

                    // check levelsToRender stuff
                    && (!$this->_levelsToRender
                        || $this->_levelsToRender > $this->_currentRenderedLevel)

                    // check collapsed stuff
                    && (!$this->_collapsed
                        || array_key_exists($section->section_id, $this->_aAllCurrentPages)))
                {
                   $listItems .= $this->_toHtml($section->children);
                }
                $listItems .= "</li>\n";
            }
        }
        $output = ($listItems) ? "\n<ul>" . $listItems . "</ul>\n" : false;
        return $output;
    }

    /**
     * Returns uri link based on node uri.
     *
     * @access  private
     * @param   array $aSections
     * @return  string
     */
    function _makeLinkFromNode(&$aSections)
    {
        if (isset($aSections->uriAlias)) {
            $aSections->resource_uri = $aSections->uriAlias;
        }
        $aTmp = explode('/', $aSections->resource_uri);

        // extract module name
        $moduleName = $aTmp[0];
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
            ? $_SERVER['PHP_SELF'] . '/staticId/' . $aSections->section_id . '/rid/' . $this->_rid . '/'
            : SGL_Url::makeLink('', $managerName, $moduleName) . $qs;

        //  extract anchor and place at end if exists
        if (stristr($url, '#')) {
            $anchorStart           = strpos($url, '#');
            list(,$anchorFragment) = split('#', $url);
            $anchorOffset          = (strpos($anchorFragment, '&amp;')) + 1;
            $anchorEnd             = $anchorStart + $anchorOffset;
            $namedAnchor           = substr($url, $anchorStart, $anchorOffset);

            //  remove anchor
            $url = str_replace($namedAnchor, '', $url);

            //  place anchor at end
            $url .= $namedAnchor;
        }

        return $url;
    }

    /**
     * Returns section name give the section id.
     *
     * @access  public
     * @return  string
     */
    function getCurrentSectionName()
    {
        if ($this->_currentTitle) {
            return $this->_currentTitle;
        } else {
            return $this->input->pageTitle;
        }
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

    /**
     * Returns site breadcrumb objects.
     *
     * @access  public
     * @return  array aBreadcrumbs
     */
    function getBreadcrumbs()
    {
        $aPositions   = array_keys($this->_aAllCurrentPages);
        $count        = count($aPositions);
        $aBreadcrumbs = array();

        if ($count) {
            $sectionId = $this->_currentSectionId;

            // is current section a homepage
            $pathNode = new stdClass();
            if ($this->_homePage->section_id == $this->_currentSectionId) {
                $pathNode->title = $this->_homePage->title;
                $pathNode->link  = false;
                $aBreadcrumbs[]  = $pathNode;
            } else {
                $position = array_search($sectionId, $aPositions);

                //  first node in pathway is home page
                $pathNode->title = $this->_homePage->title;
                $pathNode->link  = $this->_makeLinkFromNode($this->_homePage);
                $pathNode->home  = true;
                $aBreadcrumbs[]  = $pathNode;

                for ($i = $count-1; ($i >= $position); $i--) {
                    $node = $this->_aAllCurrentPages[$aPositions[$i]];
                    if ($this->_homePage->section_id != $node->section_id) {
                        $pathNode        = new stdClass();
                        $pathNode->title = $node->title;
                        $pathNode->link  = ($i == $position || !$node->is_enabled)
                            ? false : $this->_makeLinkFromNode($node);
                        $aBreadcrumbs[]  = $pathNode;
                    }
                }
            }
            $aBreadcrumbs[(count($aBreadcrumbs)-1)]->end = true;
        }
        return $aBreadcrumbs;
    }
}
?>