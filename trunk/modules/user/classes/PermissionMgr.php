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
// | PermissionMgr.php                                                         |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: PermissionMgr.php,v 1.58 2005/05/28 13:46:30 demian Exp $

require_once SGL_CORE_DIR . '/Manager.php';
require_once SGL_MOD_DIR . '/user/classes/DA_User.php';
require_once 'DB/DataObject.php';

/**
 * Manages user permissions.
 *
 * @package User
 * @author  Demian Turner <demian@phpkitchen.com>
 * @author  Jacob Hanson <jacdx@jacobhanson.com>
 * @copyright Demian Turner 2004
 * @version $Revision: 1.58 $
 */
class PermissionMgr extends SGL_Manager
{
    function PermissionMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->template     = 'permManager.html';
        $this->pageTitle    = 'Permission Manager';
        $this->da           = & DA_User::singleton();

        $this->_aActionsMapping =  array(
            'add'       => array('add'),
            'insert'    => array('insert', 'redirectToDefault'),
            'edit'      => array('edit'),
            'update'    => array('update', 'redirectToDefault'),
            'delete'    => array('delete', 'redirectToDefault'),
            'scanNew'   => array('scanNew'),
            'insertNew' => array('insertNew', 'redirectToDefault'),
            'scanOrphaned' => array('scanOrphaned'),
            'deleteOrphaned' => array('deleteOrphaned', 'redirectToDefault'),
            'list'      => array('list'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated        = true;
        $input->pageTitle       = $this->pageTitle;
        $input->masterTemplate  = 'masterMinimal.html';
        $input->template        = $this->template;
        $input->submit          = $req->get('submitted');
        $input->action          = ($req->get('action')) ? $req->get('action') : 'list';
        $input->from            = ($req->get('pageID'))? $req->get('pageID'):0;
        $input->permId          = $req->get('frmPermId');
        $input->moduleId        = $req->get('frmModuleId');
        $input->perm            = (object) $req->get('perm');
        $input->scannedPerms    = (array) $req->get('scannedPerms');
        $input->aDelete         = $req->get('frmDelete');
        $input->totalItems      = $req->get('totalItems');
        $input->sortBy          = SGL_Util::getSortBy($req->get('frmSortBy'), SGL_SORTBY_USER);
        $input->sortOrder       = SGL_Util::getSortOrder($req->get('frmSortOrder'));

        $aErrors = array();
        if ($input->submit) {
            if ($input->action == 'insert') {
                if (empty($input->perm->name)) {
                    $this->validated = false;
                    $aErrors['name'] = 'You must enter a permission name';
                }
            }
        }
        //  if errors have occured
        if (is_array($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $input->template = ($input->action == 'update') ? 'permEdit.html' : 'permAdd.html';
            $input->pageTitle = ($input->action == 'update')
                ? $this->pageTitle . ' :: Edit'
                : $this->pageTitle . ' :: Add';
            include_once SGL_MOD_DIR . '/default/classes/ModuleMgr.php';
            $input->aModules = ModuleMgr::retrieveAllModules(SGL_RET_ID_VALUE);
            $input->currentModule = $input->perm->module_id;
        }
    }

    function _add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'permAdd.html';
        $output->pageTitle = $this->pageTitle . ' :: Add';
        $output->perm = DB_DataObject::factory($this->conf['table']['permission']);

        // setup module combobox
        require_once SGL_MOD_DIR . '/default/classes/ModuleMgr.php';

        $output->aModules = ModuleMgr::retrieveAllModules(SGL_RET_ID_VALUE);
        $output->currentModule = $input->permId;
    }

    function _scanNew(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'permScan.html';

        //  switch for template re-use
        $output->isNewForm = true;

        $output->pageTitle = $this->pageTitle . ' :: Detect & Add';

        //  manually generate listbox options, due to data structure
        $output->scannedOptions = '';
        foreach ($this->scanForNewPerms() as $k => $v) {
            $isSelected = in_array($k, $input->scannedPerms) ? ' selected' : '';
            $output->scannedOptions .= "\n<option value=\"{$v[1]}\"$isSelected>{$v[0]}</option>";
        }
    }

    function _scanOrphaned(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'permScan.html';

        //  switch for template re-use
        $output->isNewForm = false;
        $output->pageTitle = $this->pageTitle . ' :: Detect Orphaned';

        //  manually generate listbox options, due to data structure
        $output->scannedOptions = '';
        foreach ($this->scanForOrphanedPerms() as $k => $v) {
            $isSelected = in_array($k, $input->scannedPerms) ? ' selected' : '';
            $output->scannedOptions .= "\n<option value=\"{$v[1]}\"$isSelected>{$v[0]}</option>";
        }
    }

    function _insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        SGL_DB::setConnection($this->dbh);
        $oPerm = DB_DataObject::factory($this->conf['table']['permission']);

        //  check to see if perm already exists
        $oPerm->name = $input->perm->name;
        if (!$oPerm->find()) {
            $oPerm->free();
            $oPerm->setFrom($input->perm);
            $oPerm->permission_id = $this->dbh->nextId($this->conf['table']['permission']);
            $success = $oPerm->insert();

            //  update perms superset cache
            SGL_Cache::clear('perms');

            if ($success) {
                SGL::raiseMsg('perm successfully added');
            } else {
                SGL::raiseError('There was a problem inserting the record',
                    SGL_ERROR_NOAFFECTEDROWS);
            }
        } else {
            SGL::raiseMsg('perm already defined');
        }
    }

