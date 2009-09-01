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
        return SGL_Media_Util::getIconPathByMimeType($mimeType, SGL_BASE_URL);
    }

    public function getImagePath($oMedia, $thumb = false)
    {
        $var =SGL_Media_Util::getImagePathByMimeType(
            $oMedia->file_name,
            $oMedia->mime_type,
            $thumb
        ); 
        SGL::logMessage($var, PEAR_LOG_DEBUG);
        return $var;
    }
}
?>
