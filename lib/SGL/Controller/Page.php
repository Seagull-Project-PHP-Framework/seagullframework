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

#FIXME: incomplete

/**
 * Abstract page controller.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.19 $
 * @abstract
 */
abstract class SGL_Controller_Page
{
    /**
     * Page master-template name.
     *
     * @access  public
     * @var     string
     */

    public $masterTemplate = 'master.html';
    /**
     * Page template name.
     *
     * @access  public
     * @var     string
     */
    public $template = '';

    /**
     * Page title, displayed in template and HTML title tags.
     *
     * @access  public
     * @var     string
     */
    public $pageTitle = 'default';

    /**
     * Array of action permitted by mgr subclass.
     *
     * @access  private
     * @var     array
     */
    protected $_aActionsMapping = array();

    protected $_aMessages;

    /**
     * Constructor.
     *
     * @access  public
     * @return  void
     */
    public function __construct()
    {
        $this->masterTemplate = SGL_Config::get('site.masterTemplate');
    }

    /**
     * Specific validations are implemented in sub classes.
     *
     * @param   SGL_Request     $req    SGL_Request object received from user agent
     * @return  boolean
     */
    abstract public function validate(SGL_Request $input);

    /**
     * Super class for implementing authorisation checks, delegates specific processing
     * to child classses.
     *
     * @param   SGL_Registry    $input  Input object received from validate()
     * @param   SGL_Output      $output Processed result
     * @return  mixed           true on success or PEAR_Error on failure
     */
    public function process(SGL_Request $input, SGL_Response $output)
    {
        $ctlrName = get_class($this);

        //  only implement authorisation check on demand
        if ( isset($this->conf[$ctlrName]['requiresAuth'])
                && $this->conf[$ctlrName]['requiresAuth'] == true
                && $this->conf['debug']['authorisationEnabled'])
        {
            //  determine global manager perm, ie that is valid for all actions
            //  in the mgr
            $ctlrPerm = SGL_String::pseudoConstantToInt('SGL_PERMS_' .
                strtoupper($ctlrName));

            //  check authorisation
            $ok = $this->_authorise($ctlrPerm, $ctlrName, $input);
            if ($ok !== true) {

                //  test for possible errors
                if (is_array($ok) && count($ok)) {

                    list($className, $methodName) = $ok;

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
                        throw new Exception('infinite loop detected, clear cookies and check perms',
                            SGL_ERROR_RECURSION);
                    }
                   // redirect to current or default screen
                    SGL::raiseMsg('authorisation failed');
                    $aHistory = SGL_Session::get('aRequestHistory');
                    $aLastRequest = isset($aHistory[1]) ? $aHistory[1] : false;
                    if ($aLastRequest) {
                        $aRedir = array(
                            'controllerName'   => $aLastRequest['controllerName'],
                            'moduleName'    => $aLastRequest['moduleName'],
                            );
                    } else {
                        $aRedir = $this->getDefaultPageParams();
                    }
                    SGL_Response::redirect($aRedir);
                } else {
                    throw new Exception('unexpected response during authorisation check',
                        SGL_ERROR_INVALIDAUTH);
                }
            }
        }

        //  all tests passed, execute relevant method
        foreach ($this->_aActionsMapping[$input->action] as $methodName) {
            $methodName = '_do'.$methodName;
            $this->{$methodName}($input, $output);
        }
        return true;
    }

    /**
     * Parent page display method.
     *
     * @access  public
     * @param   SGL_Output  $output Input object that has passed through validation
     * @return  void
     */
    abstract public function display(SGL_Response $output);

    /**
     * Perform authorisation on specified action methods.
     *
     * @param integer $ctlrPerm
     * @param string  $ctlrName
     * @param SGL_Request $input
     * @return mixed true on success, array of class/method names on failure
     */
    function _authorise($ctlrPerm, $ctlrName, $input)
    {
        // if user has no global manager perms check for each action
        if (!SGL_Session::hasPerms($ctlrPerm) && !SGL::runningFromCLI()) {

            // and if chained methods to be called are allowed
            $ret = true;
            foreach ($this->_aActionsMapping[$input->action] as $methodName) {

                //  allow redirects without perms
                if (preg_match("/redirect/", $methodName)) {
                    continue;
                }
                $methodName = '_cmd_' . $methodName;

                //  build relevant perms constant
                $perm = SGL_String::pseudoConstantToInt('SGL_PERMS_' .
                    strtoupper($ctlrName . $methodName));

                //  return false if user doesn't have method specific or classwide perms
                if (SGL_Session::hasPerms($perm) === false) {
                    $ret = array($ctlrName, $methodName);
                    break;
                }
            }
        } else {
            $ret = true;
        }
        return $ret;
    }

    public function getMessages()
    {
        return $this->_aMessages;
    }

    public function setMessages(array $aMessages)
    {
        $this->_aMessages = $aMessages;
    }
}
?>