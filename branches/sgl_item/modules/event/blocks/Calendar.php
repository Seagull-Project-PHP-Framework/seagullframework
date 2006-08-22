<?php
/**
 * Calendar Block.
 * Make a selectabdar on a left block
 *
 * @package block
 * @author  Marc - Snowcrash <marc@cargol.net>
 */

require_once 'DB/DataObject.php';

class Calendar_Block_Calendar
{
    var $template     = 'blockViewMonth.html';
    var $templatePath = 'calendar';

    function init(&$output, $block_id, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        return $this->getBlockContent($output, $aParams);
    }

    function getBlockContent(&$output, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        $blockOutput          = new SGL_Output();

        // Start calendar processing
        require_once 'Calendar/Calendar.php';
        require_once 'Calendar/Month/Weekdays.php';
        require_once 'Calendar/Day.php';

        // Load input date
        $yearSelected = date ('Y', time());
        $monthSelected = date ('m', time());
        $daySelected = date ('d', time());

        // Construct pear calendar objects
        $month = & new Calendar_Month_Weekdays($yearSelected,$monthSelected);
        $daySelect = & new Calendar_Day($yearSelected,$monthSelected,$daySelected);
        $daySelect->daySelected = true;
        $selection[] = $daySelect;

        // Load month events
        $events = DB_DataObject::factory($conf['table']['event']);
        $events->whereAdd ("start_date  LIKE '$yearSelected-$monthSelected-%'");
        $events->whereAdd ("end_date  LIKE '$yearSelected-$monthSelected-%'",'OR');
        $events->find();

        while ($events->fetch ()) {
            $events->getLinks();
            $day = & new Calendar_Day(date('Y',strtotime($events->start_date)),
                date('m',strtotime($events->start_date)),
                date('d',strtotime($events->start_date)));
            $day->event = $events;
            $selection[] = $day;
        }

        // Build month with selected dates
        $month->build($selection);

        while ( $day = $month->fetch() ) {
          $monthArray[] = $day;
        }

        // Calculate prev/next month day
        $prevMonthDay = new Calendar_Day(date('Y',$month->prevMonth('timestamp')),date('m',$month->prevMonth('timestamp')),date('d',$month->prevMonth('timestamp')));
        $nextMonthDay = new Calendar_Day(date('Y',$month->nextMonth('timestamp')),date('m',$month->nextMonth('timestamp')),date('d',$month->nextMonth('timestamp')));

        // Set output data
        $blockOutput->month = $monthArray;
        $blockOutput->monthName = strftime('%B %Y',$month->getTimeStamp());
        $blockOutput->today = $today;
        $blockOutput->prevMonthDay = $prevMonthDay;
        $blockOutput->nextMonthDay = $nextMonthDay;

        return $this->process($blockOutput);
    }

    function process(&$output)
    {
        // use moduleName for template path setting
        $output->webRoot = SGL_BASE_URL;
        $output->moduleName     = $this->templatePath;
        $output->masterTemplate = $this->template;

        $view = new SGL_HtmlSimpleView($output,'smarty');
        return $view->render();
    }
}