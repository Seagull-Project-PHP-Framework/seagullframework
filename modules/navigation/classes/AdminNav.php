<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Julien Casanova                                         |
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
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | AdminNav.php                                                             |
// +---------------------------------------------------------------------------+
// | Author:   Julien Casanova <julien_casanova@yahoo.fr>       |
// +---------------------------------------------------------------------------+
// $Id: AdminNav.php,v 1.0 2005/10/29 15:57:00 julien Exp $

require_once SGL_CORE_DIR . '/NestedSet.php';

/**
 * Handles generation of nested unordered lists in HTML containing data from admin_menu table.
 *
 * @package navigation
 * @author  Julien Casanova <julien_casanova@yahoo.fr>
 * @author  Demian Turner <demian@phpkitchen.com>
 * @author  AJ Tarachanowicz <ajt@localhype.net>
 * @version $Revision: 1.0 $
 * @since   PHP 4.1
 */

class AdminNav
{
    /**
     * Array of arrays, each representing a section node from the seagull.section
     * table. Have to fetch each as array rather than object for output to menu to
     * work properly.
     *
     * @access  private
     * @var     array
     */
    var $_aSectionNodes = array();

    /**
     * www root
     *
     * @access  private
     * @var     string
     */
    var $_webRoot = SGL_BASE_URL;

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
     * Holds section id(s) of section(s) nested below which is the current page.
     *
     * @access  private
     * @var     array
     */
    var $_aParentsOfCurrentPage = array();

    function AdminNav()
    {
        $this->_rid = (int)SGL_Session::get('rid');

        //  get a reference to the request object
        $req = SGL_Request::singleton();

        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();

        $this->_params = array(
            'tableStructure' => array(
                'section_id'    => 'id',
                'title'         => 'title',
                'root_id'       => 'rootid',
                'left_id'       => 'l',
                'right_id'      => 'r',
                'order_id'      => 'norder',
                'level_id'      => 'level',
                'parent_id'     => 'parent',
                'resource_uri'  => 'resource_uri',
                'perms'         => 'perms',
                'is_enabled'    => 'is_enabled',
                'has_link'      => 'has_link',

            ),
            'tableName'      => 'admin_menu',
            'lockTableName'  => 'table_lock',
            'sequenceName'   => 'admin_menu');
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
        $cache = & SGL_Cache::singleton();

        //  get a unique token by considering url, group ID and if page
        //  is static or not
        $reg = &SGL_Registry::singleton();
        $url = $reg->getCurrentUrl();

        $cacheId = basename($_SERVER['PHP_SELF']) . $this->_rid;
        if ($data = $cache->get($cacheId, 'adminNav')) {
            $aUnserialized = unserialize($data);
            $sectionId = $aUnserialized['sectionId'];
            $html = $aUnserialized['html'];
            SGL::logMessage('nav tabs from cache', PEAR_LOG_DEBUG);
        } else {
            $aSectionNodes = $this->getSectionsByRoleId();
            if (PEAR::isError($aSectionNodes)) {
                return $aSectionNodes;
            }
            $sectionId = $this->_currentSectionId;
            $html = $this->_toHtml($aSectionNodes);
            $aNav = array('sectionId' => $sectionId, 'html' => $html);
            $cache->save(serialize($aNav), $cacheId, 'adminNav');
            SGL::logMessage('nav tabs from db', PEAR_LOG_DEBUG);
        }
        return array($sectionId, $html);
        //echo'<pre>';
        //die(print_r($this->_aSectionNodes));
    }

