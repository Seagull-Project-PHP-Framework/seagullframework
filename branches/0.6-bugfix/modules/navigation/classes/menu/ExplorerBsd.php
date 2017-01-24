<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | ExplorerBsd.php                                                           |
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

/**
 * Creates a windows exlorer type interface.
 *
 * @package navigation
 * @author  Demian Turner <demian@phpkitchen.com>
 */

class Menu_ExplorerBsd
{
    var $module = 'navigation';

    function Menu_ExplorerBsd($options, $conf)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->conf = $conf;
    }

    function render($id = 0)
    {
        $menu = $this->getGuruTree($id);
        $html = $menu->printMenu();
        return $html;
    }

    function getGuruTree($id = 0)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  style definition .treeMenuDefault in <head>
        $tree = &$this->createFromSQL($id);

        //  initialise the class options
        require_once 'HTML/TreeMenu.php';

        //  build url for current page
        $req = & SGL_Request::singleton();
        $action = ($req->get('managerName') == 'articleview')
            ? 'summary'
            : '';
        $url = SGL_Url::makeLink($action,
            $req->get('managerName'),
            $req->get('moduleName')
            );
        $url .= 'frmCatID/';
        $nodeOptions = array(
         'text'          => '',
         'link'          => $url,
         'icon'          => 'folder.gif',
         'expandedIcon'  => 'openfoldericon.gif',
         'class'         => '',
         'expanded'      => false,
         'linkTarget'    => '_self',
         'isDynamic'     => 'true',
         'ensureVisible' => '',
         );
        $options = array(   'structure' => $tree,
                            'type' => 'heyes',
                            'nodeOptions' => $nodeOptions);

        $menu = HTML_TreeMenu::createFromStructure($options);

        require_once SGL_MOD_DIR . '/navigation/classes/HTML_TreeMenu_DHTML_SGL.php';
        $theme = 'default';
        $treeMenu = & new HTML_TreeMenu_DHTML_SGL($menu, array(
            'images' =>  SGL_BASE_URL . "/themes/$theme/images/treeNav",
            'defaultClass'  => 'treeMenuDefault'));
        return $treeMenu;
    }

    /**
    * This method imports a tree from a database using the common
    * id/parent_id method of storing the structure.
    *
    */
    function &createFromSQL($id = 0)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        require_once 'HTML/Tree.php';

        $dbh = &SGL_DB::singleton();
        $roleId = SGL_Session::get('rid');
        $query = "  SELECT  category_id as id, parent_id, label AS text
                    FROM
                        {$this->conf['table']['category']}
                    WHERE
                        $roleId NOT IN (COALESCE(perms, '-1'))
                    ORDER BY parent_id, order_id";
        $tree     = &new Tree();
        $nodeList = array();

        // Perform query
        $result = $dbh->query($query);
        if (!PEAR::isError($result)) {
            while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {

                // Parent id is 0, thus root node.
                if (!$row['parent_id']) {
                    unset($row['parent_id']);
                    $nodeList[$row['id']] = &new Tree_Node($row);
                    $tree->nodes->addNode($nodeList[$row['id']]);

                // Parent node has already been added to tree
                } elseif (!empty($nodeList[$row['parent_id']])) {
                    $parentNode = &$nodeList[$row['parent_id']];
                    unset($row['parent_id']);
                    $nodeList[$row['id']] = &new Tree_Node($row);
                    $parentNode->nodes->addNode($nodeList[$row['id']]);

                } else {
                    // Orphan node ?
                }
            }
        } else SGL::raiseError('problem with getGuruTree query');

        // jump into the cat tree at a predefined depth
        // if $id = 0 return the hole tree OR if $id != 0 return from $id branch
        $result = ($id) ? $nodeList[$id] : $tree;
        return $result;
    }
}
?>