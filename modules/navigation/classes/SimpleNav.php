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
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | SimpleNav.php                                                             |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: SimpleNav.php,v 1.43 2005/06/20 23:28:37 demian Exp $

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
     * www root
     *
     * @access  private
     * @var     string
     */
    var $_webRoot = SGL_BASE_URL;

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
     * Holds section id(s) of section(s) nested below which is the current page.
     *
     * @access  private
     * @var     array
     */
    var $_aParentsOfCurrentPage = array();
    
    function SimpleNav($input)
    {
        $this->_rid = (int)SGL_HTTP_Session::get('rid');
        
        //  get a reference to the request object
        $req = & SGL_Request::singleton();
        
        $key = $req->get('staticId');
        $this->_staticId = (is_null($key)) ? 0 : $key;
        $this->input = $input;
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();
        $this->dbh = & SGL_DB::singleton();
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
        //FIXME: bad hack
        if (isset($this->input->data)) {
            $url = $this->input->data->get('currentUrl');    
        } else {
            $url = $this->input->get('currentUrl');
        }
#$url = $this->input->data->get('currentUrl');            
        
        $cacheId = $url->getQueryString() . $this->_rid . $this->_staticId;
        if ($data = $cache->get($cacheId, 'nav')) {
            $aUnserialized = unserialize($data);
            $sectionId = $aUnserialized['sectionId'];
            $html = $aUnserialized['html'];
            SGL::logMessage('nav tabs from cache', PEAR_LOG_DEBUG);
        } else {
            $aSectionNodes = $this->getSectionsByRoleId();
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
        $query = "
            SELECT * FROM {$this->conf['table']['section']}
            WHERE parent_id = " . $sectionId . '
            ORDER BY order_id';

        $result = $this->dbh->query($query);
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
        $aQueryData = $url->getQueryData();

        // replace +'s in array elements with spaces
        $aQueryData = array_map(create_function('$a', 'return str_replace("+", " ", $a);'), 
            $aQueryData);
        
        //  temporarily remove session info
        SGL_Url::removeSessionInfo($aQueryData);

        //  return to string
        $baseUri = implode('/', $aQueryData);
         
        //  find current section  
        $aSectionNodes = array();
        $tmpBaseUri = $baseUri;
        while ($result->fetchInto($section)) {
            if (!(in_array($this->_rid, explode(',', $section->perms)))) {
                continue;
            }
            //  get orig $baseUri value for each iteration
            $baseUri = $tmpBaseUri;
            
            //  set all defaults to false, then test
            $section->children = false;
            $section->isCurrent = false;
            $section->childIsCurrent = false;
            
            //  if we're scraping a wikipage, set the title in the request
            if (preg_match("@^publisher/wikiscrape/url@", $section->resource_uri)) {
                $req = & SGL_Request::singleton();
                $req->set('articleTitle', $section->title);
            }

            //  recurse if there are (potential) children--even if R - L > 1, the children might
            //  not be children for output if is_enabled != 1 or if user's _rid not in perms.
            if ($section->right_id - $section->left_id > 1) {
                $section->children = $this->getSectionsByRoleId($section->section_id);
                if (in_array($section->section_id, $this->_aParentsOfCurrentPage)) {
                    $section->childIsCurrent = true;
                }
            }
            //  first check if querystring is a simplified version of section name,
            //  ie, if we have example.com/index.php/faq instead of example.com/index.php/faq/faq
            if (SGL_Inflector::isUrlSimplified($baseUri, $section->resource_uri)) {

                //  module name and manager name are identical, temporarily unshorten
                //  querystring name so match can be possible
                $aParts = explode('/', $baseUri);
                $moduleName = $aParts[0];
                array_unshift($aParts, $moduleName);

                //  return to string
                $baseUri = implode('/', $aParts);    
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
            if (($section->resource_uri == $baseUri && $this->_staticId == 0 ) 
            
                    //  b. it is a static article, so staticId must be non-zero
                    || ($section->section_id != 0 && $section->section_id == $this->_staticId)
                    
                    //  c. we're browsing articles by category ID
                    || (strpos($baseUri, 'articleview') !== false)
                        && strpos($section->resource_uri, 'articleview') !== false                    
                        && strpos($baseUri, 'frmCatID') !== false
                        && $section->is_static == 0) {
                $section->isCurrent = true;
                $this->_currentSectionId = $section->section_id;
                $exactMatch = true;

                //  add parent to parentsOfCurrentPage array
                $this->_aParentsOfCurrentPage[] = $section->parent_id;
            } elseif (empty($section->resource_uri)) {

                if (    $baseUri == $this->conf['site']['defaultModule']
                    || ($baseUri == $this->conf['site']['defaultModule'] . '/' .
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
                $aPieces = explode('/', $baseUri);
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
            if ($section->isCurrent) {
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
            $sectionName = $this->input->data->pageTitle;
        } else {
            $query = " 
                SELECT  title
                FROM    {$this->conf['table']['section']}
                WHERE   section_id = " . $this->_currentSectionId;
    
            $sectionName = $this->dbh->getOne($query);
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