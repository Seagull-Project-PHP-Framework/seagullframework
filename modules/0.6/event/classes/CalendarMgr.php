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
// | CalendarMgr.php                                                           |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@seaugllproject.org>                         |
// |         Marc Montanes <marc@cargol.net>                                   |
// +---------------------------------------------------------------------------+

require_once 'DB/DataObject.php';
define('SGL_SECONDS_PER_DAY', 86400);

/**
 * To allow users to contact site admins.
 *
 * @package seagull
 * @subpackage event
 * @version $Revision: 0.1 $
 *
 */
class CalendarMgr extends SGL_Manager
{
    function CalendarMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Calendar';
        $this->template     = 'monthView.html';
        $this->masterLayout = 'layout-navtop-2col_localleft.css';

        $this->_aActionsMapping =  array(
            'viewYear'    => array('viewYear'),
            'viewDay'      => array('viewDay'),
            'viewMonth' => array('viewMonth')
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $input->template    = $this->template;
        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->masterLayout   = $this->masterLayout;
        $input->template    = $this->template;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'viewMonth';
        $input->event_id    = $req->get('frmEventId');
        $input->day         = $req->get('d');
        $input->month       = $req->get('m');
        $input->year        = $req->get('y');

        // Load date
        if (isset ($input->day) && isset ($input->month) && isset ($input->year)) {
            $input->time = mktime(0, 0, 0, $input->month, $input->day, $input->year);

            if (!$input->time) {
                $aErrors['error'] = 'Date error';
            }
        } else {
            $input->time = time();
        }

        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please try again');
            $input->error = $aErrors;
            $input->template = 'viewMonth.html';
            $this->validated = false;
        }
    }

    function _cmd_viewYear(&$input, &$output)
    {
        // Not implemented yet
    }

    function _cmd_viewDay(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'viewDay.html';
        $output->pageTitle = $this->pageTitle . ' :: View month';

        // Start calendar processing
        require_once 'Calendar/Calendar.php';
        require_once 'Calendar/Day.php';

        // Load input date
        $yearSelected = date('Y', $input->time);
        $monthSelected = date('m', $input->time);
        $daySelected = date('d', $input->time);

        // Construct pear calendar objects
        $daySelect = & new Calendar_Day($yearSelected, $monthSelected, $daySelected);

        // Load events for the day
        $events = DB_DataObject::factory($this->conf['table']['event']);
        $events->whereAdd("'$yearSelected-$monthSelected-$daySelected)' BETWEEN start_date AND end_date");

        $result = $events->find();
        $aEvents = array();
        if ($result > 0) {
            while ($events->fetch()) {
                $events->getLinks();
                $aEvents[] = clone($events);
            }
        }
        // Calculate prev/next day
        $output->prevDay = new Calendar_Day(
            date('Y',$daySelect->prevDay('timestamp')),
            date('m',$daySelect->prevDay('timestamp')),
            date('d',$daySelect->prevDay('timestamp')));

        $output->nextDay = new Calendar_Day(
            date('Y',$daySelect->nextDay('timestamp')),
            date('m',$daySelect->nextDay('timestamp')),
            date('d',$daySelect->nextDay('timestamp')));

        $output->dayName = strftime('%A %d %B %Y', $daySelect->getTimeStamp());
        $output->day = $daySelect;
        $output->events = isset($aEvents) ? $aEvents : null;

    }

    function _cmd_viewMonth(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'viewMonth.html';
        $output->pageTitle = $this->pageTitle . ' :: View month';

        // Start calendar processing
        require_once 'Calendar/Calendar.php';
        require_once 'Calendar/Month/Weekdays.php';
        require_once 'Calendar/Day.php';

        // Load input date
        $yearSelected = date('Y', $input->time);
        $monthSelected = date('m', $input->time);
        $daySelected = date('d', $input->time);

        // Construct pear calendar objects
        $month = & new Calendar_Month_Weekdays($yearSelected, $monthSelected);
        $daySelect = & new Calendar_Day($yearSelected, $monthSelected, $daySelected);
        $daySelect->daySelected = true;
        $selection[] = $daySelect;

        // Load month events
        $events = DB_DataObject::factory($this->conf['table']['event']);
        $events->whereAdd("start_date  LIKE '$yearSelected-$monthSelected-%'");
        $events->whereAdd("end_date LIKE '$yearSelected-$monthSelected-%'",'OR');
        $result = $events->find();
        $aEvents = array();
        if ($result > 0) {
            while ($events->fetch()) {
                $events->getLinks();
                $aEvents[] = clone($events);
            }
        }
        require_once 'Date.php';
        foreach ($aEvents as $event) {
            $endDate = new Date($event->end_date);
            for ($currentDate = new Date($event->start_date);
                 $currentDate->before($endDate);
                 $currentDate->addSeconds(SGL_SECONDS_PER_DAY)) {
                if (isset($selection[$currentDate->getDay()])) {
                    $selection[$currentDate->getDay()]->aEvents[] = $event;
                } else {
                    $day = & new Calendar_Day(
                        $currentDate->getYear(),
                        $currentDate->getMonth(),
                        $currentDate->getDay());
                    $day->aEvents[] = $event;
                    $selection[$day->day] = $day;
                }
            }
            unset($endDate, $currentDate);
        }

        // Build month with selected dates
        $month->build($selection);

        while ($day = $month->fetch()) {
            $monthArray[] = $day;
        }

        // Calculate prev/next month day
        $prevMonthDay = new Calendar_Day(
            date('Y',$month->prevMonth('timestamp')),
            date('m',$month->prevMonth('timestamp')),
            date('d',$month->prevMonth('timestamp')));
        $nextMonthDay = new Calendar_Day(
            date('Y',$month->nextMonth('timestamp')),
            date('m',$month->nextMonth('timestamp')),
            date('d',$month->nextMonth('timestamp')));

        // Set output data
        $output->month = $monthArray;
        $output->monthName = strftime('%B %Y',$month->getTimeStamp());
        $output->day = $daySelect;
        $output->prevMonthDay = $prevMonthDay;
        $output->nextMonthDay = $nextMonthDay;
        $output->today_date = date("dS F Y", time());
    }
}
?>