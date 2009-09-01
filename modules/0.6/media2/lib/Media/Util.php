<?php

/**
 * Set of static media utilities.
 *
 * @package SGL
 * @subpackage media2
 * @author Thomas Goetz
 * @author Demian Turner <demian@phpkitchen.com>
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Media_Util
{
    /**
     * Test if specified file is a text file.
     *
     * @param string $path
     *
     * @return boolean
     */
    public static function isTextFile($path)
    {
        $ret = false;
        if (is_readable($path)) {
            $data = file_get_contents($path);
            $bad  = false;
            for ($i = 0, $len = strlen($data); !$bad && $i < $len; $i++) {
                $bad = ord($data[$i]) > 127;
            }
            $ret = !$bad;
        }
        return $ret;
    }

    /**
     * Check if mime type belongs to image mime type.
     *
     * @param string $mimeType
     *
     * @return boolean
     */
    public static function isImageMimeType($mimeType)
    {
        return preg_match("/^image/", $mimeType);
    }

    /**
     * @param string $fileName
     * @param array $aHexIdents
     *
     * @return string
     */
    public static function getFileIdent($fileName, $aHexIdents)
    {
        $ret = false;
        // open the file for reading (binary)
        if ($fp = @fopen($fileName, 'rb')) {
            // get the (converted to bin) hex identifier length
            // to extract that amount of bytes from our uploaded file
            $aBinIdents = array_map(array(__CLASS__, 'condense'), $aHexIdents);
            $aSizes     = array_map('strlen', $aBinIdents);
            $read       = max($aSizes);
            $data       = fread($fp, $read); // store the read data
            fclose($fp);

            // check our data against the array of catalogued file types
            foreach ($aBinIdents as $type => $signature) {
                $found = substr($data, 0, strlen($signature)) === $signature
                    && !empty($signature);
                if ($found) {
                    $ret = $type;
                    break;
                }
            }
        }
        return $ret;
    }

    public static function condense($value)
    {
        return pack('H*', str_replace(' ', '', $value));
    }

    public static function getUniqueString($suffix = '')
    {
        return md5(microtime() . $suffix);
    }

    public static function ensureDirIsWritable($dirName)
    {
        $ok = true;
        if (!is_writable($dirName)) {
            require_once 'System.php';
            $ok = System::mkDir(array('-p', $dirName));
            if (!PEAR::isError($ok)) {
                $mask = umask(0);
                $ok   = @chmod($dirName, 0777);
                umask($mask);
            }
        }
        return $ok;
    }

    public static function getIconPathByMimeType($mimeType, $path = SGL_WEB_ROOT)
    {
        switch ($mimeType) {
            case 'image/gif':
            case 'image/jpeg':
            case 'image/png':
                $ret = 'doc_img.png';
                break;
            case 'application/pdf':
                $ret = 'doc_pdf.png';
                break;
            case 'audio/mp3':
                $ret = 'doc_mp3.png';
                break;
            case 'application/msword':
                $ret = 'doc_msword.png';
                break;
            case 'text/plain':
                $ret = 'doc_msword.png';
                break;
            default:
                $ret = 'doc_unknown.gif';
        }
        return $path . '/media2/images/icons/' . $ret;
    }

    public static function getImagePathByMimeType($fileName, $mimeType,
        $thumb = false)
    {
        static $aConf;
        if (!isset($aConf)) {
            $confFile = SGL_Config::locateCachedFile(
                SGL_MOD_DIR . '/media2/image.ini');
            $aConf    = parse_ini_file($confFile, true);
        }
        $container = !empty($mimeType) ? $mimeType : 'default';
        $container = strtolower(str_replace(' ', '_', $container));

        // find upload dir
        $uploadDir = isset($aConf[$container]['uploadDir'])
            ? $aConf[$container]['uploadDir']
            : trim(str_replace(SGL_APP_ROOT, '', SGL_UPLOAD_DIR), '/');
        $uploadDir .= $thumb ? '/thumbs/' . $thumb . '_' : '/';

        return $uploadDir . $fileName;
    }
}
?>
