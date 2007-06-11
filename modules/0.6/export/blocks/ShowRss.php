<?php

/**
 * A block to dislay an RSS feed.
 *
 * @package    seagull
 * @subpackage export
 * @author     Demian Turner <demian@phpkitchen.com>
 * @author     Werner M. Krauss <werner@seagullproject.org>
 */
class Export_Block_ShowRss
{
    var $moduleName  = 'export';
    var $itemsToShow = 5;
    var $template;

    function init(&$output, $block_id, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        return $this->getBlockContent($output, $block_id, $aParams);
    }

    function getBlockContent(&$output, $block_id, &$aParams)
    {
        if (ini_get('safe_mode') || !ini_get('allow_url_fopen')) {
            return 'Cannot request remote feed with safe_mode on or allow_url_fopen off';
        }

        //  set block params
        if (array_key_exists('rssSource', $aParams)) {
            $rssSource = $aParams['rssSource'];
        } else {
            return false;
        }
        if (!empty($aParams['template'])) {
            $this->template = $aParams['template'];
        }
        if (!empty($aParams['itemsToShow'])) {
            $this->itemsToShow = $aParams['itemsToShow'];
        }

        $cache = &SGL_Cache::singleton($force = true);
        if ($data = $cache->get('sglSiteRss' . $block_id, 'blocks')) {
            $html = unserialize($data);
            SGL::logMessage('rss from cache', PEAR_LOG_DEBUG);
        } else {
            require_once "XML/RSS.php";
            $rss = & new XML_RSS($rssSource);
            $rss->parse();
            if (!$this->template) {
                $html = $this->_renderDefault($rss->getItems());
            } else {
                $html = $this->_renderTemplate(
                    array_slice($rss->getItems(), 0, $this->itemsToShow),
                    $output);
            }
            $cache->save(serialize($html), 'sglSiteRss' . $block_id, 'blocks');
            SGL::logMessage('rss from remote', PEAR_LOG_DEBUG);
        }
        return $html;
    }

    function _renderDefault($aItems)
    {
        $html = "<ul class='noindent'>\n";
        $x = 0;
        foreach ($aItems as $item) {
            $html .= "<li><a href=\"" . $item['link'] . "\">" .
                $item['title'] . "</a></li>\n";
            $x++;
            if ($x >= $this->itemsToShow) {
                break;
            }
        }
        $html .= "</ul>\n";
        return $html;
    }

    function _renderTemplate($aItems, &$output)
    {
        $blockOutput            = new SGL_Output();
        $blockOutput->theme     = $_SESSION['aPrefs']['theme'];
        $blockOutput->isAdmin   = $output->isAdmin();
        $blockOutput->imagesDir = $output->imagesDir;
        $blockOutput->aItems    = $aItems;
        return $this->process($blockOutput);
    }

    function process(&$output)
    {
        $output->moduleName     = $this->moduleName;
        $output->masterTemplate = $this->template;

        $view = new SGL_HtmlSimpleView($output);
        return $view->render();
    }
}
?>