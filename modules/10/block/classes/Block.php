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
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | Block.php                                                                 |
// +---------------------------------------------------------------------------+
// | Author: Gilles Laborderie <gillesl@users.sourceforge.net>                 |
// +---------------------------------------------------------------------------+
// $Id: Block.php,v 1.11 2005/05/29 00:29:08 demian Exp $

require_once 'DB/DataObject.php';

/**
 * This class extends the regular DataObjects_Block class
 * to take into block assignment
 *
 * @package block
 * @author  Gilles Laborderie <gillesl@users.sourceforge.net>
 */
class Block
{
    var $sections; // This array holds the block assignments
    var $sort_id;
    var $block;

    function Block()
    {
        $c = &SGL_Config::singleton();
        $this->conf  = $c->getAll();
        $this->dbh   = $this->_getDb();
        $this->block = DB_DataObject::factory($this->conf['table']['block']);
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
     * Loads the sections where a block should appear.
     *
     * @access public
     * @return  void
     */
    function loadSections()
    {
        $this->sections = array();

        $blockAssignment = DB_DataObject::factory($this->conf['table']['block_assignment']);
        $blockAssignment->block_id = $this->block->block_id;
        $result = $blockAssignment->find();

        if ($result > 0) {
            while ($blockAssignment->fetch()) {
                $blockAssignment->getLinks();
                if (empty($blockAssignment->section_id)) {
                    $this->sections[] = 0;
                } else {
                    $this->sections[] = $blockAssignment->section_id;
                }

            }
        }
    }

    /**
     * Loads the assigned roles.
     *
     * @access public
     * @return  void
     */
    function loadRoles()
    {
        $this->roles = array();

        $blockRoles = DB_DataObject::factory($this->conf['table']['block_role']);
        $blockRoles->block_id = $this->block->block_id;
        $result = $blockRoles->find();

        if ($result > 0) {
            while ($blockRoles->fetch()) {
                $this->roles[] = $blockRoles->role_id;
            }
        } else {
            $this->roles[] = SGL_ANY_ROLE;
        }
    }

    /**
     * Loads the blocks by position.
     *
     * @access public
     * @return  void
     */
    function loadBlocks($position)
    {
        $this->block->whereAdd("position = '".$position."'");
        $this->block->orderBy('blk_order ASC');
        $result = $this->block->find();
        $aBlocks = array();
        if ($result > 0) {
            while ($this->block->fetch()) {
                $aBlocks[$this->block->block_id] = $this->block->title;
            }
        }
        return $aBlocks;
    }

    /**
     * Copies items that are in the table definitions
     * as well as block assignments
     * from an array or object into the current object
     * will not override key values.
     *
     *
     * @param    array | object  $from
     * @param    string  $format eg. map xxxx_name to $object->name using 'xxxx_%s'
     *                               (defaults to %s - eg. name -> $object->name
     * @access   public
     * @return   true on success or array of key=>setValue error message
     */
    function setFrom(&$from, $format = '%s')
    {
        $this->block->setFrom($from, $format);

        $property = sprintf($format, 'sections');
        if (isset($from->$property)) {
            foreach ($from->{sprintf($format, 'sections')} as $key => $section) {
                if (is_object($section)) {
                    $this->sections[$key] = $section;
                } else {
                    $tmp_section = new StdClass();
                    $tmp_section->section_id = $section;
                    $this->sections[$key] = $tmp_section;
                    unset($tmp_section);
                }
            }
        }

        $property = sprintf($format, 'roles');
        if (isset($from->$property)) {
            $this->roles = $from->$property;
        }
        return true;
    }

    /**
     * Updates blocks order.
     *
     * @access  public
     * @return  void
     */
    function updateBlocksOrder($orderArray)
    {
        $pos = 1;
        foreach ($orderArray as $blockId) {
            $this->block->get($blockId);
            $this->block->blk_order = $pos;
            $success = $this->block->update();
            unset($this->block);
            $this->block = DB_DataObject::factory($this->conf['table']['block']);
            $pos++;
        }
    }

    /**
     * Get a result using key, value.
     *
     * if no value is entered, it is assumed that $key is a value
     * and get will then use the first key in _get_keys
     * to obtain the key.
     *
     * @param   string  $k column
     * @param   string  $v value
     * @access  public
     * @return  int     No. of rows
     */
    function get($k = null, $v = null)
    {
        $ret = $this->block->get($k, $v);
        if ($ret > 0) {
            $this->loadSections();
            $this->loadRoles();
        }
        return $ret;
    }

    /**
     * Insert the current objects variables into the database
     * as well as block assignments
     *
     * @access public
     * @return  mixed|false key value or false on failure
     */
    function insert()
    {
        // DataObject assumes that, if you use mysql, you are going
        // to use auto_increment which is not our case, so we have
        // to manually find the next available block id
        $this->dbh->autocommit(false);
        $block_id = $this->dbh->nextId($this->conf['table']['block']);
        $this->block->block_id = $block_id;

        // Find next available blk_order for targetted column
        $query = "
            SELECT MAX( blk_order ) FROM {$this->conf['table']['block']}
            WHERE position = '" . $this->block->position . "'";
        $next_order = (int)$this->dbh->getOne($query) + 1;
        $this->block->blk_order = $next_order;

        //  insert block record
        $this->block->insert();

        // Insert a block_assignment record for each assigned sections
        $block_assignment = DB_DataObject::factory($this->conf['table']['block_assignment']);
        $block_assignment->block_id = $block_id;
        foreach ($this->sections as $section) {
            $block_assignment->section_id = $section->section_id;
            $block_assignment->insert();
        }

        // delete 'all roles' option
        if (count($this->roles) > 1) {
            foreach ($this->roles as $key => $value) {
                if ($value == SGL_ANY_ROLE) {
                    unset($this->roles[$key]);
                }
            }
        }
        foreach ($this->roles as $role ) {

            // Insert a block_role record for each assigned roles
            $block_roles = DB_DataObject::factory($this->conf['table']['block_role']);
            $block_roles->block_id = $this->block->block_id;
            $block_roles->role_id = $role;
            $block_roles->insert();
            unset($block_roles);
        }
        $this->dbh->commit();
        $this->dbh->autocommit(true);

        return $block_id;
    }

    /**
     * Deletes items from table which match current objects variables
     * as well as block assignments
     *
     * Returns the true on success
     *
     * @param bool $useWhere (optional) If DB_DATAOBJECT_WHEREADD_ONLY is passed in then
     *             we will build the condition only using the whereAdd's.  Default is to
     *             build the condition only using the object parameters.
     *
     * @access public
     * @return bool True on success
     */
    function delete($useWhere = false)
    {

        // Delete all block assignment records for this block
        $block_assignment = DB_DataObject::factory($this->conf['table']['block_assignment']);
        $block_assignment->block_id = $this->block->block_id;
        $block_assignment->delete();

        // Delete all role assignment records for this block
        $block_role = DB_DataObject::factory($this->conf['table']['block_role']);
        $block_role->block_id = $this->block->block_id;
        $block_role->delete();

        return $this->block->delete($useWhere);
    }

    /**
     * Updates  current objects variables into the database
     * as well as block assignments
     *
     * @param object dataobject (optional) - used to only update changed items.
     * @param boolean assigments (optional) - update block assigments too.
     * @access public
     * @return  int rows affected or false on failure
     */
    function update($dataObject = false, $assigments = false)
    {
        $this->block->update($dataObject);

        if ($assigments) {

            // Delete all block assignment records for this block
            $block_assignment = DB_DataObject::factory($this->conf['table']['block_assignment']);
            $block_assignment->block_id = $this->block->block_id;
            $block_assignment->delete();
            unset($block_assignment);

            foreach ($this->sections as $section) {

                // Insert a block_assignment record for each assigned sections
                if (is_object($section)) {
                    $block_assignment = DB_DataObject::factory($this->conf['table']['block_assignment']);
                    $block_assignment->block_id = $this->block->block_id;
                    $block_assignment->section_id = $section->section_id;
                    $block_assignment->insert();
                    unset($block_assignment);
                }
            }

            // Delete all block roles records for this block
            $block_role = DB_DataObject::factory($this->conf['table']['block_role']);
            $block_role->block_id = $this->block->block_id;
            $block_role->delete();
            unset($block_role);

            // delete 'all roles' option
            if (count($this->roles) > 1) {
                foreach ($this->roles as $key => $value) {
                    if ($value == SGL_ANY_ROLE) {
                        unset($this->roles[$key]);
                    }
                }
            }
            foreach ($this->roles as $role ) {

                // Insert a block_role record for each assigned roles
                $block_roles = DB_DataObject::factory($this->conf['table']['block_role']);
                $block_roles->block_id = $this->block->block_id;
                $block_roles->role_id = $role;
                $block_roles->insert();
                unset($block_roles);
            }
        }

        return true;
    }

    /**
     * Returns an associative array from the current data
     *
     * @param   string sprintf format for array
     * @access  public
     * @return  array of key => value for row
     */
    function toArray($format = '%s')
    {
        $block_array = $this->block->toArray($format);
        $sections_array = array();
        foreach ($this->sections as $dataobject_section) {
            if (is_array($dataobject_section)) {
              array_push($sections_array, $dataobject_section->section_id);
            } else {
              array_push($sections_array, $dataobject_section);
            }
        }
        $block_array[ sprintf($format, 'sections') ] = $sections_array;
        $block_array[ sprintf($format, 'roles') ] = $this->roles;
        return $block_array;
    }

    /**
     * Sets block parameters.
     *
     * @param   string output
     * @param   string block class name
     * @param   integer block id
     * @access  public
     * @return  void
     */
    function loadBlockParams(&$output, $blockPath, $blockId = false)
    {
        $ini_file = SGL_MOD_DIR . '/' . $blockPath . '.ini';

        //  load params from db
        if ($blockId) {
            $this->block->get($blockId);
            $aSavedParams = $this->block->params ? @unserialize($this->block->params) : array();
            if (!is_array($aSavedParams)) {
                $aSavedParams = array();
            }
        } else {
            $aSavedParams = array();
        }

        //  get current params
        if (empty($output->aParams)) {
            $aCurrentParams = array();
        } else {
            $aCurrentParams = $output->aParams;
            unset($output->aParams);
        }

        //  get params from ini
        $aParams = SGL_Util::loadParams($ini_file, $aSavedParams, $aCurrentParams);

        foreach ($aParams as $key => $value) {
            $output->$key = $value;
        }
    }
}
?>