    /**
     * Gets section nodes (that are enabled and permitted to user's _rid), determines
     * _currentSectionId. Returns array of section nodes nested with kids inside parents.
     *
     * NB Recursive
     *
     * @access  public
     * @param   int $sectionId
     * @return  array
     * @author  Andy Crain <apcrain@fuse.net>
     * @author  Demian Turner <demian@phpkitchen.com>
     */
    function getSectionsByRoleId($sectionId = 0)
    {

        $dbh = & SGL_DB::singleton();
        $query = "
            SELECT * FROM {$this->conf['table']['admin_menu']}
            WHERE parent_id = " . $sectionId . '
            ORDER BY order_id';

        $result = $dbh->query($query);

        if (DB::isError($result, DB_ERROR_NOSUCHTABLE)) {
            SGL::raiseError('The database exists, but does not appear to have any tables,
                please delete the config file from the var directory and try the install again',
                SGL_ERROR_DBFAILURE, PEAR_ERROR_DIE);
        }
        if (DB::isError($result)) {
            SGL::raiseError('Cannot connect to DB, check your credentials, exiting ...',
                SGL_ERROR_DBFAILURE, PEAR_ERROR_DIE);
        }

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

        //  shift off frontScriptName element
        //if ($this->conf['site']['frontScriptName'] != false) {
        //    array_shift($aBaseUri);
        //}


        //  find current section
        $aSectionNodes = array();
        $tmpQuerystring = $querystring;
        while ($result->fetchInto($section)) {
            if (!$section->is_enabled) {
                continue;
            }
            $querystring = $tmpQuerystring;
            //  set all defaults to false, then test
            $section->children = false;
            $section->isCurrent = false;
            $section->childIsCurrent = false;

            //  recurse if there are (potential) children--even if R - L > 1, the children might
            //  not be children for output if is_enabled != 1 or if user's _rid not in perms.
            if ($section->right_id - $section->left_id > 1) {
                $section->children = $this->getSectionsByRoleId($section->section_id);
                if (in_array($section->section_id, $this->_aParentsOfCurrentPage)) {
                    $section->childIsCurrent = true;
                }
            }
            /* Specific adminNav tests for rendering  =========================>>> */
            // set flag if user has no rights
            $section->can_view = (!in_array($this->_rid, explode(',', $section->perms)) && $section->level_id != 1 && $section->title != 'Connect') ? false : true;
            // add lang chooser
            if ($section->title == 'Choix de la langue') {
                $this->addLangSelect($section);
            }
            /* <<<====================End of Specific adminNav tests for rendering  */

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
            $conda1 = $section->resource_uri == $baseUri;
            $conda2 = $this->_staticId == 0;
            $evala = $conda1 && $conda2;

            $condb1 = $section->section_id != 0;
            $condb2 = $section->section_id == $this->_staticId;
            $evalb = $condb1 && $condb2;

            $condc1 = strpos($baseUri, 'articleview') !== false;
            $condc2 = strpos($baseUri, 'frmCatID') !== false;
            $condc3 = $section->is_static == 0;
            $evalc = $condc1 && $condc2 && $condc3;
*/
            //  a. the strings are identical and it's not a static article
            if (($section->resource_uri == $querystring)

                    //  c. we're browsing articles by category ID
                    || (strpos($querystring, 'articleview') !== false)
                        && strpos($section->resource_uri, 'articleview') !== false
                        && strpos($querystring, 'frmCatID') !== false
                        && $section->is_static == 0) {
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
                foreach ($aPieces as $v) {
                    if (!$bFlag) {
                        $tmpUri .= $v;
                        $bFlag = true;
                    } else {
                        $tmpUri .= '/' . $v;
                    }
                    //  create array of potential matches
                    if ($tmpUri == $section->resource_uri) {

                        $section->isCurrent = true;
                        $this->_currentSectionId = $section->section_id;

                        //  add parent to parentsOfCurrentPage array
                        $this->_aParentsOfCurrentPage[] = $section->parent_id;
                        break;
                    }
                }
            }
            //  add section node to nodes array, only if it is enabled, ie:
            //  $this->_currentSectionId may have been set, even if tab is not to be shown
            if ($section->is_enabled) {
                $aSectionNodes[$section->section_id] = $section;
            }
            //if ($section->section_id == 5) {echo'<pre>';die(print_r($section));};
        }
        return $aSectionNodes;
    }

