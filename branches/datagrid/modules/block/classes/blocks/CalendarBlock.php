<?php
/**
 * Calendar Block.
 * Make a selectabdar on a left block
 *
 * @package block
 * @author  Sbaturzio Cantina (sbaturzio@satellite1.191.it)
 * @version $Revision: 1.3 $
 * @since   PHP 4.1
 */
class CalendarBlock
{

    function init()
    {
        return $this->getBlockContent();
    }

    function getBlockContent()
    {
    	$theme = $_SESSION['aPrefs']['theme'];
    	$cssUrl = SGL_BASE_URL . '/js/jscal/calendar-win2k-cold-1.css';
    	$jsUrl = SGL_BASE_URL . '/js/jscal/calendar.js';
    	$langUrl = SGL_BASE_URL . '/js/jscal/lang/calendar-' . SGL::getCurrentLang() . '.js';
    	$setupUrl = SGL_BASE_URL . '/js/jscal/calendar-setup.js';
        $baseUrl = SGL_BASE_URL;

        $text = <<< CALENDAR
<link rel="stylesheet" type="text/css" media="all" href="{$cssUrl}" title="win2k-cold-1" />
<script type="text/javascript" src="{$jsUrl}"></script>
<script type="text/javascript" src="{$langUrl}"></script>
<script type="text/javascript" src="{$setupUrl}"></script>
<div id="calendar-container"></div>
<script type="text/javascript">
    function dateChanged(calendar)
    {
        if (calendar.dateClicked) {
          var y = calendar.date.getFullYear();
          var m = calendar.date.getMonth();     // integer, 0..11
          var d = calendar.date.getDate();      // integer, 1..31
          window.location =  "{$baseUrl}/index.php?year=" + y + "&month=" + m + "&day=" + d;
        }
    }
  Calendar.setup(
  {
    flat         : "calendar-container", // ID of the parent element
    flatCallback : dateChanged,          // our callback function
    weekNumbers  : false,                // don't display week numbers
    firstDay     : 1                    // monday is the first day of the week
  }
  );
</script>
CALENDAR;
        return $text;
    }
}
?>
