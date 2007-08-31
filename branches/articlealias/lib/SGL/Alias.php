<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2007, Demian Turner                                         |
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
// | Alias.php                                                                 |
// +---------------------------------------------------------------------------+
// | Author:   Elijah Insua <tmpvar@gmail.com>                                 |
// +---------------------------------------------------------------------------+

/**
 * Alias Mapping
 *
 * @package SGL
 * @author  Elijah Insua <tmpvar@gmail.com>
 */
class SGL_Alias
{
    /**
     * Holds configuration
     *
     * @var array
     */
    var $conf = array();

    /**
     * DB abstract layer
     *
     * @var DB resource
     */
    var $dbh = null;

    /**
     * Constructor.
     *
     * @access  public
     * @return  void
     */
    function SGL_Alias()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $c = &SGL_Config::singleton();
        $this->conf = $c->ensureModuleConfigLoaded('default');
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
     * Add an alias by entityId
     *
     * @param int entityId
     * @param array aAlias
     */
    function add($entityId, $aAlias)
    {
        require_once 'DB/DataObject.php';
        $dbo = DB_DataObject::factory($this->conf['table']['entity_alias']);
        $dbo->entity_alias_id = $this->dbh->nextId($this->conf['table']['entity_alias']);
        $dbo->entity_id = $entityId;
        $dbo->setFrom($aAlias);
        return $dbo->insert();
    }

    /**
     * Delete an alias by it's entity ID
     * NOTE: Accepts an array of values or a single id
     *
     * @param int entityID
     * @param string type
     */
    function deleteByEntityId($entityID,$type)
    {
        $delete = "DELETE FROM {$this->conf['table']['entity_alias']}
                   WHERE entity_id = $entityID
                   AND entity_type={$this->dbh->quoteSmart($type)}";
        if (PEAR::isError($this->dbh->query($delete))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get all aliases by entityID
     *
     *
     */
    function getByEntityId($entityID,$type)
    {
        $entityID = (int)$entityID;
        $query = "SELECT *
                  FROM {$this->conf['table']['entity_alias']}
                  WHERE entity_id = $entityID
                  AND entity_type={$this->dbh->quoteSmart($type)}";
        $result = $this->dbh->getAll($query,DB_FETCHMODE_ASSOC);

        if (!PEAR::isError($result) && !empty($result)) {
            return $result;
        } else {
            return array();
        }
    }


    function update($aliasID, $target)
    {
        /* clear the uri cache when article aliases are updated */
        $cache = & SGL_Cache::singleton();
        $cache->clean('entity_alias');
        require_once 'DB/DataObject.php';
        $dbo = DB_DataObject::factory($this->conf['table']['entity_alias']);
        $dbo->get($aliasID);
        $dbo->setFrom($target);
        return $dbo->update();
    }


    /**
     * Determines if an article alias has already been created
     *
     * @param string aliasName
     * @param int    entityId  (optional)
     */
    function isDuplicate($aliasName, $entityId = null)
    {
        //  load URI aliases

        $query = "SELECT entity_id
                  FROM {$this->conf['table']['entity_alias']}
                  WHERE entity_alias={$this->dbh->quoteSmart($aliasName)}";

        $result = $this->dbh->getAll($query,DB_FETCHMODE_ASSOC);
        if (!PEAR::isError($result) && !empty($result)) {
            foreach ($result as $item)
            {
                if ($item['entity_id'] == $entityId) {
                    return true;
                }
            }
        }
        return false;
/*
        $aArticleAliases = $this->getAllArticleAliases();
        $entityId2 = isset($aArticleAliases[$aliasName])
            ? (integer)$aArticleAliases[$aliasName]->section_id
            : false;
        if ((is_null($entityId) && is_int($entityId2)) ||
            (is_int($entityId) && is_int($entityId) && $entityId2 != $entityId)) {
            $ret = true;
        } else {
            $ret = false;
        }
        return $ret;*/
    }

    /**
     * Return an array of all assigned article aliases
     */
    function getAllEntityAliases()
    {
        $query = "SELECT entity_alias, entity_id, entity_alias_id,entity_type
                  FROM {$this->conf['table']['entity_alias']}
                  WHERE is_enabled = 1
                  ORDER BY entity_alias";
        return $this->dbh->getAssoc($query);
    }

}

class SGL_AliasStrategy
{
    // with slash
    var $url = 'n/a/';

    function SGL_AliasStrategy()
    {

    }

    function getUrl($id="")
    {
        return $this->url;
    }
}

?>