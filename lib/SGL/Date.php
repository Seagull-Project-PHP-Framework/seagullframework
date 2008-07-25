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
// | Date.php                                                                  |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+

/**
 * Provides various date formatting methods.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.14 $
 */
class SGL_Date
{
    /**
     * Returns current time in YYYY-MM-DD HH:MM:SS format.
     *
     * GMT format is best for logging system events, otherwise locale offset
     * will be most helpful to users.
     *
     * @access public
     * @static
     * @param boolean $gmt       is time GMT or locale offset
     * @return string $time formatted current time
     */
    function getTime($gmt = false)
    {
        //  no logMessage allowed here
        $time = ($gmt)  ? gmstrftime("%Y-%m-%d %H:%M:%S", time())
                        : strftime("%Y-%m-%d %H:%M:%S", time());
        return $time;
    }

    /**
     * Converts date array into MySQL datetime format.
     *
     * @access  public
     * @param   array   $aDate
     * @return  string  MySQL datetime format
     * @see     publisher::ArticleMgr::process/edit
     */
    function arrayToString($aDate)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (is_array($aDate)) {
            $month  = $aDate['month'];
            $day    = $aDate['day'];
            $year   = $aDate['year'];
            $hour   = (array_key_exists('hour',$aDate))? $aDate['hour'] : '00';
            $minute = (array_key_exists('minute',$aDate))? $aDate['minute'] : '00';
            $second = (array_key_exists('second',$aDate))? $aDate['second'] : '00';

            if (empty($month) && empty($year) && empty($day)) {
                return null;
            } else {
                return $year . '-' . $month . '-' . $day .' ' . $hour . ':' . $minute . ':' . $second;
            }
        }
    }

    /**
     * Converts date into date array.
     *
     * @access  public
     * @param   string  $sDate date (may be in the ISO, TIMESTAMP or UNIXTIME format) format
     * @return  array   $aDate
     * @see     publisher::ArticleMgr::process/edit
     */
    function stringToArray($sDate)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (is_scalar($sDate)) {
            require_once 'Date.php';
            $date = & new Date($sDate);
            $aDate =      array('day'    => $date->getDay(),
                                'month'  => $date->getMonth(),
                                'year'   => $date->getYear(),
                                'hour'   => $date->getHour(),
                                'minute' => $date->getMinute(),
                                'second' => $date->getSecond());
            return $aDate;
        }
    }

    /**
     * Converts date (may be in the ISO, TIMESTAMP or UNIXTIME format) into "Mar 31, 2003 18:29".
     *
     * @access  public
     * @param   string  $date  Date (may be in the ISO, TIMESTAMP or UNIXTIME format) value
     * @return  string  $formatted  user-friendly format (european)
     */
    function formatPretty($date)
    {
        if (is_string($date)) {
            require_once 'Date.php';
            $date = & new Date($date);
            if ($_SESSION['aPrefs']['dateFormat'] == 'FR') {
                $output = $date->format('%d %B %Y, %H:%M');

            } elseif ($_SESSION['aPrefs']['dateFormat'] == 'BR') {
                // Brazilian date format
                $output = $date->format('%d de %B de %Y %H:%M');

            } elseif ($_SESSION['aPrefs']['dateFormat'] == 'DE') {
                // German date format
                $output = $date->format('%d. %B %Y %H:%M');

            } else {
                //  else UK and US
                $output = $date->format('%B %d, %Y %H:%M');
            }
            return $output;
        } else {
            SGL::raiseError('no input date passed to SGL_Date::formatPretty incorrect type',
                SGL_ERROR_INVALIDARGS);
        }
    }

    /**
     * Converts date (may be in the ISO, TIMESTAMP or UNIXTIME format) into locale dependent form.
     *
     * @access  public
     * @param   string  $input  date (may be in the ISO, TIMESTAMP or UNIXTIME format) value
     * @return  string  $output user-friendly format (locale dependent)
     */
    function format($date)
    {
        if (is_string($date)) {
            include_once 'Date.php';
            $date = & new Date($date);
            // Neither elegant nor efficient way of doing that
            // (what if we have 30 formats/locales?).
            // We should move that to a language/locale dependent file.
            if ($_SESSION['aPrefs']['dateFormat'] == 'UK') {
                $output = $date->format('%d.%m.%Y');
            } elseif ($_SESSION['aPrefs']['dateFormat'] == 'BR'
                     || $_SESSION['aPrefs']['dateFormat'] == 'FR') {
                // Brazilian/French date format
                $output = $date->format('%d/%m/%Y');
            } elseif ($_SESSION['aPrefs']['dateFormat'] == 'US') {
                $output = $date->format('%m.%d.%Y');
            } elseif ($_SESSION['aPrefs']['dateFormat'] == 'DE') {
                $output = $date->format('%d.%m.%Y');
            } else {
                //  else display ISO (international, unambiguous) format, YYYY-MM-DD
                $output = $date->format('%Y-%m-%d');
            }
            return $output;
        } else {
            SGL::raiseError('no input date passed to SGL_Date::format incorrect type',
                SGL_ERROR_INVALIDARGS);
        }
    }

    /**
     * Gets appropriate date format
     *
     * @access  public
     * @return  string  $date template (e.g. "%d %B %Y, %H:%M" for FR date format)
     */
    function getDateFormat()
    {
        if ($_SESSION['aPrefs']['dateFormat'] == 'UK') {
            $dateFormat = '%d %B %Y, %H:%M';
        } elseif ($_SESSION['aPrefs']['dateFormat'] == 'BR'
                 || $_SESSION['aPrefs']['dateFormat'] == 'FR') {
            // Brazilian/French date format
            $dateFormat = '%d %B %Y, %H:%M';
        } elseif ($_SESSION['aPrefs']['dateFormat'] == 'US') {
            $dateFormat = '%B %d, %Y %H:%M';
        } elseif ($_SESSION['aPrefs']['dateFormat'] == 'DE') {
            $dateFormat = '%d.%B.%Y %H:%M';
        } else {
            //  else display ISO (international, unambiguous) format, YYYY-MM-DD
            $dateFormat = '%Y-%B-%d';
        }
        return $dateFormat;
    }

    /**
     * Generates a select of day values.
     *
     * @access  public
     * @param   string  $selected
     * @return  string  $day_options    select day options
     * @see     showDateSelector()
     */
    function getDayFormOptions($selected = '')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $day_options = '';
        for ($i = 1; $i <= 31; $i++) {
            $day_options .= "\n<option value=\"" . sprintf('%02d', $i) . '" ';
            if ($i == $selected) {
                $day_options .= 'selected="selected"';
            }
            $day_options .= '>' . sprintf('%02d', $i) . '</option>';
        }
        return $day_options;
    }

    /**
     * Generates a select of month values.
     *
     * @access  public
     * @param   string  $selected
     * @return  string  $monthOptions  select month options
     * @see     showDateSelector()
     */
    function getMonthFormOptions($selected = '')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aMonths = SGL_String::translate('aMonths');
        $monthOptions = '';
        if (empty($selected) && $selected != null) {
            $selected = date('m', time());
        }
        if (is_array($aMonths) && count($aMonths)) {
            foreach ($aMonths as $k => $v) {
                //$monthOptions .= "\n<option value=\"" . sprintf('%02d', $k) . '" ';
                $monthOptions .= "\n<option value=\"$k\" ";
                if ($k == $selected) {
                    $monthOptions .= 'selected="selected"';
                }
                $monthOptions .= '>' . $v . '</option>';
            }
        }
        return $monthOptions;
    }

    /**
     * Generates a select of year values.
     *
     * @access  public
     * @param   string  $selected
     * @param   boolean $asc
     * @param   int     $number         number of years to show
     * @return  string  $year_options   select year options
     * @see     showDateSelector()
     */
    function getYearFormOptions($selected = '', $asc = true, $totalYears = 5)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $yearOptions = '';
        $curYear = date('Y', time());
        $startYear = $curYear;
        if ($asc) {
             for ($i = $startYear; $i <= $startYear + $totalYears; $i++) {
                 $yearOptions .= "\n<option value=\"" . $i . '" ';
                 if ($i == $selected) {
                     $yearOptions .= 'selected="selected"';
                 }
                 $yearOptions .= '>' . $i . '</option>';
             }
        } else {
             for ($i = $startYear; $i >= $startYear - ($totalYears - 1); $i--) {
                 $yearOptions .= "\n<option value=\"" . $i . '" ';
                 if ($i == $selected) {
                     $yearOptions .= 'selected="selected"';
                 }
                 $yearOptions .= '>' . $i . '</option>';
             }
        }
        return $yearOptions;
    }

    /**
     * Generates a select of hour values.
     *
     * @access  public
     * @param   string  $selected
     * @return  string  $hour_options   select hour options
     * @see     showDateSelector()
     */
    function getHourFormOptions($selected = '')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $hour_options = '';

        for ($i = 0; $i <= 23; $i++) {
            $hval = sprintf("%02d",  $i);
            $hour_options .= "\n<option value=\"" . $hval . '" ';
            if ($selected == $i && $selected !='' ) {
                $hour_options .= 'selected="selected"';
            }
            $hour_options .= '>' . $hval . '</option>';
        }
        return $hour_options;
    }

    /**
     * Generates a select of minute/second values.
     *
     * @access  public
     * @param   string  $selected
     * @return  string  $minute_options select minute/second options
     * @see     showDateSelector()
     */
    function getMinSecOptions($selected = '')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $minute_options = '';

        for ($i = 0; $i <= 59; $i++) {
            $minute_options .= "\n<option value=\"" . sprintf("%02d",  $i) . '" ';
            if ($selected == $i && $selected !="" ) {
                $minute_options .= 'selected="selected"';
            }
            $minute_options .= '>' . sprintf("%02d",  $i) . '</option>';
        }
        return $minute_options;
    }

    /**
     * Generates date/time selector widget.
     *
     * usage:
     * $timestamp=mktime();
     * $day = date('d', $timestamp);
     * $month = date('m', $timestamp);
     * $year = date('Y', $timestamp);
     * $hour = date('H', $timestamp);
     * $minute = date('i', $timestamp);
     * $second = date('s', $timestamp);
     *
     * $aDate = array(  'day' => $day,
     *                  'month' => $month,
     *                  'year' => $year,
     *                  'hour' => $hour,
     *                  'minute' => $minute,
     *                  'second' => $second);
     * print showDateSelector($aDate, 'myForm', false);
     *
     * @access  public
     * @param   array   $aDate
     * @param   string  $elementName name of the html element
     * @param   boolean $bShowTime  toggle to display HH:MM:SS
     * @param   bool    $asc
     * @param   int     $years      number of years to show
     * @return  string  $html       html for widget
*/
    function showDateSelector($aDate, $elementName, $bShowTime = true, $asc = true, $years = 5)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $html = '';
        $month_html = "\n<select name='" . $elementName . "[month]' id='".$elementName."_month' >" .
            SGL_Date::getMonthFormOptions($aDate['month']) . '</select> / ';
        $day_html = "\n<select name='" . $elementName . "[day]' id='".$elementName."_day'>" .
            SGL_Date::getDayFormOptions($aDate['day']) . '</select> / ';
        if ($_SESSION['aPrefs']['dateFormat'] == 'US') {
            $html .= $month_html . $day_html;
        } else {
            $html .= $day_html . $month_html;
        }
        $html .= "\n<select name='" . $elementName . "[year]' id='".$elementName."_year'>" .
            SGL_Date::getYearFormOptions($aDate['year'], $asc, $years) . '</select>';
        if ($bShowTime) {
            $html .= '&nbsp;&nbsp; ';
            $html .= SGL_String::translate('at time');
            $html .= ' &nbsp;&nbsp;';
            $html .= "\n<select name='" . $elementName . "[hour]'  id='".$elementName."_hour'>" .
                SGL_Date::getHourFormOptions($aDate['hour']) . '</select> : ';
            $html .= "\n<select name='" . $elementName . "[minute]' id='".$elementName."_minute'>" .
                SGL_Date::getMinSecOptions($aDate['minute']) . '</select> : ';
            $html .= "\n<select name='" . $elementName . "[second]' id='".$elementName."_second'>" .
                SGL_Date::getMinSecOptions($aDate['second']) . '</select>';
        }
        return $html;
    }
}
?>
