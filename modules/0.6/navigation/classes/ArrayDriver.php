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
     * @var array
     */
    var $aSections = array();

    /**
     * Array of current IDs by root IDs.
     *
     * @var array
     */
    var $aCurrentIndexes = array();

    /**
     * Index of current root node.
     *
     * @var integer
     */
    var $currentRootIndex;

    /**
     * Default renderer name.
     *
     * @var string
     */
    var $defaultRenderer = 'DirectTreeRenderer';

    /**
     * Array of current titles by root IDs.
     *
     * @var array
     */
    var $aCurrentTitles = array();

    function &singleton(&$output)
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new ArrayDriver($output);
        }
        return $instance;
    }

    /**
     * Constructor:
     *   - creates initial array from navigation.php files
     *   - identifies current node
     *   - checks permissions
     *
     * NOTE: only two levels menu supported by now.
     *
     * @param SGL_Output $output
     *
     * @return ArrayDriver
     */
    function ArrayDriver(&$output)
    {
        // get nodes
        if (!($aNodes = $this->loadCachedNodes())) {
            $aNodes = $this->createNavigationStructure();
            $this->cacheNodes($aNodes);
        }

        // skip admin root if not allowed
        if (empty($output->adminGuiAllowed)) {
            unset($aNodes[SGL_NODE_ADMIN]);

            // set default root index
            // it can be changed with ArrayDriver::setParams()
            $this->currentRootIndex = SGL_NODE_USER;
        } else {
            $this->currentRootIndex = SGL_NODE_ADMIN;
        }

        foreach ($aNodes as $rootId => $aSections) {
            $this->aCurrentIndexes[$rootId] = 0;
            foreach ($aSections as $sectionId => $section) {
                if (!$this->nodeAccessAllowed($section)) {
                    unset($aNodes[$rootId][$sectionId]);
                    continue;
                }
                $section['link']       = $this->makeLinkFromNode($section);
                $section['url']        = $section['link'];
                $section['is_current'] = $this->isCurrentNode($section);

                if (!empty($section['is_current'])) {
                    $this->aCurrentTitles[$rootId] = $section['title'];
                    $this->aCurrentIndexes[$rootId] = $sectionId;
                }

                // save changes made to section
                $aNodes[$rootId][$sectionId] = $section;

                if (!empty($section['sub'])) {
                    foreach ($section['sub'] as $subSectionId => $subSection) {
                        if (!$this->nodeAccessAllowed($subSection)) {
                            unset($aNodes[$rootId][$sectionId]['sub'][$subSectionId]);
                            continue;
                        }
                        $subSection['link']       = $this->makeLinkFromNode($subSection);
                        $subSection['url']        = $subSection['link'];
                        $subSection['is_current'] = $this->isCurrentNode($subSection);

                        if (!empty($subSection['is_current'])) {
                            $this->aCurrentTitles[$rootId] = $subSection['title'];
                            $this->aCurrentIndexes[$rootId] = $subSectionId;
                        }

                        // save changes made to subsection
                        $aNodes[$rootId][$sectionId]['sub'][$subSectionId] = $subSection;
                    }
                }
            }
        }
        $this->aSections = $aNodes;
    }

    /**
     * Go through all modules and read navigation.php files.
     * Combines all sections in one array ready for use with HTML_Menu.
     *
     * @return array
     */
    function createNavigationStructure()
    {
        $aMenu = array();
        $aDirs = SGL_Util::getAllModuleDirs(true);
        foreach ($aDirs as $dirName) {
            $structureFile = SGL_MOD_DIR . '/' . $dirName . '/data/navigation.php';
            if (!file_exists($structureFile)) {
                continue;
            }
            include $structureFile;
            // skip if no sections were defined
            if (empty($aSections) || !is_array($aSections)) {
                continue;
            }

            $sectionRootId = null;
            foreach ($aSections as $section) {
                // find root ID
                if (empty($sectionRootId) || $section['parent_id'] != SGL_NODE_GROUP) {
                    $sectionRootId = $section['parent_id'];
                    if (empty($aMenu[$sectionRootId])) {
                        $aMenu[$sectionRootId] = array();
                    }
                }

                // simplify node
                $section['manager'] = SGL_Inflector::getSimplifiedNameFromManagerName($section['manager']);
                if (!empty($section['uriType']) && $section['uriType'] == 'dynamic') {
                    unset($section['uriType']);
                }
                if (isset($section['actionMapping']) && empty($section['actionMapping'])) {
                    unset($section['actionMapping']);
                }
                if (isset($section['add_params']) && empty($section['add_params'])) {
                    unset($section['add_params']);
                }
                if (!empty($section['is_enabled'])) {
                    unset($section['is_enabled']);
                }

                $parentId = $section['parent_id'];
                unset($section['parent_id']);

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
            }
        }
        return $aMenu;
    }

    /**
     * Load nodes from cached file.
     *
     * @return array
     */
    function loadCachedNodes()
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
     * @param array $aNodes
     *
     * @return boolean
     */
    function cacheNodes($aNodes)
    {
        $data = var_export($aNodes, true);
        $data = "<?php\n\$aSections = $data;\n?>";
        $ok = file_put_contents(SGL_VAR_DIR . '/navigation.php', $data);
        return $ok;
    }

    /**
     * Check node permission by current role.
     *
     * @param array $aNode
     *
     * @return boolean
     */
    function nodeAccessAllowed($aNode)
    {
        static $rid;
        if (is_null($rid)) {
            $rid = SGL_Session::getRoleId();
        }
        $ret = false;
        if (!isset($aNode['is_enabled']) || !empty($aNode['is_enabled'])) {
            $aPerms = explode(',', $aNode['perms']);
            foreach ($aPerms as $permId) {
                $permValue = SGL_String::pseudoConstantToInt($permId);
                if ($permValue == $rid
                        || $permValue == SGL_ANY_ROLE) {
                    $ret = true;
                    break;
                }
            }
        }
        return $ret;
    }

    /**
     * Check if supplied node is current.
     *
     * @param array $aNode
     *
     * @return boolean
     */
    function isCurrentNode($aNode)
    {
        $req = &SGL_Request::singleton();
        $ret = false;

        // compare node's module and manager with current
        if ($req->getModuleName() == $aNode['module']
                && $req->getManagerName() == $aNode['manager']) {
            // compare node's action with current
            $ret = !empty($aNode['actionMapping'])
                ? $req->getActionName() == $aNode['actionMapping'] : true;
            // compare node's params with current
            if (!empty($aNode['add_params'])) {
                $ret     = true; // by default params match
                $aParams = explode('/', $aNode['add_params']);
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

    /**
     * Create URL from supplied node.
     *
     * @param array $aNode
     *
     * @return string
     *
     * @todo check for URI type
     */
    function makeLinkFromNode($aNode)
    {
        $action = !empty($aNode['actionMapping']) ? $aNode['actionMapping'] : '';
        $params = '';
        if (!empty($aNode['add_params'])) {
            $aVars = explode('/', $aNode['add_params']);
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
     * Set root ID for current navigation branch.
     *
     * @param mixed $rootId  can be integer or array for BC with old block
     *
     * @return void
     */
    function setParams($rootId)
    {
        if (is_array($rootId)) {
            $this->currentRootIndex = $rootId['startParentNode'];
        } else {
            $this->currentRootIndex = $rootId;
        }
    }

    /**
     * Render menu.
     *
     * @param string $renderer  name of renderer
     * @param string $menuType  type of menu
     *
     * @return array or false on failure
     *
     * @todo add breadcrumbs to result set
     */
    function render($renderer = 'DirectTreeRenderer', $menuType = 'tree')
    {
        $fileNameDriver   = SGL_LIB_PEAR_DIR . '/HTML/Menu.php';
        $fileNameRenderer = SGL_LIB_PEAR_DIR . '/HTML/Menu/' . $renderer . '.php';

        // use default one if none exists
        // need this for BC
        if (!file_exists($fileNameRenderer)) {
            $fileNameRenderer = SGL_LIB_PEAR_DIR . '/HTML/Menu/' . $this->defaultRenderer . '.php';
            $renderer = $this->defaultRenderer;
        }

        if (isset($this->aSections[$this->currentRootIndex])
                && file_exists($fileNameDriver)
                && file_exists($fileNameRenderer)) {
            require_once $fileNameDriver;
            require_once $fileNameRenderer;

            // init renderer
            $rendererClassName = 'HTML_Menu_' . $renderer;
            $renderer = & new $rendererClassName();
            $this->prepareRenderer($renderer);

            // init driver
            $menu = & new HTML_Menu($this->aSections[$this->currentRootIndex]);
            $menu->forceCurrentIndex($this->aCurrentIndexes[$this->currentRootIndex]);
            $menu->setUrlPrefix('');

            // render
            $menu->render($renderer, $menuType);
            $html = $renderer->toHtml();

            $ret = array(
                0 => $this->aCurrentIndexes[$this->currentRootIndex],
                1 => $html,
                2 => '' // breadcrumbs
            );
        } elseif (!file_exists($fileNameDriver) || !file_exists($fileNameRenderer)) {
            // should return the error instead
            SGL::raiseError('ArrayDriver: PEAR::HTML_Menu package not found');
            $ret = false;
        } else {
            $ret = false;
        }
        return $ret;
    }

    /**
     * Return current section name.
     *
     * @return string
     */
    function getCurrentSectionName()
    {
        return isset($this->aCurrentTitles[$this->currentRootIndex])
            ? $this->aCurrentTitles[$this->currentRootIndex]
            : SGL_Config::get('site.name');
    }

    /**
     * Prepare renderer's entry templates.
     *
     * @param HTML_Menu_Renderer $renderer
     */
    function prepareRenderer(&$renderer)
    {
        $renderer->setItemTemplate('', '</li>');
        $renderer->setLevelTemplate('<ul>', '</ul>');
        $renderer->setEntryTemplate(array(
            HTML_MENU_ENTRY_INACTIVE    => '<li><a href="{url}">{title}</a>',
            HTML_MENU_ENTRY_ACTIVE      => '<li class="current"><a href="{url}">{title}</a>',
            HTML_MENU_ENTRY_ACTIVEPATH  => '<li class="current"><a href="{url}">{title}</a>'
        ));
    }
}

?>