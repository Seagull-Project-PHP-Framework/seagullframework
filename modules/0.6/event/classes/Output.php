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
 * @subpackage event
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class EventOutput
{
    function formatAddress($oAddress)
    {
        $br = '<br />';
        $html = $oAddress->addr_1 . $br;
        $html .= !empty($oAddress->addr_2) ? $oAddress->addr_2 . $br : '';
        $html .= !empty($oAddress->addr_3) ? $oAddress->addr_3 . $br : '';
        $html .= !empty($oAddress->city) ? $oAddress->city . $br : '';
        $html .= !empty($oAddress->region) ? $oAddress->region . $br : '';
        $html .= !empty($oAddress->country) ? $oAddress->country . $br : '';
        $html .= !empty($oAddress->post_code) ? $oAddress->post_code . $br : '';
        return $html;
    }
}
?>
