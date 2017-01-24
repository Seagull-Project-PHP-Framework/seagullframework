<?php

/**
 * Export Seagull site to plain xHTML pages.
 *
 * @package seagull
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 *
 * USAGE:
 * php www/index.php --moduleName=siteexporter --managerName=siteexporter --action=run --url="user/login"
 */
class SiteExporterMgr extends SGL_Manager
{
    public function __construct()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->_aActionsMapping = array(
            'list'          => array('list', 'cliResult'),
            'run'           => array('run', 'cliResult'),
            'runCollection' => array('runCollection', 'cliResult'),
        );
    }

    public function validate(SGL_Request $req, SGL_Registry $input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated = true;
        $input->tty      = "\n";
        $input->action   = $req->get('action') ? $req->get('action') : 'list';

        $input->url     = $req->get('url');
        $input->baseUrl = $req->get('baseUrl');
        $input->ext     = $req->get('ext') ? $req->get('ext') : 'html';
        $input->dir     = $req->get('dir') ? $req->get('dir') : '/';
        $input->params  = $req->get('params');
    }

    public function _cmd_list(SGL_Reqistry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $input->tty = <<< HELP

Available actions:
  1. run            export single page to file
       --url          url to export
       --baseUrl      replace links' base URL with specified value
       --ext          append file extension to file
                      (default: html)
       --dir          links replacement will be limited to specified
                      subset of URLs (example: /user/)

  2. runCollection  export collection of urls to files
       --baseUrl      replace links' base URL with specified value
       --ext          append file extension to file
                      (default: html)
       --dir          links replacement will be limited to specified
                      subset of URLs (example: /user/)
       --params       pass parameters to URL collectors
                      (format: k1:v1::k2::v2)

HELP;
    }

    public function _cmd_run(SGL_Reqistry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $baseUrl = SGL_Config::get('site.baseUrl');
        $fc      = SGL_Config::get('site.frontScriptName');

        // to map file correctly on file system
        if (strpos($input->url, '%') !== false) {
            $input->url = urldecode($input->url);
        }

        // request url
        $input->url = trim($input->url, '/');
        $url = $baseUrl . '/' . ($fc ? $fc . '/' : '') . $input->url;

        // prepare save location
        $saveFile = SGL_WEB_ROOT . '/' . $input->url . '.' . $input->ext;
        $this->_ensureDirIsWriteable(dirname($saveFile));

        // do the job
        $cmd = "wget -q -O $saveFile $url";
        $ok  = `$cmd`;

        $html = file_get_contents($saveFile);
        // remove front controller from links
        if ($fc) {
            $regex = "@(<a.*? href=\")($baseUrl)/$fc({$input->dir})(.*?)\"@";
            $html = preg_replace($regex, "\\1\\2\\3\\4\"", $html);

            $html = str_replace("\"$baseUrl/$fc/\"", "\"$baseUrl\"", $html);
            $html = str_replace("\"$baseUrl/$fc\"", "\"$baseUrl\"", $html);
        }
        // replace base URL
        $html = str_replace($baseUrl, $input->baseUrl, $html);
        // add extension to all links under certain dir
        $regex = "@(<a.*? href=\")({$input->baseUrl}{$input->dir}.*?)/?\"@";
        $html = preg_replace($regex, "\\1\\2.{$input->ext}\"", $html);
        file_put_contents($saveFile, $html);

        // output
        $input->tty .= "Exported to $saveFile\n";

        $this->_flush($input->tty);
    }

    public function _cmd_runCollection(SGL_Reqistry $input, SGL_Output $output)
    {
        $aUrls   = array();
        $aParams = $this->_parseParams($input->params);
        if (SGL_Config::get('SiteExporterMgr.strategies')) {

            // collect urls
            $aCollectors = explode(',', SGL_Config::get('SiteExporterMgr.strategies'));
            $oCollection = new SGL_UrlCollection();
            foreach ($aCollectors as $collectorName) {
                $collectorName = trim($collectorName);

                // get path to UrlCollector class
                $aPath = explode('_', $collectorName);
                if ($aPath[0] == 'SGL') {
                    $strategyFile = SGL_MOD_DIR
                        . '/siteexporter/lib/UrlCollector/'
                        . array_pop($aPath) . '.php';
                } else {
                    $strategyFile = implode(DIRECTORY_SEPARATOR, $aPath) . '.php';
                }

                require_once $strategyFile;
                $oCollection->add(new $collectorName($aParams));
            }
            $aUrls = $oCollection->retrieve();
        }

        // export
        foreach ($aUrls as $url) {
            $input->url = $url;
            $this->_cmd_run($input, $output);
        }
    }

    /**
     * Action, which outputs CLI result.
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    public function _cmd_cliResult(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $input->tty .= "\n";
        $this->_flush($input->tty, $stopScript = true);
    }

    /**
     * Send data to terminal.
     *
     * @param string $string
     * @param boolean $stopScript
     */
    private function _flush(&$string, $stopScript = false)
    {
        echo $string;
        flush();
        $string = '';
        if ($stopScript) {
            exit;
        }
    }

    private function _ensureDirIsWriteable($dir)
    {
        if (!is_writeable($dir)) {
            require_once 'System.php';
            $ok = System::mkDir(array('-p', $dir));
            $mask = umask(0);
            chmod($dir, 0777);
            umask($mask);
        }
    }

    private function _parseParams($paramString)
    {
        $aRet = array();
        if (!empty($paramString)) {
            $aParams = explode('::', $paramString);
            foreach ($aParams as $paramKeyValue) {
                $aVar = explode(':', $paramKeyValue);
                if (isset($aVar[1])) {
                    $aRet[$aVar[0]] = $aVar[1];
                }
            }
        }
        return $aRet;
    }
}

/**
 * @package SGL
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_UrlCollection
{
    private $_aCollectors = array();

    public function add(SGL_UrlCollector $oCollector)
    {
        $this->_aCollectors[] = $oCollector;
    }

    public function retrieve()
    {
        $aRet = array();
        foreach ($this->_aCollectors as $oCollector) {
            $aUrl = $oCollector->generate();
            $aRet = array_merge($aRet, $aUrl);
        }
        return $aRet;
    }
}
?>