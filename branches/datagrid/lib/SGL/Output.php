<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
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
// | Seagull 0.5                                                               |
// +---------------------------------------------------------------------------+
// | Output.php                                                                |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Output.php,v 1.22 2005/06/04 23:56:33 demian Exp $

/**
 * High level HTML transform methods, 'Template Helpers' in Yahoo speak, 50% html,
 * 50% php.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.22 $
 * @todo    look at PEAR::Date to improve various date methods used here
 */
class SGL_Output
{
    var $onLoad = '';
    var $aOnLoadEvents = array();

    /**
     * Translates source text into target language.
     *
     * @access  public
     * @static
     * @param   string  $key    translation term
     * @param   string  $filter optional filter fn, ie, strtoupper()
     * @return  string          translated text
     * @see     setLanguage()
     */
    function translate($key, $filter = false, $aParams = array())
    {
        return SGL_String::translate($key, $filter, $aParams);
    }

    /**
     * Generates options for an HTML select object.
     *
     * @access  public
     * @param   array   $array      hash of select values
     * @param   mixed   $selected   default selected element, array for multiple elements
     * @param   boolean $multiple   true if multiple
     * @param   array   $options    attibutes to add to the input tag : array() {"class" => "myClass", "onclick" => "myClickEventHandler()"}
     * @return  string  select options
     */
    function generateSelect($aValues, $selected = null, $multiple = false, $options = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (!is_array($aValues) || (isset($options) && !is_array($options))) {
            SGL::raiseError('Incorrect param passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        if (is_numeric($selected)) {
            $selected = (int) $selected;
        }
        $optionsString = '';
        if (isset($options)) {
            foreach ($options as $k => $v) {
                $optionsString .= ' ' . $k . '="' . $v . '"';
            }
        }
        $r = '';
        if ($multiple && is_array($selected)) {
            foreach ($aValues as $k => $v) {
                $isSelected = in_array($k, $selected) ? ' selected="selected"' : '';
                $r .= "\n<option value=\"$k\"" . $isSelected . $optionsString . ">$v</option>";
            }
        } else {
            //  ensure $selected is not the default null arg, allowing
            //  zeros to be selected array elements
            $r = '';
            foreach ($aValues as $k => $v) {
                $isSelected = ($k === $selected && !is_null($selected)) ? ' selected="selected"' : '';
                $r .= "\n<option value=\"$k\"". $isSelected . $optionsString . ">$v</option>";
            }
        }
        return $r;
    }

    /**
     * Generates sequence checkboxes.
     *
     * @access  public
     * @param   array   $hElements  hash of checkbox values
     * @param   array   $aChecked   array of checked elements
     * @param   string  $groupName  usually an array name that will contain all elements
     * @param   array   $options    attibutes to add to the input tag : array() {"class" => "myClass", "onclick" => "myClickEventHandler()"}
     * @return  string  html        list of checkboxes
     */
    function generateCheckboxList($hElements, $aChecked, $groupName, $options = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (!is_array($hElements) || !is_array($aChecked) || (isset($options) && !is_array($options))) {
            SGL::raiseError('Incorrect param passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
            return false;
        }
        $optionsString = '';
        if (isset($options)) {
            foreach ($aValues as $k => $v) {
                $optionsString .= ' ' . $k . '="' . $v . '"';
            }
        }
        $html = '';
        foreach ($hElements as $k => $v) {
            $isChecked = (in_array($k, $aChecked)) ? ' checked' : '';
            $html .= "<input class='noBorder' type='checkbox' name='$groupName' " .
                     "id='$groupName-$k' value='$k'" . $optionsString . " $isChecked><label for='$groupName-$k'>$v</label><br />\n";
        }
        return $html;
    }

    /**
     * Generate checkbox.
     *
     * @access  public
     * @param   string   $name       element name
     * @param   string   $value      element value
     * @param   boolean  $checked    is checked
     * @param   array   $options     attibutes to add to the input tag : array() {"class" => "myClass", "onclick" => "myClickEventHandler()"}
     * @return  string  html         checkbox tag w/label
     */
    function generateCheckbox($name, $value, $checked, $options = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (isset($options) && !is_array($options)) {
            SGL::raiseError('Incorrect param passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
            return false;
        }
        $isChecked = $checked ? ' checked' : '';
        $optionsString = '';
        if (isset($options)) {
            foreach ($aValues as $k => $v) {
                $optionsString .= ' ' . $k . '="' . $v . '"';
            }
        }
        $html = "<input class='noBorder' type='checkbox' name='$name' " .
            "id= '$name' value='$value'" . $optionsString . " $isChecked><label for='$name'>$value</label><br />\n";
        return $html;
    }

    /**
     * Generates a yes/no radio pair.
     *
     * @access  public
     * @param   string   $radioName  name of radio element
     * @param   boolean  $checked    is checked
     * @param   array   $options     attibutes to add to the input tag : array() {"class" => "myClass", "onclick" => "myClickEventHandler()"}
     * @return  string   html        yes/no radio pair
     */
    function generateRadioPair($radioName, $checked, $options = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (isset($options) && !is_array($options)) {
            SGL::raiseError('Incorrect param passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
            return false;
        }
        $radioString = '';
        if ($checked) {
            $yesChecked = ' checked';
            $noChecked = '';
        } else {
            $yesChecked = '';
            $noChecked = ' checked';
        }
        $optionsString = '';
        if (isset($options)) {
            foreach ($options as $k => $v) {
                $optionsString .= ' ' . $k . '="' . $v . '"';
            }
        }
        $radioString .= "<input type='radio' name='$radioName' value='0'" . $optionsString . " $noChecked>".SGL_String::translate('no')."\n";
        $radioString .= "<input type='radio' name='$radioName' value='1'" . $optionsString . " $yesChecked>".SGL_String::translate('yes')."\n";
        return $radioString;
    }

    /**
     * Generates sequence of radio button from array.
     *
     * @access  public
     * @param   array   $elements   array of  values or radio button
     * @param   array   $selected   array selected key ... single array with only zero index , i am need in array
     * @param   string  $groupname  usually an array name that will contain all elements
     * @param   integer $newline    how many columns to display for this radio group
     * @param   array   $options    attibutes to add to the input tag : array() {"class" => "myClass", "onclick" => "myClickEventHandler()"}
     * @param 	boolean $inTable    true for adding table formatting
     * @return  string  $html       a list of radio buttons
     */
    function generateRadioList($elements, $selected, $groupname, $newline, $inTable = true, $options = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (!is_array($elements) || (isset($options) && !is_array($options))) {
            SGL::raiseError('Incorrect param passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
            return false;
        }
        $elementcount = count($elements);
        $html = '';
        $i = 0;
        $optionsString = '';
        if (isset($options)) {
            foreach ($aValues as $k => $v) {
                $optionsString .= ' ' . $k . '="' . $v . '"';
            }
        }
        if ($inTable == false){
            foreach ($elements as $k => $v) {
                $i = $i + 1;
                $html .= "<input name='" . $groupname . "' type='radio' value='" . $k . "'" . $optionsString . " ";
                if ($selected[0] == $k ){
                    $html .= " checked";
                }
                $html .= " />$v ";
                $modvalue = $i % $newline;
                if ($modvalue == 0 ) {
                    $html .= "<br/>\n";
                }
            }
        } else {
            $html ="<table>";
            $html .="<tr>";
            foreach ($elements as $k => $v) {
                $i = $i + 1;
                $html .= "<td nowrap='nowrap'><input name='" . $groupname . "' type='radio' value='" . $k . "'" . $optionsString . " ";
                if ($selected[0] == $k ) {
                    $html .= " checked ";
                }
                $html .= " />$v </td>\n";
                $modvalue = $i % $newline;
                if ( $modvalue == 0 ) {
                    if ($i < $elementcount){
                        $html .="</tr>\n<tr>";
                    } else {
                        $html .="</tr>\n";
                    }
                }
            }
            $html .="</table>";
        }
        return $html;
    }

    /**
     * Wrapper for SGL_String::formatBytes(),
     * Converts bytes to Kb or MB as appropriate.
     *
     * @access  public
     * @param   int $bytes
     * @return  int kb/MB
     */
    function formatBytes($size)
    {
        return SGL_String::formatBytes($size);
    }

    // +---------------------------------------+
    // | Date related methods                  |
    // +---------------------------------------+

    /**
     * Converts date (may be in the ISO, TIMESTAMP or UNIXTIME format) into dd.mm.yyyy.
     *
     * @access  public
     * @param   string  $input  date (may be in the ISO, TIMESTAMP or UNIXTIME format) value
     * @return  string  $output user-friendly format (european)
     */
    function formatDate($date = '')
    {
        if (empty($date)) {
            $date = SGL_Date::getTime();
        }
        return SGL_Date::format($date);
    }

    /**
     * Converts date (may be in the ISO, TIMESTAMP or UNIXTIME format) into "Mar 31, 2003 18:29".
     *
     * @access  public
     * @param   string  $date  Date (may be in the ISO, TIMESTAMP or UNIXTIME format) value
     * @return  string  $formatted  user-friendly format (european)
     */
    function formatDatePretty($date = '')
    {
        if (empty($date)) {
            $date = SGL_Date::getTime();
        }
        return SGL_Date::formatPretty($date);
    }

    /**
     * Gets appropriate date format
     *
     * @access  public
     * @return  string  $date template (e.g. "%d %B %Y, %H:%M" for FR date format)
     */
    function getDateFormat()
    {
        return SGL_Date::getDateFormat();
    }

    /**
     * Wrapper for SGL_Date::showDateSelector(),
     * Generates date/time selector widget.
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
        return SGL_Date::showDateSelector($aDate, $sFormName, $bShowTime, $asc, $years);
    }

    /**
     * Creates a checkbox for infinite Articles (no expiry)
     *
     * @access public
     * @param  array $aDate if NULL checkbox is checked
     * @param  string $sFormName Name of Date Selector to reset if checkbox is clicked
     * @return string with checkbox. Name of checkbox will be $sFormName.NoExpire, e.g. ExpiryDateNoExpire
     */
    function getNoExpiryCheckbox($aDate,$sFormName)
    {
        $checked = ($aDate == null) ? 'checked' : '';
        return '<input type="checkbox" name="'.$sFormName.'NoExpire" id="'.$sFormName.'NoExpire" value="true" onClick="time_select_reset(\''.$sFormName.'\',true);"  '.$checked.' /> '.SGL_Output::translate('No expire');
    }

    /**
     * Generates alternate classes for rows in tables, used to switch
     * row colors.
     *
     * @access  public
     * @param   boolean $isBold
     * @return  string  $curRowClass string representing class found in stylesheet
     */

    function switchRowClass($isBold = false, $id = 'default')
    {
        //  remember the last color we used
        static $curRowClass;
        static $_id;

        if ($_id != $id) {
            $curRowClass = '';
            $_id = $id;
        }

        if ($curRowClass == 'backLight' && $isBold ) {
            $curRowClass = 'backDark bold';
        } elseif ($curRowClass == 'backLight') {
            $curRowClass = 'backDark';
        } elseif ($isBold) {
            $curRowClass = 'backLight bold';
        } else {
            $curRowClass = 'backLight';
        }

        return $curRowClass;
    }

    /**
     * Generates alternate value (false/true) to be used in template
     *
     * @access  public
     * @param int $elementsToCount Number of elements to reach to switch from false/true, default 2
     * @return  bool  $switcher
     */

    function switchTrueFalse($elementsToCount=2)
    {
        static $count;
        if ($count % $elementsToCount) {
            $switcher = false;
        } else {
            $switcher = true;
        }
        $count++;

        return $switcher;
    }

    /**
     * Wrapper for SGL_String::summarise(),
     * Returns a shortened version of text string.
     *
     * @access  public
     * @param   string  $str    Text to be shortened
     * @param   integer $limit  Number of characters to cut to
     * @param   string  $appendString  Trailing string to be appended
     * @return  string  $processedString    Correctly shortened text
     */
    function summarise($str, $limit=50, $element=SGL_WORD, $appendString=' ...')
    {
         return SGL_String::summarise($str, $limit, $element, $appendString);
    }

    function msgGet()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $message     = SGL_Session::get('message');
        $messageType = SGL_Session::get('messageType');
        if (isset($message) && $message != '') {
            SGL_Session::remove('message');
            SGL_Session::remove('messageType');

            switch ($messageType) {

            case SGL_MESSAGE_INFO:
                $class = 'info';
                break;

            case SGL_MESSAGE_WARNING:
                $class = 'warning';
                break;

            default:
                $class = 'error';
            }
            echo '<div class="' . $class . 'Message">' . $message . '</div>';

            //  required to remove message that persists when register_globals = on
            unset($GLOBALS['message']);
            unset($GLOBALS['messageType']);
        } elseif (SGL_Error::count()) {

            //  for now get last message added to stack
            $msg = SGL_Error::toString($GLOBALS['_SGL']['ERRORS'][0]);
            echo '  <div class="errorContainer">
                        <div class="errorHeader">Error</div>
                        <div class="errorContent">' . $msg . '</div>
                    </div>';
        } else {
            return false;
        }
    }

    //  return true if role id  is admin (1)
    /**
     * Returns true if current user or passed role ID is that of an admin.
     *
     * @return boolean
     */
    function isAdmin($rid = null)
    {
        if (is_null($rid)) {
            $rid = SGL_Session::getRoleId();
        }
        return ($rid && $rid == SGL_ADMIN) ? true : false;
    }

    //  return true if $rid is 1 or -1
    function isAdminOrUnassigned($rid)
    {
        return (abs($rid) == SGL_ADMIN) ? true : false;
    }

    function addOnLoadEvent($event)
    {
        $this->aOnLoadEvents[] = $event;
    }

    function getAllOnLoadEvents()
    {
        if (count($this->aOnLoadEvents)) {
            return implode(';', $this->aOnLoadEvents);
        }
    }
    /**
     * Wrapper for SGL_Url::makeLink,
     * Generates URL for easy access to modules and actions.
     *
     * @access  public
     * @param string $action
     * @param string $mgr
     * @param string $mod
     * @param array $aList
     * @param string $params
     * @param integer $idx
     * @return  string
     */
    function makeUrl($action = '', $mgr = '', $mod = '', $aList = array(), $params = '', $idx = 0)
    {
        return SGL_Url::makeLink($action, $mgr, $mod, $aList, $params, $idx, $this);
    }

    function isVerticalNav($styleSheet)
    {
        return in_array($styleSheet, array('SglListamaticSubtle', 'verticalSimple'));
    }

    function outputBody($templateEngine = null)
    {
        if (empty($this->template)) {
	    $this->template = 'docBlank.html';
        }
        $this->masterTemplate = $this->template;
        $view = &new SGL_HtmlSimpleView($this, $templateEngine);
        echo $view->render();
    }

    /**
     * Returns true if client OS is windows.
     *
     * @return boolean
     */
    function isWin()
    {
        return SGL_CLIENT_OS == 'Win';
    }

    /**
     * Returns true if a and b are equal.
     *
     */
    function isEqual($a, $b)
    {
        return $a == $b;
    }

    /**
     * Check permission at the template level and returns true if permission
     * exists.
	 *
	 * Use as follows in any Flexy template:
	 * <code>
     * {if:hasPerms(#faqmgr_delete#)} on {else:} off {end:}
     * </code>
     *
     * To get various perm names, select User module then go to 'perms' section.
     *
     * @access  public
     * @param   string  $permName    Name of permission eg. "faqmgr_delete"
     * @return 	boolean
     *
     */
    function hasPerms($permName)
    {
        $permId = @constant('SGL_PERMS_' . strtoupper($permName));
        return (!empty($permId) && SGL_Session::hasPerms($permId) ? true : false);
    }

    /**
     * printf function wrapper.
     *
     * @return string
     */
    function printf()
    {
        $argv = func_get_args();
        return @call_user_func_array('sprintf', $argv);
    }


    /** ------------------ VARICO changes -----------------------------
    * below functions were added by varico programmers
    *
    *``````````````````````````````````````````````````````````````````
    */
    function getMultilangValue($object, $varName, $varLang, $varProp) {

        $varName = $varName . $varLang . $varNameEnd;
        if ($varProp == 'label') {
            return SGL_string::translate($object->$varName->$varProp);
        } else {
            return $object->$varName->$varProp;
        }
    }
    function getVarName($varName = "", $varName1 = "", $varName2 = "") {
        $varName .= $varName1 .= $varName2;
        return $varName;
    }

    function getObjectValue($object, $value, $length = null) {
        if (is_null($length) || strlen($object->$value) < $length)
            return $object->$value;
        else
            return substr($object->$value, 0, $length) . '...';
    }

    function getArrayValue($array, $value, $length = null, $isCurrency = false) {
        if (is_null($length) || strlen($array[$value]) < $length)
            if($isCurrency) {
                return number_format($array[$value], 2);
            }
            else {
                return $array[$value];
            }
        else
            return substr($array[$value], 0, $length) . '...';
    }
    
    function getArrayTranslateValue($array, $value, $length = null, $isCurrency = false) {
                return SGL_String::translate($array[$value]);
    }

    function getDateArrayValue($array, $value, $length = null) {
        $tempDate = $this->getArrayValue($array, $value, $length);
        return $this->formatDate($tempDate);
    }
    
    function getDateTimeArrayValue($array, $value, $length = null) {
        $tempDate = $this->getArrayValue($array, $value, $length);
        include_once 'Date.php';
        $date = & new Date($tempDate);
        return $date->format('%d.%m.%Y %H:%M');
    }
    
    function getDateTime2ArrayValue($array, $value, $varName) {
        if ($array[$value.$varName] != '') {
            $tempDate = $this->getArrayValue($array, $value.$varName);
            include_once 'Date.php';
            $date = & new Date($tempDate.':00');
            return $date->format('%d.%m.%Y %H:%M');
        } else
            return '';
    }
    function getActionValue($action, $valueObj, $cut = true) {
        $subject = $action;
        foreach ($valueObj as $key => $value) {
            $replace = $value;
            $search = "{".$key."}";
            $result = ereg_replace($search, $replace, $subject);
            $subject = $result;
        }
        $subject = str_replace("\n"," ",$subject);
        $subject = str_replace("\r"," ",$subject);
        $subjectLength = strlen($subject);
        if ($cut) {
            $subject = substr($subject,0,150);
            if ($subjectLength > 150)
                $subject .= ' ...';
        }
        return $subject;
    }

    function getVarNameAndArrayValue($array, $value, $varName) {
        $temp = $array[$value.$varName];
        if ($this->formatDate2DB($temp)) {
            return $this->formatDate($temp);
        }
        return $temp;
    }

    function setTemplateFields($templateField, $templateFieldValue) {
        $this->$templateField = $templateFieldValue;
    }

    function orEqual($firstObject, $object1, $object2, $object3 = '', $object4 = '') {
        return (($firstObject == $object1) || ($firstObject == $object2) || ($firstObject == $object3) || ($firstObject == $object4));
    }

    function isArray($array) {
        return is_array($array);
    }

    function isEqualWithArrayValue($object, $array, $value) {
        return $object == $this->getArrayValue($array, $value);
    }

    function arrayNotEmpty($array) {
        if (count($array) >= 1) {
            return true;
        }
        return false;
    }

    function isGreater($object1, $object2) {
        return $object1 > $object2;
    }


    /**
     * gets path to specified file in theme
     *
     * @access  public
     * @static
     * @param   string  $fileName           file name located in theme
     */
    function getThemeFileDir($fileName) {
        $fileDir = SGL_WWW_ROOT . '/themes/' . $fileName;
        if (file_exists($fileDir)) {
            return $fileDir;
        }
        return null;
    }

    /**
     * gets URL to specified file in theme
     *
     * @access  public
     * @static
     * @param   string  $fileName           file name located in theme
     */
    function getThemeFileURL($fileName) {
        return SGL_BASE_URL . '/themes/' . $fileName;
    }

    function getUserLanguages() {
        //AM this code goes from PEAR::Net_UserAgent_Detect class
        $languages = preg_split(';[\s,]+;', substr(getenv('HTTP_ACCEPT_LANGUAGE'), 0, strpos(getenv('HTTP_ACCEPT_LANGUAGE') . ';', ';')), -1, PREG_SPLIT_NO_EMPTY);
        if (empty($languages)) {
            $languages = array('en');
        }
        return $languages;
    }

    function getPreferredLanguage() {
        $languages = SGL_Output::getUserLanguages();
        return $languages[0];
    }

    /**
     * Formats date for the current user
     * @param   string  $sDate  Date in user or DB format  (YYYY-mm-dd or dd.mm.yyyy)
     * @return  string  Date formatted for the DB format (YYYY-mm-dd)
     *                   if date is not proper - return null
     */
    function formatDate2DB($sDate) {
        //check if date is in correct format
        if (preg_match("/([0-9]{4}-[0-9]{2}-[0-9]{2})$/", $sDate)) {
            $aDate = explode("-", $sDate);
            if (checkdate($aDate[1], $aDate[2], $aDate[0])) {
                return date("Y-m-d", mktime (0, 0, 0, $aDate[1], $aDate[2], $aDate[0]));
            }
        } elseif (preg_match("/[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}/", $sDate)) {
            $aDate = explode(".", $sDate);
            if (checkdate($aDate[1], $aDate[0], $aDate[2])) {
                    return date("Y-m-d", mktime (0, 0, 0, $aDate[1], $aDate[0], $aDate[2]));
            }
        } elseif (strcmp($sDate, "now()") == 0) {
            return $sDate;
        }
        return null;
    }

    function makeValidPrintLink($selfUrl)
    {
        if (strpos($selfUrl,"?SGLSESSID") == false)
            return $selfUrl."print/1/";
        else 
            return $selfUrl."print/1/";
    }


    function formatPhone($phone)
    {
        if (ereg("^\+", $phone)) {
            return preg_replace("/^0/i","",preg_replace("/[\|\&\~\!\"\(\)\\s\-]/i", "", $phone));
        } else {
            return "+48". preg_replace("/^0/i","",preg_replace("/[\|\&\~\!\"\(\)\\s\-]/i", "", $phone));
        }
    }


    function makeValidLinks($links)
    {
        return str_replace("&nbsp;&nbsp;&nbsp;","&nbsp;",$links);
    }
    /**
     * Formats datetime for the current user
     * @param   string  $sDateTime  Datetime in user or DB format
     * (YYYY-mm-dd HH:mm:ss or dd.mm.yyyy HH:mm:ss or YYYY-mm-dd or dd.mm.yyyy)
     * @return  string  Datetime formatted for the DB format (YYYY-mm-dd)
     * or (YYYY-mm-dd HH:mm:ss) if hours set; if date is not proper - return null
     */
    function formatDateTime2DB($sDateTime) {
        //check if date is in correct format
        $sResult = null;
        $aDateTime = explode(" ", $sDateTime);
        $sDate = $aDateTime[0];
        $sTime = $aDateTime[1];
        if (preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $sDate)) {
            $aDate = explode("-", $sDate);
            if (checkdate($aDate[1], $aDate[2], $aDate[0])) {
                $sResult = date("Y-m-d", mktime (0,0,0, $aDate[1], $aDate[2], $aDate[0]));
            }
        } elseif (preg_match("/[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}/", $sDate)) {
            $aDate = explode(".", $sDate);
            //$aDateArray = explode(' ', $aDate[2]);
            //$aDate[2] = $aDateArray[0];
            if (checkdate($aDate[1], $aDate[0], $aDate[2])) {
                $sResult = date("Y-m-d", mktime (0,0,0, $aDate[1], $aDate[0], $aDate[2]));
            }
        } elseif (strcmp($sDate, "now()") == 0) {
            $sResult = $sDate;
        }
        if ($sTime != "") {
            $sResult .= " ".$sTime;
        }
        return $sResult;
    }

}
?>
