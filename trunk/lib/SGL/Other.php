<?php

/**
 * Provides array manipulation methods.
 *
 */
class SGL_Array
{
    /**
     * Strips 'empty' elements from supplied array.
     *
     * 'Empty' can be a null, empty string, false or empty array.
     *
     * @param array $elem
     * @return array
     */
    function removeBlanks($elem)
    {
        if (is_array($elem)) {
            $clean = array_filter($elem);
        }
        return $clean;
    }
}

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
     * @return string $instance  formatted current time
     * @todo factor out Cache and Lang methods into their own objects
     */
    function getTime($gmt = false)
    {
        //  no logMessage allowed here
        static $instance;
        if (!isset($instance)) {
            $instance = ($gmt)  ? gmstrftime("%Y-%m-%d %H:%M:%S", time())
                                : strftime("%Y-%m-%d %H:%M:%S", time());
        }
        return $instance;
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
            include_once 'Date.php';
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
     * Generates a select of month values.
     *
     * @access  public
     * @param   string  $selected
     * @return  string  $month_options  select month options
     * @see     showDateSelector()
     */
    function getMonthFormOptions($selected = '')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aMonths = SGL_String::translate('aMonths', false, true);
        $monthOptions = '';
        if (empty($selected) && $selected != null) {
            $selected = date('m',time());
        }
        for ($i = 1; $i <= 12; $i++) {
            if ($i < 10) {
                $mval = '0' . $i;
            } else {
                $mval = $i;
            }
            $monthOptions .= "\n<option value=\"" . $mval . '" ';
            if ($i == $selected) {
                $monthOptions .= 'selected="SELECTED"';
            }
            $monthOptions .= '>' . $aMonths[$mval + 12] . '</option>';
        }
        return $monthOptions;
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
            if ($i < 10) {
                $dval = '0' . $i;
            } else {
                $dval = $i;
            }
            $day_options .= "\n<option value=\"" . $dval . '" ';
            if ($i == $selected) {
                $day_options .= 'selected="SELECTED"';
            }
            $day_options .= '>' . $dval . '</option>';
        }
        return $day_options;
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
    function getYearFormOptions($selected = '', $asc = true, $number = 5)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $year_options = '';
        $cur_year = date('Y',time());
        $start_year = $cur_year;
        if (!empty($selected)) {
            if ($selected < $cur_year) {
                $start_year = $selected;
            }
        }
        if ($asc) {
             for ($i = $start_year; $i <= $start_year+$number; $i++) {
                 $year_options .= "\n<option value=\"" . $i . '" ';
                 if ($i == $selected) {
                     $year_options .= 'selected="selected"';
                 }
                 $year_options .= '>' . $i . '</option>';
             }
        } else {
             for ($i = $start_year+1; $i >= $start_year-($number-1); $i--) {
                 $year_options .= "\n<option value=\"" . $i . '" ';
                 if ($i == $selected) {
                     $year_options .= 'selected="selected"';
                 }
                 $year_options .= '>' . $i . '</option>';
             }
        }
        return $year_options;
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
            if ($selected == $i && $selected!="" ) {
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
            if ($i < 10) {
                $mval = '0' . $i;
            } else {
                $mval = $i;
            }
            $minute_options .= "\n<option value=\"" . $mval . '" ';
            if ($selected == $i && $selected!="" ) {
                $minute_options .= 'selected="SELECTED"';
            }
            $minute_options .= '>' . $mval . '</option>';
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
     * @param   string  $sFormName  name of form
     * @param   boolean $bShowTime  toggle to display HH:MM:SS
     * @param   bool    $asc
     * @param   int     $years      number of years to show
     * @return  string  $html       html for widget
*/
    function showDateSelector($aDate, $sFormName, $bShowTime = true, $asc = true, $years = 5)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $html = '';
        $html .= "\n<select name='" . $sFormName . "[month]' id='".$sFormName."_month' >" . SGL_Date::getMonthFormOptions($aDate['month']) . '</select> / ';
        $html .= "\n<select name='" . $sFormName . "[day]' id='".$sFormName."_day'>" . SGL_Date::getDayFormOptions($aDate['day']) . '</select> / ';
        $html .= "\n<select name='" . $sFormName . "[year]' id='".$sFormName."_year'>" . SGL_Date::getYearFormOptions($aDate['year'], $asc, $years) . '</select>';
        if ($bShowTime) {
            $html .= '&nbsp;&nbsp; ';
            $html .= SGL_String::translate('at time');
            $html .= ' &nbsp;&nbsp;';
            $html .= "\n<select name='" . $sFormName . "[hour]'  id='".$sFormName."_hour'>" . SGL_Date::getHourFormOptions($aDate['hour']) . '</select> : ';
            $html .= "\n<select name='" . $sFormName . "[minute]' id='".$sFormName."_minute'>" . SGL_Date::getMinSecOptions($aDate['minute']) . '</select> : ';
            $html .= "\n<select name='" . $sFormName . "[second]' id='".$sFormName."_second'>" . SGL_Date::getMinSecOptions($aDate['second']) . '</select>';
        }
        return $html;
    }
}

