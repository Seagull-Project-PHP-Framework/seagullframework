<?php
require 'Sitemap.php';

/**
 * A manager to build SITEMAP 0.9 compliant XML.
 *
 * @package seagull
 * @author Laszlo Horvath <pentarim@gmail.com>
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SitemapMgr extends SGL_Manager
{
    public function __construct()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->masterTemplate = 'masterFeed.html';
        $this->template       = 'sitemap.xml';

        $this->_aActionsMapping = array(
            'list' => array('list'),
        );
    }

    public function validate(SGL_Request $req, SGL_Registry $input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated       = true;
        $input->masterTemplate = $this->masterTemplate;
        $input->template       = $this->template;
        $input->action         = $req->get('action')
            ? $req->get('action') : 'list';
    }

    /**
     * Generate a sitemap with the URLs gathered by sitemap strategies.
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    public function _cmd_list(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (SGL_Config::get('SitemapMgr.strategies')) {
            $oSitemap = new SGL_Sitemap(SGL_Config::get('SitemapMgr.xmlns'));

            // add strategies
            $aStrategies = explode(',', SGL_Config::get('SitemapMgr.strategies'));
            foreach ($aStrategies as $strategyString) {

                // get strategy params
                $aParams = array();
                if (strpos($strategyString, ':') !== false) {
                    $aRawParams = explode(':', trim($strategyString));
                    $strategy = array_shift($aRawParams);
                    for ($i = 0, $cnt = count($aRawParams); $i < $cnt; $i = $i + 2) {
                        if (isset($aRawParams[$i]) && isset($aRawParams[$i + 1])) {
                            $aParams[$aRawParams[$i]] = $aRawParams[$i + 1];
                        }
                    }
                } else {
                    $strategy = trim($strategyString);
                }

                require_once "Sitemap/$strategy.php";
                $strategyClass = 'SGL_Sitemap_' . $strategy;
                if (class_exists($strategyClass)) {
                    $oSitemap->addStrategy(new $strategyClass($aParams));
                }
            }
            try {
                $oSitemap->generate();
                $output->sitemap = $oSitemap;

            // show Exception's data for now
            } catch (SGL_Sitemap_Exception $e) {
                if (!SGL_Config::get('debug.production')) {
                    $msg =  '<pre>' . $e->getMessage() . '</pre>';
                } else {
                    $msg = 'error occured';
                }
                echo $msg; exit;
            }
        }
        $output->contentType = 'text/xml';
    }
}

?>