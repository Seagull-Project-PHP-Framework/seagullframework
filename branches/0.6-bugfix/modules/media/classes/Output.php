<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Seagull Systems                                       |
// | All rights reserved.                                                      |
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software               |
// | Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301,|
// | USA                                                                       |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | Output.php                                                                |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@seaugllproject.org>                         |
// +---------------------------------------------------------------------------+

/**
 * View helper methods.
 *
 * @package seagull
 * @subpackage media
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class MediaOutput
{
    /**
     * Maps icon HTML to corresponding position in a grid.
     *
     * The idea is to allow for filtering views by doc type.
     *
     * @access  public
     * @param   int     $documentID doc type
     * @return  string  $tdString
     * @see     id2AssetIcon()
     */
    function outputIcon($fileTypeId)
    {
        $iconString = $this->id2MediaIcon($fileTypeId);
        if ($fileTypeId == 7) {
            $pos = 1;
        } else {
            $pos = $fileTypeId;
        }
        $tdString = '';
        for ($i = 1; $i <= 6; $i++) {
            $icon = ($i == $pos) ? $iconString : '&nbsp;';
            $tdString .= "<td align=\"center\">". $icon ."</td>\n";
        }
        return $tdString;
    }

    /**
     * Takes doc type ID from DB and converts to corresponding icon.
     *
     * @access  public
     * @param   int     $fileTypeId file type
     * @return  string  $iconString
     */
    function id2MediaIcon($fileTypeId)
    {
        $c = &SGL_Config::singleton();
        $theme = $_SESSION['aPrefs']['theme'];
        switch ($fileTypeId) {
        case 1:
        case 8:
            $iconString = '<img src="' . SGL_BASE_URL . '/themes/' . $theme . '/images/icons/document_ms_word.png">';
            break;
        case 2:
            $iconString = '<img src="' . SGL_BASE_URL . '/themes/' . $theme . '/images/icons/document_ms_excel.png">';
            break;
        case 3:
            $iconString = '<img src="' . SGL_BASE_URL . '/themes/' . $theme . '/images/docIcons/ppt.gif">';
            break;
        case 4:
            $iconString = '<img src="' . SGL_BASE_URL . '/themes/' . $theme . '/images/docIcons/url.gif">';
            break;
        case 5:
            $iconString = '<img src="' . SGL_BASE_URL . '/themes/' . $theme . '/images/icons/document_img.png">';
            break;
        case 6:
            $iconString = '<img src="' . SGL_BASE_URL . '/themes/' . $theme . '/images/icons/document_pdf.png">';
            break;
        case 7:
            $iconString = '<img src="' . SGL_BASE_URL . '/themes/' . $theme . '/images/docIcons/unknown.gif">';
            break;
        default:
            $iconString = '&nbsp';
        }
        return $iconString;
    }

    function isImage($fileTypeId)
    {
        return ($fileTypeId == 5) ? true: false;
    }

    function rowStart($elementId, $type)
    {
        return ($elementId % 5 == 0) ? true : false;
    }

    function rowEnd($elementId, $type)
    {
        return ($elementId % 4 == 0 && $elementId != 0) ? true : false;
    }

    function isLastElement($elementId, $total)
    {
        return (count($total) == ($elementId + 1)) ? true : false;
    }

    function getKey($arrayToSearch, $key)
    {
        return $arrayToSearch[$key];
    }
}
?>
