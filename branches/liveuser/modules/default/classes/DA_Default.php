<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Demian Turner                                         |
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
// | DA_Default.php                                                            |
// +---------------------------------------------------------------------------+
// | Authors:   Demian Turner <demian@phpkitchen.com>                          |
// +---------------------------------------------------------------------------+
// $Id: DA_Default.php,v 1.14 2005/06/21 23:26:24 demian Exp $

/**
 * Data access methods for the default module.
 *
 * @package Default
 * @author  Demian Turner <demian@phpkitchen.com>
 * @copyright Demian Turner 2005
 */
class DA_Default extends SGL_Manager
{
    /**
     * Constructor - set default resources.
     *
     * @return DA_Default
     */
    function DA_Default()
    {
        parent::SGL_Manager();
    }

    /**
     * Returns a singleton DA_Default instance.
     *
     * example usage:
     * $da = & DA_Default::singleton();
     * warning: in order to work correctly, the DA
     * singleton must be instantiated statically and
     * by reference
     *
     * @access  public
     * @static
     * @return  DA_Default reference to DA_Default object
     */
    function &singleton()
    {
        static $instance;

        // If the instance is not there, create one
        if (!isset($instance)) {
            $instance = new DA_Default();
        }
        return $instance;
    }

    //  modules
    /**
     * Returns true if module record exists in db.
     *
     * @return boolean
     */
    function moduleIsRegistered($moduleName)
    {
        $query = "
            SELECT  module_id
            FROM    {$this->conf['table']['module']}
            WHERE   name = '$moduleName'";

        $exists = $this->dbh->getOne($query);

        return ! is_null($exists);
    }

    /**
     * Returns an array of all modules.
     *
     * @param integer $type
     * @return array
     */
    function retrieveAllModules($type = '')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        switch ($type) {
        case SGL_RET_ID_VALUE:
            $query = "  SELECT module_id, title
                        FROM {$this->conf['table']['module']}
                        ORDER BY module_id";
            $aMods = $this->dbh->getAssoc($query);
            break;

        case SGL_RET_NAME_VALUE:
        default:
            $query = "  SELECT name, title
                        FROM {$this->conf['table']['module']}
                        ORDER BY name";
            $aModules = $this->dbh->getAll($query);
            foreach ($aModules as $k => $oVal) {
                if ($oVal->name == 'documentor') {
                    continue;
                }
                $aMods[$oVal->name] = $oVal->title;
            }
            break;
        }
        return $aMods;
    }

    function getAllModules()
    {
        $query = "
            SELECT module_id, is_configurable, name, title, description, admin_uri, icon
            FROM {$this->conf['table']['module']}
            ORDER BY module_id";
        $aModules = $this->dbh->getAll($query);
        return $aModules;
    }

    /**
     * Returns module id by perm id.
     *
     * @param integer $permId
     * @return integer
     */
    function getModuleIdByPermId($permId = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $permId = ($permId === null) ? 0 : $permId;
        $query = "  SELECT  module_id
                    FROM    {$this->conf['table']['permission']}
                    WHERE   permission_id = $permId
                ";
        $moduleId = $this->dbh->getOne($query);
        return $moduleId;
    }

    function getPackagesByChannel($channel='phpkitchen')
    {
        require_once 'PEAR/Registry.php';
        $registry = new PEAR_Registry('/usr/local/lib/php');
        #$registry = new PEAR_Registry(SGL_LIB_PEAR_DIR);
        $aSglModules = $registry->_listPackages($channel);
        return $aSglModules;
    }

    /**
     * Returns a DataObjects Module object.
     *
     * @param integer   $id optional module id
     * @return object   A DataObjects module object
     */
    function getModuleById($id = null)
    {
        $oModule = DB_DataObject::factory($this->conf['table']['module']);
        if (!is_null($id)) {
            $oModule->get($id);
        }
        return $oModule;
    }

    function getModuleIdByName($name)
    {
        $query = "
            SELECT module_id 
            FROM {$this->conf['table']['module']} 
            WHERE name=". $this->dbh->quote($name);
        $result = $this->dbh->getOne($query);
        return $result;   
    }

    function addModule($oModule)
    {
        SGL_DB::setConnection();
        if (!isset($oModule->module_id)) {
            $oModule->module_id = $this->dbh->nextId($this->conf['table']['module']);
        }
        $oModule->is_configurable = 1;
        $oModule->title = ucfirst($oModule->name);
        $ok = $oModule->insert();
        return $ok;
    }

//    function updateGuiTranslations($aTrans, $langID, $module)
//    {
//        SGL_Translation::updateGuiTranslation($aTrans, $langID, $module);
//    }
}