<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2008, Demian Turner                                         |
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
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | Rss2Mgr.php                                                                |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+

define('SGL_FEED_RSS_VERSION', '2.0');
define('SGL_FEED_ITEM_LIMIT', 10);
define('SGL_FEED_ITEM_LIMIT_MAXIMUM', 50);
define('SGL_ITEM_TYPE_ARTICLE_HTML', 2);
define('SGL_ITEM_TYPE_ARTICLE_NEWS', 4);
define('SGL_CATEGORY_NEWS_ID', 1);


require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';

/**
 * A class to build RSS 2.0 compliant export.
 *
 */
class Rss2Mgr extends SGL_Manager
{
    public $feed;

    function __construct()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->masterTemplate  = 'masterFeed.html';
        $this->template = 'masterRss.xml';
        $this->_aActionsMapping = array(
            'list' => array('list'),
            );
        $this->da = UserDAO::singleton();

        $this->feed = new SGL_Feed();
        $this->feed->xml_version    = "1.0";
        $this->feed->xml_encoding   = "utf-8";
        $this->feed->rss_version    = SGL_FEED_RSS_VERSION;
        $this->feed->docs           = 'http://blogs.law.harvard.edu/tech/rss';
        $this->feed->title          = $this->conf['RssMgr']['feedTitle'];
        $this->feed->description    = $this->conf['RssMgr']['feedDescription'];
        $this->feed->copyright      = $this->conf['RssMgr']['feedCopyright'];
        $this->feed->managingeditor = $this->conf['RssMgr']['feedEmail'] . " (" . $this->conf['RssMgr']['feedEditor'] . ")";
        $this->feed->webmaster      = $this->conf['RssMgr']['feedEmail'] . " (" . $this->conf['RssMgr']['feedWebmaster'] . ")";
        $this->feed->ttl            = $this->conf['RssMgr']['feedRssTtl'];
        $this->feed->link           = $this->conf['RssMgr']['feedUrl'];
        $this->feed->syndicationurl = $this->conf['RssMgr']['feedSyndicationUrl'];
//        $this->feed->lastbuilddate  = $this->datetime2Rfc2822();
        $this->feed->pubdate        = $this->datetime2Rfc2822();
        $this->feed->generator      = 'Seagull RSS Manager';

/*        $image               = new stdClass();
        $image->url          = ;
        $image->title        = ;
        $image->link         = ;
        $image->width        = ""; # Maximum value for width is 144, default value is 88.
        $image->height       = ""; # Maximum value for height is 400, default value is 31.
        $image->description  = ;
        $this->feed->image   = $image;*/

