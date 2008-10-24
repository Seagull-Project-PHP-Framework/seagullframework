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
// | Seagull 2.0                                                               |
// +---------------------------------------------------------------------------+
// $Id$

#FIXME: incomplete

/**
 * Abstract page controller.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.19 $
 * @abstract
 */
abstract class SGL2_Controller_Page
{
    /**
     * Layout template name.
     *
     * @var     string
     */
    protected $_layout;

    /**
     * Page template name.
     *
     * @var     string
     */
    protected $_template;

    /**
     * Page title, displayed in template and HTML title tags.
     *
     * @var     string
     */
    protected $_pageTitle = 'default';

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
        $this->setLayout(SGL2_Config::get('site.layout'));
    }

    /**
     * Assembles layout which is built from header, banner, main body, footer, etc
     *
     * @param string $layout
     */
    public function setLayout($layout)
    {
        $this->_layout = $layout;
    }

    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     * Sets template for main content in page.
     *
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    public function getTemplate()
    {
        return $this->_template;
    }

    public function setPageTitle($title)
    {
        $this->_pageTitle = $title;
    }

    public function getPageTitle()
    {
        return $this->_pageTitle;
    }

    public function addActionMapping(array $aActionMap)
    {
        $this->_aActionsMapping = $aActionMap;
    }

    /**
     * Specific validations are implemented in sub classes.
     *
     * @param   SGL2_Request     $req    SGL2_Request object received from user agent
     * @return  boolean
     */
    abstract public function validate(SGL2_Request $input);

    /**
     * Super class for implementing authorisation checks, delegates specific processing
     * to child classses.
     *
     * @param   SGL2_Request    $input  Input object received from validate()
     * @param   SGL2_Response   $output Processed result
     * @return  mixed           true on success
     */
    public function process(SGL2_Request $input, SGL2_Response $output)
    {
        $ctlrName = get_class($this);

        //  only implement authorisation check on demand
        if ( isset($this->conf[$ctlrName]['requiresAuth'])
                && $this->conf[$ctlrName]['requiresAuth'] == true
                && $this->conf['debug']['authorisationEnabled'])
        {
            //  determine global controller perm, ie that is valid for all actions
            //  in the mgr
            $ctlrPerm = SGL2_String::pseudoConstantToInt('SGL2_PERMS_' .
                strtoupper($ctlrName));

            //  check authorisation
            $ok = $this->_authorise($ctlrPerm, $ctlrName, $input);
            if ($ok !== true) {

                //  test for possible errors
                if (is_array($ok) && count($ok)) {

                    list($className, $methodName) = $ok;

                    //  make sure no infinite redirections
                    $lastRedirected = SGL2_Session::get('redirected');
                    $now = time();
                    SGL2_Session::set('redirected', $now);

                    //  if redirects happen less than 2 seconds apart, and there are greater
                    //  than 2 of them, recursion is happening
                    if ($now - $lastRedirected < 2) {
                        $redirectTimes = SGL2_Session::get('redirectedTimes');
                        $redirectTimes ++;
                        SGL2_Session::set('redirectedTimes', $redirectTimes);
                    } else {
                        SGL2_Session::set('redirectedTimes', 0);
                    }
                    if (SGL2_Session::get('redirectedTimes') > 2) {
                        throw new Exception('infinite loop detected, clear cookies and check perms',
                            SGL2_ERROR_RECURSION);
                    }
                   // redirect to current or default screen
//SGL::raiseMsg('authorisation failed');
                    $aHistory = SGL2_Session::get('aRequestHistory');
                    $aLastRequest = isset($aHistory[1]) ? $aHistory[1] : false;
                    if ($aLastRequest) {
                        $aRedir = array(
                            'controllerName'   => $aLastRequest['controllerName'],
                            'moduleName'    => $aLastRequest['moduleName'],
                            );
                    } else {
                        $aRedir = $this->getDefaultPageParams();
                    }
                    SGL2_Response::redirect($aRedir);
                } else {
                    throw new Exception('unexpected response during authorisation check',
                        SGL2_ERROR_INVALIDAUTH);
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
     * @param   SGL2_Output  $output Input object that has passed through validation
     * @return  void
     */
    abstract public function display(SGL2_Response $output);

    /**
     * Perform authorisation on specified action methods.
     *
     * @param integer $ctlrPerm
     * @param string  $ctlrName
     * @param SGL2_Request $input
     * @return mixed true on success, array of class/method names on failure
     */
    protected function _authorise($ctlrPerm, $ctlrName, $input)
    {
        // if user has no global controller perms check for each action
        if (!SGL2_Session::hasPerms($ctlrPerm) && $input->getType() != SGL2_Request::CLI) {

            // and if chained methods to be called are allowed
            $ret = true;
            foreach ($this->_aActionsMapping[$input->action] as $methodName) {

                //  allow redirects without perms
                if (preg_match("/redirect/", $methodName)) {
                    continue;
                }
                $methodName = '_cmd_' . $methodName;

                //  build relevant perms constant
                $perm = SGL2_String::pseudoConstantToInt('SGL2_PERMS_' .
                    strtoupper($ctlrName . $methodName));

                //  return false if user doesn't have method specific or classwide perms
                if (SGL2_Session::hasPerms($perm) === false) {
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