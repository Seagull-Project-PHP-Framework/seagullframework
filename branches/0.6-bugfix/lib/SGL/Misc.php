<?php
class SGL_Cache
{
    /**
     * Returns a singleton Cache_Lite instance.
     *
     * example usage:
     * $cache = & SGL_Cache::singleton();
     * warning: in order to work correctly, the cache
     * singleton must be instantiated statically and
     * by reference
     *
     * @access  public
     * @param boolean $force    If true the $conf['cache']['enabled'] setting will
     *                          be ignored and caching enabled
     * @static
     * @return  mixed reference to Cache_Lite object
     */
    function &singleton($force = false)
    {
        static $instance;

        // If the instance doesn't exist, create one
        if (!isset($instance) || $force) {
            require_once 'Cache/Lite.php';
            $c = &SGL_Config::singleton();
            $conf = $c->getAll();

            $isEnabled = ($force) ? true : $conf['cache']['enabled'];
            $options = array(
                'cacheDir'  => SGL_TMP_DIR . '/',
                'lifeTime'  => $conf['cache']['lifetime'],
                'caching'   => $isEnabled);
            $instance = new Cache_Lite($options);
        }
        return $instance;
    }

    /**
     * Clear cache directory of a specific module's cache files. A simple wrapper to
     * PEAR::Cache_Lite's clean() method.
     *
     * @access public
     * @param  string $group name of the cache group (e.g. nav, blocks, etc.)
     * @return boolean true on success
     * @author  Andy Crain <apcrain@fuse.net>
     */
     function clear($group = false)
     {
        $cache = & SGL_Cache::singleton();
        return $cache->clean($group);
     }
}


class SGL_Error
{
    /**
     * Returns true if one or more PEAR errors exist on the global stack.
     *
     * @return boolean
     */
    function count()
    {
        return count($GLOBALS['_SGL']['ERRORS']);
    }

    /**
     * Pushes an error onto stack.
     *
     * @param PEAR_Error $oError
     * @return integer   Returns the new number of elements in the array.
     */
    function push($oError)
    {
        return array_push($GLOBALS['_SGL']['ERRORS'], $oError);
    }

    /**
     * Pops last error off stack.
     *
     * @return PEAR_Error
     */
    function pop()
    {
        return array_pop($GLOBALS['_SGL']['ERRORS']);
    }

    /**
     * Remove first error off stack.
     *
     * @return unknown
     */
    function shift()
    {
        return array_shift($GLOBALS['_SGL']['ERRORS']);
    }

    function toString($oError)
    {
        $message = $oError->getMessage();
        $debugInfo = $oError->getDebugInfo();
        $level = $oError->getCode();
        $errorType = SGL_Error::constantToString($level);
        $output = <<<EOF
  <strong>MESSAGE</strong>: $message<br />
  <strong>TYPE:</strong> $errorType<br />
  <strong>DEBUG INFO:</strong> $debugInfo<br />
  <strong>CODE:</strong> $level<br />
EOF;
        return $output;
    }

