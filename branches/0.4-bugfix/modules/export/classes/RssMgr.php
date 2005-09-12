<?php
require_once SGL_CORE_DIR . '/Item.php';

define('SGL_FEED_RSS_VERSION', "1.0");
define('SGL_FEED_COPYRIGHT', 'Seagull Systems');
define('SGL_FEED_EMAIL', 'seagull@phpkitchen.com');
define('SGL_FEED_EDITOR', 'Demian Turner');
define('SGL_FEED_WEBMASTER', 'demian@phpkitchen.com');
define('SGL_FEED_RSS_TTL', 21600); //   4 times/day
define('SGL_PODCAST_URL', 'http://example.com');
define('SGL_FEED_ITEM_LIMIT', 10);
define('SGL_FEED_ITEM_LIMIT_MAXIMUM', 50);
define('ITEM_TYPE_ARTICLE_HTML', 2);
define('CATEGORY_NEWS_ID', 1);

/**
 *
 *
 * @author Fabio Bacigalupo
 *
 *
 *
 * @todo Things that are commented out need to be checked for reasonable values
 * 
 */
class RssMgr extends SGL_Manager 
{
    var $feed;

    function RssMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $this->module   = 'export';
        $this->masterTemplate  = 'masterFeed.html';
        $this->template = 'masterRss.xml';
        
        $this->_aActionsMapping = array(
            'news' => array('news'),
            );       
        
        $this->feed = new SGL_Feed();
        $this->feed->xml_version    = "1.0";
        $this->feed->xml_encoding   = "utf-8";
        $this->feed->rss_version    = SGL_FEED_RSS_VERSION;
        $this->feed->docs = 'http://blogs.law.harvard.edu/tech/rss';
        $this->feed->title          = "RSS Feed";
        $this->feed->description    = null;
        $this->feed->copyright      = SGL_FEED_COPYRIGHT;
        $this->feed->managingeditor = SGL_FEED_EMAIL . " (" . SGL_FEED_EDITOR . ")";
        $this->feed->webmaster      = SGL_FEED_EMAIL . " (" . SGL_FEED_WEBMASTER . ")";
        $this->feed->category[]["content"] = "Podcasting";
        $this->feed->ttl            = SGL_FEED_RSS_TTL;
        $this->feed->link           = SGL_PODCAST_URL;
        $this->feed->syndicationurl = SGL_PODCAST_URL . "/" . $_SERVER["PHP_SELF"];
        $this->feed->lastbuilddate  = $this->datetime2Rfc2822();
        $this->feed->pubdate        = $this->datetime2Rfc2822();
        

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
        $input->masterTemplate    = $this->masterTemplate;
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
    function _news(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $output->template = 'masterRss.xml';
        $this->feed->title = 'RSS Title';
        $this->feed->description = 'here is the description';
        $this->feed->category[]["content"] = "News";
        
        $limit = $this->normalizeLimit($input->limit);
        $res = $this->getNews($limit);
        
        if (($res !== false) && (!empty($res))) {
            foreach ($res as $article) {
                $item = array();
                $item["title"]           = $article["title"];
                $item["link"]            = SGL_Output::makeUrl('list','default','default', array(),
                                            "frmItemID|{$article["id"]}");
                $item["description"]     = SGL_String::summariseHtml($article["description"]);# . 
                                            #" " . SGL_String::translate("Read more");
                $author_name             = (!empty($article["fullname"])) 
                                            ? " (" . $article["fullname"] . ")" 
                                            : " (" . $article["username"] . ")";
                $item["author"]          = SGL_FEED_EMAIL . $author_name;
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
        header("Content-Type: text/xml");
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

        if ((strtolower($limit) == "all") 
           or ($limit > SGL_FEED_ITEM_LIMIT_MAXIMUM)) {
            $limit = SGL_FEED_ITEM_LIMIT_MAXIMUM; 
           //   Keep the transferred data limited
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
         $dbh = & SGL_DB::singleton();
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
            array(ITEM_TYPE_ARTICLE_HTML, SGL::getTime(), 
                SGL::getTime(), SGL_STATUS_PUBLISHED, CATEGORY_NEWS_ID, $limit),
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
    var $lastbuilddate;
    var $pubdate;
    var $image;
    var $mrss = array();
    var $itunes = array();
}
?>