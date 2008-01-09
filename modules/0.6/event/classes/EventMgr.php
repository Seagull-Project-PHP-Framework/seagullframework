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
// | EventMgr.php                                                              |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@seaugllproject.org>                         |
// |         Marc Montanes <marc@cargol.net>                                   |
// +---------------------------------------------------------------------------+

require_once 'DB/DataObject.php';

/**
 * For managing events.
 *
 * @package seagull
 * @subpackage event
 * @version $Revision: 0.1 $
 *
 */
class EventMgr extends SGL_Manager
{
    function EventMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Events Manager';
        $this->template     = 'eventList.html';
        $this->masterLayout = 'layout-navtop-2col_localleft.css';

        $this->_aActionsMapping =  array(
            'add'       => array('add'),
            'insert'    => array('insert','redirectToDefault'),
            'edit'      => array('edit'),
            'update'    => array('update','redirectToDefault'),
            'delete'    => array('delete','redirectToDefault'),
            'list'      => array('list'),
            'view'      => array('view'),
            'search'    => array('search'),
            'searchResults' => array('searchResults'),
            'changeStatus' => array('changeStatus','redirectToView'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->masterLayout = $this->masterLayout;
        $input->template    = $this->template;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->eventId     = $req->get('frmEventId');
        $input->submitted   = $req->get('submitted');
        $input->event       = (object)$req->get('event');
        $input->event->description = $req->get('frmEventDesc', $allowTags = true);
        $input->search      = (object)$req->get('search');

        if ($input->submitted) {
            if ($input->action == 'searchResults') {
                if (!empty($input->search->query) && strlen($input->search->query) < 3) {
                    $aErrors['query'] = 'Your search term must be at least 3 characters';
                }
            } else {
                if (empty($input->event->name)) {
                    $aErrors['name'] = 'Please fill in a event name';
                }
                if (empty($input->event->description)) {
                    $aErrors['description'] = 'Please fill in a event description';
                }
                if (!isset($input->event->status)) {
                   $aErrors['status'] = 'Please select if event is hidden';
                }
                if (!strtotime($input->event->start_date)) {
                    $aErrors['state_date'] = 'Error in start date format';
                }
                if (!strtotime($input->event->end_date)) {
                    $aErrors['end_date'] = 'Error in end date format';
                }
                if (strtotime($input->event->start_date) > strtotime($input->event->end_date)) {
                    $aErrors['end_date'] = 'Error in end date. It must be later than the start date';
                }
            }
        }

        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $input->template = ($input->action == 'searchResults')
                ? 'eventSearch.html'
                : 'eventAddEdit.html';
            $this->validated = false;
        }
    }

    function display(&$output)
    {
        $output->wysiwyg = true;

        // dropdown data
        $aMyLocations = $this->getMyLocations();
        $output->aMyLocations = (count($aMyLocations))
            ? $aMyLocations
            : false;
        $output->aEventTypes = $this->getEventTypes();

        // Start calendar processing
        require_once 'Calendar/Day.php';
        $output->day = & new Calendar_Day(
            date('Y'),
            date('m'),
            date('d'));

        //  select appropriate jscalendar lang file depending on prefs defined language
        $lang = SGL::getCurrentLang();
        $jscalendarLangFile = (is_file(SGL_WEB_ROOT . '/js/jscalendar/lang/calendar-'. $lang . '.js'))
            ? 'js/jscalendar/lang/calendar-'. $lang . '.js'
            : 'js/jscalendar/lang/calendar-en.js';
        $output->addJavascriptFile(array(
            'js/jscalendar/calendar.js',
            $jscalendarLangFile,
            'js/jscalendar/calendar-setup.js'));
    }

    function _cmd_add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'eventAddEdit.html';
        $output->action   = 'insert';
        $output->pageTitle = $this->pageTitle . ' :: Add';

