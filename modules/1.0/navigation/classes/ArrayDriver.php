<?php

/**
 * @todo remove duplication
 */
if (!defined('SGL_NODE_USER')) {
    define('SGL_NODE_USER',  2); // nested set parent_id
    define('SGL_NODE_ADMIN', 4); // nested set parent_id
    define('SGL_NODE_GROUP', 1);
}

/**
 * Navigation driver, which uses PHP arrays to build navigation.
 *
 * @package seagull
 * @subpackage navigation
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class ArrayDriver
{
    /**
     * Navigation structure.
     *
     * @access private
     *
     * @var array
     */
    var $_aSections = array();

    /**
     * Array of current IDs by root IDs.
     *
     * @access private
     *
     * @var array
     */
    var $_aCurrentIndexes = array();

    /**
     * Index of current root node.
     *
     * @access private
     *
     * @var integer
     */
    var $_currentRootIndex;

    /**
     * Array of current titles by root IDs.
     *
     * @access private
     *
     * @var array
     */
    var $_aCurrentTitles = array();

    /**
     * Params for rendering.
     *
     * @access private
     *
     * @var array
     */
    var $_aParams = array();

    /**
     * ArrayDriver singleton.
     *
     * @access public
     *
     * @param SGL_Output $output
     *
     * @return ArrayDriver
     */
    function &singleton(&$output)
    {
        static $instance;
        if (!isset($instance)) {
            $instance = & new ArrayDriver($output);
        }
        return $instance;
    }

    /**
     * Constructor:
     *   - creates initial array from navigation.php files
     *   - identifies current node
     *   - checks permissions
     *
     * @access public
     *
     * @param SGL_Output $output
     *
     * @return ArrayDriver
     */
    function ArrayDriver(&$output)
    {
        // get nodes
        if (!($aNodes = $this->_loadCachedNodes())) {
            $aNodes = ArrayDriver::getNavigationStructure();
            ArrayDriver::saveNodes($aNodes);
        }

        // skip admin sections if not allowed
        if (empty($output->adminGuiAllowed)) {
            unset($aNodes[SGL_NODE_ADMIN]);

            // set default root index
            // it can be changed with ArrayDriver::setParams()
            $this->_currentRootIndex = SGL_NODE_USER;
        } else {
            $this->_currentRootIndex = SGL_NODE_ADMIN;
        }

        foreach ($aNodes as $rootId => $aSections) {
            if (!isset($this->_aCurrentIndexes[$rootId])) {
                $this->_aCurrentIndexes[$rootId] = 0;
            }
            $this->_processTree($aSections, $rootId);
            $aNodes[$rootId] = $aSections;
        }

        $this->_aSections = $aNodes;
    }

    function _processTree(&$aTree, $rootId, $level = 0)
    {
        foreach ($aTree as $nodeId => $aNode) {
            if (!$this->_nodeAccessAllowed($aNode)) {
                unset($aTree[$nodeId]);
                continue;
            }
            if (empty($aNode['link']) && empty($aNode['url'])) {
                $aNode['link'] = $aNode['url'] = $this->_makeLinkFromNode($aNode);
            } elseif (empty($aNode['link'])) {
                $aNode['link'] = $aNode['url'];
            } else {
                $aNode['url'] = $aNode['link'];
            }
            if (!isset($aNode['is_current'])) {
                $aNode['is_current'] = $this->_isCurrentNode($aNode)
                    || $this->_childIsCurrentNode($aNode);
            }
            $aNode['level'] = $level;
            if (!empty($aNode['is_current'])) {
                $this->_aCurrentTitles[$rootId]  = $aNode['title'];
                $this->_aCurrentIndexes[$rootId] = $nodeId;
            }
            if (!empty($aNode['sub'])) {
                $this->_processTree($aNode['sub'], $rootId, $level + 1);
            }
            $aTree[$nodeId] = $aNode;
        }
    }

    /**
     * Go through all modules and read navigation.php files.
     * Combines all sections in one array ready for use with HTML_Menu.
     *
     * @static
     *
     * @access public
     *
     * @return array
     */
    function getNavigationStructure()
    {
        $aTree    = array();
        $aModules = SGL_Util::getAllModuleDirs(true);
        foreach ($aModules as $dirName) {
            $structureFile = SGL_MOD_DIR . '/' . $dirName . '/data/navigation.php';
            if (!file_exists($structureFile)) {
                continue;
            }
            include $structureFile;
            // skip if no sections were defined
            if (empty($aSections) || !is_array($aSections)) {
                continue;
            }

            $rootId = $parentId = null;
            foreach ($aSections as $aSection) {
                ArrayDriver::_simplifyNode($aSection);

                $parentId = $aSection['parent_id'];
                unset($aSection['parent_id']);

                // find root ID and assign relevant node ID to current node
                if (empty($rootId) || $parentId != SGL_NODE_GROUP) {
                    $rootId       = $parentId;
                    $parentNodeId = ArrayDriver::_getNextNodeId($aTree[$rootId]);
                    $aNode        = &$aTree[$rootId][$parentNodeId];

                // assign relevant node ID to current node
                // under current parent node ID
                } else {
                    $nodeId = ArrayDriver::_getNextNodeId(
                        $aTree[$rootId][$parentNodeId]['sub']);
                    $aNode  = &$aTree[$rootId][$parentNodeId]['sub'][$nodeId];
                }

                // process subtrees populating proper node IDs
                ArrayDriver::_populateNodeIds($aSection);

                $aNode = $aSection;
            }
            unset($aSections);
        }
        return $aTree;
    }

    function _getNextNodeId(&$aTree)
    {
        static $i;
        if (!isset($i)) {
            $i = 100;
        }
        return ++$i;

        /*
        // create first level item
        if ($parentId != SGL_NODE_GROUP) {
            $nextId = $currentIndex = $currentNodeId = $parentId * 10 + count($aMenu[$sectionRootId]) + 1;
            $aMenu[$sectionRootId][$nextId] = $section;

        // create second level item
        } else {
            $subNav = &$aMenu[$sectionRootId][$currentNodeId]['sub'];
            if (empty($subNav)) {
                $subNav = array();
            }
            $currentIndex = $nextId * 10 + count($subNav) + 1;
            $subNav[$currentIndex] = $section;
        }
        */
    }

    function _populateNodeIds(&$aTree)
    {
        if (!empty($aTree['sub'])) {
            $aRet = array();
            foreach ($aTree['sub'] as $k => $aNode) {
        	    ArrayDriver::_populateNodeIds($aNode);
        	    $nextId = ArrayDriver::_getNextNodeId($aTree['sub']);
        	    $aRet[$nextId] = $aNode;
            }
            $aTree['sub'] = $aRet;
        }
    }

    function _simplifyNode(&$aNode)
    {
        if (isset($aNode['manager'])) {
            $aNode['manager'] = SGL_Inflector::getSimplifiedNameFromManagerName(
                $aNode['manager']);
        }
        if (!empty($aNode['uriType']) && $aNode['uriType'] == 'dynamic') {
            unset($aNode['uriType']);
        }
        if (isset($aNode['actionMapping'])) {
            if (!empty($aNode['actionMapping'])) {
                $aNode['action'] = $aNode['actionMapping'];
            }
            unset($aNode['actionMapping']);
        }
        if (isset($aNode['add_params'])) {
            if (!empty($aNode['add_params'])) {
                $aNode['params'] = $aNode['add_params'];
            }
            unset($aNode['add_params']);
        }
        if (!empty($aNode['is_enabled'])) {
            unset($aNode['is_enabled']);
        }
        if (isset($aNode['perms']) && $aNode['perms'] == SGL_ANY_ROLE) {
            unset($aNode['perms']);
        }
    }

    /**
     * Load nodes from cached file.
     *
     * @access private
     *
     * @return array
     */
    function _loadCachedNodes()
    {
        $fileName = SGL_VAR_DIR . '/navigation.php';
        if (file_exists($fileName)) {
            include $fileName;
        }
        return !empty($aSections) && is_array($aSections)
            ? $aSections
            : false;
    }

    /**
     * Cache nodes to file.
     *
     * @access public
     *
     * @static
     *
     * @param array $aNodes
     *
     * @return boolean
     */
    function saveNodes($aNodes)
    {
        $ok = false;
        if (is_writable(SGL_VAR_DIR)) {
            $data = var_export($aNodes, true);
            $data = "<?php\n\$aSections = $data;\n?>";
            $ok = file_put_contents(SGL_VAR_DIR . '/navigation.php', $data);
            @chmod(SGL_VAR_DIR . '/navigation.php', 0777);
        }
        return $ok;
    }

    /**
     * Check node permission by current role.
     *
     * @access private
     *
     * @param array $aNode
     *
     * @return boolean
     */
    function _nodeAccessAllowed($aNode)
    {
        static $rid;
        if (is_null($rid)) {
            $rid = SGL_Session::getRoleId();
        }
        $ret = false;
        if (!isset($aNode['is_enabled']) || !empty($aNode['is_enabled'])) {
            if (!empty($aNode['perms'])) {
                $aPerms = explode(',', $aNode['perms']);
                foreach ($aPerms as $permId) {
                    $permValue = SGL_String::pseudoConstantToInt($permId);
                    if ($permValue == $rid || $permValue == SGL_ANY_ROLE) {
                        $ret = true;
                        break;
                    }
                }
            } else {
                $ret = true;
            }
        }
        return $ret;
    }

    /**
     * Check if supplied node is current.
     *
     * @access private
     *
     * @param array $aNode
     *
     * @return boolean
     */
    function _isCurrentNode($aNode)
    {
        $req = &SGL_Request::singleton();
        $ret = false;

        // compare node's module and manager with current
        if ($req->getModuleName() == $aNode['module']
                && $req->getManagerName() == $aNode['manager']) {
            // compare node's action with current
            if (!empty($aNode['action'])) {
                $ret = $req->getActionName() == $aNode['action'];
            } else {
//                $ret = $req->getActionName() == 'default';
                $ret = true;
            }
            // compare node's params with current
            if (!empty($aNode['params'])) {
                $ret     = true; // by default params match
                $aParams = explode('/', $aNode['params']);
                for ($i = 0, $cnt = count($aParams); $i < $cnt; $i = $i + 2) {
                    $k = $aParams[$i];
                    $v = isset($aParams[$i + 1]) ? $aParams[$i + 1] : null;
                    if ($req->get($k) != $v) {
                        $ret = false;
                        break;
                    }
                }
            }
        }
        return $ret;
    }

    function _childIsCurrentNode($aTree)
    {
        $ret = false;
        if (!empty($aTree['sub'])) {
            foreach ($aTree['sub'] as $aNode) {
                $ret = $this->_isCurrentNode($aNode)
                    || $this->_childIsCurrentNode($aNode);
                if ($ret) {
                    break;
                }
            }
        }
        return $ret;
    }

    /**
     * Create URL from supplied node.
     *
     * @access private
     *
     * @param array $aNode
     *
     * @return string
     *
     * @todo check for URI type
     */
    function _makeLinkFromNode($aNode)
    {
        $action = !empty($aNode['action']) ? $aNode['action'] : '';
        $params = '';
        if (!empty($aNode['params'])) {
            $aVars = explode('/', $aNode['params']);
            for ($i = 0, $cnt = count($aVars); $i < $cnt; $i += 2) {
                if (isset($aVars[$i + 1])) {
                    if (!empty($params)) {
                        $params .= '||';
                    }
                    $params .= $aVars[$i] . '|' . $aVars[$i + 1];
                }
            }
        }
        return SGL_Output::makeUrl($action,
            $aNode['manager'], $aNode['module'], array(), $params);
    }

    /**
     * Set rendering params.
     *
     * @access public
     *
     * @param mixed $aParams  can be integer or array for BC with old block
     *
     * @return void
     */
    function setParams($aParams)
    {
        if (is_array($aParams)) {
            $this->_aParams = $aParams;
            if (isset($aParams['startParentNode'])) {
                $this->_currentRootIndex = $aParams['startParentNode'];
            }
        } else {
            $this->_currentRootIndex = $aParams;
        }
    }

    /**
     * Render navigation.
     *
     * @access public
     *
     * @param string $renderer  name of renderer
     * @param string $menuType  type of menu
     *
     * @return array or false (if nothing to render)
     *
     * @todo add breadcrumbs to result set
     */
    function render($renderer = 'SimpleRenderer', $menuType = 'tree')
    {
        // nothing to render
        if (empty($this->_aSections[$this->_currentRootIndex])) {
            $ret = false;
        } else {
            if ($renderBreadcrumbs = is_null($renderer)) {
                $renderer = 'SimpleRenderer';
            }
            $fileNameRenderer = dirname(__FILE__) . '/ArrayDriver/' . $renderer . '.php';
            // check if renderer file exists
            if (!file_exists($fileNameRenderer)) {
                $msg = sprintf('%s: %s renderer not found', __CLASS__, $renderer);
                $ret = SGL::raiseError($msg);
            } else {
                require_once $fileNameRenderer;
                $rendererClassName = 'ArrayDriver_' . $renderer;
                // check if renderer class exists
                if (!class_exists($rendererClassName)) {
                    $msg = sprintf('%s: %s class not found', __CLASS__, $rendererClassName);
                    $ret = SGL::raiseError($msg);
                } else {
                    $currentIdx = $this->_aCurrentIndexes[$this->_currentRootIndex];
                    $aSections  = $this->_aSections[$this->_currentRootIndex];

                    $aParams = $this->_aParams;
                    $aParams['currentIndex'] = $currentIdx;

                    // render menu
                    if (!$renderBreadcrumbs) {
                        $aParams['menuType'] = $menuType;
                        $renderer = & new $rendererClassName($aParams);
                        $menu = $renderer->toHtml($aSections);
                        $breadcrumbs = '';

                    // render breadcrumbs
                    } else {
                        $aParams['menuType'] = 'urhere';
                        $renderer = & new ArrayDriver_SimpleRenderer($aParams);
                        $breadcrumbs = $renderer->toHtml($aSections, 'DirectRenderer');
                        $menu = '';
                    }

                    if (!PEAR::isError($menu)) {
                        $ret = array(
                            0 => $currentIdx,
                            1 => $menu,
                            2 => $breadcrumbs
                        );
                    } else {
                        $ret = $menu;
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * Return current section name.
     *
     * @access public
     *
     * @return string
     */
    function getCurrentSectionName()
    {
        return isset($this->_aCurrentTitles[$this->_currentRootIndex])
            ? $this->_aCurrentTitles[$this->_currentRootIndex]
            : SGL_Config::get('site.name');
    }
}
?>