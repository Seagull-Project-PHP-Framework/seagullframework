<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2008, Demian Turner                                         |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | o Redistributions of source code must retain the above copyright          |
// |   notice, this list of conditions and the following disclaimer.           |
// | o Redistributions in binary form must reproduce the above copyright       |
// |   notice, this list of conditions and the following disclaimer in the     |
// |   documentation and/or other materials provided with the distribution.    |
// | o The names of the authors may not be used to endorse or promote          |
// |   products derived from this software without specific prior written      |
// |   permission.                                                             |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT         |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR     |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT      |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,     |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT          |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,     |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE     |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.      |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | HTML_TreeMenu_DHTML_SGL.php                                               |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: HTML_TreeMenu_DHTML_SGL.php,v 1.3 2005/02/28 23:45:33 demian Exp $


class HTML_TreeMenu_DHTML_SGL extends HTML_TreeMenu_DHTML
{
    /**
    * Dynamic status of the treemenu. If true (default) this has no effect. If
    * false it will override all dynamic status vars and set the menu to be
    * fully expanded an non-dynamic.
    */
    var $isDynamic;

    /**
    * Path to the images
    * @var string
    */
    var $images;

    /**
    * Target for the links generated
    * @var string
    */
    var $linkTarget;

    /**
    * Whether to use clientside persistence or not
    * @var bool
    */
    var $userPersistence;

    /**
    * The default CSS class for the nodes
    */
    var $defaultClass;

    /**
    * Whether to skip first level branch images
    * @var bool
    */
    var $noTopLevelImages;

    /**
    * Constructor, takes the tree structure as
    * an argument and an array of options which
    * can consist of:
    *  o images            -  The path to the images folder. Defaults to "images"
    *  o linkTarget        -  The target for the link. Defaults to "_self"
    *  o defaultClass      -  The default CSS class to apply to a node. Default is none.
    *  o usePersistence    -  Whether to use clientside persistence. This persistence
    *                         is achieved using cookies. Default is true.
    *  o noTopLevelImages  -  Whether to skip displaying the first level of images if
    *                         there is multiple top level branches.
    *
    * And also a boolean for whether the entire tree is dynamic or not.
    * This overrides any perNode dynamic settings.
    *
    * @param object $structure The menu structure
    * @param array  $options   Array of options
    * @param bool   $isDynamic Whether the tree is dynamic or not
    */
    function HTML_TreeMenu_DHTML_SGL(&$structure, $options = array(), $isDynamic = true)
    {
        $this->HTML_TreeMenu_Presentation($structure);
        $this->isDynamic = $isDynamic;

        // Defaults
        $this->images           = 'images';
        $this->linkTarget       = '_self';
        $this->defaultClass     = '';
        $this->usePersistence   = true;
        $this->noTopLevelImages = false;

        foreach ($options as $option => $value) {
            $this->$option = $value;
        }
    }


    /**
    * Prints the HTML generated by the toHTML() method.
    * toHTML() must therefore be defined by the derived
    * class.
    *
    * @access public
    * @param  array  Options to set. Any options taken by
    *                the presentation class can be specified
    *                here.
    */
    function printMenu($options = array())
    {
        foreach ($options as $option => $value) {
            $this->$option = $value;
        }

        return $this->toHTML();
    }

    /**
    * Returns the HTML for the menu. This method can be
    * used instead of printMenu() to use the menu system
    * with a template system.
    *
    * @access public
    * @return string The HTML for the menu
    */
    function toHTML()
    {
        static $count = 0;
        $menuObj = 'objTreeMenu_' . ++$count;

        $html  = "\n";
        $html .= '<script type="text/javascript">' . "\n\t";
        $html .= sprintf('%s = new TreeMenu("%s", "%s", "%s", "%s", %s, %s);',
                    $menuObj,
                    $this->images,
                    $menuObj,
                    $this->linkTarget,
                    $this->defaultClass,
                    $this->usePersistence ? 'true' : 'false',
                    $this->noTopLevelImages ? 'true' : 'false');

        $html .= "\n";

        /**
        * Loop through subnodes
        */
        if (isset($this->menu->items)) {
            for ($i = 0; $i < count($this->menu->items); $i++) {

                // We don't want HiddentRoot, with id 0 (on PostgreSQL) to be shown as part of the menu
                $currItem = $this->menu->items[$i];
                if (!empty($currItem->id)) {
                    $html .= $this->_nodeToHTML($this->menu->items[$i], $menuObj);
                }
            }
        }

        $html .= sprintf("\n\t%s.drawMenu();", $menuObj);
        if ($this->usePersistence && $this->isDynamic) {
            $html .= sprintf("\n\t%s.resetBranches();", $menuObj);
        }
        $html .= "\n</script>";

        return $html;
    }

    /**
    * Prints a node of the menu
    *
    * @access private
    */
    function _nodeToHTML($nodeObj, $prefix, $return = 'newNode')
    {
        $expanded  = $this->isDynamic ? ($nodeObj->expanded  ? 'true' : 'false') : 'true';
        $isDynamic = $this->isDynamic ? ($nodeObj->isDynamic ? 'true' : 'false') : 'false';
        $html = sprintf("\t %s = %s.addItem(new TreeNode('%s', %s, %s, %s, %s, '%s', '%s', %s));\n",
                    addslashes($return),
                    $prefix,
                    addslashes($nodeObj->text),
                    !empty($nodeObj->icon) ? "'" . $nodeObj->icon . "'" : 'null',
#                   !empty($nodeObj->link) ? "'" . $nodeObj->link . "'" : 'null',
                    !empty($nodeObj->link) ? "'" . $nodeObj->link . $nodeObj->id ."/'" : 'null',
                    $expanded,
                    $isDynamic,
                    $nodeObj->cssClass,
                    $nodeObj->linkTarget,
                    !empty($nodeObj->expandedIcon) ? "'" . $nodeObj->expandedIcon . "'" : 'null');

        foreach ($nodeObj->events as $event => $handler) {
            $html .= sprintf("\t %s.setEvent('%s', '%s');\n",
                         $return,
                         $event,
                         str_replace(array("\r", "\n", "'"), array('\r', '\n', "\'"), $handler));
        }

        /**
        * Loop through subnodes
        */
        if (!empty($nodeObj->items)) {
            for ($i=0; $i<count($nodeObj->items); $i++) {
                $html .= $this->_nodeToHTML($nodeObj->items[$i], $return, $return . '_' . ($i + 1));
            }
        }

        return $html;
    }
}
?>