    function _insertNew(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  skip insert if no perms were selected
        if (empty($input->scannedPerms) || count($input->scannedPerms) == 0) {
            SGL::raiseMsg('No perms were selected');
        }
        $input->template = 'permScan.html';

        //  let's go transactional
        $this->dbh->autocommit();

        $errors = 0;
        foreach ($input->scannedPerms as $k=>$v) {
            //  undelimit form value into perm name, moduleId
            $p = explode('^', $v);

            $query = "  INSERT INTO {$this->conf['table']['permission']} (permission_id, name, module_id)
                        VALUES (" . $this->dbh->nextId($this->conf['table']['permission']) . ",'{$p[0]}',{$p[1]} )";
            if (is_a($this->dbh->query($query), 'PEAR_Error')) {
                $errors++;
            }
        }
        if ($errors > 0) {
            $this->dbh->rollBack();
            SGL::raiseError('There was a problem inserting the record(s)',
                SGL_ERROR_NOAFFECTEDROWS);
        } else {
            $this->dbh->commit();

            //  update perms superset cache
            SGL_Cache::clear('perms');
            SGL::raiseMsg('perm(s) successfully added');
        }
    }

    function _deleteOrphaned(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  skip insert if no perms were selected
        if (empty($input->scannedPerms) || count($input->scannedPerms) == 0) {
            SGL::raiseMsg('No perms were selected');
            return;
        }
        $input->template = 'permScan.html';

        $ret = $this->da->deleteOrphanedPerms($input->scannedPerms);

        if ($ret !== true) {
            SGL::raiseError('There was a problem deleting the record(s)',
                SGL_ERROR_NOAFFECTEDROWS);
        } else {

            //  update perms superset cache
            SGL_Cache::clear('perms');
            SGL::raiseMsg('perm successfully deleted');
        }
    }

    function _edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'permEdit.html';
        $output->pageTitle = $this->pageTitle . ' :: Edit';
        $oPerm = DB_DataObject::factory($this->conf['table']['permission']);
        $oPerm->get($input->permId);
        $output->perm = $oPerm;

