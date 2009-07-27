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
// | Manager.php                                                               |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Manager.php,v 1.19 2005/06/13 12:00:25 demian Exp $

/**
 * Abstract model controller for all the 'manager' classes.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.19 $
 * @abstract
 */
class SGL_Manager
{
    /**
     * Page master-template name.
     *
     * @access  public
     * @var     string
     */

    var $masterTemplate = 'master.html';
    /**
     * Page template name.
     *
     * @access  public
     * @var     string
     */
    var $template = '';

    /**
     * Page title, displayed in template and HTML title tags.
     *
     * @access  public
     * @var     string
     */
    var $pageTitle = 'default';

    /**
     * Flag indicated is Page validation passed.
     *
     * @access  public
     * @var     boolean
     */
    var $validated = false;

    /**
     * Sortby flag, used in child classes.
     *
     * @access  public
     * @var     string
     */
    var $sortBy = '';

    /**
     * Array of action permitted by mgr subclass.
     *
     * @access  private
     * @var     array
     */
    var $_aActionsMapping = array();

    var $conf = array();
    var $dbh = null;


    /**
     * Constructor.
     *
     * @access  public
     * @return  void
     */
    function SGL_Manager()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();
        $this->dbh = $this->_getDb();

        //  detect if trans2 support required
        if ($this->conf['translation']['container'] == 'db') {
            $this->trans = & SGL_Translation::singleton();
        }

