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
// | RssMgr.php                                                                |
// +---------------------------------------------------------------------------+
// | Author: Fabio Bacigalupo <Fabio Bacigalupo <seagull@open-haus.de>         |
// +---------------------------------------------------------------------------+
// $Id: RssMgr.php,v 1.4 2005/06/23 18:21:25 demian Exp $

require_once SGL_CORE_DIR . '/Item.php';
require_once 'HTTP/Cache.php';

//define('SGL_FEED_RSS_VERSION', '1.0');
define('SGL_FEED_ITEM_LIMIT', 10);
define('SGL_FEED_ITEM_LIMIT_MAXIMUM', 50);
define('SGL_ITEM_TYPE_ARTICLE_HTML', 2);
define('SGL_CATEGORY_NEWS_ID', 1);

/**
 * A class to build RSS 1.0 compliant export.
 * @author Fabio Bacigalupo
 * 
 */
class RssMgr extends SGL_Manager 
{
    var $feed;
    var $mostRecentUpdate;

    function RssMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $this->module   = 'export';
        $this->masterTemplate  = 'masterFeed.html';
        $this->template = 'masterRss.xml';
        
        $this->_aActionsMapping = array(
            'news' => array('news'),
            );
        $this->mostRecentUpdate = $this->getMostRecentUpdateDate();
        
