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
// | CategoryPerms.php                                                         |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Permissions.php,v 1.5 2005/02/03 11:29:01 demian Exp $

require_once SGL_CORE_DIR . '/Category.php';

/**
 * Basic Permission object.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.5 $
 */
class SGL_CategoryPerms extends SGL_Category
{
    var $sPerms = '';
    var $aPerms = array();
    var $catID  = 0;

    function SGL_CategoryPerms($catID = -1)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (isset($catID) && $catID >= 0) {
            $this->init($catID);
        } else {
            SGL::raiseError('No category ID passed', SGL_ERROR_INVALIDARGS);
        }
        parent::SGL_Category();
    }

    function init($catID)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->set('catID', $catID);
    }

    function update()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  only grab keys where perms = 0
        //  outputs an 1D array of GIDs where viewing is NOT permitted
        $aKeys = array_keys($this->aPerms, 0);

        //  generate perms string from array
        $this->sPerms = implode(',', $aKeys);

        //  notice null is a mysql null which is not the same as a PHP null
        $permsStr = count($aKeys) ? "perms = '$this->sPerms'" : 'perms = NULL';
        $dbh = &SGL_DB::singleton();
        $query = "
                    UPDATE  " . SGL_Config::get('table.category') . "
                    SET     $permsStr
                    WHERE   category_id = $this->catID
                ";
        $result = $dbh->query($query);

        //  recurse to update children
        if ($this->isBranch($this->catID)) {
            $childNodeArray = $this->getChildren($this->catID);
            $this->_updateChildNodes($childNodeArray);
        }
    }

    function _updateChildNodes($childNodeArray)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aKeys = array_keys($this->aPerms, 0);

        //  generate perms string from array
        $this->sPerms = implode(',', $aKeys);

        //  notice null is a mysql null which is not comparable with a PHP null
        $permsStr = count($aKeys) ? "perms = '$this->sPerms'" : 'perms = NULL';
        $dbh = & SGL_DB::singleton();
        for ($x=0; $x < count($childNodeArray); $x++) {
            $query = "
                        UPDATE  " . SGL_Config::get('table.category') . "
                        SET     $permsStr
                        WHERE   category_id = {$childNodeArray[$x]['category_id']}
                    ";
            $result = $dbh->query($query);

            //  recurse if child links detected
            if ($this->isBranch($childNodeArray[$x]['category_id'])) {
                $childNodeArrayInner = $this->getChildren($childNodeArray[$x]['category_id']);
                $this->_updateChildNodes($childNodeArrayInner);
            }
        }
    }

    function set($attributeName, $attributeValue)
    {
        $this->$attributeName = $attributeValue;
    }

    function get($attribute)
    {
        return $this->$attribute;
    }
}
?>