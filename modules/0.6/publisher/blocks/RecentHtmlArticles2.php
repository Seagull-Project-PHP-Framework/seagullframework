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
// | RecentHtmlArticles.php                                                    |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+

/**
 * Site news block.
 *
 * @package block
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class Publisher_Block_RecentHtmlArticles2
{
    var $webRoot = SGL_BASE_URL;

    function init()
    {
        return $this->getBlockContent();
    }

    function getBlockContent()
    {
        $aResult = $this->retrieveAll();
        $newList = $this->toHtml($aResult);
        $newsContent = $newList;
        return $newsContent;
    }

    /**
     * Retrieves all published articles.
     *
     * @access  public
     * @return  array $aArticles array of article objects
     */
    function retrieveAll()
    {
        $dbh = & SGL_DB::singleton();
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        $query = "
                SELECT      i.item_id,
                            ia.addition AS title,
                            ia2.addition AS description,
                            i.start_date
                FROM    {$conf['table']['item']} i,
                        {$conf['table']['item_addition']} ia,
                        {$conf['table']['item_addition']} ia2,
                        {$conf['table']['item_type']} it,
                        {$conf['table']['item_type_mapping']} itm,
                        {$conf['table']['item_type_mapping']} itm2
                WHERE   ia.item_type_mapping_id = itm.item_type_mapping_id
                AND     ia2.item_type_mapping_id = itm2.item_type_mapping_id
                AND     it.item_type_id  = itm.item_type_id
                AND     i.item_id = ia.item_id
                AND     i.item_id = ia2.item_id
                AND     i.start_date < '" . SGL_Date::getTime() . "'
                AND     (i.expiry_date  > '" . SGL_Date::getTime() . "' OR i.expiry_date IS NULL)
                AND     itm.field_name = 'title'
                AND     it.item_type_id  = 2
                AND     itm.field_type != itm2.field_type
                AND     i.status  = " . SGL_STATUS_PUBLISHED . "
                ORDER BY i.start_date DESC
                LIMIT 5
                ";
        $aArticles = $dbh->getAll($query);

        if (!DB::isError($aArticles)) {
            return $aArticles;
        } else {
            SGL::raiseError('perhaps no item tables exist', SGL_ERROR_NODATA);
        }
    }

    function toHtml($aNewsItems)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $newItems = '';
        if (is_array($aNewsItems) && count($aNewsItems)) {
            foreach ($aNewsItems as $key => $obj) {
                $newItems   .= ''
                            .  '<a class="date" href="'
                            . SGL_Url::makeLink('view', 'articleview', 'publisher', array(), "frmArticleID|$obj->item_id") . '">'
                            . SGL_Date::formatPretty($obj->start_date) . "</a><br />"
                            . '<strong>'.$obj->title.'</strong>'
                            . '<p>'.SGL_String::summarise($obj->description, 20).'</p>';
            }
            $newItems   .= '<p><a href="'.SGL_Url::makeLink('summary', 'articleview', 'publisher').'">more ...</a></p>';
            $syndicate = <<< HTML
<p>
<a href="http://seagullsystems.com/index.php/export/rss/">
    <img src="$this->webRoot/images/xml.gif" alt="Seagull RSS" title="RSS 1.0" align="absmiddle"/>
</a>
</p>
HTML;
            $newItems .= $syndicate;
            return $newItems;
        } else {
            return 'no recent HTML Articles';
        }
    }
}
?>