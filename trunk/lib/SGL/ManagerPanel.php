<?php
/**
 * Class for all manager objects.
 * Allows to build a module header with actions, linked managers ...
 *
 * @package SGL
 * @author  Julien Casanova <julien_casanova@yahoo.fr>
 */
class SGL_ManagerPanel
{
    /**
     * Current module
     *
     * @access  public
     * @var     string
     */

    var $module = '';

    /**
     * Current manager
     *
     * @access  public
     * @var     string
     */

    var $manager = '';

    /**
     * Current action.
     *
     * @access  public
     * @var     string
     */

    var $action = '';

    /**
     * All available options will be stored in this array.
     *
     * @access  private
     * @var     array
     */

    var $_aData = '';

    /**
     * Constructor.
     *
     * @access  public
     * @return  void
     */
    function SGL_ManagerPanel(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $req = &SGL_Request::singleton();
        $reg = &SGL_Registry::singleton();
        $this->_aData = '';
        $this->module   = $req->get('moduleName');
        $this->_aData['module'] = $this->module;
        $this->manager  = $req->get('managerName');
        $this->action   = $input->action;
        //echo'<pre>';die(print_r($this));
        $this->setupPanel($input);
        //echo'<pre>';die(print_r($input));
        $this->renderPanel($input);
    }

    /**
     * Returns a singleton ManagerPanel instance .
     *
     * @access  public
     * @return  instance
     */
    function &singleton(&$input)
    {
        static $instance;

        if(!isset($instance)) {
            $instance = new SGL_ManagerPanel($input);
        }
        return $instance;
    }

    /**
     * Launches setup of Manager Panel.
     *
     * @return void
     */
    function setupPanel(&$input)
    {
        require_once SGL_DAT_DIR . "/ary.managerOptions.php";
        $currModule = $this->module;
        $currMgr = $this->manager;//die('depuis __CLASS__ : <b>' . $nom . '</b> depuis $this : <b>' . $this->manager . '</b> et depuis $req->getManager <b>' . $this->manager . '</b>');
        $currAction = $this->action;

        if (!isset($aMgrOptions[$currModule])) {
            SGL::raiseError("Je n'ai pas trouv� le module $currModule dans le tableau de donn�es.<br />Module $currModule, Manager $currMgr, Action $currAction", PEAR_ERROR_DIE);
        }
        if (!isset($aMgrOptions[$currModule][$currMgr])) {
            SGL::raiseError('Je n\'ai pas trouv� le manager '.$currMgr.' du module '.$currModule.' dans le tableau de donn�es.', PEAR_ERROR_DIE);
        }
        $aMgrOptions = isset($aMgrOptions[$currModule][$currMgr]) ? $aMgrOptions[$currModule][$currMgr] : '';
        if (empty($aMgrOptions)) {
            SGL::raiseError('Je n\'ai pas trouv� les infos du Manager dans le tableau de donn�es.', PEAR_ERROR_DIE);
        }
        // Select appropriate pageTitle in array
        if(isset($aMgrOptions[$currAction]['pageTitle']) && !empty($aMgrOptions[$currAction]['pageTitle'])) {
            $this->addPageTitle($aMgrOptions[$currAction]['pageTitle'], true);
        } elseif(isset($aMgrOptions['all']['pageTitle']) && !empty($aMgrOptions['all']['pageTitle'])) {
            $this->addPageTitle($aMgrOptions['all']['pageTitle'], true);
        }
        // Select appropriate instructions in array
        if(isset($aMgrOptions[$currAction]['instructions']) && !empty($aMgrOptions[$currAction]['instructions'])) {
            $this->addInstructions($aMgrOptions[$currAction]['instructions'], true);
        } elseif(isset($aMgrOptions['all']['instructions']) && !empty($aMgrOptions['all']['instructions'])) {
            $this->addInstructions($aMgrOptions['all']['instructions'], true);
        }
        // Select appropriate manage links in array
        if(isset($aMgrOptions[$currAction]['manage']) && !empty($aMgrOptions[$currAction]['manage'])) {
            foreach($aMgrOptions[$currAction]['manage'] as $title => $link) {
                if(is_array($link)) {
                    // $link[0] is then the link and $link[1] the right level needed to display the link
                    $this->addManageLink($title, $link[0], $link[1]);
                } else {
                    $this->addManageLink($title, $link);
                }
            }
        } elseif(isset($aMgrOptions['all']['manage']) && is_array($aMgrOptions['all']['manage'])) {
            foreach($aMgrOptions['all']['manage'] as $title => $link) {
                if(is_array($link)) {
                    // $link[0] is then the link and $link[1] the right level needed to display the link
                    $this->addManageLink($title, $link[0], $link[1]);
                } else {
                    $this->addManageLink($title, $link);
                }
            }
        }
        // Select appropriate action links in array
        if(isset($aMgrOptions[$currAction]['actions']) && !empty($aMgrOptions[$currAction]['actions'])) {
            foreach($aMgrOptions[$currAction]['actions'] as $title => $link) {
                if(is_array($link)) {
                    // $link[0] is then the link and $link[1] the right level needed to display the link
                    $this->addActionLink($title, $link[0], $link[1]);
                } else {
                    $this->addActionLink($title, $link);
                }
            }
        }
        if(isset($aMgrOptions['all']['actions']) && is_array($aMgrOptions['all']['actions'])) {
            foreach($aMgrOptions['all']['actions'] as $title => $link) {
                if(is_array($link)) {
                    // $link[0] is then the link and $link[1] the right level needed to display the link
                    $this->addActionLink($title, $link[0], $link[1], true);
                } else {
                    $this->addActionLink($title, $link, null, true);
                }
            }
        }
    }

