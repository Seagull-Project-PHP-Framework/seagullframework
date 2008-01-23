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
// | PublisherDAO.php                                                            |
// +---------------------------------------------------------------------------+
// | Authors:   Demian Turner <demian@phpkitchen.com>                          |
// +---------------------------------------------------------------------------+
// $Id: PublisherDAO.php,v 1.14 2005/06/21 23:26:24 demian Exp $

/**
 * Data access methods for the publisher module.
 *
 * @package Default
 * @author  Demian Turner <demian@phpkitchen.com>
 * @copyright Demian Turner 2005
 * @version $Revision: 1.14 $
 */
class PublisherDAO
{
    /**
     * Constructor - set default resources.
     *
     * @return PublisherDAO
     */
    function PublisherDAO()
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
     * Returns a singleton PublisherDAO instance.
     *
     * example usage:
     * $da = & PublisherDAO::singleton();
     * warning: in order to work correctly, the DA
     * singleton must be instantiated statically and
     * by reference
     *
     * @access  public
     * @static
     * @return  PublisherDAO reference to PublisherDAO object
     */
    function &singleton()
    {
        static $instance;

        // If the instance is not there, create one
        if (!isset($instance)) {
            $class = __CLASS__;
            $instance = new $class();
        }
        return $instance;
    }

    function retrievePaginatedItemType($options)
    {
        $query = "
            SELECT      item_type_id, item_type_name
            FROM        {$this->conf['table']['item_type']}
            WHERE       item_type_id != 1";

        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $options);

        return $aPagedData;
    }

    function getItemAttributesByItemId($id = null, $paginated = false)
    {
        $constraint = (is_null($id))
            ? 'WHERE itm.item_type_id = it.item_type_id'
            : "WHERE     itm.item_type_id = $id
               AND       it.item_type_id = $id";
        $query = "SELECT
                    it.item_type_id,
                    it.item_type_name,
                    itm.item_type_mapping_id,
                    itm.field_name,
                    itm.field_type
                  FROM
                    {$this->conf['table']['item_type']} it,
                    {$this->conf['table']['item_type_mapping']} itm
                  $constraint";

        if ($paginated) {
            $limit = $_SESSION['aPrefs']['resPerPage'];
            $pagerOptions = array(
                'mode'     => 'Sliding',
                'delta'    => 3,
                'perPage'  => $limit,
            );
            $ret = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);

        } else {
            $ret = $this->dbh->getAll($query, DB_FETCHMODE_ASSOC);
        }
        return $ret;
    }

    function addItemType($name)
    {
        $id = $this->dbh->nextId($this->conf['table']['item_type']);

        $query = "
            INSERT INTO {$this->conf['table']['item_type']}
                (item_type_id, item_type_name)
            VALUES ($id, '". $name . "')";

        $ok = $this->dbh->query($query);
        return (PEAR::isError($ok)) ? $ok : $id;
    }

    function addItemAttributes($itemTypeId, $name, $type)
    {
        $id = $this->dbh->nextId($this->conf['table']['item_type_mapping']);
        $query = "INSERT INTO {$this->conf['table']['item_type_mapping']}
                    (item_type_mapping_id, item_type_id, field_name, field_type)
                  VALUES ($id, $itemTypeId, '" . $name . "', $type)";
        return $this->dbh->query($query);
    }

    function updateItemTypeName($itemTypeId, $newName)
    {
        $query = "
            UPDATE {$this->conf['table']['item_type']}
            SET item_type_name='" . $newName . "'
            WHERE item_type_id=" . $itemTypeId;
        return $this->dbh->query($query);
    }

    function updateItemAttributes($attributeId, $aItemAttributes)
    {
        //  field name clause
        if ($aItemAttributes['field_name'] != $aItemAttributes['field_name_original']) {
            $fieldNameClause = "field_name='" . $aItemAttributes['field_name'] . "'";
        }
        //  field type clause
        if ($aItemAttributes['field_type'] != $aItemAttributes['field_type_original']) {
            $fieldTypeClause = "field_type=". $aItemAttributes['field_type'];
        }

        //  update field_name & field_type
        if (isset($fieldNameClause) && isset($fieldTypeClause)) {
            $setClause = "SET $fieldNameClause, $fieldTypeClause";

        //  update only field_name
        } elseif (isset($fieldNameClause)) {
            $setClause = "SET $fieldNameClause";

        //  update only field_type
        } elseif (isset($fieldTypeClause)) {
            $setClause = "SET $fieldTypeClause";
        }
        if (isset($setClause)) {
            $query = "
                UPDATE {$this->conf['table']['item_type_mapping']}
                $setClause
                WHERE item_type_mapping_id=" . $attributeId;
            $ret = $this->dbh->query($query);
        } else {
            $ret = false;
        }
        return $ret;
    }

    function deleteItemTypeById($id)
    {
        $query = "
            DELETE FROM {$this->conf['table']['item_type']}
            WHERE item_type_id=$id";
        $this->dbh->query($query);
    }

    function deleteItemAttributesByItemTypeId($id)
    {
        $query = "
            DELETE FROM {$this->conf['table']['item_type_mapping']}
            WHERE item_type_id=$id";
        $this->dbh->query($query);
    }
}