/**
 * Performs transformations on resource names, ie, urls, classes, methods, variables.
 *
 */
class SGL_Inflector
{
    /**
    * Returns true if querystring has been simplified.
    *
    * This happens when a manager name is the same as its module name, ie
    * UserManger in the 'user' module would become user/user which gets
    * reduced to user
    *
    * $querystring does not include the frontScriptName, ie, index.php
    *
    * @param string $querystring    From the querystring fragment onwards, ie /user/account/userid/2/
    * @param string $sectionName    From the database
    * @return boolean
    */
    function isUrlSimplified($querystring, $sectionName)
    {
        if (!(empty($querystring))) {
            if (SGL_Inflector::urlContainsDuplicates($querystring)) {
                $ret = false;
            } else {
                $aUrlPieces = explode('/', $querystring);
                $moduleName = $aUrlPieces[0];
                $aSections =  explode('/', $sectionName);
                $ret = in_array($moduleName, $aSections)
                    && (SGL_Inflector::urlContainsDuplicates($sectionName));
            }
        } else {
            $ret = false;
        }
        return $ret;
    }

    /**
     * Returns true if manager name is the same of module name, ie, index.php/faq/faq/.
     *
     * @param string $url
     * @return boolean
     */
    function urlContainsDuplicates($url)
    {
        if (!empty($url)) {
            $aPieces = explode('/', $url);
            $initial = count($aPieces);
            $unique = count(array_unique($aPieces));
            $ret = $initial != $unique;
        } else {
            $ret = false;
        }
        return $ret;
    }

    /**
     * Determine if a simplified notation is being used.
     *
     * If the url was of the form example.com/index.php/contactus/contactus/
     * and it got simplifeid too example.com/index.php/contactus/ it is important
     * to determine if that simplification happened, so subsequent parameters
     * don't get interpreted as 'managerName'
     *
     * @param array $aParsedUri
     * @return boolean
     */
    function isMgrNameOmitted($aParsedUri)
    {
        $fullMgrName = SGL_Inflector::getManagerNameFromSimplifiedName(
            $aParsedUri['managerName']);

        //  compensate for case-sensitivity
        $corrected = SGL_Inflector::caseFix($fullMgrName, true);
        $path = dirname(__FILE__) .'/../../modules/'. $aParsedUri['moduleName'] . '/classes/' . $corrected . '.php';

        //  if the file exists, mgr name is valid and has not been omitted
        return !file_exists($path);
    }

    /**
     * Returns the full Manager name given the short name, ie, faq becomes FaqMgr.
     *
     * @param string $name
     * @return string
     */
    function getManagerNameFromSimplifiedName($name)
    {
        //  if Mgr suffix has been left out, append it
        if (strtolower(substr($name, -3)) != 'mgr') {
            $name .= 'Mgr';
        }
        return ucfirst($name);
    }

    /**
     * Returns the short name given the full Manager name, ie FaqMgr becomes faq.
     *
     * @param unknown_type $name
     * @return unknown
     */
    function getSimplifiedNameFromManagerName($name)
    {
        //  strip file extension if exists
        if (substr($name, -4) == '.php') {
            $name = substr($name, 0, -4);
        }

        //  strip 'Mgr' if exists
        if (strtolower(substr($name, -3)) == 'mgr') {
            $name = substr($name, 0, -3);
        }
        return strtolower($name);
    }

    function getTitleFromCamelCase($camelCaseWord)
    {
        if (!SGL_Inflector::isCamelCase($camelCaseWord)) {
            return $camelCaseWord;
        }
        $ret = '';
        for ($x = 0; $x < strlen($camelCaseWord); $x ++) {
            if (preg_match("/[A-Z]/", $camelCaseWord{$x})) {
                $ret .= ' ';
            }
            $ret .= $camelCaseWord{$x};
        }
        return ucfirst($ret);
    }

    function isCamelCase($str)
    {
        //  ensure no non-alpha chars
        if (preg_match("/[^a-z].*/i", $str)) {
            return false;
        }
        //  and at least 1 capital not including first letter
        for ($x = 1; $x < strlen($str)-1; $x ++) {
            if (preg_match("/[A-Z]/", $str{$x})) {
                return true;
            }
        }
    }

    /**
     * Makes up for case insensitive classnames in php4 with get_class().
     *
     * @access   public
     * @static
     * @param    string     $str    Classname
     * @param    boolean    $force  Force the operation regardless of php version
     * @return   mixed              Either correct case classname or false
     */
    function caseFix($str, $force = false)
    {
        if (!$force && (($phpVersion{0} = PHP_VERSION) == 5)) {
            return $str;
        }
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        $aConfValues = array_keys($conf);
        $aConfValuesLowerCase = array_map('strtolower', $aConfValues);
        $isFound = array_search(strtolower($str), $aConfValuesLowerCase);
        return ($isFound !== false) ? $aConfValues[$isFound] : false;
    }
}
?>