        #$this->feed->mrss["ns"] = 'xmlns:media="http://search.yahoo.com/mrss"';
        #$this->feed->itunes["ns"] = 'xmlns:itunes="http://www.itunes.com/DTDs/Podcast-1.0.dtd"';
    }


    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template    = $this->template;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->limit       = ($req->get('limit')) ? $req->get('limit') : 10;

        $input->datasrc     = $req->get('datasrc');
        $input->contenttype = $req->get('contenttype');
        $input->module      = $req->get('module');
        $input->method      = $req->get('method');
        $input->aParams     = null;
        if (!is_null($input->method)) {
            $oUrl = $input->getCurrentUrl();
            $aAllParams = $oUrl->getQueryData($strict = true);
            unset($aAllParams['moduleName']);
            unset($aAllParams['managerName']);
            unset($aAllParams['action']);
            unset($aAllParams['datasrc']);
            unset($aAllParams['contenttype']);
            unset($aAllParams['module']);
            unset($aAllParams['method']);
            if (count($aAllParams)) {
                $input->aParams = $aAllParams;
            }
        }
        return $input;
    }


    function getContent($aArgs)
    {
        if ($aArgs['datasrc'] == 'cms') {
            require_once SGL_MOD_DIR . '/cms/classes/Content.php';
            require_once SGL_MOD_DIR . '/cms/classes/Finder.php';

            //  user reviews by product
    //        $attribFilter = array(
    //            'name'     => 'productId',
    //            'value'    => $input->productId,
    //            'operator' => '='
    //        );
            $aContents = SGL_Finder::factory('content')
                ->addFilter('typeId', $aArgs['contenttype'])
                ->addFilter('sortOrder', 'ASC')
                //->addFilter('attribute', $attribFilter)
#FIXME: only show content of type 'approved'
                ->retrieve();
            return $aContents;
        } else {
            //  dynamically loading DAO
            $className = ucfirst($aArgs['module']) . 'DAO';
            $path = SGL_MOD_DIR . "/{$aArgs['module']}/classes/$className.php";
            require_once $path;
            $obj = new $className();
            $method = $aArgs['method'];

            //  only param values needed, not keys
            if (isset($aArgs['aParams'])) {
                if (count($aArgs['aParams'])) {
                    $aArgs['aParams'] = array_values($aArgs['aParams']);
                }
                //  create comma separated arg list
                $args = implode(",", $aArgs['aParams']);

                //  execute dynamic DAO call
                eval("\$ret = \$obj->$method($args);");
            } else {
                //  or without args
                eval("\$ret = \$obj->$method();");
            }
            return $ret;
        }
    }

    function buildConfigKey($aArgs)
    {
        if ($aArgs['datasrc'] == 'cms') {
            $qs = 'contenttype/' . $aArgs['contenttype'];
        } else {
            $qs = 'module/' . $aArgs['module'];
            $params = (isset($aArgs['aParams']))
                ? implode('/', $aArgs['aParams'])
                : '';
            $qs .= (strlen($params))
                ? "/method/{$aArgs['method']}/$params"
                : "/method/{$aArgs['method']}";
        }
        $key = "/datasrc/{$aArgs['datasrc']}/$qs";
        return $key;
    }

    /**
     *
     * Generate RSS data.
     *
     * @param   object      $input
     * @param   object      $output
     *
     * @return  string      XML
     */
    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'masterRss.xml';
        $this->feed->category[]["content"] = $this->conf['RssMgr']['feedCategory'];

        //$limit = $this->normalizeLimit($input->limit);

        //  build args
        $aArgs['datasrc'] = $input->datasrc;
        if (!is_null($input->contenttype)) {
            $aArgs['contenttype'] = $input->contenttype;
        }
        if (!is_null($input->module)) {
            $aArgs['module'] = $input->module;
        }
        if (!is_null($input->method)) {
            $aArgs['method'] = $input->method;
        }
        if (!is_null($input->aParams)) {
            $aArgs['aParams'] = $input->aParams;
        }
        //  build config key
        $key = $this->buildConfigKey($aArgs);

        if (!isset($this->conf[$key])) {
            SGL::raiseError('Rss datasrc config key not found');
            $output->contentType = 'text/html';
            return false;
        }
        //  get raw data
        $aContent = $this->getContent($aArgs);

        //  map fields
        $title = $this->conf[$key]['title'];

        //  FIXME: routine to merge data fields into description
        $desc  = $this->conf[$key]['description'];

        if (isset($this->conf[$key]['createdBy'])) {
            $owner = $this->conf[$key]['createdBy'];
        }
        if (isset($this->conf[$key]['lastUpdated'])) {
            $lastUpdated = $this->conf[$key]['lastUpdated'];
        }

        if (($aContent !== false) && (!empty($aContent))) {
            foreach ($aContent as $oContent) {
                $item = array();
                $item["title"]           = $oContent->$title;
                if ($input->datasrc == 'cms') {
                    $link = SGL_Output::makeUrl('view','contentview','cms', array(), "frmContentId|{$oContent->id}");
                } else {
                    $oUrl = $input->getCurrentUrl();
                    $link = $oUrl->toString();
                }
                $item["link"]            = $link;
                $item["description"]     = SGL_String::summariseHtml($oContent->$desc);

                //  get author fullname
                $createdById = (isset($owner))
                    ? $oContent->$owner
                    : $oContent->createdById; // cms only
                $oUser = $this->da->getUserById($createdById);
                $fullName = $oUser->first_name . ' ' . $oUser->last_name;
                $author_name             = $fullName;
                $item["author"]          = $this->conf['RssMgr']['feedEmail'] . " ($author_name)";
                $item["source"]["url"]   = '';
                $item["source"]["content"] = '';
                $item["guid"]["bool"]    = "true";
                $item["guid"]["permalink"] = $item["link"];
                $item["comments"]        = $item["link"];
                if (!isset($lastUpdated) && !isset($oContent->lastUpdated)) {
                    $lastUpdated = '';
                } elseif(isset($oContent->lastUpdated)) {
                    $lastUpdated = $oContent->lastUpdated;
                }
                $item["pubdate"]         = $this->datetime2Rfc2822($lastUpdated);

                $this->feed->items[] = $item;
            }
            // Set the pubDate to the release date of the newest item
            $this->feed->pubdate = $this->feed->items[0]["pubdate"];
        }
        //  set content type for header generation
        $output->contentType = 'text/xml';
        $output->feed = $this->feed;
    }

    function datetime2Rfc2822($date = "now")
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (strlen($date) != 19) {
            return date("r");
        }
        return date("r", strtotime($date));
    }

    function normalizeLimit($limit = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if ((strtolower($limit) == "all") || ($limit > SGL_FEED_ITEM_LIMIT_MAXIMUM)) {

            //   Keep the transferred data limited
            $limit = SGL_FEED_ITEM_LIMIT_MAXIMUM;
        } elseif (is_int($limit) === true) {
            $limit = $limit;
        } else {
            $limit = SGL_FEED_ITEM_LIMIT;
        }
        return $limit;
    }
}

class SGL_Feed
{
    var $xml_version;
    var $xml_encoding;
    var $rss_version;
    var $docs;
    var $title;
    var $description;
    var $copyright;
    var $managingeditor;
    var $webmaster;
    var $category = array();
    var $ttl;
    var $link;
    var $syndicationurl;
    var $generator;
    var $lastbuilddate;
    var $pubdate;
    var $image;
    var $mrss = array();
    var $itunes = array();
}
?>