        $event = DB_DataObject::factory($this->conf['table']['event']);
        $event->start_date = $event->end_date = SGL_Date::getTime();
        $output->event = $event;
    }

    function _cmd_insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!SGL::objectHasState($input->event)) {
            SGL::raiseError('No data in input object', SGL_ERROR_NODATA);
            return false;
        }
        SGL_DB::setConnection();

        //  insert record
        $event = DB_DataObject::factory($this->conf['table']['event']);
        $event->setFrom($input->event);
        $event->event_id = $this->dbh->nextId('event');
        $event->created_by = $event->updated_by  = SGL_Session::getUid();
        $event->last_updated = $event->date_created = SGL_Date::getTime(true);
        $success = $event->insert();

        if ($success) {
            SGL::raiseMsg('Event saved successfully', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('There was a problem inserting the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'eventAddEdit.html';
        $output->pageTitle = $this->pageTitle . ' :: Edit';
        $output->action   = 'update';
        $event = DB_DataObject::factory($this->conf['table']['event']);
        $event->get($input->eventId);
        $output->event = $event;
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $event = DB_DataObject::factory($this->conf['table']['event']);
        $event->get($input->event->event_id);
        $event->setFrom($input->event);
        $event->location_id = isset($input->event->location_id)
            ? $input->event->location_id
            : 0;
        $event->updated_by = SGL_Session::getUid();
        $event->last_updated = SGL_Date::getTime(true);
        $success = $event->update();

        if ($success) {
            SGL::raiseMsg('event updated successfully', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('There was a problem updating the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $event = DB_DataObject::factory($this->conf['table']['event']);
        $event->get($input->eventId);
        $ok = $event->delete();

        SGL::raiseMsg('event deleted successfully', true, SGL_MESSAGE_INFO);
    }

    function _cmd_list (&$input, &$output)
    {
        $output->pageTitle = $this->pageTitle . ' :: Browse';
        $event = DB_DataObject::factory($this->conf['table']['event']);
        $event->created_by = SGL_Session::getUid();
        $result = $event->find();
        $aEvents = array();
        if ($result > 0) {
            while ($event->fetch()) {
                $event->getLinks();
                $event->attachmentCount = $this->getAttachmentCount($event->event_id);
                $oImage = $this->isEventImage($event->event_id);
                $event->isEventImg = ($oImage === false)
                    ? 'not set'
                    : $oImage->name;
                $aEvents[] = clone($event);
            }
        }
        $output->results = $aEvents;
    }

    function getAttachmentCount($eventId)
    {
        $query = "
            SELECT  COUNT(*)
            FROM    `{$this->conf['table']['event-media']}`
            WHERE   event_id = $eventId
            AND     is_event_image = 0";
        $count = $this->dbh->getOne($query);
        return $count;
    }

    function isEventImage($eventId)
    {
        $query = "
            SELECT  m.name, m.file_name, m.media_id
            FROM    `{$this->conf['table']['event-media']}` em
            JOIN    {$this->conf['table']['media']} m ON m.media_id = em.media_id
            WHERE   event_id = $eventId
            AND     is_event_image = 1";
        $oMediaRow = $this->dbh->getRow($query);
        return is_null($oMediaRow) ? false : $oMediaRow;
    }

    function getMediaIdsByEvent($eventId)
    {
        $query = "
            SELECT  media_id
            FROM    `{$this->conf['table']['event-media']}`
            WHERE   event_id = $eventId
            AND     is_event_image = 0";
        return $this->dbh->getCol($query);
    }

    function getMediaInfoByIds($aMediaIds)
    {
        $ids = implode(',', $aMediaIds);
        $query = "
            SELECT      m.media_id,
                        m.name, file_size, mime_type,
                        m.date_created, description,
                        mt.name AS file_type_name,
                        u.username AS media_added_by
            FROM        {$this->conf['table']['media']} m
            JOIN        {$this->conf['table']['file_type']} mt ON mt.file_type_id = m.file_type_id
            LEFT JOIN   {$this->conf['table']['user']} u ON u.usr_id = m.added_by
            WHERE       m.media_id IN($ids)";
        return $this->dbh->getAll($query);
    }

    function _cmd_view(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        require_once 'Calendar/Day.php';
        $output->template = 'eventView.html';
        $event = DB_DataObject::factory($this->conf['table']['event']);
        $ok = $event->get($input->eventId);
        $event->getLinks();
        $output->event = $event;

        //  get event image
        $image = $this->isEventImage($input->eventId);
        $res = ($image === false)
            ? 'not set'
            : $image->file_name;
        $output->imageName = $res;
        if ($image) {
            $output->imageId = $image->media_id;
        }

        //  get associated files
        $aIds = $this->getMediaIdsByEvent($input->eventId);
        if (count($aIds)) {
            $output->aFileData = $this->getMediaInfoByIds($aIds);
        } else {
            $output->aFileData = array();
        }

        //  get related address info
        $address = DB_DataObject::factory($this->conf['table']['address']);
        if (!empty($event->_location_id->address_id)) {
            $address->get($event->_location_id->address_id);
        } else {
            $address = false;
        }
        $output->address = $address;

        $dateArray = SGL_Date::stringToArray($event->start_date);
        $daySelect = & new Calendar_Day($dateArray['year'], $dateArray['month'], $dateArray['day']);
        $output->day = $daySelect;
    }

    function _cmd_changeStatus(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $event = DB_DataObject::factory($this->conf['table']['event']);
        $event->get($input->eventId);
        $event->status = ($event->status) ? 0 : 1;
        $success = $event->update();
        SGL::raiseMsg('event status changed successfully', false,
            SGL_MESSAGE_INFO);
    }

    function _cmd_search(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'eventSearch.html';
        $output->search->start_date = $output->search->end_date = SGL_Date::getTime();
    }

    function buildWhereConditions($aConstraints)
    {
        if (count($aConstraints)) {
            $aRet = array();
            foreach ($aConstraints as $constraint) {
                if (!empty($constraint)) {
                    $aRet[] = $constraint;
                }
            }
            if (count($aRet)) {
                $ret = 'WHERE ' . implode(' AND ', $aRet);
            } else {
                $ret = '';
            }
        } else {
            $ret = '';
        }
        return $ret;
    }

    function _cmd_searchResults(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'searchResults.html';

        if (isset($input->search->start_date_enabled) && !isset($input->search->end_date_enabled)) {
            $dateConstraint = " start_date >= '{$input->search->start_date}'";
        } elseif (!isset($input->search->start_date_enabled) && isset($input->search->end_date_enabled)) {
            $dateConstraint = " end_date <= '{$input->search->end_date}'";
        } elseif (isset($input->search->start_date_enabled) && isset($input->search->end_date_enabled)) {
            $dateConstraint = "
                                (
                                start_date >= '{$input->search->start_date}'
                                AND
                                start_date <= '{$input->search->end_date}'
                                )

                                OR

                                (
                                end_date >= '{$input->search->end_date}'
                                AND
                                end_date <= '{$input->search->start_date}'
                                )

                                OR

                                (
                                start_date <= '{$input->search->start_date}'
                                AND
                                end_date >= '{$input->search->end_date}'
                                )";
        } else {
            $dateConstraint = '';
        }

        $termConstraint = (!empty($input->search->query))
            ? " e.name LIKE '%{$input->search->query}%'"
            : '';
        $eventConstraint = (!empty($input->search->type_id))
            ? " e.type_id = {$input->search->type_id}"
            : '';

        $locationConstraint = (!empty($input->search->location_id))
            ? " e.location_id = {$input->search->location_id}"
            : '';

        $query = "
            SELECT  e.event_id, e.name, description, e.type_id, et.name AS event_type_name,
                    start_date, end_date, e.location_id, l.name AS location_name, ticket_cost,
                    status, date_created, last_updated, created_by
            FROM    {$this->conf['table']['event']} e
            JOIN    {$this->conf['table']['location']} l ON l.location_id = e.location_id
            JOIN    {$this->conf['table']['event_type']} et ON et.event_type_id = e.type_id";
        $query .= $this->buildWhereConditions(array($dateConstraint, $termConstraint,
            $eventConstraint, $locationConstraint));

        $output->results = $this->dbh->getAll($query);
    }

    function _cmd_redirectToView(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        SGL_HTTP::redirect(array(
            'action' => 'view',
            'frmEventId' => $input->eventId)
            );
    }

    function getEventTypes()
    {
        $query = "
            SELECT  event_type_id, name
            FROM    {$this->conf['table']['event_type']}";
        return $this->dbh->getAssoc($query);
    }

    function getMyLocations()
    {
        $currentUid = SGL_Session::getUid();
        $query = "
            SELECT  location_id, l.name
            FROM    {$this->conf['table']['location']} l
            WHERE   usr_id = $currentUid";
        return $this->dbh->getAssoc($query);

//            SELECT  location_id, l.name, l.email, l.telephone, l.fax,
//                    l.website, location_type.name AS location_type,
//                    a.addr_1, a.addr_2, a.addr_3, a.city, a.region, a.country, a.post_code
//            FROM    {$this->conf['table']['location']} l
//            JOIN    {$this->conf['table']['address']} a ON a.address_id = l.address_id
//            JOIN    {$this->conf['table']['location_type']} ON location_type.location_type_id = l.type_id
//            WHERE   usr_id = 1;
    }
}
?>
