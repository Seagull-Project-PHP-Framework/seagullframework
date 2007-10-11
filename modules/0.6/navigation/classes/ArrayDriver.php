<?php

/**
 * @todo remove duplication
 */
define('SGL_NODE_USER',  2); // nested set parent_id
define('SGL_NODE_ADMIN', 4); // nested set parent_id
define('SGL_NODE_GROUP', 1);

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

    /**
     * Constructor:
     *   - creates initial array from navigation.php files
     *   - identifies current node
     *   - checks permissions
     *
     * @param SGL_Output $output
     *
     * @return ArrayDriver
     */
    function ArrayDriver(&$output)
    {
        $aConsts = get_defined_constants(true);
        $aConsts = $aConsts['user'];

        // define all root nodes
        $aMenu = array();
        foreach ($aConsts as $constName => $constValue) {
            if (strpos($constName, 'SGL_NODE_') !== 0
                    || $constName == 'SGL_NODE_GROUP') {
                continue;
            }
            $aMenu[$constValue] = array();
            $this->aCurrentIndexes[$constValue] = 0;
        }

        // skip admin root if not allowed
        if (empty($output->adminGuiAllowed)) {
            unset($aMenu[SGL_NODE_ADMIN]);
            $this->currentRootIndex = SGL_NODE_USER;
        } else {
            $this->currentRootIndex = SGL_NODE_ADMIN;
        }

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
                if (empty($sectionRootId) || $section['parent_id'] != SGL_NODE_GROUP) {
                    $sectionRootId = $section['parent_id'];
                }

                // skip node if root ID is not known
                if (!array_key_exists($sectionRootId, $aMenu)) {
                    continue;
                }
                if (!$this->nodeAccessAllowed($section)) {
                    continue;
                }

                $section['manager']    = SGL_Inflector::getSimplifiedNameFromManagerName($section['manager']);
                $section['link']       = $this->makeLinkFromNode($section);
                $section['url']        = $section['link'];
                $section['is_current'] = $this->isCurrentNode($section);

                // create first level item
                if ($section['parent_id'] != SGL_NODE_GROUP) {
                    $nextId = $currentIndex = $currentNodeId = $section['parent_id'] * 10 + count($aMenu[$sectionRootId]) + 1;
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
                if ($section['is_current']) {
                    $this->aCurrentIndexes[$sectionRootId] = $currentIndex;
                    $this->aCurrentTitles[$sectionRootId] = $section['title'];
                }
            }
        }
        $this->aSections = $aMenu;
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
        $aPerms = explode(',', $aNode['perms']);
        $ret = false;
        foreach ($aPerms as $permId) {
            $permValue = SGL_String::pseudoConstantToInt($permId);
            if ($permValue == SGL_Session::getRoleId()
                    || $permValue == SGL_ANY_ROLE) {
                $ret = true;
                break;
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
     *
     * @todo check for parameters
     */
    function isCurrentNode($aNode)
    {
        $req = &SGL_Request::singleton();
        $ret = false;

        if ($req->getModuleName() == $aNode['module']
                && $req->getManagerName() == $aNode['manager']) {
            if (!empty($aNode['actionMapping'])) {
                $ret = $req->getActionName() == $aNode['actionMapping'];
            } else {
                $ret = true;
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
        $sep = '/';
        $urlManager = $aNode['manager'];
        $uriAction = !empty($aNode['actionMapping'])
                && $aNode['actionMapping'] != 'none'
            ? 'action' . $sep . $aNode['actionMapping'] . $sep
            : '';
        $uriParams = !empty($aNode['add_params'])
            ? $aNode['add_params']
            : '';
        $uriFc = SGL_Config::get('site.frontScriptName');
        // create url
        $ret = SGL_BASE_URL
            . $sep . $uriFc . $sep . $aNode['module']
            . $sep . $urlManager . $sep . $uriAction . $uriParams;
        if ($ret[strlen($ret)-1] != $sep) {
            $ret .= $sep;
        }
        return $ret;
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