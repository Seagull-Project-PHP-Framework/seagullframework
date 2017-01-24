<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Seagull Systems                                       |
// | All rights reserved.                                                      |
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software               |
// | Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301,|
// | USA                                                                       |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | LocationMgr.php                                                           |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@seaugllproject.org>                         |
// +---------------------------------------------------------------------------+


require_once 'DB/DataObject.php';
/**
 * Manage event locations.
 *
 * @package seagull
 * @subpackage event
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class LocationMgr extends SGL_Manager
{
    function LocationMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Location Manager';
        $this->template     = 'locationMgrList.html';
        $this->masterLayout = 'layout-navtop-2col_localleft.css';

        $this->_aActionsMapping =  array(
            'add'       => array('add'),
            'insert'    => array('insert', 'redirectToDefault'),
            'edit'      => array('edit'),
            'update'    => array('update', 'redirectToDefault'),
            'list'      => array('list'),
            'delete'    => array('delete', 'redirectToDefault'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->masterLayout   = $this->masterLayout;
        $input->template    = $this->template;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->aDelete     = $req->get('frmDelete');
        $input->submitted   = $req->get('submitted');
        $input->location = (object)$req->get('location');
        $input->locationId  = $req->get('frmLocationID');
        $input->address = (object)$req->get('address');

        if ($input->submitted) {
            if (empty($input->location->name)) {
                $aErrors['name'] = 'You must provide a name for your location';
            }
            if (empty($input->address->addr_1)) {
                $aErrors['addr_1'] = 'You must provide at least the first line of your address';
            }
            if (empty($input->address->region)) {
                $aErrors['region'] = 'You must provide your county';
            }
            if (empty($input->address->post_code)) {
                $aErrors['post_code'] = 'You must provide your post code';
            }
        }

        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->template  = 'locationEdit.html';
            $_address_id = $input->address;
            $input->location->_address_id = $_address_id;
            $input->error = $aErrors;
            $this->validated = false;
        }
    }

    function display(&$output)
    {
        $output->boolChoice = array("No", "Yes");
        $output->counties = SGL::loadRegionList('counties');
        $output->locations = $this->getLocationTypes();
    }

    function _cmd_add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template  = 'locationEdit.html';
        $output->pageTitle = 'LocationMgr :: Add';
        $output->action    = 'insert';
        $output->wysiwyg   = true;
    }

    function _cmd_insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  insert address first to get foreign key
        $address = DB_DataObject::factory($this->conf['table']['address']);
        $address->setFrom($input->address);
        $address->address_id = $this->dbh->nextId('address');
        $success1 = $address->insert();

        $location = DB_DataObject::factory($this->conf['table']['location']);
        $location->setFrom($input->location);
        $location->location_id = $this->dbh->nextId('location');
        $location->address_id = $address->address_id;
        $location->usr_id = SGL_Session::getUid();
        $success2 = $location->insert();

        if ($success1 !== false && $success2 !== false) {
            SGL::raiseMsg('location insert successfull', false, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('location insert NOT successfull',
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template  = 'locationEdit.html';
        $output->pageTitle = 'LocationMgr :: Edit';
        $output->action    = 'update';
        $output->wysiwyg   = true;

        $location = DB_DataObject::factory($this->conf['table']['location']);
        $location->location_id = $input->locationId;
        $location->find(true);
        $location->getLinks();
        $output->location = $location;
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $location = DB_DataObject::factory($this->conf['table']['location']);
        $location->location_id = $input->locationId;
        $location->find(true);
        $location->setFrom($input->location);
        $success = $location->update();

        $address = DB_DataObject::factory($this->conf['table']['address']);
        $address->get($location->address_id);
        $address->setFrom($input->address);
        $success = $address->update();

        if ($success !== false) {
            SGL::raiseMsg('location update successfull', false, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('location update NOT successfull',
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template  = 'locationList.html';
        $output->pageTitle = 'LocationMgr :: List';

        $locationList= DB_DataObject::factory($this->conf['table']['location']);
        $locationList->usr_id = SGL_Session::getUid();
        $result = $locationList->find();

        $aLocations  = array();
        if ($result > 0) {
            while ($locationList->fetch()) {
                $aLocations[] = clone($locationList);
            }
        }
        $output->results = $aLocations;
    }

    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $location = DB_DataObject::factory($this->conf['table']['location']);
        $location->get($input->locationId);
        $ok = $location->delete();

        SGL::raiseMsg('location deleted successfully', false, SGL_MESSAGE_INFO);
    }

    function getLocationTypes()
    {
        $query = "
            SELECT  location_type_id, name
            FROM    {$this->conf['table']['location_type']}";
        return $this->dbh->getAssoc($query);
    }
}
?>