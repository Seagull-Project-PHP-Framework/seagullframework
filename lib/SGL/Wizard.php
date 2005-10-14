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
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | Wizard.php                                                                |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Wizard.php,v 1.8 2005/05/09 23:33:39 demian Exp $

/**
 * Inherit from Wizard to be able to sequence Mgr pages.
 *
 * To view the demo, request the file pageWiz.php in your browser
 *
 * To create a wizard, follow these guidelines:
 *  1.  inherit your Mgr class from Wizard
 *  2.  make sure to fire the Wizard constructor in your Mgr constructor, ie
 *          parent::Wizard();
 *  3.  at the beginning of your validate method, fire the parent's validate, ie
 *          parent::validate($req, $input);
 *  4.  replace and instance of $input->submit with  $this->submit
 *  5.  at the end of your validate method, fire the following method:
 *          $this->maintainState($input->obj); 
 *      where $input->obj represent the data from your form
 *  6.  at the end of your process method, fire the following method:
 *          parent::display($output);
 *  7.  when finished, about you're about to redirect to a 'success' page in your
 *      final process method, clear the wizard data from the session with 
 *          unset($_SESSION['wiz_sequence']);
 *
 * To use the register buttons you have to rename your form to frmWizard and make
 * sure an empty hidden field named jumpID is in your form.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.8 $
 * @since   PHP 4.1
 */
class SGL_Wizard extends SGL_Manager
{
    var $sequence = array();
    /**
     * Instantiates parent class, sets class called constant to avoid
     * duplicate calling.
     *
     * @access  public
     * @return  void
     */
    function SGL_Wizard()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    function maintainState(&$obj)
    {
        //  if session has content but request is empty
        //  then session overwrites request, otherwise vice versa
        if (isset($_SESSION['wiz_sequence'][$this->_getCurrent()]['data']) 
                && !(SGL_Wizard::isObjEmpty($_SESSION['wiz_sequence'][$this->_getCurrent()]['data'])) 
                &&   SGL_Wizard::isObjEmpty($obj)) {
            $obj = clone($_SESSION['wiz_sequence'][$this->_getCurrent()]['data']);
        } else {
            $_SESSION['wiz_sequence'][$this->_getCurrent()]['data'] = clone($obj);
        }
    }

    function _getCurrent()
    {
        if (count($this->sequence)) {
            foreach ($this->sequence as $k => $v) {
                if (isset($v['current']) && $v['current'] == true) {
                    return $k;
                }
            }
        }
        return 0;
    }

    function _next()
    {
        $nextKey = $this->_getCurrent() + 1;

        //  unset current
        $this->sequence[$this->_getCurrent()]['current'] = false;

        //  set next
        $this->sequence[$nextKey]['current'] = true;
        unset($_SESSION['message']);
        SGL_HTTP::redirect($this->sequence[$nextKey]['pageName']);
    }

    function _jump()
    {
        $nextKey = $this->jumpID;

        //  unset current
        $this->sequence[$this->_getCurrent()]['current'] = false;

        //  set next
        $this->sequence[$nextKey]['current'] = true;
        unset($_SESSION['message']);
        SGL_HTTP::redirect($this->sequence[$nextKey]['pageName']);
    }

    function _back()
    {
        $prevKey = $this->_getCurrent() - 1;

        //  unset current
        $this->sequence[$this->_getCurrent()]['current'] = false;

        //  set previous
        $this->sequence[$prevKey]['current'] = true;
        unset($_SESSION['message']);
        SGL_HTTP::redirect($this->sequence[$prevKey]['pageName']);
    }

    function _createButtons()
    {
        $max = count($this->sequence) -1;
        //  beginning
        if (isset($this->sequence[0]['current']) && $this->sequence[0]['current'] == true) {
            $html = '<input type="submit" name="next" class="buttonSubmit01" value="'.SGL_String::translate('next').'" />&nbsp;';
        //  end
        } elseif (isset($this->sequence[$max]['current']) && $this->sequence[$max]['current'] == true) {
            $html = '<input type="submit" name="finish" class="buttonSubmit01" value="'.SGL_String::translate('finish').'" />&nbsp;' .
                    '<br /><input type="submit" name="back" class="buttonSubmit02" value="'.SGL_String::translate('back').'" />&nbsp;';
        //  middle
        } else {
            $html = '<input type="submit" name="next" class="buttonSubmit01" value="'.SGL_String::translate('next').'" />&nbsp;' .
                    '<br /><input type="submit" name="back" class="buttonSubmit02" value="'.SGL_String::translate('back').'" />&nbsp;';
        }
        return $html;
    }

    function _createRegister()
    {
        $html = '';
        foreach($this->sequence as $key => $aSeq) {
            $html .= '<a class="subnavi" href="javascript:document.frmWizard.jumpID.value='.$key.';document.frmWizard.submit()">'.SGL_String::translate($aSeq['pageName']['managerName']).'</a><br/>';
        }
        $html .= '<span class="mainnavi">'.SGL_String::translate($this->module).'</span>';
        return $html;
    }

    function validate($req, &$input)
    {
        // if direct entering, redirect to default page
        // for security reasons
        if (!isset($_SESSION['wiz_sequence'][0]['pageName']['managerName'])) {
            $c = &SGL_Config::singleton();
            $conf = $c->getAll();
            $aParams = array(
                'moduleName'    => $conf['site']['defaultModule'],
                'managerName'   => $conf['site']['defaultManager'],
            );
            SGL_HTTP::redirect($aParams);
        }

        //  init sequence values from session
        $this->sequence     = &$_SESSION['wiz_sequence'];
        $input->back        = $req->get('back');
        $input->next        = $req->get('next');
        $input->finish      = $req->get('finish');
        $input->jumpID      = $req->get('jumpID');
        if (isset($input->back)) {
            $this->submit = 'back';
        } elseif (isset($input->next)) {
            $this->submit = 'next';
        } elseif (isset($input->finish)) {
            $this->submit = 'finish';
        } elseif (isset($input->jumpID) && (!empty($input->jumpID) || $input->jumpID === '0')) {
            $this->submit = 'jump';
            $this->jumpID = $req->get('jumpID');
        } else {
            $this->submit = null;
        }
    }

    function display(&$output)
    {
        //  put below in parent class
        $output->buttons = $this->_createButtons();
        $output->register = $this->_createRegister(); 

        //  catch back button
        if ($this->submit == 'back') {
            $this->_back();
        }
        if ($this->submit == 'next' && $this->validated)
            $this->_next();
            
        if ($this->submit == 'jump' && $this->validated && isset($this->jumpID)) {
            $this->_jump();
        }
    }
     /**
      * Determines whether object has any properties set.
      *
      * @access  public
      * @static
      * @param    object $obj
      * @return   boolean   returns true if at least one property is set
      */
    function isObjEmpty($obj)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aObjAttrs = get_object_vars($obj);
        if (is_array($aObjAttrs)) {
            foreach ($aObjAttrs as $k => $v) {
                if (!empty($v)) {
                    return false;
                }
            }
            return true;
        }
    }    
}
?>