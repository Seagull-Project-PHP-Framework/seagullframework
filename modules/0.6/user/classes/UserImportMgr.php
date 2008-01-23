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
// | UserImportMgr.php                                                         |
// +---------------------------------------------------------------------------+
// | Authors:                                                                  |
// |            Michal Pawlowski <misza@weblab.com.pl>                         |
// |            Demian Turner <demian@phpkitchen.com>                          |
// +---------------------------------------------------------------------------+
// $Id: UserImportMgr.php,v 1.7 2005/05/24 10:48:46 demian Exp $

require_once SGL_MOD_DIR . '/user/classes/UserMgr.php';
require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';

/**
 * Module allows administrator to load users from CSV file.
 *
 */
class UserImportMgr extends UserMgr
{

    function UserImportMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::UserMgr();

        $this->pageTitle    = 'User Import Manager';
        $this->template     = 'userImport.html';
        $this->da           = & UserDAO::singleton();

        $this->_aActionsMapping =  array(
            'list' => array('list'),
            'insertImportedUsers' => array('insertImportedUsers', 'redirectToUserMgr'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::validate($req, $input);

        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template    = $this->template;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->submitted   = $req->get('submitted');
        $input->csvFile     = $req->get('frmCsvFile');
        $input->organisation= $req->get('frmOrgId');
        $input->role        = $req->get('frmRoleId');

        if ($input->submitted) {
            if (empty($input->csvFile)) {
                $aErrors['csvFile'] = 'Please select a file.';
            }
            if (empty($input->organisation)) {
                $aErrors['organisation'] = 'Please select the organisation';
            }
            if (empty($input->role)) {
                $aErrors['role'] ='Please select the role';
            }
        }

        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  build roles array
        $aRoles = $this->da->getRoles();
        $output->aRoles = $aRoles;
        if ($this->conf['OrgMgr']['enabled']) {
            $output->aOrgs = $this->da->getOrgs();
        } else {
            $output->aOrgs = array(1 => 'default');
        }

        $output->aFiles = $this->_getCsvFiles();
        //  if no .csv files, give this information to user
        if (empty($output->aFiles)) {
            $output->noCsvFile = true;
        }
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    function _cmd_insertImportedUsers(&$input, &$output)
    {
        //  read in selected CSV file
        $aUsers = $this->_readCsvFile($input->csvFile);

        //  wrap _insert method to import users
        foreach ($aUsers as $aUser) {
            $user = new StdClass();
            $user->first_name = $aUser['firstname'];
            $user->last_name = $aUser['lastname'];
            $user->email = $aUser['email'];
            $user->role_id = $input->role;
            $user->organisation_id = $input->organisation;
            $user->username = strtolower($user->first_name);
            $user->passwd = 'password';

            //  assign to input object
            $input->user = $user;

            //  call parent _insert method
            parent::_cmd_insert($input, $output);
        }
    }

    /**
     * Redirects to UserMgr::list()
     *
     * @access  private
     */
    function _cmd_redirectToUserMgr(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  if no errors have occured, redirect
        if (!SGL_Error::count()) {
            SGL_HTTP::redirect(array('manager' => 'user'));

        //  else display error with blank template
        } else {
            $output->template = 'error.html';
        }
    }

    function _getCsvFiles()
    {
        //  first check if upload folder exists, if not create it
        if (!is_writable(SGL_UPLOAD_DIR)) {
            include_once 'System.php';
            $success = System::mkDir(array(SGL_UPLOAD_DIR));
            if (!$success) {
                SGL::raiseError('The upload directory does not appear to be writable, please give the
                webserver permissions to write to it', SGL_ERROR_FILEUNWRITABLE);
                return false;
            }
        }

        //  get array of files in upload folder
        if (@$fh = opendir(SGL_UPLOAD_DIR)) {
            while (false !== ($file = readdir($fh))) {
                //  remove unwanted dir elements
                if ($file == '.' || $file == '..' || $file == 'svn') {
                    continue;
                }
                //  and anything without csv extension
                if (($ext = end(explode('.', $file))) != 'csv') {
                    continue;
                }
                $aFiles[] =  $file;
            }
            closedir($fh);
        } else {
            SGL::raiseError('There was a problem reading the upload dir, if nothing has been ' .
                            'uploaded yet, it will not have been created', SGL_ERROR_NOFILE);
        }
        //  convert to hash needed by generateSelect
        if (isset($aFiles) && count($aFiles)) {
            foreach ($aFiles as $file) {
                $aStartFiles[$file] = $file;
            }
            return $aStartFiles;
        }
        return array();
    }

    function _readCsvFile($file)
    {
        require_once 'File/CSV.php';
        $csv = new File_CSV();

        $file = SGL_UPLOAD_DIR . '/' . $file;
        $conf = $csv->discoverFormat($file);

        while (false !== ($aLine = $csv->read($file, $conf))) {
            $aRecord = array();
            $firstName  = (empty($aLine[0])) ? 'blank' : $aLine[0];
            $lastName = (empty($aLine[1])) ? 'blank' : $aLine[1];

            //  cleanup name string
            $pattern = "/[A-Z].*/i";
            preg_match($pattern, $firstName, $aFnMatches);
            preg_match($pattern, $lastName, $aLnMatches);

            //  build user structure
            $aRecord['firstname'] = @$aFnMatches[0];
            $aRecord['lastname'] = $lastName;
            $aRecord['email'] = $aLine[2];
            $aResults[] = $aRecord;
            unset($aRecord);
        }
        return $aResults;
    }
}
?>