        //  determine the value for the masterTemplate
        if (!empty($this->conf['site']['masterTemplate'])) {
            $this->masterTemplate = $this->conf['site']['masterTemplate'];
        }
    }

    function &_getDb()
    {
        $locator = &SGL_ServiceLocator::singleton();
        $dbh = $locator->get('DB');
        if (!$dbh) {
            $dbh = & SGL_DB::singleton();
            $locator->register('DB', $dbh);
        }
        return $dbh;
    }

    function getConfig()
    {
        $c = &SGL_Config::singleton();
        return $c;
    }

    /**
     * Specific validations are implemented in sub classes.
     *
     * @abstract
     *
     * @access  public
     * @param   SGL_Request     $req    SGL_Request object received from user agent
     * @param   SGL_Registry    $input  SGL_Registry for storing data
     * @return  void
     */
    function validate($req, &$input) {}

    /**
     * Super class for implementing authorisation checks, delegates specific processing
     * to child classses.
     *
     * @access  public
     * @param   SGL_Registry    $input  Input object received from validate()
     * @param   SGL_Output      $output Processed result
     * @return  mixed           true on success or PEAR_Error on failure
     */
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $mgrName = SGL_Inflector::caseFix(get_class($this));
        $defaultMgrLoaded = false;

        if (SGL_Error::count()) {
            $oLastError = SGL_Error::getLast();
            if ($oLastError->getCode() == SGL_ERROR_RESOURCENOTFOUND) {
                $defaultMgrLoaded = true;
                $output->setStatusCode(404);
            }

        //  determine if action param from $_GET is valid
        } elseif (!(array_key_exists($input->action, $this->_aActionsMapping))) {
            return SGL::raiseError('The specified method, ' . $input->action .
                ' does not exist', SGL_ERROR_NOMETHOD);
        }
        if (!count($this->conf)) {
            return SGL::raiseError('It appears you forgot to fire SGL_Manager\'s '.
                'constructor - please add "parent::SGL_Manager();" in your '.
                'manager\'s constructor.', SGL_ERROR_NOCLASS);
        }
        //  only implement authorisation check on demand
        if ( isset($this->conf[$mgrName]['requiresAuth'])
                && $this->conf[$mgrName]['requiresAuth'] == true
                && $this->conf['debug']['authorisationEnabled'])
        {
            //  determine global manager perm, ie that is valid for all actions
            //  in the mgr
            $mgrPerm = SGL_String::pseudoConstantToInt('SGL_PERMS_' . strtoupper($mgrName));

            //  check authorisation
            $ok = $this->_authorise($mgrPerm, $mgrName, $input);
            if ($ok !== true) {

                //  test for possible errors
                if (is_array($ok) && count($ok)) {

                    list($className, $methodName) = $ok;
                    SGL::logMessage('Unauthorised user '.SGL_Session::getUid() .' attempted to access ' .
                        $className . '::' .$methodName, PEAR_LOG_WARNING);

                    //  make sure no infinite redirections
                    $lastRedirected = SGL_Session::get('redirected');
                    $now = time();
                    SGL_Session::set('redirected', $now);

                    //  if redirects happen less than 2 seconds apart, and there are greater
                    //  than 2 of them, recursion is happening
                    if ($now - $lastRedirected < 2) {
                        $redirectTimes = SGL_Session::get('redirectedTimes');
                        $redirectTimes ++;
                        SGL_Session::set('redirectedTimes', $redirectTimes);
                    } else {
                        SGL_Session::set('redirectedTimes', 0);
                    }
                    if (SGL_Session::get('redirectedTimes') > 2) {
                        return PEAR::raiseError('infinite loop detected, clear cookies and check perms',
                            SGL_ERROR_RECURSION);
                    }
                   // redirect to current or default screen
                    SGL::raiseMsg('authorisation failed');
                    $aHistory = SGL_Session::get('aRequestHistory');
                    $aLastRequest = isset($aHistory[1]) ? $aHistory[1] : false;
                    if ($aLastRequest) {
                        $aRedir = array(
                            'managerName'   => $aLastRequest['managerName'],
                            'moduleName'    => $aLastRequest['moduleName'],
                            );
                    } else {
                        $aRedir = $this->getDefaultPageParams();
                    }
                    SGL_HTTP::redirect($aRedir);
                } else {
                    return PEAR::raiseError('unexpected response during authorisation check',
                        SGL_ERROR_INVALIDAUTH);
                }
            }
        }
        if (!$defaultMgrLoaded) {
            //  all tests passed, execute relevant method
            foreach ($this->_aActionsMapping[$input->action] as $methodName) {
                $methodName = '_cmd_'.$methodName;
                $this->{$methodName}($input, $output);
            }
        }
        return true;
    }

    /**
     * Perform authorisation on specified action methods.
     *
     * @param integer $mgrPerm
     * @param string  $mgrName
     * @param SGL_Registry $input
     * @return mixed true on success, array of class/method names on failure
     */
    function _authorise($mgrPerm, $mgrName, $input)
    {
        // if user has no global manager perms check for each action
        if (!SGL_Session::hasPerms($mgrPerm) && !SGL::runningFromCLI()) {

            // and if chained methods to be called are allowed
            $ret = true;
            foreach ($this->_aActionsMapping[$input->action] as $methodName) {

                //  allow redirects without perms
                if (preg_match("/redirect/", $methodName)) {
                    continue;
                }
                $methodName = '_cmd_' . $methodName;

                //  build relevant perms constant
                $perm = SGL_String::pseudoConstantToInt('SGL_PERMS_' . strtoupper($mgrName . $methodName));

                //  return false if user doesn't have method specific or classwide perms
                if (SGL_Session::hasPerms($perm) === false) {
                    $ret = array($mgrName, $methodName);
                    break;
                }
            }
        } else {
            $ret = true;
        }
        return $ret;
    }

    /**
     * Parent page display method.
     *
     * Sets CSS file if supplied in request
     *
     * @abstract
     *
     * @access  public
     * @param   SGL_Output  $output Input object that has passed through validation
     * @return  void
     */
    function display(&$output)
    {
        //  reinstate dynamically added css
        if (!$output->manager->isValid()) {
            if (!count($output->aCssFiles)) {
                //  get action
                $cssFile = $output->request->get('cssFile');
                if (!is_null($cssFile)) {
                    $output->addCssFile($cssFile);
                }
            }
        }
    }

    /**
     * Return true if child class has validated.
     *
     * @return boolean
     */
    function isValid()
    {
        return $this->validated;
    }

    /**
     * Default redirect for all Managers.
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    function _cmd_redirectToDefault(&$input, &$output)
    {
        //  must not logmessage here

        //  if no errors have occured, redirect
        if (!SGL_Error::count()) {
            SGL_HTTP::redirect();

        //  else display error with blank template
        } else {
            $output->template = 'error.html';
        }
    }

    /**
     * Returns details of the module/manager/params defaults
     * set in configuration, used for logouts and redirects.
     *
     * @return array
     */
    function getDefaultPageParams()
    {
        $moduleName     = $this->conf['site']['defaultModule'];
        $managerName    = $this->conf['site']['defaultManager'];
        $defaultParams  = $this->conf['site']['defaultParams'];
        $aDefaultParams = !empty($defaultParams)
            ? explode('/', $defaultParams)
            : array();

        $aParams = array(
            'moduleName'    => $moduleName,
            'managerName'   => $managerName,
            );

        //  convert string into hash and merge with $aParams
        $aRet = array();
        if ($numElems = count($aDefaultParams)) {
            $aTmp = array();
            for ($x = 0; $x < $numElems; $x++) {
                if ($x % 2) { // if index is odd
                    $aTmp['varValue'] = urldecode($aDefaultParams[$x]);
                } else {
                    // parsing the parameters
                    $aTmp['varName'] = urldecode($aDefaultParams[$x]);
                }
                //  if a name/value pair exists, add it to request
                if (count($aTmp) == 2) {
                    $aRet[$aTmp['varName']] = $aTmp['varValue'];
                    $aTmp = array();
                }
            }
        }
        $aMergedParams = array_merge($aParams, $aRet);
        return $aMergedParams;
    }

    function handleError($oError, &$output)
    {
        $output->template = 'error.html';
        $output->masterTemplate = 'masterNoCols.html';
        $output->aError = array(
            'message'   => $oError->getMessage(),
            'debugInfo' => $oError->getDebugInfo(),
            'level'     => $oError->getCode(),
            'errorType' => SGL_Error::constantToString($oError->getCode())
        );
    }
}
?>