    /**
     * Converts error constants into equivalent strings.
     *
     * @access  public
     * @param   int     $errorCode  error code
     * @return  string              text representing error type
     */
    function constantToString($errorCode)
    {
        $aErrorCodes = array(
            SGL_ERROR_INVALIDARGS       => 'invalid arguments',
            SGL_ERROR_INVALIDCONFIG     => 'invalid config',
            SGL_ERROR_NODATA            => 'no data',
            SGL_ERROR_NOCLASS           => 'no class',
            SGL_ERROR_NOMETHOD          => 'no method',
            SGL_ERROR_NOAFFECTEDROWS    => 'no affected rows',
            SGL_ERROR_NOTSUPPORTED      => 'not supported',
            SGL_ERROR_INVALIDCALL       => 'invalid call',
            SGL_ERROR_INVALIDAUTH       => 'invalid auth',
            SGL_ERROR_EMAILFAILURE      => 'email failure',
            SGL_ERROR_DBFAILURE         => 'db failure',
            SGL_ERROR_DBTRANSACTIONFAILURE => 'db transaction failure',
            SGL_ERROR_BANNEDUSER        => 'banned user',
            SGL_ERROR_NOFILE            => 'no file',
            SGL_ERROR_INVALIDFILEPERMS  => 'invalid file perms',
            SGL_ERROR_INVALIDSESSION    => 'invalid session',
            SGL_ERROR_INVALIDPOST       => 'invalid post',
            SGL_ERROR_INVALIDTRANSLATION => 'invalid translation',
            SGL_ERROR_FILEUNWRITABLE    => 'file unwritable',
            SGL_ERROR_INVALIDREQUEST    => 'invalid request',
            SGL_ERROR_INVALIDTYPE       => 'invalid type',
            SGL_ERROR_RECURSION         => 'recursion',
        );
        if (in_array($errorCode, array_keys($aErrorCodes))) {
            return strtoupper($aErrorCodes[$errorCode]);

        //  if not within this range, most likely a PEAR::DB error
        } else {
            return 'PEAR';
        }
    }
}

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
        } else {
            SGL::raiseError('array argument expected, got '.gettype($elem),
                SGL_ERROR_INVALIDARGS);
        }
        return $clean;
    }

    /**
     * Returns an array with imploded keys.
     *
     * @param string $glue
     * @param array $hash
     * @param string $valwrap
     * @return string
     */
    function implodeWithKeys($glue, $hash, $valwrap='')
    {
        if (is_array($hash) && count($hash)) {
            foreach ($hash as $key => $value) {
                $aResult[] = $key.$glue.$valwrap.$value.$valwrap;
            }
            $ret = implode($glue, $aResult);
        } else {
            $ret = '';
        }
        return $ret;
    }

    /**
     * Merges two arrays and replace existing entrys.
     *
     * Merges two Array like the PHP Function array_merge_recursive.
     * The main difference is that existing keys will be replaced with new values,
     * not combined in a new sub array.
     *
     * Usage:
     *        $newArray = SGL_Array::mergeReplace($array, $newValues);
     *
     * @access puplic
     * @param array $array First Array with 'replaceable' Values
     * @param array $newValues Array which will be merged into first one
     * @return array Resulting Array from replacing Process
     */
    function mergeReplace($array, $newValues)
    {
        foreach ($newValues as $key => $value) {
            if (is_array($value)) {
                if (!isset($array[$key])) {
                    $array[$key] = array();
                }
                $array[$key] = SGL_Array::mergeReplace($array[$key], $value);
            } else {
                if (isset($array[$key]) && is_array($array[$key])) {
                    $array[$key][0] = $value;
                } else {
                    if (isset($array) && !is_array($array)) {
                        $temp = $array;
                        $array = array();
                        $array[0] = $temp;
                    }
                    $array[$key] = $value;
                }
            }
        }
        return $array;
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
                $output = $date->format('%d.%B.%Y %H:%M');

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

        $aMonths = SGL_String::translate('aMonths', false);
        $monthOptions = '';
        if (empty($selected) && $selected != null) {
            $selected = date('m', time());
        }
        foreach ($aMonths as $k => $v) {
            $monthOptions .= "\n<option value=\"" . sprintf('%02d', $k) . '" ';
            if ($k == $selected) {
                $monthOptions .= 'selected="selected"';
            }
            $monthOptions .= '>' . $v . '</option>';
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
        $path = SGL_MOD_DIR . '/'. $aParsedUri['moduleName'] . '/classes/' . $corrected . '.php';

        //  if the file exists, mgr name is valid and has not been omitted
        return !is_file($path);
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
        return SGL_Inflector::caseFix(ucfirst($name));
    }

    /**
     * Returns the short name given the full Manager name, ie FaqMgr becomes faq.
     *
     * @param string $name
     * @return string
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
     * @return   mixed              Either correct case classname or original classname if no key found
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
        return ($isFound !== false) ? $aConfValues[$isFound] : $str;
    }
}

/**
 * Provides HTTP redirects.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_HTTP
{
    /**
     * Wrapper for PHP header() redirects.
     *
     * Simplified version of Wolfram's HTTP_Header class
     *
     * @access  public
     * @static
     * @param   mixed   $url    target URL
     * @return  void
     * @author  Wolfram Kriesing <wk@visionp.de>
     */
    function redirect($url = '')
    {
        //  if arg is not an array of params, pass straight to header function
        if (is_scalar($url) && strlen($url)) {

            //  add a trailing slash if one is not present for uris passed as strings
            if (substr($url, -1) != '/') {
                $url .= '/';
            }
        } else {

            $c = &SGL_Config::singleton();
            $conf = $c->getAll();

            //  get a reference to the request object
            $req = & SGL_Request::singleton();

            if (is_scalar($url)) {
                $url = array();
            }

            $moduleName  =  (array_key_exists('moduleName', $url))
                ? $url['moduleName']
                : $req->get('moduleName');
            $managerName =  (array_key_exists('managerName', $url))
                ? $url['managerName']
                : $req->get('managerName');

            //  parse out rest of querystring
            $aParams = array();
            foreach ($url as $k => $v) {
                if ($k == 'moduleName' || $k == 'managerName') {
                    continue;
                }
                if (is_string($k)) {
                    $aParams[] = urlencode($k).'/'.urlencode($v);
                }
            }
            $qs = (count($aParams)) ? implode('/', $aParams): '';
            $url = ($conf['site']['frontScriptName'])
                ? $conf['site']['frontScriptName'] . '/' . $moduleName
                : $moduleName;

            if (!empty($managerName)) {
                $url .=  '/' . $managerName;
            }
            $url .= '/' . $qs;

            //  check for absolute uri as specified in RFC 2616
            SGL_Url::toAbsolute($url);

            //  add a slash if one is not present
            if (substr($url, -1) != '/') {
                $url .= '/';
            }
            //  determine is session propagated in cookies or URL
            SGL_Url::addSessionInfo($url);
        }

        //  must be absolute URL, ie, string
        header('Location: ' . $url);
        exit;
    }
}
?>
