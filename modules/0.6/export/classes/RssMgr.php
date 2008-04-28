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
// | RssMgr.php                                                                |
// +---------------------------------------------------------------------------+
// | Authors:   Fabio Bacigalupo <seagull@open-haus.de>                        |
// |            Demian Turner <demian@phpkitchen.com>                          |
// +---------------------------------------------------------------------------+
// $Id: RssMgr.php,v 1.4 2005/06/23 18:21:25 demian Exp $

require_once SGL_CORE_DIR . '/Item.php';

define('SGL_FEED_RSS_VERSION', '2.0');
define('SGL_FEED_ITEM_LIMIT', 10);
define('SGL_FEED_ITEM_LIMIT_MAXIMUM', 50);
define('SGL_ITEM_TYPE_ARTICLE_HTML', 2);
define('SGL_ITEM_TYPE_ARTICLE_NEWS', 4);
define('SGL_CATEGORY_NEWS_ID', 1);

/**
 * A class to build RSS 2.0 compliant export.
 *
 */
class RssMgr extends SGL_Manager
{
    var $feed;

    function RssMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->masterTemplate  = 'masterFeed.html';
        $this->template = 'masterRss.xml';

        $this->_aActionsMapping = array(
            'news' => array('news'),
            );

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
        $input->action      = ($req->get('action')) ? $req->get('action') : 'news';
        $input->limit       = ($req->get('limit')) ? $req->get('limit') : 10;
        return $input;
    }


    /**
     *
     * Generate a RSS feed with the latest news from the startpage.
     *
     * @param   object      $input
     * @param   object      $output
     *
     * @return  string      XML
     */
    function _cmd_news(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'masterRss.xml';
        $this->feed->category[]["content"] = $this->conf['RssMgr']['feedCategory'];

        $limit = $this->normalizeLimit($input->limit);
        $res = $this->getNews($limit);

        if (($res !== false) && (!empty($res))) {
            foreach ($res as $article) {
                $item = array();
                $item["title"]           = $article["title"];
                $item["link"]            = SGL_Output::makeUrl('view','articleview','publisher', array(),
                                            "frmArticleID|{$article["id"]}");
                $item["description"]     = SGL_String::summariseHtml($article["description"]);# .
                                            #" " . SGL_String::translate("Read more");
                $author_name             = (!empty($article["fullname"]))
                                            ? " (" . $article["fullname"] . ")"
                                            : " (" . $article["username"] . ")";
                $item["author"]          = $this->conf['RssMgr']['feedEmail'] . $author_name;
                $item["source"]["url"]   = '';
                $item["source"]["content"]   = '';
                $item["guid"]["bool"]    = "true";
                $item["guid"]["permalink"] = $item["link"];
                $item["comments"]        = $item["link"];
                $item["pubdate"]         = $this->datetime2Rfc2822($article["issued"]);

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

    /**
     *Fetch news used for feeds
     *
     * @param   int     $limit
     */
    function getNews($limit = 10)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $cache    = &SGL_Cache::singleton();

        $hasCache = false;
        if ($data = $cache->get('rss', 'export')) {
            $aRes = unserialize($data);

            //  check if stored last_updated equals last_updated in db return cache
            $hasCache = ($aRes['last_updated'] == $this->getLastUpdated()) ? true : false;

            unset($aRes['last_updated']);
        }

        if (!$hasCache) {
            $query = "
                    SELECT  i.item_id AS id,
                            i.date_created AS created,
                            i.last_updated AS modified,
                            i.start_date AS issued,
                            ia.addition AS title,
                            ia2.addition AS description,
                            u.username AS username,
                            CONCAT(first_name, ' ', last_name) AS fullname
                    FROM
                            {$this->conf['table']['item']} i,
                            {$this->conf['table']['item_type']} it,
                            {$this->conf['table']['item_addition']} ia,
                            {$this->conf['table']['item_addition']} ia2,
                            {$this->conf['table']['item_type_mapping']} itm,
                            {$this->conf['table']['item_type_mapping']} itm2,
                            {$this->conf['table']['user']} u
                    WHERE   ia.item_type_mapping_id = itm.item_type_mapping_id
                    AND     i.created_by_id = u.usr_id
                    AND     ia2.item_type_mapping_id = itm2.item_type_mapping_id
                    AND     i.item_id = ia.item_id
                    AND     i.item_id = ia2.item_id
                    AND     it.item_type_id = itm.item_type_id
                    AND     itm.field_type <> itm2.field_type
                    AND     it.item_type_id = ?
                    AND     i.start_date < ?
                    AND     (i.expiry_date  > ? OR i.expiry_date IS NULL)
                    AND     i.status  = ?
                    GROUP BY i.item_id
                    ORDER BY i.date_created DESC
                    LIMIT 0, ?
            ";
            $aRes = $this->dbh->getAll($query, array(
                SGL_ITEM_TYPE_ARTICLE_HTML,
                SGL_Date::getTime(),
                SGL_Date::getTime(),
                SGL_STATUS_PUBLISHED,
                $limit), DB_FETCHMODE_ASSOC);

            if (DB::isError($aRes)) {
                SGL::raiseError('problem getting news: ' .
                    $aRes->getMessage(), SGL_ERROR_NOAFFECTEDROWS);
                return false;
            }

            //  add last_updated key/value
            $aCache = $aRes;
            $aCache['last_updated'] = $this->getLastUpdated();

            //  cache data
            $data = serialize($aCache);
            $cache->save($data, 'rss', 'export');

            SGL::logMessage('RSS news from db', PEAR_LOG_DEBUG);
        } else {
            SGL::logMessage('RSS news from cache', PEAR_LOG_DEBUG);
        }
        return $aRes;
    }

    function getLastUpdated()
    {
        $dbh = SGL_DB::singleton();
        $query = "SELECT MAX(last_updated)
                    FROM {$this->conf['table']['item']}
                    WHERE status = ". SGL_STATUS_PUBLISHED;
        $result = $dbh->getOne($query);
        return $result;
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