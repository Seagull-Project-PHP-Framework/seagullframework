<?php

/**
 * Js optimizer.
 *
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_JsOptimizer
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
    var $hash   = null;

    function SGL_JsOptimizer()
    {
        $lastMod = 0;

        // get files and it's mod time
        if (!empty($_GET['files'])) {
            $filesString = $_GET['files'];
            $aFiles = explode(',', $_GET['files']);
            foreach ($aFiles as $fileName) {
                if (is_file($jsFile = dirname(__FILE__) . '/' . $fileName)) {
                    $this->aFiles[] = $jsFile;
                    $lastMod = max($lastMod, filemtime($jsFile));
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
            header('Content-Type: text/javascript');
            header('Etag: ' . $this->hash);
            //header('Last-Modified: ' . $this->modifiedDate);
            header('Expires: Thu, 15 Apr 2010 20:00:00 GMT');

            $content  = $this->getFilesContent();
            //$content  = $this->optimizeJavascript($content);
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
        return $script;
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

$optimizer = new SGL_JsOptimizer();
$optimizer->send();

?>