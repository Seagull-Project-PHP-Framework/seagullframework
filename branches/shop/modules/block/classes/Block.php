<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2004, Demian Turner                                         |
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
// | Block.php                                                                 |
// +---------------------------------------------------------------------------+
// | Author: Gilles Laborderie <gillesl@users.sourceforge.net>                 |
// +---------------------------------------------------------------------------+
// $Id: Block.php,v 1.11 2005/05/29 00:29:08 demian Exp $

require_once SGL_ENT_DIR . '/Block.php';
require_once SGL_ENT_DIR . '/Block_assignment.php';

/**
 * This class extends the regular DataObjects_Block class
 * to take into block assignment
 *
 * @package block
 * @author  Gilles Laborderie <gillesl@users.sourceforge.net>
 * @version $Revision: 1.11 $
 * @since   PHP 4.1
 */
class Block extends DataObjects_Block
{
    var $sections; // This array holds the block assignments
    var $sort_id;

    /**
     * Loads the sections where a block should appear
     *
     * @access public
     * @return  void
     */
    function loadSections() 
    {
        $this->sections = array();

        $blockAssignment = & new DataObjects_Block_assignment();
        $blockAssignment->block_id = $this->block_id;
        $result = $blockAssignment->find();

        if ($result > 0) {
            while ($blockAssignment->fetch()) {
                $blockAssignment->getLinks();
                if (empty($blockAssignment->section_id)) {
                    $section = & new StdClass();
                    $section->section_id = 0;
                    $section->title = SGL_String::translate('All sections');
                    $this->sections[] = $section;
                } else {
                    $this->sections[] = $blockAssignment->section_id;                  
                }
                
            }
        }
    }

    /**
     * Copies items that are in the table definitions
     * as well as block assignments
     * from an array or object into the current object
     * will not override key values.
     *
     *
     * @param    array | object  $from
     * @param    string  $format eg. map xxxx_name to $object->name using 'xxxx_%s' (defaults to %s - eg. name -> $object->name
     * @access   public
     * @return   true on success or array of key=>setValue error message
     */
    function setFrom(&$from, $format = '%s') 
    {
        parent::setFrom($from, $format);

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
        return true;
    }

    /**
     * fetches next row into this object's vars.
     *
     * returns 1 on success 0 on failure
     *
     * @access  public
     * @return  boolean on success
     */
    function fetch() 
    {
        $ret = parent::fetch();
        if ($ret) {
            $this->loadSections();
        }
        return $ret;
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
        $ret = parent::get($k, $v);
        if ($ret > 0) {
            $this->loadSections();
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
        $dbh = & SGL_DB::singleton();
        $block_id = $dbh->nextId('block');
        $this->block_id = $block_id;
        parent::insert();

        // parent::insert resets Data_Object primary key
        // using 'mysql_insert_id' which is zero in our case
        // since we do not use the auto_increment feature of MySQL
        // so we have to manually set it back to the correct value
        $this->block_id = $block_id; 

        // Insert a block_assignment record for each assigned sections
        $block_assignment = & new DataObjects_Block_Assignment();
        $block_assignment->block_id = $this->block_id;
        foreach ($this->sections as $section) {
            $block_assignment->section_id = $section->section_id;
            $block_assignment->insert();
        }
        return $this->block_id;
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
        if (parent::delete($useWhere)) {

            // Delete all block assignment records for this block
            $block_assignment = & new DataObjects_Block_Assignment();
            $block_assignment->block_id = $this->block_id;
            $block_assignment->delete();
            return true;
        } else {
            return false;
        }
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
        parent::update($dataObject);

        if ($assigments) {
            // Delete all block assignment records for this block
            $block_assignment = & new DataObjects_Block_Assignment();
            $block_assignment->block_id = $this->block_id;
            $block_assignment->delete();
            unset($block_assignment);
            foreach ($this->sections as $section) {
                // Insert a block_assignment record for each assigned sections
                $block_assignment = & new DataObjects_Block_Assignment();
                $block_assignment->block_id = $this->block_id;                                    
                $block_assignment->section_id = $section->section_id;
                $block_assignment->insert();
                unset($block_assignment);                    
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
        $block_array = parent::toArray($format);
        $sections_array = array();
        foreach ($this->sections as $dataobject_section) {
            if (is_array($dataobject_section)) {
              array_push($sections_array, $dataobject_section->section_id);
            } else {
              array_push($sections_array, $dataobject_section);
            }
        }

        $block_array[ sprintf($format, 'sections') ] = $sections_array;
        return $block_array;
    }
}
?>