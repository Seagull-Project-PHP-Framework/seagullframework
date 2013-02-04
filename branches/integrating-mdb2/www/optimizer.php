<?php

/**
 * Optimizer.
 *
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Optimizer
{
    /**
     * Was any js file modified since last request
     *
     * @var bool
     */
    var $modifiedSinceLastRequest = true;

    /**
     * Files to extract.
     *
     * @var array
     */
    var $aFiles = array();

    /**
     * Etag
     *
     * @var string
     */
    var $hash = null;

    /**
     * Content type.
     *
     * @var string
     */
    var $type = 'javascript';

    /**
     * Enable optimization and caching.
     *
     * @var boolean
     */
    var $optimize = false;

    function SGL_Optimizer()
    {
        $lastMod = 0;

        // get content type to optimize
        if (isset($_GET['type'])
                && in_array($_GET['type'], array('css', 'javascript'))) {
            $this->type = $_GET['type'];
        }

        // check if compression is enabled
        // we only support JS compression for now
        if (isset($_GET['optimize'])) {
            $this->optimize = (boolean) $_GET['optimize'];
        }

        // get files and their mod time
        if (!empty($_GET['files'])) {
            $filesString = $_GET['files'];
            $aFiles      = explode(',', $_GET['files']);
            $allowedDir  = dirname(__FILE__); // WWW dir
            $sglDir      = dirname($allowedDir);
            $aModuleDirs = glob($sglDir . '/modules*/*/www/*', GLOB_ONLYDIR);
            foreach ($aFiles as $fileName) {
                if (is_file($loadFile = $allowedDir . '/' . $fileName)) {
                    $realPath = realpath($loadFile);
                    // check if file is located in WWW dir
                    $allow = strpos($realPath, $allowedDir) === 0;
                    // check if file is symlinked from modules WWW dir
                    if (!$allow && !empty($aModuleDirs)) {
                        foreach ($aModuleDirs as $moduleDir) {
                            $allow = strpos($realPath, $moduleDir) === 0;
                            if ($allow) {
                                break;
                            }
                        }
                    }
                    if ($allow) {
                        $this->aFiles[] = $loadFile;
                        $lastMod = max($lastMod, filemtime($loadFile));
                    }
                }
            }

            $this->hash = $lastMod . '-' . md5($filesString);

            if (isset($_SERVER['HTTP_IF_NONE_MATCH'])
                    && $_SERVER['HTTP_IF_NONE_MATCH'] == $this->hash) {
                $this->modifiedSinceLastRequest = false;
            }
            $this->modifiedDate = $this->timestampToDate($lastMod);
        }
    }

    /**
     * Send data.
     */
    function send()
    {
        if (!$this->modifiedSinceLastRequest) {
            header('HTTP/1.x 304 Not Modified');
        } elseif (empty($this->aFiles)) {
            header('HTTP/1.x 404 Not Found');
        } else {
            header('Pragma: cache');
            header('Cache-Control: public');
            header('Content-Type: text/' . $this->type);
            header('Etag: ' . $this->hash);
            //header('Last-Modified: ' . $this->modifiedDate);
            header('Expires: Thu, 15 Apr 2010 20:00:00 GMT');

            if ($this->type == 'css') {
                $content = $this->getCssContent();
            } else {
                $content = $this->getFilesContent();
                $content = $this->optimizeJavascript($content);
            }
            $encoding = $this->detectAvailableEncoding();

            if ($encoding) {
                $compressed = $this->compressContent($content, $encoding);
                if ($compressed) {
                    header('Content-Encoding: ' . $encoding);
                    $content = $compressed;
                }
            }

            header('Content-Length: ' . strlen($content));
            echo $content;
        }
    }

    /**
     * Get contactitaned files content.
     *
     * @return string
     */
    function getFilesContent()
    {
        $content = '';
        foreach ($this->aFiles as $fileName) {
            if (!empty($content)) {
                $content .= "\n\n";
            }
            $content .= file_get_contents($fileName);
        }
        return $content;
    }

    /**
     * Compress data.
     *
     * @param string $content
     * @param string $encoding
     *
     * @return string
     */
    function compressContent($content, $encoding)
    {
        $constant = 'FORCE_' . strtoupper($encoding);
        $ret = gzencode($content, 9, constant($constant));
        return $ret;
    }

    /**
     * Get available encoding to compress data.
     *
     * @return mixed
     */
    function detectAvailableEncoding()
    {
        $ret = false;
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            if (strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
                $ret = 'gzip';
            } elseif (strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate')) {
                $ret = 'deflate';
            }

            // check for buggy versions of Internet Explorer
            if (!strstr($_SERVER['HTTP_USER_AGENT'], 'Opera')
                    && preg_match('/^Mozilla\/4\.0 \(compatible; MSIE ([0-9]\.[0-9])/i',
                        $_SERVER['HTTP_USER_AGENT'], $matches)) {
                $v = floatval($matches[1]);
                if ($v < 6) {
                    $ret = false;
                } elseif ($v == 6 && !strstr($_SERVER['HTTP_USER_AGENT'], 'EV1')) {
                    $ret = false;
                }
            }
        }
        return $ret;
    }

    /**
     * Optimizes javascript with javascript packer.
     *
     * @param string $script
     *
     * @return string
     */
    function optimizeJavascript($script)
    {
        if ($this->optimize) {
            $cached_js = dirname(__FILE__) . '/../var/tmp/js_' . $this->hash;
            if (is_file($cached_js)) {
                $script = file_get_contents($cached_js);
            } else {
                require dirname(__FILE__) . '/../lib/other/jsmin.php';
                $script = JSMin::minify($script);
                file_put_contents($cached_js, $script);
            }
        }
        return $script;
    }

    function getCssContent()
    {
        include dirname(__FILE__) . '/themes/csshelpers.php';
        ob_start();
        foreach ($this->aFiles as $fileName) {
            include $fileName;
        }
        $ret = ob_get_clean();
        return $ret;
    }

    // copied from PEAR HTTP.php Date function (comments stripped)
    // Author: Stig Bakken <ssb@fast.no>
    function timestampToDate($time)
    {
        if (ini_get("y2k_compliance") == true) {
            return gmdate("D, d M Y H:i:s \G\M\T", $time);
        } else {
            return gmdate("F, d-D-y H:i:s \G\M\T", $time);
        }
    }
}

$optimizer = new SGL_Optimizer();
$optimizer->send();

?>