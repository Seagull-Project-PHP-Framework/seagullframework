<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
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
// | Seagull 0.5                                                               |
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
    var $pageTitle = '';

    /**
     * Flag indicated is Page validation passed.
     *
     * @access  public
     * @var     boolean
     */
    var $validated = false;

    /**
     * Current module name.
     *
     * @access  public
     * @var     string
     */
    var $module = '';

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

    // +---------------------------------------+
    // | Public workflow methods               |
    // |                                       |
    // | All manager classes in the framework  |
    // | workflow must follow the validate,    |
    // | process, display pattern.             |
    // +---------------------------------------+

    /**
     * Page validation method, extended by all Manager classes.
     *
     * Get tabID required by ALL pages, same goes for msg, action, from
     *
     * @abstract
     *
     * @access  public
     * @param   object  $req    SGL_Request object received from user agent
     * @param   object  $input  SGL_Output object from Controller
     * @return  void
     */
    function validate($req, &$input) {}

    /**
     * Abstract Page processing method.
     *
     * @access  public
     * @abstract
     * @param   object  $input  Input object received from validate()
     * @param   object  $output Processed result
     * @return  void
     */
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $className = get_class($this);

        //  determine if action param from $_GET is valid
        if (!(array_key_exists($input->action, $this->_aActionsMapping))) {
            SGL::raiseError('The specified method, ' . $input->action .
                ' does not exist', SGL_ERROR_NOMETHOD, PEAR_ERROR_DIE);
        }
        if (!count($this->conf)) {
            SGL::raiseError('It appears you forgot to fire SGL_Manager\'s '.
            'constructor - please add "parent::SGL_Manager();" in your '.
            'manager\'s constructor.', SGL_ERROR_NOCLASS, PEAR_ERROR_DIE);
        }
        //  don't perform checks if authentication is disabled in debug
        if ($this->conf['debug']['authenticationEnabled']) {

            //  setup classwide perm
            $classPerm = @constant('SGL_PERMS_' . strtoupper($className));

            // if user has no class perms check for each action
            if (! SGL_HTTP_Session::hasPerms($classPerm)) {

                // ...and if linked methods to be called are allowed
                foreach ($this->_aActionsMapping[$input->action] as $methodName) {

                    //  allow redirects without perms
                    if ($methodName == 'redirectToDefault') {
                        continue;
                    }
                    $methodName = '_' . $methodName;

                    //  build relevant perms constant
                    $perm = @constant('SGL_PERMS_' . strtoupper($className . $methodName));

                    //  redirect if user doesn't have method specific or classwide perms
                    if (! SGL_HTTP_Session::hasPerms($perm)) {
                        SGL::raiseMsg('you do not have perms');
                        SGL::logMessage('You do not have the required perms for ' .
                            $className . '::' .$methodName, PEAR_LOG_NOTICE);

                        //  make sure no infinite redirections
                        $lastRedirected = SGL_HTTP_Session::get('redirected');
                        $now = time();
                        SGL_HTTP_Session::set('redirected', $now);
                        if ($now - $lastRedirected < 2) {
                            PEAR::raiseError('infinite loop detected, clear cookies and check perms',
                                SGL_ERROR_RECURSION, PEAR_ERROR_DIE);
                        }
                        //  get default params for logout page
                        $aParams = $this->getDefaultPageParams();
                        SGL_HTTP::redirect($aParams);
                    }
                }
            }
        }

        //  all tests passed, execute relevant method
        foreach ($this->_aActionsMapping[$input->action] as $methodName) {
            $methodName = '_'.$methodName;
            $this->$methodName($input, $output);
        }
    }

    /**
     * Abstract page display method.
     *
     * @abstract
     *
     * @access  public
     * @param   object  $output Input object that has passed through validation
     * @return  void
     */
    function display(&$output) {}

    function isValid()
    {
        return $this->validated;
    }

    function _redirectToDefault(&$input, &$output)
    {
        //  must not logmessage here

        //  if no errors have occured, redirect
        if (!SGL_Error::count()) {
            SGL_HTTP::redirect(array());

        //  else display error with blank template
        } else {
            $output->template = 'docBlank.html';
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
        $aDefaultParams = !empty($defaultParams) ? explode('/', $defaultParams) : array();

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
}
?>