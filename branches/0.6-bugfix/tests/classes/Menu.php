<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2003-2005 m3 Media Services Limited                         |
// | For contact details, see: http://www.m3.net/                              |
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
// | Menu.php                                                                  |
// +---------------------------------------------------------------------------+
// | Authors:   Andrew Hill <andrew@m3.net>                                    |
// |            Demian Turner <demian@phpkitchen.com>                          |
// |            James Floyd <james@m3.net>                                     |
// +---------------------------------------------------------------------------+

require_once STR_PATH . '/tests/classes/FileScanner.php';
require_once 'HTML/TreeMenu.php';

/**
 * A class for managing the construction of groups of tests, and for
 * presenting them in an HTML menu.
 *
 * @author     Andrew Hill <andrew@m3.net>
 */
class STR_Menu
{

    /**
     * A method to return the HTML code needed to display a tree-based
     * menu of all the Max tests.
     *
     * @return string A string containing the HTML code needed to display
     *                the tests in a tree-based menu.
     */
    function buildTree()
    {
        $conf = $GLOBALS['_STR']['CONF'];

        // Create the root of the test suite
        $menu     = new HTML_TreeMenu();
        $rootNode = new HTML_TreeNode(
                            array(
                                'text' => $conf['project']['name'] . ' Tests',
                                'icon' => "package.png"
                            )
                        );
        // Create the top-level test groups
        foreach (array('unit', 'web') as $type) {
            $nodeName = $type . 'RootNode';
            ${$nodeName} = new HTML_TreeNode(
                                array(
                                    'text' => ucwords($type) . ' Test Suite',
                                    'icon' => "package.png",
                                    'link' => "run.php?type=$type&level=all",
                                    'linkTarget' => "right"
                                )
                            );
            $structure = STR_FileScanner::getAllTestFiles($type);
            foreach ($structure as $layerCode => $folders) {
                $firstNode = &${$nodeName}->addItem(
                    new HTML_TreeNode(
                        array(
                            'text' => $GLOBALS['_STR'][$type . '_layers'][$layerCode][0],
                            'icon' => "package.png",
                            'link' => "run.php?type=$type&level=layer&layer=$layerCode",
                            'linkTarget' => 'right'
                        )
                    )
                );
                foreach ($folders as $folder => $files) {
                    if (count($files)) {
                        $secondNode = &$firstNode->addItem(
                            new HTML_TreeNode(
                                array(
                                    'text' => $folder,
                                    'icon' => "class_folder.png",
                                    'link' => "run.php?type=$type&level=folder&layer=$layerCode&folder=$folder",
                                    'linkTarget' => 'right'
                                )
                            )
                        );
                    }
                    foreach ($files as $index => $file) {
                        $secondNode->addItem(
                            new HTML_TreeNode(
                                array(
                                    'text' => $file,
                                    'icon' => "Method.png",
                                    'link' => "run.php?type=$type&level=file&layer=$layerCode&folder=$folder&file=$file",
                                    'linkTarget' => 'right'
                                )
                            )
                        );
                    }
                }
            }
            $rootNode->addItem(${$nodeName});
        }
        // Add the root node to the menu, and return the HTML code
        $menu->addItem($rootNode);
        $tree = new HTML_TreeMenu_DHTML($menu);
        $code  = file_get_contents(STR_PATH . '/tests/media/menu.css');
        $code .= "\n<script>\n";
        $code .= file_get_contents(STR_PATH . '/tests/media/TreeMenu.js');
        $code .= "\n</script>";
        $code .= $tree->toHTML();
        return $code;
    }
}

?>