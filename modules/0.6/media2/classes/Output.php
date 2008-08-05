<?php

require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
require_once SGL_MOD_DIR . '/media2/lib/Media/Util.php';

/**
 * Media output.
 *
 * @package media2
 * @author Thomas Goetz
 * @author Demian Turner <demian@phpkitchen.com>
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class Media2Output
{
    public static function getUserFullName($userId)
    {
        $oUser = UserDAO::singleton()->getUserById($userId);
        $ret   = $oUser->first_name . ' ' . $oUser->last_name;
        if (empty($ret)) {
            $ret = $oUser->username;
        }
        return $ret;
    }

    public function formatFileSize($size)
    {
        $aUnits = array('B', 'Kb', 'Mb', 'Gb');
        foreach ($aUnits as $unit) {
            if ($size > 1024) {
                $size = round($size / 1024, 2);
            } else {
                break;
            }
        }
        $ret = $size . ' ' . $unit;
        return $ret;
    }

    public function isImageMimeType($mimeType)
    {
        return SGL_Media_Util::isImageMimeType($mimeType);
    }

    public function getIconByMimeType($mimeType)
    {
        switch ($mimeType) {
            case 'image/gif':
            case 'image/jpeg':
            case 'image/pjpeg':
            case 'image/png':
            case 'image/x-png':
                $ret = 'doc_img.png';
                break;
            case 'application/pdf':
                $ret = 'doc_pdf.png';
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
        return SGL_BASE_URL . '/media2/images/icons/' . $ret;
    }

    public function getImagePath($oMedia, $thumb = false)
    {
        static $aConf;
        if (!isset($aConf)) {
            $aConf = parse_ini_file(SGL_MOD_DIR . '/media2/image.ini', true);
        }
        $container  = !empty($oMedia->media_type)
            ? $oMedia->media_type
            : 'default';
        $container  = strtolower(str_replace(' ', '_', $container));
        $uploadDir  = isset($aConf[$container]['uploadDir'])
            ? $aConf[$container]['uploadDir']
            : trim(str_replace(SGL_APP_ROOT, '', SGL_UPLOAD_DIR), '/');
        $uploadDir .= $thumb ? '/thumbs/' . $thumb . '_' : '/';
        return $uploadDir . $oMedia->file_name;
    }
}
?>