    function addLangSelect(& $section) {
        // add lang selector
        $currLang = $_SESSION['aPrefs']['language'];
        $currUrl = 'http://' . $_SERVER['SERVER_NAME'];
        $currUrl .= rtrim($_SERVER['PHP_SELF'], '/') . '/';
        if ($pos = stristr($currUrl, '/lang/')) {
            $currUrl = str_replace($pos, '/', $currUrl);
                }
        $lang[1]->title = 'Français';
        $lang[1]->resource_uri = $currUrl . 'lang/fr-iso-8859-1/';
        $lang[1]->has_link = 1;
        $lang[1]->can_view = 1;
        $lang[2]->title = 'English';
        $lang[2]->resource_uri = $currUrl . 'lang/en-iso-8859-15/';
        $lang[2]->has_link = 1;
        $lang[2]->can_view = 1;
        foreach ($lang as $item) {
            if (strstr($item->resource_uri, $currLang)) {
                $item->checked = true;
            }
        }
        $section->children = $lang;
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
        $conf = & $GLOBALS['_SGL']['CONF'];
        foreach ($sectionNodes as $section) {
            if (isset($section->can_view) && !$section->can_view) {
                continue;
            }
            $liAtts = '';
            $aAtts = '';
            if (isset($section->isCurrent) && $section->isCurrent) {
                $liAtts      = ' current';
            }
            if (isset($section->children) && $section->children && $section->level_id != 1) {
                $aAtts      .= ' deroule';
            }
            if (isset($section->checked) && $section->checked) {
                $aAtts      .= ' checked';
            }
            if (isset($section->can_view) && !$section->can_view) {
                $aAtts      .= ' unauth';
            }
            if ($section->title == 'Connect' && $this->_rid != 0) {
                $aAtts      .= ' unauth';
            }
            // A revoir il faut tester si l'utilisateur est connecté
            if ($section->title == 'Exit' && $this->_rid == 0) {
                $aAtts      .= ' unauth';
            }
            if (!empty($liAtts)) $liAtts = " class=\"$liAtts\"";
            if (!empty($aAtts)) $aAtts = " class=\"$aAtts\"";
            if(!strstr($section->resource_uri, '//')) {
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

                $url = SGL_Url::makeLink('', $managerName, $moduleName) . $qs;
            } else {
                $url = $section->resource_uri;
            }
            //  extract anchor and place at end if exists
            if (stristr($url, '#')) {
                $anchorStart = strpos($url, '#');
                list(,$anchorFragment) = split('#', $url);
                $anchorOffset = (strpos($anchorFragment, '&')) + 1;
                $anchorEnd = $anchorStart + $anchorOffset;
                $namedAnchor = substr($url, $anchorStart, $anchorOffset);

                //  remove anchor
                $url = str_replace($namedAnchor, '', $url);

                //  place anchor at end
                $url .= $namedAnchor;
            }
            // Pour chercher un Url alias
            $aUrl = explode('/',$url);
            SGL_Url::removeSessionInfo($aUrl);
            $url = implode('/',$aUrl);
            //$url = SGL_Url::getAliasFromDestination($url); REDO THIS WHEN URL ALIASING IS SET

            if (isset($section->has_link) && $section->has_link && isset($section->can_view) && $section->can_view) {
                $anchor      = '<a' . $aAtts . ' href="' . $url . '">' . $section->title . '</a>';
            } else {
                $anchor      = '<a' . $aAtts . ' href="javascript:void(0)">' . $section->title . '</a>';
            }
            $listItems  .= "<li" . $liAtts . '>' . $anchor;
            if (isset($section->children) && $section->children) {
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
        $conf = & $GLOBALS['_SGL']['CONF'];
        $dbh = & SGL_DB::singleton();
        $query = "
            SELECT  title
            FROM    {$this->conf['table']['admin_menu']}
            WHERE   section_id = " . $this->_currentSectionId;

        $sectionName = $dbh->getOne($query);
        return $sectionName;
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
