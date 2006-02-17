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
 * @version $Revision: 1.14 $
 */
class DA_Default
{
    /**
     * Constructor - set default resources.
     *
     * @return DA_Default
     */
    function DA_Default()
    {
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();
        $this->dbh = $this->_getDb();
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

    function getSectionsByRoleId($sectionId = 0)
    {
        $query = "
            SELECT * FROM {$this->conf['table']['section']}
            WHERE parent_id = " . $sectionId . '
            ORDER BY order_id';

        $result = $this->dbh->query($query);
        if (DB::isError($result, DB_ERROR_NOSUCHTABLE)) {
            SGL::raiseError('The database exists, but does not appear to have any tables,
                please delete the config file from the var directory and try the install again',
                SGL_ERROR_DBFAILURE, PEAR_ERROR_DIE);
        }
        if (DB::isError($result)) {
            SGL::raiseError('Cannot connect to DB, check your credentials, exiting ...',
                SGL_ERROR_DBFAILURE, PEAR_ERROR_DIE);
        }
        return $result;
    }

    function getSectionById($sectionId)
    {
        $query = "
            SELECT  *
            FROM    {$this->conf['table']['section']}
            WHERE   section_id = " . $sectionId;
        return $this->dbh->getRow($query);
    }

    function getSectionNameById($sectionId)
    {
        $query = "
            SELECT  title
            FROM    {$this->conf['table']['section']}
            WHERE   section_id = " . $sectionId;
        return $this->dbh->getOne($query);
    }

    function getAllAliases()
    {
        $query = "
        SELECT uri_alias, resource_uri
        FROM {$this->conf['table']['uri_alias']} u, {$this->conf['table']['section']} s
        WHERE u.section_id = s.section_id
        ";
        return $this->dbh->getAssoc($query);
    }

    function addUriAlias($id, $aliasName, $target)
    {
        $aliasName = $this->dbh->quoteSmart($aliasName);

        $query = "
            INSERT INTO {$this->conf['table']['uri_alias']}
            (uri_alias_id, uri_alias, section_id)
            VALUES($id, $aliasName, $target)";
        return $this->dbh->query($query);
    }

    function updateUriAlias($aliasName, $target)
    {
        $aliasName = $this->dbh->quoteSmart($aliasName);

        $query = "
            UPDATE {$this->conf['table']['uri_alias']}
            SET uri_alias = $aliasName
            WHERE section_id = $target";
        return $this->dbh->query($query);
    }

    function getAliasBySectionId($id)
    {
        $query = "
            SELECT uri_alias
            FROM {$this->conf['table']['uri_alias']}
            WHERE section_id = $id
            LIMIT 1";
        return $this->dbh->getOne($query);
    }

    function getAliasIdBySectionId($id)
    {
        $query = "
            SELECT uri_alias_id
            FROM {$this->conf['table']['uri_alias']}
            WHERE section_id = $id
            ";
        return $this->dbh->getOne($query);
    }

    function deleteAliasBySectionId($sectionId)
    {
        $query = "
            DELETE FROM {$this->conf['table']['uri_alias']}
            WHERE section_id = $sectionId";
        return $this->dbh->query($query);
    }

    function getAliasById($id)
    {
        $query = "
            SELECT uri_alias
            FROM {$this->conf['table']['uri_alias']}
            WHERE uri_alias_id = $id
           ";
        return $this->dbh->getOne($query);
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
}