        #$conf = & $GLOBALS['_SGL']['CONF'];
        
//        $this->feed = new SGL_Feed();
//        $this->feed->xml_version    = "1.0";
//        $this->feed->xml_encoding   = "utf-8";
//        $this->feed->rss_version    = SGL_FEED_RSS_VERSION;
        #$this->feed->docs           = 'http://blogs.law.harvard.edu/tech/rss';
        #$this->feed->title          = $conf['RssMgr']['feedTitle'];
        #$this->feed->description    = $conf['RssMgr']['feedDescription'];
//        $this->feed->copyright      = $conf['RssMgr']['feedCopyright'];
//        $this->feed->managingeditor = $conf['RssMgr']['feedEmail'] . " (" . $conf['RssMgr']['feedEditor'] . ")";
//        $this->feed->webmaster      = $conf['RssMgr']['feedEmail'] . " (" . $conf['RssMgr']['feedWebmaster'] . ")";
//        $this->feed->ttl            = $conf['RssMgr']['feedRssTtl'];
//        $this->feed->link           = $conf['RssMgr']['feedUrl'];
//        $this->feed->syndicationurl = $conf['RssMgr']['feedSyndicationUrl'];
//        $this->feed->lastbuilddate  = $this->datetime2Rfc2822();
        #$this->feed->pubdate        = $this->datetime2Rfc2822();
        #$this->feed->generator      = 'Seagull RSS Manager';
        
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
     * Generate a RSS feed from news articles.
     *
     * @param   object      $input
     * @param   object      $output
     *
     * @return  string      XML
     */
    function _news(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $output->template = 'masterRss.xml';
        
        #$cache = &new HTTP_Cache(array('auto' => true));
        
        // create an etag
        #$etag = '"' .$this->mostRecentUpdate.'"';
        #$cache->setEtag($etag);
        
        // The browser cache is not valid
        #if (!$cache->isValid()) {
            #$data = $this->_buildXml($input);
            
            // pass it to the cache
         #   $cache->setBody($data);
        #}
        header('Content-Type: text/xml; charset=utf-8');
        session_cache_limiter('public');
        
        /*
         * Caching logic - Do not send feed if nothing has changed
         * Implementation inspired by Simon Willison 
         * [http://simon.incutio.com/archive/2003/04/23/conditionalGet], Thiemo Maettig
         */
    
        // See if the client has provided the required headers.
        // Always convert the provided header into GMT timezone to 
        // allow comparing to the server-side last-modified header
        $modified_since = !empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])
                        ? gmdate('D, d M Y H:i:s \G\M\T', 
                            $this->serverOffsetHour(strtotime(stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE'])), true))
                        : false;
        $none_match     = !empty($_SERVER['HTTP_IF_NONE_MATCH'])
                        ? str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH']))
                        : false;
    
        if ($this->mostRecentUpdate) {
            $last_modified = gmdate('D, d M Y H:i:s \G\M\T', $this->serverOffsetHour($this->mostRecentUpdate, true));
            $etag          = '"' . $last_modified . '"';
    
            header('Last-Modified: ' . $last_modified);
            header('ETag: '          . $etag);
    
            if (($none_match == $last_modified && $modified_since == $last_modified) ||
                (!$none_match && $modified_since == $last_modified) ||
                (!$modified_since && $none_match == $last_modified)) {
                header('HTTP/1.0 304 Not Modified');
                return;
            }
        }
        
        // send header or data
        $output->feed = $this->_buildXml($input);
    }
    
    function serverOffsetHour($timestamp = null, $negative = false, $serverOffsetHours = 0) 
    {
        if ($timestamp == null) {
            $timestamp = time();
        }
    
        if (empty($serverOffsetHours) 
                || !is_numeric($serverOffsetHours) 
                || $serverOffsetHours == 0) {
            return $timestamp;
        } else {
            return $timestamp + (($negative 
                ? -$serverOffsetHours 
                : $serverOffsetHours) * 60 * 60);
        }
    }

    function _buildXml(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $conf = & $GLOBALS['_SGL']['CONF'];
        
        $limit = $this->normalizeLimit($input->limit);
        $res = $this->getNews($limit);

        require_once 'XML/Serializer.php';
        
        //  RSS 2.0 format
        $options = array(
            "indent"    => "    ",
            "linebreak" => "\n",
            "typeHints" => false,
            "addDecl"   => true,
            "encoding"  => "UTF-8",
            "rootName"   => "rss",
            'rootAttributes' => array(
                'version' => '2.0',
                'xmlns:content' => 'http://purl.org/rss/1.0/modules/content/',
                'xmlns:wfw' => 'http://wellformedweb.org/CommentAPI/',
                'xmlns:dc' => 'http://purl.org/dc/elements/1.1/',
                ),
            "defaultTagName" => "item",
        );
        
        $lastUpdate = $this->getMostRecentUpdateDate();
        
        //  build data structure
        $data['channel'] = array(
            'title' => $conf['RssMgr']['feedTitle'],
            'link'  => $conf['RssMgr']['feedUrl'],
            'description' =>  $conf['RssMgr']['feedDescription'],
            'copyright' => $conf['RssMgr']['feedCopyright'],
            'lastBuildDate' => $this->datetime2Rfc2822($lastUpdate),
            'category' => $conf['RssMgr']['feedCategory'],
            'generator' => 'Seagull RSS Manager',
            'language' => 'en',
            'ttl' => $conf['RssMgr']['feedRssTtl'],
            'docs' => 'http://blogs.law.harvard.edu/tech/rss',
            );
                
        if (($res !== false) && (!empty($res))) {
            foreach ($res as $article) {
                
                $author_name  = (!empty($article["fullname"])) 
                                ? " (" . $article["fullname"] . ")" 
                                : " (" . $article["username"] . ")";
                $link = SGL_Output::makeUrl('view','articleview','publisher', array(),
                    "frmArticleID|{$article["id"]}");

                //  build items
                $data['channel'][] = array(
                    'title'       => $article['title'], 
                    'link'        => $link,
                    'description' => SGL_String::summariseHtml($article["description"]),
                    'author'      => $conf['RssMgr']['feedEmail'] . $author_name,
                    'comments'    => $link,
                    'pubDate'     => $this->datetime2Rfc2822($article["issued"]),
                );
            }
            
            $serializer = new XML_Serializer($options);
            if ($serializer->serialize($data)) {
#                header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T', strtotime($lastUpdate)));
#                header("Etag: \"" . strtotime($lastUpdate)."\"");
#                header('Cache-Control: max-age=21600');
#                #header("Pragma: ");
                return $serializer->getSerializedData();
            }
        }
    }
    
    function getMostRecentUpdateDate()
    {
         $dbh = & SGL_DB::singleton();
         $conf = & $GLOBALS['_SGL']['CONF'];
         
         $date = $dbh->getOne("SELECT MAX(last_updated) FROM {$conf['table']['item']}");
         return $date;
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
      *
      * Fetch news
      * used for feeds
      *
      * @param   int     $limit
      */
     function getNews($limit = 10)
     {
         SGL::logMessage(null, PEAR_LOG_DEBUG);

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
                         item i,
                         item_type it,
                         item_addition ia,
                         item_addition ia2,
                         item_type_mapping itm,
                         item_type_mapping itm2,
                         usr u
                 WHERE   ia.item_type_mapping_id = itm.item_type_mapping_id
                 AND     i.created_by_id = u.usr_id
                 AND     ia2.item_type_mapping_id = itm2.item_type_mapping_id
                 AND     i.item_id = ia.item_id
                 AND     i.item_id = ia2.item_id
                 AND     it.item_type_id = itm.item_type_id
                 AND     itm.field_type \!= itm2.field_type
                 AND     it.item_type_id = ?
                 AND     i.start_date < ?
                 AND     i.expiry_date  > ?
                 AND     i.status  = ?
                 AND     i.category_id = ?
                 GROUP BY i.item_id
                 ORDER BY i.date_created DESC
                 LIMIT 0, ?
             ";

         $dbh = & SGL_DB::singleton();
         $aRes = $dbh->getAll($query,
            array(SGL_ITEM_TYPE_ARTICLE_HTML, SGL::getTime(), 
                SGL::getTime(), SGL_STATUS_PUBLISHED, SGL_CATEGORY_NEWS_ID, $limit),
                DB_FETCHMODE_ASSOC);

         if (DB::isError($aRes)) {
             SGL::raiseError('problem getting news: ' . 
                $aRes->getMessage(), SGL_ERROR_NOAFFECTEDROWS, PEAR_ERROR_RETURN);
             return false;
         }

         return $aRes;
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
