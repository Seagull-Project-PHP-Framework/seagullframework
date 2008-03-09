<?php

/**
 * Export Seagull site to plain xHTML pages.
 *
 * @package seagull
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
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
    }

    public function _cmd_list(SGL_Reqistry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $input->tty = <<< HELP

Available actions:
  1. run            export single page to file
       --url          url to export
       --baseUrl      replace page's base url with specified value
       --ext          file extension, html is default
       --dir          limit to certain directory
  2. runCollection  export the arbitrary number of pages to files


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

            /*
            $html = str_replace(
                $baseUrl . '/' . $fc . $input->dir,
                $baseUrl . $input->dir,
                $html
            );
            */
        }
        // replace base URL
        $html = str_replace($baseUrl, $input->baseUrl, $html);
        // add extension to all links under certain dir
        $regex = "@(<a.*? href=\")({$input->baseUrl}{$input->dir}.*?)/?\"@";
        $html = preg_replace($regex, "\\1\\2.{$input->ext}\"", $html);
        file_put_contents($saveFile, $html);

        // output
        $input->tty .= "Exported to $saveFile\n";
        if ($input->action == 'run') {
            $input->tty .= "\n";
        }

        $this->_flush($input->tty);
    }

    public function _cmd_runCollection(SGL_Reqistry $input, SGL_Output $output)
    {
        // collect urls
        $aCollectors = explode(',', SGL_Config::get('SiteExporterMgr.urls'));
        $oCollector  = new SGL_UrlCollector();
        foreach ($aCollectors as $collectorName) {
            require_once dirname(__FILE__) . "/UrlCollector/$collectorName.php";
            $className = 'SGL_UrlCollector_' . $collectorName;
            $oCollector->add(new $className());
        }
        $aUrls = $oCollector->retrieve();

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

        $this->_flush($input->tty);
        exit;
    }

    /**
     * Send data to terminal.
     *
     * @param string $string
     */
    private function _flush(&$string)
    {
        echo $string;
        flush();
        $string = '';
    }

    private function _ensureDirIsWriteable($dir)
    {
        if (!is_writeable($dir)) {
            require_once 'System.php';
            System::mkDir($dir);
            $mask = umask(0);
            chmod($dir, 0777);
            umask($mask);
        }
    }
}

/**
 * @package seagull
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_UrlCollector
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