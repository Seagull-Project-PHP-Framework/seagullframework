<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | SelectBox.php                                                             |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006 Demian Turner                                          |
// |                                                                           |
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This library is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU Library General Public               |
// | License as published by the Free Software Foundation; either              |
// | version 2 of the License, or (at your option) any later version.          |
// |                                                                           |
// | This library is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU         |
// | Library General Public License for more details.                          |
// |                                                                           |
// | You should have received a copy of the GNU Library General Public         |
// | License along with this library; if not, write to the Free                |
// | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
// |                                                                           |
// +---------------------------------------------------------------------------+
//
require_once SGL_CORE_DIR . '/Category.php';

/**
 * Creates a select menu from db category structure.
 *
 * @package navigation
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.6 $
 * @since   PHP 4.1
 */

class Menu_SelectBox extends SGL_Category
{
    var $_separator     = '';
    var $_ret           = array();
    var $_excludeCatID  = 0;
    var $_depth         = 0;
    var $module         = 'navigation';

    function Menu_SelectBox($options, $conf)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Category();

        $this->conf = $conf;
        $this->_separator   = ($options['separator'])
                                ? $options['separator']
                                :'&nbsp;&nbsp;&nbsp;&nbsp;';
        $this->_excludeCatID = (isset($options['exclude'])) ? $options['exclude']:'';
    }

    function render($id = 0)
    {
        //  iterate through whole DB resultset, return array
        $result = $this->getChildren($id);
        $maxElements = count($result);
        for ($x=0; $x < $maxElements; $x++) {
            $index = $result[$x]['category_id'];
            $value = str_repeat($this->_separator, $this->_depth) . stripslashes($result[$x]['label']);
            $this->_ret[$index] = $value;

            //  if branch then recurse
            if ($this->isBranch($result[$x]['category_id'])) {
                $this->_depth ++;
                $this->render($result[$x]['category_id']);
                $this->_depth --;
            }
        }
        $ret = $this->_ret;

        //  exclude element if applicable
        if (isset($this->_excludeCatID) && is_array($this->_excludeCatID)) {
            foreach ($this->_excludeCatID as $key) {
                unset($ret[$key]);
            }
        } elseif (isset($this->_excludeCatID)) {
            unset($ret[$this->_excludeCatID]);
        }
        return $ret;
    }
}
?>