        //  setup module combobox
        require_once SGL_MOD_DIR . '/default/classes/ModuleMgr.php';
        $output->aModules = ModuleMgr::retrieveAllModules(SGL_RET_ID_VALUE);
        $output->currentModule = ModuleMgr::getModuleIdByPermId($input->permId);
    }

    function _update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $oPerm = DB_DataObject::factory($this->conf['table']['permission']);
        $oPerm->get($input->perm->permission_id);
        $original = clone($oPerm);
        $oPerm->setFrom($input->perm);
        $success = $oPerm->update($original);

        //  update perms superset cache
        SGL_Cache::clear('perms');

        if ($success) {
            SGL::raiseMsg('perm successfully updated');
        } else {
            SGL::raiseError('There was a problem updating the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        foreach ($input->aDelete as $index => $permId) {
            $oPerm = DB_DataObject::factory($this->conf['table']['permission']);
            $oPerm->get($permId);
            $oPerm->delete();
            unset($oPerm);
        }
        //  deleting associated perms - taken care of by cascading deletes

        //  update perms superset cache
        SGL_Cache::clear('perms');

        //  redirect on success
        SGL::raiseMsg('perm successfully deleted');
    }

    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->pageTitle = $this->pageTitle . ' :: Browse';

        //  get limit and totalNumRows
        $limit = $_SESSION['aPrefs']['resPerPage'];

        //  if there is no sort by module filter, paginate results
        if (is_numeric($input->moduleId)) {
            $whereClause = " WHERE module_id = $input->moduleId";
            $disabled = true;
        } else {
            $whereClause = '';
            $disabled = false;
        }

        $allowedSortFields = array('permission_id','name');
        if (  !empty($input->sortBy)
           && !empty($input->sortOrder)
           && in_array($input->sortBy, $allowedSortFields)) {
                $orderBy_query = 'ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder ;
        } else {
            $orderBy_query = 'ORDER BY permission_id ASC ';
        }

        $query = "
            SELECT  permission_id, name, module_id, description
            FROM    {$this->conf['table']['permission']}
            $whereClause
            $orderBy_query ";

        $pagerOptions = array(
            'mode'      => 'Sliding',
            'delta'     => 3,
            'perPage'   => $limit,
            'totalItems'=> $input->totalItems,
//            'append'    => false,
//            'fileName'  => 'pageID/%d/'
        );
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions, $disabled);

        $output->aPagedData = $aPagedData;
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit || $input->moduleId) ? false : true;
        }
        $output->addOnLoadEvent("document.getElementById('frmUserMgrChooser').perms.disabled = true");

        //  setup module combobox
        require_once SGL_MOD_DIR . '/default/classes/ModuleMgr.php';
        $output->aModules = ModuleMgr::retrieveAllModules(SGL_RET_ID_VALUE);
        $output->currentModule = $output->moduleId;
   }

   /**
    * Finds perms that exist in class files but not in the database.
    *
    * @author  Jacob Hanson <jacdx@jacobhanson.com>
    * @copyright Jacob Hanson 2004
    * @access  public
    * @return  array          array of arrays (perm name/description, delimited perm values)
    */
    function scanForNewPerms()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get all perms currently in db
        $dbPerms = $this->da->getPermsByModuleId('', SGL_RET_ARRAY);

        $filePerms = $this->retrievePermsFromFiles();
        $newPerms = array();

        //  attempt to find each file perm in the db perms.
        //  if not found, add it to $newPerms
        foreach ($filePerms as $k => $filePerm) {
            $found = false;
            foreach ($dbPerms as $k2 => $dbPerm) {
                if ($dbPerm['name'] == $filePerm['perm']) {
                    $found = true;
                    break;
                }
            }
            //  add each, if not found. store display name and a
            //  delimited value used for form submission
            if (!$found) {

                //  ignore 'redirectToDefault' type perms
                if (strpos($filePerm['perm'], 'redirectToDefault') !== false) {
                    continue;
                }
                $permType = (strpos($filePerm['perm'], '_') === false) ? 'class' : 'method';
                $newPerms[] = array("{$filePerm['perm']} - $permType perm ({$filePerm['module_name']})",
                    "{$filePerm['perm']}^{$filePerm['module_id']}");
            }
        }
        return $newPerms;
    }

   /**
    * Finds perms that exist in the database but not in class files
    *
    * @author  Jacob Hanson <jacdx@jacobhanson.com>
    * @copyright Jacob Hanson 2004
    * @access  public
    * @return  array          array of arrays (perm name/description, delimited perm values)
    */
    function scanForOrphanedPerms()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get all perms currently in db
        $dbPerms = $this->da->getPermsByModuleId('', SGL_RET_ARRAY);

        $filePerms = $this->retrievePermsFromFiles();

        $orphanedPerms = array();

        //  attempt to find each file perm in the db perms.
        //  if not found, add it to $newPerms
        foreach ($dbPerms as $k => $dbPerm) {
            $found = false;
            foreach ($filePerms as $k2 => $filePerm) {
                if ($dbPerm['name'] == $filePerm['perm']) {
                    $found = true;
                    break;
                }
            }

            //  add each, if not found. store display name and a
            //  delimited value used for form submission
            if (!$found) {
                $permType = (strpos($dbPerm['name'], '_') === false) ? 'class' : 'method';
                $orphanedPerms[] = array("{$dbPerm['name']} - $permType perm ({$dbPerm['module_name']})",
                    "{$dbPerm['name']}^{$dbPerm['module_id']}");
            }
        }
        return $orphanedPerms;
    }

   /**
    * Scans class files and retrieves an array of class and method perms using the aAllowedActions property
    *
    * @author  Jacob Hanson <jacdx@jacobhanson.com>
    * @copyright Jacob Hanson 2004
    * @access  public
    * @return  array          array of arrays (perm name, module id, module name)
    */
    function retrievePermsFromFiles()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get a list of modules in db
        $query = "SELECT module_id, name FROM {$this->conf['table']['module']}";
		$modules = $this->dbh->getAssoc($query);

		if (is_a($modules, 'PEAR_Error')) {
           return SGL::raiseError('There was a problem retrieving modules',
                SGL_ERROR_NODATA);
		}

        $permsFound = array();

        //  scan
        require_once  'System.php';
        $files = System::find(array(SGL_MOD_DIR, '-maxdepth', 3, '-name' , '*.php'));

        foreach ($files as $k => $v) {
            //  only process files in 'classes' directories
            if (stristr ($v, 'classes') === false) {
                continue;
            }

            //  grab class name from path (platform independent)
            preg_match('/[\\\\\/](\\w+)\\.php/', $v, $className);
            if (isset($className[1])) {
                $className = strtolower($className[1]);
            } else {
                continue;
            }
            //  grab module name from path (platform independent)
            preg_match('/(\\w+)[\\\\\/]classes[\\\\\/]/', $v, $moduleName);
            if (isset($moduleName[1])) {
                $moduleName = strtolower($moduleName[1]);
            } else {
                continue;
            }
            //  load file as string (note: just for curiosity, I tried reading
            //  line by line and 1K and 4K chunks, so I wouldn't have to load the whole file
            //  and file_get_contents was a little faster! ...and it's 1 line of code
            $t = file_get_contents($v);

            //  find first actionsMapping statement, if any
            $pos1 = strpos($t, '$this->_aActionsMapping');
            if ($pos1 === false) continue;
            $pos2 = strpos($t, ';', $pos1);
            if ($pos2 === false) continue;

            //  narrow down to actionsMapping statement only, so preg
            //  doesn't have to work so hard
            $actionStr = substr($t, $pos1, $pos2 - $pos1);

            //  grab all allowed actions into an array
            $aTmp = array();
            preg_match_all("/[^']*'(\w*)'[^']*/s", $actionStr, $aTmp);

            //  remove duplicates
            $aActions = array_unique($aTmp[1]);

            //  find moduleId for moduleName
            $moduleId = array_search($moduleName, $modules);

            //  add class perm
            $permsFound[] = array(
                'perm' => $className,
                'module_id' => $moduleId,
                'module_name' => $moduleName);

            //  add each method perm, if not found. store display name and a
            //  delimited value used for form submission
            foreach ($aActions as $k2 => $v2) {
                $permsFound[] = array(
                    'perm' => "{$className}_{$v2}",
                    'module_id' => $moduleId,
                    'module_name' => $moduleName);
            }
            unset($aActions);

        }
        return $permsFound;
    }
}
?>