    /**
     * Render panel
     *
     * @access  public
     * @return  void
     */
    function renderPanel(&$output)
    {
        $output->managerPanel = $this->_aData;
    }

    /**
     * Adding pageTitle to Manager panel
     *
     * @access  public
     * @return  void
     */
    function addPageTitle($pageTitle, $bTranslate=true)
    {
        if($bTranslate) {
            $this->_aData['pageTitle'] = SGL_String::translate($pageTitle);
        } else {
            $this->_aData['pageTitle'] = $pageTitle;
        }
    }

    /**
     * Adding instructions to Manager panel
     *
     * @access  public
     * @return  void
     */
    function addInstructions($instructions, $bTranslate=true)
    {
        if($bTranslate) {
            $this->_aData['instructions'] = SGL_String::translate($instructions);
        } else {
            $this->_aData['instructions'] = $instructions;
        }
    }

    /**
     * Adding manage links (i.e. links to other related managers) to Manager panel
     *
     * @access  public
     * @param string $title Title of the link to display
     * @param array $link
     * @param const $rightLevel Currently has to be exactly same rightLevel. TODO: add role hierarchy.
     * @param bool $before If true, data is inserted at the beginning of the array, default false
     * @return  void
     */
    function addManageLink($title, $link, $rightLevel=null, $before=false)
    {
        if(!isset($this->_aData['manageLinks']) || !count($this->_aData['manageLinks'])) {
            $this->_aData['manageLinks'] = array();
        }
        $count = count($this->_aData['manageLinks']);
        if($rightLevel === null || SGL_HTTP_Session::getUserType() == $rightLevel) {
            if($before) {
                // Insert links at the beginning of array
                array_unshift($this->_aData['manageLinks'], array(
                                                               'title' => SGL_String::translate($title),
                                                               'link' => $link)
                                                               );
            } else {
                $this->_aData['manageLinks'][$count]['title'] = SGL_String::translate($title);
                $this->_aData['manageLinks'][$count]['link'] = $link;
            }
        }
    }

    /**
     * Adding action links (i.e. links to other related managers) to Manager panel
     *
     * @access  public
     * @param string $title Title of the link to display
     * @param array $link
     * @param bool $before If true, data is inserted at the beginning of the array, default false
     * @return  void
     */
    function addActionLink($title, $link, $rightLevel=null, $before=false)
    {
        if(!isset($this->_aData['actionLinks']) || !count($this->_aData['actionLinks'])) {
            $this->_aData['actionLinks'] = array();
        }
        $count = count($this->_aData['actionLinks']);
        if($rightLevel === null || SGL_HTTP_Session::getUserType() == $rightLevel) {
            if($before) {
                // Insert links at the beginning of array
                array_unshift($this->_aData['actionLinks'], array(
                                                               'title' => SGL_String::translate($title),
                                                               'link' => $link)
                                                               );
            } else {
                $this->_aData['actionLinks'][$count]['title'] = SGL_String::translate($title);
                $this->_aData['actionLinks'][$count]['link'] = $link;
            }
        }
    }
}
?>